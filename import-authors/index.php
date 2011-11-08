<?php
spl_autoload_register('autoload');
function autoload($class_name) {
	include './lib/'.$class_name . '.class.php';
}

$authorImport = new AuthorImport();

$conn = mysql_connect($authorImport::$mysqlhost, $authorImport::$mysqluser, $authorImport::$mysqlpswd);

if (!$conn) {
    echo "Unable to connect to DB: " . mysql_error();
    exit;
}

if (!mysql_select_db($authorImport::$mysqldb)) {
    echo "Unable to select mydbname: " . mysql_error();
    exit;
}

mysql_query("SET NAMES `cp1251`");

$sql = "SELECT id , fio, job
        FROM   mod_authors
        WHERE  active = 1";

$result = mysql_query($sql);

if (!$result) {
    echo "Could not successfully run query ($sql) from DB: " . mysql_error();
    exit;
}

if (mysql_num_rows($result) == 0) {
    echo "No rows found, nothing to print so am exiting";
    exit;
}
$data = array();

while ($row = mysql_fetch_assoc($result)) {
    $data[] = $row;
}

mysql_free_result($result);

// Обрабатываем запрос
$requestMethod = strtolower($_SERVER['REQUEST_METHOD']);



if($requestMethod === "post")
{
    $compliances = array();
    foreach($data as $row) {

        if(!empty($_POST['news_section_id_'.$row['id']]))
        {
            $compliances[$row['id']] =  $_POST['news_section_id_'.$row['id']];
        }
    }

    $authorImport->writeCompliancesFile(json_encode($compliances));

    echo('Усе гут!');
    die();
}
else {
	// Получаем список рубрик
	$export = new Export();
	$newsSectionList = $export->getNewsSectionsList();

	//Получаем список уже сохраненных соответствий
	$authorsCompliancesList = $authorImport->getAuthorsCompliancesList();

/**
 * Выводит список доступных рубрик
 * @param array $newsSectionList
 * @return HTML string
 */
function renderNewsSectionsList($newsSectionList = array(), $authorsCompliancesList = null, $item = null)
{

	$html = '<option value="0">Выбирете рубрику</option>';

	foreach ($newsSectionList as $newsSection)
	{
		$html .= '<option value="'.$newsSection['id'].'"';

		if(isset($authorsCompliancesList->$item) && $authorsCompliancesList->$item == $newsSection['id'])
		$html .= ' selected="selected" ';

		$html .= '>'.$newsSection['name'].'</option>';
	}

	return $html;
}



}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Импорт авторских колонок</title>
  <style type="text/css">
  </style>
</head>
<body>
	<div id="centerLayer">
		<h1>Укажите соответствия автора и рубрики в которую будут импортированы все его публикации</h1>
		<form name="parser" action="./index.php" method="post"
			enctype="multipart/form-data">
			<table>
				<thead>
					<tr>
						<th>Автор</th>
						<th>Рубрика</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($data as $author):?>
					<tr>
						<td><input size="100" name="id_<?php echo $author['id'] ?>" type="text"
							value="<?php echo iconv('Windows-1251','UTF-8',$author['fio']) ?> ( <?php echo iconv('Windows-1251','UTF-8',$author['job']) ?> )" disabled="disabled"  />
						</td>
						<td><select name="news_section_id_<?php echo $author['id'] ?>">
	  			<?php echo renderNewsSectionsList($newsSectionList, $authorsCompliancesList,$author['id']) ?>
						</select>
						</td>
					</tr>
  		        <?php endforeach;?>
  		    </tbody>
				<tfoot>
					<tr align="left">
						<th colspan="2"><input type="submit" value="Готово!" />
						</th>
					</tr>
				</tfoot>
			</table>
		</form>
	</div>
</body>
</html>