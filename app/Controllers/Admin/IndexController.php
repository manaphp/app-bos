<?php
namespace App\Controllers\Admin;

use ManaPHP\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        return date('Y-m-d H:i:s');
    }
}