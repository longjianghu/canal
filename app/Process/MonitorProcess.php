<?php declare(strict_types=1);

namespace App\Process;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;

/**
 * MonitorProcess
 *
 * @since 2.0
 * @Bean()
 */
class MonitorProcess extends UserProcess
{
    /**
     * @param Process $process
     * @throws DbException
     */
    public function run(Process $process): void
    {
        while (true) {

        }
    }
}