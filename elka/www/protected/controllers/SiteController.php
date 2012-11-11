<?php
class SiteController extends Controller
{
	public $layout='column1';

    public function actionJoin () {
        $form = new ElkaJoinForm;
        $cForm = new FormsFormRender(array());
        $cForm->action = CHtml::normalizeUrl(array('/site/join'));
        $cForm->model = $form;

        $submittedText = false;
        if ($cForm->submitted()) {
            if ($cForm->model->validate()) {
                // todo: отправить письмо
                $submittedText = '<p>Спасибо, ваше письмо отправлено.</p><p>Мы обязательно с вами свяжемся.</p>';
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

        $this->render ('join', array(
            'cForm' => $cForm,
            'submittedText' => $submittedText,
        ));
    }

	public function actionIndex()
	{
        $this->render ('main', array());
	}
	
    public function actionGifts()
    {
        $this->render ('gifts', array());
    }


}