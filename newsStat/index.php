<?php 
$newsStatRootDir = dirname(__FILE__);

require ($newsStatRootDir.'/lib/NewsStatController.php');

$config = require($newsStatRootDir.'/config.php');

foreach ($config as $k=>$v)
{
	if (empty($v))
		die ("empty key ".$k);
}

$application = new NewsStatController ($newsStatRootDir, $config);
$application->run();
?>