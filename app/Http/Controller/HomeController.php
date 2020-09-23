<?php declare(strict_types=1);

namespace App\Http\Controller;

use Swoft\Http\Message\Request;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Annotation\Mapping\RequestMethod;

/**
 * HomeController
 *
 * @Controller(prefix="/")
 */
class HomeController
{
    /**
     * 默认首页
     *
     * @access public
     * @param Request $request
     * @RequestMapping(route="/",method=RequestMethod::GET)
     * @return array
     */
    public function index(Request $request)
    {
        return withJson(['code' => 200, 'data' => ['canal.client'], 'message' => '']);
    }
}
