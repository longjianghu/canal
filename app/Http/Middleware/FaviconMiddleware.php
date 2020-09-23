<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Http\Message\Request;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Http\Server\Contract\MiddlewareInterface;

/**
 * FaviconMiddleware
 *
 * @Bean()
 */
class FaviconMiddleware implements MiddlewareInterface
{
    /**
     * 自定义处理方法.
     *
     * @access public
     * @param ServerRequestInterface|Request $request
     * @param RequestHandlerInterface        $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUriPath() === '/favicon.ico') {
            return context()->getResponse()->withStatus(404);
        }

        return $handler->handle($request);
    }
}
