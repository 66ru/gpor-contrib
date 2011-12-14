<?php

$ekburgWeather = file_get_contents('http://www.ekburg.ru/.out/weatherSite/weather66.php');
if (!empty($ekburgWeather)) {
	$ekburgWeather = json_decode($ekburgWeather, true);
	if (!empty($ekburgWeather['weather']['deg']) && is_numeric($ekburgWeather['weather']['deg']))
		return $ekburgWeather['weather']['deg'];
}

return false;