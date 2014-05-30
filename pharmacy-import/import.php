<?php

require ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
$params = require('config.php');

foreach ($params as $key => $value) {
    if ($value == '')
        die('Param "' . $key . '"  not found in config.php. See config-dist.php.' . PHP_EOL);
}
// check mysql connect
$db = $params['mysqlDBName'];
$status = mysql_connect($params['mysqlHost'], $params['mysqlUser'], $params['mysqlPassword']);
if (!$status) {
    die('Failed to connect to mysql server, check config.php' . PHP_EOL);
}
mysql_select_db($db);

// copy sql dump from ftp to local path
$path = dirname(__FILE__) . '/export/';
if (!is_dir($path)) {
    mkdir($path, 0777, true);
}
$zipPath = $path . 'export.zip';

$status = copy($params['ftpExport'], $zipPath);
if ($status) {
    unlink($params['ftpExport']);

    // Unzip and import sql dump to local DB
    shell_exec('/usr/bin/unzip -d ' . $path . ' -o ' . $zipPath);
    unlink($zipPath);

    foreach (glob($path . '*.sql') as $file) {
        shell_exec('/usr/bin/mysql -f -h ' . $params['mysqlHost'] . ' -u ' . $params['mysqlUser'] . ' --password=' . $params['mysqlPassword'] . ' ' . $params['mysqlDBName'] . ' < ' . $file);
        unlink($file);
    }
}

// Now let's parse xml feeds
foreach ($params['feedList'] as $name => $feed) {
    $status = parseFeed($feed);
    if (!$status) {
        print 'Error parse feed - ' . $name . PHP_EOL;
    }
}

function parseFeed($feed)
{
    global $db;
    $xml = simplexml_load_file($feed['url']);
    if (!$xml) {
        return false;
    }

    $simple = new SimpleXMLElement($xml->asXML());
    $apt_ids_list = array();
    foreach ($simple->Position as $line) {
        $apt_id = (string) $line['IdApt']; 

        $scode = mysql_query("SELECT apt_code FROM {$db}.apts WHERE apt_code = '{$apt_id}';");
        $apt_ids_list[] = $apt_id;

        if($scode) {
            $apt_scode_id = $apt_id;
            $apt_dcode_id = (string)$line['Code086'];
            $prep_id = (string)$line['IdPrep'];
            $url = ($feed['byeLinkPrefix'] ? $feed['byeLinkPrefix'] . $prep_id : null);
            $prep = mysql_query("SELECT price, url 
                FROM {$db}.aptdrugpresent 
                WHERE scode = '{$apt_scode_id}' AND dcode = '{$apt_dcode_id}';");
            $apt_prep_old = mysql_fetch_assoc($prep);

            $rub = '0';
            $kop = '00';

            if (preg_match('/(\d*)\.?(\d{1,2})?/', (string)$line['PricePrep'], $m)) {
                $rub = $m[1];
                if (isset($m[2])) {
                    $kop = str_pad($m[2], 2 , "00");
                }
            }

            $apt_price = $rub . $kop;

            $sql = false;
            if($apt_prep_old && $apt_prep_old['price']) {
                if ($apt_prep_old['price'] != $apt_price || $apt_prep_old['url'] != $url) {
                    $sql = "UPDATE {$db}.aptdrugpresent 
                        SET price = '{$apt_price}', url = '{$url}' 
                        WHERE scode = '{$apt_scode_id}' AND dcode = '{$apt_dcode_id}'";
                }
            } else {
                $sql = "INSERT INTO {$db}.aptdrugpresent 
                    VALUES ('{$apt_dcode_id}', '{$apt_scode_id}', 0, 1, '{$apt_price}', NOW(), '.', '', 1, 0, '', 4, '{$url}') ";
            }

            if ($sql) {
                mysql_query($sql);
            }
        }
    }

    $apt_ids_list = array_unique($apt_ids_list);
    foreach ($apt_ids_list as $apt_id) {
        $feedUrl = makeProductsJSON($apt_id, $feed['byeLinkPrefix'], $feed['reserveLinkPrefix']);
        $result = mysql_query("SELECT * FROM {$db}.apts WHERE `apt_code`=" . $apt_id);
        if (!$result) {
            continue;
        }

        $apt = mysql_fetch_assoc($result);
        $apt_array = array(
            'code' => $apt['apt_code'],
            'name' => iconv('windows-1251', 'UTF-8', $apt['apt_short']),
            'address' => iconv('windows-1251', 'UTF-8', $apt['apt_add']),
            'phones' => iconv('windows-1251', 'UTF-8', $apt['phone']),
            'worktime' => parseWorktime($apt),
            'feed' => $feedUrl
        );
        sendDrugstoreToGpor($apt_array);
    }

    return true;
}

function sendDrugstoreToGpor($apt)
{
    global $params;

    $client = new xmlrpc_client($params['apiUrl']);
    $client->return_type = 'phpvals';

    $message = new xmlrpcmsg('pharmacy.postDrugstore');
    $p0 = new xmlrpcval($params['apiKey'], 'string');
    $message->addparam($p0);

    $p1 = php_xmlrpc_encode($apt);
    $message->addparam($p1);

    $resp = $client->send($message, 0, 'http11');

    if (is_object($resp) && $resp->errno)
        die('Error uploading data: ' . $resp->errstr . PHP_EOL);
    print 'Drugstore ' . $apt['code'] . ' uploaded' . PHP_EOL;
}

function makeProductsJSON($apt, $byeLinkPrefix, $reserveLinkPrefix)
{
    global $db, $params;

    // fetching drugs
    $result = mysql_query("SELECT * FROM `aptdrugpresent` `adp`
        INNER JOIN `drug_list` `dl` ON `adp`.`dcode` = `dl`.`drug_code`
        WHERE `adp`.`scode`=" . $apt['apt_code']);

    if (!$result || !mysql_num_rows($result)) {
        return false;
    }

    // make feed
    $product_list = array();
    while ($row = mysql_fetch_assoc($result)) {
        $product_list[] = array(
            'drug_code' => $row['dcode'],
            'price' => $row['price'],
            'byeLink' => $byeLinkPrefix . $row['dcode'],
            'reserveLink' => $reserveLinkPrefix . $row['dcode'],
            'updated' => $row['cdate']
        );
    }

    // save drugs feed
    $filename = 'pharmacyFeed_' . $apt['apt_code'] . '.json';
    $path = $params['jsonPath'] . $filename;
    $url = $params['jsonUrl'] . $filename;
    file_put_contents($path, json_encode($product_list));

    return $url;
}

function parseWorktime($apt)
{
    $aroundWord = iconv('UTF-8', 'windows-1251', 'круглосуточно');
    if ($apt['week'] == $aroundWord && $apt['saturday'] == $aroundWord && $apt['sunday'] == $aroundWord) {
        return array('aroundTheClock' => 'on');
    }

    $result = array();

    // parse week
    $wt = preg_replace('/[^0-9-:]/', '', $apt['week']);
    if (strpos($wt, '-') && strpos($wt, ':')) {
        $arr = explode('-', $wt);
        if (isset($arr[0]) && isset($arr[1])) {
            $tmp = array('from' => trim($arr[0]), 'to' => trim($arr[1]));
            $result = array_fill(1, 5, $tmp);
        }
    }

    // parse weekend
    $wt = preg_replace('/[^0-9-:]/', '', $apt['saturday']);
    if (strpos($wt, '-') && strpos($wt, ':')) {
        $arr = explode('-', $wt);
        if (isset($arr[0]) && isset($arr[1])) {
            $result[6] = array('from' => trim($arr[0]), 'to' => trim($arr[1]));
        }
    }

    $wt = preg_replace('/[^0-9-:]/', '', $apt['sunday']);
    if (strpos($wt, '-') && strpos($wt, ':')) {
        $arr = explode('-', $wt);
        if (isset($arr[0]) && isset($arr[1])) {
            $result[7] = array('from' => trim($arr[0]), 'to' => trim($arr[1]));
        }
    }

    return $result;
}