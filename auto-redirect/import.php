<?php
/*
скрипт, который ставит редиректы с каталога авто на доску объявлений
*/
require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$apiKey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
$importUrl = isset($params['importUrl']) ? $params['importUrl'] : false;

if (!$apiKey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');
if (!$importUrl)
	die('Error. "importUrl" not found in config.php');

$c = file_get_contents($importUrl);
if (!$c) {
	die('Error. Can\'t read '.$importUrl);
}
$data = @json_decode($c);
if (!$data) {
	die('Error. Error parsing data from '.$importUrl);
}

$client = new xmlrpc_client($apiUrl);
$client->return_type = 'phpvals';

$marks = array();

foreach ($data->marks as $mark) {
	$_data = array(
		'oldUrl' => $params['siteHost'] . '/auto/catalog/' . $mark->link . '/',
		'newUrl' => $params['siteHost'] . '/auto/doska/' . strtolower(str_replace(array('_', ' '), array('-', '-'), $mark->link)) . '/',
		'ttl' => 365,
	);
	$marks[(int)$mark->id] = $mark;

	echo $mark->name . ' ' . $_data['newUrl'] . "\n";

	$message = new xmlrpcmsg("admin.setOldUrlMapping");
	$p0 = new xmlrpcval($apiKey, 'string');
	$message->addparam($p0);
	$p1 = php_xmlrpc_encode($_data);
	$message->addparam($p1);
	$resp = $client->send($message, 0, 'http11');
	if (!is_object($resp))
		die('fatal error. returned raw data:'.$resp->raw_data);
	if (is_object($resp) && $resp->errno)
		die('Error sending request: '.$resp->errstr);
}


foreach ($data->models as $model) {
	$link = $marks[(int)$model->mark_id]->link . '/' . $model->link;
	$newLink = strtolower(str_replace(array('_', ' '), array('-', '-'), $marks[(int)$model->mark_id]->link)) . '/' . strtolower(str_replace(array('_', ' '), array('-', '-'), $model->link));
	$_data = array(
		'oldUrl' => $params['siteHost'] . '/auto/catalog/' . $link . '/',
		'newUrl' => $params['siteHost'] . '/auto/doska/' . $newLink . '/',
		'ttl' => 365,
	);

	echo $marks[(int)$model->mark_id]->link .': ' .$model->name . ' ' . $_data['newUrl'] . "\n";

	$message = new xmlrpcmsg("admin.setOldUrlMapping");
	$p0 = new xmlrpcval($apiKey, 'string');
	$message->addparam($p0);
	$p1 = php_xmlrpc_encode($_data);
	$message->addparam($p1);
	$resp = $client->send($message, 0, 'http11');
	if (!is_object($resp))
		die('fatal error. returned raw data:'.$resp->raw_data);
	if (is_object($resp) && $resp->errno)
		die('Error sending request: '.$resp->errstr);
}

