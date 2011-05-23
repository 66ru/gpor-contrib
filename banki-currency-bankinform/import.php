<?php
define ('DS', '/');
require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
require 'banksCurrencyFunctions.php';
$params = require ('config.php');

$apiKey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;

if (!$apiKey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');



// Создаем SAX парсер, который будет использоваться для
// обработки XML-данных.
$parser = xml_parser_create();
xml_set_element_handler($parser,'saxStartElement','saxEndElement');
xml_set_character_data_handler($parser,'saxCharacterData');
xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,false);

$ar_banks = array();        // В этом массиве будут банки, полученные из XML файла
$cur_bank = null;    // Текущий банк.
$cur_currency = null; // текущая валюта
$index = null;          // Текущий индекс в массиве

// Получаем содержимое XML-файла с курсами валют банков.
@$xml = join('',file('http://bankinform.ru/services/rates/xml.aspx'));
if (!$xml || empty($xml)) {
	print "Error. Can't get http://bankinform.ru/services/rates/xml.aspx";
	exit();
}

// Производим парсинг полученного XML-файла. получаем массив ar_banks
if (!xml_parse($parser,$xml,true))
	die(sprintf('Ошибка XML: %s в строке %d',
        xml_error_string(xml_get_error_code($parser)),
        xml_get_current_line_number($parser)));
xml_parser_free($parser);
$client = new xmlrpc_client($apiUrl);
$client->return_type = 'xmlrpcvals';
$banks_ids = array();
foreach ($ar_banks as $ar_bank)
{
	$bank_name = $ar_bank['name'];
	$bank_name = trim($bank_name);
	if (isset($banks_bankinform[$bank_name])) {
		$id_banki66 = $banks_bankinform[$bank_name];
	}
	else {
		continue;
	}
	if ($id_banki66 == 0) {
		continue;
	}
    if(empty($ar_bank['USD_buy']) || empty($ar_bank['USD_sale']) || empty($ar_bank['EUR_buy']) || empty($ar_bank['EUR_sale']))
    {
        continue;
    }

	$msg = new xmlrpcmsg('banki.setCurrency');
	$p1 = new xmlrpcval($apiKey, 'string');
	$msg->addparam($p1);
	$p2 = array(
		'id' => $id_banki66,
		'usdBye' => str_replace(',', '.',$ar_bank['USD_buy']),
		'usdSale' => str_replace(',', '.',$ar_bank['USD_sale']),
		'eurBye' => str_replace(',', '.',$ar_bank['EUR_buy']),
		'eurSale' => str_replace(',', '.',$ar_bank['EUR_sale']),
	);
	$p2 = php_xmlrpc_encode($p2);
	$msg->addparam($p2);
    $banks_ids[] = $id_banki66;
	$res =& $client->send($msg, 0, 'http11');

	continue;
}

if(!empty($banks_ids))
{
    $msg = new xmlrpcmsg('banki.cleanCurrency');
    $p1 = new xmlrpcval($apiKey, 'string');
    $msg->addparam($p1);
    $p2 = array(
        'ids' => $banks_ids,
    );
    $p2 = php_xmlrpc_encode($p2);
    $msg->addparam($p2);
    $res =& $client->send($msg, 0, 'http11');
}
?>