<?php
namespace App\Cli\Controllers;

use ManaPHP\Cli\Controller;

class BucketController extends Controller
{
    /**
     * create a new bucket
     *
     * @param string $name       bucket name
     * @param string $access_key access key
     */
    public function createCommand($name, $access_key = '')
    {
        $data = [];

        $data['bucket'] = $name;
        $data['access_key'] = $access_key ?: $this->random->getBase(32);

        $this->console->write($this->bosClient->createBucket($data));
    }

    /**
     * list all buckets
     */
    public function listCommand()
    {
        $this->console->write($this->bosClient->listBuckets());
    }
}