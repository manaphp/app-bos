<?php

namespace App;

use App\Controllers\BucketController;
use App\Controllers\ObjectController;

class Router extends \ManaPHP\Router
{
    public function __construct()
    {
        parent::__construct();
        $this->_prefix = '/api';

        $this->addRest('/buckets', BucketController::class);
        $this->addRest('/objects', ObjectController::class);
    }
}
