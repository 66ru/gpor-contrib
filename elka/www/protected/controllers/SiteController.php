<?php
class SiteController extends Controller
{
	public $layout='column1';

    protected function isAdmin () {
        Yii::app()->session->open();
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    protected function getWishes () {
        $items = array();
        $c = file_get_contents(FILES_PATH . DS . 'elka_list.json');
        if ($c) {
            $tmp = CJSON::decode($c);
            foreach ($tmp as $id => $item) {
                $items[$id] = array_merge(array(
                    'id' => '',
                    'name' => '',
                    'age' => '',
                    'wish' => '',
                    'status' => 0,
                ), $item);
            }
        }
        return $items;
    }

    protected function getNews () {
        $items = array();
        $c = file_get_contents(FILES_PATH . DS . 'elka_news.json');
        if ($c) {
            $items = CJSON::decode($c);
        }
        return $items;
    }

    protected function saveWishes ($items) {
        file_put_contents(FILES_PATH . DS . 'elka_list.json', CJSON::encode($items));
        return true;
    }

    public function actionJoin () {
        $form = new ElkaJoinForm;
        if (isset($_GET['theme'])) {
            $form->theme = (int)$_GET['theme'];
        }
        if (isset($_GET['giftTo'])) {
            $id = (int)$_GET['giftTo'];
            $items = $this->getWishes();
            if (isset($items[$id])) {
                $form->theme = ElkaJoinForm::THEME_GIFT;
                $form->comment = 'Хочу купить подарок: '.$items[$id]['name'].' ('.$items[$id]['wish'].')';
            }
        }
        $cForm = new FormsFormRender(array());
        $cForm->action = CHtml::normalizeUrl(array('/site/join'));
        $cForm->model = $form;

        $submittedText = false;
        if ($cForm->submitted()) {
            if ($cForm->model->validate()) {
                // todo: отправить письмо
                $submittedText = '<p>Спасибо, ваше письмо отправлено.</p><p>Мы обязательно с вами свяжемся.</p>';

                $fields = array(
                    'name' => $cForm->model->name,
                    'theme' => ElkaJoinForm::getTheme($cForm->model->theme),
                    'email' => $cForm->model->email,
                    'phone' => $cForm->model->phone,
                    'comment' => $cForm->model->comment,
                );
                $html = $this->renderPartial('application.views.mail.feedback', $fields, true);

                $message = array(
                    'to_email' => Yii::app()->params['managerEmail'],
                    'to_username' => 'admin',
                    'from_email' => Yii::app()->params['senderEmail'],
                    'subject' => 'Елка желаний - '.ElkaJoinForm::getTheme($cForm->model->theme),
                    'html' => $html,
                );
                MailHelper::sendMail($message);
            }

            if (Yii::app()->request->isAjaxRequest) {
                if ($submittedText) {
                    $res = array(
                        'success' => true,
                        'text' => $submittedText,
                    );
                }
                else {
                    $tmp = $form->getErrors();
                    $errors = array();
                    foreach ($tmp as $inputName => $inputErrors) {
                        foreach ($inputErrors as $inputError) {
                            $errors[CHtml::activeName($cForm->model, $inputName)] = $inputError;
                            break;
                        }
                    }
                    $res = array(
                        'errors' => $errors,
                    );
                }
                echo CJSON::encode($res);
                die();

            }

        }

        $this->pageTitle = 'Стань участником акции'.Yii::app()->params['title'];

        $this->render ('join', array(
            'cForm' => $cForm,
            'submittedText' => $submittedText,
        ));
    }

	public function actionIndex()
	{
        $news = $this->getNews();

        $this->pageTitle = 'Благотворительная акция &laquo;Ёлка желаний&raquo;'.Yii::app()->params['title'];

        $this->render ('main', array(
            'news' => $news,
        ));
	}
	
    public function actionGifts()
    {
        $items = $this->getWishes();

        $cForm = false;
        $showForm = false;
        if ($this->isAdmin()) {
            $form = new ElkaWishForm();
            $cForm = new FormsFormRender(array());
            $cForm->action = CHtml::normalizeUrl(array('/site/gifts'));
            $cForm->model = $form;

            if ($cForm->submitted()) {
                if ($cForm->model->validate()) {
                    $id = $cForm->model->id;
                    if (isset($items[$id])) {
                        foreach ($cForm->model->attributes as $k=>$v) {
                            $items[$id][$k] = $v;
                        }
                        $this->saveWishes($items);
                        $this->redirect (array('/site/gifts'));
                        Yii::app()->end();
                    }
                    else {
                        $cForm->model->addError('santaName', 'Ребенок не найден в базе');
                    }
                    if ($cForm->model->getErrors())
                        $showForm = true;
                }
            }

        }

        $res = array();
        $tmp = array_keys(ElkaWishForm::statusTypes());
        foreach ($tmp as $s) {
            $res[$s] = array();
        }
        foreach ($items as $item) {
            $res[$item['status']][] = $item;
        }

        $this->pageTitle = 'Список желаний детей'.Yii::app()->params['title'];

        $this->render ('gifts', array(
            'data' => $res,
            'statuses' => $tmp,
            'cForm' => $cForm,
            'showForm' => $showForm,
        ));
    }

    public function actionAdmin()
    {
        if ($this->isAdmin()) {
            $form = new ElkaLogoutForm();
            $cForm = new FormsFormRender(array());
            $cForm->action = CHtml::normalizeUrl(array('/site/logout'));
            $cForm->model = $form;
        }
        else {
            $form = new ElkaLoginForm();
            $cForm = new FormsFormRender(array());
            $cForm->action = CHtml::normalizeUrl(array('/site/admin'));
            $cForm->model = $form;

            if ($cForm->submitted()) {
                if ($cForm->model->validate()) {
                    $_SESSION['is_admin'] = $cForm->model->login;
                    $this->redirect (array('/site/admin'));
                    Yii::app()->end();
                }
            }
        }

        $this->render ('login', array(
            'cForm' => $cForm,
        ));
    }

    public function actionLogout()
    {
        if ($this->isAdmin()) {
            unset($_SESSION['is_admin']);
        }
        $this->redirect (array('/site/admin'));
    }

}
