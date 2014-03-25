<?php
date_default_timezone_set('Asia/Yekaterinburg');

header( 'Content-Type: text/html; charset=utf-8');
$DR = dirname(__FILE__);
include_once ($DR.'/../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');

if (!is_file($DR."/config.php"))
	die( "config.php not found" );
include $DR."/config.php";


class AfishaEventsKassy
{
	const DEBUG = true;
	const EVENTS_URL = 'http://gate.ekb.kassy.ru/v2/event_c?_set=0';
	const EVENTS_DESC_URL = 'http://gate.ekb.kassy.ru/v2/show_c?_set=0';
	const EVENTS_RSS_URL = 'http://ekb.kassy.ru/rss/';
	const HALL_URL = 'http://gate.ekb.kassy.ru/v2/hall_c?_set=0&_order=IdHall';
	const PLACE_URL = 'http://gate.ekb.kassy.ru/v2/building_c?_set=0&_order=IdBld';
	const WEB_AGENT_ID = '115034268';

	// Данные, полученные с gpor и с kassy
	private $_gporEventsData = array();
	private $_kassyEventsData = array();

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

		$this->_kassyEventsData['places'] = array();
		$this->_kassyEventsData['events'] = array();
	}


	/**
	 * Точка запуска
	 */
	public function run()
	{
		$this->output(__METHOD__);

		// Получаем данные с gpor и с kassy
		$this->getKassyData();

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

		$kassyPlaceId = isset($this->_kassyEventsData['hallsToPlaces'][$event['IdHall']]) ? $this->_kassyEventsData['hallsToPlaces'][$event['IdHall']] : false;
		$placeIdGpor = $this->getGporExternalId($kassyPlaceId);

		$eventData = array(
			"title"       => $event['Name'] . ($event['Age_restriction'] ? ' ('. $event['Age_restriction'] . ')' : ''),
			"description" => $event['Rem'],
			"eventPlaceId"     => $placeIdGpor,
			"seances"     => serialize( array( (string)$event['DT'] ) ),
			"image"       => $event['image'],
			"siteBooking" => "http://ekb.kassy.ru/event/".$event['kassyId']."/order/",
			"tickets" => ($event['MinPrc'] ? 'от '.$event['MinPrc'] . ' ' : '') . ($event['MaxPrc'] ? 'до '.$event['MaxPrc'] . ' ' : ' ') . ($event['MinPrc'] || $event['MaxPrc'] ? 'руб.' : ''),
		);
		if (isset($this->options['eventTags'][$event['IdShType']])) {
			$eventData['tags_ids'] = array($this->options['eventTags'][$event['IdShType']]);
		}
		if (isset($this->options['eventTypes'][$event['IdShType']])) {
			$eventData['type'] = array($this->options['eventTypes'][$event['IdShType']]);
		}

		print_r($eventData);
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
	 * Получаем данные с kassy
	 */
	private function getKassyData()
	{
		$this->output(__METHOD__);

		$this->_kassyEventsData['places'] = array();
		$this->_kassyEventsData['events'] = array();

		// Залы
		$url = self::HALL_URL;
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml);
		$data = $this->parseXmlObject( $xmlDataObject );
		if (!is_array($data) || !$data['ROWDATA'] || !$data['ROWDATA']['ROW']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['ROWDATA']['ROW'] as $loc)
		{
			$locData = $loc['@attributes'];
			$id = $locData['Id'];
			$this->_kassyEventsData['hallsToPlaces'][$id] = $locData['IdBld'];
		}


		$url = self::PLACE_URL;
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml);
		$data = $this->parseXmlObject( $xmlDataObject );
		if (!is_array($data) || !$data['ROWDATA'] || !$data['ROWDATA']['ROW']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['ROWDATA']['ROW'] as $loc)
		{
/*
                            [@attributes] => Array
                                (
                                    [Id] => 1
                                    [IdBldTyp] => 1
                                    [IdClient] => 1
                                    [IdRegion] => 67
                                    [IdMetro] => 
                                    [Name] => Городские Зрелищные Кассы
                                    [Descr] => 
                                    [Addr] => 620075, Екатеринбург, ул. Карла Либкнехта, 22, офис 602
                                    [Phone] => (343) 222-70-00
                                    [Way] => 
                                    [WorkHrs] => пн-пт с 10:00 до 18:00, сб-вс выходной
                                    [IdNomPC] => 
                                    [IdNomPCTop] => 
                                )

*/			
			$locData = $loc['@attributes'];
			$id = $locData['Id'];
			$this->_kassyEventsData['places'][$id] = $locData;
		}


		// События
		$url = self::EVENTS_RSS_URL;
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml);
		$data = $this->parseXmlObject( $xmlDataObject );

		if (!is_array($data) || !$data['channel'] || !$data['channel']['item']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['channel']['item'] as $loc)
		{
			$id = false;

			if(preg_match('#/event/([0-9]+)/#', $loc['link'], $matches)) {
				$id = $matches[1];
			}
			if (!$id) {
				continue;
			}
			$item = array ('image' => false, 'kassyId' => $id);

			if (isset($loc['enclosure']) && $loc['enclosure'] && $loc['enclosure']['@attributes'] && $loc['enclosure']['@attributes']['url']) {
				$item['image'] = $loc['enclosure']['@attributes']['url'];
			}
			$this->_kassyEventsData['events'][$id] = $item;
		}


		// События
		$url = self::EVENTS_URL;
		$url = $url . '&' . 'DT1=' . time()  .'&DT2=' . (time() + 60*60*24*90);
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml);
		$data = $this->parseXmlObject( $xmlDataObject );

		if (!is_array($data) || !$data['ROWDATA'] || !$data['ROWDATA']['ROW']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['ROWDATA']['ROW'] as $loc)
		{
/*
                                    [Id] => 9569065
                                    [IdShow] => 3950489
                                    [IdHall] => 8
                                    [IdPrice] => 24311
                                    [IdTime] => 905
                                    [DT] => 1395725400
                                    [State] => 3
                                    [Gst] => 0
                                    [Prm] => 0
                                    [Hidden] => 0
                                    [IdPC] => 
                                    [IdPCTop] => 
                                    [MinPrc] => 100
                                    [MaxPrc] => 300
*/			
			$locData = $loc['@attributes'];
			$id = $locData['Id'];
			if (!isset($this->_kassyEventsData['events'][$id])) {
				continue;
			}
			$this->_kassyEventsData['showToEvents'][$locData['IdShow']] = $locData['Id'];
			foreach ($locData as $key => $value) {
				$this->_kassyEventsData['events'][$id][$key] = $value;
			}
		}

		// События
		$url = self::EVENTS_DESC_URL;
		$this->output("\tparse ".$url);

		$xml = file_get_contents($url);
		if (!$xml)
			throw new Exception("Can't load data from ".$url);

		$xmlDataObject = simplexml_load_string($xml);
		$data = $this->parseXmlObject( $xmlDataObject );

		if (!is_array($data) || !$data['ROWDATA'] || !$data['ROWDATA']['ROW']) {
			throw new Exception("Can't read data from ".$url);
		}
		foreach ($data['ROWDATA']['ROW'] as $loc)
		{
/*
                                    [Id] => 8856160
                                    [IdRollerman] => 62657
                                    [IdShType] => гс
                                    [IdGenre] => 
                                    [Name] => Буратино на льду
                                    [Descr] => 
                                    [Age_restriction] => 
                                    [PremiereDate] => 1385130480
                                    [Duration] => 7200
                                    [Rem] => В  программе: " Аттракционы; " Сказка "Буратино на льду" с элементами  огненного шоу; " Развлечения с Дедом Морозом и Снегурочкой; " Фото сессия и возможность катания на льду  с артистами ледового шоу; " Сладкий подарок с сюрпризом.  Даты:  29 декабря 2013г., 4, 5, 6 января 2014г. начало в 12.00 и 15.00 Стоимость билета:  от 350 рублей (с подарком).  Купив билеты на ледовый спектакль, который состоится в культурно - развлекательном комплексе "Уралец", вы подарите себе и своим детям настоящее новогоднее представление. Продолжительность шоу: 45-50 минут Продолжительность мероприятия: 1,5 - 2 часа.  Программа рассчитана на семейную аудиторию с детьми  от 3-х до 14 лет
                                    [Actors] => 
                                    [Producer] => 
                                    [Painter] => 
                                    [Hidden] => 0
*/			
			$locData = $loc['@attributes'];
			if (!isset($this->_kassyEventsData['showToEvents'][$locData['Id']])) {
				continue;
			}
			$id = $this->_kassyEventsData['showToEvents'][$locData['Id']];
			foreach ($locData as $key => $value) {
				$this->_kassyEventsData['events'][$id][$key] = $value;
			}
		}


		$this->output("\t\tTotalPlaces: ".count($this->_kassyEventsData['places']));
		$this->output("\t\tTotalEvents: ".count($this->_kassyEventsData['events']));
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

		foreach ($this->_kassyEventsData['places'] as $placeIdkassy => &$placekassy)
		{
			if (empty($placekassy['Name']))
				continue;

			$found = false;
			foreach ($this->_gporEventsData['places'] as $placeIdGpor => $placeGpor)
			{
				if ( $this->matchName( $placekassy['Name'], $placeGpor['title'] ) )
					$found = $placeGpor;

				if ( $placeGpor['synonym'] )
				{
					foreach ( unserialize($placeGpor['synonym']) as $syn )
					{
						if ( $this->matchName( $placekassy['Name'], $syn) ) {
							$found = $placeGpor;
						}
					}
				}

				if ($found)
					break;
			}

			if (!$found)
			{
				$this->output( "\tNew place ['".$placekassy['Name']."']" );
				$newPlacesCount++;
			}
			else
			{
				$this->output( "\tFound ['".$placekassy['Name']."'] id=".$found['id'] );

				$this->_kassyEventsData['places'][$placeIdkassy]['externalId'] = $found['id'];
				$oldPlacesCount++;
			}
			$item = array (
 				'id' => ($found['id'] ? $found['id'] : false),
 				'externalId' => ($found['id'] ? $found['id'] : false),
				'name' => $placekassy['Name'],
				'title' => $placekassy['Name'],
				'address' => $placekassy['Addr'],
				'phone' => $placekassy['Phone'],
				'type' => 'empty',
			);
			if (isset($this->options['placeTypes'][$placekassy['IdBldTyp']])) {
				//$item['type'] = array($this->options['placeTypes'][$placekassy['IdBldTyp']]);
			}
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
	 * 'location' => string '71298346' (length=8)
	 * 'date' => string '2012-09-27T18:30:00+0400' (length=24)
	 * 'url' => string 'http://www.kassy.ru/ekb/db/text/832911118.html' (length=47)
	 * 'id' => string '832911118' (length=9)
	 * 'title' => string 'Трамвай "Желание"' (length=31)
	 * 'text' => string 'Теннеси УильямсДрама в 2-х действиях.Продолжительность спектакля 2 часа 40 минут с антрактом.  Знаменитая пьеса великого американского драматурга была написана в 1927 году и имела оглушительный успех. До сих пор ее ставят самые знаменитые театры мира, очень часто пьесу экранизируют'... (length=1771)
	 * 'image' => string 'http://www.kassy.ru/data/159/573/1338/1tramvaj-121x130.jpg' (length=59)
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

		foreach ($this->_kassyEventsData['events'] as $eventIdkassy => &$eventkassy)
		{
			if (empty($eventkassy['Name']) || !isset($eventkassy['DT']) || empty($eventkassy['DT'])){
				continue;
			}

			$found = false;
			foreach ($this->_gporEventsData['events'] as $eventIdGpor => &$eventGpor)
			{
				if ( $this->matchName( $eventkassy['Name'], $eventGpor['title'] ) )
					$found = $eventGpor;

				$fullName = $eventkassy['Name'] . ($eventkassy['Age_restriction'] ? ' ('. $eventkassy['Age_restriction'] . ')' : '');
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
					$kassyPlaceId = isset($this->_kassyEventsData['hallsToPlaces'][$eventkassy['IdHall']]) ? $this->_kassyEventsData['hallsToPlaces'][$eventkassy['IdHall']] : false;
					$placeIdGpor = $this->getGporExternalId( $kassyPlaceId );
					if ($placeIdGpor != $found['placeId'])
						$found = false;
				}

				if ($found)
					break;
			}

			if (!$found)
			{
				// Добавляем новое событие в список gpor, теперь к нему будут добавляться только сеансы
				$this->output( "\tNew event ['".$eventkassy['Name']."']" );
				$this->_gporEventsData['events'][] = $this->sendGporEvent( $eventkassy );
				$newEventsCount++;
			}
			else
			{
				// Сравниваем сеансы
                $kassyDate = (string)$eventkassy['DT'];
				if (!in_array($kassyDate, unserialize($found['seances'])))
				{
					$this->output( "\tNew seance ['".$eventkassy['Name']."'] id=".$found['id']." placeId=".$found['placeId'] );
					$seancesToSend[] = array(
						"id" => $found['id'],
						"seanceTime" => $kassyDate,
					);
					$newSeancesCount++;
				}
				else
				{
					$this->output( "\tFound ['".$eventkassy['Name']."'] id=".$found['id']." placeId=".$found['placeId'] );
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
	 */
	private function getGporExternalId( $placeIdkassy )
	{
		// Если externalId не найден, то это пипец ошибка,
		// значит не все новые события были добавлены на GPOR и надо дебажить код выше 
		if (!isset($this->_kassyEventsData['places'][$placeIdkassy]['externalId'])) {
			print_r($this->_kassyEventsData['places']);
			die();
		}
		return $this->_kassyEventsData['places'][$placeIdkassy]['externalId'];
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


$obj = new AfishaEventskassy($options);
try
{
	$obj->run();
}
catch( Exception $e )
{
	echo( $e );
}

?>