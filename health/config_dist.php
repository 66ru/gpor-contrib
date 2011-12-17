<?php 
return array (
	'rootDir' => '', // абсолютный путь до www (включая www/)

	// реквизиты к базе
	'db' => '',
	'host' => '',
	'user' => '',
	'password' => '',

	// реквизиты к базе абонента
	'abonentdb' => '',
	'abonenthost' => '',
	'abonentuser' => '',
	'abonentpassword' => '',

	// реквизиты к базе справочника компаний
	'pricedb' => '',
	'pricehost' => '',
	'priceuser' => '',
	'pricepassword' => '',

	'pharmdb' => '', // база справочника лекарств

	'priceRubric' => 18, // id корневой рубрики "здоровье. краоста." в рубрикаторе справочника компаний
	
	'blogTheme' => 6, // id темы блогов здоровья

	'domain' => '', // домен сайта http://...
	
	'apiUrl' => '', 
	'apiKey' => '', // ключ для метода обновления КБ
	
	// досочные урлы
	'latest_krasota_url' => '',
	'latest_krasota_sell_url' => '',
	'latest_krasota_buy_url' => '',
	'latest_krasota_serv_url' => '',

	'latest_zdorovie_url' => '',	
	'latest_zdorovie_sell_url' => '',	
	'latest_zdorovie_buy_url' => '',	
	'latest_zdorovie_serv_url' => '',

	'doska_zdorovie_url' => '',
	'doska_krasota_url' => '',
	'doska_addAnnounce_url' => '',

);
?>