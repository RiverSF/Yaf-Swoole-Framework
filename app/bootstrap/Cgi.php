<?php
/**
 * @name Bootstrap
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 *  * @see http://www.laruence.com/manual/ch06s02.html
 */

class Bootstrap extends Yaf_Bootstrap_Abstract
{

    private $_config;

    public function _initConfig(Yaf_Dispatcher $dispatcher)
    {
        $this->_config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $this->_config);
    }

    /*
    |--------------------------------------------------------------------------
    | Register The Auto Loader
    |--------------------------------------------------------------------------
    |
    | Composer provides a convenient, automatically generated class loader for
    | our application. We just need to utilize it! We'll simply require it
    | into the script here so that we don't have to worry about manual
    | loading any of our classes later on. It feels great to relax.
    |
    */
    public function _initComposer(Yaf_Dispatcher $dispatcher)
    {
        $autoload = $this->_config['composer']['autoload'];
        if (file_exists($autoload)) {
            Yaf_Loader::import($autoload);
        }
    }

    //错误转异常 统一处理
    public function _initError(Yaf_Dispatcher $dispatcher)
    {
        error_reporting(E_ALL);

        ob_start();
        $whoops = new \Whoops\Run;

        if (DEBUG) {
            ini_set('display_errors', 1);
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        } else {
            ini_set('display_errors', 0);
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler(\Library\Core\Log::getInstance()));
            $whoops->writeToOutput(false);
        }

        $whoops->register();
    }

    // 常用类简化名称
    public function _initAlias()
    {
        class_alias('FangStarNet\PHPValidator\Validator', 'Validator');
        class_alias('Library\Core\Log', 'Log');
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher)
    {
        $auth = new AuthPlugin();
        $session = new SessionPlugin();
        $dispatcher->registerPlugin($session);
        $dispatcher->registerPlugin($auth);
    }

    public function _initLoader(Yaf_Dispatcher $dispatcher)
    {

    }

    public function _initRoute(Yaf_Dispatcher $dispatcher)
    {

    }

    public function _initView(Yaf_Dispatcher $dispatcher)
    {

    }
}