<?php

require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$kkey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
$dataUrl = isset($params['dataUrl']) ? $params['dataUrl'] : false;
if (!$kkey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');
if (!$dataUrl)
	die('Error. "dataUrl" not found in config.php');

// import companies
$fileName = $dataUrl;
$data = file_get_contents($fileName);
if (!$data)
	die('cant get '.$fileName);
$data_arr = json_decode($data, true);
if (empty($data_arr))
	die('cant parse '.$fileName.' answer');

echo ('import started...'."\n");
$client = new xmlrpc_client($apiUrl);
$client->request_charset_encoding = 'UTF-8';
$client->return_type = 'phpvals';
$message = new xmlrpcmsg("discountClub.upload");
$p0 = new xmlrpcval($kkey, 'string');
$message->addparam($p0);
$p1 = php_xmlrpc_encode($data_arr);
$message->addparam($p1);
$resp = $client->send($message, 0, 'http11');

var_dump($resp);
if (!is_object($resp))
	echo ('fatal error. returned raw data:'.$resp->raw_data."\n");
elseif (is_object($resp) && $resp->errno)
	echo ('Error uploading data: '.$resp->errstr."\n");
else
	echo ('successfull'."\n");


?>