<?php 
switch ($code)
{
	case 404:
		echo '<h1>Ошибка 404. Страница не найдена.</h1>';
		break;
	default:
		echo '<h1>Ошибка '.$code.'.</h1>';
		echo '<div>'.$message.'.</div>';
		break;
}
?>