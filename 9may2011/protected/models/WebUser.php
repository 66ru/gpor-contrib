<?php
 
class WebUser extends CWebUser {

	public function init()
	{
		if (!RAuthHelper::getCurrentHash()) {
			$this->logout();
		}

		parent::init();
	}
}
