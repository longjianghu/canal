<?php declare(strict_types=1);

return [
    'default' => [
        'enable' => true,
        'host'   => env('NSQ_CLIENT_IP', '172.17.0.1'),
        'port'   => env('NSQ_CLIENT_PORT', 4150),
        'pool'   => [
            'min_connections' => 1,
            'max_connections' => 10,
            'connect_timeout' => 10.0,
            'wait_timeout'    => 3.0,
            'heartbeat'       => -1,
            'max_idle_time'   => 60.0,
        ],
        'nsqd'   => [
            'port'    => 4151,
            'options' => [],
        ],
    ],
];
