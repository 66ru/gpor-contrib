<?php

/**
 * This is the model class for table "comment".
 *
 * The followings are the available columns in table 'comment':
 * @property integer $id
 * @property string $text
 * @property string $date
 * @property string $userId
 */
class Comment extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function rules()
	{
		return array(
			array('text, date', 'safe'),
			array('userId', 'numerical', 'integerOnly'=>true),
		);
	}

	public function relations()
	{
		return array(
			'user' => array(self::BELONGS_TO, 'User', 'userId', 'together'=>true),
		);
	}

	public function beforeId($id)
	{
		$this->getDbCriteria()->mergeWith(array(
		   'condition' => 'id <'.$id,
		));
		return $this;
	}

	public function scopes() {
		return array(
			'limitDefault' => array(
				'limit' => Yii::app()->params['plainCommentsCount'],
			),
		);
	}

	public function defaultScope() {
		return array(
			'order'=> 'id DESC',
		);
	}
}