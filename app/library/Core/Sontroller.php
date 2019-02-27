<?php
/**
 * Created by PhpStorm.
 * User: BM
 */

namespace Library\Core;

use Yaf_Controller_Abstract;
use Yaf_Dispatcher;
use Yaf_Exception;

abstract class Sontroller extends Yaf_Controller_Abstract
{
    protected $argc;
    protected $argv;

    protected function init()
    {
        if ($this->getRequest()->isCli()) {
            Yaf_Dispatcher::getInstance()->returnResponse(true);
            Yaf_Dispatcher::getInstance()->disableView();

            $this->argc = $GLOBALS['argc'];
            $this->argv = $GLOBALS['argv'];
        } else {
            throw new Yaf_Exception("Environment is not in CLI mode.\n");
        }
    }

    //入口方法
    abstract public function mainAction();
}