<?php

include_once ('../_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');

$params = array(
    'apiUrl' => '',
    'apiKey' => ''
);

if (!is_file('config.php'))
    die('missing config.php');
$params = array_merge($params, include 'config.php');

$path = dirname(__FILE__) . '/json/';

if (!is_dir($path)) {
    mkdir($path);
}

$fileList = scandir($path);
foreach ($fileList as $file) {
    $filename = $path . $file;
    if ($file == '.' || $file == '..' || !is_file($filename)) {
        continue;
    }
    $data = json_decode(file_get_contents($filename), true);
    if (!$data) {
        continue;
    }
    $newsParams = $data['params'];

    $client = new xmlrpc_client('/', $params['apiUrl'], 80);
    $client->return_type = 'xmlrpcvals';

    $msg = new xmlrpcmsg('news.listNews');
    $p0 = new xmlrpcval($params['apiKey'], 'string');
    $msg->addparam($p0);

    $p1 = new xmlrpcval('News', 'string');
    $msg->addparam($p1);

    $p2 = array();
    if ($newsParams['sectionId']) {
        $p2[]= array('type' => 'number', 'value' => $newsParams['sectionId'], 'field' => 'sectionId');
    }
    if ($newsParams['tagId']) {
        $p2[] = array('type' => 'array', 'value' => array($newsParams['tagId']), 'field' => 'tags');
    }

    $p2 = php_xmlrpc_encode($p2);
    $msg->addparam($p2);

    $p3 = array('id', 'title', 'postTime', 'annotation', 'commentsCount', 'link', 'imageUrl');
    $p3 = php_xmlrpc_encode($p3);
    $msg->addparam($p3);

    $p4 = array('limit' => $newsParams['limit']);
    $p4 = php_xmlrpc_encode($p4);
    $msg->addparam($p4);

    $resp = $client->send($msg, 0, 'http11');

    if (is_object($resp) && $resp->errno) {
        var_dump($resp);
        echo 'Error lodaing data: ' . $resp->errstr . PHP_EOL;
        continue;
    }

    $newsList = php_xmlrpc_decode($resp->val);
    $result = array();
    foreach ($newsList as $news) {
        $result[] = array(
            'title' => $news['title'],
            'announce' => $news['annotation'],
            'link' => $news['link'],
            'commentsCount' => isset($news['commentsCount']) ? $news['commentsCount'] : 0,
            'mainpageImageUrl' => $news['_parsedSpecImage'],
            'lentaImageUrl' => isset($news['_parsedLentaImage']) ? $news['_parsedLentaImage'] : false,
            'postDate' => $news['_parsedPostdate']
        );
    }

    $data = json_encode(array('params' => $newsParams, 'news' => $result));
    file_put_contents($filename, $data);
}
