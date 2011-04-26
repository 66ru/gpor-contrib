<?php
 
class NewsListWidget extends Widget {
    public $news;

	public function run() {

        $this->render('index',array(
            'news' => $this->news,
        ));

	}

}
