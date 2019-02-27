<?php
/**
 * @desc Swoole 服务启动文件 接管客户端请求与分发
 * User: River
 * Date: 2019/2/27
 * Time: 13:43
 */
class Http
{
    public $http;

    public static $instance;
    public static $get;
    public static $post;
    public static $header;
    public static $server;
    private $application;

    public function __construct()
    {
        $this->http = new swoole_http_server("127.0.0.1", 9501);
        $this->http->set(
            array(
                'worker_num' => 16,
                'daemonize' => true,
                'max_request' => 10000,
                'dispatch_mode' => 1
            )
        );
        $this->http->on('WorkerStart' , array( $this , 'onWorkerStart'));
        $this->http->on('request', function ($request, $response) {
            if( isset($request->server) ) {
                self::$server = $request->server;
            }else{
                self::$server = [];
            }
            if( isset($request->header) ) {
                self::$header = $request->header;
            }else{
                self::$header = [];
            }
            if( isset($request->get) ) {
                self::$get = $request->get;
            }else{
                self::$get = [];
            }
            if( isset($request->post) ) {
                self::$post = $request->post;
            }else{
                self::$post = [];
            }
            // TODO handle img
            ob_start();
            try {
                $yaf_request = new Yaf_Request_Http(Http::$server['request_uri']);
                $this->application->getDispatcher()->dispatch($yaf_request);

                // unset(Yaf_Application::app());
            } catch ( Yaf_Exception $e ) {
                var_dump( $e );
            }

            $result = ob_get_contents();
            ob_end_clean();
            // add Header
            // add cookies
            // set status
            $response->end($result);
        });
        $this->http->start();
    }

    /**
     * 启动 Yaf
     */
    public function onWorkerStart()
    {
        define('APPLICATION_PATH', dirname(__DIR__));
        $this->application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");
        ob_start();
        $this->application->bootstrap()->run();
        ob_end_clean();
    }

    /**
     * 启动 Swoole Http 服务
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
Http::getInstance();