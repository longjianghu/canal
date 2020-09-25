<?php declare(strict_types=1);

return [
    'canal'     => [
        'host'        => env('CANAL_HOST', '127.0.0.1'),
        'port'        => env('CANAL_PORT', 11111),
        'clientId'    => env('CANAL_CLIENT_ID', 10000),
        'destination' => env('CANAL_DESTINATION', 'canal-server'),
        'filter'      => env('CANAL_FILTER', '.*\\..*'),
    ], // Canal 配置
    'serverUrl' => env('SERVER_URL'), // 远程提交URL
    'nsq'       => [
        'clientIp'   => env('NSQ_CLIENT_IP', '127.0.0.1'),
        'clientPort' => env('NSQ_CLIENT_PORT', 4150),
        'topic'      => env('NSQ_TOPIC', 'canal'),
    ], // NSQ 队列配置
];
