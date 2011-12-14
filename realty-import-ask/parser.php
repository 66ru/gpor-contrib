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
	$parser->configure(array('id','rooms', 'square', 'price', 'floor', 'visible'), true, ';');
	$parser->parse();

	//Получаем список уже сохраненных соответствий
	$objectCompliancesList = $parser->getObjectCompliancesList();

	// Получаем список новостроек
	$export = new Export();
	$realtyObjectsList = $export->getClearedObjectListWithStages(1);

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
<script src="js/jquery.min.js" type="text/javascript"></script>
<script>
var getStat = function() {
	$.getJSON('./getStat.php', function(data) {

		  $('#log').html('');
		  $.each(data, function(key, val) {
			if(key !="error") {
				if(key!="total")
				{
					$('#log').append(key + ' Добавлено: ' + val.added + ' Отредактировано: ' + val.edited + ' Удалено: ' + val.deleted + ' Ошибок: ' + val.error);
				}
				else if(val != 0) {
						$('#total').html('Осталось объявлений: ' + val);
					}
					else {
						clearInterval(t);
						$('#total').html('Импорт завершен');
						$('input[type="submit"]').removeAttr('disabled');
						$('#wait').remove();
					}
				} 
		  				
		  });
			    	//items.push('<b>'+ key + '</b>' + val);
		  });
};
var t;
$(function() {

	$('form select[name^="realty_id_"]').each(function(index) {
		index2 = $(this).attr('name').substr(10);
		item = $('form input[name="id_' + index2 +'"]').val();
		if($(this).val() != '0') {
			var objects = {};
			objects = '{ "'+ item +'":"'+ $(this).val() + '"}';
			var title = '<p>'+$(this).children('option:selected').text()+'</p>';
		}
	});
				
	$('form').submit(function(){
		$.ajax({
			url:  $(this).attr("action"),
			type: 'POST',
			data: $('form').serialize(),
			cache: false,
			success: function(data) {
				$('#content').html(data);
				$('input[type="submit"]').attr('disabled','disabled').after('&nbsp;<div id="wait"><b>Импорт запущен...</b></div>');
				$('#total').html(''); 
				t = setInterval("getStat()", 10000);
				},
			error: function(data, status) {
				$('#log').html(data.responseText);
				clearInterval(t);
				$('#total').html('Импорт завершен');
				$('input[type="submit"]').removeAttr('disabled');
				$('#wait').remove();				
				}
			});
		
		return false;	
		});
		
});
</script>
<title>Парсер новостроек Атомстройкомплекса</title>
<style type="text/css">
</style>
</head>
<body>
	<div id="centerLayer">
		<h1>2. Укажите соответствия Id новостроек в базе АСК и базе 66.ru</h1>
		<form name="parser" action="./import.php" method="post"
			enctype="multipart/form-data">
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
						<td><input size="5" name="id_<?php echo $i ?>" type="text"
							value="<?php echo $item ?>" disabled="disabled" />
						</td>
						<td><select name="realty_id_<?php echo $i ?>">
	  			<?php echo renderRealtyObjectsList($realtyObjectsList, $objectCompliancesList, $item) ?>
						</select>
						</td>
					</tr>
					
					
					
					
					
					
					
						
  		<?php endforeach;?>
  		</tbody>
				<tfoot>
					<tr align="left">
						<th colspan="2"><input type="submit" value="Импортировать" />
						</th>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
	<div id="result">
		<h2>Отчет</h2>
		<div id="total"></div>
		<div id="log"></div>
		<div id="content"></div>
	</div>
</body>
</html>


<?php
