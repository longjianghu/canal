<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Data\MonitorData;

use Swoole\Coroutine;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;

use Swoft\Log\Helper\Log;
use Swoft\Process\Process;
use Swoft\Stdlib\Helper\Arr;
use Swoft\Process\UserProcess;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Config\Annotation\Mapping\Config;

/**
 * MonitorProcess
 *
 * @since 2.0
 * @Bean()
 */
class MonitorProcess extends UserProcess
{
    /**
     * @Config("app.canal")
     */
    private $_canal;

    /**
     * @Inject()
     * @var MonitorData
     */
    private $_monitorData;

    /**
     * @param Process $process
     */
    public function run(Process $process): void
    {
        Log::info(sprintf('连接Canal Server服务器！'));

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
                        sgo(function () use ($v) {
                            $entry = $this->_monitorData->parseEntryData($v);

                            if (Arr::get($entry, 'code') == 200) {
                                $entry = Arr::get($entry, 'data');
                                $this->_monitorData->send($entry);
                            }
                        });
                    }
                }

                Coroutine::sleep(1);
            }

            $client->disConnect();
        } catch (\Throwable $e) {
            Log::info(sprintf('Canal Server服务器连接失败！'));
        }
    }
}