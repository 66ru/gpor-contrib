<?php

mb_internal_encoding("UTF-8");
include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');

class PharmacyImport
{
    // Лимиты на отправку данных
    const PRODUCTS_LIMIT = 100;
    const DRUGSTORE_LIMIT = 100;

    private $params = array(
        'apiUrl' => '',
        'apiKey' => '',
        'ftpExport' => '',
        'feedList' => array(),
        'rubricJSONUrl' => '',

        'jsonPath' => '',
        'jsonUrl' => '',

        'mysqlHost' => '',
        'mysqlUser' => '',
        'mysqlPassword' => '',
        'mysqlDBName' => '',

        'debug' => false
    );

    /**
     * Имя БД
     * @var string
    */
    private $db = false;

    /**
     * Загрузка параметров и проверка соединения с БД
    */
    private function init()
    {
        if (!is_file('config.php'))
            die('missing config.php');
        $this->params = array_merge($this->params, include 'config.php');

        foreach ($this->params as $key => $param) {
            if ($param === '')
                die('Param "' . $key . '"  not found in config.php. See config-dist.php.' . PHP_EOL);
        }

        $this->db = $this->params['mysqlDBName'];
        $status = mysql_connect($this->params['mysqlHost'], $this->params['mysqlUser'], $this->params['mysqlPassword']);
        if (!$status) {
            die('Failed to connect to mysql server, check config.php' . PHP_EOL);
        }
        mysql_select_db($this->db);
    }

    /**
     * Запуск импорта
    */
    public function run()
    {
        $this->init();

        $imported = $this->importSQLDump();

        // Отправляем все данные на гпор только если пришел новый файл экспорта
        if ($imported) {
            $this->sendDataToGpor();
        }

        foreach ($this->params['feedList'] as $name => $feed) {
            $status = $this->parseFeed($feed);
            if (!$status && $this->params['debug']) {
                print 'Error parse feed - ' . $name . PHP_EOL;
            }
        }
    }

    /**
     * Импорт дампа БД по фтп и разворачивание на локальной бд
     * @return bool
    */
    private function importSQLDump()
    {
        $path = dirname(__FILE__) . '/export/';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $zipPath = $path . 'export.zip';

        $status = copy($this->params['ftpExport'], $zipPath);
        if ($status) {
            unlink($this->params['ftpExport']);

            // Unzip and import sql dump to local DB
            shell_exec('/usr/bin/unzip -d ' . $path . ' -o ' . $zipPath);
            unlink($zipPath);

            foreach (glob($path . '*.sql') as $file) {
                shell_exec('/usr/bin/mysql -f -h ' . $this->params['mysqlHost'] . ' -u ' . $this->params['mysqlUser'] . ' --password=' . $this->params['mysqlPassword'] . ' ' . $this->params['mysqlDBName'] . ' < ' . $file);
                unlink($file);
            }
        }

        return $status;
    }

    /**
     * Отправляет все аптеки и лекарства по АПИ на гпор
    */
    private function sendDataToGpor()
    {
        // Отправляем рубрики
        $this->sendRubrics();
        return;
        
        // Отправляем лекарства
        $offset = 0;
        do {
            $result = mysql_query("SELECT `drug_code`, `phr_group_code`, `drug_name`, `drug_name_lat`, `opis`, `_updated` FROM {$this->db}.drug_list LIMIT {$offset}, " . self::PRODUCTS_LIMIT);
            $product_list = array();
            while ($row = mysql_fetch_assoc($result)) {
                $product_list[$row['drug_code']] = array(
                    'code' => (int)$row['drug_code'],
                    'rubric_code' => (int)$row['phr_group_code'],
                    'name' => mb_convert_encoding($row['drug_name'], 'UTF-8', 'windows-1251'),
                    'name_short' => mb_convert_encoding($row['drug_name_lat'], 'UTF-8', 'windows-1251'),
                    'description' => mb_convert_encoding($row['opis'], 'UTF-8', 'windows-1251'),
                    'updated' => $row['_updated']
                );
            }
            if (!empty($product_list))
                $this->sendObjectsToGpor('postProducts', $product_list);
            $offset += self::PRODUCTS_LIMIT;
        } while (mysql_num_rows($result));

        // Для каждой аптеки создаем фид и вместе с ним отправляем
        $offset = 0;
        do {
            $result = mysql_query("SELECT `apt_code`, `apt_short`, `apt_add`, `phone`, `week`, `saturday`, `sunday` FROM {$this->db}.apts LIMIT {$offset}, " . self::DRUGSTORE_LIMIT);
            $drugstore_list = array();
            while ($row = mysql_fetch_assoc($result)) {
                $feedUrl = $this->makeProductsJSON($row['apt_code']);
                $drugstore_list[$row['apt_code']] = array(
                    'code' => (int)$row['apt_code'],
                    'name' => mb_convert_encoding($row['apt_short'], 'UTF-8', 'windows-1251'),
                    'address' => mb_convert_encoding($row['apt_add'], 'UTF-8', 'windows-1251'),
                    'phones' => mb_convert_encoding($row['phone'], 'UTF-8', 'windows-1251'),
                    'worktime' => $this->parseWorktime($row),
                    'feed' => $feedUrl
                );
            }
            if (!empty($drugstore_list))
                $this->sendObjectsToGpor('postDrugstores', $drugstore_list);
            $offset += self::DRUGSTORE_LIMIT;
        } while (mysql_num_rows($result));
    }

    /**
     * Парсит json с рубриками и отправляет их на гпор
    */
    private function sendRubrics()
    {
        $headers = get_headers($this->params['rubricJSONUrl']);
        if (substr($headers[0], 9, 3) != '200') {
            return false;
        }

        $result = file_get_contents($this->params['rubricJSONUrl']);
        $result = json_decode($result, 1);

        $rubric_list = array();
        foreach ($result as $row) {
            $rubric_list[$row['id']] = array(
                'code' => $row['id'],
                'name' => $row['label'],
                'parent' => $row['parentId']
            );
        }

        if (!empty($rubric_list))
            $this->sendObjectsToGpor('postRubrics', $rubric_list);
    }

    /**
     * Парсит XML-фид аптеки
     * @param array $feed
     * @return bool
    */
    private function parseFeed($feed)
    {
        $xml = simplexml_load_file($feed['url']);
        if (!$xml) {
            return false;
        }

        if ($this->params['debug'])
            print 'Parse feed ' . $feed['url'] . PHP_EOL;

        $simple = new SimpleXMLElement($xml->asXML());
        $apt_ids_list = array();
        foreach ($simple->Position as $line) {
            $apt_id = (string) $line['IdApt']; 

            $scode = mysql_query("SELECT apt_code FROM {$this->db}.apts WHERE apt_code = '{$apt_id}';");
            $apt_ids_list[] = $apt_id;

            if($scode) {
                $apt_scode_id = $apt_id;
                $apt_dcode_id = (string)$line['Code086'];
                $prep_id = (string)$line['IdPrep'];
                $prep = mysql_query("SELECT price 
                    FROM {$this->db}.aptdrugpresent 
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
                    if ($apt_prep_old['price'] != $apt_price) {
                        $sql = "UPDATE {$this->db}.aptdrugpresent 
                            SET price = '{$apt_price}'
                            WHERE scode = '{$apt_scode_id}' AND dcode = '{$apt_dcode_id}'";
                    }
                } else {
                    $sql = "INSERT INTO {$this->db}.aptdrugpresent 
                        VALUES ('{$apt_dcode_id}', '{$apt_scode_id}', 0, 1, '{$apt_price}', NOW(), '.', '', 1, 0, '', 4, '') ";
                }

                if ($sql) {
                    mysql_query($sql);
                }
            }
        }

        $apt_ids_list = array_unique($apt_ids_list);
        $drugstore_list = array();
        foreach ($apt_ids_list as $apt_id) {
            $feedUrl = $this->makeProductsJSON($apt_id, $feed['byeLinkPrefix'], $feed['reserveLinkPrefix']);
            $result = mysql_query("SELECT * FROM {$this->db}.apts WHERE `apt_code`=" . $apt_id);
            if (!$result) {
                continue;
            }

            $apt = mysql_fetch_assoc($result);
            $drugstore_list[$apt['apt_code']] = array(
                'code' => (int)$apt['apt_code'],
                'name' => mb_convert_encoding($apt['apt_short'], 'UTF-8', 'windows-1251'),
                'address' => mb_convert_encoding($apt['apt_add'], 'UTF-8', 'windows-1251'),
                'phones' => mb_convert_encoding($apt['phone'], 'UTF-8', 'windows-1251'),
                'worktime' => $this->parseWorktime($apt),
                'feed' => $feedUrl
            );
        }

        if (!empty($drugstore_list)) {
            $this->sendObjectsToGpor('postDrugstores', $drugstore_list);
        }

        return true;
    }

    /**
     * Создает JSON-файл с информацией о наличии препаратов для аптеки
     * @param int $apt_id
     * @param string $byeLinkPrefix
     * @param string $reserveLinkPrefix
     * @return string URL до созданного файла
    */
    private function makeProductsJSON($apt_id, $byeLinkPrefix = false, $reserveLinkPrefix = false)
    {
        // fetching drugs
        $result = mysql_query("SELECT `adp`.*, `dl`.`drug_code` FROM `aptdrugpresent` `adp`
            INNER JOIN `drug_list` `dl` ON `adp`.`dcode` = `dl`.`drug_code`
            WHERE `adp`.`scode`=" . $apt_id);

        if (!$result || !mysql_num_rows($result)) {
            return false;
        }

        // make feed
        $product_list = array();
        while ($row = mysql_fetch_assoc($result)) {
            $product_list[] = array(
                'drug_code' => (int)$row['dcode'],
                'price' => $row['price'] / 100,
                'byeLink' => ($byeLinkPrefix ? $byeLinkPrefix . $row['dcode'] : false),
                'reserveLink' => ($reserveLinkPrefix ? $reserveLinkPrefix . $row['dcode'] : false),
                'online_store' => $byeLinkPrefix || $reserveLinkPrefix,
                'updated' => $row['cdate'],
            );
        }

        if ($this->params['debug'])
            print 'Make JSON file for drugstore ' . $apt_id . ' of ' . count($product_list) . ' products' . PHP_EOL;

        // save drugs feed
        $filename = 'pharmacyFeed_' . $apt_id . '.json';
        if (!is_dir($this->params['jsonPath'])) {
            mkdir($this->params['jsonPath'], 0777, true);
        }
        $path = $this->params['jsonPath'] . $filename;
        $url = $this->params['jsonUrl'] . $filename;
        file_put_contents($path, json_encode(array('list' => $product_list, 'updated' => time())));

        return $url;
    }

    /**
     * Отправляет массив данных по АПИ
     * @param array $drugstore
    */
    private function sendObjectsToGpor($method, $objects)
    {
        $client = new xmlrpc_client($this->params['apiUrl']);
        $client->return_type = 'phpvals';

        $message = new xmlrpcmsg('pharmacy.' . $method);
        $p0 = new xmlrpcval($this->params['apiKey'], 'string');
        $message->addparam($p0);

        $p1 = php_xmlrpc_encode($objects);
        $message->addparam($p1);

        if ($this->params['debug'])
            print 'Make request `pharmacy.' . $method . '` of ' . count($objects) . ' objects...';

        $resp = $client->send($message, 0, 'http11');

        if (is_object($resp) && $resp->errno)
            die('Error uploading data: ' . $resp->errstr . PHP_EOL);

        if ($this->params['debug'])
            print 'SUCCESS' . PHP_EOL;
    }

    /**
     * Конвертация в необходимый формат времени работы аптеки
     * @param array $apt
     * @return array
    */
    private function parseWorktime($apt)
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
}

$instance = new PharmacyImport();
$instance->run();