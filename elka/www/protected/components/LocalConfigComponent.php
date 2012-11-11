<?php

/**
 * $Id$
 *
 * @author stepanoff
 * @since  22.00.2010
 */
class localConfigExtension extends CApplicationComponent
{
	const DEFAULT_LOCALCONFIG_BASEPATH='localConfig';
	
	private $_localConfigBasePath;

	/**
	 * @var string base URL for accessing the publishing directory.
	 */
	private $_baseUrl;
	
	public function init()
	{
		parent::init();
		Yii::import('application.extensions.localconfig.*');
		$request=Yii::app()->getRequest();
		if($this->getLocalConfigBasePath()===null)
			$this->setLocalConfigBasePath(dirname($request->getScriptFile()).DS.self::DEFAULT_LOCALCONFIG_BASEPATH);
	}

	/**
	 * возвращает базовый путь локального хранилища, в котором складываются все пользовательские файлы
	 * 
	 * @return string
	 */
	public function getLocalConfigBasePath()
	{
		if (!isset($this->_localConfigBasePath )) {
			$this->_localConfigBasePath = Yii::app()->getBasePath().DS.'..'.DS.self::DEFAULT_LOCALCONFIG_BASEPATH;
            if (!is_dir($this->_localConfigBasePath)) {
                mkdir($this->_localConfigBasePath, 0777, true);
            }
            $this->_localConfigBasePath = realpath($this->_localConfigBasePath);
		}
		return $this->_localConfigBasePath; 
	}
	
	/**
	 * задает базовый путь локального хранилища, в котором складываются все пользовательские файлы
	 * 
	 * @param string $path базовый путь в ФС локального хранилища
	 */
	public function setLocalConfigBasePath($path)
	{
		$this->_localConfigBasePath = $path;
	}
	
	/**
	 * задает базовый URL для локального хранилища, в котором складываются все пользовательские файлы
	 * 
	 * @param string $path базовый путь в ФС локального хранилища
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl=rtrim($value,'/');
	}

	/*
	 * читает массив данных из локального файла
	 */
	public function getConfig ($path, $index=false)
	{
		$pokes = explode ('.', $path);
		$basePath = $this->getLocalConfigBasePath();
		$file_found = false;
		
		$path = $basePath;
		$n = count($pokes);
		
		for ($i=0; $i<$n; $i++)
		{
			$path = $path.DS.$pokes[$i];
			$file = $path.'.php';
			if (is_dir($path) && $i < ($n-1))
				continue;
			elseif (file_exists($file))
			{
				$file_found = true;
				break;
			}
		}
		
		$res = null;
		
		if ($file_found)
		{
			$res = include ($file);
			$i++;
			for ($x=$i; $x<$n; $x++)
			{
				if (!isset($res[$pokes[$x]]))
				{
					$res = null;
					break;
				}
				else
					$res = $res[$pokes[$x]];
			}
		}
		
		return $res;
	}
}

?>