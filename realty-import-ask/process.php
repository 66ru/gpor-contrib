<?php
spl_autoload_register('autoload');
function autoload($class_name) {
	include './lib/'.$class_name . '.class.php';
}

$data = unserialize(base64_decode($_POST['data']));
$objects = $_POST['objects'];
 
$parser = new Parser();
$parser->data = $data;
$parser->objectComplianceList = json_decode($objects);

$parser->prepareAnnonceListAndImport();
if(($count = count($parser->announcesNotUsed)) > 0)
{
	echo 'Не добавлено: '.$count .'<br />';
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