<?php
namespace App\Models;

/**
 * Class App\Models\BosBucket
 */
class Bucket extends \ManaPHP\Db\Model
{
    public $bucket_id;
    public $bucket_name;
    public $base_url;
    public $access_key;
    public $created_time;

    /**
     * @param mixed $context
     *
     * @return string
     */
    public function getSource($context = null)
    {
        return 'bos_bucket';
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return 'bucket_id';
    }
}