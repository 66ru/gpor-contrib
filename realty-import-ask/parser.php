<?php
spl_autoload_register('autoload');
function autoload($class_name) {
    include './lib/'.$class_name . '.class.php';
}

// Обрабатываем запрос
$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);

if($requestMethod === "post")
{
	$parser = new Parser();
	
	// Сохранение загруженного файла
	if (!empty($_FILES))
	{
		$file = $_FILES['file']['tmp_name'];
		$filetype = strtolower(end(explode(".", $_FILES['file']['name'])));
		
		if($filetype === "csv")
		{
			$parser->saveFile($file);
		}
		else die('Загруженный файл должен быть CSV! Расширение полученного файла '.$filetype);
		
	}

	// Парсим загруженный файл
	$parser->configure(array('id','rooms', 'square', 'price', 'floor'), true, ';');
	$parser->parse();
	
	//Получаем список уже сохраненных соответствий
	$objectCompliancesList = Parser::getObjectCompliancesList();
	
	// Получаем список новостроек
	$export = new Export();
	$realtyObjectsList = $export->getClearedObjectListWithStages();
	
}
else die("Файл не загружен!");

/**
 * Выводит список доступных новостроек
 * @param array $realtyObjectsList
 * @return HTML string
 */
function renderRealtyObjectsList($realtyObjectList = array(), $objectCompliancesList, $item)
{

	$html = '<option value="0">Выбирете новостройку</option>';
	
	foreach ($realtyObjectList as $realtyObject)
	{
		$html .= '<option value="'.$realtyObject['id'].'"';
		
		if(isset($objectCompliancesList->$item) && $objectCompliancesList->$item == $realtyObject['id']) 
			$html .= ' selected="selected" '; 

		$html .= '>'.$realtyObject['name'].'</option>';
	}
	
	return $html;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Парсер новостроек Атомстройкомплекса</title>
  <style type="text/css">
  </style>
</head>
<body>
  <div id="centerLayer">
  <h1>2. Укажите соответствия Id новостроек в базе АСК и базе 66.ru</h1>
  	<form name="parser" action="./import.php" method="post" enctype="multipart/form-data">
  	<table>
  		<thead>
  			<tr>
  				<th>Id в базе АСК</th>
  				<th>Новостройка в базе 66.ru</th>
  			</tr>
  		</thead>
  		<tbody>
		<?php foreach ($parser->getUniqueObjectIds() as $i => $item):?>
		<tr>
			<td>
  				<input size="5" name="id_<?php echo $i ?>" type="text" value="<?php echo $item ?>" disabled="disabled" />
  			</td>
  			<td>	
  			<select name="realty_id_<?php echo $i ?>">
	  			<?php echo renderRealtyObjectsList($realtyObjectsList, $objectCompliancesList, $item) ?>
  			</select>
  			</td>
  		</tr>	
  		<?php endforeach;?>
  		</tbody>
  		<tfoot>
  			<tr align="left">
  				<th colspan="2">
		  			<input type="submit" value="Импортировать" />
  				</th>
  			</tr>
  		</tfoot>
  	</table>
  	</form>
  </div>
</body>
</html>
<?php
