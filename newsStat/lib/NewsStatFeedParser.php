<?
/*
 * парсер новостных фидов
 */
class NewsStatFeedParser
{
	protected  $_processDir; // путь где лежат файлы с информацией о том, какие фиды сейчас парсятся
	protected  $_resultDir; // путь где лежат файлы с распарсенными фидами
	protected  $_feedsFile; // путь где лежат файлы с распарсенными фидами
	protected  $_lastError;
	protected  $_result; // результат выполнения работы	
	protected  $_feeds = null; // список фидов	
	
	public $maxFeedCount = false; // максимальное кол-во фидов, которые обрабатывает один парсер	
	public $pid = 10; // текущий pid парсера	
	

	public function __construct($pid, $config = array())
	{
		$this->pid = $pid;
		$this->_processDir = isset($config['processDir']) ? $config['processDir'] : dirname(__FILE__).'/../feedProcess';
		$this->_resultDir = isset($config['resultDir']) ? $config['resultDir'] : dirname(__FILE__).'/../feedResult';
		$this->_feedsFile = isset($config['feedsFile']) ? $config['feedsFile'] : dirname(__FILE__).'/../feeds.xml';
		if (!file_exists($this->_feedsFile))
		{
			$this->_lastError = 'Can\'t find feedsFile '.$this->_feedsFile;
			throw new ErrorException('Can\'t find feedsFile '.$this->_feedsFile);
		}
	}
	
	public function getResultDir()
	{
		return $this->_resultDir;
	}
	
	public function run ()
	{
		$feeds = $this->findFreeFeeds ();
		if ($feeds)
		{
			if($this->lockFeeds($feeds))
			{
				foreach ($feeds as $feed)
				{
					$result = $this->parseRss($feed);
					if ($result)
						$this->writeParseResult($feed, $result);
					$this->unlockFeed($feed);
				}
				if ($this->unlockFeeds())
				{
					return true;
				}
				return false;
			}
		}
		return false;
	}

	protected function parseRss($url)
	{
		$result = array();
		//$rss = simplexml_load_file($url);
//		$ch = curl_init();
//		curl_setopt($ch, CURLOPT_URL, $url);
//		curl_setopt($ch, CURLOPT_HEADER, TRUE);
//		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
//		$head = curl_exec($ch);
//		$c = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//		curl_close($ch);

		$c = @file_get_contents($url);
		if ($c)
		{
			$rss = @simplexml_load_string($c);
			if ($rss)
			{
				$result = array(
						'sourceLink' => (string)$rss->channel->link,
						'sourceName' => (string)$rss->channel->title,
						'items' => array(),
					);
				if ($rss->channel->item)
				{
					foreach ($rss->channel->item as $item)
					{
						$result['items'][] = array (
							'pubDate' => (string)$item->pubDate,
							'title' => (string)$item->title,
							'link' => (string)$item->link,
						);
					}
				}
				return $result;
			}
			else
			{
				$this->_lastError = 'Can\'t parse url '.$url;
			}
		}
		else
		{
			$this->_lastError = 'Can\'t open url '.$url;
			return false;
		}
		return $result;
	}
	
	protected function writeParseResult($feed, $result)
	{
		$resultFile = $this->_resultDir.'/'.md5($feed).'.json';
		$tmpFile = $this->_resultDir.'/'.md5($feed).'.tmp.json';
		if(!$handle = fopen($tmpFile, 'w+'))
		{
			$this->_lastError = 'Can\'t create file '.$tmpFile;
			return false;
		}
		fwrite($handle, json_encode($result));
		fclose($handle);
    	if (file_exists($tmpFile)){
	    	if (file_exists($resultFile)) unlink($resultFile);
    		copy($tmpFile, $resultFile);
    	}
    	unlink($tmpFile);
		
		return true;
	}
	
	protected function lockFeeds ($feeds)
	{
		$lockFile = $this->_processDir.'/'.$this->pid.'.lock';
		if(!$handle = fopen($lockFile, 'w+'))
		{
			$this->_lastError = 'Can\'t create file '.$lockFile;
			return false;
		}
		fwrite($handle, implode('|', $feeds));
		fclose($handle);
		chmod ( $lockFile , 0777 );
		return true;
	}
	
	protected function unlockFeed ($feed)
	{
		$lockFile = $this->_processDir.'/'.$this->pid.'.lock';
		$content = @file_get_contents($lockFile);
		if (!$content)
		{
			$this->_lastError = 'Can\'t read file '.$lockFile;
			return false;
		}

		$tmp = explode('|', $content);
		$feeds = array();
		foreach ($tmp as $_tmp)
		{
			if ($_tmp == $feed)
				continue;
			$feeds[] = $_tmp;
		}
		
		if ($feeds)
			return $this->lockFeeds($feeds);
		return true;
	}
	
	
	protected function unlockFeeds ()
	{
		$lockFile = $this->_processDir.'/'.$this->pid.'.lock';
		unlink($lockFile);
		return true;
	}
	
	
	/*
	 * кол-во запущенных процессов
	 */
	public function totalRunningProcesses ()
	{
		$dir = $this->_processDir;
		$dh  = opendir($dir);
		
		$n = 0;
		while (false !== ($filename = readdir($dh)))
		{
			if ($filename === '.' || $filename === '..')
				continue;
			$fullFilename = $dir.'/'.$filename;
			if (is_dir($fullFilename))
				continue;
				
			$tmp = explode('.', $filename);
			$pid = $tmp[0];
			if (posix_getsid($pid))
			{
				$n++;
			}
		}
		return $n;
	}
	
	public function findFreeFeeds ()
	{
		$allFeeds = $this->getFeeds();
		if (!$allFeeds)
		{
			$this->_lastError = 'feed list is empty';
			return false;
		}
		
		$dir = $this->_processDir;
		$dh  = opendir($dir);
		
		$proccessingFeeds = array();
		
		// составляем список фидов, которые сейчас обрабатываются
		while (false !== ($filename = readdir($dh)))
		{
			if ($filename === '.' || $filename === '..')
				continue;
			$fullFilename = $dir.'/'.$filename;
			if (is_dir($fullFilename))
				continue;
				
			$content = @file_get_contents($fullFilename);
			$tmp = explode('.', $filename);
			$pid = $tmp[0];
			if (posix_getsid($pid))
			{
				if ($content)
				{
					$tmp = explode('|', $content);
					foreach ($tmp as $feed)
						$proccessingFeeds[$feed] = 1;
				}
			}
		}
		
		// составляем список фидов, которые можно обрабатывать
		$result = array();
		if (count($proccessingFeeds))
		{
			$i = 0;
			foreach ($allFeeds as $_tmp)
			{
				if (isset($proccessingFeeds[$_tmp]))
					continue;
				$result[] = $_tmp;
			}
		}
		else
			$result = $allFeeds;
		
		// оставляем максимальное кол-во фидов, которые можно обрабатывать
		if (!$result)
		{
			$this->_result = 'There are no free feeds';
			return false;
		}
		
		if ($this->maxFeedCount && count($result) > $this->maxFeedCount)
			$result = array_slice($result, 0, $this->maxFeedCount);
		
		return $result;
	}
	
	public function getFeeds ()
	{
		if ($this->_feeds === null)
		{
			$this->_feeds = array();
			$c = @file_get_contents($this->_feedsFile);
			if (!$c)
			{
				$this->_lastError = 'Can\'t read file '.$this->_feedsFile;
				return false;
			}
			
			$rss = @simplexml_load_string($c);
			if ($rss)
			{
				if ($rss->body->outline)
				{
					foreach ($rss->body->outline as $node)
					{
						$this->_feeds = array_merge($this->_feeds, $this->_parseFeedsNode($node));
					}
				}
			}
			else
			{
				$this->_lastError = 'Can\'t parse feeds file '.$this->_feedsFile;
			}			
			
		}
		return $this->_feeds;
	}


	private function _parseFeedsNode ($node)
	{
		$result = array();
		if (property_exists($node, 'outline'))
		{
			foreach ($node->outline as $_node)
			{
				$result = array_merge($result, $this->_parseFeedsNode($_node));
			}
		}
		else
		{
			$tmp = array();
			foreach ($node->attributes() as $k=>$v)
			{
				$tmp[$k] = (string)$v;
			}

			if (isset($tmp['xmlUrl']) && isset($tmp['type']) && $tmp['type'] == 'rss')
				$result = array($tmp['xmlUrl']);
		}

		return $result;
	}
	
}

?>