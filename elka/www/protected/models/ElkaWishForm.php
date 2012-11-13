<?php
class ElkaWishForm extends CFormModel
{
    const STATUS_NONE = 0;
    const STATUS_WAIT = 10;
    const STATUS_CHECKED = 20;

	public $status;
	public $santaName;
	public $santaLink;
	public $id;

	public static function statusTypes()
	{
		return array(
			self::STATUS_NONE => 'Поарок никто не взял',
			self::STATUS_WAIT => 'Подарок взят, но пока еще нет в офисе',
			self::STATUS_CHECKED => 'Подарок в офисе',
			);
	}
	
	public function rules()
    {
        return array(
			array('santaName, santaLink', 'safe' ),
			array('id, status', 'required'),
		);
    }
    
    public function attributeLabels()
    {
        return array(
        	'santaName' => 'Имя (или ник на 66) дарителя',
        	'santaLink' => 'Ссылка на профиль на 66',
        	'status' => 'Статус подарка',
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
            'status' => array(
                'type' => 'dropdownlist',
                'items' => self::statusTypes(),
            ),
            'id' => array(
                'type' => 'hidden',
            ),
            'santaName' => array(
                'type' => 'text',
            ),
            'santaLink' => array(
                'type' => 'text',
            ),
        );
        return $res;

    }

    public function getButtons()
    {
        $res = array();
        $res['submit'] = array(
            'type' => 'submit',
            'label'=> 'Отправить',
        );
        return $res;
    }

}
?>