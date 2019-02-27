<?php
/**
 * RPC 服务端方法
 *
 * Created by PhpStorm.
 * User: River
 * Date: 2019/2/27
 * Time: 10:25
 */
namespace Model\RPC;

class YarClient
{
    /** Yar Client：调用远程 Yar_Server 开放的服务方法
     *
     * @param $params
     * @return mixed
     */
    public function demo($params)
    {
        $client = new \Yar_Client('Yar_Server_Url');
        $client->SetOpt(YAR_OPT_TIMEOUT, 30000);
        $result = $client->demo($params);
        return $result;
    }
}