<?php

include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = include_once('config.php');
if (!$params)
	die('Error: config.php not found.');

//TODO: Не забыть убрать
$kkey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
if (!$kkey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');

//Получение счетчиков с доски по недвижимости жилых поещений и их парсинг
$realty_live_counters_data = @file_get_contents('http://66.ru/ajax/realty_live_counters.json');
if ($realty_live_counters_data===false){
	echo("cant get http://66.ru/ajax/realty_live_counters.json \n");
	$realty_live_counters_data = "";
}
else{
	$realty_live_counters_arr = @json_decode($realty_live_counters_data, true);
	if (!is_array($realty_live_counters_arr))
		echo("cant parse http://66.ru/ajax/realty_live_counters.json \n");
	$realty_live_counters_arr = null;
}

//Получение объявлений с доски по недвижимости и их парсинг
$realty_live_msg_data = @file_get_contents('http://66.ru/ajax/latest50_live.json');
if ($realty_live_msg_data===false){
	echo("cant get http://66.ru/ajax/latest50_live.json \n");
	$realty_live_msg_data = "";
}
else{
$realty_live_msg_arr = @json_decode($realty_live_msg_data, true);
	if (!is_array($realty_live_msg_arr))
		echo("cant parse http://66.ru/ajax/latest50_live.json \n");
	$realty_live_msg_arr = null;
}

//Получение счетчиков с доски по недвижимости нежилых помещений и их парсинг
$realty_com_counters_data = @file_get_contents('http://66.ru/ajax/realty_com_counters.json');
if ($realty_com_counters_data===false){
	echo("cant get http://66.ru/ajax/realty_com_counters.json \n");
	$realty_com_counters_data = "";
}
else{
	$realty_com_counters_arr = @json_decode($realty_com_counters_data, true);
	if (!is_array($realty_com_counters_arr))
		echo("cant parse http://66.ru/ajax/realty_com_counters.json \n");
	$realty_com_counters_arr = null;
}
//Отправка данных
$client = new xmlrpc_client($apiUrl);
$client->request_charset_encoding = 'UTF-8';
$message = new xmlrpcmsg("realty.doska", array(
		new xmlrpcval($kkey, 'string'), 
		new xmlrpcval($realty_live_counters_data, 'string'),
		new xmlrpcval($realty_live_msg_data, 'string'),
		new xmlrpcval($realty_com_counters_data, 'string')
));
$hRet = $client->send($message, 0, 'http11');
if(!$hRet->faultCode()){
	//удача
}
else
{
	print "An error occurred: ";
	print " Code: ".htmlspecialchars($hRet->faultCode());
	print " Reason: '".htmlspecialchars($hRet->faultString())."' \n";
}

?>