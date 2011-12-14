<?php
set_include_path(__DIR__.DIRECTORY_SEPARATOR);
return array(
	'apiUrl' => '', //Api путь
    'apiKey' => '', //Api ключ
	'developerId' => 0, //ID застройщика на сайте (АСК)
	'agencyId'	  => 0, //ID агентства на сайте (АСК)
	
	'fileToParsePath' => '', //путь файла для парсинга
	'compliancesFilePath' => '', //путь до файла соответствий (сам генерится)
	'preparedDataFilePath' => '', //путь до подготовленных файлов (сам генерится)
	'importOperationsNumber' => 0, //количество одновременных операций импорта за один запуск
	'logFile' => '', //путь до файла с логами
	'statusFile' => '', //путь до lock-файла
	'errorFile' => '', //путь до файла с ошибками
);
?>