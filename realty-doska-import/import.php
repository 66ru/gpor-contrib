<?php
 
include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = include_once('config.php');
if (!$params)
	die('Error: config.php not found.');

//
$kkey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
if (!$kkey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');

//Подгрузка данных с достки
$msg = Upload($params['live_msg_url']);
$stat_live = Upload($params['live_counters_url']);
$stat_notlive = Upload($params['notlive_counters_url']);

//Сбор всех данных в один массив
$result = array(
	'msg' => array(
		'all' => array('name'=>'Все', 'list'=>FilterMSG($msg)),
		'sell' => array('name'=>'Продам', 'list'=>FilterMSG($msg, "/^Продам /u")),
		'lease' => array('name'=>'Сдам', 'list'=>FilterMSG($msg, "/^Сдам /u")),
		'hite' => array('name'=>'Сниму', 'list'=>FilterMSG($msg, "/^Сниму /u"))
	),
	'counters' => array(
		'live' => array(
			'sell' => ProcLive('sell', $stat_live['sell']),
			'lease' => ProcLive('lease', $stat_live['lease']),
			'hire' => ProcLive('hire', $stat_live['hire'])
		),
		'notlive' =>  array(
			'sell' => ProcNotLive('sell', $stat_notlive['sell']),
			'lease' => ProcNotLive('lease', $stat_notlive['lease']),
			'hire' => ProcNotLive('hire', $stat_notlive['hire'])
		)
	),
	'serviceUrl' => $params['serviceUrl']
);

//Отправка данных
$client = new xmlrpc_client($apiUrl);
$client->request_charset_encoding = 'UTF-8';
$message = new xmlrpcmsg("realty.doska", array(
		new xmlrpcval($kkey, 'string'), 
		new xmlrpcval(json_encode($result), 'string'),
));
$hRet = $client->send($message, 0, 'http11');
if(!$hRet->faultCode()){
	//удача
}
else
{
	print "An error occurred: ";
	print " Code: ".htmlspecialchars($hRet->faultCode());
	print " Reason: '".htmlspecialchars($hRet->faultString())."' \n";
}

/**
 * Получение данных с доски и представление их ввиде массива.
 * @return array
 */
function Upload($url){
	$arr = null;
	$data = @file_get_contents($url);
	if ($data===false){
		echo("Can not load data from \"$url\". ");
	}
	else{
		$arr = @json_decode($data, true);
		if (!is_array($arr)){
			echo("Can not parse load data from \"$url\". ");
			$arr = null;
		}
	}
	return $arr;
}//Upload

/**
 * Фильтрация сообщений по регулярному выражению. Проверяеися title.
 * @param array $msg Сообщения ввиде массива.
 * @param string $pat Регулярное выражение, на основе которого происходит отбор сообщений.
 * @return array|null Возвращает массив или null если нечего не найденно.
 */
function FilterMSG($msg, $pat=null){
	if($msg===null) return null;
	if($pat===null){
		return count($msg)>0?$msg:null;
	}
	else{
		$result = array();
		foreach($msg as $el){
			if(preg_match($pat, $el['title'])===1){
				array_push($result, $el);
			}
		}
		return count($result)>0?$result:null;
	}
}//FilterMSG

/**
 */
function ProcLive($action_type, $action_value){
	global $params;
	$result = array('total' => null);
	if(!$action_value) return null;
	foreach($action_value as $object_type => $el){
		if($object_type=="total")	$result['total'] = $el;
		else
		if($object_type=="kv"){
			$kvs_res = array();
			foreach($el as $room => $count){
				$kvs_res += array('room'.$room => array(
					'name' => $room.'к',
					'rooms' => $room,
					'url' => $params['serviceUrl'].'?object_type='.$object_type.'&rooms='.$room.'&action_type='.$action_type,
					'count' => $count
				));
			}
			
			$result += array($object_type=>count($kvs_res)>0?$kvs_res:null);
		}
		else{
			$result += array($object_type=>array(
				'name' => RusNames($object_type),
				'url' => $params['serviceUrl'].'?object_type='.$object_type.'&action_type='.$action_type,
				'count' => $el
			));
		}	
	}
	return $result;
}//ProcLive

/**
 */
function ProcNotLive($action_type, $action_value){
	global $params;
	$result = array('total' => null);
	if(!$action_value) return null;
	foreach($action_value as $object_type => $el){
		if($object_type=="total")	$result['total'] = $el;
		else
		if($object_type=="office"){
			$office_res = array();
			foreach($el as $square => $count){
				$office_res += array($square => array(
					'name' => RusNames($square),
					'url' => "#",
					'count' => $count
				));
			}
			$result += array($object_type=>count($office_res)>0?$office_res:null);
		}
		else{
			$result += array($object_type=>array(
				'name' => RusNames($object_type),
				'url' => "#",
				'count' => $el
			));
		}	
	}
	return $result;
}//ProcNotLive

function RusNames($key){
	$names = array(
		'house' => 'Дома',
		'garage' => 'Гаражи',
		'ground' => 'Участки',
		'room' => 'Комнаты',
		'100' => 'до 100м<sup>2</sup>',
		'100-500' => '100&ndash;500м<sup>2</sup>',
		'500' => '500м<sup>2</sup>+',
		'store' => 'Торговые площади',
		'office' => 'Офисы',
		'production' => 'Производство',
		'depot' => 'Склады',
		'other' => 'Прочее'
	);
	return $names[$key];
}

?>