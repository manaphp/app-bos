<?php

namespace App\Controllers;

use App\Models\BosBucket;
use ManaPHP\Rest\Controller;
use ManaPHP\Helper\Str;

class BucketController extends Controller
{
    public function indexAction()
    {
        jwt_verify($this->request->getToken(), 'bos.bucket.list');

        return BosBucket::all([], null, ['bucket_name', 'base_url', 'created_time']);
    }

    public function createAction($bucket, $base_url = '')
    {
        jwt_verify($this->request->getToken(), 'bos.bucket.create');

        if (!preg_match('#^[a-z\d][a-z\d-]{1,30}[a-z\d]$#', $bucket)) {
            return "`$bucket` bucket name must be compatible with DNS";
        }

        if (BosBucket::exists(['bucket_name' => $bucket])) {
            return 'bucket is exists.';
        }

        if ($base_url) {
            $base_url = rtrim($base_url, '/') . '/';
            if (!Str::contains($base_url, '://')) {
                $base_url = $this->request->getScheme() . '://' . $this->request->getServer('HTTP_HOST') . $base_url;
            }
        } else {
            $base_url = $this->request->getUrl();
            if (($pos = strrpos($base_url, '/api/')) === false) {
                return 'base_url can not be inferred';
            }
            $base_url = substr($base_url, 0, $pos) . "/uploads/$bucket/";
        }

        $bosBucket = new BosBucket();

        $bosBucket->bucket_name = $bucket;
        $bosBucket->base_url = $base_url;

        return $bosBucket->create();
    }
}