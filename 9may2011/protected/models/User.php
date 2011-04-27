<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $name
 * @property string $profileLink
 * @property string $image
 */
class User extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function rules()
	{
		return array(
			array('name, profileLink, image, uid', 'safe'),
		);
	}

    public function getAvatar() {
        return $this->image ? $this->image : 'http://img.66.ru/dez/noavatar25.gif';
    }

    public function getProfileLink() {
        return $this->profileLink;
    }

    public function getUsername() {
        return $this->name;
    }

    public function getUid() {
        return $this->uid;
    }
}