<?php

require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

//TODO: Не забыть убрать
$kkey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
if (!$kkey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');

//Получение счетчиков с доски по недвижимости и их парсинг
$realty_live_counters_data = @file_get_contents('http://66.ru/ajax/realty_live_counters.json');
if ($realty_live_counters_data===false)
	die('cant get http://66.ru/ajax/realty_live_counters.json');
$realty_live_counters_arr = @json_decode($realty_live_counters_data, true);
if (!is_array($realty_live_counters_arr))
	die('cant parse http://66.ru/ajax/realty_live_counters.json');

//Получение объявлений с доски по недвижимости и их парсинг
$realty_live_msg_data = @file_get_contents('http://66.ru/ajax/latest50_live.json');
if ($realty_live_msg_data===false)
	die('cant get http://66.ru/ajax/latest50_live.json');
$realty_live_msg_arr = @json_decode($realty_live_msg_data, true);
if (!is_array($realty_live_msg_arr))
	die('cant parse http://66.ru/ajax/latest50_live.json');

//Отправка данных
$client = new xmlrpc_client($apiUrl);
$client->request_charset_encoding = 'UTF-8';
$message = new xmlrpcmsg("realty.doska", array(
		new xmlrpcval($kkey, 'string'), 
		new xmlrpcval($realty_live_counters_data, 'string'),
		new xmlrpcval($realty_live_msg_data, 'string')
));
$hRet = $client->send($message, 0, 'http11');
if(!$hRet->faultCode()){
	//удача
}
else
{
	print "An error occurred: ";
	print " Code: ".htmlspecialchars($hRet->faultCode());
	print " Reason: '".htmlspecialchars($hRet->faultString())."'\n";
}

?>
