<?php

class DateHelper {

	// название месяца на русском
	static function getRusMonth($month)
	{
		if ($month > 12 || $month < 1)
			return false;
		$aMonth = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
		return $aMonth[$month - 1];
	}

	static function formatRusDate($timestamp) {
		$result = date('j ## Y | H:i', $timestamp);
		$rusMonth = self::getRusMonth(date('n', $timestamp));
		$result = str_replace('##', $rusMonth, $result);

		return $result;
	}
}
