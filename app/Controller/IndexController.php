<?php declare(strict_types=1);

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;

class IndexController extends AbstractController
{
    /**
     * 默认首页
     *
     * @access public
     * @return ResponseInterface
     */
    public function index(): ResponseInterface
    {
        return withJson(['code' => 200, 'data' => ['canal.client'], 'message' => '']);
    }
}
