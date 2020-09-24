<?php declare(strict_types=1);

namespace App\Model\Data;

use Swoft\Log\Helper\Log;
use Swoft\Stdlib\Helper\Arr;
use xingwenge\canal_php\CanalClient;
use xingwenge\canal_php\CanalConnectorFactory;

use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Config\Annotation\Mapping\Config;

/**
 * MonitorData
 *
 * @Bean()
 * @package App\Model\Data
 */
class MonitorData
{
    /**
     * @Config("app.canal")
     */
    private $_canal;

    /**
     * @Config("app.serverUrl")
     */
    private $_serverUrl;

    /**
     * @Config("app.nsq")
     */
    private $_nsq;

    /**
     * 监控数据
     *
     * @access public
     * @return array
     */
    public function monitor()
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $canal  = $this->_canal;
            $client = CanalConnectorFactory::createClient(CanalClient::TYPE_SOCKET_CLUE);

            $client->connect(Arr::get($canal, 'host'), Arr::get($canal, 'port'));
            $client->checkValid();
            $client->subscribe(Arr::get($canal, 'clientId'), Arr::get($canal, 'destination'), Arr::get($canal, 'filter'));

            while (true) {
                $message = $client->get(100)->getEntries();

                if (empty($message)) {
                    continue;
                }

                foreach ($message as $k => $v) {
                    $entry = $this->_parseEntryData($v);

                    if (Arr::get($entry, 'code') == 200) {
                        $entry = Arr::get($entry, 'data');

                        Log::info('解析数据', json_encode($entry));

                        $this->_sendQuery($entry);
                    }
                }
            }

            $client->disConnect();
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 发送数据
     *
     * @access public
     * @param array $data 发送数据
     * @return array
     */
    private function _sendQuery(array $data)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($data)) {
                throw new \Exception('发送数据不能为空！');
            }

            // POST提交数据
            $url = $this->_serverUrl;

            if ( ! empty($url)) {
                $query = sendRequest($url, $data, [], 'POST');
                Log::info('URL发送结果:', (Arr::get($query, 'code') == 200) ? '发送成功！' : Arr::get($query, 'message'));
            }

            // NSQ队列
            $clientIp   = Arr::get($this->_nsq, 'clientIp');
            $clientPort = Arr::get($this->_nsq, 'clientPort');
            $topic      = Arr::get($this->_nsq, 'topic');

            if ( ! empty($clientIp)) {
                $nsq   = new \Nsq();
                $hosts = [sprintf('%s:%s', $clientIp, $clientPort)];

                $isConnected = $nsq->connectNsqd($hosts);

                if ( ! empty($isConnected)) {
                    $nsq->publish($topic, json_encode($data));
                    $nsq->closeNsqdConnection();

                    $str = '发送成功!';
                } else {
                    $str = '服务器连接失败!';
                }

                Log::info('NSQ发送结果:', $str);
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 解析数据
     *
     * @access private
     * @param Entry $entry 实例
     * @return array
     */
    private function _parseEntryData(Entry $entry)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($entry)) {
                throw new \Exception('实例数据不能为空！');
            }

            $entryType = $entry->getEntryType();

            if (in_array($entryType, [EntryType::TRANSACTIONBEGIN, EntryType::TRANSACTIONEND])) {
                throw new \Exception('不处理事务记录！');
            }

            $rowChange = new RowChange();
            $rowChange->mergeFromString($entry->getStoreValue());
            $evenType = $rowChange->getEventType();
            $header   = $entry->getHeader();

            $data = [
                'filename'   => $header->getLogfileName(),
                'offset'     => $header->getLogfileOffset(),
                'schemaName' => $header->getSchemaName(),
                'tableName'  => $header->getTableName(),
                'eventType'  => $header->getEventType(),
                'sql'        => '',
                'rawData'    => [],
                'newData'    => []
            ];

            $sql = $rowChange->getSql();
            $sql = sprintf('%s;', $sql);

            $data['sql'] = str_replace(PHP_EOL, '', $sql);

            $rowDatas = $rowChange->getRowDatas();

            /**
             * @var RowData $rowDatas
             */
            foreach ($rowDatas as $k => $v) {
                $before = $v->getBeforeColumns();
                $after  = $v->getAfterColumns();

                switch ($evenType) {
                    case EventType::DELETE:
                        $data['rawData'] = $this->_getColumnData($before);
                        break;
                    case EventType::INSERT:
                        $data['newData'] = $this->_getColumnData($after);
                        break;
                    default:
                        $data['rawData'] = $this->_getColumnData($before);
                        $data['newData'] = $this->_getColumnData($after);
                        break;
                }
            }

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 获取数据列
     *
     * @access private
     * @param Column $columns 数据列
     * @return array
     */
    private function _getColumnData(Column $columns)
    {
        $data = [];

        foreach ($columns as $k => $v) {
            $data[$v->getName()] = $v->getValue();
        }

        return $data;
    }
}