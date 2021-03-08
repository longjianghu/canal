<?php declare(strict_types=1);

namespace App\Data;

use Hyperf\Nsq\Nsq;
use Hyperf\Utils\Arr;
use Hyperf\Config\Annotation\Value;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\RowData;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;

class SendData
{
    /**
     * @Value("app.apiUrl")
     */
    private $_apiUrl;

    /**
     * @Value("app.nsqTopic")
     */
    private $_nsqTopic;

    /**
     * 解析数据
     *
     * @access private
     * @param Entry $entry 实例
     * @return array
     */
    public function parseEntryData(Entry $entry)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($entry)) {
                throw new \Exception('实例数据不能为空！');
            }

            if (in_array($entry->getEntryType(), [EntryType::TRANSACTIONBEGIN, EntryType::TRANSACTIONEND])) {
                throw new \Exception('事务事件直接忽略！');
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
            $sql = ( ! empty($sql)) ? sprintf('%s;', $sql) : '';

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
                        $data['rawData'][] = $this->_getColumnData($before);
                        break;
                    case EventType::INSERT:
                        $data['newData'][] = $this->_getColumnData($after);
                        break;
                    default:
                        $data['rawData'][] = $this->_getColumnData($before);
                        $data['newData'][] = $this->_getColumnData($after);
                        break;
                }
            }

            $data = json_encode($data);

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 发送数据
     *
     * @access public
     * @param string $data 发送数据
     * @return array
     */
    public function send(string $data)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            if (empty($data)) {
                throw new \Exception('发送数据不能为空！');
            }

            $taskId = md5($data);

            // POST提交数据
            if ( ! empty($this->_apiUrl)) {
                $args   = [];
                $apiUrl = explode(',', $this->_apiUrl);

                foreach ($apiUrl as $k => $v) {
                    if ( ! filter_var($v, FILTER_VALIDATE_URL)) {
                        continue;
                    }

                    $args[] = ['url' => $v, 'method' => 'post', 'query' => ['data' => $data]];
                }

                if ( ! empty($args)) {
                    $query = sendMultiRequest($args);
                    console()->info(sprintf('%s[URL]:%s', $taskId, (Arr::get($query, 'code') == 200) ? '发送成功！' : Arr::get($query, 'message')));
                }
            }

            // NSQ队列
            if ( ! empty($this->_nsqTopic)) {
                $nsq = make(Nsq::class);
                $nsq->publish($this->_nsqTopic, $data);

                console()->info(sprintf('%s[NSQ]:%s', $taskId, $data));
            }

            $status = ['code' => 200, 'data' => [], 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }

    /**
     * 获取数据列
     *
     * @access private
     * @param object $columns 数据列
     * @return array
     */
    private function _getColumnData(object $columns)
    {
        $data = [];

        foreach ($columns as $k => $v) {
            $data[$v->getName()] = $v->getValue();
        }

        return $data;
    }
}