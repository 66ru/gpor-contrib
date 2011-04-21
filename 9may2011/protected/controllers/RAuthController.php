<?php

class RAuthController extends Controller
{
	public function actionLogin()
	{
		$hash = RAuthHelper::getRequestHash();
		$remember_me = (bool)@$_GET['remember_me'];

		if($hash !== false) {
			RAuthHelper::setCookieHash($hash, $remember_me);
		}

		$this->showImage();
	}

	public function actionLogout() {

		$hash = RAuthHelper::getRequestHash();

		if($hash !== false) {
			RAuthHelper::unsetCookieHash();
			Yii::app()->user->logout();
		}

		$this->showImage();
	}

	protected function showImage() {
		header("Content-Type: image/png");
		header('P3P: CP="CAO PSA OUR"');
		die(pack('N*',
			0X89504E47, 0X0D0A1A0A, 0X0000000D, 0X49484452,
			0X00000001, 0X00000001, 0X08060000, 0X001F15C4,
			0X89000000, 0X0B494441, 0X5478DA63, 0X60000200,
			0x00050001, 0XE9FADCD8, 0X00000000, 0X49454E44,
			0XAE426082));
	}
}
