<?php
 
class CommentsController extends Controller {

	public function actionPost() {
		if (Yii::app()->user->isGuest)
			throw new CHttpException(403);

		$comment = new Comment();
		$comment->text = $_POST['newComment'];
		$comment->datetime = time();
		$comment->userId = Yii::app()->user->getId();
		if ($comment->save()) {
			$this->widget('PlainCommentsWidget',array('commentId'=>$comment->id));
			Yii::app()->end();
		}
	}

	public function actionLoad() {
		$lastId = intval($_GET['lastId']);
		if (!$lastId)
			throw new CHttpException(404);

		$this->widget('PlainCommentsWidget',array('lastId'=>$lastId));
		Yii::app()->end();
	}
}
