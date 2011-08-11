<?php
// Настраиваем автолоадеры
spl_autoload_register('autoload');
function autoload($class_name) {
    include './lib/'.$class_name . '.class.php';
}

// Обрабатываем запрос
$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

if($requestMethod === "post")
{
	$parser = new Parser();
	$parser->configure(array('id','rooms', 'square', 'price', 'floor'), true, ';');
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
	$parser->prepareAnnonceListAndImport();
	
	
}
else die('Нет данных, вернитесь к пункту 2');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Парсер новостроек Атомстройкомплекса</title>
  <style type="text/css">
   #centerLayer {
    position: absolute; /* Абсолютное позиционирование */
    width: 880px; /* Ширина слоя в пикселах */
    height: 680px; /* Высота слоя в пикселах */
    left: 50%; /* Положение слоя от левого края */
    top: 50%; /* Положение слоя от верхнего края */
    margin-left: -150px; /* Отступ слева */
    margin-top: -100px;	/* Отступ сверху */
    padding: 10px; /* Поля вокруг текста */
    overflow: auto; /* Добавление полосы прокрутки */ 
   }
  </style>
</head>
<body>
  <div id="centerLayer">

  </div>
</body>
</html>
<?php
