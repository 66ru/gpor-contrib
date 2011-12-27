<?php 
$newsStatRootDir = dirname(__FILE__);
$config = require($newsStatRootDir.'/config.php');

require ($newsStatRootDir.'/lib/NewsStatFeedParser.php');

foreach ($config as $k=>$v)
{
	if (empty($v))
		die ("empty key ".$k);
}

$pid = getmypid();

$parser = new NewsStatFeedParser ($pid, $config);

$max = $config['maxStreamsCount'];

$totalFeeds = count($parser->getFeeds());
$totalRunning = $parser->totalRunningProcesses();

$max = $max - $totalRunning;
if ($max > 0 && $totalFeeds)
{
	$command = $config['phpPath'].' '.$newsStatRootDir.'/runFeedParser.php';
	
	$handle = popen($command, 'r');
	echo "'$handle'; " . gettype($handle) . "\n";
	$read = fread($handle, 2096);
	echo $read;
	pclose($handle);
}
?>