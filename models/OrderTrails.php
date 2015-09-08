<?php

/**
 * This is the model class for table "order_trails".
 *
 * The followings are the available columns in table 'order_trails':
 * @property integer $pk_order_id
 * @property integer $fk_domain_id
 * @property integer $fk_customer_id
 * @property integer $fk_invoice_id
 * @property integer $fk_service_id
 * @property integer $order_status
 * @property string $order_amount
 * @property integer $fk_billing_id
 * @property string $order_description
 * @property integer $is_trial
 * @property integer $trialDuration
 * @property integer $extention_period
 *
 * The followings are the available model relations:
 * @property Customers $fkCustomer
 * @property Invoices $fkInvoice
 * @property SubscriptionChange[] $subscriptionChanges
 */
class OrderTrails extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OrderTrails the static model class
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
		return 'order_trails';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fk_domain_id, fk_customer_id, fk_invoice_id, fk_service_id, order_status, order_amount, fk_billing_id, order_description, is_trial, trialDuration, extention_period', 'required'),
			array('fk_domain_id, fk_customer_id, fk_invoice_id, fk_service_id, order_status, fk_billing_id, is_trial, trialDuration, extention_period', 'numerical', 'integerOnly'=>true),
			array('order_amount', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('pk_order_id, fk_domain_id, fk_customer_id, fk_invoice_id, fk_service_id, order_status, order_amount, fk_billing_id, order_description, is_trial, trialDuration, extention_period', 'safe', 'on'=>'search'),
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
			'fkCustomer' => array(self::BELONGS_TO, 'Customers', 'fk_customer_id'),
			'fkInvoice' => array(self::BELONGS_TO, 'Invoices', 'fk_invoice_id'),
			'subscriptionChanges' => array(self::HAS_MANY, 'SubscriptionChange', 'fk_order_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'pk_order_id' => 'Pk Order',
			'fk_domain_id' => 'Fk Domain',
			'fk_customer_id' => 'Fk Customer',
			'fk_invoice_id' => 'Fk Invoice',
			'fk_service_id' => 'Fk Service',
			'order_status' => 'Order Status',
			'order_amount' => 'Order Amount',
			'fk_billing_id' => 'Fk Billing',
			'order_description' => 'Order Description',
			'is_trial' => 'Is Trial',
			'trialDuration' => 'Trial Duration',
			'extention_period' => 'Extention Period',
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

		$criteria->compare('pk_order_id',$this->pk_order_id);
		$criteria->compare('fk_domain_id',$this->fk_domain_id);
		$criteria->compare('fk_customer_id',$this->fk_customer_id);
		$criteria->compare('fk_invoice_id',$this->fk_invoice_id);
		$criteria->compare('fk_service_id',$this->fk_service_id);
		$criteria->compare('order_status',$this->order_status);
		$criteria->compare('order_amount',$this->order_amount,true);
		$criteria->compare('fk_billing_id',$this->fk_billing_id);
		$criteria->compare('order_description',$this->order_description,true);
		$criteria->compare('is_trial',$this->is_trial);
		$criteria->compare('trialDuration',$this->trialDuration);
		$criteria->compare('extention_period',$this->extention_period);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/*
		* Returns Domain URL by ID
		* @Param - Domain ID primary
	*/
	public function getDomainUrl( $id )
	{
		$domain = Yii::app()->db->createCommand()
				  ->select('domain_url')
				  ->from('domains')
				  ->where('pk_domain_id = "'.$id.'"')
				  ->queryAll();
	   return $domain[0]['domain_url'];			  
	}
}