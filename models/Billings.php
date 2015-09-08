<?php

/**
 * This is the model class for table "billings".
 *
 * The followings are the available columns in table 'billings':
 * @property integer $pk_billing_id
 * @property integer $fk_customer_id
 * @property string $billing_created
 * @property string $name_on_card
 * @property string $creditcard
 * @property string $cvv
 * @property integer $expiry_month
 * @property integer $expiry_year
 * @property string $billing_email
 * @property string $billing_phone
 * @property string $billing_fax
 * @property integer $billing_address
 * @property string $billing_city
 * @property string $billing_postal_code
 * @property string $billing_county
 * @property string $billingProfileId
 */
class Billings extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Billings the static model class
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
		return 'billings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array( 
			array('billing_phone, billing_fax', 'numerical'), 
		    array('billing_email,  billing_city,  billing_county, billing_postal_code,billing_country,billing_phone', 'required', 'on'=>'billing'), 
			 
			/*array('name_on_card, billing_email, billingProfileId', 'length', 'max'=>30),
			array('creditcard', 'length', 'max'=>100),
			array('cvv', 'length', 'max'=>5),*/
			//array('billing_phone, billing_fax, billing_city, billing_postal_code, billing_county', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('pk_billing_id, fk_customer_id, billing_created, name_on_card, creditcard, cvv, expiry_month, expiry_year, billing_email, billing_phone, billing_fax, billing_address, billing_city, billing_postal_code, billing_county, billingProfileId', 'safe', 'on'=>'search'),
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
			'pk_billing_id' => 'Pk Billing',
			'fk_customer_id' => 'Fk Customer',
			'billing_created' => 'Billing Created',
			'name_on_card' => 'Name On Card',
			'creditcard' => 'Creditcard',
			'cvv' => 'Cvv',
			'expiry_month' => 'Expiry Month',
			'expiry_year' => 'Expiry Year',
			'billing_email' => 'Billing Email',
			'billing_phone' => 'Telephone',
			'billing_fax' => 'Fax',
			'billing_address' => 'Billing Address',
			'billing_city' => 'City',
			'billing_postal_code' => 'Postal Code',
			'billing_county' => 'County/State',
			'billing_country' => ' Country',
			'billingProfileId' => 'Billing Profile',
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

		$criteria->compare('pk_billing_id',$this->pk_billing_id);
		$criteria->compare('fk_customer_id',$this->fk_customer_id);
		$criteria->compare('billing_created',$this->billing_created,true);
		$criteria->compare('name_on_card',$this->name_on_card,true);
		$criteria->compare('creditcard',$this->creditcard,true);
		$criteria->compare('cvv',$this->cvv,true);
		$criteria->compare('expiry_month',$this->expiry_month);
		$criteria->compare('expiry_year',$this->expiry_year);
		$criteria->compare('billing_email',$this->billing_email,true);
		$criteria->compare('billing_phone',$this->billing_phone,true);
		$criteria->compare('billing_fax',$this->billing_fax,true);
		$criteria->compare('billing_address',$this->billing_address);
		$criteria->compare('billing_city',$this->billing_city,true);
		$criteria->compare('billing_postal_code',$this->billing_postal_code,true);
		$criteria->compare('billing_county',$this->billing_county,true);
		$criteria->compare('billingProfileId',$this->billingProfileId,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	
	public function updateBilling($post)
	{
		    $customer_id = Yii::app()->user->pk_customer_id;
		    $customer_model	 = new Customers;
			/*	Set Scenario for validation  */
		    $customer_model->scenario = "billing";
			$this->scenario  = "billing";
			
			 
			 $customer_model->attributes = $post['Customers']; 
 			 $this->attributes 			 = $post['Billings'];
			 
			if($customer_model->validate() && $this->validate())
 			{	
				$customer_model->updateByPk($customer_id, $post['Customers']);
				$this->updateByPk($post['billing_id'], $post['Billings']);
				 
				/*  Updating Technical and billing */
				//$related_customer = $customer_model->getAllRelatedCustomers( $customer_id );
				 
				/*foreach($related_customer[0] as $key=>$value) {
				//	echo '<pre>'; print_r($related_customer[0]); exit; 
					if($post['is_billing'] == $key)
 						$customer_model->updateByPk($key, array('is_billing'=>'1'));
					else
						$customer_model->updateByPk($key, array('is_billing'=>'0'));
						
					if($post['is_technical'] == $key)	
						$customer_model->updateByPk($key, array('is_technical'=>'1'));
					else
						$customer_model->updateByPk($key, array('is_technical'=>'0'));
						
					if( $post['is_emergency_contact'] == $key )		
						$customer_model->updateByPk($key, array('is_emergency_contact'=>'1'));
					else
						$customer_model->updateByPk($key, array('is_emergency_contact'=>'0'));	
				}*/
				return true;
			}
			else
				return false;
		 
	}
	
	 
}