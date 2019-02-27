<?php
/**
 * @desc Swoole 服务启动文件 接管客户端请求与分发
 * User: River
 * Date: 2019/2/27
 * Time: 13:43
 */
class WebSocket
{
    public static $instance;
    public $http;
    public static $get;
    public static $post;
    public static $header;
    public static $server;
    private $application;

    public function __construct()
    {
        $ws = new swoole_websocket_server("127.0.0.1", 9502);
        $ws->set(['worker_num' => 1, 'daemonize' => 0, 'max_request' => 10000, 'dispatch_mode' => 1]);
        //$ws->on('WorkerStart' , array( $this , 'onWorkerStart'));

        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
            var_dump($request->fd, $request->get, $request->server);
            $ws->push($request->fd, "hello, welcome\n");
        });

        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            echo "Message: {$frame->data}\n";
            $ws->push($frame->fd, "server: {$frame->data}");
        });

        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();
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
     * @return WebSocket
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
WebSocket::getInstance();