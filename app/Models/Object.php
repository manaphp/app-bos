<?php
namespace App\Models;

/**
 * Class App\Models\BosObject
 */
class Object extends \ManaPHP\Db\Model
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
     * @param mixed $context
     *
     * @return string
     */
    public function getSource($context = null)
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