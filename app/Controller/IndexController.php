<?php declare(strict_types=1);

namespace App\Controller;

class IndexController extends AbstractController
{
    /**
     * 默认首页
     *
     * @access public
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function index()
    {
        return withJson(['code' => 200, 'data' => ['canal.client'], 'message' => '']);
    }
}