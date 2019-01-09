<?php
namespace App\Controllers\Api;

use App\Models\Bucket;
use App\Models\Object;
use ManaPHP\Identity\Adapter\Jwt;
use ManaPHP\Rest\Controller;

/**
 * Class ObjectController
 * @package App\Controllers
 */
class ObjectController extends Controller
{
    public function indexAction()
    {
        $token = input('token');
        $bucket_name = jwt_get_claim($token, 'bucket');
        jwt_decode($token, 'bos.object.list', Bucket::value(['bucket_name' => $bucket_name], 'access_key'));

        $paginator = Object::paginate([
            'bucket_name' => $bucket_name,
            'key?' => input('key', ''),
            'key^=?' => input('prefix', ''),
            'mime_type?' => input('mime_type', ''),
            'extension?' => input('extension', ''),
            'md5?' => input('md5', ''),
            'created_time>=?' => ($created_after = input('created_after', '')) ? strtotime($created_after) : null,
            'created_time<=?' => ($created_before = input('created_before', '')) ? strtotime($created_before) : null,
        ]);

        $bucket = Bucket::firstOrFail(['bucket_name' => $bucket_name]);
        foreach ($paginator->items as $i => $item) {
            $paginator->items[$i]['url'] = $bucket->base_url . $item['key'];
        }

        return $paginator;
    }

    /**
     * @param \App\Models\Object $object
     */
    protected function _completeKey($object)
    {
        $object->key = preg_replace_callback('#\{([^\}]+)\}#', function ($matches) use ($object) {
            $placeholder = $matches[1];
            if ($placeholder === 'file') {
                return $object->original_name;
            } elseif ($placeholder === 'ext') {
                return $object->extension;
            } elseif ($placeholder === 'md5') {
                return $object->md5;
            } elseif (str_starts_with($placeholder, 'r') && strlen($placeholder) > 1 && is_numeric($num = substr($placeholder, 1))) {
                return mt_rand(0, $num);
            } elseif (str_starts_with($placeholder, 'h') && strlen($placeholder) > 1 && is_numeric($num = substr($placeholder, 1))) {
                return $this->random->getBase(max(1, min($placeholder, 64)), 16);
            } elseif (is_numeric($placeholder)) {
                return $this->random->getBase(max(1, min($placeholder, 64)));
            } else {
                return date($placeholder);
            }
        }, $object->key);
    }

    /**
     * @param \App\Models\Bucket $bucket
     * @param \App\Models\Object $object
     *
     * @return array
     */
    protected function _getCreateObjectResponse($bucket, $object)
    {
        $data = $object->toArray();
        $data['bucket'] = $data['bucket_name'];
        $data = array_except($data, ['object_id', 'bucket_id', 'bucket_name']);

        $data['url'] = $bucket->base_url . $object->key;
        $data['scope'] = 'bos.object.create.response';

        $jwt = new Jwt(['key' => $bucket->access_key]);

        return ['token' => $jwt->encode($data, 300), 'url' => $data['url']];
    }

    public function createAction()
    {
        $files = $this->request->getFiles();
        if (!$files) {
            $post_max_size = trim(ini_get('post_max_size'), 'M');
            $upload_max_filesize = trim(ini_get('upload_max_filesize'), 'M');
            $max_size = min($post_max_size, $upload_max_filesize) . 'M';
            return $this->response->setJsonError("file is not exists, max uploaded file size is $max_size");
        }

        if (count($files) !== 1) {
            return $this->response->setJsonError('only support upload one file.');
        }
        $file = $files[0];

        $token = input('token');
        $bucket_name = jwt_get_claim($token, 'bucket');

        $policy = jwt_decode($token, 'bos.object.create.request', Bucket::value(['bucket_name' => $bucket_name], 'access_key'));

        if (!isset($policy['bucket'])) {
            return $this->response->setJsonError('bucket name is missing');
        }

        if (!isset($policy['key'])) {
            return $this->response->setJsonError('key is missing');
        }

        if (!$bucket = Bucket::first(['bucket_name' => $policy['bucket']])) {
            return $this->response->setJsonError('bucket name is not exists');
        }

        $object = new Object();

        $object->key = $policy['key'];
        $object->bucket_id = $bucket->bucket_id;
        $object->bucket_name = $bucket->bucket_name;
        $object->original_name = $file->getName();
        $object->mime_type = $file->getType();
        $object->extension = strtolower($file->getExtension());
        if (strlen($object->extension) > 16) {
            $object->extension = '';
        }

        if (str_starts_with($object->mime_type, 'image/')) {
            $size = getimagesize($file->getTempName());
            $object->width = $size[0];
            $object->height = $size[1];
        } else {
            $object->width = 0;
            $object->height = 0;
        }

        $object->size = $file->getSize();
        $object->md5 = md5_file($file->getTempName());
        $object->ip = client_ip();

        if (str_contains($object->key, '{')) {
            $this->_completeKey($object);
        }

        if ($oldObject = Object::first(['bucket_name' => $object->bucket_name, 'key' => $object->key])) {
            if ($oldObject->md5 === $object->md5) {
                return $this->_getCreateObjectResponse($bucket, $object);
            }

            if (!isset($policy['insert_only']) || $policy['insert_only']) {
                return $this->response->setJsonError("`$object->key` key is exists in bucket");
            } else {
                Object::deleteAll(['bucket_name' => $object->bucket_name, 'key' => $object->key]);
            }
        }

        $file->moveTo("@uploads/{$object->bucket_name}/{$object->key}", '*');

        $object->create();

        return $this->_getCreateObjectResponse($bucket, $object);
    }
}