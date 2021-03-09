<?php declare(strict_types=1);

namespace App\Amqp\Producer;

use Hyperf\Amqp\Annotation\Producer;
use Hyperf\Amqp\Message\ProducerMessage;

/**
 * @Producer(exchange="canal", routingKey="canal")
 */
class CanalProducer extends ProducerMessage
{
    /**
     * 初始化方法.
     *
     * @access public
     * @param mixed $data 任务数据
     * @return void
     */
    public function __construct($data)
    {
        $this->payload = $data;
    }
}
