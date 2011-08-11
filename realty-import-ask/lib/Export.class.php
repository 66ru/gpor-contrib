<?php
// Реализует экспорт данных из базы
class Export extends Api {

	/**
	 * Список новостроек
	 * @return array
	 */
	private function getObjectList()
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;

		$this->createXMLRpc();

		$message = new xmlrpcmsg("realty.objectList", array(
		new xmlrpcval($this->_apiKey, $xmlrpcString))
			
		);

		return $this->sendXMLRpc($message);

	}

	/**
	 * Оставит в списке новостроек только те, которые принадлежат импортируемому застройщику
	 * @return array
	 */
	public function getClearedObjectList()
	{
		$objectList = $this->getObjectList();
		$developerObjectList = array();

		foreach ($objectList as $object)
		{
			if($object['developerId'] == $this->developerId)
			$developerObjectList[] = $object;
		}

		return $developerObjectList;
	}

	/**
	 * Оставит в списке новостроек только те, которые принадлежат импортируемому застройщику и дополнит список очередями
	 * @return array
	 */
	public function getClearedObjectListWithStages($minStage = 1)
	{
		$objectList = $this->getObjectList();
		$developerObjectList = array();

		foreach ($objectList as $object)
		{
			if($object['developerId'] == $this->developerId) {
				$stageListOfObject = $this->getStageListOfObject($object['id']);
					
				if(!empty($stageListOfObject) && count($stageListOfObject) >= $minStage)
				{
					foreach ($stageListOfObject as $stage)
					{
						$developerObjectList[] = array('id' => $object['id'].'.'.$stage['id'], 'name' => $object['name'].' ('.$stage['name'].')');
					}
				}
				else
				{
					$developerObjectList[] = $object;
				}
					
				unset($stageListOfObject);
			}
		}
		masort($developerObjectList, 'name');
		return $developerObjectList;
	}
	

	/**
	 * Дополняет список "чистых" новостроек еще очередями
	 * @return array
	 */
	public function getClearedStageListOfObject()
	{
		$clearedObjectList = $this->getClearedObjectList();
		$clearedObjectListOfObject = array();

		foreach ($clearedObjectList as $i => $object)
		{
			$stageListOfObject = $this->getStageListOfObject($object['id']);

			if(!empty($stageListOfObject))
			{

				$clearedObjectList = array_push($clearedObjectList, $i, 1, $stageListOfObject);
				//var_dump($clearedObjectListWithStages);
			}
		}
		return $clearedObjectListWithStages;
	}

	/**
	 * Список очередей новостройки
	 * @return array
	 */
	public function getStageListOfObject($objectId = 0)
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;

		$this->createXMLRpc();

		$message = new xmlrpcmsg("realty.stageListOfObject", array(
		new xmlrpcval($this->_apiKey, $xmlrpcString),
		new xmlrpcval($objectId, $xmlrpcInt))
			
		);

		return $this->sendXMLRpc($message);

	}

	/**
	 * Список планировок
	 * @param integer $objectId
	 * @return array
	 */
	public function getFlatListOfObject($objectId = 0)
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;

		$this->createXMLRpc();

		$message = new xmlrpcmsg("realty.flatListOfObject", array(
		new xmlrpcval($this->_apiKey, $xmlrpcString),
		new xmlrpcval($objectId, $xmlrpcInt))
			
		);

		return $this->sendXMLRpc($message);
	}

	/**
	 * Список объявлений
	 * @param integer $objectId
	 * @return array
	 */
	private function getAnnounceListOfObject($objectId = 0)
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;

		$this->createXMLRpc();

		$message = new xmlrpcmsg("realty.announceListOfObject", array(
		new xmlrpcval($this->_apiKey, $xmlrpcString),
		new xmlrpcval($objectId, $xmlrpcInt))
			
		);

		return $this->sendXMLRpc($message);

	}

	/**
	 * Оставит в списке новостроек только те,
	 * которые принадлежат импортируемому застройщику (агенству недвижимости)
	 * @param integer $objectId
	 * @return array
	 * */
	public function clearAnnounceListOfObject($objectId)
	{
		$announceList = $this->getAnnounceListOfObject($objectId);
		$developerAnnounceList = array();

		foreach ($announceList as $announce)
		{
			if($announce['agencyId'] == $this->agencyId)
			$developerAnnounceList[] = $announce;
		}

		return $developerAnnounceList;
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
