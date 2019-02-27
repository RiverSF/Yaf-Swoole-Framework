<?php
namespace Library\Core;

use Yaf_Dispatcher;
use Yaf_Controller_Abstract;

class Controller extends Yaf_Controller_Abstract
{
    public function init()
    {
        Yaf_Dispatcher::getInstance()->autoRender(false);
    }

    protected function assign($key, $val)
    {
        $this->getView()->assign($key, $val);
    }

    protected function json($code, $message = '', $data = [])
    {
        ob_end_clean();

        $data = array_map_recursive('nullToString', $data);
        Response::show($code, $message, $data);
    }
}