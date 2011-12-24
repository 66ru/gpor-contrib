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
$parser->run();
?>