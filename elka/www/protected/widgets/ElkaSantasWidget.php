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

        $res = $this->getWishes();

        $items = array();
        foreach ($res as $item) {
            if ($item['santaName']) {
                $items[] = array(
                    'santaName' => $item['santaName'],
                    'santaLink' => $item['santaLink'],
                );
            }
        }

		$this->render('santas', array(
            'items' => $items,
		));
    }

}
