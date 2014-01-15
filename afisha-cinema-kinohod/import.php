<?php 
define('DS', '/');
mb_internal_encoding("UTF-8");
date_default_timezone_set('Asia/Yekaterinburg');
include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
class afishaCinemaKinohodParser
{
	private $params = array (

	'apiUrl'       => '',
	'apiKey'       => '',

	'kApiUrl' 	  => '',
	'kApiKey'	  => '',
	'kPurchaseUrl' => '',

	'kClientApiKey' => '',

	'debug' 	  => false,

	'accessPlaces' => array(
		
		)
	);

	private $places = array();
	private $seances = array();

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

	public function loadPlace($id,$dateString) 
	{
		$url = $this->params['kApiUrl'].$id.'/schedules?date='.$dateString.'&apikey='.$this->params['kApiKey'];
		$headers = get_headers($url);
		if (substr($headers[0], 9, 3) == '200')
		{
			$result = file_get_contents($url);
			$result = json_decode($result,1); 
			return $result;
		}
		else 
			return false;	
	}

	public function run($dateString)
	{
		$this->loadParams();
		$existingMovies = $this->sendData('afisha.listMovies');
		$existingPlaces = $this->sendData("afisha.listPlaces");
		
		// This load cinemas(places) listed in config with its movies with schedules from kinohod
		// and matches it with existing cinemas(places)
		foreach ($this->params['accessPlaces'] as $rPlaceName => $rPlaceId) 
		{
			$tmp = array('ePlaceId'=>0,'data'=>$this->loadPlace($rPlaceId,$dateString));
				
			if ($tmp['data']) 
			{
				$this->places[$rPlaceId] = $tmp;
				foreach ($existingPlaces as $ePlace) 
				{
					if ($this->matchName($tmp['data'][0]['cinema']['title'], $ePlace['name'])) // 0 is here 'cause we take only first movie to get cinema's title
						$this->places[$rPlaceId]['ePlaceId'] = $ePlace['id'];
					if ($ePlace['synonym']) 
						foreach(unserialize($ePlace['synonym']) as $syn)
							if ($this->matchName($tmp['data'][0]['cinema']['title'], $syn))
								$this->places[$rPlaceId]['ePlaceId'] = $ePlace['id'];
				}
			}
			else 
			{
				if($this->params['debug']) echo('Error loading '.$rPlaceName.' @ '.$dateString."\n");	
			}
		}

		//var_dump($existingMovies);

		
		foreach ($this->places as $rPlaceId => $place) 
		{
			foreach ($place['data'] as $rMovieKey => $rMovie) 
			{	
				// This matches loaded movies from each loaded cinema with existing movies
				foreach ($existingMovies as $eMovie) 
				{
					if ($this->matchName($rMovie['movie']['title'],$eMovie['title'])) 
						$this->places[$rPlaceId]['data'][$rMovieKey]['movie']['eMovieId'] = $eMovie['id'];
					if ($this->matchName($rMovie['movie']['originalTitle'],$eMovie['originalTitle'])) 
						$this->places[$rPlaceId]['data'][$rMovieKey]['movie']['eMovieId'] = $eMovie['id'];
					if ($eMovie['synonym'])
						foreach(unserialize($eMovie['synonym']) as $syn)
							if ($this->matchName($rMovie['movie']['title'],$syn))
								$this->places[$rPlaceId]['data'][$rMovieKey]['movie']['eMovieId'] = $eMovie['id'];
				}

				// This prepare seances to send
				foreach ($rMovie['schedules'] as $seance) {
					$newSeance = array();
					$startTime = strtotime($seance['startTime']);
					$newSeance['seanceTime'] = $startTime;
					$newSeance['placeId'] = $place['ePlaceId'];
					$newSeance['movieId'] = $this->places[$rPlaceId]['data'][$rMovieKey]['movie']['eMovieId'];
					if ($seance['isSaleAllowed'])
						$newSeance['purchaseLink'] = $this->params['kPurchaseUrl'].$seance['id'].'?apikey='.$this->params['kClientApiKey'];
					else
						$newSeance['purchaseLink'] = '';
					$this->seances[] = $newSeance;
				} 

			}
		}

		$seances = $this->seances;

		// Seance sending
		if(sizeof($seances)) {
			for($i = 0; $i<sizeof($seances);$i += 250){
				if($this->params['debug']) echo "afisha.postSeances " .$i . " - " . min(sizeof($seances),($i+250)) . " of total " . sizeof($seances) ."\n";
				$this->sendData('afisha.postSeances',array_slice($seances,$i,250));
			}
		}
	}

	
	private function getFileData($name, $params = array())
	{
		
		$filedata = file_get_contents($this->getUrl($name, $params));
		if(!$filedata) return false;
		
		return json_decode($filedata, 1);
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
		if (($a == $b)&&($a!='')) return true;
		return false;
	}
}


for ($i=0;$i<7;$i++) //for a week
{
	$p = new afishaCinemaKinohodParser();
	$p->run(date('dmY', strtotime("+".$i." days")));	
}
