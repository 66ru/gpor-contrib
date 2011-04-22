<?php
 
class CommentsController extends Controller {

	public function actionPost() {
		if (Yii::app()->user->isGuest)
			throw new CHttpException(403);

		$comment = new Comment();
		$comment->text = $_POST['newComment'];
		$comment->datetime = time();
		$comment->userId = Yii::app()->user->getId();
		if ($comment->save())
			$this->renderPartial('_comment', array(
				'comment' => $comment,
			));
	}

	public function actionLoad() {
		$lastId = intval($_GET['lastId']);
		if (!$lastId)
			throw new CHttpException(404);

		$PlainCommentsWidget = new PlainCommentsWidget();
		$PlainCommentsWidget->lastId = $lastId;
		$PlainCommentsWidget->run();
		Yii::app()->end();
	}
}
