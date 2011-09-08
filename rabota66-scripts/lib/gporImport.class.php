<?

	include_once( ROOT.'/lib/xmlrpc/sphinxapi.php' );
	lib('strings');
	lib('search');
    lib('vacancies');
    lib('email');
    lib('templates');
    lib('dates');
    lib('companies');
    lib('companies_employees');
    include_once( ROOT.'/lib/email.lib.php' );
    include_once( ROOT.'/lib/notes.lib.php' );
    include_once( ROOT.'/lib/resume.lib.php' );
    include_once( ROOT.'/lib/managers.lib.php' );
    include_once( ROOT.'/lib/companies.lib.php' );
    include_once( ROOT.'/lib/dates.lib.php' );
    

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
		$this->_log[] = date('d-m-Y G:i:s', time()).': '.$val;
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
            $this->setLastError('�� ����� apiUrl ��� apiKey');
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
				
				//if ($company->user)
				{
					$params['client'] = array(
						'id' => 1,
						'login' => 'rabotaTest',
						'password' => 'rabotaTest',
						'username' => 'rabotaTest',
						'email' => 'rabotaTest@rabota66.ru',
					);
				}
					
				$res = $xmlRpc->send(array($params));
				if ($res)
					$this->addLog($company['id'].': success');
				else
				{
					$this->addLog($company['id'].': error. '.$xmlRpc->getLastError());
				}
			}
			return true;
		}
		else
		{
			$this->addLog('�������� �������������');
			return false;
		}
		
	}
	
	
	public function importVacancies()
	{
		if (!$this->apiUrl || !$this->apiKey)
		{
            $this->setLastError('�� ����� apiUrl ��� apiKey');
            return false;
		}

		$lastId = $this->getLastId();
		
		$limit = $this->limit;
		
		$items = db_assoc('SELECT `id` FROM `'.TABLE_COMPANIES_VACANCIES.'`
            WHERE 
                '.vacancy_sql( VACANCY_DEFAULT ).' AND `_actual` > 0 '.($lastId ? ' AND id > '.$lastId : '').'
            ORDER BY id
            LIMIT 0, '.$limit);
		
		
		if ($items)
		{
			$xmlRpc = new XmlRpc($this->apiUrl, $this->apiKey, 'job.postVacancy');
			$result = array();
			foreach ($items as $row)
			{
				$item = vacancy_get($row['id']);
				$this->setLastId($item['id']);
				$params = $item;
				
				$geoplaces = self::geoplacesToGpor($item['cities']);
				if ($geoplaces)
					$params['geoplacesNames'] = $geoplaces;
				else
					continue;
				$params['branches'] = self::branchesToGpor($item['branch']);
				$params['vbranches'] = self::vbranchesToGpor($item['vac_branch']);
				$params['vac_type'] = self::vactypeToGpor($item['vac_type']);
				$params['workplan'] =  self::workplanToGpor($item['workplan']);
				$params['pay_sum_to'] =  $item['pay_sum'];
				
				if (!$params['vbranches'])
					continue;
				
				$res = $xmlRpc->send(array($params));
				
				if ($res)
					$this->addLog($item['id'].': success');
				else
				{
					$this->addLog($item['id'].': error. '.$xmlRpc->getLastError());
				}
			}
			
			return true;
		}
		else
		{
			$this->addLog('�������� �������������');
			return false;
		}
	}
	
	
	public function exportResponses ()
	{
		if (!$this->apiUrl || !$this->apiKey)
		{
            $this->setLastError('�� ����� apiUrl ��� apiKey');
            return false;
		}
		
		$lastResponse = db_row('SELECT * FROM `'.TABLE_RESPONSES.'`
            WHERE 
                `referal_id` = 1
            ORDER BY id DESC LIMIT 1' );
		
		if ($lastResponse)
		{
			$fromTime = strtotime($lastResponse['datetime']);
			//$fromTime = time() - 60*60*24*7;
			$xmlRpc = new XmlRpc($this->apiUrl, $this->apiKey, 'job.listResponses');
			$params = array (
				$fromTime,
			);
			
			if ($xmlRpc->send($params) )
			{
				$resp = $xmlRpc->getResponseVal();
				if ($resp['items'])
				{
					foreach ($resp['items'] as $item)
					{
						$res = $this->saveResponse($item);
						if ($res)
							$this->addLog($item['id'].': success');
						else
						{
							$this->addLog($item['id'].': error. '.$xmlRpc->getLastError());
						}
					}
				}
			}
		}
		
	}
	

	
	public static function branchesToGpor ($ids)
	{
		$items = array(
        1 => 1, //    '�������, ��������� � �����������',
        2 => 7, //   'IT, �������, �����',
        3 => 4, //   '�������������, ������������',
        4 => 20, //   '���������������',
        5 => 19, //   '���������, �����, ���������',
        6 => 5,  //  '����������, ���������',
        7 => 23, //   '��������, ���������, ����������',
        8 => 27, //   '�������� ���������',
        9 => 23, //   '�����, �����, �����������',
        10 => 8, //  '�������, �����, ������, ����������, �����',
        11 => 8, //  '���, ������������',
        12 => 13,//   '������� ��������',
        13 => 12, //  '��������� ��������',
        14 => 24, //  '�����, ������',
        15 => 6,  // '���������, �����, ���',
        16 => 3, //  '������������, ��������������',
        17 => 26, //  '��������������� ������',
        18 => 11, //  '������� ������������',
        19 => 11, //  '������������ �������',
        20 => 11, //  '��������� �������',
        21 => 11, //  '����� �����',			        
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
	        1 => 1, // '�����������',
	        25 => 25, // '�������, ��������� � �����������',
	        18 => 18, //'�������������, ������������',
	        17 => 17, //'��������������, ������������, ������',
	        6 => 6, //'IT, �������, �����',
	        13 => 13, //'�����������, ���',
	        5 => 5, //'HR, ��������',
	        4 => 4, //'���������, �������, PR',
	        37 => 37, //'���, ������������, ����������',
	        24 => 24, //'������������, �������� �������������',
	//        2 => '����� ������',
	//        15 => '��������� ��������',
	        45 => 15, //'��������� �������� ���',
	        46 => 15, //'��������� �������� �������� �������',	        
	        33 => 33, //'������� (��������������, ������������)',
	        27 => 27, //'������� (�������������, ������������)',
	        29 => 29, //'������� (���, ��������)',
	        31 => 31, //'������� (������, �������)',
	        34 => 34, //'������� (�������, �����������)',
	        32 => 32, //'������� (����������, ��������)',
	        30 => 30, //'������� (IT, ����������)',
	        28 => 28, //'������� (������, ������)',
	        7 => 7, //'�������������',
	        36 => 36, //'��������������� ������',
	        16 => 16, //'�����������, ������, ���������',
	        38 => 38, //'������, ���������',
	        3 => 3, //'��������, ���',
	        10 => 10, //'���������, ���������, �����',
	        39 => 39, //'�������� ���������',
	        22 => 22, //'��������, ������������',
	        40 => 40, //'�����, �������, ������ �������',
	        21 => 21, //'���������, ���������� ���������',
	        41 => 41, //'��������, ���������, �����������',
	        23 => 23, //'�����, �����������, ����������',
	        14 => 14, //'������ ������������, ������',
	        42 => 42, //'�������� ��������, ������������',
	        26 => 26, //'������������',
	        43 => 43, //'������ ��� ��������, ���������',
	        44 => 44, //'��������, ��������� ������',
	        47 => 45, //'������ ��� �����������',			        
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
	
	public static function geoplacesToGpor ($geoplaceIds)
	{
		global $GEO_LIST;
		$result = array();
		if ($geoplaceIds)
		{
			foreach ($geoplaceIds as $geoplaceId)
			{
				if (isset($GEO_LIST[$geoplaceId]))
					$result[] = $GEO_LIST[$geoplaceId]['name'];
			}
		}
		
		return $result;
	}
	
	public static function educationToGpor ($education)
	{
	    $educatuionsList = array(
	        1 => 1, //'������',
	        2 => 2, //'�������',
	        3 => 3, //'�������� ������',
	        4 => 4, //'������� �����������'
	        );
	    if (isset($educatuionsList[$education]))
	    	return $educatuionsList[$education];
	    return 0;
	}
	
	
	public static function vactypeToGpor ($val)
	{
	    $items = array(
	        1 => 1,
	        2 => 2,
	        3 => 3,
	        );
	    if (isset($items[$val]))
	    	return $items[$val];
	    return 0;
	}

	
	public static function workplanToGpor ($val)
	{
	    $items = array(
			        1 => 1, //	 '������ ������� ����',
			        4 => 4, //    '��������',
			        2 => 2, //    '��������� ������',
			        3 => 3, //    '��������',
			        5 => 5, //    '��������',
			        6 => 1, //    '�����',
			        
	    );
	    if (isset($items[$val]))
	    	return $items[$val];
	    return 0;
	}
	
	
	public function saveResponse ($data)
	{
		if (!$vacancy = vacancy_get($data['for_id']))
		{
			$this->setLastError('�������� �� �������');
			return false;
		}	
		/*
		if( !$_FILES['file']['name'] )
				$err['ERROR_FILE_SIZE'] = true;
    	*/
        if (!$company = company_get( $vacancy['company_id'] ))
        {
			$this->setLastError('�������� �� �������');
			return false;
        }
		
		$data['from_type'] = $data['from_type'] == 2 ? 'user' : 'guest';
		$data['referal_id'] = 1;
    	
		$_file = array();
		if ($data['fileURL'])
		{
	       	$_file['content'] = @file_get_contents($data['fileURL']);
	       	if ($_file['content'])
	       	{
		       	$_file['md5'] = md5( $_file['content'] );
				$_file['pathinfo'] = pathinfo( $data['fileURL'] );
		       	$_file['filename'] = '/upload_'.R.'/sendResume/'.date('Y-m-d').'/'.$_file['md5'].'.'.$_file['pathinfo']['extension'];
				
				$k = 'response'.$data['id'];
				$tempName = tempnam(sys_get_temp_dir(), 'n');
				file_put_contents($tempName, $_file['content']);
				$_FILES[$k]['name'] = $data['name'].'.'.$_file['pathinfo']['extension'];
				//$_FILES['type'][$k] = CFileHelper::getMimeTypeByExtension($oldFileName);
				$_FILES[$k]['tmp_name'] = $tempName;
				$_FILES[$k]['error'] = 0;
				$_FILES[$k]['size'] = filesize($tempName);
				
				if (!file_upload($k, $_file['filename'], false, true))
				{
					$this->setLastError('error file upload');
					return false;
				}
	       	}
	       	else
	       	{
				$this->setLastError('���� �� ������');
				return false;
	       	}
		}
		else
		{
			$this->setLastError('no fileURL');
			return false;
		}
				
	        $content = array();
	        $content['phone'] = $data['phone'];
	        $content['name'] = $data['name'];	        
	        $content['email'] = $data['email'];
	        $content['file'] = $_file['filename'];
			
			db_query('
	    	       INSERT INTO `'.TABLE_RESPONSES.'`
	    	       SET
	    	           `type` = "resumeFile",
	    	           `datetime` = NOW(),
	    	           `subject` = "'.safe($data['name']).'",
	    	           `content` = "'.safe(serialize($content)).'",
	    	           `for_type` = "vacancy",
	    	           `for_id` = '.$vacancy['id'].',
	    	           `from_type` = "'.safe($data['from_type']).'",
	    	           `from_id` = "'.safe($data['from_uid']).'",
	    	           `vacancy_type` = '.($vacancy['_exclusive'] ? 1 : 0).',
	    	           `referal_id` = 1,
	    	           `is_subscribe` = 1
	    	');
	    	    
	    	$resp_id = db_insert_id();
	    	    
			$in_db = db_assoc('SELECT `md5`, `checked`, `agree` FROM `'.TABLE_RESP.'` WHERE `md5`="'.$_file['md5'].'" AND (`checked` = 1 OR `agree` = 1)');
				    
			$_checked = false;
			$_agree = false;
				    
			foreach ($in_db as $_k => $_v)
			{
			   	if ($_v['checked'])
			   		$_checked = true;
				    		
			   	if ($_v['agree'])
			   		$_agree = true;
			}
				    
			db_query('
			    			INSERT
			              		INTO `'.TABLE_RESP.'`
							SET
			                	`date` = NOW(),
			                    `file` = "'.$_file['filename'].'",
			                    `md5` = "'.$_file['md5'].'",
			                    `ip` = "'.safe($_SERVER['REMOTE_ADDR']).'",
			                    `response_id` = '.(int)$resp_id.',
			                    `agree`='.( isset($data['agree']) || $_agree ? 1 : 0 ).',
			                    `checked`='.( $_checked ? 1 : 0 ).'	 	                    
			');
			    
			$employee = company_employee_get( $vacancy['employee_id'] );
    	    
	    	$from = '������ '.R_NUM.' <mail@rabota'.R_NUM.'.ru>';
    	    
    	    $files = array();
    	    
    	    if ($company['tariff_disable'])
    	    	$txt = template('emails/resumeSendFile', array('data' => $content, 'file' => $_file, 'vacancy' => $vacancy));
    	    else
    	    {
    	      	$txt = template('emails/resumeSendFile', array('data' => $content, 'file' => $_file, 'vacancy' => $vacancy, 'company' => $company));
    	       	$files[ ROOT.$_file['filename'] ] = $content['name'].'.'.$_file['pathinfo']['extension'];
    	    }    	    	

			$files[] = true;
    	    $txt = str_replace( '%responseId', $resp_id, $txt );
    	    
    	    ////////////// ������� � ����� //////////////  
    	    $news_list = db_assoc('SELECT * from `'.TABLE_PUBLICATIONS.'` where `type`=7 AND `publicate`=1 ORDER by `date` DESC LIMIT 3');
	        $news = '';
	        foreach ($news_list as $n)
	           	$news .= '<li><a href="http://'.R_HOST.'/news/'.$n['id'].'">'.$n['name'].'</a></li>';
	        $txt .= '<p>������� �� "������ '.R_NUM.'":</p><ul>'.$news.'</ul> ';
	        //////////////////////
	        
   	    	email_send( 
    	       		$employee['email'].';', 
    	       		'������ �� '.$data['name'].' �� �������� '.$vacancy['name'], 
	    	       
    	       		email_blank( $txt,  '������ �� ��������'), 
    	       		$from, 
    	       		$files
	       		);
	       	return true;
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
	protected  $_responseVal;
	
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
	
	public function getResponseVal ()
	{
		return $this->_responseVal;
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
			$this->_responseVal = self::convertToCp1251($xmlrpcresp->val);
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
					if (is_numeric($param))
						$p = new xmlrpcval($param, 'int');
					else
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
	
	
	public static function convertToCp1251 ($str)
	{
		$res = '';
		if (is_array($str))
		{
			$res = array();
			foreach ($str as $k=>$v)
			{
				$k = iconv('UTF-8', 'cp1251//IGNORE', $k);
				$v = self::convertToCp1251 ($v);
				$res[$k] = $v;
			}
		}
		else
			$res = iconv('UTF-8', 'cp1251//IGNORE', $str);
		return $res;
		
	}
	
}

?>