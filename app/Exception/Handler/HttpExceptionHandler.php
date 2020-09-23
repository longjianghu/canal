<?php declare(strict_types=1);

namespace App\Exception\Handler;

use const APP_DEBUG;
use function get_class;
use ReflectionException;
use function sprintf;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Error\Annotation\Mapping\ExceptionHandler;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Exception\Handler\AbstractHttpErrorHandler;
use Throwable;

/**
 * HttpExceptionHandler
 *
 * @ExceptionHandler(\Throwable::class)
 */
class HttpExceptionHandler extends AbstractHttpErrorHandler
{
    /**
     * @param Throwable $e
     * @param Response  $response
     *
     * @return Response
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function handle(Throwable $e, Response $response): Response
    {
        $status = [
            'code'    => $e->getCode(),
            'data'    => [],
            'message' => $e->getMessage()
        ];

        if ( ! empty(APP_DEBUG)) {
            $status['data'] = [
                'error' => sprintf('(%s) %s', get_class($e), $e->getMessage()),
                'file'  => sprintf('At %s line %d', $e->getFile(), $e->getLine()),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return $response->withData($status);
    }
}
