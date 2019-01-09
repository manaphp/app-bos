<?php
namespace App;

use App\Controllers\Api\BucketController;
use App\Controllers\Api\ObjectController;

class Router extends \ManaPHP\Router
{
    public function __construct()
    {
        parent::__construct();

        $this->setAreas(['Api', 'Admin']);
        $this->addRest('/api/buckets', BucketController::class);
        $this->addRest('/api/objects', ObjectController::class);
    }
}
