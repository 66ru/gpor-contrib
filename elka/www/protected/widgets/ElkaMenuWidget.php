<?php
class VitrinaMenuWidget extends CWidget {

    public $uri = false;

    public function run() {
		parent::run();

        $items = array (
            array ('link' => array('/vitrinaShop/index'), 'caption' => 'Магазины', 'regexp' =>  '/shop*'),
            array ('link' => array('/vitrinaCollection/section', 'sectionId'=>311), 'caption' => 'Для женщин', 'regexp' => '/coll/cat311.*'),
            array ('link' => array('/vitrinaCollection/section', 'sectionId'=>313), 'caption' => 'Для мужчин', 'regexp' => '/coll/cat313.*'),
            array ('link' => array('/vitrinaCollection/section', 'sectionId'=>314), 'caption' => 'Для детей', 'regexp' => '/coll/cat314.*'),
            array ('link' => array('/vitrinaCollection/section', 'sectionId'=>315), 'caption' => 'Обувь', 'regexp' => '/coll/cat315.*'),
            array ('link' => array('/vitrinaAction/index'), 'caption' => 'Акции', 'regexp' => '/action*'),
            array ('link' => array('/vitrinaWidget/create'), 'caption' => 'Создать стиль', 'regexp' => '/mystyle*'),
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
        $items = $tmp;

		$this->render('menu', array(
            'items' => $items,
		));
    }

}
