<?php
include_once (LIB_PATH.'/xmlrpc-3.0.0.beta/xmlrpc.inc');
include_once (LIB_PATH.'/xmlrpc-3.0.0.beta/xmlrpcs.inc');
include_once (LIB_PATH.'/xmlrpc-3.0.0.beta/xmlrpc_wrappers.inc');
global $GLOBALS;
$GLOBALS['xmlrpc_internalencoding']='UTF-8';

class XmlRpc
{
	protected  $_apiUrl;
	protected  $_apiKey;
	protected  $_apiCommand;
	protected  $_lastError;
	protected  $_response;
	
	public $client;
	

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
	
	public function getLastError ()
	{
		return $this->_lastError;
	}
	
	
	protected function sendXMLRpc(xmlrpcmsg $message)
	{
		$xmlrpcresp = $this->client->send($message, 0, 'http11');
		
		$this->_response = $xmlrpcresp;
		
		if(!$xmlrpcresp->faultCode()){
			if ($xmlrpcresp->errcode)
			{
				$this->_lastError = "An error occurred: "." Reason: ".htmlspecialchars(implode(',', $xmlrpcresp->errors));
				return false;
			}
			return true;
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
					$tmp = array();
					foreach ($param as $k=>$v)
					{
						if (!is_array($v))
						{
							$v = iconv("UTF-8", "UTF-8//IGNORE", $v);
						}
						$tmp[$k] = $v;
					}
					$p = php_xmlrpc_encode($tmp);
				}
				else
				{
					$p = new xmlrpcval(iconv("UTF-8", "UTF-8//IGNORE", $param), 'string');
				}
				$message->addparam($p);
			}
		}
		return $this->sendXMLRpc ($message);
		
	}
}