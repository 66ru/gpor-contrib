<?php
define ('DS', '/');
require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
require "weatherImportFunction.php";
$params = require('config.php');

$kkey = $params['weatherStatusImportKey']; // API KEY

$apiUrl = $params['apiUrl'];

$statuses = $codes_descr;
$n = count ($statuses);
$i = 0;
foreach ($clouds_descr as $descr)
{
	$statuses[$n+$i] = $descr;
	$i++;
}

foreach ($statuses as $k=>$descr)
{
	$client = new xmlrpc_client($apiUrl);
	$client->return_type = 'phpvals';
	$message = new xmlrpcmsg("weather.setWeatherStatus");
	$p0 = new xmlrpcval($kkey, 'string');
	$message->addparam($p0);

	$p1 = array('code' => $k, 'description' => base64_encode($descr));
	$p1 = php_xmlrpc_encode($p1);
	$message->addparam($p1);

	$resp = $client->send($message, 0, 'http11');
}
?>