<?php
class ElkaSantasWidget extends CWidget {

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
		$this->render('santas', array(
		));
    }

}
