<?php 
define('DS', '/');
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

        'kClientApiKey' => '',

        'debug'       => false
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
        $url = $this->params['kApiUrl'] . $type . '/?city=' . $this->params['kCityId'] . '&apikey=' . $this->params['kApiKey'];
        $headers = get_headers($url);
        if (substr($headers[0], 9, 3) != '200') {
            return false;
        }

        $result = file_get_contents($url);
        $result = json_decode($result, 1);

        return $result; 
    }

    /**
     * Загрузка расписаний кинотеатра
     * @param int placeId
     * @param string $dateString
    */
    public function loadSchedules($placeId, $dateString) 
    {
        $url = $this->params['kApiUrl'] . 'cinemas/' . $placeId . '/schedules?date=' . $dateString . '&apikey=' . $this->params['kApiKey'];
        $headers = get_headers($url);
        if (substr($headers[0], 9, 3) == '200') {
            $result = file_get_contents($url);
            $result = json_decode($result, 1); 
            return $result;
        }
        else 
            return false;   
    }

    public function run($dateString)
    {
        $this->loadParams();

        $this->places = $this->loadList('cinemas'); // список кинотеатров города
        $existingPlaces = $this->sendData("afisha.listPlaces");

        // Формируем массив кинотеатров, которые нужно будет создать на gpor
        $placesToSend = array();
        foreach ($this->places as $key => $place) {
            if (isset($this->params['disabledPlaces'][$place['title']])) {
                if ($this->params['debug']) echo('Place "' . $place['title'] . '" disabled.' . PHP_EOL);   
                continue;
            }
            $found = false;
            foreach($existingPlaces as $eKey => $ePlace) {
                if ($found) break;
                if ($this->matchName($place['title'], $ePlace['name']) || $this->matchName($place['shortTitle'], $ePlace['name'])) {
                    $this->places[$key]['ePlaceId'] = $ePlace['id'];
                    unset($existingPlaces[$eKey]);
                    $found = true;
                    break;
                }
                if ($ePlace['synonym']) {
                    foreach (unserialize($ePlace['synonym']) as $syn) {
                        if ($this->matchName($place['title'], $syn) || $this->matchName($place['shortTitle'], $syn)) {
                            $this->places[$key]['ePlaceId'] = $ePlace['id'];
                            unset($existingPlaces[$eKey]);
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found) {
                $placesToSend[] = array('name' => $place['title']);
            }
        }

        // Отправляем новые кинотеатры на gpor
        if (!empty($placesToSend)) {
            if ($this->params['debug']) echo('Send new places to gpor.' . PHP_EOL);   
            $sendedPlaces = $this->sendData('afisha.postPlace', $placesToSend);
            // Проставляем созданным кинотеатрам корректные внешние идентификаторы
            foreach ($sendedPlaces as $ePlace) {
                foreach ($this->places as $key => $place) {
                    if ($this->matchName($place['title'], $ePlace['name'])) {
                        $this->places[$key]['ePlaceId'] = $ePlace['id'];
                    }
                }
            }
        }

        // Формируем массив фильмов для загрузки на gpor
        $this->movies = $this->loadList('movies');
        $existingMovies = $this->sendData('afisha.listMovies');
        $movieIdsArray = array(); // Массив соответствий индентификаторов фильма (kinohod => gpor)

        $moviesToSend = array();
        foreach ($this->movies as $key => $movie) {
            $found = false;
            foreach ($existingMovies as $eKey => $eMovie) {
                if ($found) break;
                if ($this->matchName($movie['title'], $eMovie['title']) || $this->matchName($movie['originalTitle'], $eMovie['originalTitle'])) {
                    $movieIdsArray[$movie['id']] = $eMovie['id'];
                    unset($existingMovies[$eKey]);
                    $found = true;
                    break;
                }
                if ($eMovie['synonym']) {
                    foreach (unserialize($eMovie['synonym']) as $syn) {
                        if ($this->matchName($movie['title'], $syn) || $this->matchName($movie['originalTitle'], $syn)) {
                            $movieIdsArray[$movie['id']] = $eMovie['id'];
                            unset($existingMovies[$eKey]);
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found) {
                $moviesToSend[] = array(
                    'name' => $movie['title'],
                    'text' => $movie['annotationFull'],
                    'genre' => implode(', ', $movie['genres']),
                    'year' => $movie['productionYear'],
                    'director' => implode(', ', $movie['directors']),
                    'starring' => implode(', ', $movie['actors']),
                    'country' => implode(', ', $movie['countries']),
                    'duration' => $movie['duration']
                );
            }
        }

        // Отправляем новые фильмы на gpor
        if (!empty($moviesToSend)) {
            if ($this->params['debug']) echo('Send new movies to gpor.' . PHP_EOL); 
            $sendedMovies = $this->sendData('afisha.postMovie', $moviesToSend);
            // Проставляем в массив соответсивий идентификаторов загруженные значения
            foreach ($sendedMovies as $eMovie) {
                foreach ($this->movies as $key => $movie) {
                    if ($this->matchName($movie['title'], $eMovie['title'])) {
                        $movieIdsArray[$movie['id']] = $eMovie['id'];
                    }
                }
            }
        }

        // Формируем массивы сеансов
        $schedulesToSend = array();
        foreach ($this->places as $place) {
            // пропускаем кинотеатр, если его по каким-то причинам нет на гпоре
            $placeId = isset($place['ePlaceId']) ? $place['ePlaceId'] : false;
            if (!$placeId) continue;

            $dataList = $this->loadSchedules($place['id'], $dateString);
            foreach($dataList as $data) {
                // Пропускаем сеанс, если такого фильма на гпоре нет
                $movieId = isset($movieIdsArray[$data['movie']['id']]) ? $movieIdsArray[$data['movie']['id']] : false;
                if (!$movieId) continue;

                foreach ($data['schedules'] as $schedule) {
                    $newSeance = array();
                    $startTime = strtotime($schedule['startTime']);
                    $newSeance['seanceTime'] = $startTime;
                    $newSeance['placeId'] = $placeId;
                    $newSeance['movieId'] = $movieId;
                    if ($schedule['isSaleAllowed'])
                        $newSeance['purchaseLink'] = $this->params['kPurchaseUrl'] . $schedule['id'] . '?apikey=' . $this->params['kClientApiKey'];
                    else
                        $newSeance['purchaseLink'] = '';
                    $schedulesToSend[] = $newSeance;
                }
            }
        }

        // Отправка сеансов
        if (sizeof($schedulesToSend)) {
            for ($i = 0; $i < sizeof($schedulesToSend); $i += 250){
                    if($this->params['debug']) echo "afisha.postSeances " .$i . " - " . min(sizeof($schedulesToSend),($i+250)) . " of total " . sizeof($schedulesToSend) ."\n";
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
}


for ($i = 0; $i < 7; $i++) {
    $p = new afishaCinemaKinohodParser();
    $p->run(date('dmY', strtotime("+" . $i . " days")));    
}
