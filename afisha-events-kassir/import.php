<?php
date_default_timezone_set('Asia/Yekaterinburg');

header( 'Content-Type: text/html; charset=utf-8');
$DR = dirname(__FILE__);
include_once ($DR.'/../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');

if (!is_file($DR."/config.php"))
	die( "config.php not found" );
include $DR."/config.php";


class AfishaEventsKassir
{
	const DEBUG = false;
	const HALL_URL = 'kassir_getHallList.xml';
	const EVENTS_URL = 'kassir_getEventList.xml';
	const WEB_AGENT_ID = '115034268';

	// Данные, полученные с gpor и с kassir
	private $_gporEventsData = array();
	private $_kassirEventsData = array();


	public function __construct()
	{
		if (self::DEBUG)
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			echo "<pre>";
		}
		set_time_limit(0);
		mb_internal_encoding("UTF-8");

		$this->_kassirEventsData['places'] = array();
		$this->_kassirEventsData['events'] = array();
	}


	/**
	 * Точка запуска
	 */
	public function run()
	{
		$this->output(__METHOD__);

		// Получаем данные с gpor и с kassir
		$this->getKassirData();

		// Сравниваем места
		$this->_gporEventsData['places'] = $this->getGporEventPlaces();
		$placesToSend = $this->comparePlaces();
		$this->sendGporEventPlaces( $placesToSend );

		// Сравниваем события
		// Получаем места еще раз, т.к. новые места получили новые ID
		$this->_gporEventsData['places'] = $this->getGporEventPlaces();
		$this->_gporEventsData['events'] = $this->getGporEvents();

		// Прописываем все externalId и сравниваем события
		$this->comparePlaces();
		$seancesToSend = $this->compareEvents();
		$this->sendGporEventSeances( $seancesToSend );
	}


	/**
	 * Получаем данные с Gpor
	 */
	private function getGporEventPlaces()
	{
		$this->output(__METHOD__);

		$arr = $this->sendApiRequest('afisha.listEventPlaces');
		$this->output("\t\tTotalPlaces: ".count($arr));
		return $arr;
	}


	/**
	 * Получаем данные с Gpor
	 */
	private function getGporEvents()
	{
		$this->output(__METHOD__);

		$arr = $this->sendApiRequest('afisha.listEvents');
		$this->output("\t\tTotalEvents: ".count($arr));
		return $arr;
	}


	/**
	 * Передать данные на Gpor
	 */
	private function sendGporEventPlaces( $placesToSend )
	{
		$this->output(__METHOD__);

		$res = false;
		if (count($placesToSend))
			$res = $this->sendApiRequest('afisha.postEventPlaces', $placesToSend);
		return $res;
	}


	/**
	 * Передать данные на Gpor
	 */
	private function sendGporEvent( $event )
	{
		$this->output(__METHOD__);

		$eventData = array(
			"title"       => $event['title'],
			"description" => $event['text'],
			"placeId"     => $this->getGporExternalId( $event['location'] ),
			"seances"     => serialize( array( (string)strtotime(str_replace('+0400', '+0600', $event['date']) ) ) ), // тупой kassir.ru отдает местное время, но сдвиг московский
			"image"       => $event['image'],
			"siteBooking" => "http://www.kassir.ru/__wa/".self::WEB_AGENT_ID."/".str_replace('http://','',$event['url'])
		);

		$res = $this->sendApiRequest('afisha.postEvent', $eventData);
		return $res;
	}


	/**
	 * Передать данные на Gpor
	 */
	private function sendGporEventSeances( $seancesToSend )
	{
		$this->output(__METHOD__);

		$res = false;
		if (count($seancesToSend))
			$res = $this->sendApiRequest('afisha.postEventSeances', $seancesToSend);
		return $res;
	}


	/**
	 * Получаем данные с kassir
	 */
	private function getKassirData()
	{
		$this->output(__METHOD__);

		$this->_kassirEventsData['places'] = array();
		$this->_kassirEventsData['events'] = array();

		// Места
		$url = self::HALL_URL;
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml);
		$data = $this->parseXmlObject( $xmlDataObject );
		foreach ($data['Location'] as $loc)
		{
			$locData = $loc['@attributes'];
			$id = $locData['id'];
			$this->_kassirEventsData['places'][$id] = $locData;
		}

		// События
		$url = self::EVENTS_URL;
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml);
		$data = $this->parseXmlObject( $xmlDataObject );
		foreach ($data['Event'] as $loc)
		{
			$locData = $loc['@attributes'];
			$id = $locData['id'];
			$this->_kassirEventsData['events'][$id] = $locData;
		}

		$this->output("\t\tTotalPlaces: ".count($this->_kassirEventsData['places']));
		$this->output("\t\tTotalEvents: ".count($this->_kassirEventsData['events']));
	}


	/**
	 * Сравниваем и обновляем места событий
	 */
	private function comparePlaces()
	{
		$this->output(__METHOD__);

		$placesToSend = array();

		$oldPlacesCount = 0;
		$newPlacesCount = 0;

		foreach ($this->_kassirEventsData['places'] as $placeIdKassir => &$placeKassir)
		{
			if (empty($placeKassir['title']))
				continue;

			$found = false;
			foreach ($this->_gporEventsData['places'] as $placeIdGpor => $placeGpor)
			{
				if ( $this->matchName( $placeKassir['title'], $placeGpor['title'] ) )
					$found = $placeGpor;

				if ( $placeGpor['synonym'] )
				{
					foreach ( unserialize($placeGpor['synonym']) as $syn )
					{
						if ( $this->matchName( $placeKassir['title'], $syn) )
							$found = $placeGpor;
					}
				}

				if ($found)
					break;
			}

			if (!$found)
			{
				$this->output( "\tNew place ['".$placeKassir['title']."']" );
				$placesToSend[] = $placeKassir;
				$newPlacesCount++;
			}
			else
			{
				$this->output( "\tFound ['".$placeKassir['title']."'] id=".$found['id'] );

				// Т.к. placeKassir это ссылка, то externalId появляется в _kassirEventsData
				$placeKassir['externalId'] = $found['id'];
				$placesToSend[] = $placeKassir;
				$oldPlacesCount++;
			}
		}

		$this->output( "" );
		$this->output( "\t$newPlacesCount new places" );
		$this->output( "\t$oldPlacesCount old places" );

		return $placesToSend;
	}


	/**
	 * Сравниваем и обновляем события
	 *
	 * 'location' => string '71298346' (length=8)
	 * 'date' => string '2012-09-27T18:30:00+0400' (length=24)
	 * 'url' => string 'http://www.kassir.ru/ekb/db/text/832911118.html' (length=47)
	 * 'id' => string '832911118' (length=9)
	 * 'title' => string 'Трамвай "Желание"' (length=31)
	 * 'text' => string 'Теннеси УильямсДрама в 2-х действиях.Продолжительность спектакля 2 часа 40 минут с антрактом.  Знаменитая пьеса великого американского драматурга была написана в 1927 году и имела оглушительный успех. До сих пор ее ставят самые знаменитые театры мира, очень часто пьесу экранизируют'... (length=1771)
	 * 'image' => string 'http://www.kassir.ru/data/159/573/1338/1tramvaj-121x130.jpg' (length=59)
	 * 'plan' => string '' (length=0)
	 * 'rubricator' => string 'Театры' (length=12)
	 *
	 * 'id' => string '4' (length=1)
	 * 'title' => string 'событие 4' (length=16)
	 * 'seances' => string (array)
	 * 'placeId'
	 * 'synonym' => string '' (length=0)
	 */
	private function compareEvents()
	{
		$this->output(__METHOD__);

		$seancesToSend = array();

		$oldEventsCount = 0;
		$newEventsCount = 0;
		$newSeancesCount = 0;

		foreach ($this->_kassirEventsData['events'] as $eventIdKassir => &$eventKassir)
		{
			if (empty($eventKassir['title']))
				continue;

			$found = false;
			foreach ($this->_gporEventsData['events'] as $eventIdGpor => &$eventGpor)
			{
				if ( $this->matchName( $eventKassir['title'], $eventGpor['title'] ) )
					$found = $eventGpor;

				if ( $eventGpor['synonym'] )
				{
					foreach ( unserialize($eventGpor['synonym']) as $syn )
					{
						if ( $this->matchName( $eventKassir['title'], $syn) )
							$found = $eventGpor;
					}
				}

				// Сравниваем места
				if ($found)
				{
					$placeIdGpor = $this->getGporExternalId( $eventKassir['location'] );
                    if($placeIdGpor !== false && $placeIdGpor != $found['placeId'])
						$found = false;
				}

				if ($found)
					break;
			}

			if (!$found)
			{
				// Добавляем новое событие в список gpor, теперь к нему будут добавляться только сеансы
				$this->output( "\tNew event ['".$eventKassir['title']."']" );
				$this->_gporEventsData['events'][] = $this->sendGporEvent( $eventKassir );
				$newEventsCount++;
			}
			else
			{
				// Сравниваем сеансы
                $kassirDate = (string)strtotime(str_replace('+0400', '+0600', $eventKassir['date']) );
				if (!in_array($kassirDate, unserialize($found['seances'])))
				{
					$this->output( "\tNew seance ['".$eventKassir['title']."'] id=".$found['id']." placeId=".$found['placeId'] );
					$seancesToSend[] = array(
						"id" => $found['id'],
						"seanceTime" => $kassirDate,
					);
					$newSeancesCount++;
				}
				else
				{
					$this->output( "\tFound ['".$eventKassir['title']."'] id=".$found['id']." placeId=".$found['placeId'] );
					$oldEventsCount++;
				}
			}
		}

		$this->output( "" );
		$this->output( "\t$newEventsCount new events" );
		$this->output( "\t$oldEventsCount old events" );
		$this->output( "\t$newSeancesCount new seances" );

		return $seancesToSend;
	}


	/**
	 * Получить externalId по ID кассира
     *
     * @param integer $placeIdKassir // location id на kassir.ru
     * @return integer|boolean
	 */
	private function getGporExternalId( $placeIdKassir )
	{
		// Если externalId не найден, то отдаем false,
		// значит не все новые события были добавлены на GPOR и надо дебажить код выше
		return isset($this->_kassirEventsData['places'][$placeIdKassir]['externalId']) ? $this->_kassirEventsData['places'][$placeIdKassir]['externalId'] : false;
	}


	/**
	 * Отправить API-запрос на gpor
	 */
	private function sendApiRequest($name, $params = array(), $isDebug = false)
	{
		global $apiKey, $apiUrl;

		$this->output( "\tCall API method: $name" );

		$client	= new xmlrpc_client($apiUrl);
		$client->request_charset_encoding	= 'UTF-8';
		$client->return_type				= 'phpvals';
		$client->debug						= self::DEBUG;

		$msg = new xmlrpcmsg($name);
		$p1 = new xmlrpcval($apiKey, 'string');
		$msg->addparam($p1);

		if ($params)
		{
			$p2 = php_xmlrpc_encode($params);
			$msg->addparam($p2);
		}
	
		$client->accepted_compression = 'deflate';
		$res = $client->send($msg, 60 * 5, 'http11');

		if ($res->faultcode())
		{
			print "An error occurred: ";
			print " Code: " . htmlspecialchars($res->faultCode());
			print " Reason: '" . htmlspecialchars($res->faultString()) . "' \n";
			die;
		}

		return $res->val;
	}


	/**
	 * Парсинг XML объекта в массив
	 */
	private function parseXmlObject($arrObjData, $arrSkipIndices = array())
	{
		$arrData = array();

		// if input is object, convert into array
		if (is_object($arrObjData))
			$arrObjData = get_object_vars($arrObjData);

		if (is_array($arrObjData))
		{
			foreach ($arrObjData as $index => $value)
			{
				if (is_object($value) || is_array($value))
					$value = $this->parseXmlObject($value, $arrSkipIndices); // recursive call
				if (in_array($index, $arrSkipIndices))
					continue;
				$arrData[$index] = $value;
			}
		}
		return $arrData;
	}


	/**
	 * Сравниваем два текстовых поля
	 */
	function matchName($a, $b)
	{
		$a = mb_strtolower($a);
		$b = mb_strtolower($b);

		// Вырезаем все несимвольные и нецифровые символы
		$a = preg_replace('|[^\p{L}\p{Nd}]|u', '', $a);
		$b = preg_replace('|[^\p{L}\p{Nd}]|u', '', $b);

		if ($a == $b)
			return true;
		return false;
	}


	/**
	 * Вывод информации в output
	 */
	private function output($str)
	{
		if (self::DEBUG)
			echo $str."\n";
	}
}


$obj = new AfishaEventsKassir();
try
{
	$obj->run();
}
catch( Exception $e )
{
	echo( $e );
}

?>