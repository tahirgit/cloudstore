<?php 

/**
 * This is the model class for table "customers".
 *
 * The followings are the available columns in table 'customers':
 * @property integer $pk_customer_id
 * @property string $username
 * @property string $password
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property string $phone
 * @property string $fax
 * @property string $business_type
 * @property string $subscription_type
 * @property string $country
 * @property string $city
 * @property string $postal_code
 * @property string $street_address
 * @property string $company_name
 * @property string $company_registration_id
 * @property string $designation
 * @property string $role
 * @property string $vat_number
 * @property integer $parent_id
 *
 * The followings are the available model relations:
 * @property Invoices[] $invoices
 * @property Orders[] $orders
 * @property ServicesUsers[] $servicesUsers
 * @property Subscriptions[] $subscriptions
 */
class Customers extends CActiveRecord
{
	public $pk_customer_id;
	public $username;
	public $password;
	public $rememberMe;
	public $firstname;
	private $_identity;
	public $password1;
	public $password2;
	/**
	
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Customers the static model class
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
		return 'customers';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() 
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array( 
			 
			array('username, password, firstname, lastname, email, phone, business_type, subscription_type, country, county, city, postal_code,  company_name, company_registration_id, designation, role, vat_number, parent_id', 'required','on'=>'create'),
			
			 
			 // Add New Contact 
			array('firstname, lastname, email,   phone,  country, county, city, postal_code',  'required','on'=>'edit_contact'), 
				
			// For Profile update
			array('email, secondary_email', 'email'),
			// Email ALready Exists
			array('email', 'unique','message'=>'Email already exists!', 'on'=>'add_contact'),  
			// username already exists
			array('username', 'unique','message'=>'Username already exists!', 'on'=>'add_contact' ), 
			  
			array('phone, fax', 'numerical'),
			array('firstname, lastname, email, city, county, country, postal_code, phone', 'required','on'=>'update'),
			
			array('firstname, lastname, email, city, county, country, postal_code, phone, security_answer', 'required','on'=>'answer_required'),
			// Billing index
			array('company_name,  company_registration_id, vat_number', 'required','on'=>'billing'),
			
			
				// Email ALready Exists
			array('email', 'unique','message'=>'Email already exists!', 'on'=>'register'),  
			// username already exists
			array('username', 'unique','message'=>'Username already exists!', 'on'=>'register' ), 
			 
			 // Add New Contact 
			array('username, password, firstname, lastname, email, phone,  country, county, city, postal_code',  'required','on'=>'add_contact'),
			// Register
			array('firstname, lastname, username, password, email, county, city, postal_code, country, phone', 'required','on'=>'register'),


			array('parent_id', 'numerical', 'integerOnly'=>true),
			array('username, password, firstname, lastname, company_name, company_registration_id, designation, vat_number', 'length', 'max'=>30),
			array('email', 'length', 'max'=>40),
			array('phone, fax, business_type, subscription_type, country, city, role', 'length', 'max'=>20),
			array('postal_code', 'length', 'max'=>15),
			array('street_address', 'length', 'max'=>100),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('pk_customer_id, username, password, firstname, lastname, email, phone, fax, business_type, subscription_type, country, city, postal_code, street_address, company_name, company_registration_id, designation, role, vat_number, parent_id', 'safe', 'on'=>'search'),
			
			 
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
			'invoices' => array(self::HAS_MANY, 'Invoices', 'fk_customer_id'),
			'orders' => array(self::HAS_MANY, 'Orders', 'fk_customer_id'),
			'servicesUsers' => array(self::HAS_MANY, 'ServicesUsers', 'fk_customer_id'),
			'subscriptions' => array(self::HAS_MANY, 'Subscriptions', 'fk_customer_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'pk_customer_id' => 'Pk Customer',
			'username' => 'Username',
			'password' => 'Password',
			'title' => 'Title',
			'firstname' => 'Forename',
			'lastname' => 'Surname',
			'email' => 'Primary Email Address',
			'secondary_email'=>'Secondary Email Address',
			'phone' => 'Telephone',
			'fax' => 'Fax',
			'business_type' => 'Business Type',
			'subscription_type' => 'Subscription Type',
			'country' => 'Country',
			'city' => 'City',
			'postal_code' => 'Postal Code',
			'county'=>'County/State',
			'street_address' => 'Street Address',
			'company_name' => 'Company Name',
			'company_registration_id' => 'Company Registration',
			'designation' => 'Designation',
			'role' => 'Role',
			'vat_number' => 'Vat Number',
			'parent_id' => 'Parent',
			'security_question' => 'Security Question',
			'security_answer' => 'Security Answer',
			'send_overdue_invoices' => 'Send Overdue Invoices',
			'send_ue_invoices' => 'Send Due Invoices',
			'send_payment_invoices' => 'Send Payment Invoices',
			'is_billing'=>'Billing',
			'is_technical'=>'is_technical',
			'is_emergency_contact'=>'is_emergency_contact',
			 
		 
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

		$criteria->compare('pk_customer_id',$this->pk_customer_id);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('fax',$this->fax,true);
		$criteria->compare('business_type',$this->business_type,true);
		$criteria->compare('subscription_type',$this->subscription_type,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('postal_code',$this->postal_code,true);
		$criteria->compare('street_address',$this->street_address,true);
		$criteria->compare('company_name',$this->company_name,true);
		$criteria->compare('company_registration_id',$this->company_registration_id,true);
		$criteria->compare('designation',$this->designation,true);
		$criteria->compare('role',$this->role,true);
		$criteria->compare('vat_number',$this->vat_number,true);
		$criteria->compare('parent_id',$this->parent_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	*/
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			if(!$this->_identity->authenticate())
				$this->addError('password','Incorrect username or password.');
		}
	}
	
	public function changePassword( $newPass, $email )
	{
		$customer = $this->find("email = '".$email."'");
	   
		if( isset($customer->pk_customer_id) ) {
			$flag = Yii::app()->db->createCommand("UPDATE customers SET password = '".$newPass."' WHERE email = '".$email."'")->execute();
			return $customer;
		}
		else
		{
			return false;
		}
	}

/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{ 
		 
		if($this->_identity===null)
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			$this->_identity->authenticate();
		}
		if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
		{
			$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
			Yii::app()->user->login($this->_identity,$duration);
 			   return true;
		}
		else
			return false;
	}
	
	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function storelogin()
	{ 
		 
		$userRecord	=	Customers::model()->find('username="'.$this->username.'" AND password = "'.md5($this->password).'"');
			if( count($userRecord) )
			{
				Yii::app()->session['username'] = $this->username;
				return true;
			}
		else
			return false;
	}
	
	/* 
		Customer info and domains array 
	*/
	function getCustomerInfo()
	{
		 
		$row =  Yii::app()->db->createCommand()
				->select('*')
				->from('customers')
				->where('username=:id', array(':id'=>Yii::app()->user->id))
				->queryRow();
		 $row['customer_domain'] = $this->getCustomerDomains($row['pk_customer_id']);
	 
		 return $row;
	}
	/*
		Return customer Domains 
		@Param - $id Customer Primary ID
	*/
	function getCustomerDomains($id) 
	{
		$domain = Yii::app()->db->createCommand()
				->select('*')
				->from('domains')
				->where('fk_customer_id=:id AND status=:status', array(':id'=>$id, ":status"=>"1"))
				->queryAll();
		return $domain;		
	}
	
	/*
		Return current Parent user Billing, Tech and Emergency contact Customer
		@param - customer ID
	*/
	public function getAllRelatedCustomers( $customer_id , $parent_id = 0)
	{
		$condition = '';
		 
		if($parent_id > 0)
			$condition =  ' OR pk_customer_id = '.$parent_id.' OR parent_id = '.$parent_id;
		else
		 $parent_id = Yii::app()->user->pk_customer_id;
		 	
		$customers = Yii::app()->db->createCommand()
			->select('*')
			->from('customers')
			->where('pk_customer_id='.$customer_id.' OR parent_id = '.$customer_id.$condition)
			->queryAll();
				
		$relatedCustomers = array();
		$supportCustomers = array();
		
		/* Support Customers Role */
		$supportCustomers['billing_customer'] = $customer_id;
		$supportCustomers['technical_customer'] = $customer_id;
		$supportCustomers['emergency_contact_customer'] = $parent_id;
		
		/* Build Arrays for Customers and for their Roles*/
		foreach($customers as $cus)
		{
  			$relatedCustomers[$cus['pk_customer_id']] = $cus['firstname'] .' '.$cus['lastname'];
			
			if( $cus['is_billing'] == '1')
			$supportCustomers['billing_customer'] = $cus['pk_customer_id'];	
			
			if( $cus['is_technical'] == '1')
			$supportCustomers['technical_customer'] = $cus['pk_customer_id'];	
			
			if( $cus['is_emergency_contact'] == '1')
			$supportCustomers['emergency_contact_customer'] = $cus['pk_customer_id'];	
			 
		} 
		 
	
		$array[] = $relatedCustomers;
		$array[] = $supportCustomers;
		return $array;
			
	}
	
	public function saveContact($post)
	{
		/*  Updating Technical and billing */
		$customer_id = Yii::app()->user->pk_customer_id;
		$related_customer = $this->getAllRelatedCustomers( $customer_id );
 		foreach($related_customer[0] as $key=>$value) {
 			/*if($post['is_billing'] == $key)
 				$this->updateByPk($key, array('is_billing'=>'1'));
			else
				$this->updateByPk($key, array('is_billing'=>'0'));
						
			if($post['is_technical'] == $key)	
				$this->updateByPk($key, array('is_technical'=>'1'));
			else
				$this->updateByPk($key, array('is_technical'=>'0'));*/
						
			if( $post['is_emergency_contact'] == $key )		
				$this->updateByPk($key, array('is_emergency_contact'=>'1'));
			else
				$this->updateByPk($key, array('is_emergency_contact'=>'0'));	
		}
	}
	
	public function saveRegister($post)
	{
		 
		$parent_id = 0;
		$role = "Admin"; $is_technical = 0; $is_billing = 0;
		if(isset($post['role']))
		{
			$role = $post['role'];
			if($role == 'tech') $is_technical = 1;
 			elseif($role == 'billing') $is_billing = '1';
			
		}
 		 
		$username = $post['username'];
		$password = $post['password'];
			
		if(isset(Yii::app()->user->pk_customer_id))	
		$parent_id = Yii::app()->user->pk_customer_id;
		
 		$command = Yii::app()->db->createCommand();
		$command->insert("customers", array('username'=>$username, 'password'=>md5($password), "title"=>$post['title'], 
		'firstname'=>$post['firstname'], 'lastname'=>$post['lastname'],
											'email'=>$post['email'], 'secondary_email'=>$post['secondary_email'], 'phone'=>$post['phone'],
											'city'=>$post['city'], 'fax'=>$post['fax'], 'street_address'=>$post['street_address'],
											'county'=>$post['county'], 'postal_code'=>$post['postal_code'], 'country'=>$post['country'],
 											'parent_id'=>$parent_id, "role"=>$role, 'is_billing'=>$is_billing, 
											'is_technical'=>$is_technical, 'send_overdue_invoices'=>1,  'send_due_invoices'=>1,
											'send_payment_invoices'=>1)
		);
		
		$custId = Yii::app()->db->getLastInsertId();
		// Insert In billing
		 
		$command->insert("billings", array('fk_customer_id'=>$custId, 'billing_created'=>md5($password), "billing_email"=>$post['email'], 
		'billing_phone'=>$post['phone'], 'billing_fax'=>$post['fax'],
											'billing_address'=>$post['street_address'], 'billing_city'=>$post['city'], 'billing_postal_code'=>$post['postal_code'],
											'billing_county'=>$post['county'], 'billing_country'=>$post['country'], "billing_email"=>$post['email'])
		);
		
		if( !isset(Yii::app()->user->pk_customer_id))
		{
		 	$this->username = $username;
		 	$this->password = $password;
		 	$this->login();
		}
		return ;
	}
 	 
	/*
		* Get child ids string
	*/ 
	
	public function getChildCustomers($parent_id)
	{ 
		$arr = array();
		if($parent_id > 0) {
			$childs = $this->findAll("parent_id = ".$parent_id);
			foreach($childs as $child)
			{
				$arr[] = $child->pk_customer_id;
			}
			$arr[] = $child->parent_id;
		}
		$currentChild = $this->findAll("parent_id = ".Yii::app()->user->pk_customer_id);
		foreach($currentChild as $child2)
		{
			$arr[] = $child2->pk_customer_id;
		}
		
		$arr[] = Yii::app()->user->pk_customer_id;
		$strChild = implode(",", $arr);
		
		 
		return $strChild;
	}
	// ====================   Admin Functions ======================= //
	 
}