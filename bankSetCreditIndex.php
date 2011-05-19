<?php 
define ('DS', '/');

include ('_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$kkey = '3eaba1eee378e5c9eb5d89054d803e8e'; // API KEY

$hhost = isset($params['apiUrl']) ? str_replace('/', '', str_replace('http://', '', $params['apiUrl'])) : null;
$apiUrl = $params['apiUrl'];

if (!$hhost || !$kkey)
	die();

		$stavka_ref = null;
		$error = null;
        $soapClient = new SoapClient("http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?op=MainInfoXML&WSDL");
        try {
	        $result = $soapClient->MainInfoXML();
        } catch (SoapFault $fault) {
            $error = 1;
            echo('Sorry, SOAP ERROR: '.$fault->faultcode."-".$fault->faultstring);
            die();
        }
        if (is_object($result))
	       $result = $result->MainInfoXMLResult->any;
	       
	    if ($result && preg_match('/<stavka_ref.*>(.+)<\/stavka_ref>/', $result, $matches) )
	    	$stavka_ref = $matches[1];
	    
	    if ($stavka_ref !== null)
	    {
			$client = new xmlrpc_client($apiUrl);
			$client->return_type = 'phpvals';
			
			$message = new xmlrpcmsg("banki.setCreditIndex");
	
			$p0 = new xmlrpcval($kkey, 'string');
			$message->addparam($p0);

			$p = array('index' => 'cbrf', 'value' => $stavka_ref);
			$p = php_xmlrpc_encode($p);
			$message->addparam($p);
			
			$resp = $client->send($message, 0, 'http11');
			if (is_object($resp) && !$resp->errno)
				echo 'New index: '.$stavka_ref;
			else
				echo 'Error setting index: '.is_object($resp)?$resp->errstr:'';
	    }
?>