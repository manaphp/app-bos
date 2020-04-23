<?php

namespace App\Models;

/**
 * Class App\Models\BosBucket
 */
class BosBucket extends \ManaPHP\Db\Model
{
    public $bucket_id;
    public $bucket_name;
    public $base_url;
    public $created_time;

    /**
     * @return string
     */
    public function getTable()
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