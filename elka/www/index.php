<?php
date_default_timezone_set('Asia/Yekaterinburg');

// change the following paths if necessary
$localConfig = @include(dirname(__FILE__) . '/localConfig/params.php');
$yiiDebug = (!empty($localConfig) && isset($localConfig['yiiDebug'])) ? $localConfig['yiiDebug'] : false;

$config=dirname(__FILE__).'/protected/config/main.php';

define('ROOT_PATH', dirname(__FILE__));
define('BASE_PATH', dirname(__FILE__). DS . '..');
define('FILES_PATH', dirname(__FILE__). DS . 'files');
define('LIB_PATH', dirname(__FILE__). DS . '..' . DS . '..' . DS . '_lib');

defined('YII_DEBUG') or define('YII_DEBUG', $yiiDebug);
defined('YII_DEBUG_LOG') or define('YII_DEBUG_LOG', $yiiDebug);

$yii = LIB_PATH . '/yii-1.1.8.r3324/framework/yii.php';

require_once($yii);
require(dirname(__FILE__) . '/protected/components/ExtendedWebApplication.php');
Yii::createApplication('ExtendedWebApplication', $config)->run();
