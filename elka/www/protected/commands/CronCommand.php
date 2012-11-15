<?php
abstract class CronCommand extends CComponent {

	/**
	 * @var string cron period.
	 */
	protected $period;
	
	/**
	 * @var string the service name.
	 */
	protected $name;
	
	/**
	 *
	 * @var string the service title to display in views. 
	 */
	protected $title;
	
	/**
	 * @var EssentialDataProvider the {@link EssentialDataProvider} application component.
	 */
	private $component;
	
	public function init($component, $options = array()) {
		if (isset($component))
			$this->setComponent($component);
	
		foreach ($options as $key => $val)
			$this->$key = $val;
	}
	
	public function getCommandPeriod() {
		return $this->period;
	}
	
	public function getCommandName() {
		return $this->name;
	}
	
	public function getCommandTitle() {
		return $this->title;
	}
	
	public function setComponent($component) {
		$this->component = $component;
	}
	
	public function getComponent() {
		return $this->component;
	}
	
	public function getLogPath ($driver)
	{
		return $this->component->logPath . '/' . $this->getCommandName() . '/' . $driver . '.json';
	}
	
	public function run() {
		$myPid = getmypid();
		$path = $this->component->logPath . '/' . $this->getCommandName();
		$lastLaunchFile = $path . '/lastLaunch.txt';
		$lastLaunchTime = 0;
		if (file_exists($lastLaunchFile))
			$lastLaunchTime = file_get_contents($lastLaunchFile) + 1;

		$lockFile = $path . '/lock.txt';
		if (file_exists($lockFile))
		{
			$pid = file_get_contents($lockFile);
			if (posix_getsid($pid))
			{
				return false;
			}
		}
        $this->saveData($lockFile, $myPid);
		$lastLaunchTime = time();
        $this->runProcess();
		
        $this->saveData($lastLaunchFile, $lastLaunchTime);
		unlink($lockFile);
		return true;
	}

    public function runProcess()
    {
        return true;
    }

	public function saveData ($path, $data)
	{
		$pathinfo = pathinfo($path);
		$tmp = explode('/', $pathinfo['dirname']);
		$tmpPath = '';
		foreach ($tmp as $part)
		{
			if (empty($part))
			{
				$tmpPath = '/';
				continue;
			}
			$tmpPath .= $part . '/';
			if (!is_dir($tmpPath))
			{
				echo $tmpPath;
				if (!mkdir($tmpPath, 0755))
					throw new CronException(Yii::t('auth_backend', 'Can\'t create dir {dir}', array('{dir}' => $tmpPath)), 500);
				else
					chmod($tmpPath, 0755);
			}
		}

		$tmpFile = $path.'.tmp';
		if(!$handle = fopen($tmpFile, 'w+'))
		{
			throw new CronException(Yii::t('auth_backend', 'Can\'t create file {file}', array('{file}' => $tmpFile)), 500);
			return false;
		}
		fwrite($handle, $data);
		fclose($handle);
    	if (file_exists($tmpFile)){
			if (file_exists($path))
				unlink($path);
			copy($tmpFile, $path);
		}
		unlink($tmpFile);

		return true;
	}


	/**
	 * Makes the curl request to the url.
	 * @param string $url url to request.
	 * @param array $options HTTP request options. Keys: query, data, referer.
	 * @param boolean $parseJson Whether to parse response in json format.
	 * @return string the response.
	 */
	public function makeRequest($url, $options = array(), $parseJson = true) {
		$ch = $this->initRequest($url, $options);
		
		if (isset($options['referer']))
			curl_setopt($ch, CURLOPT_REFERER, $options['referer']);
		
		if (isset($options['query'])) {
			$url_parts = parse_url($url);
			if (isset($url_parts['query'])) {
				$old_query = http_build_query($url_parts['query']);
				$url_parts['query'] = array_merge($url_parts['query'], $options['query']);
				$new_query = http_build_query($url_parts['query']);
				$url = str_replace($old_query, $new_query, $url);
			}
			else {
				$url_parts['query'] = $options['query'];
				$new_query = http_build_query($url_parts['query']);
				$url .= '?'.$new_query;
			}					
		}
		
		if (isset($options['data'])) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
		}
		
		if (isset($options['headers']))
		{
			curl_setopt ($ch, CURLOPT_HTTPHEADER, $options['headers']); 			
		}
				
		curl_setopt($ch, CURLOPT_URL, $url);

		$result = curl_exec($ch);
		$headers = curl_getinfo($ch);

		if (curl_errno($ch) > 0)
			throw new CronException(curl_error($ch), curl_errno($ch));
		
		if ($headers['http_code'] != 200) {
			Yii::log(
				'Invalid response http code: '.$headers['http_code'].'.'.PHP_EOL.
				'URL: '.$url.PHP_EOL.
				'Options: '.var_export($options, true).PHP_EOL.
				'Result: '.$result,
				CLogger::LEVEL_ERROR, 'application.extensions.essentialdata'
			);
			throw new CronException('Invalid response http code: '.$headers['http_code'].'.', $headers['http_code']);
		}
		
		curl_close($ch);
				
		if ($parseJson)
			$result = $this->parseJson($result);
		
		return $result;
	}
	
	/**
	 * Initializes a new session and return a cURL handle.
	 * @param string $url url to request.
	 * @param array $options HTTP request options. Keys: query, data, referer.
	 * @param boolean $parseJson Whether to parse response in json format.
	 * @return cURL handle.
	 */
	protected function initRequest($url, $options = array()) {
		$ch = curl_init();		
		//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // error with open_basedir or safe mode
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		return $ch;
	}
		
	/**
	 * Parse response from {@link makeRequest} in json format and check errors.
	 * @param string $response Json string.
	 * @return object result.
	 */
	protected function parseJson($response) {
		try {
			$result = json_decode($response);
			$error = $this->fetchJsonError($result);
			if (!isset($result)) {
				throw new CronException('Invalid response format.', 500);
			}
			else if (isset($error)) {
				throw new CronException($error['message'], $error['code']);
			}
			else
				return $result;
		}
		catch(Exception $e) {
			throw new CronException($e->getMessage(), $e->getCode());
		}
	}
	
	/**
	 * Returns the error info from json.
	 * @param stdClass $json the json response.
	 * @return array the error array with 2 keys: code and message. Should be null if no errors.
	 */
	protected function fetchJsonError($json) {
		if (isset($json->error)) {
			return array(
				'code' => 500,
				'message' => 'Unknown error occurred.',
			);
		}
		else
			return null;
	}


}