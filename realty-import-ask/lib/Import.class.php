<?php
class Import extends Api {

	/**
	 * Статистика по работе импорта
	 * @var array
	 */
	public $statistics = array();

	protected  $_errorLogFile;
	
	public function __construct()
	{
		parent::__construct();
		$params = require ('config.php');

		$this->_errorLogFile = isset($params['errorFile']) ? $params['errorFile'] : false;
		
		if (!$this->_errorLogFile)
			die('Error. "errorFile" not found in config.php');
		
		$file = get_include_path().$this->_errorLogFile;
		if (!$handle = fopen($file, 'a+')) {
			throw new ErrorException("Cannot open file ($file)");
		}
		
		if (fwrite($handle, '') === FALSE) {
			throw new ErrorException("Cannot write to file (".$file.")");
		}

		@chmod($file, 0666);
		fclose($handle);
		$this->_errorLogFile = $file;
	}

	/**
	 * Формирует запросы к API на изменение или добавление
	 * @param unknown_type $data
	 */
	public function importAnnounce($objectFlat)
	{
		$report = array();
		
		$importData = array(
			'flatId' 	 => $objectFlat->flatId, 
			'square' 	 => $objectFlat->square, 
			'floor' 	 => $objectFlat->floor, 
			'price' 	 => $objectFlat->price,
		);
		switch ($objectFlat->action)
		{
			case 'edit':
				$report = $this->editAnnounce($objectFlat->announceId, $importData);
				break;
					
			case 'add':
				$report = $this->addAnnounce($importData);
				break;
				
			case 'delete':
				$report = $this->deleteAnnounce($objectFlat->announceId);
				break;
		}
		
		return $report;
	}

	public function importAnnounceListByPart($data, $maxOperations = 10)
	{
		$operations = 1;
		foreach ($data as $objectId => $objectGroup)
		{
			foreach ($objectGroup as $flatId => $objectFlat)
			{
				$report = $this->importAnnounce($objectFlat);
				$status = 'Error';
				if(empty($report['errcode'])) {
					$status = 'Success';
					unset($data[$objectId][$flatId]);
				}
				else 
				{
					$this->logError($objectId.' | '.$flatId.' | '.implode(" ",$report));
					unset($data[$objectId][$flatId]);
				}
				$this->statistics[] = time().'|'.$objectId.'|'.$status.'|'.implode(" ",$report);
				$operations++;
				if($operations > $maxOperations)
				{
					$total = count($data, COUNT_RECURSIVE) - count($data);
					$this->statistics[] = time().'|'.'Total|'.$total;
						
					return $data;
				}

			}
		}
		$total = count($data, COUNT_RECURSIVE) - count($data);
		$this->statistics[] = time().'|'.'Total|'.$total;
		
		return $data;
	}
	
	public function logError($error)
	{
		$error = date('d-m-Y G:i:s').': '.$error."\n";
		if (!$handle = fopen($this->_errorLogFile, 'a')) {
			throw new ErrorException("Cannot open file ($this->_errorLogFile)");
		}
		
		if (fwrite($handle, $error) === FALSE) {
			throw new ErrorException("Cannot write to file (".$this->_errorLogFile.")");
		}
		fclose($handle);
	}

	/**
	 * Выводит статистику по работе импорта
	 */
	public function getStatistics()
	{
		return $this->statistics;
		//echo 'Добавлено: '.$this->statistics['added'].'<br>';
		//echo 'Отредактировано: '.$this->statistics['edited'].'<br>';
		//echo 'Удалено: '.$this->statistics['deleted'].'<br />';
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