<?php

require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$kkey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
if (!$kkey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');

$data = file_get_contents('http://66.ru/realty_data.php');
if (!$data)
	die('cant get http://66.ru/realty_data.php');
$data_arr = json_decode($data, true);
if (empty($data_arr))
	die('cant parse http://66.ru/realty_data.php answer');

$client = new xmlrpc_client($apiUrl);
$client->request_charset_encoding = 'UTF-8';
//$client->debug = 2;
$client->return_type = 'phpvals';
$message = new xmlrpcmsg("realty.upload");
$p0 = new xmlrpcval($kkey, 'string');
$message->addparam($p0);
$p1 = php_xmlrpc_encode($data_arr);
$message->addparam($p1);
$resp = $client->send($message, 0, 'http11');

//var_dump($resp);
if (!is_object($resp))
	die('fatal error. returned raw data:'.$resp->raw_data);
if (is_object($resp) && $resp->errno)
	die('Error uploading data: '.$resp->errstr);

