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