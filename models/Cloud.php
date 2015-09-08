<?php

class Cloud extends CFormModel
{
	
   public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'customer_id' => 'CustomerID',
			'domain_alias' => 'Domain Alias',
			'username' => 'Username',
			'password' => 'Password',
			'subscription_id' => 'SubuscriptionID',
			' status' => 'Status',
			 
		);
	}
	
	 
	
 # 1. get all information of a cloud service 
 public function getService($service)
 {
	 $cloud = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($service)
								->queryAll();
								
	 $cloud_comp = Yii::app()->db->createCommand()
	   							->select("c.*, d.*")
	   						    ->from($service."_component AS c")
								->join($service."_discount_range AS d", "c.component_id = d.applied_to")
								->queryAll();	
	
	$cloud_discount_range = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($service."_discount_range")
								->queryAll();
								
	$cloud_array = array($service=>$cloud, $service."_component"=>$cloud_comp, $service."_discount_range"=>$cloud_discount_range);
	return $cloud_array;																											
 }	
	
	/*
   		*Custom Query for servers
   */
   public function getCustomServicesInfo($Customservice, $id){
	
		$price = 	 Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($Customservice)
								->where("id = '".$id."'")
								->queryRow();
								// echo '<pre>'; print_r($price); exit;
	$small = 	 Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($Customservice."_component")
								->where("feature_name = 'small'")
								->queryRow();
	$medium = 	 Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($Customservice."_component")
								->where("feature_name = 'medium'")
								->queryRow();
	// echo '<pre>'; print_r($medium); exit;
	$large = 	 Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($Customservice."_component")
								->where("feature_name = 'large'")
								->queryRow();	
	$xlarge = 	 Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($Customservice."_component")
								->where("feature_name = 'extralarge'")
								->queryRow();						
	// echo '<pre>'; print_r($result_3); exit;
	$custom_array = array($Customservice=>$price, "small"=>$small, "medium"=>$medium, "large"=>$large,"extra large"=>$xlarge);
	return $custom_array;
	
	}
	
 /*
 	Get service info
	@Param - Service Table name
	@Param - Package Type (Brown, Silver, Gold, Platinum) 
 */	 
   public function getServiceInfo( $tableName, $package_type = 'silver')
   {
	   $service = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($tableName)
								->where("package_type = '".$package_type."'")
								->queryRow();
		$service['tablename'] = $tableName;						
		return $service;						
   }
   
 /*  
   		Store shopped Item in Cart table
   
   public function saveInCart( $tableName, $packageSelect,$post)
   {
	   $quantity = $post['quantity']; if($quantity < 1) $quantity = 1;
	   # Generate a new key
	   if( !isset($_SESSION['Shop_key']) )
	   $_SESSION['Shop_key'] = md5(time());
	   Yii::app()->db->createCommand()->insert("cart", array("package_type"=>$packageSelect, "service_table"=>$tableName, 
	   "session_id"=>$_SESSION['Shop_key'],"quantity"=>$quantity ,"date_created"=>time())); 
     	return;
	}
*/	
	/*
 	Get service info
	@Param - Service Table name
  */	 
   public function getServiceTbName( $tableName)
   {
	   $service = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from($tableName)
 								->queryAll();
		$service['tablename'] = $tableName;						
		return $service;						
   }
   
   
  
	/* 
		# - Get Component information
		@Param - $category Name of the component , CPU, Ram etc
		@Param - $cloudService Service name, vdi, mailbox etc
	*/
	public function getComponentInfo($category, $cloudService)
	{
		$info = Yii::app()->db->createCommand()
							  ->select("*")
							  ->from($cloudService."_component")
							  ->where("category='".$category."'")
							 ->queryAll(); 
		$ret = array();				  
		foreach($info as $key=>$value) {
			
			$ret[$value['feature_name']] = $value;	
		}
		// echo '<pre>'; print_r($ret); exit;
		return $ret;
	}
	
	/* 
		# - Get Component information
		@Param - $category ID of the component , CPU, Ram etc
		@Param - $cloudService Service name, vdi, mailbox etc
	*/
	public function getComponentInfoById($category, $cloudService)
	{
		$info = Yii::app()->db->createCommand()
							  ->select("*")
							  ->from($cloudService."_component")
							  ->where("component_id='".$category."'")
							 ->queryAll(); 
		
		// echo '<pre>'; print_r($ret); exit;
		return $info;
	}
	
	/*
		* Return Array of All VDI  users of All subscriptions of a customer
	*/
	function getVdiUsers($userId = 0, $domainID = '')
	{
		if(strlen($domainID) != 0) { 
			//$domain = $this->getDomain4sById($domainID);
			$domain['domain_url'] = $domainID;
			Yii::app()->session['domain'] = $domain['domain_url'];
		} 
		
		$condition = '';
	    
		if($userId > 0) $condition .= " And id= '".$userId."'";
		else $condition .= " AND domain_alias LIKE '%".Yii::app()->session['domain']."%'";
		 	 
		$customer_id 	= Yii::app()->user->pk_customer_id;
		$parent_id 	= Yii::app()->user->parent_id;
		
		$vdiUsers = Yii::app()->db->createCommand()
		->select("*")
		->from("vdi_users")
		->where("(customer_id=".$customer_id ." or customer_id=".$parent_id.")" .$condition)
		->queryAll();
		return $vdiUsers;
	}
	
	/*
		# Get VDI Mailbox users
	*/
	function getVdiMailboxUsers($domain)
	{
		$subModel = new Subscriptions;
		$custModel = new Customers;
 		$sub_id = $this->getAllSubsIdByDomain("VDI");  // Comma separated 4554,3423
	 
		if(!empty($sub_id))
			Yii::app()->session['subs_id'] = $sub_id;
 		 
		$subDetails = $subModel->getSubscriptionByIDsStr($sub_id);
		
		 
		$domain_mailbox = ''; $mail_user_allow = 0;
		
		foreach($subDetails['components'] as $key=>$value) {
			if($value['component'] == "Mailbox Domain Name")
			{
			 
				$domain_mailbox = $value['component_value'];
				Yii::app()->session['vdiMailDomain'] = $domain_mailbox;
 			}
			if($value['component'] == "Number of Mailbox(s)")
			{
				  $mail_user_allow += $value['component_value'];
 			}
		}
		if( empty($domain_mailbox)) 
		return false;
		
	    # Select Vdi-mailbox users by subscriptionId + Domain  
		//$condition = " AND domain_alias LIKE '%".$domain_mailbox."%' AND subscription_id = ".Yii::app()->session['subs_id'];
		$condition = " AND domain_alias LIKE '%".$domain_mailbox."%' AND subscription_id in (".Yii::app()->session['subs_id'].")";	 
		$customer_id 	= Yii::app()->user->pk_customer_id;
		$parent_id 	= Yii::app()->user->parent_id;
		$allChild   = $custModel->getChildCustomers($parent_id);
		
		$mailboxUsers['list'] = Yii::app()->db->createCommand()
		->select("*")
		->from("mailbox_users")
		->where("customer_id IN (".$allChild.")" .$condition)
		->queryAll();
		
		$mailboxUsers['count']  = $mail_user_allow;
		return $mailboxUsers;
	}
	/*
		* Return Array of Mailbox users
	*/
	function getMailUsers($userId = 0, $domainID = '')
	{
	
		if(strlen($domainID) != 0) { 
			//$domain = $this->getDomainsById($domainID);
			$domain['domain_url'] = $domainID;
			Yii::app()->session['domain'] = $domain['domain_url'];
		} 
 		$condition = '';
		if($userId > 0) 
			$condition .= " And id= '".$userId."'";
	    else
			$condition = " AND domain_alias LIKE '%".Yii::app()->session['domain']."%'";
			
		$custModel = new Customers;
	   // get customer Primary key value   
		$customer_id = Yii::app()->user->pk_customer_id;
		$parent_id 	= Yii::app()->user->parent_id;
		$allRelatedCustomers = $custModel->getChildCustomers($parent_id);
		 
		$mailUsers = Yii::app()->db->createCommand()
					->select("*")
					->from("mailbox_users")
					->where("customer_id IN (".$allRelatedCustomers.")" .$condition)
					->queryAll();
		# Check is_free users
		$free = 0;
		foreach($mailUsers as $user)
		{
			if( $user['is_free'] == 1 )
			$free += 1;
		}			 
		Yii::app()->session['free_user'] = $free;
		return $mailUsers;
	}
	
	# =================================
	# - Send VDI/Mailbox users count
	# - @ Service Name 
	# =================================
	public function getServiceUserCount( $serviceName ) {
		
		$custModel = new Customers;
		$parent_id 	= Yii::app()->user->parent_id;
		$condition = '';
		if( $serviceName == "mailbox") $condition = "";
		$allRelatedCustomers = $custModel->getChildCustomers($parent_id);
		$count = Yii::app()->db->createCommand()
							   ->select("count(id) AS userCount")
							   ->from($serviceName."_users")
							   ->where("domain_alias LIKE '%".Yii::app()->session['domain']."%' ".$condition." 
							   AND (customer_id IN (".$allRelatedCustomers.") OR customer_id=".$parent_id.')')
							   ->queryRow();
							   
							   
		# Allowed users to be created:
		// $subId = $this->getSubsriptionIdByDomain($serviceName);
		$subId = $this->getAllSubsIdByDomain($serviceName);
		 
		$condition = ''; 
		 if($serviceName == "mailbox") 
		 { 
		 	$user_count_name  = "Number of mailbox";
			$user_count_name2 = 'Number of Mailbox(s)';
			 
		     
		 }
		 elseif($serviceName == "vdi") {  
		 	$user_count_name = "Number of VDI User(s)";
		 
		 }
		 if(!empty($subId)) $condition .= '  AND subscription_id IN ('.$subId.')';
		 
		 if($serviceName == "vdi") {
		 $SubscriptionInfo = Yii::app()->db->createCommand()
							   ->select("component_value")
							   ->from("subscription_customized")
							   ->where("component LIKE '%".$user_count_name."%' ".$condition)
							   ->queryAll();
		}
		if($serviceName == "mailbox") {
		
		 
		 $SubscriptionInfo = Yii::app()->db->createCommand()
							   ->select("component_value")
							   ->from("subscription_customized")
							   ->where("(component LIKE '%".$user_count_name."%' or component LIKE '%".$user_count_name2."%') ".$condition)
							   ->queryAll();
							   
							   
		$SubscriptionInfoVDI = Yii::app()->db->createCommand()
							   ->select("component_value")
							   ->from("subscription_customized")
							   ->where("(component LIKE '%".$user_count_name2."%') ".$condition)
							   ->queryAll(); 
			
		$vdiMails = 0;					   
		 foreach($SubscriptionInfoVDI as $subV)
		 {
		 	$vdiMails +=  $subV['component_value'];
		 } 
         Yii::app()->session['vdiMailboxUsers'] = $vdiMails;
		 
		 	 			    					   
 		}
		$count['total'] = 0;
		foreach($SubscriptionInfo as $subs)  {
			
			$count['total'] += $subs['component_value'];
		} 					   				   
		return $count;					   
	}
	
	# =================================
	# - Store vdi/mailbox user account
	# =================================
	public function saveUser($request, $service , $subs_id = 0)
	{
 		$command = Yii::app()->db->createCommand();
		if($request->getPost("free_backup_user") == 1)
		$free = 1;
		else
		$free = 0;
		$command->insert($service."_users", array("customer_id"=>Yii::app()->user->pk_customer_id, 
													"domain_alias"=>$request->getPost("domain_name"),
													"username"=>$request->getPost("username"),
													"password"=>rand(1000, 9999),
													"status"=>'2',
													"created"=>new CDbExpression('NOW()'),
													"subscription_id"=>$subs_id,
													"is_free"=>$free
													));
	}
	
	# ================================
	# Get Domains
	# ================================
	function getDomainsById( $domainID )
	{
		$domain = Yii::app()->db->createCommand()->select("*")->from("domains")->where("pk_domain_id=".$domainID)->queryRow();
		return $domain;
	}
	# ===================================
	# List of VDI domains 
	# ===================================
	function getCustomerVdiDomains()
	{
	    $customer_id = $custModel->getChildCustomers( Yii::app()->user->parent_id );
		
		$subModel = new Subscriptions;
		$subscription = $subModel->findAll("service_name = 'VDI' and fk_domain_id != 0 and fk_customer_id  IN (".$customer_id.")");
		$domains = array();
		foreach($subscription as $subs)
		{
			$domains[] = $this->getDomainsById( $subs );
		}
	}
	
	# ================================================
	# Get Customer Current Subscription by Domain Alias
	# ================================================
	function getSubsriptionIdByDomain($service = "VDI")
	{
		$custModel = new Customers;
		
	    $childs = $custModel->getChildCustomers( Yii::app()->user->parent_id );
		$domain = Yii::app()->session['domain'];
		$subId	= $domain = Yii::app()->db->createCommand()
							->select("s.pk_sub_id")
							->from("domains AS d")
							->leftjoin("subscriptions AS s", 'd.pk_domain_id = s.fk_domain_id')
							->where("d.domain_url='".$domain ."' AND s.service_name LIKE '%".$service."%' AND s.fk_customer_id IN (".$childs.")")
							->queryRow();
							
							//echo '<pre>'; print_r($subId); exit;
							
	  return $subId['pk_sub_id'];						
		
		
	}
	
	# ================================================
	# Get Customer Current Subscription by Domain Alias
	# ================================================
	function getAllSubsIdByDomain($service = "VDI")
	{
		$custModel = new Customers;
		$subId2 = array();
		$subId3 = array();
		$childs = $custModel->getChildCustomers( Yii::app()->user->parent_id );
		$domain = Yii::app()->session['domain'];
		$subId	=  Yii::app()->db->createCommand()
							->select("s.pk_sub_id")
							->from("domains AS d")
							->leftjoin("subscriptions AS s", 'd.pk_domain_id = s.fk_domain_id')
							->where("s.service_name LIKE '%".$service."%' AND s.fk_customer_id IN (".$childs.")
									AND d.domain_url = '".$domain."'")
							->queryAll();
		
	  if($service == "mailbox")
	  {
	     $domainIDs = Yii::app()->db->createCommand()->select('pk_domain_id')->from('domains')
		 				->where('domain_url LIKE "%'.Yii::app()->session['domain'].'%" AND fk_customer_id IN ('.$childs.') ')->queryAll();
		 $domainIDarr = array(); 
		 foreach($domainIDs as $did)
		 {
		 	$domainIDarr[] = $did['pk_domain_id'];
		 } 
		 $domainStr = implode(',', $domainIDarr);  
		 
		 if(!empty($domainStr))
 	  	 $subId2	=   Yii::app()->db->createCommand()
							->select("s.pk_sub_id")
							->from("domains AS d")
							->leftjoin("subscriptions AS s", 'd.pk_domain_id = s.fk_domain_id')
							->where("s.fk_customer_id IN (".$childs.") AND d.pk_domain_id IN (".$domainStr.")")
 							->queryAll();
		# Pick subscription ID from Customized subscription values
		 $subId3 = Yii::app()->db->createCommand()
		 				->select('s.pk_sub_id')
						->from('subscriptions AS s')
						->leftjoin('subscription_customized AS sc', 's.pk_sub_id = sc.subscription_id')
		 				->where('sc.component_value LIKE "%'.Yii::app()->session['domain'].'%" AND s.fk_customer_id IN ('.$childs.') ')
						->queryAll();
						
		  			
							 
	  }					
	  $subArray = array();
	  $subId =array_merge($subId, $subId2, $subId3);  
	  foreach($subId as $sub)
	  {
	  	$subArray[] = $sub['pk_sub_id'];
	  }	
							
	  return implode(",", array_unique($subArray));						
		
		
	}
	
}

?>   