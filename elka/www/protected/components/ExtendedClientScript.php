<?php
/* 
 * Compress and cache used JS and CSS files.
 * Needs jsmin in helpers and csstidy in extensions
 *
 * Ties into the 1.0.4 (or > SVN 813) Yii CClientScript functions
 *
 * @author Maxximus <maxximus007@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009
 * @license http://www.yiiframework.com/license/
 * @version 0.4
 */

class ExtendedClientScript extends CClientScript
{
	/**
	 * Compress all Javascript files with JSMin. JSMin must be installed as an extension in dir jsmin.
	 * code.google.com/p/jsmin-php/
	 */
	public $compressJs = false;
	/**
	 * Compress all CSS files with CssTidy. CssTidy must be installed as an extension in dir csstidy.
	 * Specific browserhacks will be removed, so don't add them in to be compressed CSS files
	 * csstidy.sourceforge.net
	 */
	public $compressCss = false;
	/**
	 * Combine all JS and CSS files into one. Be careful with relative paths in CSS.
	 */
	public $combineFiles = false;
	/**
	 * Exclude certain files from inclusion. array('/path/to/excluded/file') Useful for fixed base
	 * and incidental additional JS.
	 */
	public $excludeFiles = array();
	/**
	 * Path where the combined/compressed file will be stored. Will use coreScriptUrl if not defined
	 */
	public $filePath;
	/**
	 * If true, all files to be included will be checked if they are modified.
	 * To enhance speed (eg production) set to false.
	 */
	public $autoRefresh = true;
	/**
	 * Relative Url where the combined/compressed file can be found
	 */
	public $fileUrl;
	/**
	 * Path where files can be found
	 */
	public $basePath;
	/**
	 * Used for garbage collection. If not accessed for that period: remove.
	 */
	public $ttlDays = 1;
	/**
	 * prefix for the combined/compressed files
	 */
	public $prefix = 'c_';
	/**
	 * CssTidy template. See CssTidy for more information
	 */
	public $cssTidyTemplate = "highest_compression";
	/**
	 * CssTidy parameters. See CssTidy for more information
	 */
	public $cssTidyConfig = array(
				  'css_level' => 'CSS2.1',
				  'discard_invalid_properties' => FALSE,
				  'lowercase_s' => FALSE,
				  'sort_properties' => FALSE,
				  'sort_selectors' => FALSE,
				  'preserve_css' => FALSE,
				  'timestamp' => FALSE,
				  'remove_bslash' => TRUE,
				  'compress_colors' => TRUE,
				  'compress_font-weight' => TRUE,
				  'remove_last_,' => TRUE,
				  'optimise_shorthands' => 1,
				  'case_properties' => 1,
				  'merge_selectors' => 2,
			 );

	private $_changesHash = '';
	private $_renewFile;

	/**
	 * Will combine/compress JS and CSS if wanted/needed, and will continue with original
	 * renderHead afterwards
	 *
	 * @param <type> $output
	 */
	public function renderHead(&$output)
	{
		if ($this->combineFiles)
		{
			if (isset($this->scriptFiles[parent::POS_HEAD]) && count($this->scriptFiles[parent::POS_HEAD]) !==  0)
			{
				$jsFiles = $this->scriptFiles[parent::POS_HEAD];
				if (!empty($this->excludeFiles))
				{
					foreach ($jsFiles as &$fileName)
						(in_array($fileName, $this->excludeFiles)) AND $fileName = false;
					$jsFiles = array_filter($jsFiles);
				}
				$this->combineAndCompress('js',$jsFiles,parent::POS_HEAD);
			}

			if (count($this->cssFiles) !==  0)
			{
				foreach ($this->cssFiles as $url => $media)
					$cssFiles[$media][$url] = $url;
				foreach ($cssFiles as $media => $url)
					$this->combineAndCompress('css',$url, $media);
			}
		}
		parent::renderHead($output);
	}

	/**
	 * Will combine/compress JS if wanted/needed, and will continue with original
	 * renderBodyEnd afterwards
	 *
	 * @param <type> $output
	 */
	public function renderBodyBegin(&$output)
	{
		if ($this->combineFiles)
		{
			if (isset($this->scriptFiles[parent::POS_BEGIN]) && count($this->scriptFiles[parent::POS_BEGIN]) !==  0)
			{
				$jsFiles = $this->scriptFiles[parent::POS_BEGIN];
				if (!empty($this->excludeFiles))
				{
					foreach ($jsFiles as &$fileName)
						(in_array($fileName, $this->excludeFiles)) AND $fileName = false;
					$jsFiles = array_filter($jsFiles);
				}
				$this->combineAndCompress('js',$jsFiles,parent::POS_BEGIN);
			}
		}
		parent::renderBodyBegin($output);
	}

	/**
	 * Will combine/compress JS if wanted/needed, and will continue with original
	 * renderBodyEnd afterwards
	 *
	 * @param <type> $output
	 */
	public function renderBodyEnd(&$output)
	{
		if ($this->combineFiles)
		{
			if (isset($this->scriptFiles[parent::POS_END]) && count($this->scriptFiles[parent::POS_END]) !==  0)
			{
				$jsFiles = $this->scriptFiles[parent::POS_END];
				if (!empty($this->excludeFiles))
				{
					foreach ($jsFiles as &$fileName)
						(in_array($fileName, $this->excludeFiles)) AND $fileName = false;
					$jsFiles = array_filter($jsFiles);
				}
				$this->combineAndCompress('js',$jsFiles,parent::POS_END);
			}
		}
		parent::renderBodyEnd($output);
	}

	/**
	 *	Performs the actual combining and compressing
	 *
	 * @param <type> $type
	 * @param <type> $urls
	 * @param <type> $pos
	 */
	private function combineAndCompress($type, $urls, $pos)
	{
		$this->fileUrl or $this->fileUrl = $this->getCoreScriptUrl();
		$this->filePath or $this->filePath = realpath($_SERVER['DOCUMENT_ROOT'].$this->fileUrl);
		$this->basePath or $this->basePath = $_SERVER['DOCUMENT_ROOT'];

		if ($this->autoRefresh)
		{
			$mtimes = array();
			foreach ($urls as $file)
				$mtimes[] = filemtime($this->basePath.DIRECTORY_SEPARATOR.trim($file,DIRECTORY_SEPARATOR));
			$this->_changesHash = md5(serialize($mtimes));
		}

		$combineHash = md5(implode('',$urls));

		$optionsHash = ($type == 'js') ? md5($this->basePath.$this->compressJs.$this->ttlDays.$this->prefix):
			md5($this->basePath.$this->compressCss.$this->ttlDays.$this->prefix.serialize($this->cssTidyConfig));

		$fileName = $this->prefix.md5($combineHash.$optionsHash.$this->_changesHash).".$type";

		$this->_renewFile = (file_exists($this->filePath.DIRECTORY_SEPARATOR.$fileName)) ? false : true;

		if ($this->_renewFile)
		{
			$this->garbageCollect($type);

			$combinedFile = '';

			foreach ($urls as $key => $file)
				$combinedFile .= file_get_contents($this->basePath.DIRECTORY_SEPARATOR.$file);

			if ($type == 'js' && $this->compressJs)
				$combinedFile = $this->minifyJs($combinedFile);

			if ($type == 'css' && $this->compressCss)
				$combinedFile = $this->minifyCss($combinedFile);

			file_put_contents($this->filePath.DIRECTORY_SEPARATOR.$fileName, $combinedFile);
		}

		foreach ($urls as $url)
			$this->scriptMap[basename($url)] = $this->fileUrl.DIRECTORY_SEPARATOR.$fileName;

		$this->remapScripts();
	}

	private function garbageCollect($type)
	{
		$files = CFileHelper::findFiles($this->filePath, array('fileTypes' => array($type), 'level'=> 0));

		foreach($files as $file)
		{
			if (strpos($file, $this->prefix) !== false && $this->fileTTL($file))
				@unlink($file);
		}
	}

	/**
	 * See if file is ready for deletion
	 *
	 * @param <type> $file
	 */
	private function fileTTL($file)
	{
		$ttl = $this->ttlDays * 60 * 60 * 24;
		return ((fileatime($file) + $ttl) < time()) ? true : false;
	}

	/**
	 * Minify javascript with JSMin
	 *
	 * @param <type> $js
	 */
	private function minifyJs($js)
	{
		Yii::import('application.extensions.jsmin.*');
		require_once('JSMin.php');
		return JSMin::minify($js);
	}

	/**
	 * Yii-ified version of CSS.php of the Minify package with fixed options
	 *
	 * @param <type> $css
	 */
	private function minifyCss($css)
	{
		Yii::import('application.extensions.csstidy.*');
		require_once('class.csstidy.php');

		$cssTidy = new csstidy();
		$cssTidy->load_template($this->cssTidyTemplate);

		foreach($this->cssTidyConfig as $k => $v)
			$cssTidy->set_cfg($k, $v);

		$cssTidy->parse($css);
		return $cssTidy->print->plain();
	}
}