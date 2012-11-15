<?php 
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('YII_DEBUG') or define('YII_DEBUG',true);
error_reporting(E_ALL | E_STRICT);

mb_internal_encoding("UTF-8");

$localConfig = @include(dirname(__FILE__) . '/../localConfig/params.php');
$configFile=dirname(__FILE__).'/config/console.php';

define('ROOT_PATH', dirname(__FILE__). DS . '..');
define('BASE_PATH', dirname(__FILE__). DS . '..' . DS . '..');
define('FILES_PATH', dirname(__FILE__). DS . '..' . DS . '..' . DS . 'files');
define('LIB_PATH', dirname(__FILE__). DS . '..' . DS . '..' . DS . '..' . DS . '_lib');

// подключаем файл инициализации Yii
require_once(LIB_PATH .'/yii-1.1.8.r3324/framework/yii.php');


require(dirname(__FILE__) . '/components/ExtendedConsoleApplication.php');
Yii::createApplication('ExtendedConsoleApplication', $configFile);
//Yii::createConsoleApplication($configFile)->run();
//Yii::import('application.extensions.croncommand.*');
?>