<?php
 
class WebUser extends CWebUser {

	public $isAdmin = false;

	public function init()
	{
		if (!RAuthHelper::getCurrentHash()) {
			$this->logout();
		}

		parent::init();
	}

	public function afterLogin($fromCookie)
	{
		if ( in_array($this->getId(), Yii::app()->params['adminIds']) )
			$this->isAdmin = true;

		parent::afterLogin($fromCookie);
	}
}
