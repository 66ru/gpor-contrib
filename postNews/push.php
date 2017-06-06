 
<?php


include_once ('_lib/xmlrpc-3.0.0.beta/xmlrpc.inc');

$config = array (
	'apiUrl'	=> 'http://api.66.ru/',
	'apiKey'	=> 'fd26604976f485bb5982a164cf29ede4'
);
$isDebug = false;


$params = array();
$params['version'] = 2;


$content = base64_encode('<p>
    Середина февраля порадует екатеринбуржцев хорошей погодой. Морозы наконец-то&nbsp;отступили, так что&nbsp;дневная температура воздуха на&nbsp;этой неделе не&nbsp;будет опускаться ниже -11&deg;C. Впрочем, до&nbsp;этой отметки столбик термометра опустится только в&nbsp;понедельник. Также в&nbsp;этот день ожидается небольшой снег и&nbsp;гололедица.</p>
<p>
    Во&nbsp;вторник температура воздуха составит от&nbsp;-9 до&nbsp;-7&nbsp;градусов, также синоптики обещают слабую метель. В&nbsp;среду температура воздуха поднимется уже до&nbsp;-6&deg;C.</p>
<p>
    <fake_object object_id="574516"></fake_object></p>
<p>
    Самым теплым днем недели станет четверг, когда термометр покажет всего 4&nbsp;градуса ниже нуля. В&nbsp;пятницу синоптики прогнозируют -6&deg;C.</p>
');

$params['news']['forceCreateTime']      = true;
$params['news']['createTime']           = time();
$params['news']['editTime']             = time();
$params['news']['postTime']             = time();
$params['news']['title']                = base64_encode('[Тестовая новость:] к середине февраля в Екатеринбург пришло долгожданное потепление');
$params['news']['annotation']           = base64_encode('Температура воздуха на этой неделе будет подниматься до -4°C.');
$params['news']['content']              = $content;
$params['news']['sectionId']            = 274;
$params['news']['toNewsAggregators']    = false;
$params['news']['comment']              = '';
$params['news']['infograph']            = false;
$params['news']['containPhoto']         = false;
$params['news']['containVideo']         = false;
$params['news']['containAudio']         = false;
$params['news']['online']               = 0;
$params['news']['lentaType']            = 4;
$params['news']['lentaImage']           = 'http://s.66.ru/localStorage/news/89/7d/56/23/897d5623.jpg';
$params['news']['specImage']            = 'http://s.66.ru/localStorage/news/6e/e6/c1/fc/6ee6c1fc.jpg';
$params['news']['layoutType']           = 10;
$params['news']['image']                = 'http://s.66.ru/localStorage/news/73/63/89/7c/7363897c.jpg';
$params['news']['imageAuthor']          = base64_encode('Фотобанк 66.ru');
$params['news']['tags']                 = array(
                                                base64_encode('weather')
                                            );
$params['news']['photoVideoInfo']       = base64_encode('Фотобанк 66.ru');
$params['news']['imageAuthor']          = base64_encode('Фотобанк 66.ru');
$params['news']['status'] = 2;
$params['news']['changeStatus'] = false;

$params['news']['collection'] = array(
    array(
        'id'                => 574516,
        'sourceFilename'    => 'weather.jpg',
        'newsgallery'       => false,
        'watermark'         => false,
        'title'             => '',
        'orderNum'          => 1,
        'type'              => 'ImageFile',
        'src'               => 'http://s.66.ru/originalStorage/news/6e/e6/c1/fc/6ee6c1fc_uncropped.jpg',
        'createTime'        => time(),
        'repeated'          => 0
    )
);
$params['news']['photoreportages'] = array();

$client = new xmlrpc_client($config['apiUrl']);
$client->request_charset_encoding = 'UTF-8';
$client->return_type = 'phpvals';
$client->debug = $isDebug;

$msg = new xmlrpcmsg('news.postNews');
$p1 = new xmlrpcval($config['apiKey'], 'string');
$msg->addparam($p1);

if ($params)
{
    $p2 = php_xmlrpc_encode($params);
    $msg->addparam($p2);
}
$client->accepted_compression = 'deflate';
$res = $client->send($msg, 60 * 5, 'http11');

if ($res->faultcode()) // server failure
{
    print_r($res);
    print "An error occurred: ";
    print " Code: " . htmlspecialchars($res->faultCode());
    print " Reason: '" . htmlspecialchars($res->faultString()) . "' \n";
    die;
}
else {
    if ($res->val['errcode'])
        print "An error occurred: " . $res->val['errstr'];
    else
        print 'success. New id: ' . $res->val['newsid'];
}




