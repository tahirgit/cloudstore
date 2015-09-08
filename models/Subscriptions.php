<?php

/**
 * This is the model class for table "subscriptions".
 *
 * The followings are the available columns in table 'subscriptions':
 * @property integer $pk_sub_id
 * @property integer $fk_customer_id
 * @property integer $sub_parent_id
 * @property integer $fk_service_id
 * @property string $service_name
 * @property integer $sub_description
 * @property integer $sub_status
 * @property string $sub_created
 * @property integer $sub_duration_day
 * @property string $sub_expiration
 * @property integer $sub_month
 * @property integer $num_sub_paid
 * @property string $plan_subscribed
 * @property integer $is_custom
 * @property double $amount_paid
 *
 * The followings are the available model relations:
 * @property Invoices[] $invoices
 * @property Payments[] $payments
 * @property Promotions[] $promotions
 * @property SubscriptionChange[] $subscriptionChanges
 * @property Customers $fkCustomer
 */
class Subscriptions extends CActiveRecord
{
    //status 0=
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Subscriptions the static model class
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
		return 'subscriptions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fk_customer_id, sub_parent_id, fk_service_id, service_name, sub_description, sub_status, sub_created, sub_duration_day, sub_expiration, sub_month, num_sub_paid, plan_subscribed, is_custom, total_amount', 'required'),
			array('fk_customer_id, sub_parent_id, fk_service_id, sub_description, sub_status, sub_duration_day, sub_month, num_sub_paid, is_custom', 'numerical', 'integerOnly'=>true),
			array('amount_paid', 'numerical'),
			array('service_name', 'length', 'max'=>120),
			array('plan_subscribed', 'length', 'max'=>25),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('pk_sub_id, fk_customer_id, sub_parent_id, fk_service_id, service_name, sub_description, sub_status, sub_created, sub_duration_day, sub_expiration, sub_month, num_sub_paid, plan_subscribed, is_custom, total_amount', 'safe', 'on'=>'search'),
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
			'invoices' => array(self::HAS_MANY, 'Invoices', 'fk_subscription_id'),
			'payments' => array(self::HAS_MANY, 'Payments', 'fk_subscription_id'),
			'promotions' => array(self::HAS_MANY, 'Promotions', 'fk_subscription_id'),
			'subscriptionChanges' => array(self::HAS_MANY, 'SubscriptionChange', 'fk_subscription_id'),
			'fkCustomer' => array(self::BELONGS_TO, 'Customers', 'fk_customer_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'pk_sub_id' => 'Pk Sub',
			'fk_customer_id' => 'Fk Customer',
			'sub_parent_id' => 'Sub Parent',
			'fk_service_id' => 'Fk Service',
			'service_name' => 'Service Name',
			'sub_description' => 'Sub Description',
			'sub_status' => 'Sub Status',
			'sub_created' => 'Sub Created',
			'sub_duration_day' => 'Sub Duration Day',
			'sub_expiration' => 'Sub Expiration',
			'sub_month' => 'Sub Month',
			'num_sub_paid' => 'Num Sub Paid',
			'plan_subscribed' => 'Plan Subscribed',
			'is_custom' => 'Is Custom',
			'total_amount' => 'Total Amount',
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

		$criteria->compare('pk_sub_id',$this->pk_sub_id);
		$criteria->compare('fk_customer_id',$this->fk_customer_id);
		$criteria->compare('sub_parent_id',$this->sub_parent_id);
		$criteria->compare('fk_service_id',$this->fk_service_id);
		$criteria->compare('service_name',$this->service_name,true);
		$criteria->compare('sub_description',$this->sub_description);
		$criteria->compare('sub_status',$this->sub_status);
		$criteria->compare('sub_created',$this->sub_created,true);
		$criteria->compare('sub_duration_day',$this->sub_duration_day);
		$criteria->compare('sub_expiration',$this->sub_expiration,true);
		$criteria->compare('sub_month',$this->sub_month);
		$criteria->compare('num_sub_paid',$this->num_sub_paid);
		$criteria->compare('plan_subscribed',$this->plan_subscribed,true);
		$criteria->compare('is_custom',$this->is_custom);
		$criteria->compare('total_amount',$this->total_amount);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	public function getCustomerSubscriptions($id='')
	{
		$condition = '';
		$cusModel = new Customers;
		if( !empty($id) ) // search for one subscription by id
		   $condition = " AND s.pk_sub_id = '".$id."' ";	
		 
		// get customer Primary key value   
		$customer_id 	= Yii::app()->user->pk_customer_id;
		$parent_id 		=  Yii::app()->user->parent_id;
		$allChilds      = $cusModel->getChildCustomers($parent_id);
 	 
		$subscriptions  = Yii::app()->db->createCommand()
						->select("s.*,d.*,i.*")
						->from("subscriptions AS s")
						->join("invoice_subscriptions AS insub", "s.pk_sub_id = insub.fk_subscription_id")
						->join("invoices AS i" , "i.pk_invoice_id = insub.fk_invoice_id")
 						->leftjoin("domains AS d", "s.fk_domain_id = d.pk_domain_id")
						->where("(s.fk_customer_id IN (".$allChilds.") or s.fk_customer_id = ".$parent_id.')'.$condition)
						->group("s.pk_sub_id")
						->queryAll();				
		 				
						 		
 		return $subscriptions;								  
	}
	
	/*
		* Get SUbscription by ID
	*/
	public function getSubscriptionByID($id)
	{
		# - Subscription Detail
		//$subscriptions  =  $this->find('pk_sub_id = '.$id);	
		$subscriptions =  Yii::app()->db->createCommand()
						->select("s.*, d.domain_url")
 						->from("subscriptions AS s")
						->leftjoin("domains AS d", "d.pk_domain_id = s.fk_domain_id")
  						->where("s.pk_sub_id = ".$id)
 						->queryAll();
		 
		
		# - Customized Subscription Component
		$components =  Yii::app()->db->createCommand()
						->select("cs.*")
 						->from("subscription_customized AS cs")
  						->where("cs.subscription_id = ".$id)
 						->queryAll();		
						
		# - Invoices of this Subscription
		$invoices =  Yii::app()->db->createCommand()
						->select("i.*, insub.*, p.*, c.*")
 						->from("invoice_subscriptions AS insub")
						->leftjoin("invoices AS i" , "i.pk_invoice_id = insub.fk_invoice_id")
						->leftjoin("payments AS p", "p.fk_invoice_id = i.pk_invoice_id")
						->leftjoin("customers AS c", "i.fk_customer_id = c.pk_customer_id")
 						->where("insub.fk_subscription_id = ".$id)
 						->queryAll();	
						
		
  		return array("subscription"=>$subscriptions, "invoices"=>$invoices, "components"=>$components);	
	}
	
	/*
		* Get SUbscription by ID
	*/
	public function getSubscriptionByIDsStr($ids)
	{
		# - Subscription Detail
		//$subscriptions  =  $this->find('pk_sub_id = '.$id);	
		$subscriptions =  Yii::app()->db->createCommand()
						->select("s.*, d.domain_url")
 						->from("subscriptions AS s")
						->leftjoin("domains AS d", "d.pk_domain_id = s.fk_domain_id")
  						->where("s.pk_sub_id IN (".$ids.")")
 						->queryAll();
		 
		
		# - Customized Subscription Component
		$components =  Yii::app()->db->createCommand()
						->select("cs.*")
 						->from("subscription_customized AS cs")
  						->where("cs.subscription_id IN (".$ids.")")
 						->queryAll();		
						
		# - Invoices of this Subscription
		$invoices =  Yii::app()->db->createCommand()
						->select("i.*, insub.*, p.*, c.*")
 						->from("invoice_subscriptions AS insub")
						->leftjoin("invoices AS i" , "i.pk_invoice_id = insub.fk_invoice_id")
						->leftjoin("payments AS p", "p.fk_invoice_id = i.pk_invoice_id")
						->leftjoin("customers AS c", "i.fk_customer_id = c.pk_customer_id")
 						->where("insub.fk_subscription_id IN (".$ids.")")
 						->queryAll();	
						
		
  		return array("subscription"=>$subscriptions, "invoices"=>$invoices, "components"=>$components);	
	}
	
/*
		* Get SUbscription by Invoice ID
	*/
	public function getSubscriptionByInvoiceID($invoiceId)
	{
		# - Subscription Detail
		$subscriptions  =  Yii::app()->db->createCommand()
		                   ->select("insub.*, s.*")
						   ->from("invoice_subscriptions AS insub")
						   ->leftjoin("subscriptions AS s", "s.pk_sub_id = insub.fk_subscription_id")
						   ->where("insub.fk_invoice_id = ".$invoiceId)
						   ->queryAll(); 
		
		# - Customized Subscription Component
		$allSubscriptions = array();
		foreach($subscriptions as $key=>$sub) {
		$allSubscriptions['subscription'][$key] = $sub;
		$allSubscriptions['subscription'][$key]['components'] =  Yii::app()->db->createCommand()
						->select("cs.*")
 						->from("subscription_customized AS cs")
  						->where("cs.subscription_id = ".$sub['pk_sub_id'])
 						->queryAll();		
		}
		 
		
  		return array("subscription"=>$allSubscriptions);	
	}
	/*
		Get All users by DomainID
	*/
	public function getMailboxUsers( $domain_id)
	{
		$mailUsers = Yii::app()->db->createCommand()
					 ->select("u.*,d.*")
					 ->from("services_users AS u")
					 ->join("domains AS d","u.fk_domain_id = d.pk_domain_id")
					 ->where('u.fk_domain_id = "'.$domain_id.'"')
					 ->queryAll();
		return $mailUsers;			 
	} 
	
	/************************************************
		Get Mailbox user info by ID
	***********************************************/
	public function getUserById( $userId)
	{
		$mailUser = Yii::app()->db->createCommand()
					 ->select("u.*,d.*")
					 ->from("services_users AS u")
					 ->join("domains AS d","u.fk_domain_id = d.pk_domain_id")
					 ->where('u.pk_user_id = "'.$userId.'" and u.fk_customer_id = "'.Yii::app()->user->pk_customer_id.'"')
					 ->queryRow();
			 
		return $mailUser;			 
	}
	
	/********************************************************
		Create Subscriptions for each cart Item
		@Param - invoiceID  New invoice created id
		@Param - CartItems 
	*********************************************************/
	public function createSubscriptionByCart( $invoiceID, $cartItems )
	{
		$command = Yii::app()->db->createCommand();
		foreach( $cartItems as $item )
		{
			# -  Create Subscription for each Item
			$command->insert("subscriptions", array(
														"fk_customer_id"=>Yii::app()->user->pk_customer_id, 
														"service_name"=>$item['item']['serviceName'],
														"sub_status"=>'0',
														"sub_created"=>new CDbExpression( 'NOW()' ),
														"sub_duration_day"=>'30',
														"sub_expiration"=>date('Y-m-d H:i:s', strtotime("+30 days")),
														"sub_month"=>date("m"),
														"plan_subscribed"=>$item['item']['package_type'],
														"total_amount"=>$item['item']['totalAmount'])
														 
											       );
		 	$sub_id = Yii::app()->db->getLastInsertId();
			
			# - Put Customized Items in sub subscription_customized	
			foreach($item['item']['customized'] as $custom) {
				$command->insert("subscription_customized", array(
																'component'=>$custom['component'],
																'subscription_id'=>$sub_id,
																'component_value'=>$custom['component_value'],
																'unit_price'=>$custom['unit_price'],
																'component_price'=>$custom['component_price'] )
													    );
				#- Create Domain for VDI, Mailbox, CPanel
				if(  $custom['component'] == "Domain Name") 
					$domain = $custom['component_value'];
													
			}
			# - Create relationship with invoice.
			$command->insert("invoice_subscriptions", array(
																'fk_invoice_id'=>$invoiceID,
																'fk_subscription_id'=>$sub_id,
																'created'=>time() )
													    );	
														
		    #- Create Domain for VDI, Mailbox, CPanel
			if( isset($domain) )
			{
				$command->insert("domains", array(
																'domain_url'=>$domain,
																'fk_subscription_id'=>$sub_id,
																'fk_customer_id'=>Yii::app()->user->pk_customer_id,
																'date_created'=>new CDbExpression( 'NOW()' ),
																'expiry_date'=>date('Y-m-d H:i:s', strtotime("+45 days")),
																'login'=>Yii::app()->user->id )
													    );	
			  $domain_id = Yii::app()->db->getLastInsertId();											
			  $command->update("subscriptions", array('fk_domain_id'=>$domain_id), 'pk_sub_id=:sub_id', array(":sub_id"=>$sub_id));									
			}																				   		
		}
	}
	
	# your subscription
	public function getSubscriptionName()
	{
		$subs = $this->getCustomerSubscriptions(); 	
		# Find Customer Subscriptions
		$services = array();
		
		foreach($subs as $key=>$value){
		
			if(in_array("Server", $value))$services["Server"] = "Server";
			if(in_array("CPanel", $value))$services["cPanel"] = "cPanel";
			if(in_array("VDI", $value))$services["VDI"] = "VDI";
			if(in_array("VOIP", $value))$services["VOIP"] = "VOIP";
			if(in_array("VDC", $value))$services["VDC"] = "VDC";
			if(in_array("Online Backup", $value))$services["Online Backup"] = "Online Backup";
			if(in_array("Mailbox", $value))$services["Mailbox"] = "Mailbox";
			
		}
		
		return $services;
	}
}