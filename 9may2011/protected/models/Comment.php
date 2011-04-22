<?php

/**
 * This is the model class for table "comment".
 *
 * The followings are the available columns in table 'comment':
 * @property integer $id
 * @property string $text
 * @property integer $datetime
 * @property integer $userId
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
			array('text', 'safe'),
			array('userId, datetime', 'numerical', 'integerOnly'=>true),
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

	public function getFormattedDatetime() {
		
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