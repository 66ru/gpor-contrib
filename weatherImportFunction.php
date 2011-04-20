<?php

// FILE old66.ru inc/xml2array.php

###################################################################################
#
# XML Library, by Keith Devens, version 1.2b
# http://keithdevens.com/software/phpxml
#
# This code is Open Source, released under terms similar to the Artistic License.
# Read the license at http://keithdevens.com/software/license
#
###################################################################################

###################################################################################
# XML_unserialize: takes raw XML as a parameter (a string)
# and returns an equivalent PHP data structure
###################################################################################
function & XML_unserialize(&$xml){
	$xml_parser = &new XML();
	$data = &$xml_parser->parse($xml);
	$xml_parser->destruct();
	return $data;
}
###################################################################################
# XML_serialize: serializes any PHP data structure into XML
# Takes one parameter: the data to serialize. Must be an array.
###################################################################################
function & XML_serialize(&$data, $level = 0, $prior_key = NULL){
	if($level == 0){ ob_start(); echo '<?xml version="1.0" ?>',"\n"; }
	while(list($key, $value) = each($data))
		if(!strpos($key, ' attr')) #if it's not an attribute
			#we don't treat attributes by themselves, so for an empty element
			# that has attributes you still need to set the element to NULL

			if(is_array($value) and array_key_exists(0, $value)){
				XML_serialize($value, $level, $key);
			}else{
				$tag = $prior_key ? $prior_key : $key;
				echo str_repeat("\t", $level),'<',$tag;
				if(array_key_exists("$key attr", $data)){ #if there's an attribute for this element
					while(list($attr_name, $attr_value) = each($data["$key attr"]))
						echo ' ',$attr_name,'="',htmlspecialchars($attr_value),'"';
					reset($data["$key attr"]);
				}

				if(is_null($value)) echo " />\n";
				elseif(!is_array($value)) echo '>',htmlspecialchars($value),"</$tag>\n";
				else echo ">\n",XML_serialize($value, $level+1),str_repeat("\t", $level),"</$tag>\n";
			}
	reset($data);
	if($level == 0){ $str = &ob_get_contents(); ob_end_clean(); return $str; }
}
###################################################################################
# XML class: utility class to be used with PHP's XML handling functions
###################################################################################
class XML{
	var $parser;   #a reference to the XML parser
	var $document; #the entire XML structure built up so far
	var $parent;   #a pointer to the current parent - the parent will be an array
	var $stack;    #a stack of the most recent parent at each nesting level
	var $last_opened_tag; #keeps track of the last tag opened.

	function XML(){
 		$this->parser = &xml_parser_create();
		xml_parser_set_option(&$this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_object(&$this->parser, &$this);
		xml_set_element_handler(&$this->parser, 'open','close');
		xml_set_character_data_handler(&$this->parser, 'data');
	}
	function destruct(){ xml_parser_free(&$this->parser); }
	function & parse(&$data){
		$this->document = array();
		$this->stack    = array();
		$this->parent   = &$this->document;
		return @xml_parse(&$this->parser, &$data, true) ? $this->document : NULL;
	}
	function open(&$parser, $tag, $attributes){
		$this->data = ''; #stores temporary cdata
		$this->last_opened_tag = $tag;
		if(is_array($this->parent) and array_key_exists($tag,$this->parent)){ #if you've seen this tag before
			if(is_array($this->parent[$tag]) and array_key_exists(0,$this->parent[$tag])){ #if the keys are numeric
				#this is the third or later instance of $tag we've come across
				$key = count_numeric_items($this->parent[$tag]);
			}else{
				#this is the second instance of $tag that we've seen. shift around
				if(array_key_exists("$tag attr",$this->parent)){
					$arr = array('0 attr'=>&$this->parent["$tag attr"], &$this->parent[$tag]);
					unset($this->parent["$tag attr"]);
				}else{
					$arr = array(&$this->parent[$tag]);
				}
				$this->parent[$tag] = &$arr;
				$key = 1;
			}
			$this->parent = &$this->parent[$tag];
		}else{
			$key = $tag;
		}
		if($attributes) $this->parent["$key attr"] = $attributes;
		$this->parent  = &$this->parent[$key];
		$this->stack[] = &$this->parent;
	}
	function data(&$parser, $data){
		if($this->last_opened_tag != NULL) #you don't need to store whitespace in between tags
			$this->data .= $data;
	}
	function close(&$parser, $tag){
		if($this->last_opened_tag == $tag){
			$this->parent = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_pop($this->stack);
		if($this->stack) $this->parent = &$this->stack[count($this->stack)-1];
	}
}
function count_numeric_items(&$array){
	return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
}
?>



<?php

// FILE old66.ru inc/weather_functions.php

// ЕКАТЕРИНБУРГ
$weather_main_city_id = 28440;

$weatherTextArray = array( 0 => 'гроза', 1 => 'гроза', 2 => 'гроза', 3 => 'гроза', 4 => 'гроза', 5 => 'снег', 6 => 'снег', 7 => 'снег', 8 => 'град', 9 => 'град', 10 => 'град', 11 => 'дождь', 12 => 'снег', 13 => 'снег', 14 => 'снег', 15 => 'снег', 16 => 'снег', 17 => 'град', 18 => 'снег', 19 => 'снег', 20 => 'облачно', 21 => 'облачно', 22 => 'облачно', 23 => 'пасмурно', 24 => 'пасмурно', 25 => 'пасмурно', 26 => 'пасмурно', 27 => 'облачно', 28 => 'переменная облачность', 29 => 'переменная облачность', 30 => 'переменная облачность', 31 => 'ясно', 32 => 'ясно', 33 => 'ясно', 34 => 'ясно', 35 => 'ливень', 36 => 'ясно', 37 => 'гроза', 38 => 'ливень', 39 => 'гроза', 40 => 'ливень', 41 => 'снег', 42 => 'снег', 43 => 'снег', 44 => 'переменная облачность', 45 => 'ливень', 46 => 'снег', 47 => 'гроза', 3200 => '');
$weather_icons = array(0 => 8, 1 => 8, 2 => 8, 3 => 8, 4 => 8, 5 => 6, 6 => 6, 7 => 6, 8 => 7, 9 => 7, 10 => 7, 11 => 4, 12 => 6, 13 => 6, 14 => 6, 15 => 6, 16 => 6, 17 => 7, 18 => 6, 19 => 6, 20 => 2, 21 => 2, 22 => 2, 23 => 3, 24 => 3, 25 => 3, 26 => 3, 27 => 11, 28 => 1, 29 => 10, 30 => 1, 31 => 9, 32 => 0, 33 => 9, 34 => 0, 35 => 5, 36 => 0, 37 => 8, 38 => 5, 39 => 8, 40 => 5, 41 => 6, 42 => 6, 43 => 6, 44 => 1, 45 => 5, 46 => 6, 47 => 8, 3200 => 0);

$weatherIcoArray = array (
0 => 0,
1 => 4,
2 => 5,
3 => 6,
4 => 8,
5 => 6,
6 => 0,
7 => 0,
8 => 1,
9 => 1,
10 => 2,
11 => 3,
12 => 3,
13 => 0,
14 => 7,
15 => 0
);

$dayper = array('ночь','утро','день','вечер');
$week = array('Вс','Пн','Вт','Ср','Чт','Пт','Сб','Вс');

$citiesOtherNames = array(
	28440=>'Екатеринбурге',
	28240=>'Нижнем Тагиле',
	28044=>'Серове',
	23921=>'Ивдели',
	28449=>'Каменск-Уральском',
	28344=>'Невьянске',
	28351=>'Ирбите',
	28135=>'Качканаре',
	28437=>'Первоуральске',
);

$citiesNamesLink = array(
	28440=>'ekaterinburg',
	28240=>'nizhniy-tagil',
	28044=>'serov',
	23921=>'ivdel',
	28449=>'kamensk-uralskiy',
	28344=>'nevyansk',
	28351=>'irbit',
	28135=>'kachkanar',
	28437=>'pervouralsk',
);



$thistext = array();
$thistext[0] = 'Ясно';
$thistext[1] = 'Переменная облачность';
$thistext[2] = 'Облачно';
$thistext[3] = 'Пасмурно';
$thistext[4] = 'Дождь';
$thistext[5] = 'Ливень';
$thistext[6] = 'Снег';
$thistext[7] = 'Град';
$thistext[8] = 'Гроза';
$thistext[11] = 'Ясно';
$thistext[12] = 'Переменная облачность';
$thistext[13] = 'Облачно';

$months = array(
	1 => 'января',
	2 => 'февраля',
	3 => 'марта',
	4 => 'апреля',
	5 => 'мая',
	6 => 'июня',
	7 => 'июля',
	8 => 'августа',
	9 => 'сентября',
	10 => 'октября',
	11 => 'ноября',
	12 => 'декабря',
	);

$thiswind = array(
	0 => 'c',
	1 => 'cв',
	2 => 'в',
	3 => 'юв',
	4 => 'ю',
	5 => 'юз',
	6 => 'з',
	7 => 'сз',
	8 => 'штиль',
	9 => 'разного направления',
);

$thisicons = array();
$thisicons[0] = 0;
$thisicons[1] = 1;
$thisicons[2] = 2;
$thisicons[3] = 3;
$thisicons[4] = 4;
$thisicons[5] = 5;
$thisicons[6] = 6;
$thisicons[7] = 7;
$thisicons[8] = 8;

$clouds_descr=array(
					"ясно","ясно","малооблачно","небольшая облачность","переменная облачность","переменная облачность",
					"облачно с прояснениями","облачность с просветами","пасмурно","неба не видно","слабая облачность"
				);

$codes_descr=array(
					"","облачность","облачность","облачность","облачность","мгла",
					"пыль в воздухе","пыль поднятая ветром","пыльные вихри","пыльная буря","дымка",
					"туман","туман","зарница","осадки","осадки",
					"осадки","гроза","шквал","смерчь","морось, снежные зерна",
					"дождь","снег","дождь со снегом","осадки","ливневый дождь",
					"ливневый дождь со снегом","град, крупа","туман","гроза","пыльная буря",
					"пыльная буря","пыльная буря","сильная пыльная буря","сильная пыльная буря","сильная пыльная буря",
					"поземок","сильный поземок","метель","сильная метель","облачность",
					"местами туман","туман","сильный туман","туман","сильный туман",
					"туман","сильный туман","туман с отложениями изморози","сильный туман с отложениями изморози","временами слабая морось",
					"слабая морось","временами морось","морось","временами сильная морось","сильная морось",
					"слабая замерзающая морось","сильная замерзающая морось","слабая морось с дождем","сильная морось с дождем","временами слабый дождь",
					"слабый дождь","временами дождь","дождь","временами сильный дождь","сильный дождь",
					"слабый дождь, образующий гололед","сильный дождь, образующий гололед","слабый дождь со снегом","сильный дождь со снегом","временами слабый снег",
					"слабый снег","временами снег","снег","временами сильный снег","сильный снег",
					"ледяные иглы","снежные зерна","отдельные снежинки","ледяной дождь","слабый ливневый дождь",
					"сильный ливневый дождь","ливневый дождь","слабый ливневый дождь со снегом","сильный ливневый дождь со снегом","слабый снег",
					"сильный снег","слабая ливневая крупа","ливневая крупа","слабый град","сильный град",
					"слабый дождь, гроза","сильный дождь, гроза","слабый дождь со снегом, гроза","сильный дождь со снегом, гроза","гроза",
					"гроза, град","сильная гроза","гроза, пыльная буря","сильная гроза, град"
				);

/*
 * 0 - ясно
 * 1 - переменная облачность
 * 2 - облачно
 * 3 - пасмурно
 * 4 - дождь
 * 5 - ливень
 * 6 - снег
 * 7 - град
 * 8 - гроза
 * 9 - вечером ясно
 * 10 - вечером переменная облачность
 * 11 - вечером облачно
*/

// HNM Icons -> 66 Icons
$code_to_66icon = array(
	'0'=>0,
	'100' => 0,
	'101' => 0,
	'1010' => 0,
	'102' => 0,
	'103' => 2,
	'104' => 2,
	'105' => 2,
	'106' => 2,
	'107' => 3,
	'108' => 3,
	'109' => 0,
	'11' => 0,
	'12' => 0,
	'13' => 8,
	'17' => 8,
	'18' => 0,
	'19' => 0,
	'20' => 4,
	'21' => 4,
	'22' => 6,
	'23' => 6,
	'24' => 6,
	'25' => 4,
	'26' => 4,
	'27' => 4,
	'28' => 0,
	'29' => 8,
	'30' => 0,
	'31' => 0,
	'32' => 0,
	'33' => 0,
	'34' => 0,
	'35' => 0,
	'36' => 6,
	'37' => 6,
	'38' => 6,
	'39' => 6,
	'41' => 0,
	'42' => 0,
	'43' => 0,
	'44' => 0,
	'45' => 0,
	'46' => 0,
	'47' => 0,
	'48' => 0,
	'49' => 0,
	'50' => 4,
	'51' => 4,
	'52' => 4,
	'53' => 4,
	'54' => 4,
	'55' => 4,
	'56' => 4,
	'57' => 4,
	'58' => 4,
	'59' => 4,
	'6' => 0,
	'60' => 4,
	'61' => 4,
	'62' => 4,
	'63' => 5,
	'64' => 4,
	'65' => 4,
	'66' => 4,
	'67' => 4,
	'68' => 4,
	'69' => 4,
	'7' => 0,
	'70' => 6,
	'71' => 6,
	'72' => 6,
	'73' => 6,
	'74' => 6,
	'75' => 0,
	'76' => 0,
	'77' => 3,
	'78' => 0,
	'79' => 4,
	'8' => 0,
	'80' => 4,
	'81' => 4,
	'82' => 4,
	'83' => 4,
	'84' => 4,
	'85' => 6,
	'86' => 6,
	'87' => 6,
	'88' => 6,
	'89' => 6,
	'9' => 0,
	'90' => 3,
	'91' => 8,
	'92' => 8,
	'93' => 8,
	'94' => 8,
	'95' => 8,
	'96' => 8,
	'97' => 8,
	'98' => 8,
	'99' => 8,
);


function code_repl($Yc,$Cb)
	{
	global $clouds_descr, $codes_descr;
	
	$weatherStatus = 0;

	$a = array(0,1,2,3,4,5,6,7,8,10,14,15,16,36,37,40);

	if ($Yc=="-" && $Cb=="-")
		{
		$rico = 0;
		$weatherStatus = count($codes_descr);
		$rtext = $clouds_descr[$rico];
		}
	else
		{
		if ($Yc=="" || $Yc=="-" || in_array($Yc,$a))
			 {
			 $rico = "10".$Cb;
			 $rtext = $clouds_descr[$Cb];
			 $weatherStatus = count($codes_descr)+$Cb;
			 }
		else
			{
			$m7=	Array(21=>25,22=>74,23=>26,24=>26,82=>81,88=>87,90=>89);
			$br7=	Array(25=>82,26=>67,27=>87);
			$b7=	Array(29=>97,50=>51,52=>53,54=>55,60=>61,62=>63,64=>65,70=>71,72=>73,74=>75,76=>71,78=>71,80=>61,81=>82,83=>66,84=>67,85=>71,86=>75,87=>88,89=>90,91=>97,92=>97,93=>99,94=>99,95=>97,96=>99);
			$b2_7=	Array(76=>70,78=>70);

			$rico=$Yc;

			If (($Cb<7) AND (IsSet($m7[$Yc]))) $rico=$m7[$Yc];
			If (($Cb>=7) AND (IsSet($br7[$Yc]))) $rico=$br7[$Yc];
			If (($Cb>7) AND (IsSet($b7[$Yc]))) $rico=$b7[$Yc];
			If (($Cb>2) AND ($Cb<7) AND IsSet($b2_7[$Yc])) $rico=$b2_7[$Yc];


			$rtext = $codes_descr[$rico];
			$weatherStatus = $rico;
			}
		}

	return array($rico,$rtext, $weatherStatus);
	}

function saveCurrentWeatherData_toNew66($t,$p,$api = 'api.dev.66.ru')
	{
	if ($api=='')$api = 'api.dev.66.ru';
	$hash = 'c9762056aeddef7a5327f46e5363828b';
	$debug=0;



	$client = new xmlrpc_client('/', $api, 80);
	$client->return_type = 'xmlrpcvals';
	$client->setDebug($debug);
	$msg = new xmlrpcmsg('weather.setCurrentWeather');

	$p1 = new xmlrpcval($hash, 'string');
	$msg->addparam($p1);

	$p2 = array('temperature' => $t, 'precipitation' => $p);
	$p2 =& php_xmlrpc_encode($p2);
	$msg->addparam($p2);

	$res =& $client->send($msg, 0, 'http11');

	if ($res->faultcode())
		return $res;
	else
		return php_xmlrpc_decode($res->value());
	}


function setWeeather_toNew66($data, $api = 'api.dev.66.ru') {
	if ($api=='')$api = 'api.dev.66.ru';
	$hash = 'c9762056aeddef7a5327f46e5363828b';
	$debug=0;

	$client = new xmlrpc_client('/', $api, 80);
    $client->return_type = 'xmlrpcvals';
    $client->setDebug($debug);
    $msg = new xmlrpcmsg('weather.updateWeather');


    $p1 = new xmlrpcval($hash, 'string');
    $msg->addparam($p1);

    $p2 = $data;
    $p2 =& php_xmlrpc_encode($p2);
    $msg->addparam($p2);

    $res =& $client->send($msg, 0, 'http11');


    if ($res->faultcode())
    	return $res;
    else
    	return php_xmlrpc_decode($res->value());
}

function weather_u2w($s)
{
	return @iconv('utf-8','windows-1251',$s);
}

function wind_direct($degres)
	{
	If ($degres==0) 													 return 8;
	If ($degres==990) 													 return 9;

	If (($degres>0 AND $degres<=11) OR ($degres>=349 AND $degres<=360))	 return 0;
	If ($degres>=12 AND $degres<=33)									 return 0;
	If ($degres>=34 AND $degres<=56)									 return 1;
	If ($degres>=57 AND $degres<=78)									 return 1;
	If ($degres>=79 AND $degres<=101)									 return 2;
	If ($degres>=102 AND $degres<=123)									 return 2;
	If ($degres>=124 AND $degres<=146)									 return 3;
	If ($degres>=147 AND $degres<=168)									 return 3;
	If ($degres>=169 AND $degres<=191)									 return 4;
	If ($degres>=192 AND $degres<=214)									 return 4;
	If ($degres>=215 AND $degres<=236)									 return 5;
	If ($degres>=237 AND $degres<=258)									 return 5;
	If ($degres>=259 AND $degres<=281)									 return 6;
	If ($degres>=282 AND $degres<=303)									 return 6;
	If ($degres>=304 AND $degres<=326)									 return 7;
	If ($degres>=327 AND $degres<=348)									 return 7;
	}

function getCloudiness($c_from_xml)
	{
	/*
	 * -------------- 66 ---------------
	 * 0 - ясно
	 * 1 - переменная облачность
	 * 2 - облачно
	 * 3 - пасмурно
	 * 4 - дождь
	 * 5 - ливень
	 * 6 - снег
	 * 7 - град
	 * 8 - гроза
	 * 9 - вечером ясно
	 * 10 - вечером переменная облачность
	 * 11 - вечером облачно
	 *
	 * -------------- HMN ---------------
	    [1] => дождь
	    [2] => снег
	    [3] => дождь, возможен град
	    [4] => осадки
	    [5] => облачно
	    [6] => переменная облачность
	    [7] => ясно
	    [8] => дождь, гроза
	    [9] => переменная облачность, дождь
	    [10] => переменная облачность, небольшой дождь
	    [11] => облачно, небольшой дождь
	    [12] => переменная облачность, небольшой снег
	    [13] => облачно, небольшой снег
	    [14] => переменная облачность, небольшие осадки
	    [15] => облачно, небольшие осадки
	    [16] => облачно, без существенных осадков
	    [17] => метель

	*/

	if ($c_from_xml == 1) return 3;
	elseif ($c_from_xml == 2) return 3;
	elseif ($c_from_xml == 3) return 3;
	elseif ($c_from_xml == 4) return 1;
	elseif ($c_from_xml == 5) return 2;
	elseif ($c_from_xml == 6) return 1;
	elseif ($c_from_xml == 7) return 0;
	elseif ($c_from_xml == 8) return 3;
	elseif ($c_from_xml == 9) return 1;
	elseif ($c_from_xml == 10) return 1;
	elseif ($c_from_xml == 11) return 2;
	elseif ($c_from_xml == 12) return 1;
	elseif ($c_from_xml == 13) return 2;
	elseif ($c_from_xml == 14) return 1;
	elseif ($c_from_xml == 15) return 2;
	elseif ($c_from_xml == 16) return 2;
	elseif ($c_from_xml == 17) return 1;
	else return 0;
	}

function getPrecipitation($c_from_xml)
	{
	/*
	 * -------------- 66 ---------------
	 * 4 - дождь
	 * 5 - ливень
	 * 6 - снег
	 * 7 - град
	 * 8 - гроза
	 *
	 * -------------- HMN ---------------
	    [1] => дождь
	    [2] => снег
	    [3] => дождь, возможен град
	    [4] => осадки
	    [8] => дождь, гроза
	    [9] => переменная облачность, дождь
	    [10] => переменная облачность, небольшой дождь
	    [11] => облачно, небольшой дождь
	    [12] => переменная облачность, небольшой снег
	    [13] => облачно, небольшой снег
	    [14] => переменная облачность, небольшие осадки
	    [15] => облачно, небольшие осадки
	    [17] => метель

	*/

	if ($c_from_xml == 1) return 4;
	elseif ($c_from_xml == 2) return 6;
	elseif ($c_from_xml == 3) return 4;
	elseif ($c_from_xml == 4) return 4;
	elseif ($c_from_xml == 8) return 8;
	elseif ($c_from_xml == 9) return 4;
	elseif ($c_from_xml == 10) return 4;
	elseif ($c_from_xml == 11) return 4;
	elseif ($c_from_xml == 12) return 6;
	elseif ($c_from_xml == 13) return 6;
	elseif ($c_from_xml == 14) return 4;
	elseif ($c_from_xml == 15) return 4;
	elseif ($c_from_xml == 17) return 6;
	else return 0;
	}



?>