<?php
class ElkaLoginForm extends CFormModel
{
	public $login;
	public $password;

	public static function admins()
	{
		return array(
			Yii::app()->params['adminLogin'] => Yii::app()->params['adminPassword'],
			);
	}
	
	public function rules()
    {
        return array(
			array('login', 'required', 'message' => 'Укажите логин' ),
            array('password', 'required', 'message' => 'Укажите пароль' ),
            array('password', 'checkPassword'),
		);
    }

    public function checkPassword ($attribute, $params) {
        $admins = self::admins();
        foreach ($admins as $k => $v) {
            if ($this->login == $k) {
                if ($this->$attribute == $v) {
                    return true;
                }
            }
        }
        $this->addError($attribute, 'Логин или пароль указан неверно');
    }
    
    public function afterValidate()
    {
    	return parent::afterValidate();
    }

    public function attributeLabels()
    {
        return array(
        	'login' => 'Логин',
        	'comment' => 'Пароль',
        );
    }

    public function getFormRenderData () {
        $elements = array(
            'elements' => array(),
            'enctype' => 'multipart/form-data',
            'elements' => $this->getFormElements(),
            'buttons' => $this->getButtons(),
        );
        return $elements;
    }

    public function getFormElements ()
    {
        $res = array(
            'login' => array(
                'type' => 'text',
            ),
            'password' => array(
                'type' => 'password',
            ),
        );
        return $res;

    }

    public function getButtons()
    {
        $res = array();
        $res['submit'] = array(
            'type' => 'submit',
            'label'=> 'Войти',
        );
        return $res;
    }

}
?>