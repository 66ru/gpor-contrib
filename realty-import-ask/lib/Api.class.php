<?php
include_once get_include_path().'../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc';
include_once get_include_path().'../_lib/xmlrpc-3.0.0.beta/xmlrpcs.inc';
include_once get_include_path().'../_lib/xmlrpc-3.0.0.beta/xmlrpc_wrappers.inc';
global $GLOBALS;
$GLOBALS['xmlrpc_internalencoding']='UTF-8';

abstract class Api {

	protected  $_apiUrl;
	protected  $_apiKey;

	/**
	 * Id застройщика
	 * @var integer
	 */
	protected $developerId;

	/**
	 * Id агентсва недвижимости
	 * @var integer
	 */
	protected $agencyId;

	public $client;

	public function __construct()
	{
		$params = require ('config.php');

		$this->_apiKey = isset($params['apiKey']) ? $params['apiKey'] : false;
		$this->_apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;

		$this->developerId = isset($params['developerId']) ? $params['developerId'] : false;
		$this->agencyId = isset($params['agencyId']) ? $params['agencyId'] : false;

		if (!$this->_apiKey)
		die('Error. "apiKey" not found in config.php');
		if (!$this->_apiUrl)
		die('Error. "apiUrl" not found in config.php');

		if (!$this->developerId)
		die('Error. "developerId" not found in config.php');
		if (!$this->agencyId)
		die('Error. "agencyId" not found in config.php');
		
	}

	protected function createXMLRpc()
	{
		$this->client = new xmlrpc_client($this->_apiUrl);
		$this->client->request_charset_encoding = 'UTF-8';
		$this->client->return_type = 'phpvals';
		//$this->client->setDebug(2);

		//return $client;
	}

	protected function sendXMLRpc(xmlrpcmsg $message)
	{
		$xmlrpcresp = $this->client->send($message, 0, 'http11');

		if(!$xmlrpcresp->faultCode()){
			//удача
			return $xmlrpcresp->val;
		}
		else{
			//неудача
			throw new ErrorException("An error occurred: "." Reason: ".htmlspecialchars($xmlrpcresp->faultString()), htmlspecialchars($xmlrpcresp->faultCode()));
		}

	}
}