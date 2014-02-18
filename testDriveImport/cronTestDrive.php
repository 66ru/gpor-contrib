<?php 
/*
скрипт, который берет с гпора последние 10 тест-драйвов и записывает их в виде json на гпор в текстовые файлы.
Используется для КБ с тест-драйвом. Пример есть в этой же папке в файле .html
*/
$newsStatRootDir = dirname(__FILE__);
$config = require($newsStatRootDir.'/config.php');

include_once ($newsStatRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
include_once ($newsStatRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpcs.inc');
include_once ($newsStatRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpc_wrappers.inc');
require ($newsStatRootDir.'/lib/NewsStatXmlRpc.php');

foreach ($config as $k=>$v)
{
	if (empty($v))
		die ("empty key ".$k);
}

$dataDir = $config['jsonPath'];
if (!file_exists($dataDir))
{
	if (!mkdir($dataDir, 0777))
	{
		die('Can\'t create folder '.$dataDir);
	}
}

// берем последние тест-драйвы
$fileName = $dataDir.'latestTestDrives.json';
$stat = getTestDrives ($fileName, $config);
if ($stat) {
	writeTestDrivesToFile ($fileName, $stat);
	uploadTestDrivesToGpor ($config, 'latestTestDrives.json');
}


/*
 * Вспомогательные ф-ции
 */

function writeTestDrivesToFile ($fileName, $data)
{
	$resultFile = $fileName;
	file_put_contents($resultFile, json_encode($data));
		
	return true;
}


function uploadTestDrivesToGpor ($config, $fileName) {
	$method = 'admin.setCustomFile';

	$p2 = array();
	$p2['name'] = '/json/news/test_drive/latest.json'; // имя файла. Если файла не существует - он создается, иначе - изменяется.
	$p2['contentType'] = 'text/json'; // Тип содержимого
	$p2['dataFile'] = $config['jsonUrl'].$fileName; // ссылка на файл

	$_xmlRpc = new NewsStatXmlRpc($config['apiUrl'], $config['apiKey'], $method);
	return $_xmlRpc->send(array($p2));
}


function getTestDrives ($fileName, $config)
{
	$result = array();
	$method = 'news.listNews';

	$modelName = 'NewsAuto';
	$p1 = $modelName;

	$p2 = array();
	$p2[] = array('type' => 'number', 'value' => '1', 'field' => 'testDrive');

	$p3 = array('title', 'postTime', 'annotation', 'commentsCount', 'titlelink', 'fulltitlelink', 'link', 'wideimageurl', 'imageurl', 'id');

	class cls{};
	$p4 = new cls();
	$p4->limit = 10;
//	$p4 = array('imit' => 10);

	$params = array($p1, $p2, $p3, $p4);

	$newsStatXmlRpc = new NewsStatXmlRpc($config['apiUrl'], $config['apiKey'], $method);
	$result = $newsStatXmlRpc->send($params);

//	var_dump($result);

	return $result;
}


?>