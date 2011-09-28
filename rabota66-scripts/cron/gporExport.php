<?php 
	include_once( 'config.php' );
	include_once( ROOT.'/config/common.conf.php' );
	include_once( ROOT.'/config/db.conf.php' );
	include_once( ROOT.'/lib/gporImport.class.php' );
	
	$xmlRpcConfig = include( ROOT.'/config/xmlrpc.conf.php' );

	$kkey = isset($xmlRpcConfig['apiKey']) ? $xmlRpcConfig['apiKey'] : false;
	$apiUrl = isset($xmlRpcConfig['apiUrl']) ? $xmlRpcConfig['apiUrl'] : false;
	if (!$kkey)
		die('Error. "apiKey" not found in config.php');
	if (!$apiUrl)
		die('Error. "apiUrl" not found in config.php');
		
	$lastLaunchFile = ROOT.'/cron/gporExport.txt';
	$lastLaunchTime = 0;
	if (file_exists($lastLaunchFile))
		$lastLaunchTime = file_get_contents($lastLaunchFile);

	$lockFile = ROOT.'/cron/gporExport.lock';
	if (file_exists($lockFile))
		return;
	file_put_contents($lockFile, $lastLaunchTime);
	
	$import = new gporImport();
	$import->setLastLaunchTime($lastLaunchTime);
	$import->apiUrl = $apiUrl;
	$import->apiKey = $kkey;

	$import->exportResponses();
	$import->clearLog();

	file_put_contents($lastLaunchFile, $lastLaunchTime);
	unlink ($lockFile);

?>