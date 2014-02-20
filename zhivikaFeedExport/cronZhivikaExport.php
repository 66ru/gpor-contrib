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
$newXML = new domDocument('1.0', "windows-1251");
$newXML->formatOutput = true;

$preps = $newXML->createElement('preps');
$newXML->appendChild($preps);

foreach ($srcXML->Position as $line) {
	$aptId = (string) $line['IdApt'];
	$prepId = (string) $line['IdPrep'];
	$name = (string) $line;
	$prep = $newXML->createElement('prep', $name);
	$prep->setAttribute('IdApt', $aptId);
	$prep->setAttribute('IdPrep', $prepId);
	$prep->setAttribute('link', 'http://www.zhivika.ru/plugins/catalog/item/cid/0/item/'.$prepId);
	$preps->appendChild($prep);
}

$newXML->save($fileName);

uploadZhivikaLinkFeed($config, 'latestZhivikaFeed.xml');


function uploadZhivikaLinkFeed($config, $fileName) {
	$method = 'admin.setCustomFile';

	$p2 = array();
	$p2['name'] = '/feed/latest.xml'; // имя файла. Если файла не существует - он создается, иначе - изменяется.
	$p2['contentType'] = 'text/xml'; // Тип содержимого
	$p2['dataFile'] = $config['feedUrl'].$fileName; // ссылка на файл

	$_xmlRpc = new FeedXmlRpc($config['apiUrl'], $config['apiKey'], $method);
	if (!$_xmlRpc->send(array($p2))) {
		echo $_xmlRpc->getLastError();
	}
}
