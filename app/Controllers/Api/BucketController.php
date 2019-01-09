<?php
namespace App\Controllers\Api;

use App\Models\Bucket;
use ManaPHP\Rest\Controller;

class BucketController extends Controller
{
    public function indexAction()
    {
        jwt_decode(input('token'), 'bos.bucket.list', param_get('bos.admin_key'));

        return Bucket::all([], null, ['bucket_name', 'base_url', 'access_key']);
    }

    public function createAction()
    {
        jwt_decode(input('token'), 'bos.bucket.create', param_get('bos.admin_key'));

        $bucket_name = input('bucket');
        $access_key = input('access_key');

        if (!preg_match('#^[a-z\d][a-z\d-]{1,30}[a-z\d]$#', $bucket_name)) {
            return $this->response->setJsonError('bucket name is invalid');
        }

        if (Bucket::exists(['bucket_name' => $bucket_name])) {
            return $this->response->setJsonError('bucket is exists.');
        }

        if ($base_url = input('base_url', '')) {
            $base_url = rtrim($base_url, '/') . '/';
            if (!str_contains($base_url, '://')) {
                $base_url = $this->request->getScheme() . '://' . $this->request->getHost() . $base_url;
            }
        } else {
            $base_url = $this->request->getUrl();
            if (($pos = strrpos($base_url, '/api/')) === false) {
                return $this->response->setJsonError('base_url can not be inferred');
            }
            $base_url = substr($base_url, 0, $pos) . "/uploads/$bucket_name/";
        }

        $bucket = new Bucket();

        $bucket->bucket_name = $bucket_name;
        $bucket->access_key = $access_key;
        $bucket->base_url = $base_url;

        return $bucket->create();
    }
}