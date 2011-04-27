<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Онтон
 * Date: 27.04.11
 * Time: 11:43
 * To change this template use File | Settings | File Templates.
 */
 
class XMLRPCHelper {
    public static $method = 'http11';

    protected static function getApiConfig($key = null) {
        $config = (array)Yii::app()->params['api'];

        foreach(array('host', 'port', 'path', 'key') as $_key) {
            if(!isset($config[$_key]))
                $config[$_key] = null;
        }

        if($key !== null) {
            $key = strval($key);

            if(!isset($config[$key]))
                return null;

            return $config[$key];
        }

        return $config;
    }

    protected static function createMessage($message) {
        $message = new xmlrpcmsg($message);
        $p0 = new xmlrpcval(self::getApiConfig('key'), 'string');
        $message->addparam($p0);

        foreach(array_slice(func_get_args(), 1) as $arg) {
            $arg = php_xmlrpc_encode($arg);
            $message->addparam($arg);
        }

        return $message;
    }

    public static function sendMessage() {
        $c = self::getApiConfig();

        $client = new xmlrpc_client($c['path'], $c['host'], $c['port']);
        $client->return_type = 'xmlrpcvals';
        $client->accepted_compression = 'deflate';
        $client->method = self::$method;

        $args = func_get_args();
        $message = call_user_func_array(array(__CLASS__, 'createMessage'), $args);
        $res = $client->send($message);

        if ($res->faultcode())
			throw new CException(get_class($res) . ': ' . $res->faultString());
        else
			return php_xmlrpc_decode($res->value());
    }
}
