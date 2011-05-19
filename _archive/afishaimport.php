<?php
/**
 * @author: vv
 * @since: 14.07.2010
 */

$config = require('config.php');

mb_internal_encoding("UTF-8");
define('DS', DIRECTORY_SEPARATOR);
define('KASSY_FEED_URL', 'http://ekb.kassy.ru/rss/');
define('REMOTE_API_HOST', $config['apiUrl']);
define('REMOTE_API_KEY', 'd0eaf58d952a82ec1bf2ec2a07c0ec00');

// path to base class of ezComponents library, needed to setup autoload
$ezComponentsBase = '_lib/ezcomponents-2009.2/Base/src/base.php';
require_once($ezComponentsBase);
spl_autoload_register(array('ezcBase', 'autoload'));
require_once('_lib/xmlrpc-3.0.0.beta'.DS.'xmlrpc.inc');
require_once('_lib/xmlrpc-3.0.0.beta'.DS.'xmlrpcs.inc');

$kassyFeed = ezcFeed::parse(KASSY_FEED_URL);

$shows = array();
$teatr = array();
$kinders = array();

foreach ($kassyFeed->item as $item) {
    $type = '';

    $item->enclosure;

    $tmp = array();
    $tmp['image'] = is_array($item->enclosure) ? $item->enclosure[0]->url : $item->enclosure->url;
    $tmp['guid'] = $item->guid;

    $desc = strip_tags(preg_replace("#  #","", preg_replace("#[\t\r\n]+#","",$item->description->__toString())),"<b><br><p><div><a>");
    $desc = str_Replace('<br />', '<br>', str_Replace('<br/>', '<br>', $desc));
    $desc = trim($desc,'<br>');

    if (substr($desc,-2)=='/p') $desc.='>';
    $data = explode('<br>',$desc);




    $d1 = trim(str_replace('Учреждение: ', '', $data[0]));
    $d2 = trim(str_replace('Дата: ', '', $data[1]));
    $d3 = trim(str_replace('Cтоимость билетов: ', '', $data[2]));
    $d4 = trim(str_replace('Тип зрелища: ', '', $data[3]));

    unset($data[0]);
    unset($data[1]);
    unset($data[2]);
    unset($data[3]);

    $d2 = trim($d2);
    preg_match('#(\d{1,2}) (.+?) (\d\d\d\d) (\d\d:\d\d)#', $d2, $date_tmp);
    $date =  textdate2norm(($date_tmp[1]<10 ? '0'.$date_tmp[1] : $date_tmp[1]).' '.$date_tmp[2].' '.$date_tmp[3]);

    $time = isset($date_tmp[4]) ? $date_tmp[4] : '00:00:00';

    $d5 = trim(implode('<br>',$data));

    $new_data = array($d1,$d3,$d4,$d5, $d2);

    $type = $d4;

    $tmp['data'] = $new_data;
    $tmp['date'] = $date.' '.$time.':00';
/*    $tmp[$element->Name] = $desc;
    if(isset($element->Data))
        {
        $tmp[$element->Name] = $element->Data;
        }*/

    $imageData = @file_get_contents($tmp['image']);
    $tmp['pictureBase64'] = base64_encode($imageData);

    if ($type == 'Концерты и Шоу')
        $shows[] = $tmp;

    if ($type == 'Театр')
        $teatr[] = $tmp;

    if ($type == 'Детские')
        $kinders[] = $tmp;
}

foreach ($shows as $show) {
    $client = new xmlrpc_client('/', REMOTE_API_HOST, 0);
    $client->return_type = 'xmlrpcvals';
    $client->setDebug($debug);
    $msg = new xmlrpcmsg('afisha.editEvent');
    $p1 = new xmlrpcval(REMOTE_API_KEY, 'string');
    $msg->addparam($p1);
    $eventParams = array_merge(array(
            'remoteUid'=>$show['guid'],
            'source' => 'ekb.kassy.ru',
            'title' => $show->title,
        ),
        $show
    );
    $p2 =& php_xmlrpc_encode($eventParams);
    $msg->addparam($p2);
    $res =& $client->send($msg, 0, 'http11');
    if ($res->faultcode()) return $res; else return php_xmlrpc_decode($res->value());
}



function textdate2norm($date)
{
	$month = array('января' => '01','февраля' => '02','марта' => '03','апреля' => '04','мая' => '05','июня' => '06','июля' => '07','августа' => '08','сентября' => '09','октября' => '10','ноября' => '11','декабря' => '12');

	$tmp = explode(" ",$date);
	if (sizeof($tmp) <> 3)
	$date = date("Y-m-d");
	else
	$date = (int)$tmp[2].'-'.(int)$month[strtolower($tmp[1])].'-'.(int)$tmp[0];

	return $date;
}
