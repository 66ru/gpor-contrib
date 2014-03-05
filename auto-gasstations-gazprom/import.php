<?php

require ('lib/simple_html_dom.php');
require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

$apiKey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
$importUrl = isset($params['importUrl']) ? $params['importUrl'] : false;
$imgUrlPrefix = isset($params['imgUrlPrefix']) ? $params['imgUrlPrefix'] : false;
$jsonPath = isset($params['jsonPath']) ? $params['jsonPath'] : false;
$jsonUrl = isset($params['jsonUrl']) ? $params['jsonUrl'] : false;

if (!$apiKey || !$apiUrl || !$importUrl || !$imgUrlPrefix || !$jsonPath || !$jsonUrl) {
    die('Some required params not found in config.php. See config-dist.php.');
}

$dataDir = $jsonPath;
if (!file_exists($dataDir)) {
    if (!mkdir($dataDir, 0777)) {
        die('Can\'t create folder ' . $dataDir);
    }
}

$html = file_get_html($importUrl);
if (!$html) {
    die('Error loading URL: ' . $importUrl . PHP_EOL);
}

// Parsing fuel types
$fuelTypes = parseFuelTypes($html);
// Parsing services
$serviceTypes = parseServiceTypes($html, $imgUrlPrefix);
// Parsing stations
$stations = parseStations($html, $fuelTypes, $serviceTypes);
// Set id field to array keys
$fuelTypes = setFieldValueToArrayKey($fuelTypes);
$serviceTypes = setFieldValueToArrayKey($serviceTypes);

$html->clear();
unset($html);

// Write data to json
$filesToSend = array();
if (!empty($fuelTypes) && !empty($serviceTypes)) {
    $infoFilename = $dataDir . 'gazpromInfo.json';
    file_put_contents($infoFilename, json_encode(array(
        'fuelTypes' => $fuelTypes,
        'serviceTypes' => $serviceTypes
    )));
    $filesToSend['info']['name'] = '/json/auto/gazpromInfo.json';
    $filesToSend['info']['path'] = $jsonUrl . 'gazpromInfo.json';
}
if (!empty($stations)) {
    $stationsFilename = $dataDir . 'gazpromStations.json';
    file_put_contents($stationsFilename, json_encode($stations));
    $filesToSend['stations']['name'] = '/json/auto/gazpromStations.json';
    $filesToSend['stations']['path'] = $jsonUrl . 'gazpromStations.json';
}

// Send data to gpor
foreach($filesToSend as $file) {
    $client = new xmlrpc_client($apiUrl);
    $client->return_type = 'phpvals';

    $message = new xmlrpcmsg("admin.setCustomFile");
    $p0 = new xmlrpcval($apiKey, 'string');
    $message->addparam($p0);

    $customFileData = array(
        'name' => $file['name'],
        'contentType' => 'text/json',
        'dataFile' => $file['path']
    );
    $p1 = php_xmlrpc_encode($customFileData);
    $message->addparam($p1);

    $resp = $client->send($message, 0, 'http11');
    if (!is_object($resp))
        die('fatal error. returned raw data:' . $resp->raw_data);
    if (is_object($resp) && $resp->errno)
        die('Error uploading data: ' . $resp->errstr);
}

/**
 * Парсит типы топлива
 * @param simple_htlm_dom $html
 * @return array
*/
function parseFuelTypes($html) {
    $fuelTypes = array();
    foreach($html->find('#FUEL', 0)->find('option') as $el) {
        $title = trim($el->innertext);
        $fuelTypes[$title] = array('id' => $el->value, 'title' => $title);
    }

    return $fuelTypes;
}

/**
 * Парсит услуги (доступные фильтры)
 * @param simple_html_dom $html
 * @param string $imgUrlPrefix
 * @return array
*/
function parseServiceTypes($html, $imgUrlPrefix) {
    $serviceTypes = array();
    foreach ($html->find('.param_azs_bar', 0)->find('div.param') as $el) {
        $title = trim($el->find('span', 0)->plaintext);
        $img = $el->find('img', 0)->src;

        $serviceTypes[$title]['title'] = $title;
        $serviceTypes[$title]['img'] = $imgUrlPrefix . $img;
    }

    // Prasing service ids
    foreach ($html->find('#PARAM_AZS', 0)->find('option') as $el) {
        $title = trim($el->plaintext);
        $id = $el->value;

        $serviceTypes[$title]['id'] = $id;
    }

    return $serviceTypes;
}

/**
 * Парсит заправки
 * @param simple_html_dom $html
 * @param array $fuelTypes
 * @param array $serviceTypes
 * @return array
*/
function parseStations($html, $fuelTypes, $serviceTypes) {
    $stations = array();
    foreach ($html->find('.azs_container') as $i => $infoWrapper) {
        // id and title
        $title = trim($infoWrapper->find('span[id^="azs_number"]', 0)->plaintext);
        $id = filter_var($title, FILTER_SANITIZE_NUMBER_INT);

        $stations[$i] = array('id' => $id, 'title' => $title);
        // address
        $address = trim($infoWrapper->find('div[id^="azs_address"]', 0)->plaintext);
        $stations[$i]['address'] = $address;
        // gps coords
        $gps = trim(str_replace('GPS:', '', $infoWrapper->find('div.pt10', 0)->find('nobr', 0)->plaintext));
        $stations[$i]['gps'] = explode(' ', $gps);
        // fuel types
        $stations[$i]['fuelTypes'] = array();
        foreach ($infoWrapper->find('div.pt10', 1)->find('div.DinPro') as $el) {
            $fuel = trim($el->plaintext);
            if (isset($fuelTypes[$fuel])) {
                $stations[$i]['fuelTypes'][] = $fuelTypes[$fuel]['id'];
            }
        }
        // services
        $stations[$i]['services'] = array();
        foreach ($infoWrapper->find('div.serviceIcons', 0)->find('img') as $el) {
            $service = $el->title;
            if (isset($serviceTypes[$service])) {
                $stations[$i]['services'][] = $serviceTypes[$service]['id'];
            }
        }
    }

    return $stations;
}

/**
 * Устанавливает значение поля $field ключем элемента массива
 * @param array $arr
 * @param string $field = 'id'
 * @return array
*/
function setFieldValueToArrayKey($arr, $field = 'id')
{
    foreach ($arr as $key => $el) {
        $arr[$el[$field]] = $el;
        unset($arr[$key]);
    }
    
    return $arr;
}