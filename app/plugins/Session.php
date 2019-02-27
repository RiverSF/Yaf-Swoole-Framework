<?php
/**
 * 插件类定义
 *
 */
class SessionPlugin extends Yaf_Plugin_Abstract
{

    public function __construct()
    {

    }

    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        ini_set("session.gc_maxlifetime", '43200');
        $path = APPLICATION_PATH.'/storage/session/';
        if (!file_exists($path)) {
            mkdir($path);
        }
        ini_set("session.save_path", $path);

        $controller = strtolower($request->getControllerName());
        if($controller != "syncservice"){
            Yaf_Session::getInstance();
        }
    }

}