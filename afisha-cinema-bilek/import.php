<?php
/**
 * User: bazilio
 * Date: 19.03.12
 * Time: 16:22
 */
define('DS', '/');
mb_internal_encoding("UTF-8");
include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
class afishaCinemaBilekParser
{
	private $params = array(
		'root'     => '',
		'debug'    => false,
		'url'      => '',
		'urls'     => array(
			'performances' => 'http://bilektron.org/api/afisha.php?act=performances&referal=r66',
			'movie'        => 'http://bilektron.org/api/afisha.php?act=filmdesc&referal=r66&filmId={id}',
			'image'        => '{url}{filesDir}/images/{id}.{ext}',
			'purchaseLink' => 'http://bilektron.org/{seanceId}/',
		),
		'file'     => array(
			'performances' => 'performances.json',
			'movie'        => 'movie_{id}.json',
			'image'        => 'images/{id}.{ext}',
		),
		'filesDir' => 'files',
	);

	private $movieStack = array();
	private $places = array();

	private function loadParams()
	{
		if (!is_file('config.php')) {
			echo "missing config.php";
			die;
		}
		$this->params = array_merge($this->params, include 'config.php');

		foreach ($this->params as $key => $param) {
			if (!isset($param)) {
				echo 'missing ' . $key . 'param in config file';
				die;
			}
		}
	}

	public function run()
	{
		$this->loadParams();

		$this->places = $this->getFileData('performances');

		foreach ($this->places as $placeId => $place) {

			foreach ($place['performances'] as $eventId => $event) {
				$this->movieStack[(int)$event['filmId']]                  = array();
				$date                                                     = date_parse($event['startTime']);
				$this->places[$placeId]['performances'][$eventId]['time'] = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
			}

		}

		foreach ($this->movieStack as $movieId => $movie) {
			$movie = array_merge($movie, $this->getFileData('movie', array('id' => $movieId)));

			preg_match('/\n(.+?), (\d\d\d\d), (\d+) мин\.$/', trim(strip_tags($movie['text'])), $out);
			$movie['text'] = preg_replace('/\n(.+?), (\d\d\d\d), (\d+) мин\./', '', $movie['text']);

			if (isset($out[1])) $movie['country'] = $out[1];
			if (isset($out[3])) $movie['duration'] = $out[3];

			preg_match('#src=\'(.+?)\'#sim', $movie['logo'], $logoUrl);
			if (isset($logoUrl[1])) {
				$ext  = pathinfo($logoUrl[1], PATHINFO_EXTENSION);
				$path = $this->getFilePath('image',
					array(
					     'id'  => $movieId,
					     'ext' => $ext
					));
				/*if (!is_file($path))
					copy($logoUrl[1], $path);
				$movie['logoUrl'] = $this->getUrl('image',
					array(
					     'id'       => $movieId,
					     'ext'      => $ext,
					     'url'      => $this->params['url'],
					     'filesDir' => $this->params['filesDir'],
					));*/
				$movie['logoUrl'] = $logoUrl[1];
			}
			$this->movieStack[$movieId] = $movie;
		}
		if($this->params['debug']) echo "afisha.listPlaces\n";
		$remotePlaces = $this->sendData("afisha.listPlaces");
		$placesToSend = array();
		foreach ($this->places as $placeId => $place) {
			foreach ($remotePlaces as $rplaceId => $rplace) {
				if ($this->matchName($place['name'] , $rplace['name'])) $place['found'] = $rplaceId;
				if($rplace['synonym'])
					foreach(unserialize($rplace['synonym']) as $syn)
						if ($this->matchName($place['name'] , $syn)) $place['found'] = $rplaceId;
			}
			if (isset($place['found'])) {
				$this->places[$placeId]['remoteId'] = $place['found'];
			} else {
				$p = $place;
				unset($p['performances']);
				$placesToSend[] = $p;
			}
		}
		if (sizeof($placesToSend)) {
			if($this->params['debug']) echo "afisha.postPlace\n";
			$this->sendData('afisha.postPlace', $placesToSend);
		}

		if($this->params['debug']) echo "afisha.listPlaces\n";
		$remotePlaces = $this->sendData("afisha.listPlaces");

		foreach ($remotePlaces as $rplaceId => $rplace) {
			foreach ($this->places as $placeId => $place) {
				if ($this->matchName($place['name'], $rplace['name'])) $place['remoteId'] = $rplaceId;
				if ($this->matchName($place['name'], $rplace['synonym'])) $place['remoteId'] = $rplaceId;
				$this->places[$placeId] = $place;
				if (!isset($place['remoteId'])) unset($this->place[$placeId]);
			}
		}

		if($this->params['debug']) echo "afisha.listMovies\n";
		$exrternalMovies = $this->sendData('afisha.listMovies');
		$moviesToSend    = array();
		foreach ($this->movieStack as $movieId => $movie) {
			foreach ($exrternalMovies as $eMovieId => $eMovie) {
				if ($this->matchName($movie['name'], $eMovie['title'])) $movie['remoteId'] = $eMovieId;
				if ($this->matchName($movie['name'], $eMovie['originalTitle'])) $movie['remoteId'] = $eMovieId;
				if($eMovie['synonym'])
					foreach(unserialize($eMovie['synonym']) as $syn)
						if ($this->matchName($movie['name'], $syn)) $movie['remoteId'] = $eMovieId;
			}
			if (!isset($movie['remoteId']) || (isset($movie['remoteId']) && !empty($movie['edited']) && $movie['edited'] == '0')) {
				$moviesToSend[] = $movie;
			} else {
				$this->movieStack[$movieId] = $movie;
			}
		}

		if (sizeof($moviesToSend)) {
			if($this->params['debug']) echo "afisha.postMovie\n";
			$p = $this->sendData('afisha.postMovie', $moviesToSend);
			if ($exrternalMovies && $p) {
				$exrternalMovies = array_merge($exrternalMovies, $p);
				foreach ($this->movieStack as $movieId => $movie) {
					foreach ($exrternalMovies as $eMovieId => $eMovie) {
						if ($this->matchName($movie['name'], $eMovie['title'])) $movie['remoteId'] = $eMovie['id'];
						if ($this->matchName($movie['name'], $eMovie['originalTitle'])) $movie['remoteId'] = $eMovie['id'];
						if($eMovie['synonym'])
							foreach(unserialize($eMovie['synonym']) as $syn)
								if ($this->matchName($movie['name'], $syn)) $movie['remoteId'] = $eMovie['id'];
					}
					if (!isset($movie['remoteId'])) {
						unset($this->movieStack[$movieId]);
					} else {
						$this->movieStack[$movieId] = $movie;
					}
				}
			}
		}

		$seances = array();
		foreach($this->places as $placeId => $place){
			foreach ($place['performances'] as $eventId => $event) {
				if(isset($this->movieStack[(int)$event['filmId']]['remoteId'])) {
					$seances[$eventId] = array(
						'movieId' => $this->movieStack[(int)$event['filmId']]['remoteId'],
						'placeId' => $place['remoteId'],
						'purchaseLink' => $this->getUrl('purchaseLink', array('seanceId' => $event['id'])),
						'seanceTime' => $event['time'],
					);
				}
			}
		}

		if(sizeof($seances)) {
			for($i = 0; $i<sizeof($seances);$i += 250){
				if($this->params['debug']) echo "afisha.postSeances " .$i . " - " . min(sizeof($seances),($i+250)) . " of total " . sizeof($seances) ."\n";
				$this->sendData('afisha.postSeances',array_slice($seances,$i,250));
			}
		}
	}

	/*
		 * Универсальная функция получения данных, сначала при включеном debug проверяет наличие файла в fs, потом забирает по url
		 * @return array
		 * */
	private function getFileData($name, $params = array())
	{
		if ($this->params['debug'] && is_file($this->getFilePath($name, $params))) {
			$filedata = file_get_contents($this->getFilePath($name, $params));
			if(!$filedata) return false;
		} else {
			$filedata = file_get_contents($this->getUrl($name, $params));
			if(!$filedata) return false;
			$fn       = fopen($this->getFilePath($name, $params), 'w+');
			fwrite($fn, $filedata);
			fclose($fn);
		}

		return json_decode($filedata, 1);
	}

	private function getFilePath($name, $params = array())
	{
		if (!array_key_exists($name, $this->params['file'])) return false;
		$tpl = $this->params['file'][$name];

		foreach ($params as $key => $param) {
			$tpl = preg_replace('|{' . $key . '}|', $param, $tpl);
		}

		return $this->params['root'] . $this->params['filesDir'] . DS . $tpl;
	}

	private function getUrl($name, $params = array())
	{
		if (!array_key_exists($name, $this->params['urls'])) return false;

		$tpl = $this->params['urls'][$name];

		foreach ($params as $key => $param) {
			$tpl = preg_replace('|{' . $key . '}|', $param, $tpl);
		}
		return $tpl;
	}

	/*
	 * $data = array('string' => 'abc', 'struct' => array())
	 * */


	public function sendData($name, $params = array())
	{
		$client                           = new xmlrpc_client($this->params['apiUrl']);
		$client->request_charset_encoding = 'UTF-8';
		$client->return_type              = 'phpvals';
		$client->debug                    = 0;
		$msg                              = new xmlrpcmsg($name);
		$p1                               = new xmlrpcval($this->params['apiKey'], 'string');
		$msg->addparam($p1);

		if ($params) {
			$p2 = php_xmlrpc_encode($params);
			$msg->addparam($p2);
		}
		$client->accepted_compression = 'deflate';
		$res                          = $client->send($msg, 60 * 5, 'http11');
		if ($res->faultcode()) {
			print "An error occurred: ";
			print " Code: " . htmlspecialchars($res->faultCode());
			print " Reason: '" . htmlspecialchars($res->faultString()) . "' \n";
			die;
		} else
			return $res->val;
	}

	private function matchName($a, $b)
	{
		$a = mb_strtolower($a);
		$b = mb_strtolower($b);
		$a = preg_replace('|[^\p{L}\p{Nd}]|u', '', $a);
		$b = preg_replace('|[^\p{L}\p{Nd}]|u', '', $b);
		if ($a == $b) return true;
		return false;
	}
}


$p = new afishaCinemaBilekParser();
$p->run();