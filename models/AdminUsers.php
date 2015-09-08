<?php

/**
 * This is the model class for table "admin_users".
 *
 * The followings are the available columns in table 'admin_users':
 * @property integer $user_id
 * @property string $fullname
 * @property string $username
 * @property string $password
 * @property string $email
 * @property integer $fk_group_id
 */
class AdminUsers extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return AdminUsers the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'admin_users';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fullname, username, password, email, fk_group_id', 'required'),
			array('fk_group_id', 'numerical', 'integerOnly'=>true),
			array('fullname, username, password, email', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, fullname, username, password, email, fk_group_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
			'fullname' => 'Fullname',
			'username' => 'Username',
			'password' => 'Password',
			'email' => 'Email',
			'fk_group_id' => 'Fk Group',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('fullname',$this->fullname,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('fk_group_id',$this->fk_group_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}