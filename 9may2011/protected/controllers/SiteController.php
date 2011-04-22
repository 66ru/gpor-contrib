<?php

class SiteController extends Controller
{
	public function init()
	{
		if (Yii::app()->user->isGuest ||
				!User::model()->findByPk(Yii::app()->user->getId())) {
			$identity=new RAuthUserIdentity();
			$identity->authenticate();
			if ($identity->errorCode===$identity::ERROR_NONE) {
				$user = User::model()->findByPk($identity->getId());
				if (empty($user)) {
					$user = new User();
					$user->id = $identity->gpor_userid;
					$user->name = $identity->name;
					$user->profileLink = $identity->profileLink;
					$user->image = $identity->image;
					$user->save();
				}
				Yii::app()->user->login($identity, 30*60);
			}
		}
	}

	public function actionIndex()
	{
        $this->layout = 'index';

		$this->render('index', array(
			'user' => Yii::app()->user,
		));
	}

    public function actionNewsShow() {
        $id = $_GET['id'];

        $this->render('newsShow', array(
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