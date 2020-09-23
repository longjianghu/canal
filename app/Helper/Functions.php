<?php declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Promise;

use Swoft\Stdlib\Helper\Arr;

/**
 * 发送请求(表单提交)
 *
 * @access public
 * @param string $url     URL
 * @param array  $args    提交参数
 * @param array  $headers HEAD信息
 * @param string $method  请求方法
 * @return array
 */
if ( ! function_exists('sendRequest')) {
    function sendRequest(string $url, array $args = [], array $headers = [], $method = 'GET')
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $options = [];
            $method  = ( ! empty($method)) ? $method : null;

            if ( ! empty($args)) {
                $options = (strtoupper($method) == 'POST') ? ['form_params' => $args] : ['query' => $args];
            }

            if ( ! empty($headers)) {
                $options['headers'] = $headers;
            }

            $response = (new Client(['verify' => false]))->request($method, $url, $options);
            $status   = ['code' => 200, 'data' => $response->getBody()->getContents(), 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 并发请求
 *
 * $args格式：[['url'=>'','method'=>'get/post','query'=>[],'header'=>[]],url]
 *
 * @access public
 * @param array $args 提交参数
 * @return array
 */
if ( ! function_exists('sendMultiRequest')) {
    function sendMultiRequest(array $args)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $data = $promises = [];

            $client = new Client(['verify' => false]);

            foreach ($args as $k => $v) {
                $url = Arr::get($v, 'url');
                $url = ( ! empty($url)) ? $url : $v;

                $method = Arr::get($v, 'method', 'GET');
                $method = strtoupper($method);

                $query  = Arr::get($v, 'query');
                $header = Arr::get($v, 'header');

                $options = [];

                if ( ! empty($query)) {
                    $field = ($method == 'POST') ? 'form_params' : 'query';

                    $options[$field] = $query;
                }

                if ( ! empty($header)) {
                    $options['headers'] = $header;
                }

                $promises[] = ($method == 'POST') ? $client->postAsync($url, $options) : $client->getAsync($url, $options);
            }

            $result = Promise\unwrap($promises);

            foreach ($result as $k => $v) {
                $data[$k] = ($v->getStatusCode() == 200) ? $v->getBody()->getContents() : $v->getReasonPhrase();
            }

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 单站点并发请求
 *
 * $args格式：[['uri'=>'','method'=>'get/post','query'=>[],'header'=>[]],uri]
 *
 * @access public
 * @param string $baseUrl 基础URL
 * @param array  $args    提交参数
 * @return array
 */
if ( ! function_exists('singleMultiRequest')) {
    function singleMultiRequest(string $baseUrl, array $args)
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $data   = $promises = [];
            $client = new Client(['base_uri' => $baseUrl, 'verify' => false]);

            foreach ($args as $k => $v) {
                $uri = Arr::get($v, 'uri');
                $uri = ( ! empty($uri)) ? $uri : $v;

                $method = Arr::get($v, 'method', 'GET');
                $method = strtoupper($method);

                $query  = Arr::get($v, 'query');
                $header = Arr::get($v, 'header');

                $options = [];

                if ( ! empty($query)) {
                    $field = ($method == 'POST') ? 'form_params' : 'query';

                    $options[$field] = $query;
                }

                if ( ! empty($header)) {
                    $options['headers'] = $header;
                }

                $promises[] = ($method == 'POST') ? $client->postAsync($uri, $options) : $client->getAsync($uri, $options);
            }

            $result = Promise\unwrap($promises);

            foreach ($result as $k => $v) {
                $data[$k] = ($v->getStatusCode() == 200) ? $v->getBody()->getContents() : $v->getReasonPhrase();
            }

            $status = ['code' => 200, 'data' => $data, 'message' => ''];
        } catch (\Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 格式化JSON
 *
 * @access public
 * @param mixed $data 待处理数据
 * @return mixed
 */
if ( ! function_exists('withJson')) {
    function withJson(array $data)
    {
        if (isset($data['data'])) {
            if (empty($data['data']) && is_array($data['data'])) {
                $data['data'] = new \stdClass();
            }
        }

        return $data;
    }
}