<?php

define('DS', DIRECTORY_SEPARATOR);
define('LIB_PATH', dirname(__FILE__).'/../lib');

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',false);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once(dirname(__FILE__).'/../../framework/yii.php');
$configDist=require(dirname(__FILE__).'/../config/main-dist.php');
$config=require(dirname(__FILE__).'/../config/main.php');
$config=CMap::mergeArray($configDist, $config);
Yii::createWebApplication($config)->run();
