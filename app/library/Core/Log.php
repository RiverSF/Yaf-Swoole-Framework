<?php
/**
 * Created by PhpStorm.
 * User: BM
 */

namespace Library\Core;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;

class Log
{
    private static $name = 'DEMO';
    private static $log = null;

    private static $path = '/storage/logs/syslog.log';

    public static function getInstance()
    {
        if (is_null(self::$log)) {
            self::$log = $log = new Logger(self::$name);
            $handler = new RotatingFileHandler(APPLICATION_PATH . self::$path, 30);
            $log->pushHandler($handler);
            $log->pushProcessor(new WebProcessor());
            $log->pushProcessor(new MemoryPeakUsageProcessor());
        }
        return self::$log;
    }

    public static function __callStatic($action, $params)
    {
        $log = self::getInstance();
        call_user_func_array([$log, $action], $params);
    }
}