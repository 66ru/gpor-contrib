<?php
// Настраиваем автолоадеры
spl_autoload_register('autoload');
function autoload($class_name) {
	include './lib/'.$class_name . '.class.php';
}
$parser = new Parser();
$parser->configure(array('id','rooms', 'square', 'price', 'floor'), true, ';');
$parser->parse();
// Получаем связи idшников и записываем их сохраняем их
$newObjectCompliances = array();
foreach ($parser->getUniqueObjectIds() as $i => $item) {
	$objectId = $_POST['realty_id_'.$i];
	$newObjectCompliances[$item] = $objectId;
}
$parser->saveObjectCompliances($newObjectCompliances);