<?php declare(strict_types=1);

use Swoft\Server\SwooleEvent;
use Swoft\Http\Server\HttpServer;
use Swoft\Log\Handler\FileHandler;
use Swoft\Task\Swoole\TaskListener;
use Swoft\Task\Swoole\FinishListener;

return [
    'httpServer'         => [
        'class'   => HttpServer::class,
        'port'    => 18306,
        'on'      => [
            SwooleEvent::TASK   => \bean(TaskListener::class),
            SwooleEvent::FINISH => \bean(FinishListener::class)
        ],
        'process' => [],
        'setting' => [
            'task_enable_coroutine' => true,
            'enable_static_handler' => true,
            'worker_num'            => 6,
            'document_root'         => dirname(__DIR__).'/public',
        ]
    ],
    'httpDispatcher'     => [
        'middlewares'      => [
            App\Http\Middleware\FaviconMiddleware::class,
            App\Http\Middleware\TrimMiddleware::class,
        ],
        'afterMiddlewares' => [\Swoft\Http\Server\Middleware\ValidatorMiddleware::class]
    ],
    'lineFormatter'      => [
        'format'     => '%datetime% [%level_name%] [%channel%] [%event%] [tid:%tid%] [cid:%cid%] %messages%',
        'dateFormat' => 'Y-m-d H:i:s',
    ],
    'noticeHandler'      => [
        'class'     => FileHandler::class,
        'logFile'   => '@runtime/logs/sys-%d{Y-m-d}.log',
        'formatter' => \bean('lineFormatter'),
        'levels'    => 'notice,error,warning,trace',
    ],
    'applicationHandler' => [
        'class'     => FileHandler::class,
        'logFile'   => '@runtime/logs/app-%d{Y-m-d}.log',
        'formatter' => \bean('lineFormatter'),
        'levels'    => 'info,debug',
    ],
    'logger'             => [
        'flushRequest' => false,
        'enable'       => true,
        'handlers'     => [
            'application' => \bean('applicationHandler'),
            'notice'      => \bean('noticeHandler'),
        ],
    ]
];
