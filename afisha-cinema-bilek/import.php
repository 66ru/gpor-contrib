<?php
/**
 * User: bazilio
 * Date: 19.03.12
 * Time: 16:22
 */
define('DS', '/');
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
		$this->params = array_merge($this->params, include 'config-dist.php');

		foreach ($this->params as $key => $param) {
			if (empty($param)) {
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

			preg_match('/\n(.+?), (\d\d\d\d), (\d+) мни.$/', trim(strip_tags($movie['text'])), $out);

			if (isset($out[1])) $movie['country'] = $out[1];
			if (isset($out[3])) $movie['timelength'] = $out[3];

			preg_match('#src=\'(.+?)\'#sim', $movie['logo'], $logoUrl);
			if (isset($logoUrl[1])) {
				$ext  = pathinfo($logoUrl[1], PATHINFO_EXTENSION);
				$path = $this->getFilePath('image',
					array(
					     'id'  => $movieId,
					     'ext' => $ext
					));
				if (!is_file($path))
					copy($logoUrl[1], $path);
				$movie['logoPath'] = $path;
			}
			$this->movieStack[$movieId] = $movie;
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
		} else {
			$filedata = file_get_contents($this->getUrl($name, $params));
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
}


$p = new afishaCinemaBilekParser();
$p->run();