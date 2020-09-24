<?php declare(strict_types=1);

namespace App\Process;

use App\Model\Data\MonitorData;

use Swoole\Coroutine;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;

/**
 * MonitorProcess
 *
 * @since 2.0
 * @Bean()
 */
class MonitorProcess extends UserProcess
{
    /**
     * @Inject()
     * @var MonitorData
     */
    private $_monitorData;

    /**
     * @param Process $process
     * @throws DbException
     */
    public function run(Process $process): void
    {
        $this->_monitorData->monitor();
    }
}