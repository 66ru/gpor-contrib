<?php
 
class PlainCommentsWidget extends Widget {

	public $lastId = 0;

	public function run() {
		if (!$this->lastId) {
			$comments = Comment::model()->limitDefault()->findAll();
			$commentsCount = Comment::model()->count();
			$view = 'index';
		} else {
			$comments = Comment::model()->limitDefault()->beforeId($this->lastId)->findAll();
			$commentsCount = Comment::model()->beforeId($this->lastId)->count();
			$view = 'block';
		}

		$this->render($view,array(
			'comments' => $comments,
			'user' => Yii::app()->user,
			'moreComments' => $commentsCount > Yii::app()->params['plainCommentsCount'],
		));
	}

}
