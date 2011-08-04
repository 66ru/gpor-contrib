<?php
class Import extends Api {
	
	/**
	 * Статистика по работе импорта
	 * @var array
	 */
	public $statistics = array(
					'added' 	=> 0,
					'edited' 	=> 0,
					'deleted'	=> 0);

	/**
	 * Формирует запросы к API на изменение или добавление 
	 * @param unknown_type $data
	 */
	public function importAnnounceList($data = array())
	{
		foreach ($data as $objectId => $objectGroup)
		{
			foreach ($objectGroup as $objectFlat)
			{
				$importData = array(
							'flatId' 	=> $objectFlat->flatId, 
							'square' 	=> $objectFlat->square, 
							'floor' 	=> $objectFlat->floor, 
							'price' 	=> $objectFlat->price,
				);
				switch ($objectFlat->action)
				{
					case 'edit':
						$this->editAnnounce($objectFlat->announceId, $importData);
						$this->statistics['edited']++;
					break;
					
					case 'add':
						$this->addAnnounce($importData);
						$this->statistics['added']++;
					break;							
				}
			}
		}
	}
	
	/**
	 * Выводит статистику по работе импорта
	 */
	public function getStatistics()
	{
		echo 'Добавлено: '.$this->statistics['added'].'<br>';
		echo 'Отредактировано: '.$this->statistics['edited'].'<br>';
		echo 'Удалено: '.$this->statistics['deleted'].'<br />';
	}
	
	/**
	 * Формирует запросы к API на удаление неактуальных объявлений
	 * @param array $announce2Delete
	 */
	public function deleteOldAnnounceList($announce2Delete = array())
	{
		foreach ($announce2Delete as $announce)
		{
			$this->deleteAnnounce($announce['id']);
			$this->statistics['deleted']++;
		}
	}
	/**
	 * Добавляет объявление
	 * @param xmlrpcStruct $announceList
	 * @return array
	 */
	public function addAnnounce($data = array())
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;
		
		$this->createXMLRpc();

		$message = new xmlrpcmsg("realty.addAnnounce", array(
						new xmlrpcval($this->_apiKey, $xmlrpcString),
						new xmlrpcval($this->prepareSendData($data), $xmlrpcStruct))
			
		);

		return $this->sendXMLRpc($message);
	}
	
	/**
	 * Редактирует объявление
	 * @param integer $announceId
	 * @param xmlrpcStruct $data
	 * @return array
	 */
	public function editAnnounce($announceId = 0, $data = array())
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;
		
		$this->createXMLRpc();

		$message = new xmlrpcmsg("realty.editAnnounce", array(
						new xmlrpcval($this->_apiKey, $xmlrpcString),
						new xmlrpcval($announceId, $xmlrpcInt),
						new xmlrpcval($this->prepareSendData($data), $xmlrpcStruct))
			
		);
		return $this->sendXMLRpc($message);
	}

	/**
	 * Удаляет объявление
	 * @param integer $announceId
	 * @return array
	 */
	public function deleteAnnounce($announceId = 0)
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;
		
		$this->createXMLRpc();

		$message = new xmlrpcmsg("realty.deleteAnnounce", array(
						new xmlrpcval($this->_apiKey, $xmlrpcString),
						new xmlrpcval($announceId, $xmlrpcInt))
			
		);

		return $this->sendXMLRpc($message);
	}

	/**
	 * Подготовка данных для отправки
	 * @param array $data
	 * @return array
	 */
	private function prepareSendData($data)
	{
		global $xmlrpcString, $xmlrpcBoolean, $xmlrpcerruser, $xmlrpcInt, $xmlrpcStruct, $xmlrpcArray, $xmlrpcDouble;
		
		return array( 
 			
    			'flatId' => new xmlrpcval(intval($data['flatId']), $xmlrpcInt),
    			'agencyId' => new xmlrpcval($this->agencyId, $xmlrpcInt),
    			'square' => new xmlrpcval($data['square'], $xmlrpcString),
    			'floor' => new xmlrpcval(intval($data['floor']), $xmlrpcInt),
    			'price' => new xmlrpcval(doubleval(str_replace(",",".",$data['price']))*1000000, $xmlrpcDouble),
 		
		);		
	}

}