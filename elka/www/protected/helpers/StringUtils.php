<?php
class StringUtils
{
	const PADEG_IMEN = 1;
	const PADEG_RODIT = 10;
	const PADEG_VINIT = 20;

	public static function convertWinToUTF8($inStr)
	{
		return @iconv('windows-1251','utf-8',$inStr);
	}
	
	public static function pluralEnd ( $amount, $ends = array("","","") )
	{
		if ($amount > 10 && $amount < 20) {
			return $ends[2];
		}
		else {
			$res = $amount % 10;
			if ($res == 1) return $ends[0];
			elseif ($res > 1 && $res < 5) return $ends[1];
			else return $ends[2];
		}
	}
	
    public static function normalize($str)
    {
    	return html_entity_decode( strval( $str ), ENT_QUOTES);
    }
    
    public static function safe($str, $normalize = true)
    {
        $str = str_replace( '\\', '\\\\', $str);
        
        if( $normalize )
            $str = self::normalize( $str );
            
    	$str = htmlspecialchars( $str, ENT_QUOTES);
    	$str = preg_replace('@\&amp;#\d+;@', '&mdash;', $str);
    	
    	return $str;
    }

    public static function txt( $str )
    {
    	$str = strval( $str );
    	$str = nl2br( $str );
    	
    	$str = preg_replace( '#&amp;\#[0-9]+;#', '- ', $str );
    	
    	return $str;
    }

    /*
     * Красиво форматирует сумму
     */
    public static function sum( $sum, $opts=false, $cur = true, $nowrap = true )
    {
		$opts = is_array($opts)?$opts:array('',' ','','подключен');
		$cur_array = !$cur?false:($cur===true?array('рубль','рубля','рублей'):(is_array($cur)?$cur:array($cur, $cur, $cur)));
		
		$sum_str = $sum==0?$opts[3]:number_format($sum, 0, '', ' ').$opts[1].($cur?StringUtils::pluralEnd($sum, $cur_array):'');
    	$sum_str = ($nowrap?'<span style="white-space:nowrap;">':'').$opts[0].$sum_str.$opts[2].($nowrap?'</span>':'');
    	return $sum_str;
    }

    /*
     * Красиво форматирует интревал сумм
     * @param int - сумма от
     * @param int - сумма до
     * @param array - оформление ( слово_от, слово_до, до_первой_суммы, после_первой_сумы, перед_второй_суммой, после_второй_суммы, текст_если_суммы_нулевые )
     * @param array - склонения слова валюты
     */
    public static function sumInterval( $sum_from, $sum_to, $opts=false, $cur = true, $nowrap = true )
    {
    	$str = '';
		$opts = is_array($opts)?$opts:array('от ','до','','','',' ','');
		$cur_array = !$cur?false:($cur===true?array('рубль','рубля','рублей'):(is_array($cur)?$cur:array($cur, $cur, $cur)));
		
		if ($sum_from == $sum_to)
		{
			$str = StringUtils::sum($sum_from, array($opts[4], $opts[5], '', $opts[6]), $cur, $nowrap);
		}
		elseif ($sum_from && !$sum_to)
		{
			$str = $opts[0].StringUtils::sum($sum_from, array($opts[4], $opts[5], '', $opts[6]), $cur, $nowrap);
		}
		elseif (!$sum_from && $sum_to)
		{
			$str = $opts[1].'&nbsp;'.StringUtils::sum($sum_to, array($opts[4], $opts[5], '', $opts[6]), false, $nowrap);
		}
		elseif ($sum_from && $sum_to)
		{
			$str = $opts[0].StringUtils::sum($sum_from, array($opts[2], $opts[3], '', $opts[6]), false, $nowrap).'&nbsp;'.$opts[1].'&nbsp;'.StringUtils::sum($sum_to, array($opts[4], $opts[5], '', $opts[6]), $cur, $nowrap);
		}
		
    	return $str;
    }
    
    public static function ageInterval( $age_from, $age_to, $opts=false, $end = true )
    {
		$opts = is_array($opts)?$opts:array('от ','до','','','',' ','');
		$words = !$end?false:($end===true?array('год','года','лет'):(is_array($end)?$end:array($end, $end, $end)));
		
		return self::sumInterval($age_from, $age_to, $opts, $words);
    }

    /*
     * Отображет слово "столько-то вакансий" с правильными числами и падежами
     */
    public static function vacancyWord( $amount, $opts=false, $show_num = true, $padeg = false )
    {
		$opts = is_array($opts)?$opts:array('',' ','','');
		if (!$amount)
			return $opts[3];
		switch ($padeg)
		{
			case self::PADEG_RODIT:
				$words =array('вакансии','вакансий','вакансий');
				break;
			case self::PADEG_VINIT:
				$words =array('вакансию','вакансии','вакансий');
				break;
			default:
				$words =array('вакансия','вакансии','вакансий');
				break;
		}
			
		$vac_word = StringUtils::pluralEnd($amount, $words);
		if ($show_num)
	    	return '<span style="white-space:nowrap;">'.$opts[0].number_format($amount, 0, '', ' ').$opts[1].$vac_word.$opts[2].'</span>';
	    else
	    	return $vac_word;
    }
    
    /*
     * 
     */
    public static function num ( $amount )
    {
    	return '<span style="white-space:nowrap;">'.number_format($amount, 0, '', ' ').'</span>';
    }
    

    /*
     * Красиво форматирует пароль ))
     */    
    public static function generate_pass($count = 6)
    {
    	$str = '0,1,2,3,4,5,6,7,8,9';
            
		$arr = explode(',', preg_replace( '#[\n\r\t\s]#', '', $str ) );
		$c = count( $arr );
            
		$password = '';
            
		for( $i = 0; $i <= $count; $i++ )
			$password .= $arr[ mt_rand( 0, $c - 1 ) ];
                
		return $password;
	}   
	
	public static function unserialize($serial_str)
	{
		$out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str );
		return unserialize($out);
	}

    /**
     * Вырезание заданного количества символов с начала строки с учётом HTML тегов
     * @param string $text Исходный текст
     * @param integer $length Длина вырезаемого куска
     * @param string $tail Текст, дописываемый к вырезаемому куску, если он меньше всего текста
     * @return string Кусок текста заданной длины
     */
    static public function shrink($text, $length, $tail = '…')
    {
        if( mb_strlen($text) > $length )
        {
            $whiteSpacePosition = mb_strpos($text, ' ', $length) - 1;

            if( $whiteSpacePosition > 0 )
            {
                $chars = count_chars(mb_substr($text, 0, ($whiteSpacePosition + 1)), 1);
                if ( isset($chars[ord('<')]) && isset($chars[ord('>')]) && ($chars[ord('<')] > $chars[ord('>')]) )
                {
                    $whiteSpacePosition = mb_strpos($text, '>', $whiteSpacePosition) - 1;
                }
                $text = mb_substr($text, 0, ($whiteSpacePosition + 1));
            }

            // close unclosed html tags
            if( preg_match_all('|<([a-zA-Z]+)|', $text, $aBuffer) )
            {
                if( !empty($aBuffer[1]) )
                {
                    preg_match_all('|</([a-zA-Z]+)>|', $text, $aBuffer2);

                    if( count($aBuffer[1]) != count($aBuffer2[1]) )
                    {
                        foreach( $aBuffer[1] as $index => $tag )
                        {
                            if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag)
                            {
                                $text .= '</'.$tag.'>';
                            }
                        }
                    }
                }
            }

            $text .= $tail;
        }

        return $text;
    }
    
    /*
     * Вырезает все лишнее из заданой строки. Используется для поиска дублей
     */
    public static function baseName ($str = '', $min_len = 4)
    {
    	if (empty($str))
    		return '';
    	$str = str_replace ( array('"', '&laquo;', '&raquo;', '«', '»'), array('','','','',''), $str );
    	$tmp = explode (' ',$str);
    	
    	$parts = array();
    	foreach ($tmp as $part)
    	{
    		if (strlen($part)<($min_len*2))
    			continue;
    		$parts[] = trim($part);
    	}
    	if (count($parts))
    		return implode(' ', $parts);
    	return trim(implode(' ',$tmp));
    }
    

    public static function translit ($str)
    {
    	$result = mb_convert_case($str, MB_CASE_LOWER, "utf-8");
		$result = strtr($result,
			array(
				'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e',
				'ё'=>'jo', 'ж'=>'zh', 'з'=>'z', 'и'=>'i', 'й'=>'jj', 'к'=>'k',
				'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r',
				'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=> 'f', 'х'=>'kh', 'ц'=>'c',
				'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shh', 'ъ'=>'', 'ы'=>'y', 'ь'=>'',
				'э'=>'eh', 'ю'=>'ju', 'я'=>'ja', ' '=>'_', '-'=>'-', '_'=>'_'
			)
		);
		$result = preg_replace('/[^a-z0-9\-\_\.]/u', '', $result);

		return $result;
    }
}
?>