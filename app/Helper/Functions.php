<?php declare(strict_types=1);

use GuzzleHttp\Client;

use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use Hyperf\Amqp\Producer;
use Hyperf\Guzzle\HandlerStackFactory;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

/**
 * AMQP
 *
 * @access public
 * @return Producer|mixed
 */
if (! function_exists('amqp')) {
    function amqp()
    {
        return container()->get(Producer::class);
    }
}

/**
 * 控制台日志
 *
 * @access public
 * @return StdoutLoggerInterface|mixed
 */
if (! function_exists('console')) {
    function console()
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

/**
 * 获取容器
 *
 * @access public
 * @return ContainerInterface
 */
if (! function_exists('container')) {
    function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}

/**
 * 获取 GuzzleHttp Client
 *
 * @access public
 * @return Client|mixed
 */
if (! function_exists('guzzleClient')) {
    function guzzleClient(array $options = []): Client
    {
        $config = ['handler' => (new HandlerStackFactory())->create(), 'verify' => false, 'http_errors' => false];
        $config = (! empty($options)) ? array_merge($config, $options) : $config;

        return make(Client::class, ['config' => $config]);
    }
}

/**
 * 校验手机号码
 *
 * @access public
 * @param string $mobile 手机号码
 * @return bool
 */
if (! function_exists('isMobile')) {
    function isMobile(string $mobile): bool
    {
        return (bool)preg_match('/^(0|86|17951)?1[3456789](\d){9}$/', $mobile);
    }
}

/**
 * 日志
 *
 * @access public
 * @return LoggerFactory|mixed
 */
if (! function_exists('logger')) {
    function logger(string $name = '', string $group = 'default'): LoggerInterface
    {
        return container()->get(LoggerFactory::class)->get($name, $group);
    }
}

/**
 * 数据请示
 *
 * @access public
 * @retrun mixed
 */
if (! function_exists('request')) {
    function request()
    {
        return container()->get(RequestInterface::class);
    }
}

/**
 * 响应方法
 *
 * @access public
 * @retrun mixed
 */
if (! function_exists('response')) {
    function response()
    {
        return container()->get(ResponseInterface::class);
    }
}

/**
 * 发送请求(表单提交)
 *
 * @param string $url     URL
 * @param array  $args    提交参数
 * @param array  $headers 头信息
 * @param string $method  方法名称
 * @return array
 */
if (! function_exists('sendRequest')) {
    function sendRequest(string $url, array $args = [], array $headers = [], $method = 'GET'): array
    {
        $status = ['code' => 0, 'data' => [], 'message' => ''];

        try {
            $options = [];
            $method  = (! empty($method)) ? $method : null;

            if (! empty($args)) {
                $options = (strtoupper($method) == 'POST') ? ['form_params' => $args] : ['query' => $args];
            }

            if (! empty($headers)) {
                $options['headers'] = $headers;
            }

            $client   = guzzleClient();
            $response = $client->request($method, $url, $options);

            if ($response->getStatusCode() != 200) {
                throw new Exception($response->getReasonPhrase());
            }

            $status = [
                'code'    => 200,
                'data'    => $response->getBody()->getContents(),
                'message' => '',
            ];
        } catch (Throwable $e) {
            $status['message'] = $e->getMessage();
        }

        return $status;
    }
}

/**
 * 格式化JSON
 *
 * @access public
 * @param array $data 待处理数据
 * @return PsrResponseInterface
 */
if (! function_exists('withJson')) {
    function withJson(array $data): PsrResponseInterface
    {
        if (isset($data['data'])) {
            if (empty($data['data']) && is_array($data['data'])) {
                $data['data'] = new stdClass();
            }
        }

        return response()->json($data);
    }
}
