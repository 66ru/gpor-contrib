<?php

$path = dirname(__FILE__) . '/json/';

if (!is_dir($path)) {
    mkdir($path);
}

$params = array(
    'sectionId' => isset($_GET['sectionId']) ? $_GET['sectionId'] : false,
    'tagId' => isset($_GET['tagId']) ? $_GET['tagId'] : false,
    'limit' => isset($_GET['limit']) ? $_GET['limit'] : 10
);

$hash = md5(serialize($params));
$filename = $path . $hash . '.json';

if (file_exists($filename)) {
    $data = file_get_contents($filename);
} else {
    $data = json_encode(array('params' => $params, 'news' => array()));
    file_put_contents($filename, $data);
}

header('Content-Type: application/json');
echo $data;