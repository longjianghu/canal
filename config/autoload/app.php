<?php declare(strict_types=1);

return [
    'workerNum' => env('WORKER_NUM', 1),
    'canal'     => [
        'host'        => env('CANAL_HOST', '127.0.0.1'),
        'port'        => env('CANAL_PORT', 11111),
        'clientId'    => env('CANAL_CLIENT_ID', 10000),
        'destination' => env('CANAL_DESTINATION', 'canal-server'), // 需要和Canal Server中一致
        'filter'      => env('CANAL_FILTER', '.*'), // 表示所有表
    ], // Canal 配置
    'apiUrl'    => env('API_URL'), // 远程接口地址
    'nsqTopic'  => env('NSQ_TOPIC', 'canal'), // NSQ TOPIC
    'openAPI'   => env('OPEN_API', 0), // 是否开放 API
    'nsqQueue'  => env('NSQ_QUEUE', 0), // 是否开放 NSQ
    'amqpQueue' => env('AMQP_QUEUE', 0), // 是否开放 RabbitMQ
];