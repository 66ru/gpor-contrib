<?php
#ini_set('display_errors',1);
#error_reporting(E_ALL);

$params = array(
    'host' => '66.ru',
    'filePath' => '', // путь где должен храниться сгенерированный index.html
);

$host = isset($params['host']) ? $params['host'] : false;
$filePath = isset($params['filePath']) ? $params['filePath'] : '';

if (!$host)
	die('Error. "host" not found in config.php');

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
$content = str_replace('<li class="head_menu_item head_menu_item-begin rc3">', '<li class="head_menu_item head_menu_item-begin rc3" style="display: none;">', $content);
$content = preg_replace('#href="([^h])#','href="http://'.$host.'/$1',$content);
$content = preg_replace('#href=\'([^h])#','href="http://'.$host.'/$1',$content);
$content = preg_replace('/action="([^"]+)"/i','action="http://'.$host.'$1"',$content);
$content = preg_replace('/GporAuth\.run \(function\(token\).*\s*.*\s*.*\);/', '', $content);

$fn = fopen($filePath.'index.html','w');
fwrite($fn, $content);
fclose($fn);
?>