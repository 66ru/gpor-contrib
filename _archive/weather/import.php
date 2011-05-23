<?php
/**
 * ??????? ?? ??????? 66, ???????? ?? ????????????? ??? ??? ? ???
 *
 * @TODO: $url1 ???????? ?? ????????? ?????
 */

define ('DS', '/');
require ('_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
require "weatherImportFunction.php";

$setOnlyCurrentWeather = isset($setOnlyCurrentWeather) ? $setOnlyCurrentWeather : false;

$params = require('config.php');
$cityId = isset($params['weatherCityId']) ? $params['weatherCityId'] : false;
$apiUrl = isset($params['apiUrl']) ? $params['apiUrl'] : false;
$kkey = isset($params['weatherImportKey']) ? $params['weatherImportKey'] : false; // API KEY

if (!$kkey) {
    echo 'Error. "weatherImportKey" not found in config.php';
    die();
}
if (!$cityId) {
    echo 'Error. "weatherCityId" not found in config.php';
    die();
}
if (!$apiUrl) {
    echo 'Error. "apiUrl" not found in config.php';
    die();
}


//$url1 = 'http://news.hmn.ru/news_out/Reclama_66/';
$url1 = 'http://66.ru/.null/weather/';

//$prefix = '66_ru';
$prefix = 'xmls';


if (!$setOnlyCurrentWeather) {
    /**
     *
     * ??? ?????? ??????? ?? ??? 10 ???? + ???????
     *
     */

    $files_days = array(
        $prefix . '/0day_forecast.xml',
        $prefix . '/1day_forecast.xml',
        $prefix . '/2day_forecast.xml',
        $prefix . '/3day_forecast.xml',
        $prefix . '/4day_forecast.xml',
        $prefix . '/5day_forecast.xml',
        $prefix . '/6day_forecast.xml',
        $prefix . '/7day_forecast.xml',
        $prefix . '/8day_forecast.xml',
        $prefix . '/9day_forecast.xml',
        $prefix . '/10day_forecast.xml',
    );

    for ($if = 0; $if < sizeof($files_days); $if++) {
        $file1 = $url1 . $files_days[$if];

        try {
            $file = $file1;
            $xmldata = file_get_contents($file);
        }
        catch (Exception $e) {
            print $e->message();
        }

        $array = XML_unserialize($xmldata);
        $array = $array['forecast'];

        $currDate = date('Y-m-d', strtotime(implode('-', array_values($array['f_provider']['forecast_to_date attr']))));

        $celements = array();
        $tmp = $array['c'];
        for ($i = 0; $i < sizeof($tmp) / 2; $i++) {
            $city_id = $tmp[$i . ' attr']['id'];

            $celements[$city_id] = $tmp[$i];

            $celements[$city_id]['t'] = weather_u2w($celements[$city_id]['t']);
            $celements[$city_id]['tc'] = weather_u2w($celements[$city_id]['tc']);
        }


        foreach ($celements as $city_id => $itemtime) {
            $cities[$city_id] = $itemtime['t'];
            if ($city_id != $cityId)
                continue;

            // ????
            $t = 12;
            $dtime = $currDate . ' ' . ((int)$t < 10 ? '0' . $t : $t) . ':00:00';
            $time = strtotime($dtime);
            $weekday = date('w', $time) + 1;

            $c = getCloudiness($itemtime['dw']);
            $p = getPrecipitation($itemtime['dw']);

            $wind_direct = wind_direct($itemtime['dwd']);

            $client = new xmlrpc_client($apiUrl);
            $client->return_type = 'phpvals';
            $message = new xmlrpcmsg("weather.updateWeather");
            $p0 = new xmlrpcval($kkey, 'string');
            $message->addparam($p0);

            $weatherData =
                    array(
                        'date' => date('Y', $time) . '-' . date('m', $time) . '-' . date('d', $time),
                        'temperatureMin' => $itemtime['td'],
                        'temperatureMax' => $itemtime['td'],
                        'relwetMin' => $itemtime['hum_d'],
                        'relwetMax' => $itemtime['hum_d'],
                        'pressureMin' => $itemtime['pd'],
                        'pressureMax' => $itemtime['pd'],
                        'windMin' => $itemtime['dws'],
                        'windMax' => $itemtime['dws'],
                        'cloudiness' => $c,
                        'precipitation' => $p,
                        'rpower' => 0,
                        'spower' => 0,
                        'windDirection' => $wind_direct,
                        'heatMin' => 20,
                        'heatMax' => 20,
                        'weekDay' => $weekday,
                        'time' => $time,
                    );

            $p1 = php_xmlrpc_encode($weatherData);
            $message->addparam($p1);

            $resp = $client->send($message, 0, 'http11');

            if (is_object($resp) && !$resp->errno) {
            }
            else
                echo 'Error setting weather: ' . is_object($resp) ? $resp->errstr : '';
        }
    }

    /**
     *
     * ??? ??????? ????? ?????? ?? 4 ??? ???????
     *
     */

    $files_3 = array(
        $prefix . '/0day_d_forecast.xml',
        $prefix . '/1day_d_forecast.xml',
        $prefix . '/2day_d_forecast.xml',
        $prefix . '/3day_d_forecast.xml',
    );

    $currentWeatherData = array();

    for ($if = 0; $if < sizeof($files_3); $if++) {
        $file1 = $url1 . $files_3[$if];

        try {
            $file = $file1;

            $xmldata = file_get_contents($file);
        }
        catch (Exception $e) {
            print $e->message();
        }


        $array = XML_unserialize($xmldata);
        $array = $array['forecast'];
        $currDate = date('Y-m-d', strtotime(implode('-', array_values($array['f_provider']['forecast_to_date attr']))));

        $celements = array();
        $tmp = $array['c'];
        for ($i = 0; $i < sizeof($tmp) / 2; $i++) {
            $city_id = $tmp[$i . ' attr']['id'];

            $celements[$city_id] = $tmp[$i];

            $celements[$city_id]['t'] = weather_u2w($celements[$city_id]['t']);
            $celements[$city_id]['tc'] = weather_u2w($celements[$city_id]['tc']);

            $new = array();
            $tmp_ft = $celements[$city_id]['ft'];
            for ($i2 = 0; $i2 < sizeof($tmp_ft) / 2; $i2++) {
                $t = $tmp_ft[$i2 . ' attr']['t'];
                $new[$t] = $tmp_ft[$i2];
            }

            $celements[$city_id]['ft'] = $new;
        }


        foreach ($celements as $city_id => $data) {
            $cities[$city_id] = $data['t'];
            if ($city_id != $cityId)
                continue;

            foreach ($data['ft'] as $t => $itemtime) {
                $t = $t == 24 ? 0 : $t;
                if ($t != 0 && $t != 6 && $t != 12 && $t != 18)
                    continue;
                $dtime = $currDate . ' ' . ((int)$t < 10 ? '0' . $t : $t) . ':00:00';
                $time = strtotime($dtime);
                $weekday = date('w', $time) + 1;
                $wind_direct = wind_direct($itemtime['wd']);

                $c = getCloudiness($itemtime['w']);
                $p = getPrecipitation($itemtime['w']);
                list($ico, $text, $weatherStatus) = code_repl($itemtime['yc'], $itemtime['cb']);

                $client = new xmlrpc_client($apiUrl);
                $client->return_type = 'phpvals';
                $message = new xmlrpcmsg("weather.updateWeather");
                $p0 = new xmlrpcval($kkey, 'string');
                $message->addparam($p0);

                $weatherData =
                        array(
                            'date' => date('Y', $time) . '-' . date('m', $time) . '-' . date('d', $time),
                            'temperatureMin' => $itemtime['tt'],
                            'temperatureMax' => $itemtime['tf'],
                            'relwetMin' => $itemtime['hum'],
                            'relwetMax' => $itemtime['hum'],
                            'pressureMin' => $itemtime['p'],
                            'pressureMax' => $itemtime['p'],
                            'windMin' => $itemtime['ws'],
                            'windMax' => $itemtime['ws'],
                            'cloudiness' => $c,
                            'precipitation' => $p,
                            'weatherStatus' => $weatherStatus,
                            'rpower' => 0,
                            'spower' => 0,
                            'windDirection' => $wind_direct,
                            'heatMin' => 20,
                            'heatMax' => 20,
                            'weekDay' => $weekday,
                            'time' => $time,
                        );

                $p1 = php_xmlrpc_encode($weatherData);
                $message->addparam($p1);

                $resp = $client->send($message, 0, 'http11');
                if (is_object($resp) && !$resp->errno) {
                }
                else
                    echo 'Error setting weather: ' . is_object($resp) ? $resp->errstr : '';
            }
        }
    }

}

/**
 * ??? ??????? ?????? ----------------------------------------------------------------
 */

$file_act = $prefix . '/fact_astro.xml';

$file = $url1 . $file_act;
$xmldata = file_get_contents($file);


$array = @XML_unserialize($xmldata);

$cities = array();
for ($i = 0; $i < 20; $i++)
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
            mysql_connect('FILLME', 'FILLME', 'FILLME');
            mysql_select_db('FILLME');
            $query = mysql_query('SELECT timekey,ROUND(`value`) as val FROM sdata WHERE fieldid=0 AND sensorid=9 ORDER BY timekey DESC LIMIT 1');
            $query = mysql_fetch_array($query);
            if ($query['timekey'] < (time() - 60 * 60))
                $query = false;

            $temperature = $query ? $query['val'] : $weather_quick['current_temp'];
            $time = $query ? ($setOnlyCurrentWeather ? time() : $query['timekey']) : time();

            $client = new xmlrpc_client($apiUrl);
            $client->return_type = 'phpvals';
            $message = new xmlrpcmsg("weather.updateWeather");
            $p0 = new xmlrpcval($kkey, 'string');
            $message->addparam($p0);

            $p1 = array('temperatureMin' => $temperature, 'temperatureMax' => $temperature, 'time' => $time, 'weatherStatus' => $weatherStatus, 'precipitation' => $weather_quick['current_ico']);
            $p1 = php_xmlrpc_encode($p1);
            $message->addparam($p1);

            $resp = $client->send($message, 0, 'http11');
            if (is_object($resp) && !$resp->errno) {
            } else {
                echo 'Error setting weather: ' . is_object($resp) ? $resp->errstr : '';
            }
            break;
        }

    }
}
?>