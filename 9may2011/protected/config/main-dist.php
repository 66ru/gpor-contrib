<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'9may2011',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
		'application.helpers.*',
		'application.widgets.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'Enter Your Password Here',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'user'=>array(
			'class' => 'WebUser',
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format

		'urlManager'=>array(
			'urlFormat'=>'path',
			'showScriptName' => false,
			'urlSuffix' => '/',
			'appendParams' => false,
			'rules'=>array(
//				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
//				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<action:\w+>/<id:\d+>'=>'site/<action>',
				'<action:\w+>'=>'site/<action>',
			),
		),

		'db'=>array(
			'connectionString' => 'sqlite:'.dirname(__FILE__).'/../data/9may.db',
		),
		'cache' => array(
			'class'=>'system.caching.CFileCache',
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		'gpor_server_url' => '',
		'gpor_secret_key' => '',
		'gpor_server_uid'=> '', // Добавляется к profileLink: gpor_server_uid/user/userId

        'api' => array(
            'host' => 'api.new66.gpor.ru',
            'port' => '80',
            'path' => '/',
            'key'  => ''
        ),

        'vkontakteApiId' => 2148796,
        'newsSection' => 67,
		'plainCommentsCount' => 10,
		'adminIds' => array(65266),

        'siteName' => 'www.66.ru',
        'outerHostName' => '66.ru',
	),
);

