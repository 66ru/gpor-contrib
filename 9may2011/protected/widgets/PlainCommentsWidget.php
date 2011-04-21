<?php
 
class PlainCommentsWidget extends Widget {

	public $offset = 0;

	public function run() {
		$comments = Comment::model()->findAll(array(
			'offset' => $this->offset,
		));
		
		$this->render('index',array(
			'comments' => $comments,
		));
	}

}
