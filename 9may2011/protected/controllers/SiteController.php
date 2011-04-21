<?php

class SiteController extends Controller
{
	public function init()
	{
		if (Yii::app()->user->isGuest) {
			$identity=new RAuthUserIdentity();
			$identity->authenticate();
			if ($identity->errorCode===$identity::ERROR_NONE) {
				Yii::app()->user->login($identity, 30*60);
			}
		}
	}

	public function actionIndex()
	{
		$this->render('index', array(
			'user' => Yii::app()->user,
		));
	}

	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}
}