<?php
/**
 * 公共助手函数
 */


/**
 * Widget 调用函数 用于模板中加载组件
 * @param String $widget 组件名/方法名  例如 NavWidget/menu
 * @param array $activeData 附加数据
 */
function W($widget, $activeData = null)
{
    $namespace = '\Widget\\';
    list($className, $actionName) = explode('/', $widget);

    $viewDir = APPLICATION_PATH . '/app/widgets/views/' . trim($className, 'Widget');

    $className = $namespace . $className;

    $widgetObj = new $className();
    $data = $widgetObj->$actionName();

    $view = new \Yaf_View_Simple($viewDir);
    $view->assign('baseData', $data);
    $view->assign('activeData', $activeData);

    echo $view->render($actionName . '.phtml');
}


/**curl请求
 *
 * @param $url
 * @param null $data
 * @param null $header
 * @return mixed
 */
function curlRequest($url, $data = null, $header = null)
{
    //初始化
    $curl = curl_init();
    //设置url
    curl_setopt($curl, CURLOPT_URL, $url);
    //设置https
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //如果传递了数据，则使用POST请求
    if (!is_null($data)) {
        //开启post模式
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    //设置header
    /*
        $header = array(
            'apikey: 您自己的apikey',
        );
    */
    if (!is_null($header)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    }
    //结果返回成字符串  如果是0  则是直接输出
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //执行
    $output = curl_exec($curl);
    //释放资源
    curl_close($curl);
    return $output;
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filters 参数过滤方法
 * @param mixed $datas 要获取的额外数据源
 * @return mixed
 */
function input($name, $default = '', $filters = 'htmlspecialchars', $datas = null)
{
    if (strpos($name, '.')) { // 指定参数来源
        list($method, $name) = explode('.', $name, 2);
    } else { // 默认为自动判断
        $method = 'param';
    }
    switch (strtolower($method)) {
        case 'get'     :
            $input = &$_GET;
            break;
        case 'post'    :
            $input = &$_POST;
            break;
        case 'put'     :
            parse_str(file_get_contents('php://input'), $input);
            break;
        case 'json'    :
            $input = file_get_contents('php://input');
            $input = json_decode($input, true);
            break;
        case 'param'   :
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input = $_POST;
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input = $_GET;
            }
            break;
        case 'request' :
            $input = &$_REQUEST;
            break;
        case 'session' :
            $input = &$_SESSION;
            break;
        case 'cookie'  :
            $input = &$_COOKIE;
            break;
        case 'server'  :
            $input = &$_SERVER;
            break;
        case 'data'    :
            $input = &$datas;
            break;
        default:
            return NULL;
    }
    if ('' == $name) { // 获取全部变量
        $data = $input;
    } elseif (isset($input[$name])) { // 取值操作
        $data = $input[$name];
    } else { // 变量默认值
        $data = isset($default) ? $default : NULL;
    }

    //使用过滤器过滤
    if ($filters) {
        if (is_string($filters)) {
            $filters = explode(',', $filters);
        }
        foreach ($filters as $filter) {
            $data = is_array($data)
                ? array_map_recursive($filter, $data)
                : call_user_func($filter, $data);
        }
    }

    return $data;
}

//递归处理筛选过滤
function array_map_recursive($filter, $data)
{
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val)
            ? array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
    }
    return $result;
}


/** NULL 转换为 空字符串
 *
 * @param string $param
 * @return string
 */
function nullToString(string $param) :string
{
    return is_null($param) ? '' : $param;
}

/** filter null param
 *
 * @param $params
 * @return array
 */
function filterNullParams($params)
{
    $params = array_filter($params, function ($item) {
        return $item != '';
    });
    return $params;
}

/** Read configuration
 *
 * @param $path
 * @param $section
 * @return Yaf_Config_Ini
 */
function getConfig($path, $section = '')
{
    return new Yaf_Config_Ini(APPLICATION_PATH . '/conf/' . trim($path, '/\\'), $section);
}

/**文件缓存
 *
 * @param $file
 * @param $cache
 * @param int $live [缓存时间：天]
 * @return bool
 */
function set_cache($file, $cache, $live = 7)
{
    $path = APPLICATION_PATH.'/storage/cache/'.$file;
    if (empty($cache)) {
        return false;
    }
    $data = json_encode(['keep-alive'=>$live, 'cache'=>$cache]);
    return file_put_contents($path, $data);
}


/**
 * @param $file [文件名]
 * @return bool|string
 */
function get_cache($file)
{
    $path = APPLICATION_PATH.'/storage/cache/'.$file;
    if (!file_exists($path)) {
        return false;
    }

    $lastTime = filemtime($path);
    $keepAlive = (time()-$lastTime) / (3600 * 24);

    $content = json_decode(file_get_contents($path), true);
    $live = $content['keep-alive'];
    if ($keepAlive > $live) {
        unlink($path);
        return false;
    }
    return $content['cache'];
}

if (! function_exists('array_group_by')) {
    /**
     *  按照指定 key 进行分组
     * @param $array
     * @param $key
     * @return array
     */
    function array_group_by($array, $key) :array
    {
        $grouped = [];
        foreach ($array as $value) {
            $grouped[$value[$key]][] = $value;
        }
        if (func_num_args() > 2) {
            $args = func_get_args();
            foreach ($grouped as $key => $value) {
                $params = array_merge([$value], array_slice($args, 2, func_num_args()));
                $grouped[$key] = call_user_func_array('array_group_by', $params);
            }
        }
        return $grouped;
    }
}

if (! function_exists('float_number_format')) {

    /**浮点数格式化千分位
     *
     * @param $numeric
     * @return string
     */
    function float_number_format($numeric)
    {
        return number_format(doubleval($numeric), 2);
    }
}
