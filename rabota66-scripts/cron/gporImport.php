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
		
	$lastLaunchFile = 'gporImport.txt';
	$lastLaunchTime = 0;
	if (file_exists($lastLaunchFile))
		$lastLaunchTime = file_get_contents($lastLaunchFile);
	file_put_contents($lastLaunchFile, time());
	
	$import = new gporImport();
	$import->setLastLaunchTime($lastLaunchTime);
	$import->apiUrl = $apiUrl;
	$import->apiKey = $kkey;
	$import->limit = 100;
	
	$lastLaunchTime = time();

	$import->setLastId(1);
	$res = $import->importCompanies();
	while ($res)
	{
		$res = $import->importCompanies();
	}
	$import->clearLog();

	$import->setLastId(1);
	$res = $import->importVacancies();
	while ($res)
	{
		$res = $import->importVacancies();
		
	}
	$import->hideVacancies();
	$import->clearLog();
	
	$import->exportResponses();
	$import->clearLog();
	
	file_put_contents($lastLaunchFile, $lastLaunchTime);
?>