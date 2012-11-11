<?php
class DateUtils
{
	const PERIOD_DAY = 'DAY';
	const PERIOD_HOUR = 'HOUR';
	
	const FORMAT_MYSQL = 10;
	const FORMAT_TIMESTAMP = 10;
	
	static $months = array(
   	  	  1 => 'Января',
   	  	  2 => 'Февраля',
   	  	  3 => 'Марта',
   	  	  4 => 'Апреля',
   	  	  5 => 'Мая',
   	  	  6 => 'Июня',
   	  	  7 => 'Июля',
   	  	  8 => 'Августа',
   	  	  9 => 'Сентября',
   	  	  10 => 'Октября',
   	  	  11 => 'Ноября',
   	  	  12 => 'Декабря',
	);   	
	
	// Получаем период при помощи mySQL	
	public static function GetPeriod($in = self::PERIOD_DAY, $toDate, $fromDate = false)
	{
		$fromDate = $fromDate?DateUtils::toMySql($fromDate):DateUtils::toMySql(time());
		$toDate = $toDate?DateUtils::toMySql($toDate):DateUtils::toMySql(time());
		
		$comand = Yii::app()->db->createCommand('SELECT TIMESTAMPDIFF( '.$in.', "'.$fromDate.'", "'.$toDate.'")');
		return $comand->queryScalar();
	}

	// В mySQL формат
	public static function toMysql($date)
	{
		if (strstr('-', $date) )
			return $date;
		return Yii::app()->dateFormatter->format('y-M-d H:m:s', $date);
	}
	
	// Из mySQL формата
	public static function fromMysql($date)
	{
		return CDateTimeParser::parse($date, 'y-M-d H:m:s');
	}	
	
	// Из календаря
	public static function fromForm($date, $setTime = true, $pattern = 'dd.MM.yyyy')
	{
		return CDateTimeParser::parse($date.($setTime?' '.Yii::app()->dateFormatter->format('H:m:s', time()):''), $pattern.($setTime?' H:m:s':''));
	}	
	
	// В календарь
	public static function toForm($date, $pattern = 'dd.MM.yyyy')
	{
		return Yii::app()->dateFormatter->format($pattern, $date);
	}	

	// Возвращает период в днях
	public static function periodInHours($toDate, $fromDate = false)
	{
		if (!$fromDate)
			$fromDate = time();

		return ceil(($toDate - $fromDate) / 60 / 60);
	}
	
	// Возвращает дату из кол-ва часов
	public static function hoursToDate($hours)
	{
		return time() + ($hours * 60 * 60);
	}	
	
	// Возвращает дату из кол-ва дней
	public static function daysToDate($days)
	{
		return time() + ($days * 24 * 60 * 60);
	}	
	
	// Возвращает формулировку кол-ва дней
	public static function formatDaysAmount ($amount, $type=false, $opts=false)
	{
		$opts = is_array($opts)?$opts:array('',' ','','подключен');
		$type = $type===false?self::PERIOD_DAY:$type;
		if ($amount > 0)
		{
		if ($type == self::PERIOD_HOUR)
			$amount = ceil ($amount/24);
		}
		elseif ($amount < 0)
		{
			return $opts[3];
		}
		return $opts[0].$amount.$opts[1].StringUtils::pluralEnd($amount,array('день','дня','дней')).$opts[2];
	}
	
	// Прикольно форатирует mySQL дату
    public static function _date($datetime = false, $todayFormat = true, $time = true)
    {
    	if( $datetime == false )
    		$datetime = date('Y-m-d H:i:s');
    		
    	if( is_numeric($datetime) )
    		$datetime = date('Y-m-d '.($time?'H:i:s':'00:00:00'), $datetime);
    	else
	   		$datetime = $datetime.(!$time?'00:00:00':'');  

    	if( !preg_match('#(([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}))?\s*(([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}))?#', trim($datetime), $p ) )
    		return false;
    		
    	list(,, $y, $m, $d, , $h, $i, $s ) = array_map('intval', $p);
    	
    	$out = '';
    	
    	if( $m && $d )
    	{
    	    if( $todayFormat && $d.'-'.$m.'-'.$y == date('j-n-Y') )
    	      $out = 'Сегодня';
    	    else
    		  $out = $d.' '.self::$months[$m].($y && $y != date('Y') ? ' '.$y : '');
    	}
    	
    	if( $time && ($h > 0 || ($h == 0 && $i)) )
    		$out .= ($out ? ' ' : '').($h < 10 ? '0' : '').$h.':'.($i < 10 ? '0' : '').$i;
    	
    	return $out;
    }
    
    public static function toTimeStamp($date)
    {
    	if (!strstr(':', $date))
    		return $date;
    	else
    		return strtotime($date);
    }
}
?>