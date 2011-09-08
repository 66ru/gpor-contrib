<?php 
	include_once( 'config.php' );
	include_once( ROOT.'/config/common.conf.php' );
	include_once( ROOT.'/config/db.conf.php' );
	include_once( ROOT.'/config/xmlrpc.conf.php' );
	
	$xmlRpcConfig = include_once( ROOT.'/lib/gporImport.class.php' );

	$kkey = isset($xmlRpcConfig['apiKey']) ? $xmlRpcConfig['apiKey'] : false;
	$apiUrl = isset($xmlRpcConfig['apiUrl']) ? $xmlRpcConfig['apiUrl'] : false;
	if (!$kkey)
		die('Error. "apiKey" not found in config.php');
	if (!$apiUrl)
		die('Error. "apiUrl" not found in config.php');
	
	$import = new gporImport();
	$import->apiUrl = $apiUrl;
	$import->apiKey = $kkey;
	$import->limit = 100;

	$import->setLastId(1);
	while ($import->importCompanies())
	{
		
	}
	$import->clearLog();

	$import->setLastId(1);
	while ($import->importVacaanies())
	{
		
	}
	$import->clearLog();
	
	$import->exportResponses();
	$import->clearLog();
?>