<?php
$myPid = getmypid();

// Настраиваем автолоадеры
spl_autoload_register('autoload');
function autoload($class_name) {
	include 'lib/'.$class_name . '.class.php';
}
$params = require ('config.php');

$maxOperations = isset($params['importOperationsNumber']) ? $params['importOperationsNumber'] : false;
$preparedDataFilePath = isset($params['preparedDataFilePath']) ? $params['preparedDataFilePath'] : false;
$logFile = isset($params['logFile']) ? $params['logFile'] : false;
$statusFile = isset($params['statusFile']) ? $params['statusFile'] : false;

$statusFile = get_include_path().$statusFile;
$logFile = get_include_path().$logFile;
$log = '';

// Не позволяем запускать одновременно несколько копий скрипта
if (file_exists($statusFile))
{
	$pid = file_get_contents($statusFile);
	if (posix_getsid($pid))
		die('process is running');
}
file_put_contents($statusFile, $myPid);

	if(file_exists(get_include_path().$preparedDataFilePath)) {
		$data = unserialize(base64_decode(file_get_contents(get_include_path().$preparedDataFilePath)));

		$import = new Import();
		if(file_exists($logFile) && !is_writable($logFile))
			chmod($logFile, 0777);
		file_put_contents($logFile, '');
		chmod($logFile, 0777);

		while($data) {
			$newData = $import->importAnnounceListByPart($data, $maxOperations);
			$stat = $import->getStatistics();

			$parser = new Parser();
			$parser->writePreparedDataFile($newData);

			$log = '';
			foreach ($stat as $row)
			{
				$log .= $row."\n";
			}

			print($log);
			$fp = fopen ($logFile, 'r+');
			fwrite($fp, $log);
			fclose($fp);
			
			$data = $newData;
			foreach ($data as $objectId => $objectData)
			{
				if (empty($data[$objectId]))
					unset($data[$objectId]);
			}
		}
		unlink(get_include_path().$preparedDataFilePath);
		$log = time().'|'.'Total|0'."\n";
	}
	else {
		$log = time().'|'.'Total|0'."\n";
	}
	if(file_exists($logFile) && !is_writable($logFile))
		chmod($logFile, 0777);
	$hRet = file_put_contents($logFile, $log, FILE_APPEND);
	chmod($logFile, 0777);
	
	unlink(get_include_path().$statusFile);