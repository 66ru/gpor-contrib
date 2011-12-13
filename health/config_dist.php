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

);
?>