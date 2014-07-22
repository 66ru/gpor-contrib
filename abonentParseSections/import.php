#!/usr/bin/php
<?php
$DR = dirname(__FILE__);
include_once ($DR.'/../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$debug = false;
if(is_file($DR."/config.php"))
	include $DR."/config.php";
else {
	echo "config.php not found";
	die;
}
if($debug) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
}
set_time_limit(0);
mb_internal_encoding("UTF-8");

$res = array();
$url = $config['url'];
$parentSections = getParentSections($url);

if ($parentSections) {
	foreach ($parentSections as $section) {
		$res[] = $section;
		$childSections = getChildSections($section['url'], $section['id']);
		if ($childSections) {
			$res = array_merge($res, $childSections);
		}
	}
}

$fn = fopen($DR . '/abonent_sections.json', 'w');
fwrite($fn, json_encode($res));
fclose($fn);

echo 'done'."\n";

//sendData('afisha.postPlace', $pre);
//sendData('afisha.postSeances',array_slice($seanceStack,$i,250));


/**
 * Получаем список корневых рубрик
 *
 * @param unknown_type $url            -    URL дня парсинга
 * @return unknown
 */
function getParentSections($url)
{
	$data_Page = loadUtfData($url);
	$res = array();

	// Получаем ссылки на фильмы и название фильмов
	preg_match_all('#<div><h3><a href="(.+?)">(.+?)</a></h3>#sim', $data_Page, $found);
	if ($found[1] && $found[2]) {
		$i = 0;
		foreach ($found[1] as $key => $value) {
			if (strlen($value) > 100 || !strstr($value, '/ekaterinburg/fgroup/')) {
				continue;
			}
			$res[] = array(
				'id' => (int)str_replace('/ekaterinburg/fgroup/', '', $value),
				'label' => $found[2][$key],
				'url' => 'http://www.2048080.ru'.$value,
				'parentId' => 0,
			);
			$i++;
		}
	}
	return $res;
}

function getChildSections($url, $parentId)
{
	$data_Page = loadUtfData($url);
	$res = array();

	// Получаем ссылки на фильмы и название фильмов
	preg_match_all('#<div class="body">\s+?<h3>Подгруппы<h3>\s+?<ul>\s+(.+?)</ul>#sim', $data_Page, $found);
	if ($found[1]) {
		$i = 0;
		foreach ($found[1] as $key => $value) {
			preg_match_all('#<li><a href="(.+?)">(.+?)</a>#sim', $value, $found2);

			if ($found2[1] && $found2[2]) {
				$i = 0;
				foreach ($found2[1] as $key2 => $value2) {
					if (strlen($value2) > 100 || !strstr($value2, '/ekaterinburg/fgroup/')) {
						continue;
					}
					$res[] = array(
						'id' => (int)str_replace('/ekaterinburg/fgroup/', '', $value2),
						'label' => $found2[2][$key2],
						'url' => 'http://www.2048080.ru'.$value2,
						'parentId' => $parentId,
					);
					$i++;
				}
			}
		}
	}
	return $res;
}


/****************************************************/

function normalText($s)
{
	$s = str_replace("\n", "", str_replace("\r", "", str_replace("\t", "", trim($s))));
	$s = preg_Replace('#[ ]+#', ' ', $s);
	$s = str_replace(' , ', ', ', $s);
	return $s;
}

function loadUtfData($url)
{
	$page = @file_get_contents($url);

	if (strlen($page) < 300) $page = '';

	return $page;
}

function sendData($name, $params = array())
{
	global $apiKey, $apiUrl;
	$client                           = new xmlrpc_client($apiUrl);
	$client->request_charset_encoding = 'UTF-8';
	$client->return_type              = 'phpvals';
	$client->debug                    = 0;
	$msg                              = new xmlrpcmsg($name);
	$p1                               = new xmlrpcval($apiKey, 'string');
	$msg->addparam($p1);

	if ($params) {
		$p2 = php_xmlrpc_encode($params);
		$msg->addparam($p2);
	}
	$client->accepted_compression = 'deflate';
	$res                          = $client->send($msg, 60 * 5, 'http11');
	if ($res->faultcode()) {
		print "An error occurred: ";
		print " Code: " . htmlspecialchars($res->faultCode());
		print " Reason: '" . htmlspecialchars($res->faultString()) . "' \n";
		die;
	} else
		return $res->val;
}

function matchName($a, $b)
{
	$a = mb_strtolower($a);
	$b = mb_strtolower($b);
	$a = preg_replace('|[^\p{L}\p{Nd}]|u', '', $a);
	$b = preg_replace('|[^\p{L}\p{Nd}]|u', '', $b);
	if ($a == $b) return true;
	return false;
}

?>