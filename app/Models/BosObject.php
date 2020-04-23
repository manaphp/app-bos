<?php

namespace App\Models;

/**
 * Class App\Models\BosObject
 */
class BosObject extends \ManaPHP\Db\Model
{
    public $object_id;
    public $key;
    public $bucket_id;
    public $bucket_name;
    public $original_name;
    public $mime_type;
    public $extension;
    public $width;
    public $height;
    public $size;
    public $md5;
    public $ip;
    public $created_time;

    /**
     * @return string
     */
    public function getTable()
    {
        return 'bos_object';
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'object_id';
    }
}