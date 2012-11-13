<?php
class ElkaCountersWidget extends CWidget {

    protected function getWishes () {
        $items = array();
        $c = file_get_contents(FILES_PATH . DS . 'elka_list.json');
        if ($c) {
            $items = CJSON::decode($c);
        }
        return $items;
    }

    public function run() {
		parent::run();

        $items = $this->getWishes();
        $counters = array();
        foreach($items as $item) {
            if (!isset($counters[$item['status']]))
                $counters[$item['status']] = 1;
            else
                $counters[$item['status']]++;
        }

		$this->render('counters', array(
            'checked' => (isset($counters[ElkaWishForm::STATUS_CHECKED]) ? $counters[ElkaWishForm::STATUS_CHECKED] : 0),
            'none' => (isset($counters[ElkaWishForm::STATUS_NONE]) ? $counters[ElkaWishForm::STATUS_NONE] : 0),
		));
    }

}
