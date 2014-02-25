<?php
error_reporting(0);
/**
 * Скрпит берет feed аптеки Живика и формирует новый фид со ссылками на товары
 */
$rootDir = dirname(__FILE__);
$config = require_once($rootDir.'/config.php');

require_once($rootDir.'/../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
require_once($rootDir.'/../_lib/xmlrpc-3.0.0.beta/xmlrpcs.inc');
require_once($rootDir.'/../_lib/xmlrpc-3.0.0.beta/xmlrpc_wrappers.inc');
require_once($rootDir.'/lib/FeedXmlRpc.php');

foreach ($config as $k=>$v)
{
	if (empty($v))
		die ("empty key ".$k);
}

$dataDir = $config['feedPath'];

$fileName = $dataDir.'latestZhivikaFeed.xml';
if (!file_exists($dataDir)) {
	if (!mkdir($dataDir, 0777)) {
		die('Can\'t create folder '.$dataDir);
	}
}

$xml = simplexml_load_file($config['originalFeedUrl']);

if(!$xml) {
	die('File empty or not xml');
}

$srcXML = new SimpleXMLElement($xml->asXML());

foreach ($srcXML->Position as $line) {
	/** @var $line SimpleXMLElement */
	$prepId = (string) $line['IdPrep'];
	$line->addAttribute('link', 'http://www.zhivika.ru/plugins/catalog/item/cid/0/item/'.$prepId);
}

$srcXML->saveXML($fileName);

uploadZhivikaLinkFeed($config, 'latestZhivikaFeed.xml');


function uploadZhivikaLinkFeed($config, $fileName) {
	$method = 'admin.setCustomFile';

	$p2 = array();
	$p2['name'] = $config['feedTargetUrl']; // имя файла. Если файла не существует - он создается, иначе - изменяется.
	$p2['contentType'] = 'text/xml'; // Тип содержимого
	$p2['dataFile'] = $config['feedUrl'].$fileName; // ссылка на файл

	$_xmlRpc = new FeedXmlRpc($config['apiUrl'], $config['apiKey'], $method);
	if (!$_xmlRpc->send(array($p2))) {
		echo $_xmlRpc->getLastError();
	}
}
