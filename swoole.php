<?php
chdir(__DIR__);

require 'vendor/autoload.php';

$loader = new \ManaPHP\Loader();

require 'app/Swoole.php';
$app = new \App\Swoole($loader);
$app->main();