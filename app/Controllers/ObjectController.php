<?php

namespace App\Controllers;

use App\Models\BosBucket;
use App\Models\BosObject;
use App\Services\ObjectLimitCheckerService;
use ManaPHP\Helper\Arr;
use ManaPHP\Helper\LocalFS;
use ManaPHP\Helper\Str;
use ManaPHP\Rest\Controller;

/**
 * Class ObjectController
 *
 * @package App\Controllers
 */
class ObjectController extends Controller
{
    public function indexAction($bucket)
    {
        jwt_verify($this->request->getToken(), 'bos.object.list');

        $paginator = BosObject::search([
            'bucket_name' => $bucket,
            'key?' => input('key', ''),
            'key^=?' => input('prefix', ''),
            'mime_type?' => input('mime_type', ''),
            'extension?' => input('extension', ''),
            'md5?' => input('md5', ''),
            'created_time>=?' => ($created_after = input('created_after', '')) ? strtotime($created_after) : null,
            'created_time<=?' => ($created_before = input('created_before', '')) ? strtotime($created_before) : null,
        ])->orderBy(['object_id' => SORT_DESC])->paginate();

        if (!$bosBucket = BosBucket::first(['bucket_name' => $bucket])) {
            return "`$bucket` bucket is not exists";
        }

        foreach ($paginator->items as $i => $item) {
            $paginator->items[$i]['url'] = $bosBucket->base_url . $item['key'];
        }

        return $paginator;
    }

    /**
     * @param \App\Models\BosObject $object
     *
     * @return string
     */
    protected function _completeKey($object)
    {
        return preg_replace_callback(/** @lang text */ '#\{([^\}]+)\}#', function ($matches) use ($object) {
            $placeholder = $matches[1];
            if ($placeholder === 'file') {
                return $object->original_name;
            } elseif ($placeholder === 'ext') {
                return $object->extension;
            } elseif ($placeholder === 'md5') {
                return $object->md5;
            } elseif ($placeholder === 'md5_l1') {
                $md5 = $object->md5;
                return substr($md5, 0, 2) . '/' . $md5;
            } elseif ($placeholder === 'md5_l2') {
                $md5 = $object->md5;
                return substr($md5, 0, 2) . '/' . substr($md5, 2, 2) . '/' . $md5;
            } elseif ($placeholder === 'uuid') {
                return bin2hex(random_bytes(16));
            } elseif ($placeholder === 'uuid_l1') {
                $uuid = bin2hex(random_bytes(16));
                return substr($uuid, 0, 2) . '/' . $uuid;
            } elseif ($placeholder === 'uuid_l2') {
                $uuid = bin2hex(random_bytes(16));
                return substr($uuid, 0, 2) . '/' . substr($uuid, 2, 2) . '/' . $uuid;
            } else {
                return date($placeholder);
            }
        }, $object->key);
    }

    /**
     * @param \App\Models\BosBucket $bucket
     * @param \App\Models\BosObject $object
     *
     * @return array
     */
    protected function _createObjectResponse($bucket, $object)
    {
        $data = $object->toArray();
        $data['bucket'] = $data['bucket_name'];
        $data = Arr::except($data, ['bucket_name']);
        $data['url'] = $bucket->base_url . $object->key;

        return ['token' => jwt_encode($data, 300, 'bos.object.create.response'), 'url' => $data['url']];
    }

    public function createAction(ObjectLimitCheckerService $objectLimitCheckerService)
    {
        $policy = jwt_decode($this->request->getToken(), 'bos.object.create.request');
        $bucket = $policy['bucket'];

        if (!$files = $this->request->getFiles()) {
            $post_max_size = trim(ini_get('post_max_size'), 'M');
            $upload_max_filesize = trim(ini_get('upload_max_filesize'), 'M');
            $max_size = min($post_max_size, $upload_max_filesize) . 'M';
            return "file is not exists, max uploaded file size is $max_size";
        }

        if (count($files) !== 1) {
            return 'only support upload one file.';
        }
        $file = $files[0];

        if (!isset($policy['bucket'])) {
            return 'bucket name is missing';
        }

        if (!isset($policy['key'])) {
            return 'key is missing';
        }

        if (!$bosBucket = BosBucket::first(['bucket_name' => $bucket])) {
            return "`$bucket` bucket is not exists";
        }

        $bosObject = new BosObject();

        $bosObject->key = $policy['key'];
        $bosObject->bucket_id = $bosBucket->bucket_id;
        $bosObject->bucket_name = $bosBucket->bucket_name;
        $bosObject->original_name = $file->getName();
        $bosObject->mime_type = $file->getType();
        $bosObject->extension = strtolower($file->getExtension());
        if (strlen($bosObject->extension) > 16) {
            $bosObject->extension = '';
        }

        if (Str::startsWith($bosObject->mime_type, 'image/')) {
            $size = getimagesize($file->getTempName());
            $bosObject->width = $size[0];
            $bosObject->height = $size[1];
        } else {
            $bosObject->width = 0;
            $bosObject->height = 0;
        }

        $bosObject->size = $file->getSize();
        $bosObject->md5 = md5_file($file->getTempName());
        $bosObject->ip = client_ip();

        if (str::contains($bosObject->key, '{')) {
            $bosObject->key = $this->_completeKey($bosObject);
        }

        $target = path("@uploads/{$bosObject->bucket_name}/{$bosObject->key}");
        if ($oldObject = BosObject::first(['bucket_name' => $bosObject->bucket_name, 'key' => $bosObject->key])) {
            if ($oldObject->md5 === $bosObject->md5 && LocalFS::fileExists($target)) {
                return $this->_createObjectResponse($bosBucket, $bosObject);
            }

            if (!isset($policy['insert_only']) || $policy['insert_only']) {
                return "`$bosObject->key` key is exists in bucket";
            }

            $bosObject->object_id = $oldObject->object_id;
        }

        if (isset($policy['limit'])) {
            if (($r = $objectLimitCheckerService->checkLimit($bosObject, $policy['limit'])) !== true) {
                return $this->response->setJsonError("`$r` limit is not satisfied");
            }
        }

        $file->moveTo($target, '*', true);

        $bosObject->save();

        return $this->_createObjectResponse($bosBucket, $bosObject);
    }
}
