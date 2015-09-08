<?php

/**
 * This is the model class for table "invoices".
 *
 * The followings are the available columns in table 'invoices':
 * @property integer $pk_invoice_id
 * @property integer $fk_customer_id
 * @property string $invoice_date
 * @property string $invoice_total
 * @property integer $invoice_status
 * @property string $payment_method
 * @property string $vat
 * @property string $payment_due_date
 * @property integer $fk_subscription_id
 *
 * The followings are the available model relations:
 * @property Customers $fkCustomer
 * @property Subscriptions $fkSubscription
 * @property OrdersTrails[] $ordersTrails
 */
class Invoices extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Invoices the static model class
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
		return 'invoices';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('fk_customer_id, invoice_date, invoice_total, invoice_status, payment_method, vat, payment_due_date, fk_subscription_id', 'required'),
			array('fk_customer_id, invoice_status, fk_subscription_id', 'numerical', 'integerOnly'=>true),
			array('invoice_total', 'length', 'max'=>10),
			array('payment_method', 'length', 'max'=>20),
			array('vat', 'length', 'max'=>30),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('pk_invoice_id, fk_customer_id, invoice_date, invoice_total, invoice_status, payment_method, vat, payment_due_date, fk_subscription_id', 'safe', 'on'=>'search'),
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
			'fkSubscription' => array(self::BELONGS_TO, 'Subscriptions', 'fk_subscription_id'),
			'ordersTrails' => array(self::HAS_MANY, 'OrdersTrails', 'fk_invoice_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'pk_invoice_id' => 'Pk Invoice',
			'fk_customer_id' => 'Fk Customer',
			'invoice_date' => 'Invoice Date',
			'invoice_total' => 'Invoice Total',
			'invoice_status' => 'Invoice Status',
			'payment_method' => 'Payment Method',
			'vat' => 'Vat',
			'payment_due_date' => 'Payment Due Date',
			'fk_subscription_id' => 'Fk Subscription',
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

		$criteria->compare('pk_invoice_id',$this->pk_invoice_id);
		$criteria->compare('fk_customer_id',$this->fk_customer_id);
		$criteria->compare('invoice_date',$this->invoice_date,true);
		$criteria->compare('invoice_total',$this->invoice_total,true);
		$criteria->compare('invoice_status',$this->invoice_status);
		$criteria->compare('payment_method',$this->payment_method,true);
		$criteria->compare('vat',$this->vat,true);
		$criteria->compare('payment_due_date',$this->payment_due_date,true);
		$criteria->compare('fk_subscription_id',$this->fk_subscription_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/**
	  *	Return Subscriptions related invoices
	  * @Param customer_id Current Customer ID
	*/   
    public function getAllInvoices( $customer_id , $invoice_id ='' )
	{
		$model = new Customers;
		$customerInfo = $model->find("pk_customer_id = '".$customer_id."'");
		$condition = '';
		if( !empty($invoice_id) )
		$condition = ' AND i.pk_invoice_id = "'.$invoice_id.'" ';
		/*  Invoice -> subscription -> Domain info   */
		$allRelatedCustomer = $model->getChildCustomers($customerInfo->parent_id);
 
			$invoices = Yii::app()->db->createCommand()
				    ->select("i.*,s.*,d.*, p.*, c.*")
					->from("invoices AS i")
 					->join("invoice_subscriptions AS insub", "i.pk_invoice_id = insub.fk_invoice_id")
					->join('subscriptions AS s', 's.pk_sub_id = insub.fk_subscription_id')
					->join("payments AS p", 'i.pk_invoice_id = p.fk_invoice_id')
					->leftjoin("customers AS c", "i.fk_customer_id = c.pk_customer_id")
 					->leftjoin('domains AS d' , 'd.fk_subscription_id = s.pk_sub_id')
 					->where('(i.fk_customer_id IN  ('.$allRelatedCustomer.') or i.fk_customer_id = '.$customerInfo->parent_id.')'.$condition)
					->order('i.pk_invoice_id DESC')
					->group("i.pk_invoice_id")
					->queryAll();
					
  		return $invoices;		
	}
	
	
		/**
	  *	Return Invoice Detail by ID
	  * @Invoice ID
	*/   
    public function getInvoicesById($invoice_id ='0' )
	{
        $subModel = new Subscriptions;   
		$invoice = $this->find("pk_invoice_id = ".$invoice_id); 
		$subscriptions = $subModel->getSubscriptionByInvoiceID($invoice_id);
 					
  		return array("invoice"=>$invoice, "subscriptions"=>$subscriptions);		
	}
	/*
		* Return Invoice Payment Info 
	*/ 
	public function getInvoicePayment( $invoice_id )
	{
		$payment  =  Yii::app()->db->createCommand()
				    ->select("i.invoice_description, i.invoice_status, p.*")
					->from("invoices AS i")
					->join("payments AS p", "i.pk_invoice_id=p.fk_invoice_id")
  					->where('fk_invoice_id = "'.$invoice_id.'"')
 					->queryAll();
		return $payment;			
	}
	/*
		* Update invoice to paid
	*/
	public function updateToPaid($invoiceID)
	{
		# Update invoice Status to paid
		Yii::app()->db->createCommand()->update("invoices", array("invoice_status"=>'1', 'payment_method'=>'creditcard'), 
			"pk_invoice_id=:invoiceID", array(":invoiceID"=>$invoiceID ));
 		# Create new Payment
		//Yii::app()->db->createCommand()->insert('payments', array('payment_date'=>'Tester', 'email'=>'tester@example.com',));
		
		return true;
	}
	
	
	/*
		Update Invoice by GoCardless Payment
	
	*/
	public function updateGocardlessPayment( $confirmed_resource, $invoiceID )
	{
		 
		# Update invoice Status to paid
		Yii::app()->db->createCommand()->update("invoices", array("invoice_status"=>'1', 'payment_method'=>'GoCardless - Pay Now'), 
			"pk_invoice_id=:invoiceID", array(":invoiceID"=>$invoiceID ));
 		# Update new Payment
		$duration = ''; $amount = '';
		if( isset($confirmed_resource->interval_length) )
			$duration = $confirmed_resource->interval_length;
		if( isset($confirmed_resource->amount) )
			$amount = $confirmed_resource->amount;
		elseif( isset($confirmed_resource->max_amount) )
			$amount = $confirmed_resource->max_amount;	
				
 		Yii::app()->db->createCommand()->update("payments", array(
											      "payment_amount"=>$amount, 
												  'payment_method'=>'GoCardless', 
												  'payment_type'=>$confirmed_resource->name, 
												  'duration'=>$duration, 
												  'payment_status'=>'1', 
												  'transaction_number'=>$confirmed_resource->id,
												  'ip_address'=>$_SERVER['REMOTE_ADDR'],
 												  'created_by'=>'Customer'
												  ),  
			"fk_invoice_id=:invoiceID", array(":invoiceID"=>$invoiceID ));
			return true;
	}
	
	
	/*
		* Customer Payment Request for Admin
	*/
	public function sendPaymentRequest( $invoiceId, $payment_method, $payment_type )
	{
		Yii::app()->db->createCommand()->update("invoices", array(
 															  'payment_method'=>$payment_method
 															),"pk_invoice_id=:invoiceID", array(":invoiceID"=>$invoiceId) );
															
		Yii::app()->db->createCommand()->update("payments", array(
															'ip_address'=>$_SERVER['REMOTE_ADDR'],
															'created_by'=>'Customer',
															'payment_method'=>$payment_method,
															'payment_type'=>$payment_type 
															),"fk_invoice_id=:invoiceID", array(":invoiceID"=>$invoiceId) );	
		return true;												
 															
	}
	
	/*
		* Create invoice from Shopping cart items
	*/
	public function createCartItemInvoice()
	{
		# -  Get Visitor Cart Items for Listing
		$model = new Store;
	    $cartItem = $model->getAllCartItem();
		$total = 0; $setup = 0;
		$ItemString = '';
		$i = 1;
		$lastItem = count($cartItem);
	    foreach( $cartItem as $item )
		{
 			$total += $item['item']['totalAmount'];
			$setup += $item['item']['setup_charges'];
			
			if($i > 1 && $lastItem != $i) 
				$ItemString .= ", ";
				
			if( $lastItem == $i && $i != 1)	
				$ItemString .= " and ";
				
			$ItemString .= $item['item']['serviceName'] . ' - '.$item['item']['package_type'];
			
			$i++;
		}
		# - Invoice 
		Yii::app()->db->createCommand()->insert("invoices", array(
																 "fk_customer_id"=>Yii::app()->user->pk_customer_id, 
																 "invoice_date"=>new CDbExpression('NOW()'),
																 "invoice_total"=>$total,
																 "invoice_description"=>$ItemString,
																 "payment_method"=>"Credit Card",
																 "setup_charges"=>$setup,
																 'next_invoice_date'=>date('Y-m-d', strtotime('+1 month') ) 
																)
											    );		
		$invoice_id = Yii::app()->db->getLastInsertId();	
		# - Payments									
		Yii::app()->db->createCommand()->insert("payments", array(
																 "fk_invoice_id"=>$invoice_id, 
																 "payment_date"=>new CDbExpression('NOW()'),
																 "payment_amount"=>$total,
																 "ip_address"=>$_SERVER['REMOTE_ADDR'],
																 "created_by"=>"Customer")
											    );
		# - Create Subscription and relation with invoice
		$subscription = new Subscriptions;
		$subscription_id = $subscription->createSubscriptionByCart($invoice_id, $cartItem);
												
		return $invoice_id;
		
	}
	
	/*
		Create invoice for Cron invoice
	*/
	public function createInvoiceFromInvoice($inv)
	{
		# - Invoice 
		Yii::app()->db->createCommand()->insert("invoices", array(
																 "fk_customer_id"=>$inv->fk_customer_id, 
																 "invoice_date"=>new CDbExpression('NOW()'),
																 "invoice_total"=>($inv->invoice_total - $inv->setup_charges),
																 "invoice_description"=>$inv->invoice_description,
																 "payment_method"=>"None",
																 'next_invoice_date'=>date('Y-m-d', strtotime('+1 month') ) 
																 )
											    );		
		$invoice_id = Yii::app()->db->getLastInsertId();	
		# - Payments									
		Yii::app()->db->createCommand()->insert("payments", array(
																 "fk_invoice_id"=>$invoice_id, 
																 "payment_date"=>new CDbExpression('NOW()'),
																 "payment_amount"=>($inv->invoice_total - $inv->setup_charges),
																 "ip_address"=>$_SERVER['REMOTE_ADDR'],
																 "created_by"=>"Cron")
											    );
		$inv_sub = 	Yii::app()->db->createCommand()
		 			->select("*")
					->from("invoice_subscriptions")
					->where("fk_invoice_id = ".$inv->pk_invoice_id)
					->queryAll();									
		foreach($inv_sub as $inSub)
		{
					
			Yii::app()->db->createCommand()->insert("invoice_subscriptions", array(
																 "fk_invoice_id"=>$invoice_id, 
																 "fk_subscription_id"=>$inSub['fk_subscription_id'],
																 "created"=>time())
											    );
		}	
		
		Yii::app()->db->createCommand()->update("invoices", array(
															'next_invoice_date'=>'Renewed',
 															),"pk_invoice_id=:invoiceID", array(":invoiceID"=>$inv->pk_invoice_id) );									
	}
}