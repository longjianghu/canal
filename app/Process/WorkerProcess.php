<?php declare(strict_types=1);

namespace App\Process;

use App\Data\SendData;

use Psr\Container\ContainerInterface;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;

use Hyperf\Utils\Arr;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Config\Annotation\Value;
use Hyperf\Process\AbstractProcess;

class WorkerProcess extends AbstractProcess
{
    /**
     * @Value("app.canal")
     */
    private $_canal;

    /**
     * @Inject()
     * @var SendData
     */
    private $_sendData;

    /**
     * 初始化.
     *
     * @access public
     * @param ContainerInterface $container 容器接口
     * @return void
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->nums = config('app.workerNum', swoole_cpu_num());
    }

    /**
     * 执行任务
     *
     * @access public
     * @return void
     */
    public function handle(): void
    {
        console()->notice('=== 开始连接Canal Server服务器 ===');

        try {
            $canal  = $this->_canal;
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SWOOLE);

            $client->connect(Arr::get($canal, 'host'), Arr::get($canal, 'port'));
            $client->checkValid();
            $client->subscribe(Arr::get($canal, 'clientId'), Arr::get($canal, 'destination'), Arr::get($canal, 'filter'));

            while (true) {
                $message = $client->get(100)->getEntries();

                if ( ! empty($message)) {
                    foreach ($message as $k => $v) {
                        go(function () use ($v) {
                            $entry = $this->_sendData->parseEntryData($v);

                            if (Arr::get($entry, 'code') == 200) {
                                $entry = Arr::get($entry, 'data');
                                $this->_sendData->send($entry);
                            }
                        });
                    }
                }

                usleep(100000);
            }

            $client->disConnect();
        } catch (\Throwable $e) {
            console()->error('=== Canal Server服务器连接失败 ===');
        }
    }
}
