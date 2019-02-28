<?php
/**
 * 使用 Yar 实现 RPC 服务
 *
 * Created by PhpStorm.
 * User: River
 * Date: 2018/1/10
 * Time: 16:25
 */

use Library\Core\Controller;

class YarServerController extends Controller
{

    /**
     *  初始化访问控制
     */
    public function init(){}


    /**
     * 开放对外服务
     *
     * @usage new \Yar_Client('http://xxx.com/yarserver/demo')
     */
    public function demoAction()
    {
        $server = new \Yar_Server(new \Model\RPC\Demo());
        $server->handle();
        return false;
    }
}