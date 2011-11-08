<?php
/**
 * Enter description here ...
 * @author astronom
 *
 */
class AuthorImport {


	/**
	 * Массив соответствий id имортируемого и нашей базы
	 * @var array
	 */
	public $authorsCompliancesList;

	/**
	 * Путь до файла где хранятся соотвектсвия в виде json серриализации
	 * @var string
	 */
	static protected $compliancesFilePath;
    static public $mysqlhost;
    static public $mysqluser;
    static public $mysqlpswd;
    static public $mysqldb;


	public function __construct()
	{
		$params = require ('config.php');

		self::$compliancesFilePath = isset($params['compliancesFilePath']) ? $params['compliancesFilePath'] : false;
        self::$mysqlhost = isset($params['mysqlhost']) ? $params['mysqlhost'] : false;
        self::$mysqluser = isset($params['mysqluser']) ? $params['mysqluser'] : false;
        self::$mysqlpswd = isset($params['mysqlpswd']) ? $params['mysqlpswd'] : false;
        self::$mysqldb = isset($params['mysqldb']) ? $params['mysqldb'] : false;

        if (!self::$compliancesFilePath)
		    die('Error. "compliancesFilePath" not found in config.php');
        if (!self::$mysqlhost)
		    die('Error. "mysqlhost" not found in config.php');
        if (!self::$mysqluser)
		    die('Error. "mysqluser" not found in config.php');
        if (!self::$mysqlpswd)
		    die('Error. "mysqlpswd" not found in config.php');
        if (!self::$mysqldb)
		    die('Error. "mysqdb" not found in config.php');

	}

	/**
	 * Отдает массив соответсвий нашей базы и базы для импорта по id
	 */
	public function getAuthorsCompliancesList()
	{
		if(empty($this->authorsCompliancesList)) {
			if(file_exists(self::$compliancesFilePath) && is_readable(self::$compliancesFilePath))
			{
				return json_decode(file_get_contents(self::$compliancesFilePath));
			}
			else
			{
				return array();
			}
		}
		else return $this->authorsCompliancesList;
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
	 * Записывает данные в файл соответсвтий
	 * Создаст файл если он не существует
	 * @param array $data
	 * @throws ErrorException
	 */
	public function writeCompliancesFile($data)
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