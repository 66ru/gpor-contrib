<?

	include_once( ROOT.'/lib/xmlrpc/sphinxapi.php' );
	lib('strings');
	lib('search');
    lib('vacancies');
    lib('email');
    lib('templates');
    lib('dates');
    lib('companies_employees');

	include_once (ROOT.'/lib/xmlrpc-3.0.0.beta/xmlrpc.inc');
	include_once (ROOT.'/lib/xmlrpc-3.0.0.beta/xmlrpcs.inc');
	include_once (ROOT.'/lib/xmlrpc-3.0.0.beta/xmlrpc_wrappers.inc');
	global $GLOBALS;
	$GLOBALS['xmlrpc_internalencoding']='UTF-8';

class gporImport
{
		private $_lastError = false;
		private $_lastId = 0;
		private $_log = array();
		
		public $apiUrl;
		public $apiKey;
		public $limit = 100;


	public function setLastError($val)
	{
		$this->_lastError = $val;
		$this->addLog($val);
	}
		
	public function getLastError()
	{
		return $this->_lastError;
	}
		
	public function getLastId()
	{
		return $this->_lastId;
	}
		
	public function setLastId($val)
	{
		$this->_lastId = (int)$val;
		return $this->_lastId;
	}

	protected function addLog($val)
	{
		$this->_log[time()] = $val;
		return true;
	}
		
	protected function clearLog()
	{
		$this->_log = array();
		return true;
	}
		
	public function getLog()
	{
		return $this->_log;
	}
		
	public function importCompanies()
	{
		if (!$this->apiUrl || !$this->apiKey)
		{
            $this->setLastError('Не задан apiUrl или apiKey');
            return false;
		}

		$lastId = $this->getLastId();
		
		$limit = $this->limit;
		
		$where = array();
		if ($lastId)
			$where[] = '`c`.`id` > '.$lastId;
		$where[] = '`c`.`checked` > 0';
		$companies = db_assoc('	SELECT 
								*
							FROM
								`companies` AS `c`
							WHERE
								('.implode(') AND (', $where).')
							ORDER BY id
							LIMIT 0, '.$limit);
		
		if ($companies)
		{
			$xmlRpc = new XmlRpc($this->apiUrl, $this->apiKey, 'job.postCompany');
			$result = array();
			foreach ($companies as $company)
			{
				$company['employees'] = db_assoc('
					SELECT * FROM `'.TABLE_COMPANIES_EMPLOYEES.'` WHERE `company_id` = '.(int)$company['id']
				);
				$this->setLastId($company['id']);
				$params = $company;
				$params['type'] = 10;
				$params['branch'] = self::branchesToGpor($company['branch']);
				$params['__region'] = 2;
				$params['checked'] = $company['checked'] ? 10 : -10;
				$params['employees'] = array();
				
				if ($company['logo'])
				{
					if (file_exists(ROOT.$company['logo']))
						$params['logoURL'] = 'http://www.rabota66.ru'.$company['logo'];
				}

				if ($company['employees'])
				{
					$params['employees'] = array();
					foreach ($company['employees'] as $e)
					{
						$e['email'] = '';
						$params['employees'][] = $e;
					}
				}
				
				// TO_DO: получить реквизиты доступа компании
				/*
				if ($company->user)
				{
					$params['client'] = $company->user->attributes;
					$params['client']['login'] = $company->user->username;
				}
				*/
					
				$res = $xmlRpc->send(array($params));
				if ($res)
					$this->addLog($company->id.': success');
				else
				{
					$this->addLog($company->id.': error. '.$xmlRpc->getLastError());
				}
			}
			return true;
		}
		else
		{
			$this->addLog('Компании импортированы');
			return false;
		}
		
	}
	
	
	public function importVacancies()
	{
		if (!$this->apiUrl || !$this->apiKey)
		{
            $this->setLastError('Не задан apiUrl или apiKey');
            return false;
		}

		$lastId = $this->getLastId();
		
		$limit = $this->limit;
		
		$items = $model->findAll();
		
		if ($items)
		{
			$xmlRpc = new XmlRpc($this->apiUrl, $this->apiKey, 'job.postVacancy');
			$result = array();
			foreach ($items as $item)
			{
				$this->setLastId($item['id']);
				$params = $item;
				
				$geoplaces = self::geoplacesToGpor($item->_geoplaces);
				if ($geoplaces)
					$params['geoplaces'] = $geoplaces;
				else
					continue;
				$params['branches'] = self::branchesToGpor($item->_branches);
				$params['vbranches'] = self::vbranchesToGpor($item->_vbranches);
				$params['vac_type'] = $params['vac_type'] ? $params['vac_type'] : 1;
				$params['workplan'] = $params['workplan'] ? $params['workplan'] : 1;
				
				if (!$params['vbranches'])
					continue;
				
				$res = $xmlRpc->send(array($params));
				
				if ($res)
					$this->addLog($item->id.': success');
				else
				{
					$this->addLog($item->id.': error. '.$xmlRpc->getLastError());
				}
			}
			
			return true;
		}
		else
		{
			$this->addLog('Вакансии импортированы');
			return false;
		}
	}
	
	
	public function exportResponses ()
	{
		if (!$this->apiUrl || !$this->apiKey)
		{
            $this->setLastError('Не задан apiUrl или apiKey');
            return false;
		}

		$lastId = $this->getLastId();
		
		$limit = $this->limit;
		
		$items = $model->findAll();
		
		$xmlRpc = new XmlRpc($this->apiUrl, $this->apiKey, 'job.importResponses');
		$params = array (
			'fromDate' => '',
		);
		
		if ($items = $xmlRpc->send(array($params)) )
		{
			foreach ($items as $item)
			{
				
			}
		}
	}
	
	
	public function exportResumes ()
	{
		if (!$this->apiUrl || !$this->apiKey)
		{
            $this->setLastError('Не задан apiUrl или apiKey');
            return false;
		}

		$lastId = $this->getLastId();
		
		$limit = $this->limit;
		
		$items = $model->findAll();
		
		$xmlRpc = new XmlRpc($this->apiUrl, $this->apiKey, 'job.postResumes');
		$params = array (
			'fromDate' => '',
		);
		
		if ($items = $xmlRpc->send(array($params)) )
		{
			foreach ($items as $item)
			{
				
			}
		}
	}
	
	public static function branchesToGpor ($ids)
	{
		$items = array(
        1 => 1, //    'Финансы, экономика и страхование',
        2 => 7, //   'IT, телеком, связь',
        3 => 4, //   'Строительство, недвижимость',
        4 => 20, //   'Здравоохранение',
        5 => 19, //   'Рестораны, досуг, гостиницы',
        6 => 5,  //  'Автобизнес, транспорт',
        7 => 23, //   'Культура, искусство, творчество',
        8 => 27, //   'Сельское хозяйство',
        9 => 23, //   'Курсы, наука, образование',
        10 => 8, //  'Реклама, кадры, юристы, консалтинг, аудит',
        11 => 8, //  'СМИ, издательство',
        12 => 13,//   'Оптовая торговля',
        13 => 12, //  'Розничная торговля',
        14 => 24, //  'Спорт, туризм',
        15 => 6,  // 'Логистика, склад, ВЭД',
        16 => 3, //  'Производство, промышленность',
        17 => 26, //  'Государственная служба',
        18 => 11, //  'Частный работодатель',
        19 => 11, //  'Обслуживание бизнеса',
        20 => 11, //  'Индустрия красоты',
        21 => 11, //  'Сфера услуг',			        
		);

		$idsAr = is_array($ids) ? $ids : array ($ids);
		
		$res = array();
		foreach ($idsAr as $id)
		{
			if (isset($items[$id]))
				$res[] = $items[$id];
		}
		
		if (!is_array($ids))
		{
			return $res ? $res[0] : false;
		}
		return $res;
	}
	
	
	public static function vbranchesToGpor ($ids)
	{
		$items = array(
	        1 => 1, // 'Бухгалтерия',
	        25 => 25, // 'Финансы, экономика и страхование',
	        18 => 18, //'Строительство, недвижимость',
	        17 => 17, //'Промышленность, производство, сервис',
	        6 => 6, //'IT, телеком, связь',
	        13 => 13, //'Секретариат, АХО',
	        5 => 5, //'HR, тренинги',
	        4 => 4, //'Маркетинг, реклама, PR',
	        37 => 37, //'СМИ, Издательство, полиграфия',
	        24 => 24, //'Региональные, торговые представители',
	//        2 => 'Отдел продаж',
	//        15 => 'Розничная торговля',
	        45 => 15, //'Розничная торговля ТНП',
	        46 => 15, //'Розничная торговля продукты питания',	        
	        33 => 33, //'Продажи (Промышленность, оборудование)',
	        27 => 27, //'Продажи (Строительство, недвижимость)',
	        29 => 29, //'Продажи (ТНП, продукты)',
	        31 => 31, //'Продажи (Услуги, реклама)',
	        34 => 34, //'Продажи (Финансы, страхование)',
	        32 => 32, //'Продажи (Автомобили, запчасти)',
	        30 => 30, //'Продажи (IT, компьютеры)',
	        28 => 28, //'Продажи (Одежда, мебель)',
	        7 => 7, //'Юриспруденция',
	        36 => 36, //'Государственная служба',
	        16 => 16, //'Рестораторы, повара, официанты',
	        38 => 38, //'Туризм, гостиницы',
	        3 => 3, //'Поставки, ВЭД',
	        10 => 10, //'Логистика, транспорт, склад',
	        39 => 39, //'Сельское хозяйство',
	        22 => 22, //'Медицина, фармацевтика',
	        40 => 40, //'Спорт, фитнесс, салоны красоты',
	        21 => 21, //'Дизайнеры, творческие профессии',
	        41 => 41, //'Культура, искусство, развлечения',
	        23 => 23, //'Наука, образование, консалтинг',
	        14 => 14, //'Служба безопасности, охрана',
	        42 => 42, //'Домашний персонал, обслуживание',
	        26 => 26, //'Разнорабочие',
	        43 => 43, //'Работа для молодежи, студентов',
	        44 => 44, //'Сезонная, временная работа',
	        47 => 45, //'Работа для пенсионеров',			        
		);

		$idsAr = is_array($ids) ? $ids : array ($ids);
		
		$res = array();
		foreach ($idsAr as $id)
		{
			if (isset($items[$id]))
				$res[] = $items[$id];
		}
		
		if (!is_array($ids))
		{
			return $res ? $res[0] : false;
		}
		return $res;
	}
	
	public static function geoplacesToGpor ($geoplaceId)
	{
		$cities = array (
			1799 => 3,
			1800 => 7,
			1801 => 8,
			1802 => 9,
			1803 => 10,
			1804 => 11,
			1805 => 12,
			1806 => 13,
			1807 => 14,
			1808 => 15,
			1809 => 2,
			1810 => 16,
			1811 => 17,
			1812 => 18,
			1813 => 19,
			1814 => 20,
			1815 => 21,
			1816 => 22,
			1817 => 1,
			1818 => 23,
			1819 => 24,
			1820 => 4,
			1821 => 25,
			1822 => 26,
			1823 => 5,
			1824 => 27,
			);
		if (isset($cities[$geoplaceId]))
			return $cities[$geoplaceId];
		return false;
	}

}


/*
 * 
 */
class XmlRpc
{
	protected  $_apiUrl;
	protected  $_apiKey;
	protected  $_apiCommand;
	protected  $_lastError;
	protected  $_response;
	
	public $client;
	

	public function __construct($apiUrl = false, $apiKey = false, $apiCommand = false)
	{
		$this->_apiKey = $apiKey;
		$this->_apiUrl = $apiUrl;
		$this->_apiCommand = $apiCommand;
	}

	protected function createXMLRpc()
	{
		$this->client = new xmlrpc_client($this->_apiUrl);
		$this->client->request_charset_encoding = 'UTF-8';
		$this->client->return_type = 'phpvals';
		//$this->client->setDebug(2);
		//return $client;
	}
	
	public function setApiUrl ($url)
	{
		$this->_apiUrl = $url;
		return true;
	}

	public function setApiKey ($key)
	{
		$this->_apiKey = $key;
		return true;
	}
	
	public function setApiCommand ($command)
	{
		$this->_apiCommand = $command;
		return true;
	}
	
	public function getApiUrl ()
	{
		return $this->_apiUrl;
	}

	public function getApiKey ()
	{
		return $this->_apiKey;
	}
	
	public function getApiCommand ()
	{
		return $this->_apiCommand;
	}
	
	public function getResponse ()
	{
		return $this->_response;
	}
	
	public function getLastError ()
	{
		return $this->_lastError;
	}
	
	
	protected function sendXMLRpc(xmlrpcmsg $message)
	{
		$xmlrpcresp = $this->client->send($message, 0, 'http11');
		
		$this->_response = $xmlrpcresp;
		
		if(!$xmlrpcresp->faultCode()){
			if ($xmlrpcresp->errcode)
			{
				$this->_lastError = "An error occurred: "." Reason: ".htmlspecialchars(implode(',', $xmlrpcresp->errors));
				return false;
			}
			return true;
		}
		else{
			$this->_lastError = "An error occurred: "." Reason: ".htmlspecialchars($xmlrpcresp->faultString());
			return false;
		}

	}
	
	
	public function send ($params = array())
	{
		$this->_lastError = '';
		$this->_response = '';
		
		$this->createXMLRpc();
		
		$message = new xmlrpcmsg($this->_apiCommand);
		$p0 = new xmlrpcval($this->getApiKey (), 'string');
		$message->addparam($p0);
		
		if ($params)
		{
			foreach ($params as $param)
			{
				if (is_array($param))
				{
					$param = self::convertToUtf8 ($param);
					$p = php_xmlrpc_encode($param);
				}
				else
				{
					$p = new xmlrpcval(iconv("UTF-8", "UTF-8//IGNORE", $param), 'string');
				}
				$message->addparam($p);
			}
		}
		return $this->sendXMLRpc ($message);
		
	}
	
	public static function convertToUtf8 ($str)
	{
		$res = '';
		if (is_array($str))
		{
			$res = array();
			foreach ($str as $k=>$v)
			{
				$k = iconv('cp1251', 'UTF-8//IGNORE', $k);
				$v = self::convertToUtf8 ($v);
				$res[$k] = $v;
			}
		}
		else
			$res = iconv('cp1251', 'UTF-8//IGNORE', $str);
		return $res;
		
	}
}
?>