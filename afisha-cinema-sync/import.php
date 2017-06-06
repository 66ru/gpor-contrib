<?php 
define('DS', '/');
ini_set('memory_limit', '-1');
mb_internal_encoding("UTF-8");
date_default_timezone_set('Asia/Yekaterinburg');
include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
class afishaCinemaSync
{
    private $params = array(
        'apiUrl'       => '',
        'apiKey'       => '',

        'apiUrl2'       => '',
        'apiKey2'       => '',

        'debug'       => true
    );

    private $movies = array();

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

    public function loadList($name, $params = array())
    {
        $client                           = new xmlrpc_client($this->params['apiUrl2']);
        $client->request_charset_encoding = 'UTF-8';
        $client->return_type              = 'phpvals';
        $client->debug                    = 0;
        $msg                              = new xmlrpcmsg($name);
        $p1                               = new xmlrpcval($this->params['apiKey2'], 'string');
        $msg->addparam($p1);

        if ($params) {
            $p2 = php_xmlrpc_encode($params);
            $msg->addparam($p2);
        }
        $client->accepted_compression = 'deflate';
        $res = $client->send($msg, 60 * 5, 'http11');
        if ($res->faultcode()) {
            print "An error occurred: ";
            print " Code: " . htmlspecialchars($res->faultCode());
            print " Reason: '" . htmlspecialchars($res->faultString()) . "' \n";
            die;
        } else
            return $res->val;
    }

    public function run()
    {
        $this->loadParams();

        // Формируем массив фильмов для загрузки на gpor
        $this->movies = $this->loadList('afisha.listMovies');
        $existingMovies = array();
        $existingMovies = $this->sendData('afisha.listMovies');
        $movieIdsArray = array(); // Массив соответствий индентификаторов фильма (kinohod => gpor)

        $moviesToSend = array();
        foreach ($this->movies as $key => $movie) {
            if (!in_array($movie['status'], array(20, 21) ))
                continue;
            $found = false;
            foreach ($existingMovies as $eKey => $eMovie) {
                if ($found)
                    break;
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
                echo 'get ' . $movie['id'] . "\n";
                $movieFull = $this->loadList('afisha.getMovie', array('id'=>$movie['id']));
                $moviesToSend[] = $movieFull;
            }
        }

        // Отправляем новые фильмы на gpor
        if (!empty($moviesToSend)) {
            if ($this->params['debug'])
                echo 'Send new movies to gpor.' . PHP_EOL;

            var_dump($moviesToSend);

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


$p = new afishaCinemaSync();
$p->run();    
