<?php

require_once(LIB_PATH.DS.'xmlrpc-3.0.0.beta'.DS.'xmlrpc.inc');


class RCommentsWidget extends Widget {
    public $objectId = 0;
	public $objectTypeCode = 0;

	public function run() {
        $this->_postComment();
        $comments = $this->_retrieveComments();

        $this->render('default', array(
			'comments' => $comments,
		));
	}

    protected function _retrieveComments() {
        $arg = array(
            // 'base64' => true,
            'objectId' => $this->objectId,
            'objectTypeCode' => $this->objectTypeCode
        );

        $list = (array)XMLRPCHelper::sendMessage('comments.listComments', $arg);

        /*foreach($list as &$item) {
            $item['content'] = base64_decode($item['content']);
            $item['contentPreparsed'] = base64_decode($item['contentPreparsed']);
            $item['contentParsed'] = base64_decode($item['contentParsed']);
        }*/

        return $list;
    }

    protected function _postComment() {
        if(!$_POST || !isset($_POST['comment']))
            return;

        $app = Yii::app();
        $web_user = $app->user;
        $comment = (array)$_POST['comment'];

        if(!isset($comment['content']) || !isset($comment['parentCommentId']) || $web_user->isGuest)
            return;

        $user = User::model()->findByPk($web_user->getId());
        if(!$user)
            return;

        $comment_array = array(
            'base64' => true,
            'objectId' => $this->objectId,
            'objectTypeCode' => $this->objectTypeCode,
            'authorUid' => $user->getUid(),
            'content' => base64_encode($comment['content']),
            'parentCommentId' => $comment['parentCommentId']
        );

        XMLRPCHelper::sendMessage("comments.postComment", $comment_array);

        header('Location: '. $_SERVER['REQUEST_URI']);
        exit;
    }

    protected function getUserForRComment($arr) {
        if(!is_array($arr) || !isset($arr['restrictedUserId'])) {
            return new User();
        }

        if($user = User::model()->findByPk($arr['restrictedUserId']))
            return $user;


        $userdata = XMLRPCHelper::sendMessage('user.getUserInfo', $arr['restrictedUserId'], 'restrictedUuserId');
        if($userdata) {
            $user = new User();
            $user->gender = $userdata['gender'];
            $user->name = $userdata['username'];
            $user->uid = $userdata['uid'];
            $user->id = $userdata['id'];
            $user->profileLink = $userdata['profileLink'];
            $user->image = $userdata['avatarSmallUrl'];
            $user->save();
            return $user;
        }

        return new User();
    }

}
