<?php
define ('DS', '/');
include ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
require "weatherImportFunction.php";
$params = require('config.php');

$apiKey = isset($params['apiKey']) ? $params['apiKey'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
$hmnUrl = isset($params['hmnUrl']) ? $params['hmnUrl'] : false;
$hmnCityId = isset($params['hmnCityId']) ? $params['hmnCityId'] : false;


if (!$apiKey)
	die('Error. "apiKey" not found in config.php');
if (!$apiUrl)
	die('Error. "apiUrl" not found in config.php');
if (!$hmnUrl)
	die('Error. "hmnUrl" not found in config.php');
if (!$hmnCityId)
	die('Error. "hmnCityId" not found in config.php');


if ($params['useMeteo']) {
    $meteoMysqlHost = isset($params['meteoMysqlHost']) ? $params['meteoMysqlHost'] : false;
    $meteoMysqlDb = isset($params['meteoMysqlDb']) ? $params['meteoMysqlDb'] : false;
    $meteoMysqlUser = isset($params['meteoMysqlUser']) ? $params['meteoMysqlUser'] : false;
    $meteoMysqlPassword = isset($params['meteoMysqlPassword']) ? $params['meteoMysqlPassword'] : false;


    if (!$meteoMysqlHost)
        die('Error. "meteoMysqlHost" not found in config.php');
    if (!$meteoMysqlDb)
        die('Error. "meteoMysqlDb" not found in config.php');
    if (!$meteoMysqlUser)
        die('Error. "meteoMysqlUser" not found in config.php');
    if (!$meteoMysqlPassword)
        die('Error. "meteoMysqlPassword" not found in config.php');
}


$file = $hmnUrl . '/fact_astro.xml';

$xmldata = file_get_contents($file);

$array = @XML_unserialize($xmldata);

$cities = array();
for ($i = 0; $i < 50; $i++) // kinda retarded shit...
{
    if (isset($array['fact_astro']['c'][$i])) {
        $weather = $array['fact_astro']['c'][$i];
        $city_id = $array['fact_astro']['c'][$i . ' attr']['id'];

        $weather['city_id'] = $city_id;

        $weather['t'] = weather_u2w($weather['t']);
        $weather['tc'] = weather_u2w($weather['tc']);
        $weather['do'] = weather_u2w($weather['do']);

        $cities[$city_id] = ($weather['t']);

        // ????????? ????? ?????? ??????? ??????
        $weather_quick = array();

        list($ico, $text, $weatherStatus) = code_repl($weather['yc'], $weather['cb']);


        $weather_quick['current_temp'] = (string)intval($weather['tf']);
        $weather_quick['current_cond'] = $text;
        $weather_quick['current_ico'] = (string)($code_to_66icon[$ico]);

        if ($city_id == $cityId) {
            if ($params['useMeteo']) {
                mysql_connect($meteoMysqlHost, $meteoMysqlUser, $meteoMysqlPassword);
                mysql_select_db($meteoMysqlDb);
                $query = mysql_query('SELECT timekey,ROUND(`value`) as val FROM sdata WHERE fieldid=0 AND sensorid=9 ORDER BY timekey DESC LIMIT 1');
                $query = mysql_fetch_array($query);
                if ($query['timekey'] < (time() - 60 * 60))
                    $query = false;
            } else {
                $query = false;
            }
            $temperature = $query ? $query['val'] : $weather_quick['current_temp'];
            $time = $query ? ($setOnlyCurrentWeather ? time() : $query['timekey']) : time();

            $client = new xmlrpc_client($apiUrl);
            $client->return_type = 'phpvals';
            $message = new xmlrpcmsg("weather.updateWeather");
            $p0 = new xmlrpcval($apiKey, 'string');
            $message->addparam($p0);

            $p1 = array('temperatureMin' => $temperature, 'temperatureMax' => $temperature, 'time' => $time, 'weatherStatus' => $weatherStatus, 'precipitation' => $weather_quick['current_ico']);
            $p1 = php_xmlrpc_encode($p1);
            $message->addparam($p1);

            $resp = $client->send($message, 0, 'http11');
            if (is_object($resp) && !$resp->errno) {
            }
            else
                echo 'Error setting weather: ' . is_object($resp) ? $resp->errstr : '';
            break;
        }

    }
}

?>