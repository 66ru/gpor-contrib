<?php 
	include_once( 'config.php' );
	include_once( ROOT.'/config/common.conf.php' );
	include_once( ROOT.'/config/db.conf.php' );

	include_once( ROOT.'/lib/gporImport.class.php' );

	$import = new gporImport();
	$import->apiUrl = 'http://api.dev.gpor.ru';
	$import->apiKey = '7af68d1dc9af32aec89880636ff4d673';
	$import->limit = 1;
	$import->setLastId(82787);

	while ($import->importCompanies())
	{
		
	}
	print_r($import->getLog());

//	while ($import->importVacancies())
//	{
//		
//	}

//	$responses = $import->getNewResponses();

//	$resumes = $import->getNewResumes();
?>