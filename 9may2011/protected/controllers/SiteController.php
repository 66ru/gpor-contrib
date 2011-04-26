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
        $this->setPageTitle('66 Лет Победы');
        $this->setPageDescription('66 Лет Победы');
        
        $news = NewsHelper::getNews();

        $pages = new CPagination($news['totalCount']);
        $pages->pageSize=NewsHelper::PER_PAGE;        

		$this->render('index', array(
			'user' => Yii::app()->user,
            'news' => $news['news'],
            'pages' => $pages,
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
    public function actionNews()
    {
        if(!isset($_GET['id']))
            throw new CHttpException(404);

        $news = NewsHelper::getNews((int) $_GET['id']);
        $other_news = NewsHelper::getNews(null, $_GET['id']);

        if(!$news)
            throw new CHttpException(404);

        $this->setPageTitle($news['news'][0]['title']);
        $this->setPageDescription($news['news'][0]['annotation']);

        $pages = new CPagination($other_news['totalCount']);
        $pages->pageSize=NewsHelper::PER_PAGE;

        $this->render(
            'singleNews',
            array(
                'news' => $news['news'][0],
                'other_news' => $other_news['news'],
                'pages' => $pages,
            )
        );

    }
}