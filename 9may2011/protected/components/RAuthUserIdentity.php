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
    public $uid;

	public function getId()
	{
		return $this->gpor_userid;
	}

	public function getName()
	{
		return $this->name;
	}

    public function rauthRedirect() {
        if(Yii::app()->request->cookies["firstvisitpassed"]->value)
			return;

        $cookie = new CHttpCookie("firstvisitpassed", 1);
        Yii::app()->request->cookies["firstvisitpassed"] = $cookie;

        $location = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI'];
        header('Location: http://66.ru/external_service/rauth/?location=' . $location);
        Yii::app()->end();
    }

	public function authenticate()
	{
        $this->rauthRedirect();

		$gpor_user = RauthHelper::getResponse("get_user_info");
		if (!$gpor_user) {
			$this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
		} else {
			$this->name = $gpor_user['username'];
			$this->gpor_userid = $gpor_user['user_id'];
			$this->profileLink = Yii::app()->params['gpor_server_uid'].'/user/'.$this->gpor_userid.'/';
            $this->uid = $gpor_user['uid'];
			$this->image = !empty($gpor_user['avatar']['small']) ? $gpor_user['avatar']['small'] : 'http://img.66.ru/dez/noavatar25.gif';
			$this->errorCode = self::ERROR_NONE;
		}

		return !$this->errorCode;
	}
}