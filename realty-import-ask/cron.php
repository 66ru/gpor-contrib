<?php
// Настраиваем автолоадеры
spl_autoload_register('autoload');
function autoload($class_name) {
	include 'lib/'.$class_name . '.class.php';
}
$params = require ('config.php');

$maxOperations = isset($params['importOperationsNumber']) ? $params['importOperationsNumber'] : false;
$preparedDataFilePath = isset($params['preparedDataFilePath']) ? $params['preparedDataFilePath'] : false;
$logFile = isset($params['logFile']) ? $params['logFile'] : false;
$log = '';

if(file_exists(get_include_path().$preparedDataFilePath)) {
	$data = unserialize(base64_decode(file_get_contents(get_include_path().$preparedDataFilePath)));

	$import = new Import();
	
	if($data) {
		$newData = $import->importAnnounceListByPart($data, $maxOperations);
		$stat = $import->getStatistics();

		$parser = new Parser();
		$parser->writePreparedDataFile($newData);

		$log = '';
		foreach ($stat as $row)
		{
			$log .= $row."\n";
		}
	}
}
else {
	$log = time().'|'.'Total|0'."\n";
}
$hRet = file_put_contents(get_include_path().$logFile, $log, FILE_APPEND);
chmod(get_include_path().$logFile, 0666);