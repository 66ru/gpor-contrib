<?php
class ElkaMenuWidget extends CWidget {

    public $uri = false;

    public function run() {
		parent::run();

        $items = array (
            array ('link' => array('/site/index'), 'caption' => 'Главная', 'regexp' =>  false),
            array ('link' => array('/site/join'), 'caption' => 'Обратная связь', 'regexp' =>  '/join*'),
            array ('link' => array('/site/gifts'), 'caption' => 'Подарки', 'regexp' =>  '/gifts*'),
        );

        $uri = $this->uri ? $this->uri : Yii::app()->request->getRequestUri();

        $tmp = array();
        $active = false;
        foreach ($items as $item)
        {
            $isActive = false;
            if (!$active && $item['regexp'] && preg_match('#'.$item['regexp'].'#', $uri))
            {
                $active = true;
                $isActive = true;
            }
            $item['active'] = $isActive;
            $tmp[] = $item;
        }
        if (!$active)
            $tmp[0]['active'] = true;
        $items = $tmp;

		$this->render('menu', array(
            'items' => $items,
		));
    }

}
