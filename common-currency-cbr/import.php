<?php
define ('DS', '/');
include ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$apiKey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;

if (!$apiKey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');

$xml_content = @file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp');
if (empty($xml_content)) {
	print 'Error. cbr.ru returned empty answer';
	exit();
}

preg_match('/<CharCode>USD<\/CharCode>.*?<Value>(.*?)<\/Value>/is', $xml_content, $matches1);
preg_match('/<CharCode>EUR<\/CharCode>.*?<Value>(.*?)<\/Value>/is', $xml_content, $matches2);
preg_match('#<ValCurs Date="([0-9\.]+)#is', $xml_content, $matches3);
$course_usd = $matches1[1];
$course_eur = $matches2[1];
$date = $matches3[1];
$date = strtotime ($date);

if (empty($course_usd) || empty($course_eur)) {
	print 'Error. USD or EUR course is empty';
	exit();
}

$client = new xmlrpc_client($apiUrl);
$client->return_type = 'phpvals';

$message = new xmlrpcmsg("currency.setCurrency");

$p0 = new xmlrpcval($apiKey, 'string');
$message->addparam($p0);

$p2 = array(
	'course_usd' => str_replace(',', '.', $course_usd),
	'course_eur' => str_replace(',', '.', $course_eur),
	'date' => date('Y-m-d', $date),
);

$p2 = php_xmlrpc_encode($p2);
$message->addparam($p2);

$resp = $client->send($message, 0, 'http11');
if (is_object($resp) && !$resp->errno) {
//	print "New currency:\n";
//	print 'USD: ' . $course_usd . "\n";
//	print 'EUR: ' . $course_eur . "\n";
}
else {
	print 'Error. Failed to set currency: ' . (is_object($resp) ? $resp->errstr : '');
}
?>