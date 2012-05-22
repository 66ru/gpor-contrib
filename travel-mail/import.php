<?php
//Инициализуруем файл с логами
$logFile = @fopen('log.txt','w');
if(!$logFile)
    die("Can't open log file: log.txt");


//Берем на вход текстовый файл, где каждая строка или пустая или содержит одну ссылку на travel.mail.ru
$blockText = array();
if(!empty($_SERVER['argv'][1]))
{
	if (is_file($_SERVER['argv'][1]) && $handle = fopen($_SERVER['argv'][1],'r'))
	{
        //Берем ссылку, получаем из неё заголовок статьи
		while($line = fgets($handle)){
			$line = trim($line);
			if(!$line)
				continue;

			$html = @file_get_contents($line);
			if(!$html)
				continue;
			$html = prepareForDOM($html, 'windows-1251');

			$dom = new DOMDocument();
			@$dom->loadHTML($html);

			$xml = simplexml_import_dom($dom);
			$links = $xml->xpath('/html/body/center/table[@id="map_table2"]/tr/td[@id="map_td2"]/table[@id="map_table2"]/tr/td[@id="map_td2"]/table/tr/td/h2');
			$text = trim($links[0]);
			if($text)
                $blockText[$line] = $text;
		}
	}
	else
	{
		echo "Unable to open file to parse\r\n";
	}
}
else
{
	echo "Usage: php import.php filename\r\n";
}

if(empty($blockText))
    exit;

//Берем с API gpor список контент-блоков с именами links*
require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$kkey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
if (!$kkey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');


$client = new xmlrpc_client($apiUrl);
$client->request_charset_encoding = 'UTF-8';
$client->return_type = 'phpvals';
$message = new xmlrpcmsg("contextblock.list");
$p0 = new xmlrpcval($kkey, 'string');
$message->addparam($p0);
$p1 = new xmlrpcval('links*', 'string');
$message->addparam($p1);
$resp = $client->send($message, 0, 'http11');

if (!is_object($resp))
	die('fatal error. returned raw data:'.$resp->raw_data);
if (is_object($resp) && $resp->errno)
	die('Error uploading data: '.$resp->errstr);

$blocks = $resp->val;

//Складываем в контент-блок по одной ссылке
foreach($blockText as $link => $text)
{
    $block = array_shift($blocks);
    if(!$block)
        exit;

    $client = new xmlrpc_client($apiUrl);
    $client->request_charset_encoding = 'UTF-8';
    $client->return_type = 'phpvals';
    $message = new xmlrpcmsg("contextblock.update");
    $p0 = new xmlrpcval($kkey, 'string');
    $message->addparam($p0);
    $p1 = new xmlrpcval($block, 'string');
    $message->addparam($p1);
    $p2 = new xmlrpcval(base64_encode('<div class="content-block" style="padding-top: 20px; padding-left: 20px;"><span style="color: #666666; font-size: 0.8em;">Путешествия@mail.ru:</span><p><a href="'.$link.'"_blank">'.$text.'</a></p></div>'), 'string');
    $message->addparam($p2);
    $p3 = new xmlrpcval('base64', 'string');
    $message->addparam($p3);
    $resp = $client->send($message, 0, 'http11');

    if (!is_object($resp))
        die('fatal error. returned raw data:'.$resp->raw_data);
    if (is_object($resp) && $resp->errno)
        die('Error uploading data: '.$resp->errstr);
    //Пишем в лог информацию, какую ссылку в какой КБ положили
    fwrite($logFile, "$link   $block\r\n");
}
fclose($logFile);



/**
 * Функция перевода контента из CP-1251 в UTF-8
 * @param $html
 * @param $encoding
 * @return mixed|string
 */
function prepareForDOM($html, $encoding) {
	$html = iconv($encoding, 'UTF-8//TRANSLIT', $html);
	$html = preg_replace('/<(script|style|noscript)\b[^>]*>.*?<\/\1\b[^>]*>/is', '', $html);
	$html = preg_replace('#<meta[^>]+>#isu', '', $html);
	$html = preg_replace('#<head\b[^>]*>#isu', "<head>\r\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />", $html);
	return $html;
};
?>