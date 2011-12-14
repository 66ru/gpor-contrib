<?php
$params = require ('./config.php');
$logFile = isset($params['logFile']) ? $params['logFile'] : false;

if(file_exists($logFile))
{
	$log = file_get_contents($logFile);
	//unlink($logFile);
	$stat = explode("\n", $log);
	$resp = array();
	foreach ($stat as $row)
	{
		if(!empty($row)) {
			$item = explode("|", $row);
			if($item[1] == "Total")
			{
				$resp['total'] = $item[2];
				continue; 
			}
			if(empty($resp[$item[1]])) {
				$resp[$item[1]] = array('error' => 0, 'edited' => 0, 'added' => 0, 'deleted' => 0);
			}
			switch ($item[2]) {
				case 'Success':
					$status = explode(" ", $item[3]);
					
					if ($status[0] == "Added") {
						$resp[$item[1]]['added']++;
					}
					if ($status[0] == "Changed") {
							$resp[$item[1]]['edited']++;
					}
					if ($status[0] == "Deleted") {
						$resp[$item[1]]['deleted']++;
					}
					break;
				case 'Error':
					$resp[$item[1]]['error']++;
					break;

			}
		}
	}
	echo json_encode($resp);
}
else echo json_encode(array('error' => 'bysy'));