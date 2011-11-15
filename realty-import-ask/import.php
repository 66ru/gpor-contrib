<?php
// Настраиваем автолоадеры
spl_autoload_register('autoload');
function autoload($class_name) {
	include 'lib/'.$class_name . '.class.php';
}

// Обрабатываем запрос
$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

if($requestMethod === "post")
{
	$params = require ('config.php');
	$logFile = isset($params['logFile']) ? $params['logFile'] : false;
	
	$parser = new Parser();
	$parser->configure(array('id','rooms', 'square', 'price', 'floor', 'visible'), true, ';');
	$parser->parse();

	// Получаем связи idшников и записываем их сохраняем их
	$newObjectCompliances = array();

	foreach ($parser->getUniqueObjectIds() as $i => $item)
	{
		$objectId = $_POST['realty_id_'.$i];

		$newObjectCompliances[$item] = $objectId;
	}
	$parser->saveObjectCompliances($newObjectCompliances);

	
	// Готовим объявления для импорта и ипортируем
	$parser->prepareAnnonceListAndSave();
	$hRet = file_put_contents(get_include_path().$logFile, '');
	
	if(($count = count($parser->announcesNotUsed)) > 0)
	{
		echo 'Объявления, которые не удалось привязать: '.$count .' шт.<br />';
		echo '<table border="1">
						<thead>
							<tr>
								<th>id дома</th>
								<th>кол-во комнат</th>
								<th>площадь</th>
								<th>цена (млн р.)</th>
								<th>этаж</th>
							</tr>
						</thead>
						<tbody>';
		foreach ($parser->announcesNotUsed as $annonce)
		{
			echo '<tr><td>'.$annonce->id. '</td><td>'.$annonce->rooms.'</td><td>'.$annonce->square.'</td><td>'.$annonce->price.'</td><td>'.$annonce->floor.'</td></tr>';
		}

		echo '</tbody></table><br /><br /><br />';
	}
}
else die('Нет данных, вернитесь к пункту 2');