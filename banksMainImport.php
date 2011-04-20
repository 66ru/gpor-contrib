<?php
#ini_set('display_errors',1);
#error_reporting(E_ALL);
$filePath = '';
$params = array();
$params = include('config.php');
$host = isset($params['outerHostName']) && $params['outerHostName'] ? $params['outerHostName'] : false;
if (!$host)
{
	echo 'Error. outerHostName not found in config.php.';
	die();
}

//header('Content-type: text/html; charset=utf-8');
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $host."/bank/");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_ENCODING, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER , 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding: gzip,deflate'));
$content = curl_exec($ch);
curl_close($ch);

$content = str_replace('href="/','href="http://'.$host.'/',$content);
$content = str_replace('href=\'/','href=\'http://'.$host.'/',$content);
$content = preg_replace('#href="([^h])#','href="http://'.$host.'/$1',$content);
$content = preg_replace('#href=\'([^h])#','href="http://'.$host.'/$1',$content);

$fn = fopen($filePath.'index.html','w');
fwrite($fn, $content);
fclose($fn);
?>