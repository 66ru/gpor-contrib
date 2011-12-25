<?php 
$healthRootDir = dirname(__FILE__);
$config = require($healthRootDir.'/config.php');

$DR = $config['rootDir'];

include_once ($healthRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
include_once ($healthRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpcs.inc');
include_once ($healthRootDir.'/lib/xmlrpc-3.0.0.beta/xmlrpc_wrappers.inc');
require ($healthRootDir.'/lib/NewsStatXmlRpc.php');

foreach ($config as $k=>$v)
{
	if (empty($v))
		die ("empty key ".$k);
}

$dataDir = $healthRootDir.'/data/';

// собираем статистику просмотров за 2 месяца
$fileName = $dataDir.'viewsStat.json';
$stat = newsStatGetPeriodStat ($fileName, 'news.getNewsStat', array('sectionId' => false, 'type' => 'views'), $config);
if ($stat)
	newsStatWriteStatToFile ($fileName, $stat);
	
if ($config['sectionIds'])
{
	foreach($config['sectionIds'] as $sid)
	{
		$fileName = $dataDir.'viewsStatSection'.$sid.'.json';
		$stat = newsStatGetPeriodStat ($fileName, 'news.getNewsStat', array('sectionId' => $sid, 'type' => 'views'), $config);
		if ($stat)
			newsStatWriteStatToFile ($fileName, $stat);
	}
}

// собираем статистику комментариев за 2 месяца
$fileName = $dataDir.'commentsStat.json';
$stat = newsStatGetPeriodStat ($fileName, 'news.getNewsStat', array('sectionId' => false, 'type' => 'comments'), $config);
if ($stat)
	newsStatWriteStatToFile ($fileName, $stat);
	
if ($config['sectionIds'])
{
	foreach($config['sectionIds'] as $sid)
	{
		$fileName = $dataDir.'commentsStatSection'.$sid.'.json';
		$stat = newsStatGetPeriodStat ($fileName, 'news.getNewsStat', array('sectionId' => $sid, 'type' => 'comments'), $config);
		if ($stat)
			newsStatWriteStatToFile ($fileName, $stat);
	}
}


// собираем топ просматриваемых
$fileName = $dataDir.'viewsTop.json';
$newsStatXmlRpc = new NewsStatXmlRpc($config['apiUrl'], $config['apiKey'], 'news.listTopNews');
$stat = $newsStatXmlRpc->send(array($config['maxNewsOnTop'], array(
		'type' => 'views',
		'startDate' => date('Y-m-d').' 00:00:00', 
		'endDate' => date('Y-m-d').' 23:59:59', 
		'sectionId' => false,
		)
	));
if (is_array($stat))
	newsStatWriteStatToFile ($fileName, $stat);

if ($config['sectionIds'])
{
	foreach($config['sectionIds'] as $sid)
	{
		$fileName = $dataDir.'viewsTopSection'.$sid.'.json';
		$newsStatXmlRpc = new NewsStatXmlRpc($config['apiUrl'], $config['apiKey'], 'news.listTopNews');
		$stat = $newsStatXmlRpc->send(array($config['maxNewsOnTop'], array(
				'type' => 'views',
				'startDate' => date('Y-m-d').' 00:00:00', 
				'endDate' => date('Y-m-d').' 23:59:59', 
				'sectionId' => $sid,
				)
			));
		if (is_array($stat))
			newsStatWriteStatToFile ($fileName, $stat);
	}
}

// собираем топ комментируемых
$fileName = $dataDir.'commentsTop.json';
$newsStatXmlRpc = new NewsStatXmlRpc($config['apiUrl'], $config['apiKey'], 'news.listTopNews');
$stat = $newsStatXmlRpc->send(array($config['maxNewsOnTop'], array(
		'type' => 'comments',
		'startDate' => date('Y-m-d').' 00:00:00', 
		'endDate' => date('Y-m-d').' 23:59:59', 
		'sectionId' => false,
		)
	));
if (is_array($stat))
	newsStatWriteStatToFile ($fileName, $stat);

if ($config['sectionIds'])
{
	foreach($config['sectionIds'] as $sid)
	{
		$fileName = $dataDir.'commentsTopSection'.$sid.'.json';
		$newsStatXmlRpc = new NewsStatXmlRpc($config['apiUrl'], $config['apiKey'], 'news.listTopNews');
		$stat = $newsStatXmlRpc->send(array($config['maxNewsOnTop'], array(
				'type' => 'comments',
				'startDate' => date('Y-m-d').' 00:00:00', 
				'endDate' => date('Y-m-d').' 23:59:59', 
				'sectionId' => $sid,
				)
			));
		if (is_array($stat))
			newsStatWriteStatToFile ($fileName, $stat);
	}
}
	

/*
 * Вспомогательные ф-ции
 */

function newsStatWriteStatToFile ($fileName, $data)
{
	$resultFile = $fileName;
	$tmpFile = $fileName.'.tmp';
	if(!$handle = fopen($tmpFile, 'w+'))
	{
		throw new ErrorException('Can\'t create file '.$tmpFile);
		return false;
	}
	fwrite($handle, json_encode($data));
	fclose($handle);
    if (file_exists($tmpFile)){
	    	if (file_exists($resultFile)) unlink($resultFile);
    		copy($tmpFile, $resultFile);
    	}
    unlink($tmpFile);
		
	return true;
}

function newsStatGetStatFromFile ($fileName)
{
	$result = array();
	if (file_exists($fileName))
	{
		$content = @file_get_contents($fileName);
		if ($content)
		{
			$content = json_decode($content);
			if ($content)
				return $content;
		}
	}
	
	return $result;
}


function newsStatGetPeriodStat ($fileName, $method, $filters, $config)
{
	$result = array();
	echo $method.' start...';
	$days = $config['days'];
	$stat = newsStatGetStatFromFile ($fileName);
	$tmp = array();
	if ($stat)
	{
		foreach ($stat as $date=>$total)
		{
			$tmp[$date] = $total;
			if (count($tmp) >= $config['days'])
				break;
		}
	}

	for ($i = 0; $i < $days; $i++)
	{
		$date = date('Y-m-d', (time() - 60*60*24*$i));
		if ($i == 0 || !isset($tmp[$date]))
		{
			$newsStatXmlRpc = new NewsStatXmlRpc($config['apiUrl'], $config['apiKey'], $method);
			$result = $newsStatXmlRpc->send(array( array(
					'type' => $filters['type'],
					'startDate' => $date.' 00:00:00', 
					'endDate' => $date.' 23:59:59',
					'sectionId' => $filters['sectionId'],
					)
				));
			$total = 0;
			if ($result && isset($result['total']))
				$tmp[$date] = $result['total'];
		}
	}
	echo "end\n";
	return $tmp;
	
}


?>