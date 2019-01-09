<?php

return [
    'env' => env('APP_ENV', 'prod'),
    'debug' => env('APP_DEBUG', false),
    'version' => '1.1.1',
    'timezone' => 'PRC',
    'master_key' => env('MASTER_KEY'),
    'services' => [],
    'params' => [
        'bos' => ['admin_key' => 'admin']
    ],
    'aliases' => [
        '@uploads' => '@root/public/uploads'
    ],
    'components' => [
        'db' => [env('DB_URL')],
        'logger' => ['level' => env('LOGGER_LEVEL', 'info')],
        'httpClient' => ['proxy' => env('HTTP_CLIENT_PROXY', '')],
        'bosClient' => ['endpoint' => env('BOS_UPLOADER_ENDPOINT'), 'admin_key' => 'admin', 'access_key' => env('BOS_UPLOADER_ACCESS_KEY', [])],
    ],
];