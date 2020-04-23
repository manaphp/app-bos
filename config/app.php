<?php

return [
    'id' => 'bos',
    'env' => env('APP_ENV', 'prod'),
    'debug' => env('APP_DEBUG', false),
    'version' => '1.1.1',
    'timezone' => 'PRC',
    'master_key' => env('MASTER_KEY'),
    'services' => [],
    'aliases' => [
        '@uploads' => '@root/public/uploads'
    ],
    'components' => [
        '!httpServer' => ['port' => 9501, 'max_request' => 1000000],
        'db' => [env('DB_URL')],
        'redis' => env('REDIS_URL'),
        'logger' => ['level' => env('LOGGER_LEVEL', 'info')],
    ],
    'plugins' => [
        'cors'
    ]
];