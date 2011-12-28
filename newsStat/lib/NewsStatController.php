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
	
	static $months = array(
   	  	  1 => 'Января',
   	  	  2 => 'Февраля',
   	  	  3 => 'Марта',
   	  	  4 => 'Апреля',
   	  	  5 => 'Мая',
   	  	  6 => 'Июня',
   	  	  7 => 'Июля',
   	  	  8 => 'Августа',
   	  	  9 => 'Сентября',
   	  	  10 => 'Октября',
   	  	  11 => 'Ноября',
   	  	  12 => 'Декабря',
	);

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
				case 'updateFeed':
					$this->_action = 'actionRefreshFeed';
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
		$sections = $this->getSections();
		$data = array(
			'currentSectionId' => 0,
			'currentSectionName' => $sections[0],
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
		);
		
		$sections = $this->getSections();
		
		$this->renderTemplate('main', array(
			'data' => $data,
			'config' => $this->_config,
			'currentSectionId' => 0,
			'currentSectionName' => $sections[0],
			)
		);
	}
	
	public function getSections ()
	{
		return $this->_config['sections'];
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
					$nextSectionId = array_slice(array_keys($sections), $i, 1);
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
	
	public function actionRefreshFeed()
	{
		$data = $this->getFeeds();
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
		$data = $this->getViewsStat($sectionId, 'light');
		if ($data && isset($data[$date]))
			return $data[$date];
		return 0;
	}
	
	protected function getDayComments($date, $sectionId = false)
	{
		$data = $this->getCommentsStat($sectionId, 'light');
		if ($data && isset($data[$date]))
			return $data[$date];
		return 0;
	}
	
	protected function getViewsStat($sectionId = false, $type = 'full')
	{
		$fileName = $sectionId ? 'viewsStatSection'.$sectionId.'.json' : 'viewsStat.json';
		$data = $this->readDataFile ($this->getDataPath().'/'.$fileName);
		if ($data && $type != 'full')
			return $data;
		elseif ($data)
		{
			$result = array();
			$tmp = array();
			foreach ($data as $date => $count)
			{
				$item = array (
					'date' => self::date(strtotime($date)), 
					'count' => $count, 
					'average' => 0, 
				);
				$tmp[strtotime($date)] = $item;
			}
			$keys = array_keys($tmp);
			sort($keys);
			$i = 0;
			foreach ($keys as $k)
			{
				$delay = $i >= $this->_config['graphDelay'] ? $this->_config['graphDelay'] : $i;
				$sum = $tmp[$k]['count'];
				for ($x = 1; $x <=$delay; $x++)
				{
					$sum += $result[$i - $x]['count'];
				}
					
				$tmp[$k]['average'] = $sum > 0 ? ceil($sum / ($delay + 1)) : 0;
				$result[] = $tmp[$k];
				$i++;
			}

			return $result;
		}
		return array();
	}
	
	protected function getCommentsStat($sectionId = false, $type = 'full')
	{
		$fileName = $sectionId ? 'commentsStatSection'.$sectionId.'.json' : 'commentsStat.json';
		$data = $this->readDataFile ($this->getDataPath().'/'.$fileName);
		if ($data && $type != 'full')
			return $data;
		elseif ($data)
		{
			$result = array();
			$tmp = array();
			foreach ($data as $date => $count)
			{
				$item = array (
					'date' => self::date(strtotime($date)), 
					'count' => $count, 
					'average' => 0, 
				);
				$tmp[strtotime($date)] = $item;
			}
			$keys = array_keys($tmp);
			sort($keys);
			$i = 0;
			foreach ($keys as $k)
			{
				$delay = $i >= $this->_config['graphDelay'] ? $this->_config['graphDelay'] : $i;
				$sum = $tmp[$k]['count'];
				for ($x = 1; $x <=$delay; $x++)
				{
					$sum += $result[$i - $x]['count'];
				}
					
				$tmp[$k]['average'] = $sum > 0 ? ceil($sum / ($delay + 1)) : 0;
				$result[] = $tmp[$k];
				$i++;
			}
			
			return $result;
		}
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
		{
			$result = array();
			foreach ($data as $row)
			{
				$row['md5'] = md5($row['link']);
				$row['date'] = self::date($row['pubDate'], true, true);
				$result[] = $row;
			}
			return $result;
		}
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
		else
			header('Content-Type: text/html; charset=utf-8'); 
		include ($viewFile);
		if ($returnResult)
		{
			$res = ob_get_contents();
			ob_end_clean();
			return $res;
		}
		return true;
		
	}
	
    public static function date($datetime = false, $todayFormat = true, $time = false)
    {
    	if( $datetime == false )
    		$datetime = date('Y-m-d H:i:s');
    		
    	if( is_numeric($datetime) )
    		$datetime = date('Y-m-d '.($time?'H:i:s':'00:00:00'), $datetime);
    	else
	   		$datetime = $datetime.(!$time?'00:00:00':'');  

    	if( !preg_match('#(([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}))?\s*(([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2}))?#', trim($datetime), $p ) )
    		return false;
    		
    	list(,, $y, $m, $d, , $h, $i, $s ) = array_map('intval', $p);
    	
    	$out = '';
    	
    	if( $m && $d )
    	{
    	    if( $todayFormat && $d.'-'.$m.'-'.$y == date('j-n-Y') )
    	      $out = '';
    	    else
    		  $out = $d.' '.self::$months[$m].($y && $y != date('Y') ? ' '.$y : '');
    	}
    	
    	if( $time && ($h > 0 || ($h == 0 && $i)) )
    		$out .= ($out ? ' ' : '').($h < 10 ? '0' : '').$h.':'.($i < 10 ? '0' : '').$i.':'.($s < 10 ? '0' : '').$s;
    	
    	return $out;
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