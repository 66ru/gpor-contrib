<?php
/**
 * Created by PhpStorm.
 * User: hamway
 * Date: 18.02.14
 * Time: 14:35
 */
class FeedXmlRpc {

	public $client;

	protected $_apiUrl;
	protected $_apiKey;
	protected $_apiCommand;
	protected $_lastError;
	protected $_response;
	protected $_responseVal;

	public function __construct($apiUrl = false, $apiKey = false, $apiCommand = false)
	{
		$this->_apiKey = $apiKey;
		$this->_apiUrl = $apiUrl;
		$this->_apiCommand = $apiCommand;
	}

	protected function createXMLRpc()
	{
		$this->client = new xmlrpc_client($this->_apiUrl);
		$this->client->request_charset_encoding = 'UTF-8';
		$this->client->return_type = 'phpvals';
		//$this->client->setDebug(2);
		//return $client;
	}

	public function setApiUrl ($url)
	{
		$this->_apiUrl = $url;
		return true;
	}

	public function setApiKey ($key)
	{
		$this->_apiKey = $key;
		return true;
	}

	public function setApiCommand ($command)
	{
		$this->_apiCommand = $command;
		return true;
	}

	public function getApiUrl ()
	{
		return $this->_apiUrl;
	}

	public function getApiKey ()
	{
		return $this->_apiKey;
	}

	public function getApiCommand ()
	{
		return $this->_apiCommand;
	}

	public function getResponse ()
	{
		return $this->_response;
	}

	public function getResponseVal ()
	{
		return $this->_responseVal;
	}

	public function getLastError ()
	{
		return $this->_lastError;
	}

	protected function sendXMLRpc(xmlrpcmsg $message)
	{
		$xmlrpcresp = $this->client->send($message, 0, 'http11');

		$this->_response = $xmlrpcresp;

		if(!$xmlrpcresp->faultCode()){
			$val = $xmlrpcresp->val;
			if (isset($val['error']) && $val['error'])
			{
				$this->_lastError = "An error occurred: "." Reason: ".htmlspecialchars($val['error']);
				return false;
			}
			//$this->_responseVal = self::convertToCp1251($xmlrpcresp->val);
			$this->_responseVal = $xmlrpcresp->val;
			return $this->_responseVal;
		}
		else{
			$this->_lastError = "An error occurred: "." Reason: ".htmlspecialchars($xmlrpcresp->faultString());
			return false;
		}

	}

	public function send ($params = array())
	{
		$this->_lastError = '';
		$this->_response = '';

		$this->createXMLRpc();

		$message = new xmlrpcmsg($this->_apiCommand);
		$p0 = new xmlrpcval($this->getApiKey (), 'string');
		$message->addparam($p0);

		if ($params)
		{
			foreach ($params as $param)
			{
				if (is_array($param))
				{
					//$param = self::convertHtml ($param);
					$p = php_xmlrpc_encode($param);
				}
				elseif (is_object($param))
				{
					//$param = self::convertHtml ($param);
					$p = php_xmlrpc_encode($param);
				}
				else
				{
					if (is_numeric($param))
						$p = new xmlrpcval($param, 'int');
					else
						$p = new xmlrpcval($param, 'string');
				}
				$message->addparam($p);
			}
		}
		return $this->sendXMLRpc ($message);

	}
}