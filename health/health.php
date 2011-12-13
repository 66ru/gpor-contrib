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




// список компаний
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




// список аптечных препаратов
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



// блоги
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
		'blog_url' => $config['domain'].'/' . $row['user_id'] . '/blog/' . $row['id'] . '/',
		'count' => $row['cnt'],
	);
}


$res = healthRenderTemplate ('blogs', array(
		'blogs' => $blogs,
	));
	
//echo $res;
$healthXmlRpc = new HealthXmlRpc($config['apiUrl'], $config['apiKey'], 'contextblock.update');
$healthXmlRpc->send(array('health_blogsBlock', base64_encode($res), 'base64' ));



// объявления
$announces = array ();

$res = healthRenderTemplate ('doska', array(
		'announces' => $announces,
	));

//echo $res;
$healthXmlRpc = new HealthXmlRpc($config['apiUrl'], $config['apiKey'], 'contextblock.update');
$healthXmlRpc->send(array('health_doskaBlock', base64_encode($res), 'base64' ));


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

?>