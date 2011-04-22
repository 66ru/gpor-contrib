<?php

/**
 * RAuthUserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class RAuthUserIdentity extends CBaseUserIdentity
{
	public $gpor_userid;
	public $name;
	public $profileLink;
	public $image;

	public function getId()
	{
		return $this->gpor_userid;
	}

	public function getName()
	{
		return $this->name;
	}

	public function authenticate()
	{
		$gpor_user = RauthHelper::getResponse("get_user_info");
		if (!$gpor_user) {
			$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
		} else {
			$this->name = $gpor_user['username'];
			$this->gpor_userid = $gpor_user['user_id'];
			$this->profileLink = Yii::app()->params['gpor_server_uid'].'/user/'.$this->gpor_userid.'/';
			$this->image = $gpor_user['avatar']['small'];
			$this->errorCode = self::ERROR_NONE;
		}

		return !$this->errorCode;
	}
}