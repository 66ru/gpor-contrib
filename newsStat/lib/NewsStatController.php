<?
/*
 * 
 */
class NewsStatController
{
	protected  $_config;
	protected  $_action;
	protected  $_basePath;
	protected  $_dataFiles;
	
	public $client;
	

	public function __construct($basePath, $config)
	{
		$this->_config = $config;
		$this->_basePath = $basePath;
//		if (!isset($config['rootDir']))
//		{
//			throw new ErrorException('Error. rootDir not found in config');
//			return false;
//		}
		
	}
	
	public function isAjaxRequest ()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}
	
	public function run()
	{
		if ($this->parseUrl())
		{
			$action = $this->_action;
			$this->$action();
		}
	}
	
	protected function parseUrl ()
	{
		$this->_action = 'actionMain';
		$action = isset($_GET['action']) ? $_GET['action'] : false;
		
		if ($action)
		{
			switch ($action)
			{
				case 'next':
					$this->_action = 'actionNextPage';
					break;
				default:
					$this->_action = false;
					break;
			}
		}
		
		if (!$this->_action)
		{
			throw new ErrorException('404 Error. can\'t find action for this url');
			return false;
		}
		
		return true;
	}
	
	public function actionMain()
	{
		$data = array(
			'todayViews' => $this->getDayViews(date('Y-m-d'), false),
			'yesterdayViews' => $this->getDayViews(date('Y-m-d', (time()-60*60*24)), false),
			'todayComments' => $this->getDayComments(date('Y-m-d'), false),
			'yesterdayComments' => $this->getDayComments(date('Y-m-d', (time()-60*60*24)), false),
			'viewsStat' => $this->getViewsStat(false),
			'commentsStat' => $this->getCommentsStat(false),
			'viewsTop' => $this->getViewsTop(false),
			'commentsTop' => $this->getCommentsTop(false),
			'feed' => $this->getFeeds(),
			'ajax' => $this->isAjaxRequest(),
			'config' => $this->_config,
		);
		
		$sections = $this->getSections();
		
		$this->renderTemplate('main', array(
			'data' => $data,
			'currentSectionId' => 0,
			'currentSectionName' => $sections[0],
			)
		);
	}
	
	public function getSections ()
	{
		return array(
			0 => 'Общая статистика',
			3 => 'Бизнес',
		);
		
	}
	
	public function actionNextPage($currentSection = false)
	{
		$currentSection = isset($_GET['currentSectionId']) ? (int)$_GET['currentSectionId'] : null;
		$sections = $this->getSections();
		$nextSection = false;
		$nextSectionName = '';
		if ($currentSection === null)
		{
			throw new ErrorException('404 Error. Page not found');
			return false;
		}
		
		$i = 0;
		$nextSectionId = false;
		foreach ($sections as $sid => $section)
		{
			$i++;
			if ($sid == $currentSection)
			{
				if (count($sections) == $i)
				{
					$nextSectionId = 0;
					$nextSectionName = $sections[0];
				}
				else
				{
					$nextSectionId = array_slice(array_keys($sections), 1, ($i));
					$nextSectionId = $nextSectionId[0];
					$nextSectionName = $sections[$nextSectionId];
					break;
				}
			}
		}
		
		$data = array(
			'currentSectionId' => $nextSectionId,
			'currentSectionName' => $nextSectionName,
			'todayViews' => $this->getDayViews(date('Y-m-d'), $nextSectionId),
			'yesterdayViews' => $this->getDayViews(date('Y-m-d', (time()-60*60*24)), $nextSectionId),
			'todayComments' => $this->getDayComments(date('Y-m-d'), $nextSectionId),
			'yesterdayComments' => $this->getDayComments(date('Y-m-d', (time()-60*60*24)), $nextSectionId),
			'viewsStat' => $this->getViewsStat($nextSectionId),
			'commentsStat' => $this->getCommentsStat($nextSectionId),
			'viewsTop' => $this->getViewsTop($nextSectionId),
			'commentsTop' => $this->getCommentsTop($nextSectionId),
			'ajax' => $this->isAjaxRequest(),
		);
		echo json_encode($data);
		die();
		
	}

	public function actionRefreshFeeds()
	{
		
	}

	protected function getBasePath()
	{
		return $this->_basePath;
	}
	
	protected function getViewsPath()
	{
		return $this->getBasePath().'/views';
	}
	
	protected function getDataPath()
	{
		return $this->getBasePath().'/data';
	}
	
	protected function getDayViews($date, $sectionId = false)
	{
		$data = $this->getViewsStat($sectionId);
		if ($data && isset($data[$date]))
			return $data[$date];
		return 0;
	}
	
	protected function getDayComments($date, $sectionId = false)
	{
		$data = $this->getCommentsStat($sectionId);
		if ($data && isset($data[$date]))
			return $data[$date];
		return 0;
	}
	
	protected function getViewsStat($sectionId = false)
	{
		$fileName = $sectionId ? 'viewsStatSection'.$sectionId.'.json' : 'viewsStat.json';
		$data = $this->readDataFile ($this->getDataPath().'/'.$fileName);
		if ($data)
			return $data;
		return array();
	}
	
	protected function getCommentsStat($sectionId = false)
	{
		$fileName = $sectionId ? 'commentsStatSection'.$sectionId.'.json' : 'commentsStat.json';
		$data = $this->readDataFile ($this->getDataPath().'/'.$fileName);
		if ($data)
			return $data;
		return array();
	}
	
	protected function getViewsTop($sectionId = false)
	{
		$fileName = $sectionId ? 'viewsTopSection'.$sectionId.'.json' : 'viewsTop.json';
		$data = $this->readDataFile ($this->getDataPath().'/'.$fileName);
		if ($data)
			return $data;
		return array();
	}
	
	protected function getCommentsTop($sectionId = false)
	{
		$fileName = $sectionId ? 'commentsTopSection'.$sectionId.'.json' : 'commentsTop.json';
		$data = $this->readDataFile ($this->getDataPath().'/'.$fileName);
		if ($data)
			return $data;
		return array();
	}
	
	protected function getFeeds()
	{
		$data = $this->readDataFile ($this->getDataPath().'/feeds.json');
		if ($data)
			return $data;
		return array();
	}
	
	protected function readDataFile ($file)
	{
		if (!isset($this->_dataFiles[$file]))
		{
			$this->_dataFiles[$file] = false;
			if (file_exists($file))
			{
				$content = file_get_contents($file);
				if ($content)
				{
					$data = @json_decode($content);
					if ($data)
					{
						$this->_dataFiles[$file] = newsStatobject2Array($data);
					}
				}
			}
		}
		return $this->_dataFiles[$file];
	}
	
	public function renderTemplate ($template, $templateData = array(), $returnResult = false)
	{
		$viewFile = $this->getViewsPath().'/'.$template.'.php';
		if (!file_exists($viewFile))
		{
			throw new ErrorException('Error. viewFile '.$viewFile.' not found');
			return false;
		}
		foreach ($templateData as $k=>$v)
			$$k = $v;
		unset ($templateData);
		
		if ($returnResult)
			ob_start();
		include ($viewFile);
		if ($returnResult)
		{
			$res = ob_get_contents();
			ob_end_clean();
			return $res;
		}
		return true;
		
	}
	
	
}

function newsStatobject2Array($d)
{
        if (is_object($d))
        {
            $d = get_object_vars($d);
        }
 
        if (is_array($d))
        {
            return array_map(__FUNCTION__, $d);
        }
        else
        {
            return $d;
        }
}


?>