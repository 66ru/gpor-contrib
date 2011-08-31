<?php
/**
 * Enter description here ...
 * @author astronom
 *
 */
class Parser extends CsvParser {


	/**
	 * Содержимое csv файла
	 * @var object
	 */
	public $data;

	/**
	 * Список объяв на удаление
	 * @var array
	 */
	public $announce2Delete = array();

	/**
	 * Массив с уникальными id от аск
	 * @var array
	 */
	public $uniqueObjectIds = array();

	/**
	 * Массив соответствий id имортируемого и нашей базы
	 * @var array
	 */
	public $objectComplianceList;

	/**
	 * Список объявлений которые не удалось привязать и импортировать
	 * @var array
	 */
	public $announcesNotUsed = array();

	/**
	 * Путь до файла где хранятся соотвектсвия в виде json серриализации
	 * @var string
	 */
	static protected $compliancesFilePath;

	/**
	 * Путь до файла для парсинга
	 * @var unknown_type
	 */
	protected $fileToParsePath;

	public function __construct()
	{
		$params = require ('./config.php');

		$this->fileToParsePath = isset($params['fileToParsePath']) ? $params['fileToParsePath'] : false;
		self::$compliancesFilePath = isset($params['compliancesFilePath']) ? $params['compliancesFilePath'] : false;

		$this->developerId = isset($params['developerId']) ? $params['developerId'] : false;
		$this->agencyId = isset($params['agencyId']) ? $params['agencyId'] : false;

		if (!$this->fileToParsePath)
		die('Error. "fileToParsePath" not found in config.php');
		if (!self::$compliancesFilePath)
		die('Error. "compliancesFilePath" not found in config.php');
	}

	/**
	 * Настройка параметров парсера
	 * @param array $columns Массив колонок файла
	 * @param boolean $ignoreFirstLine Флаг, игнорировать или нет первую строку
	 * @param string $separator Разделитель в csv
	 */
	public function configure($columns = array(), $ignoreFirstLine = true, $separator = ';')
	{

		$this->_columns = $columns;
		$this->_ignoreFirstLine = $ignoreFirstLine;
		$this->_separator = $separator;

	}

	public function parse()
	{
		parent::parse($this->fileToParsePath);
	}

	public function parseLine(stdClass $line)
	{
		$this->data[] = $line;
	}

	/**
	 * Отдает массив с уникальными id новостроек
	 * @return array
	 */
	public function getUniqueObjectIds()
	{
		if(empty($this->uniqueObjectIds))
		{
			foreach ($this->data as $object)
			{
				array_push($this->uniqueObjectIds, $object->id);
			}

			$this->uniqueObjectIds = array_unique($this->uniqueObjectIds);
		}
		return $this->uniqueObjectIds;
	}

	/**
	 * Отдает массив соответсвий нашей базы и базы для импорта по id
	 */
	public function getObjectCompliancesList()
	{
		if(empty($this->objectComplianceList)) {
			if(file_exists(self::$compliancesFilePath) && is_readable(self::$compliancesFilePath))
			{
				return json_decode(file_get_contents(self::$compliancesFilePath));
			}
			else
			{
				return array();
			}
		}
		else return $this->objectComplianceList;
	}

	/**
	 * Сохранит новое соответствие id если нет дублей
	 * @param array $data
	 */
	public function saveObjectCompliances($data)
	{
		// Проверка на дублирование связей
		foreach ($data as $item) {
			if($this->specified_array_unique($data, $item))
			die('Найдены дублирование соответствия, вернитесь к пункту 2');
			// @todo обработка ошибки
		}
		$this->writeCompliancesFile(json_encode($data));
	}

	/**
	 * Проверяет есть ли повторяющиеся значения в массиве
	 * @param array $array
	 * @param $value
	 * @return boolean
	 */
	private function specified_array_unique($array, $value)
	{
		$count = 0;

		foreach($array as $array_key => $array_value)
		{
			if (($array_value == $value) && ($array_value > 0) )
			{
				$count++;
			}
		}

		if($count > 1)
		{
			return true;
		}
		else
		{
			return false;
		}

	}

	/**
	 * Формирует объект $data для импорта
	 * Собирает массив устаревших объявлений
	 */
	public function prepareAnnonceList()
	{
		// Привязываем объявления к планировкам
		$data = $this->announce2Flat();

		$export = new Export();
		foreach ($data as $objectId => $objectGroup)
		{
			// Получим список объяв агентсва по данной новостройке
			$announceList = $export->clearAnnounceListOfObject($objectId);

			foreach ($objectGroup as $objectFlat)
			{
				foreach ($announceList as $i => $announce)
				{
					// Объявление найдено
					if( (int) $announce['flatId'] == (int) $objectFlat->flatId)
					{
						$objectFlat->action = 'edit';
						$objectFlat->announceId = $announce['id'];

						// Найденное объявление удаляем из списка
						unset($announceList[$i]);

						break;
					}


				}
				// Если объявление не найдено
				if(!isset($objectFlat->action))
				$objectFlat->action = 'add';
			}

			// Собираем объявы которые надо удалить
			$this->announce2Delete = array_merge($this->announce2Delete, $announceList);
		}

		// Перезапишем объект
		$this->data = $data;
			
	}

	/**
	 * Обращает к Import Api для внесения изменений в базу
	 */
	public function importAnnounceList()
	{
		$import = new Import();
		$import->importAnnounceList($this->data);
		$import->deleteOldAnnounceList($this->announce2Delete);
		$import->getStatistics();
	}

	public function prepareAnnonceListAndImport()
	{
		$this->prepareAnnonceList();
		$this->importAnnounceList();
	}

	/**
	 * Преобразует список объявлений для дальнейшего иморта
	 * 1. Группировка по новостройкам
	 * 2. Оставляет только объявления id новостроек которых определены в соответсвиях
	 * @return multitype:
	 */
	public function groupAnnonceListByObject()
	{
		$groupedData = array();

		$objectCompliancesList = $this->getObjectCompliancesList();

		// группирует по новостройкам
		foreach ($this->getUniqueObjectIds() as $objectId)
		{
			// Объявления id новостройки которого не определен выбросить
			if(isset($objectCompliancesList->$objectId) &&  $objectCompliancesList->$objectId > 0)
			{
				$groupedData[$objectCompliancesList->$objectId] = array();
				foreach ($this->data as $data)
				{
					if($data->id == $objectId)
					array_push($groupedData[$objectCompliancesList->$objectId], $data);
				}
			}
			// @TODO возможно тут собирать информацию о не обработанных новостройках
		}

		return $groupedData;
	}

	/**
	 * Привязка объявлений к планировкам
	 */
	public function announce2Flat()
	{
		$groupedData = $this->groupAnnonceListByObject();

		$export = new Export();

		foreach ($groupedData as $complexObjectId => $objectGroup)
		{
			// Выставляем id по дефолту 0
			$objectId = 0; // id Новостройки
			$stageId = 0;  // id Очереди новостройки
			// Если id новостройки связано с планировкой (напр. "1.1")
			// Закоментирован Notice чтобы зря не ругалась
			@list($objectId, $stageId) = explode('.', $complexObjectId);

			// получаем список планировок новостройки
			$flatList = $export->getFlatListOfObject((int) $objectId);

			// Перезаписываем данные с найденной планировкой
			$groupedData[$objectId] = $this->findFlat($objectGroup, $flatList, (int) $stageId);

			// Удаляем данные со сложным id (новостройка с планировкой)
			if($stageId > 0)
			{
				unset($groupedData[$complexObjectId]);
			}
		}

		return $groupedData;
	}

	/**
	 * Привязывает объявление к планировке
	 * @param array $objectGroup
	 * @param array $flatList
	 * @param mixed int $stageId
	 */
	private function findFlat($objectGroup, $flatList, $stageId = 0)
	{
		foreach ($objectGroup as $i => $objectflat) {
			$maybyFlat = array();
			$_dd = 9999;
			$maybyFlatId = 0;
			foreach ($flatList as $flat)
			{
				// Если есть четкая привязка к очереди, то рассматриваем только планировки указанной очереди
				if(!((int) $flat['stageId'] == $stageId && $stageId > 0))
				{
					continue;
				}
				// Проверяем чтобы привязка осуществлялась только к планировке, у которой этаж и количество комнат совпададет
				if($flat['rooms'] == $objectflat->rooms && in_array($objectflat->floor, $flat['floors'])) {

					// Найдено точное соответствие
					if($flat['square'] == $objectflat->square)
					{
						$objectflat->flatId = intval($flat['id']);
						continue;
					}
					// Вычисляем разницу площадей
					else
					{
						$dd = abs($flat['square'] - $objectflat->square);
						if($dd < $_dd)
						{
							$_dd = $dd;
							// Кладем в массив
							$maybyFlatId = $flat['id'];
						}
					}
				}
			}

			// если точного совпадения нет
			if(!isset($objectflat->flatId) && $maybyFlatId > 0)
			{
				$objectflat->flatId = $maybyFlatId;
			}
			else {
				// Записываем объявы, которые не удалось привязать
				array_push($this->announcesNotUsed, $objectGroup[$i]);
				// Эту объяву мы дальше не будем обрабатывать
				unset($objectGroup[$i]);
			}
		}
		return $objectGroup;
	}

	/**
	 * Сохранит загруженный файл и установит права
	 * @param string $tmpFile
	 */
	public function saveFile($tmpFile)
	{
		copy($tmpFile, $this->fileToParsePath);
		unlink($tmpFile);
		chmod($this->fileToParsePath, 0644);
	}

	/**
	 * Записывает данные в файл соответсвтий
	 * Создаст файл если он не существует
	 * @param array $data
	 * @throws ErrorException
	 */
	private function writeCompliancesFile($data)
	{
		$file = self::$compliancesFilePath;
			
		if (!$handle = fopen($file, 'w')) {
			throw new ErrorException("Cannot open file ($file)");
		}

		if (fwrite($handle, $data) === FALSE) {
			throw new ErrorException("Cannot write to file ($filename)");
		}

		fclose($handle);

	}
}

class ParserException extends Exception
{

}