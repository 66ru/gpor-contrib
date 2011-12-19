<?php 
$healthRootDir = dirname(__FILE__);
$config = require($healthRootDir.'/config.php');

$DR = $config['rootDir'];
include $DR."/config.php";
include $DR."/inc/memcashe.php";
include $DR."/inc/main_functions.php";
include $DR."/inc/cry_functions.php";

include_once ($healthRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
include_once ($healthRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpcs.inc');
include_once ($healthRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpc_wrappers.inc');

require ($healthRootDir.'/lib/HealthXmlRpc.php');

foreach ($config as $k=>$v)
{
	if (empty($v))
		die ("empty key ".$k);
}



// блок поиска лекарств
if (isset($config['abonenthost']) && isset($config['abonentuser']) && isset($config['abonentpassword']) &&
		$config['abonenthost'] && $config['abonentuser'] && $config['abonentpassword'] )
{
	$districts = array ();
	mysql_connect($config['abonenthost'],$config['abonentuser'],$config['abonentpassword']);
	mysql_select_db($config['abonentdb']);
	
	$sql = 'SELECT * FROM `dist_table` ORDER BY short_name';
	
	$r = mysql_query($sql);
	while($row = mysql_fetch_array($r))
		$districts[] = array(
			'name' => iconv('cp1251', 'UTF-8//IGNORE', $row['short_name']),
			'id' => $row['code_dist'],
		);
	
	$res = healthRenderTemplate ('search', array(
			'districts' => $districts,
		));
	
	//echo $res;
	$healthXmlRpc = new HealthXmlRpc($config['apiUrl'], $config['apiKey'], 'contextblock.update');
	$healthXmlRpc->send(array('health_searchBlock', base64_encode($res), 'base64' ));
}



// список компаний
if (isset($config['pricehost']) && isset($config['priceuser']) && isset($config['pricepassword']) &&
		$config['pricehost'] && $config['priceuser'] && $config['pricepassword'] )
{
	$rubrics = array ();
	$max = 15;
	mysql_connect($config['pricehost'],$config['priceuser'],$config['pricepassword']);
	mysql_select_db($config['pricedb']);
	
	$sql = 'SELECT pricedb_dev.pdb_enpr_rubriks.rubrik_id as `id`, pricedb_dev.pdb_enpr_rubriks.rubrik_name AS `name`, pricedb_dev.pdb_enpr_rubriks.enpr_count AS `count`
	        		FROM pricedb_dev.pdb_enpr_rubriks
	        		WHERE pricedb_dev.pdb_enpr_rubriks.parent_id = '.$config['priceRubric'].' AND pricedb_dev.pdb_enpr_rubriks.enpr_count > 0
	        		ORDER BY pricedb_dev.pdb_enpr_rubriks.pos, pricedb_dev.pdb_enpr_rubriks.rubrik_name';
	
	$r = mysql_query($sql);
	while($rubric = mysql_fetch_array($r))
		$rubrics[] = array(
			'name' => iconv('cp1251', 'UTF-8//IGNORE', $rubric['name']),
			'count' => $rubric['count'],
			'link' => $config['domain'].'/help/firms/cat'.$rubric['id'].'/',
		);
	
	if (count($rubrics) > $max)
	{
		$tmp = array();
		foreach ($rubrics as $rubric)
			$tmp[] = $rubric['count'];
		arsort ($tmp);
		$maxCount = array_pop(array_slice($tmp, ($max - 1),1));
	
		$tmp = $rubrics;
		$rubrics = array();
		foreach ($tmp as $rubric)
		{
			if ($rubric['count'] < $maxCount)
				continue;
			$rubrics[] = $rubric;
			if (count($rubrics) >= $max)
				break;
		}
	}
	
	
	
	$res = healthRenderTemplate ('companies', array(
			'rubrics' => $rubrics,
			'link' => $config['domain'].'/help/firms/cat'.$config['priceRubric'].'/',
		));
	
	//echo $res;
	$healthXmlRpc = new HealthXmlRpc($config['apiUrl'], $config['apiKey'], 'contextblock.update');
	$healthXmlRpc->send(array('health_companiesBlock', base64_encode($res), 'base64' ));
}




// список аптечных препаратов
if (isset($config['host']) && isset($config['user']) && isset($config['password']) && isset($config['pharmdb']) &&
		$config['host'] && $config['user'] && $config['password'] && $config['pharmdb'] )
{
	$rubrics = array ();
	mysql_connect($config['host'],$config['user'],$config['password']);
	mysql_select_db($config['pharmdb']);
	
	$sql = 'SELECT * FROM mod_pharmacy_catalog WHERE parent =0 AND active =1 ORDER BY sort';
	$r = mysql_query($sql);
	while($rubric = mysql_fetch_array($r))
		$rubrics[$rubric['id']] = array(
			'name' => iconv('cp1251', 'UTF-8//IGNORE', $rubric['name']),
			'count' => $rubric['cnt_items'],
			'link' => $config['domain'].'/help/pharmacy/'.$rubric['id'].'/',
		);
	
	$res = healthRenderTemplate ('drugs', array(
			'rubrics' => $rubrics,
			'link' => $config['domain'].'/help/pharmacy/',
		));
	
	//echo $res;
	$healthXmlRpc = new HealthXmlRpc($config['apiUrl'], $config['apiKey'], 'contextblock.update');
	$healthXmlRpc->send(array('health_drugsBlock', base64_encode($res), 'base64' ));
}



// блоги
if (isset($config['host']) && isset($config['user']) && isset($config['password']) && isset($config['blogTheme']) &&
		$config['host'] && $config['user'] && $config['password'] && $config['blogTheme'] )
{
	$blogs = array ();
	
	mysql_connect($config['host'],$config['user'],$config['password']);
	mysql_select_db($config['db']);
	
	$r = mysql_query('select b.id, b.user_id, b.title, cc.cnt from '.$config['db'].'.com_user_blog b, '.$config['db'].'.com_user_blog_comments_cnt cc, '.$config['db'].'.com_user_blog_theme ct where b.user_id>0 and ct.postID=b.id and ct.themeID="'.$config['blogTheme'].'" and b.id=cc.blog_id and b.close=0 AND b.show=0 order by b.id desc limit 10');
		
	while ($row = mysql_fetch_array($r))
	{
		$username = get_username($row['user_id']);
		$blogs[] = array (
			'id' => $row['id'],
			'user_id' => $row['user_id'],
			'username' => iconv('cp1251', 'UTF-8//IGNORE', $username[1]),
			'user_url' => $config['domain'].'/user/' . $row['user_id'] . '/',
			'title' => iconv('cp1251', 'UTF-8//IGNORE', $row['title']),
			'blog_url' => $config['domain'].'/user/' . $row['user_id'] . '/blog/' . $row['id'] . '/',
			'count' => $row['cnt'],
		);
	}
	
	
	$res = healthRenderTemplate ('blogs', array(
			'blogs' => $blogs,
		));
		
	//echo $res;
	$healthXmlRpc = new HealthXmlRpc($config['apiUrl'], $config['apiKey'], 'contextblock.update');
	$healthXmlRpc->send(array('health_blogsBlock', base64_encode($res), 'base64' ));
}



// объявления
if (isset($config['latest_krasota_url']) && isset($config['latest_krasota_sell_url']) && isset($config['latest_krasota_buy_url']) && isset($config['latest_krasota_serv_url']) &&
		$config['latest_krasota_url'] && $config['latest_krasota_sell_url'] && $config['latest_krasota_buy_url'] && $config['latest_krasota_serv_url'] )
{
	$maxAnnounces = 10;
	$latestKrasota = @json_decode(@file_get_contents($config['latest_krasota_url']));
	$latestKrasota = $latestKrasota ? $latestKrasota : array();
	
	$latestKrasotaSell = @json_decode(@file_get_contents($config['latest_krasota_sell_url']));
	$latestKrasotaSell = $latestKrasotaSell ? $latestKrasotaSell : array();
	
	$latestKrasotaBuy = @json_decode(@file_get_contents($config['latest_krasota_buy_url']));
	$latestKrasotaBuy = $latestKrasotaBuy ? $latestKrasotaBuy : array();
	
	$latestKrasotaServ = @json_decode(@file_get_contents($config['latest_krasota_serv_url']));
	$latestKrasotaServ = $latestKrasotaServ ? $latestKrasotaServ : array();
		
	$latestZdorovie = @json_decode(@file_get_contents($config['latest_zdorovie_url']));
	$latestZdorovie = $latestZdorovie ? $latestZdorovie : array();
	
	$latestZdorobieSell = @json_decode(@file_get_contents($config['latest_zdorovie_sell_url']));
	$latestZdorobieSell = $latestZdorobieSell ? $latestZdorobieSell : array();
	
	$latestZdorobieBuy = @json_decode(@file_get_contents($config['latest_zdorovie_buy_url']));
	$latestZdorobieBuy = $latestZdorobieBuy ? $latestZdorobieBuy : array();
	
	$latestZdorobieServ = @json_decode(@file_get_contents($config['latest_zdorovie_serv_url']));
	$latestZdorobieServ = $latestZdorobieServ ? $latestZdorobieServ : array();
	
	$data = array (
			'all' => healthFindLatestAnnounces(array($latestKrasota, $latestZdorovie), $maxAnnounces),
			'sell' => healthFindLatestAnnounces(array($latestKrasotaSell, $latestZdorobieSell), $maxAnnounces),
			'serv' => healthFindLatestAnnounces(array($latestKrasotaServ, $latestZdorobieServ), $maxAnnounces),
			'buy' => healthFindLatestAnnounces(array($latestKrasotaBuy, $latestZdorobieBuy), $maxAnnounces),
	);
	
	$res = healthRenderTemplate ('doska', array(
			'data' => $data,
			'urls' => array (
			'zdorovie' => $config['doska_zdorovie_url'],
			'krasota' => $config['doska_krasota_url'],
			'announceAdd' => $config['doska_addAnnounce_url'],
			)
		));
	
	//echo $res;
	$healthXmlRpc = new HealthXmlRpc($config['apiUrl'], $config['apiKey'], 'contextblock.update');
	$healthXmlRpc->send(array('health_doskaBlock', base64_encode($res), 'base64' ));
}



// вспомогательные ф-ции
function healthRenderTemplate ($template, $args)
{
	global $healthRootDir;
	foreach ($args as $k => $v)
		$$k = $v;

	ob_start();
	include($healthRootDir.'/views/'.$template.'.php');
	$res = ob_get_contents();
	ob_end_clean();
	return $res;
}


function healthFindLatestAnnounces ($args, $max = 10)
{
	$tmp = array();
	foreach ($args as $arg)
	{
		foreach ($arg as $item)
			$tmp[strtotime($item->updated)] = $item;
	}
	krsort($tmp);
	if (count($tmp) > $max)
		return array_slice($tmp, 0, $max);
	return $tmp;
}


function healthAnnounceDate($date)
{
	$res = '';
	$time = strtotime($date);
	$yesterday = date('Y-m-d', (time()-(60*60*24)) );
	$yesterday = strtotime($yesterday.' 00:00:00');
	$yesterday2 = strtotime($yesterday.' 00:00:00') - 60*60*24;
	
	if ($time > $yesterday)
		$res = 'сегодня';
	elseif ($time > $yesterday2)
		$res = 'вчера';
	else
		$res = date('d.m');
	return $res;
}
?>