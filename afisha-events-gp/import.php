<?php
//date_default_timezone_set('Asia/Yekaterinburg');
date_default_timezone_set('Asia/Karachi');

header( 'Content-Type: text/html; charset=utf-8');
$DR = dirname(__FILE__);
include_once ($DR.'/../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');

if (!is_file($DR."/config.php"))
	die( "config.php not found" );
include $DR."/config.php";


class AfishaEventsGp
{
	const DEBUG = true;
	const EVENTS_URL = 'http://gorodskoyportal.ru/img/xml/afisha_ekaterinburg.xml';

	// Данные, полученные с gpor и с gp
	private $_gporEventsData = array();
	private $_gpEventsData = array();

	protected $options = array();


	public function __construct($options)
	{
		if (self::DEBUG)
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			echo "<pre>";
		}
		set_time_limit(0);
		mb_internal_encoding("UTF-8");

		$this->options = array_replace_recursive(array('placeTypes'=>array(), 'eventTypes'=>array(), 'eventTags'=>array()), $options);

		$this->_gpEventsData['places'] = array();
		$this->_gpEventsData['events'] = array();
	}


	/**
	 * Точка запуска
	 */
	public function run()
	{
		$this->output(__METHOD__);

		// Получаем данные с gpor и с gp
		$this->getData();

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

		$offset = 0;
		$limit = 200;

		$result = array();
		$arr = array();

		while(true) {
			$arr = $this->sendApiRequest('afisha.listEventsLimit', array('limit' => $limit, 'offset' => $offset));
			if (empty($arr)) {
				break;
			}

			$result = array_merge($result, $arr);
			$offset += $limit;
		}

		$this->output("\t\tTotalEvents: ".count($result));
		return $result;
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

		$gpPlaceId = $event['placeId'];
		$placeIdGpor = $this->getGporExternalId($gpPlaceId);

		$eventData = $event;
		unset($eventData['id']);
		$eventData['eventPlaceId'] = $placeIdGpor;
		$eventData['status'] = 20;

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
	 * Получаем данные с gp
	 */
	private function getData()
	{
		$this->output(__METHOD__);

		$this->_gpEventsData['places'] = array();
		$this->_gpEventsData['events'] = array();

		// Места
		$url = self::EVENTS_URL;
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml, null, LIBXML_NOCDATA);
		$data = $this->parseXmlObject( $xmlDataObject );

		if (!is_array($data) || !$data['places'] || !$data['places']['place']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['places']['place'] as $loc)
		{
			$id = (int)$loc['@attributes']['id'];
			$phone = isset($loc['phones']) && is_array($loc['phones']['phone']) ? implode(',', $loc['phones']['phone']) : (isset($loc['phones']) ? $loc['phones']['phone'] : '');
			$locData = array (
				'title' => $loc['title'],
				'externalId' => $id,
				'address' => $loc['address'],
				'type' => $loc['@attributes']['type'],
				'phone' => $phone,
			);
			$this->_gpEventsData['places'][$id] = $locData;
		}


		// События
/*
Array
(
    [@attributes] => Array
        (
            [id] => 58529
            [type] => theatre
        )

    [title] => Левая грудь Афродиты
    [age_restricted] => 18+
    [tags] => Array
        (
            [tag] => Array
                (
                    [0] => комедия
                    [1] => спектакль
                )

        )

    [persons] => Array
        (
            [person] => Array
                (
                    [name] => Юрий Поляков
                    [role] => screenwriter
                )

        )

    [gallery] => Array
        (
            [image] => Array
                (
                    [@attributes] => Array
                        (
                            [href] => http://gorodskoyportal.ru/ekaterinburg/pictures/posters/58529/58529_yandex.jpg?1426682980
                        )

                )

        )

    [text] => <p>Две молодые пары, только-только связавшие себя узами брака, проводят в Крыму свой медовый месяц. Но отдыхать, расслабляться и наслаждаться обществом друг друга у них получается недолго. Супруга бизнесмена узнает в другой семейной паре своего бывшего мужа-писателя, а сам бизнесмен оказывается бывшим возлюбленным нынешней молодой супруги этого писателя. Старая любовь оказывается сильнее новой, страсть по отношению к своим «бывшим» одолевает и тех, и других молодоженов. Тайные связи, измены лежат в основе сюжетной коллизии. Подавить в душе любовный мятеж или все-таки пренебречь священными узами брака — сложный выбор, по сути, являющийся драмой. Но и сам хозяин гостиницы фигура мистическая... а этот магический знак... а дух маршала... и многое другое.</p>
)
*/			
		if (!is_array($data) || !$data['events'] || !$data['events']['event']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['events']['event'] as $loc)
		{
			$id = (int)$loc['@attributes']['id'];
			$tags = isset($loc['tags']['tag']) && is_array($loc['tags']['tag']) ? $loc['tags']['tag'] : (isset($loc['tags']['tag']) ? array($loc['tags']['tag']) : array());

			$annotation = isset($loc['text']) ? $loc['text'] : '';
			if (isset($loc['text'])) {
				preg_match_all('#<[p|div]\s?([^>]+)?>\s?(.*)<[/|b]#u', $loc['text'], $matches);
				if ($matches && $matches[2]) {
					$annotation = $matches[2][0];
				}
				else {
					// TODO: не смог обработать регулярку
				}
			}

			$item = array (
			    'title' => $loc['title'],
			    'annotation' => $annotation,
			    'type' => $loc['@attributes']['type'],
			    'ageRestrictions' => isset($loc['age_restricted']) ? $loc['age_restricted'] : '',
			    'duration' => '',
			    'tags' => $tags,
			    'description' => isset($loc['text']) ? $loc['text'] : '',
			    'placeId' => '',
			    'siteBooking' => '',
				'image' => false,
				'gpId' => $id,
				'seances' => array()
			);

			if (isset($loc['gallery']) && $loc['gallery'] && $loc['gallery']['image']) {
				if (isset($loc['gallery']['image']['@attributes'])) {
					$image = $loc['gallery']['image'];
				}
				else {
					$image = $loc['gallery']['image'][0];
				}
				$item['image'] = $image['@attributes']['href'];
			}

			$this->_gpEventsData['events'][$id] = $item;
		}


		// Даты
		if (!is_array($data) || !$data['schedule'] || !$data['schedule']['session']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['schedule']['session'] as $loc)
		{
/*
Array
(
    [@attributes] => Array
        (
            [id] => 9943915
            [event] => 30480
            [place] => 1054
            [date] => 2015-08-09
            [time] => 20:00
        )

)
*/			
			$locData = $loc['@attributes'];
			$id = (int)$locData['id'];
			$eventId = (int)$locData['event'];
			$placeId = (int)$locData['place'];
			if (!isset($this->_gpEventsData['events'][$eventId])) {
				continue;
			}
			elseif ($this->_gpEventsData['events'][$eventId]['placeId'] && $placeId != $this->_gpEventsData['events'][$eventId]['placeId']) {
				continue;
			}
			$this->_gpEventsData['events'][$eventId]['placeId'] = $placeId;
			$this->_gpEventsData['events'][$eventId]['seances'][] = strtotime($locData['date'] . ' ' . $locData['time'] . ':00');
		}

		$this->output("\t\tTotalPlaces: ".count($this->_gpEventsData['places']));
		$this->output("\t\tTotalEvents: ".count($this->_gpEventsData['events']));
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

		foreach ($this->_gpEventsData['places'] as $placeIdgp => &$placegp)
		{
			if (empty($placegp['title']))
				continue;

			$found = false;
			foreach ($this->_gporEventsData['places'] as $placeIdGpor => $placeGpor)
			{
				if ( $this->matchName( $placegp['title'], $placeGpor['title'] ) )
					$found = $placeGpor;

				if ( $placeGpor['synonym'] )
				{
					foreach ( unserialize($placeGpor['synonym']) as $syn )
					{
						if ( $this->matchName( $placegp['title'], $syn) ) {
							$found = $placeGpor;
						}
					}
				}

				if ($found)
					break;
			}

			if (!$found)
			{
				$this->output( "\tNew place ['".$placegp['title']."']" );
				$newPlacesCount++;
			}
			else
			{
				$this->output( "\tFound ['".$placegp['title']."'] id=".$found['id'] );

				$this->_gpEventsData['places'][$placeIdgp]['externalId'] = $found['id'];
				$oldPlacesCount++;
			}
			$item = array (
 				'id' => ($found['id'] ? $found['id'] : false),
 				'externalId' => ($found['id'] ? $found['id'] : false),
				'name' => $placegp['title'],
				'title' => $placegp['title'],
				'address' => $placegp['address'],
				'phone' => $placegp['phone'],
				'type' => $placegp['type'],
			);
			$placesToSend[] = $item;
		}

		$this->output( "" );
		$this->output( "\t$newPlacesCount new places" );
		$this->output( "\t$oldPlacesCount old places" );

		return $placesToSend;
	}


	/**
	 * Сравниваем и обновляем события
	 *
	 */
	private function compareEvents()
	{
		$this->output(__METHOD__);

		$seancesToSend = array();

		$oldEventsCount = 0;
		$newEventsCount = 0;
		$newSeancesCount = 0;

		foreach ($this->_gpEventsData['events'] as $eventIdgp => &$eventgp)
		{
			if (empty($eventgp['title']) || empty($eventgp['seances'])){
				continue;
			}

			$found = false;
			foreach ($this->_gporEventsData['events'] as $eventIdGpor => &$eventGpor)
			{
				if ( $this->matchName( $eventgp['title'], $eventGpor['title'] ) )
					$found = $eventGpor;

				$fullName = $eventgp['title'] . ($eventgp['ageRestrictions'] ? ' ('. $eventgp['ageRestrictions'] . ')' : '');
				if ( $eventGpor['synonym'] )
				{
					foreach ( unserialize($eventGpor['synonym']) as $syn )
					{
						if ( $this->matchName( $fullName, $syn) )
							$found = $eventGpor;
					}
				}

				// Сравниваем места
				if ($found)
				{
					$gpPlaceId = $eventgp['placeId'];
					$placeIdGpor = (int)$this->getGporExternalId( $gpPlaceId );
					if ($placeIdGpor != $found['placeId'])
						$found = false;
				}

				if ($found)
					break;
			}

			if (!$found)
			{
				if (count($eventgp['seances']) >= 10) {
					echo $eventgp['title'] .' skipped' . "\n";
					// TODO: делать интервальным
					continue;
				} 

				// Добавляем новое событие в список gpor, теперь к нему будут добавляться только сеансы
				$this->output( "\tNew event ['".$eventgp['title']."']" );
				$this->_gporEventsData['events'][] = $this->sendGporEvent( $eventgp );
				$newEventsCount++;
			}
			else
			{
				// интервальные события пропускаем
				if (count($eventgp['seances']) >= 10) {
					echo $eventgp['title'] .' skipped' . "\n";
					// TODO: делать интервальным
					continue;
				}

				// Сравниваем сеансы
				foreach ($eventgp['seances'] as $gpDate) {
					if (1 || !in_array($gpDate, unserialize($found['seances'])))
					{
						$this->output( "\tNew seance ['".$eventgp['title']."(".$gpDate.")'] id=".$found['id']." placeId=".$found['placeId'] );
						$seancesToSend[] = array(
							"id" => $found['id'],
							"seanceTime" => $gpDate,
						);
						$newSeancesCount++;
					}
					else
					{
						$this->output( "\tFound ['".$eventgp['title']."'] id=".$found['id']." placeId=".$found['placeId'] );
						$oldEventsCount++;
					}

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
	 */
	private function getGporExternalId( $placeIdgp )
	{
		// Если externalId не найден, то это пипец ошибка,
		// значит не все новые события были добавлены на GPOR и надо дебажить код выше 
		if (!isset($this->_gpEventsData['places'][$placeIdgp]['externalId'])) {
			print_r($this->_gpEventsData['places']);
			die();
		}
		return $this->_gpEventsData['places'][$placeIdgp]['externalId'];
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
		$client->debug						= $isDebug;

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
			print_r($res);
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


$obj = new AfishaEventsGp($options);
try
{
	$obj->run();
}
catch( Exception $e )
{
	echo( $e );
}

?>