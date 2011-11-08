<?php
// Реализует экспорт данных из базы
class Export extends Api {

	/**
	 * Список новостных рубрик
	 * @return array
	 */
	public function getNewsSectionsList()
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;

		$this->createXMLRpc();

		$message = new xmlrpcmsg("news.listNewsSections", array(
		    new xmlrpcval($this->_apiKey, $xmlrpcString),
            new xmlrpcval(array(new xmlrpcval('id',$xmlrpcString),new xmlrpcval('name',$xmlrpcString)), $xmlrpcArray))
			
		);
		return $this->sendXMLRpc($message);

	}
}

/**
* Магическая сортировка массива по внутренним полям
* http://php.southpark.com.ua/2007/sortirovka-massiva-po-polyu-ili-uasort-na-steroidax/
* 
* @param array $data
* @param string $sortby
* @return boolean
*/
function masort(&$data, $sortby) {
	static $funcs = array();
	 
	if (empty($funcs[$sortby])) {
		$code = "\$c=0;";
		foreach (split(',', $sortby) as $key) {
			$key = trim($key);
			if (strlen($key)>5 && substr($key, -5)==' DESC') {
				$asc = false;
				$key = substr($key, 0, strlen($key)-5);
			} else {
				$asc = true;
			}
			 
			$array = array_pop($data);
			array_push($data, $array);
			 
			if ($asc) {
				if(is_numeric($array[$key])) {
					$code .= "if ( \$c = ((\$a['$key'] == \$b['$key']) ? 0:((\$a['$key'] " . (($asc)?'<':'>') . " \$b['$key']) ? -1 : 1 )) ) return \$c;";
				} else {
					$code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return " . (($asc)?'':'-') . "\$c;\n";
				}
			}
		}
		$code .= 'return $c;';
		$func = $funcs[$sortby] = create_function('$a, $b', $code);
	} else {
		$func = $funcs[$sortby];
	}
	$func = $funcs[$sortby];
	 
	return uasort($data, $func);
}
