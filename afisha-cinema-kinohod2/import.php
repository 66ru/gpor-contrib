<?php 
define('DS', '/');
ini_set('memory_limit', '-1');
mb_internal_encoding("UTF-8");
date_default_timezone_set('Asia/Yekaterinburg');
include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
class afishaCinemaKinohodParser
{
    private $params = array(

        'apiUrl'       => '',
        'apiKey'       => '',

        'kApiUrl'     => '',
        'kApiKey'     => '',
        'kPurchaseUrl' => '',
        'kPurchaseUrlMobile' => '',
        'kClientApiKey' => '',

        'debug'       => true
    );

    private $places = array();
    private $movies = array();
    private $seances = array();

    private function loadParams()
    {
        if (!is_file('config.php')) {
            echo "missing config.php";
            die;
        }
        $this->params = array_merge($this->params, include 'config.php');

        foreach ($this->params as $key => $param) {
            if (!isset($param)) {
                echo 'missing ' . $key . 'param in config file';
                die;
            }
        }
    }

    /**
     * Загружает список объектов с кинохода
     * @param string $type Доступны значения 'cinemas', 'movies'
     * @return array
    */
    public function loadList($type)
    {
        if (!$this->params['kCityId']) {
            return array();
        }
        $url = $this->params['kApiUrl'] . $this->params['kApiKey'] . '/' . $type . '.json';
        $headers = get_headers($url);
        if (substr($headers[0], 9, 3) != '200') {
            return false;
        }

        $content = false;
        $result = @file_get_contents($url);
        if ($result) {
            if ($this->saveData($type.'.json.gz', $result))
                $content = $this->readData($type.'.json.gz');
        }

        return $content; 
    }

    /**
     * Загрузка расписаний кинотеатра
     * @param int placeId
     * @param string $dateString
    */
    public function loadSchedules() 
    {
        $res = array();
        $urls = array();
        $urls[] = $this->params['kApiUrl'] . $this->params['kApiKey'] . '/city/' . $this->params['kCityId'] . '/seances/week.json';
        $urls[] = $this->params['kApiUrl'] . $this->params['kApiKey'] . '/city/' . $this->params['kCityId'] . '/seances/week/+1.json';
        $i = 1;
        foreach ($urls as $url) {
            $headers = get_headers($url);
            if (substr($headers[0], 9, 3) != '200')
                return false;

            $content = false;
            $result = @file_get_contents($url);
            if ($result) {
                if ($this->saveData('seances' . $i . '.json.gz', $result))
                    $content = $this->readData('seances' . $i . '.json.gz');
            }
            $i++;

            if ($content)
                $res = array_merge($content, $res);
        }

        return $res;
    }

    public function run()
    {
        $this->loadParams();

        $this->places = $this->loadList('cinemas'); // список кинотеатров города
        $existingPlaces = $this->sendData("afisha.listPlaces");

        // Формируем массив кинотеатров, которые нужно будет создать на gpor
        $placesToSend = array();
        foreach ($this->places as $key => $place) {
            if (isset($this->params['disabledPlaces'][$place->title])) {
                if ($this->params['debug'])
                    echo 'Place "' . $place->title . '" disabled.' . PHP_EOL;
                continue;
            }
            if ($place->cityId != $this->params['kCityId']) {
                //if ($this->params['debug'])
                //    echo 'Place "' . $place->title . '" different city.' . PHP_EOL;
                continue;                
            }
            $found = false;
            foreach($existingPlaces as $eKey => $ePlace) {
                if ($found)
                    break;
                if ($this->matchName($place->title, $ePlace['name']) || $this->matchName($place->shortTitle, $ePlace['name'])) {
                    $this->places[$key]->ePlaceId = $ePlace['id'];
                    unset($existingPlaces[$eKey]);
                    $found = true;
                    break;
                }
                if ($ePlace['synonym']) {
                    foreach (unserialize($ePlace['synonym']) as $syn) {
                        if ($this->matchName($place->title, $syn) || $this->matchName($place->shortTitle, $syn)) {
                            $this->places[$key]->ePlaceId = $ePlace['id'];
                            unset($existingPlaces[$eKey]);
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found) {
                $placesToSend[] = array('name' => $place->title);
            }
            else {
                if ($this->params['debug'])
                    echo 'Place "' . $place->title . '" found.' . PHP_EOL;                
            }
        }

        // Отправляем новые кинотеатры на gpor
        if (!empty($placesToSend)) {
            if ($this->params['debug'])
                echo 'Send new places to gpor.' . PHP_EOL;
            $sendedPlaces = $this->sendData('afisha.postPlace', $placesToSend);
            // Проставляем созданным кинотеатрам корректные внешние идентификаторы
            foreach ($sendedPlaces as $ePlace) {
                foreach ($this->places as $key => $place) {
                    if ($this->matchName($place->title, $ePlace['name'])) {
                        $this->places[$key]->ePlaceId = $ePlace['id'];
                    }
                }
            }
        }

        // Формируем массив фильмов для загрузки на gpor
        $this->movies = $this->loadList('city/'. $this->params['kCityId'] .'/running/week');
        $existingMovies = $this->sendData('afisha.listMovies');
        $movieIdsArray = array(); // Массив соответствий индентификаторов фильма (kinohod => gpor)

        $moviesToSend = array();
        foreach ($this->movies as $key => $movie) {
            $found = false;
            foreach ($existingMovies as $eKey => $eMovie) {
                if ($found)
                    break;
                if ($this->matchName($movie->title, $eMovie['title']) || $this->matchName($movie->originalTitle, $eMovie['originalTitle'])) {
                    $movieIdsArray[$movie->id] = $eMovie['id'];
                    unset($existingMovies[$eKey]);
                    $found = true;
                    break;
                }
                if ($eMovie['synonym']) {
                    foreach (unserialize($eMovie['synonym']) as $syn) {
                        if ($this->matchName($movie->title, $syn) || $this->matchName($movie->originalTitle, $syn)) {
                            $movieIdsArray[$movie->id] = $eMovie['id'];
                            unset($existingMovies[$eKey]);
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found) {
                $moviesToSend[] = array(
                    'name' => $movie->title,
                    'text' => $movie->annotationFull,
                    'genre' => implode(', ', self::ObjToList($movie->genres)),
                    'year' => $movie->productionYear,
                    'ageRestriction' => $movie->ageRestriction,
                    'director' => implode(', ', self::ObjToList($movie->directors)),
                    'starring' => implode(', ', self::ObjToList($movie->actors)),
                    'country' => implode(', ', self::ObjToList($movie->countries)),
                    'duration' => $movie->duration
                );
            }
        }

        // Отправляем новые фильмы на gpor
        if (!empty($moviesToSend)) {
            if ($this->params['debug'])
                echo 'Send new movies to gpor.' . PHP_EOL;

            $sendedMovies = $this->sendData('afisha.postMovie', $moviesToSend);
            // Проставляем в массив соответсивий идентификаторов загруженные значения
            foreach ($sendedMovies as $eMovie) {
                foreach ($this->movies as $key => $movie) {
                    if ($this->matchName($movie->title, $eMovie['title'])) {
                        $movieIdsArray[$movie->id] = $eMovie['id'];
                    }
                }
            }
        }

        // Формируем массивы сеансов
        $schedulesToSend = array();
        $dataList = $this->loadSchedules();
        foreach ($this->places as $place) {
            // пропускаем кинотеатр, если его по каким-то причинам нет на гпоре
            $placeId = isset($place->ePlaceId) ? $place->ePlaceId : false;
            if (!$placeId)
                continue;

            foreach($dataList as $data) {
                // Пропускаем сеанс, если такого фильма на гпоре нет
                $movieId = isset($movieIdsArray[$data->movieId]) ? $movieIdsArray[$data->movieId] : false;
                $placeId = false;

                if (!$movieId)
                    continue;
                foreach ($this->places as $place) {
                    if ($place->id == $data->cinemaId && $place->ePlaceId) {
                        $placeId = $place->ePlaceId;
                        break;
                    }
                }
                if (!$placeId)
                    continue;

                    $newSeance = array();
                    $startTime = strtotime($data->startTime);
                    $newSeance['is3d'] = in_array('3d', $data->formats) ? 1 : 0;
                    $newSeance['isImax'] = in_array('imax', $data->formats) ? 1 : 0;
                    $newSeance['seanceTime'] = $startTime;
                    $newSeance['placeId'] = $placeId;
                    $newSeance['movieId'] = $movieId;
                    $newSeance['minPrice'] = $data->minPrice;
                    $newSeance['maxPrice'] = $data->maxPrice;
                    if ($data->isSaleAllowed) {
                        $newSeance['purchaseLink'] = $this->params['kPurchaseUrl'] . $data->id . '?apikey=' . $this->params['kClientApiKey'];
                        $newSeance['purchaseLinkMobile'] = $this->params['kPurchaseUrlMobile'] . $data->id . '?apiKey=' . $this->params['kClientApiKey'];
                    } else {
                        $newSeance['purchaseLink'] = '';
                        $newSeance['purchaseLinkMobile'] = '';
                    }
                    $schedulesToSend[] = $newSeance;
            }
        }

        // Отправка сеансов
        if (sizeof($schedulesToSend)) {
            for ($i = 0; $i < sizeof($schedulesToSend); $i += 250) {
                if ($this->params['debug'])
                    echo "afisha.postSeances " .$i . " - " . min(sizeof($schedulesToSend),($i+250)) . " of total " . sizeof($schedulesToSend) ."\n";
                $this->sendData('afisha.postSeances', array_slice($schedulesToSend, $i, 250));
            }
        }
    }

    public function sendData($name, $params = array())
    {
        $client                           = new xmlrpc_client($this->params['apiUrl']);
        $client->request_charset_encoding = 'UTF-8';
        $client->return_type              = 'phpvals';
        $client->debug                    = 0;
        $msg                              = new xmlrpcmsg($name);
        $p1                               = new xmlrpcval($this->params['apiKey'], 'string');
        $msg->addparam($p1);

        if ($params) {
            $p2 = php_xmlrpc_encode($params);
            $msg->addparam($p2);
        }
        $client->accepted_compression = 'deflate';
        $res                          = $client->send($msg, 60 * 5, 'http11');
        if ($res->faultcode()) {
            print "An error occurred: ";
            print " Code: " . htmlspecialchars($res->faultCode());
            print " Reason: '" . htmlspecialchars($res->faultString()) . "' \n";
            die;
        } else
            return $res->val;
    }

    private function matchName($a, $b)
    {
        $a = mb_strtolower($a);
        $b = mb_strtolower($b);
        $a = preg_replace('|[^\p{L}\p{Nd}]|u', '', $a);
        $b = preg_replace('|[^\p{L}\p{Nd}]|u', '', $b);
        if (($a == $b) && ($a!='')) 
            return true;
        return false;
    }




    public static function ObjToList($data)
    {
        $res = array();
        if (is_array($data)) {
            foreach ($data as $row) {
                $res[] = $row->name;
            }
        }
        return $res;
    }


    public function saveData($filename, $data)
    {
        $path = $this->params['filePath'] . '/' . $filename;
        $pathinfo = pathinfo($path);
        $tmp = explode(DS, $pathinfo['dirname']);
        $tmpPath = '';
        foreach ($tmp as $part) {
            if (empty($part)) {
                $tmpPath = DS;
                continue;
            }
            $tmpPath .= $part . DS;
            if (!is_dir($tmpPath)) {
                echo $tmpPath;
                if (!mkdir($tmpPath, 0755))
                    die('Can\'t create dir '.$tmpPath);
                chmod($tmpPath, 0755);
            }
        }

        $tmpFile = $path.'.tmp';
        if (!$handle = fopen($tmpFile, 'w+'))
            die('Can\'t create file '.$filename);

        fwrite($handle, $data);
        fclose($handle);
        if (file_exists($tmpFile)) {
            if (file_exists($path))
                unlink($path);
            copy($tmpFile, $path);
        }
        unlink($tmpFile);
        return true;
    }

    public function readData($filename)
    {
        $fileName = $this->params['filePath'] . '/' . $filename;
        if (!file_exists($fileName))
            return null;

        $content = '';
        var_dump($filename);
        $handle = gzopen($filename, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $content .= $line;
            }
            fclose($handle);
        } else {
            die('error reading ' . $filename);
        }

        //$content = @file_get_contents($fileName);
        if (!$content)
            return null;

        $content = json_decode($content);
        if (!$content)
            return null;

        return $content;
    }    

}


$p = new afishaCinemaKinohodParser();
$p->run();    
