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

	public function actionDelete($commentId) {
		if (!Yii::app()->request->isAjaxRequest)
			throw new CHttpException(403);

		$commentId = intval($commentId);
		if (Yii::app()->user->isAdmin) {
			if ($comment = Comment::model()->findByPk($commentId)) {
				if ($comment->delete()) {
					$result = array(
						'errCode' => 0,
					);
				} else {
					$result = array(
						'errCode' => 1,
						'errMsg' => 'Не удалось удалить комментарий',
					);
				}
			} else {
				$result = array(
					'errCode' => 1,
					'errMsg' => 'Комментарий не найден',
				);
			}
		} else {
			$result = array(
				'errCode' => 1,
				'errMsg' => 'Вы не админ',
			);
		}

		$result['commentId'] = $commentId;
		echo CJSON::encode($result);
		Yii::app()->end();
	}
}
