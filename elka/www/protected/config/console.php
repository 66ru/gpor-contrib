<?php
$params = array();
$localConfigFile = dirname(__FILE__).'/../../localConfig/params.php';
$localDistConfigFile = dirname(__FILE__).'/../../localConfig/params-dist.php';
if (file_exists($localDistConfigFile))
	$localDistConfig = require($localDistConfigFile);
else
	die('local config-dist doesn`t exists at '.$localDistConfigFile."\n");
if (file_exists($localConfigFile))
	$localConfig = require($localConfigFile);
else
	die('local config doesn`t exists at '.$localConfigFile."\n");
$params = array_replace_recursive ($localDistConfig, $localConfig);
$emptyKeys = array();
foreach ($params as $k=>$v)
{
	if (is_string($v) && empty($v))
		$emptyKeys[] = $k;
}

/*
if (sizeof($emptyKeys))
{
	echo "Error: params\n".implode("\n", $emptyKeys)."\nrequired";
	die();
}
*/

return array(
    'basePath'=>dirname(__FILE__).'/..',
    'name'=>$params['appName'],
    'runtimePath' => dirname(__FILE__).'/../../../data',
    'language' => 'ru',
    'commandMap' => array(
//        'mailsend'                  => $extDir . DS . 'mailer' . DS . 'MailSendCommand.php',
    ),

	'preload'=>array('log'),

	// autoloading model, component and helper classes
	'import'=>array(
        'application.models.*',
        'application.commands.*',
        'application.components.*',
        'application.models.*',
        'application.widgets.*',
        'application.extensions.*',
        'application.helpers.*',
        'application.widgets.*',
    ),

	'params'=>$params,

	'components'=>array(
        'cron' => array(
			'class' => 'CronComponent',
			'logPath' => FILES_PATH,
		),
        'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning, notice',
//					'levels'=>'error, warning',
				),
			),
		),
        'errorHandler' => array(
        	'class' => 'application.components.ExtendedErrorHandler'
        ),
		'clientScript'=>array(
			'class'=>'application.components.ExtendedClientScript',
			'combineFiles'=>false,
			'compressCss'=>false,
			'compressJs'=>false,
		),
        'urlManager'=> require(dirname(__FILE__) . '/urlManager.php'),

        'cache' => array(
			'class' => 'CFileCache'
		),
    ),

    'modules'=> require(dirname(__FILE__) . '/modules.php'),

);