<?php

namespace Library\Core;

class Response
{
    const JSON = "json";

    /**
     * 按综合方式输出通信数据
     * @param integer $code 状态码
     * @param string $message 提示信息
     * @param array $data 数据
     * @param string $type 数据类型
     * @return string
     */
    public static function show($code, $message = '', $data = array(), $type = self::JSON)
    {
        if (!is_numeric($code)) {
            return '';
        }

        $type = isset($_GET['format']) ? $_GET['format'] : self::JSON;

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );

        if ($type == 'array') {
            dump($result);
            exit;
        } elseif ($type == 'xml') {
            self::xmlEncode($code, $message, $data);
            exit;
        } else {
            self::json($code, $message, $data);
            exit;
        }
    }

    /**
     * 按json方式输出通信数据
     * @param integer $code 状态码
     * @param string $message 提示信息
     * @param array $data 数据
     * @return string
     */
    private static function json($code, $message = '', $data = array())
    {

        if (!is_numeric($code)) {
            return '';
        }

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        );

        header("Content-Type:application/json");
        $result = json_encode($result, JSON_HEX_APOS);
        $result = str_replace('null', '', $result);

        exit($result);
    }

    /**
     * 按xml方式输出通信数据
     * @param integer $code 状态码
     * @param string $message 提示信息
     * @param array $data 数据
     * @return string
     */
    private static function xmlEncode($code, $message, $data = array())
    {
        if (!is_numeric($code)) {
            return '';
        }

        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );

        header("Content-Type:text/xml");
        $xml = "<?xml version='1.0' encoding='UTF-8'?>\n";
        $xml .= "<root>\n";

        $xml .= self::xmlToEncode($result);

        $xml .= "</root>";

        $xml = str_replace('null', '', $xml);

        exit($xml);
    }

    private static function xmlToEncode($data)
    {
        $xml = $attr = "";
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $attr = " id='{$key}'";
                $key = "item";
            }
            $xml .= "<{$key}{$attr}>";
            $xml .= is_array($value) ? self::xmlToEncode($value) : $value;
            $xml .= "</{$key}>\n";
        }
        return $xml;
    }
}