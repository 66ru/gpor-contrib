<?php

require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$apiKey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
$importUrl = isset($params['importUrl']) ? $params['importUrl'] : false;
$redirectPrefix = isset($params['redirectPrefix']) ? $params['redirectPrefix'] : false;

if (!$apiKey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');
if (!$importUrl)
	die('Error. "importUrl" not found in config.php');
if (!$redirectPrefix)
	die('Error. "redirectPrefix" not found in config.php');

if (!function_exists('simplexml_load_file'))
	die('I need php with --enable-libxml option installed');

$data = simplexml_load_file($importUrl);

if (!$data)
	die('cant process '.$importUrl);

$templateTop = <<<HTML
<style type="text/css">
		#payed_autocenters {
			margin-right: -240px;
			overflow: visible;
			position: relative;
			width: auto;
			z-index: 100;

			text-align: justify;
			background: #eaf2da;
			height: 50px;
			padding: 9px 0 9px 9px;
			font-size: 9px;
			color: #6a6a6a;

			margin-bottom: 10px;
		}
		#payed_autocenters span {
			overflow: hidden;
			display: inline-block !important;
			display: inline;
			zoom:1;
			margin-right: 2px;
			width:90px;
			text-align: center;
			vertical-align: top;
		}
		#payed_autocenters li {
			display: inline-block !important;
			display: inline;
			zoom:1;
			margin-right: 2px;
			width:90px;
			text-align: center;
		}
		#payed_autocenters a {
			color: #6a6a6a;
			text-decoration: none;
		}

		#auto_content {
			margin-right: 240px;
			position: relative;
		}
</style>

<div id="payed_autocenters">
HTML;
$itemTemplate = <<<HTML
<span>
<a target="_blank" href="%s">
	<img src="http://t.66.ru/auto/pixel.gif" style="background: url('%s') center 0px no-repeat; width:53px; height:30px" alt="%s">
</a>
<br><a target="_blank" href="%s">%s</a></span>&nbsp;
HTML;
$templateBottom = <<<HTML
<span style="width:100%;font-size:1px;"></span>
</div>
HTML;

$items = '';
foreach($data->item as $item) {
	$items.= sprintf($itemTemplate, $redirectPrefix.$item->link, $item->image, $item->title, $redirectPrefix.$item->link, $item->title);
}

$client = new xmlrpc_client($apiUrl);
$client->return_type = 'phpvals';
$message = new xmlrpcmsg("contextblock.update");
$p0 = new xmlrpcval($apiKey, 'string');
$message->addparam($p0);
$p1 = new xmlrpcval('auto_content_top_block', 'string');
$message->addparam($p1);
$p2 = new xmlrpcval($templateTop.$items.$templateBottom, 'string');
$message->addparam($p2);
$resp = $client->send($message, 0, 'http11');
if (!is_object($resp))
	die('fatal error. returned raw data:'.$resp->raw_data);
if (is_object($resp) && $resp->errno)
	die('Error setting cb: '.$resp->errstr);

