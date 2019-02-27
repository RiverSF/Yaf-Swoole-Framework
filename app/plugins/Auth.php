<?php

/**
 * 插件类定义
 *
 */
class AuthPlugin extends Yaf_Plugin_Abstract
{

    public function __construct()
    {

    }

    //在路由之前触发，这个是7个事件中, 最早的一个. 但是一些全局自定的工作, 还是应该放在Bootstrap中去完成
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
    }

    //路由结束之后触发，此时路由一定正确完成, 否则这个事件不会触发
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        $controller = strtolower($request->getControllerName());
        $aciton = strtolower($request->getActionName());
    }
}