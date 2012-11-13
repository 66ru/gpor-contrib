<?php
class ElkaLogoutForm extends CFormModel
{
	public function rules()
    {
        return array(
		);
    }

    public function attributeLabels()
    {
        return array(
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
        );
        return $res;

    }

    public function getButtons()
    {
        $res = array();
        $res['submit'] = array(
            'type' => 'submit',
            'label'=> 'Выйти',
        );
        return $res;
    }

}
?>