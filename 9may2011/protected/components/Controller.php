<?php

class Controller extends CController
{
    private $_pageDescription;
    private $_pageKeywords;
	public $layout='//layouts/main';

	public function getPageKeywords()
	{
		if($this->_pageKeywords!==null)
			return $this->_pageKeywords;
		else
		{
			return '';
		}
	}
	public function setPageKeywords($value)
	{
		$this->_pageKeywords=$value;
	}

	public function getPageDescription()
	{
		if($this->_pageDescription!==null)
			return $this->_pageDescription;
		else
		{
			return '';
		}
	}

	public function setPageDescription($value)
	{
		$this->_pageDescription=$value;
	}

}