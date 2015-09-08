<?php

class CloudController extends Controller
{
/* --> TICKET PRIORITY */
	public $possible_priority = array(
										 
										1 => 'LOW',
										2 => 'MEDIUM',
										3 => 'HIGH',
										4 => 'URGENT',
							    );
								
	public $Status = array("0"=>"Block", "1"=>"Active", '2'=>'Request Pending');
	
	
	public function filters()
    {
        return array(
            'accessControl'           // required to enable accessRules
        );
    }
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		
		$arr = array();
		if( isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "admin" ) # Admin Role
			{
				$arr = array('paymentForm','vdiUsers','index', 'vdi', 'mailbox', 'modifyUser', 'modifyVdiUser', 'mailUsers', 
				'mailboxUsers', 'modifyMailUser', 'newVdiUser', 'newMailUser', 'excelFileUpload', 'newVdiMailboxUser','modifyVdiMailUser');
 			}
 			elseif(isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "billing") # Billing Role
			{
 				$arr = array('paymentForm','vdiUsers','index', 'vdi', 'mailbox', 'mailboxUsers', 'excelFileUpload', 'mailUsers');
 			}
		  elseif(isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "technical") # Technical Role
			{ 
				$arr = array('paymentForm','vdiUsers','index', 'vdi', 'mailbox', 'modifyUser', 'modifyVdiUser', 'vdiUsers','mailUsers',  
				'mailboxUsers', 'modifyMailUser', 'newVdiUser', 'newMailUser', 'excelFileUpload', 'newVdiMailboxUser','modifyVdiMailUser');
			}
		 elseif(isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "billing-technical") 
			{
				$arr = array('paymentForm','vdiUsers','index', 'vdi', 'mailbox', 'modifyUser', 'modifyVdiUser', 'mailUsers', 
				'mailboxUsers', 'modifyMailUser', 'newVdiUser', 'newMailUser', 'excelFileUpload','newVdiMailboxUser','modifyVdiMailUser');
			}
		
		
		return array(
		 
			array('allow',  // Allow all users
 			   'actions'=>array('checkoutLogin', 'register'),
 				'users'=>array('*'),
			),
			array('allow', // allow authenticated users to access all actions
				'actions'=>$arr,
				'users'=>array('@'),
				),
				array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$model = new Tickets;
		$subscriptions = new Subscriptions;
		$subs = $subscriptions->getCustomerSubscriptions(); 
		 	
		# Find Customer Subscriptions
		$services = array();
		$mailboxDomain = array();
		$vdiDomain = array();
		 
		foreach($subs as $key=>$value) {
		
 		    $subscriptionDetails = $subscriptions->getSubscriptionByID($value['pk_sub_id']);
			//echo '<pre>'; print_r($subscriptionDetails['components']);  
			if(in_array("Server", $value)) $services[] 	= "Server"; 
			if(in_array("CPanel", $value)) $services[] 	= "cPanel"; 
			if(in_array("VDI", $value)) 
			{ 	
			  $services[]  = "VDI";
			  if(!empty($value['domain_url'])) 
 			  $vdiDomain[$value['domain_url']] = $value['domain_url']; 	
			  foreach($subscriptionDetails['components'] as $keyCustom=>$custom )
				{
				 
					if($custom['component'] == "Mailbox Domain Name")
					$vdiDomain[$value['domain_url'].','.$custom['component_value']] = $custom['component_value']; 
					
				}
			}
			if(in_array("VOIP", $value))$services[] 	= "VOIP";
			if(in_array("VDC", $value))$services[] 		= "VDC";
			if(in_array("Data Backup", $value))$services[] = "Data Backup";
			if(in_array("Mailbox", $value)){ $services[] 	= "Mailbox";
			if(!empty($value['domain_url']))  
				$mailboxDomain[$value['domain_url']] = $value['domain_url']; 
				
			}
			
		}
		  
 		$this->render('index', array('model'=>$model, 'services'=>$services, "mailboxDomain"=>$mailboxDomain, "vdiDomain"=>$vdiDomain));
	}
	/*
		* Cloud VDI service listing
	*/
	public function actionVdi()
	{
		if( isset($_POST['domain_id']) )
		{
			 $session=new CHttpSession;
  			 $session->open();
  			 $session['vdi_domain'] = $_POST['domain_id'];   
		}
		
	}
	
  /* =================================
		* Cloud VDI service listing
     ================================= */
	public function actionMailbox()
	{
		if( isset($_POST['domain_id']) )
		{
   			 $_SESSION['mailbox_domain'] = $_POST['domain_id'];   
		}
		
		if( isset($_SESSION['mailbox_domain']) )
		{
			$model = new Subscriptions;
			$mailUsers = $model->getMailboxUsers( $_SESSION['mailbox_domain'] ); 
			$this->render("mailbox", array("mailUsers"=>$mailUsers));
		}
	}
	
 /* ======================================
		Modify user form and request form
	====================================== */
	public function actionModifyuser()
	{
		$userId = Yii::app()->request->getParam("id", "default");
		$model = new Subscriptions;
		$userInfo = $model->getUserById( $userId );
		$this->render("modifyuser", array("userInfo"=>$userInfo)); 
 	}
  
  
  /* ================================
  	  checkout Login form
     ================================ */
   public function actionCheckoutLogin()
   {
   	   if( isset($_SESSION['invoice_id']) ) unset($_SESSION['invoice_id']);
	   
	   if( isset(Yii::app()->user->pk_customer_id) && Yii::app()->user->pk_customer_id > 0)
	   $this->redirect(array('cloud/paymentForm'));
	   
	   $this->layout = "store";
	   $model=new Customers;
	  // $this->render("logincheckout", array("model"=>$model));
	    $this->render("checkoutNow", array("model"=>$model));
	   // collect user input data
		if(isset($_POST['Customers']))
		{
			$model->attributes=$_POST['Customers'];
			// validate user input and redirect to the previous page if valid
			 
			if($model->login()) {
				
				//if( isset($_SESSION['Shop_key']) ) {
				$this->redirect(array('cloud/paymentForm'));
 			}
			else
				Yii::app()->user->setFlash('error', "Login has failed. Incorrect login/password!");	
		}
		// Forget Password case
		if(isset($_POST['emailForPass']) && strlen($_POST['emailForPass']) > 5)
		{
			if( $this->forgetPassword($_POST['emailForPass']) )
			{
				Yii::app()->user->setFlash('success', "Password reset link has been sent to your email. Please check your email!");	
			}
		}
   }
   
   // Show payment form with Total Amount and Cart Items
   public function actionPaymentForm()
   {
	   $model = new Store;
	   $this->layout = "payment";
	   $cartItem = $model->getAllCartItem();
	   
	   if(  !isset($_SESSION['invoice_id']) or empty($_SESSION['invoice_id'])) {
		   # Create invoice for the subscriptions
			$invoiceModel  = new Invoices;
			$invoice_id    = $invoiceModel->createCartItemInvoice();
 			$_SESSION['invoice_id'] = $invoice_id;
	   }
	   
	   if( is_array($cartItem) )
	   $this->render("paymentForm", array("cartItems"=>$cartItem));
	   else
	   $this->redirect(array("customer/index"));
   }
   
   public function actionVdiUsers()
   {
	   $model = new Cloud;
	   $domainID = Yii::app()->request->getPost('vdiDomainId');
 	   
 	   $vdiUsers = $model->getVdiUsers(0, $domainID);
	   $userCount = $model->getServiceUserCount( "vdi" );
 	     
	   $vdi_mailbox = $model->getVdiMailboxUsers($domainID);
	   
 	   $this->render("vdiusers", array("users"=>$vdiUsers, "userCount"=>$userCount['userCount'], 'totalCount'=>$userCount['total'],"vdi_mailbox"=>$vdi_mailbox));
   }
   
   /* ================================
   		 Modify VDI user
      ================================ */
   public function actionModifyVdiUser()
   {
	   $userId = Yii::app()->request->getParam("id", "0");
	   $model = New Cloud;
	   $userInfo = $model->getVdiUsers($userId);
 	   if( isset($_POST['request_type']) )
	   {
		   # Setting remaining Important Fields
		    $request = Yii::app()->request;
			$ticketModel = new Tickets;
			$departments = $ticketModel->getAllDepartments();  
			#- Variable Assignment to Save in Trelis DAtabase
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->mname		   		=  Yii::app()->user->id; 
			$ticketModel->email		   		=  Yii::app()->user->customer_email;
			$ticketModel->ipadd		   		=  $_SERVER['REMOTE_ADDR'];
 			$ticketModel->date		   		=  time();
			$ticketModel->did            	=  $request->getPost('departments');
			$ticketModel->dname         	=  $departments[$ticketModel->did];
			
			$ticketModel->message 			=  'VDI Username: '.$request->getPost('username').'<br>'.$request->getPost('request_detail'); 
			$ticketModel->subject			=  'VDI: '.$request->getPost('request_type').' Request'; 
			
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->priority 			=  4; // Urgent Ticket
			# validate user input and redirect to the previous page if valid
			if($ticketModel->validate() && $ticketModel->save()) 
			{
				# Create customer in Members Table of the Trellis database as well
				$ticketModel->saveInTrellis();
				$ticketModel->updateMemId(); // Update Member ID in Tickets
				$ticketModel->sendEmail('support@centrica-it.com',$ticketModel->subject, $ticketModel->message,"Support");
				Yii::app()->user->setFlash('success', "Your Request has been placed successfully. We will contact you shortly");	
  				$this->redirect(array("cloud/vdiUsers")); 
 			}
		   
	   }
	   
	   $this->render("modifyVdiUser", array("user"=>$userInfo));
   }
   
 
   
   /* =============================
   		Mailbox Users view
   	  ============================= */
   public function actionMailUsers()
   {
	    $model = new Cloud;
		 $domainID = Yii::app()->request->getPost('mailboxDomainId');
 	    $mailUsers = $model->getMailUsers(0, $domainID);
		$userCount = $model->getServiceUserCount( "mailbox" );
 	    $this->render("mailUsers", array("users"=>$mailUsers, "userCount"=>$userCount['userCount'], 'totalCount'=>$userCount['total']));
   }
   
   /* =================================
    	Modify Mailbox users
      ================================= */
   public function actionModifyMailUser()
   {
	   $userId = Yii::app()->request->getParam("id", "0");
	   $model = New Cloud;
	   $userInfo = $model->getMailUsers($userId);
 	   if( isset($_POST['request_type']) )
	   {
		   # Setting remaining Important Fields
		    $request = Yii::app()->request;
			$ticketModel = new Tickets;
			$departments = $ticketModel->getAllDepartments();  
			#- Variable Assignment to Save in Trelis DAtabase
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->mname		   		=  Yii::app()->user->id; 
			$ticketModel->email		   		=  Yii::app()->user->customer_email;
			$ticketModel->ipadd		   		=  $_SERVER['REMOTE_ADDR'];
 			$ticketModel->date		   		=  time();
			$ticketModel->did            	=  $request->getPost('departments');
			$ticketModel->dname         	=  $departments[$ticketModel->did];
			$ticketModel->message 			=  'Mailbox Username: '.$request->getPost('username').'<br>'.$request->getPost('request_detail'); 
			$ticketModel->subject			=  'Mailbox: '.$request->getPost('request_type').' Request';
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->priority 			=  4; // Urgent Ticket
			# validate user input and redirect to the previous page if valid
			if($ticketModel->validate() && $ticketModel->save()) 
			{
				# Create customer in Members Table of the Trellis database as well
				$ticketModel->saveInTrellis();
				$ticketModel->updateMemId(); // Update Member ID in Tickets
				
				$ticketModel->sendEmail('',$ticketModel->subject, $ticketModel->message);
				Yii::app()->user->setFlash('success', "Your Request has been placed successfully. We will contact you shortly");	
  				$this->redirect(array("cloud/mailUsers")); 
 			}
		   
	   }
	   $this->render("modifyMailUser", array("user"=>$userInfo)); 
   }
   
   
   /* ===========================================
   		Add New VDI user Request 
      =========================================== */
   public function actionNewVdiUser()
   {
	   
	   $model = New Cloud;
	   $customerModel = new Customers;
 	   if( isset($_POST['username']) )
	   {
		   # Setting remaining Important Fields
		    $request = Yii::app()->request;
			$ticketModel = new Tickets;
			$departments = $ticketModel->getAllDepartments();  
			#- Variable Assignment to Save in Trelis DAtabase
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->mname		   		=  Yii::app()->user->id; 
			$ticketModel->email		   		=  Yii::app()->user->customer_email;
			$ticketModel->ipadd		   		=  $_SERVER['REMOTE_ADDR'];
 			$ticketModel->date		   		=  time();
			$ticketModel->did            	=  $request->getPost('departments');
			$ticketModel->dname         	=  $departments[$ticketModel->did];
			$ticketModel->message 			=  'Username: '.$request->getPost('username').'<br>
												Password: '.$request->getPost('password').
												"<br>Domain: ".$request->getPost("domain_name"); 		   
														
			$ticketModel->subject			=  "Create New VDI User"; 
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->priority 			=  4; // Urgent Ticket
			# validate user input and redirect to the previous page if valid
			
			# - Create Users in database in Pending
			$model->saveUser($request, "vdi");
			
			if($ticketModel->validate() && $ticketModel->save()) 
			{
				# Create customer in Members Table of the Trellis database as well
				$ticketModel->saveInTrellis();
				$ticketModel->updateMemId(); // Update Member ID in Tickets
				
				# ================== EMAIL Section ========================== 
				
				# 2 To creator
				$message_creator = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
				Please login to <a href="https://mycloud.centrica-it.com/">customer panel</a> for 
									future correspondence. Ticket details are as follows:<br>
									<p> Ticket ID: '.$ticketModel->id.'<br>
										Priority: '.$this->possible_priority[$ticketModel->priority].'<br>
										Created: '.date("M d, Y", $ticketModel->date).'<br>
										Subject: '.$ticketModel->subject.'<br>
										Message: '.$ticketModel->message.'<br>
 									</p>';
				$message_support = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
									Ticket details are as follows:<br>
									<p> Ticket ID: '.$ticketModel->id.'<br>
										Priority: '.$this->possible_priority[$ticketModel->priority].'<br>
										Created: '.date("M d, Y", $ticketModel->date).'<br>
										Subject: '.$ticketModel->subject.'<br>
										Message: '.$ticketModel->message.'<br>
 									</p>';
													
 				# 1 Send Email to Support
				$ticketModel->sendEmail("support@centrica-it.com", $ticketModel->subject, $message_support, "Support");
				
				# 2 To creator
				$ticketModel->sendEmail( Yii::app()->user->customer_email, $ticketModel->subject, $message_creator, "Customer" );
				
				# 3 Email to Parent Customer
 				
				if( Yii::app()->user->parent_id  > 0 )
				{
					$ParentInfo  = $customerModel->find("pk_customer_id = ".Yii::app()->user->parent_id ); 
 					$ticketModel->sendEmail( $ParentInfo->email, $ticketModel->subject, $message_creator,  "Customer");
				}
				# ===========================================================
				 
				Yii::app()->user->setFlash('success', "Your Request has been placed successfully. We will contact you shortly");	
  				$this->redirect(array("cloud/vdiUsers")); 
 			}
			
			
		   
	   }
	   $this->render("newVdiUser");
   }
    
/* ====================================
   	Add New Mailbox user Request 
   ==================================== */
   public function actionNewMailUser()
   {
	   
	   $model = New Cloud;
	   $customerModel = new Customers;
	  
 	   if( isset($_POST['username']) )
	   {
		   # Setting remaining Important Fields
		    $request = Yii::app()->request;
			$ticketModel = new Tickets;
			$departments = $ticketModel->getAllDepartments();  
			#- Variable Assignment to Save in Trelis DAtabase
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->mname		   		=  Yii::app()->user->id; 
			$ticketModel->email		   		=  Yii::app()->user->customer_email;
			$ticketModel->ipadd		   		=  $_SERVER['REMOTE_ADDR'];
 			$ticketModel->date		   		=  time();
			$ticketModel->did            	=  $request->getPost('departments');
			$ticketModel->dname         	=  $departments[$ticketModel->did];
			$ticketModel->message 			=  'Username: '.$request->getPost('username').'<br>
												Password: '.$request->getPost('password').'
												<br>Domain: '.$request->getPost("domain_name"); 
			if( isset($_POST['default_quota']))$ticketModel->message .= "<br> Default Quota: ".$_POST['default_quota'];											   
			if( isset($_POST['email_forward'])) { $ticketModel->message .= "<br> Email Forwarding To: ".$_POST['email_forward'];}
														
			$ticketModel->subject			=  "Create New Mailbox User"; 
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->priority 			=  4; // Urgent Ticket
			
			# - Create Users in database in Pending
			$model->saveUser($request, "mailbox");
			
			# validate user input and redirect to the previous page if valid
			if($ticketModel->validate() && $ticketModel->save()) 
			{
				# Create customer in Members Table of the Trellis database as well
				$ticketModel->saveInTrellis();
				$ticketModel->updateMemId(); // Update Member ID in Tickets
				# ================== EMAIL Section ========================== 
				
				# 2 To creator
				$message_creator = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
				Please login to <a href="https://mycloud.centrica-it.com/">customer panel</a> for 
									future correspondence. Ticket details are as follows:<br>
									<p> Ticket ID: '.$ticketModel->id.'<br>
										Priority: '.$this->possible_priority[$ticketModel->priority].'<br>
										Created: '.date("M d, Y", $ticketModel->date).'<br>
										Subject: '.$ticketModel->subject.'<br>
										Message: '.$ticketModel->message.'<br>
 									</p>';
				$message_support = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
									Ticket details are as follows:<br>
									<p> Ticket ID: '.$ticketModel->id.'<br>
										Priority: '.$this->possible_priority[$ticketModel->priority].'<br>
										Created: '.date("M d, Y", $ticketModel->date).'<br>
										Subject: '.$ticketModel->subject.'<br>
										Message: '.$ticketModel->message.'<br>
 									</p>';
													
 				# 1 Send Email to Support
				$ticketModel->sendEmail("support@centrica-it.com", $ticketModel->subject, $message_support, "Support");
				
				# 2 To creator
				$ticketModel->sendEmail( Yii::app()->user->customer_email, $ticketModel->subject, $message_creator, "Customer" );
				
				# 3 Email to Parent Customer
 				
				if( Yii::app()->user->parent_id  > 0 )
				{
					$ParentInfo  = $customerModel->find("pk_customer_id = ".Yii::app()->user->parent_id ); 
 					$ticketModel->sendEmail( $ParentInfo->email, $ticketModel->subject, $message_creator,  "Customer");
				}
				# ===========================================================
				
				Yii::app()->user->setFlash('success', "Your Request has been placed successfully. We will contact you shortly");	
  				$this->redirect(array("cloud/mailUsers")); 
 			}
		   
	   }
	   $this->render("newMailUser");
   }
    
	
	// Upload Excel File for Multiple user request for VDI. Mailbox
	public function actionExcelFileUpload()
	{
		$service = Yii::app()->request->getParam("service", "-");
		 $this->render("excelFileUpload", array("service"=>$service));
	}
	
	
	/*
		customer Initial Registration
	*/
	function actionRegister()
	{
		$this->layout = "store";
		$model = new Customers;
		
		if(isset($_POST['Customers']))
		{
 			 $model->scenario = 'register'; // Use Rules that are set for update
 			 $model->attributes=$_POST['Customers'];
			// Save info
			if( $model->validate() )
			{
			 	$model->saveRegister($_POST['Customers']);
				
				// Send Confirmation Email
				$this->registerSendEmail($_POST['Customers']);
    		    $this->redirect(array("cloud/paymentForm")); 
			}
			else
			
			{
				//echo '<pre>'; print_r($model->getErrors()); 
				 $this->render("checkoutNow", array("model"=>$model,"registrationError"=>1) );
				 
			}
		}
		else
		 $this->render("checkoutNow", array("model"=>$model) );
				 
	}
	
	public function registerSendEmail($post)
	{
 		  # Email Template 
		  if( !isset($post['role']) ) $post['role'] = "Admin";
		  $message = "
		 			<p> We appreciate your interest in Centrica IT. You have successfully been registered with us now.</p>
					<p> Your Login Credentials are as follows. You can login 
					<a href='https://mycloud.centrica-it.com/' target='_blank'> here</a>.</p>
					<p> Username: ".$post['username'] ."<br>
						Password: ".$post['password'].
					"<br>	Role:".$post['role']." </p>";
						
		 			
						
		// $template = $this->renderPartial("mail_register", null, true);
		 $template = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html");
		 $template = str_replace("#from_name#", $post['firstname'] .' '.$post['lastname'], $template);  
		 $template = str_replace("#message#", $message, $template);
		 
		 
		 $name='=?UTF-8?B?'.base64_encode("Centrica IT").'?=';
		 $subject='=?UTF-8?B?'.base64_encode("Welcome To Centrica IT").'?=';
		 $headers="From: $name <support@centrica-it.com>\r\n".
					"Reply-To: support@centrica-it.com\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/html; charset=UTF-8";
 		 @mail($post['email'], $subject, $template, $headers, "-f support@centrica-it.com");
 		 
		 # -  Email Send to support Centrica
		 $message2 = '	<p> A new user has been registered with the following info:</p>
		 				<p> Full Name: '.$post['firstname'] .' '.$post['lastname'].'</p>
						<p> Username: '.$post['username'].'</p>
						<p> Email: '.$post['email'].'</p>
						<p> Telephone: '.$post['phone'].'
						<br>	Role:'.$post['role'].'</p>
						'; 
						/* Password: ".$post['password']*/	
		 $template2 = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html");
		 $template2 = str_replace("#from_name#", "Support", $template2);  
		 $template2 = str_replace("#message#", $message2, $template2);
 		 @mail("support@centrica-it.com", "New User Registered Successfully", $template2, $headers, "-f support@centrica-it.com");
	}
	
	
	/*
		Function to Create new Mailbox user in VDI
	*/
	function actionNewVdiMailboxUser()
	{
 	   $model = New Cloud;
	  $customerModel = new Customers;
 	   if( isset($_POST['username']) )
	   {
		   # Setting remaining Important Fields
		    $request = Yii::app()->request;
			$ticketModel = new Tickets;
			$departments = $ticketModel->getAllDepartments();  
			#- Variable Assignment to Save in Trelis DAtabase
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->mname		   		=  Yii::app()->user->id; 
			$ticketModel->email		   		=  Yii::app()->user->customer_email;
			$ticketModel->ipadd		   		=  $_SERVER['REMOTE_ADDR'];
 			$ticketModel->date		   		=  time();
			$ticketModel->did            	=  $request->getPost('departments');
			$ticketModel->dname         	=  $departments[$ticketModel->did];
			$ticketModel->message 			=  'Username: '.$request->getPost('username').'<br>
												Password: '.$request->getPost('password').'
												<br>Domain: '.$request->getPost("domain_name"); 
			if( isset($_POST['default_quota']))$ticketModel->message .= "<br> Default Quota: ".$_POST['default_quota'];											   
			if( isset($_POST['email_forward'])) { $ticketModel->message .= "<br> Email Forwarding To: ".$_POST['email_forward'];}
														
			$ticketModel->subject			=  "Create New Mailbox User"; 
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->priority 			=  4; // Urgent Ticket
			
			# - Create Users in database in Pending
			$model->saveUser($request, "mailbox", Yii::app()->session['subs_id']);
			
			# validate user input and redirect to the previous page if valid
			if($ticketModel->validate() && $ticketModel->save()) 
			{
				# Create customer in Members Table of the Trellis database as well
				$ticketModel->saveInTrellis();
				$ticketModel->updateMemId(); // Update Member ID in Tickets
				# ================== EMAIL Section ========================== 
				
				# 2 To creator
				$message_creator = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
				Please login to <a href="https://mycloud.centrica-it.com/">customer panel</a> for 
									future correspondence. Ticket details are as follows:<br>
									<p> Ticket ID: '.$ticketModel->id.'<br>
										Priority: '.$this->possible_priority[$ticketModel->priority].'<br>
										Created: '.date("M d, Y", $ticketModel->date).'<br>
										Subject: '.$ticketModel->subject.'<br>
										Message: '.$ticketModel->message.'<br>
 									</p>';
				$message_support = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
									Ticket details are as follows:<br>
									<p> Ticket ID: '.$ticketModel->id.'<br>
										Priority: '.$this->possible_priority[$ticketModel->priority].'<br>
										Created: '.date("M d, Y", $ticketModel->date).'<br>
										Subject: '.$ticketModel->subject.'<br>
										Message: '.$ticketModel->message.'<br>
 									</p>';
													
 				# 1 Send Email to Support
				$ticketModel->sendEmail("support@centrica-it.com", $ticketModel->subject, $message_support, "Support");
				
				# 2 To creator
				$ticketModel->sendEmail( Yii::app()->user->customer_email, $ticketModel->subject, $message_creator, "Customer" );
				
				# 3 Email to Parent Customer
 				
				if( Yii::app()->user->parent_id  > 0 )
				{
					$ParentInfo  = $customerModel->find("pk_customer_id = ".Yii::app()->user->parent_id ); 
 					$ticketModel->sendEmail( $ParentInfo->email, $ticketModel->subject, $message_creator,  "Customer");
				}
				# ===========================================================
				Yii::app()->user->setFlash('success', "Your Request has been placed successfully. We will contact you shortly");	
  				$this->redirect(array("cloud/vdiUsers")); 
 			}
		   
	   }
	   $this->render("newVdiMailboxUser");
   
	}
    
	 /* =================================
    	Modify VDI=>Mailbox users
      ================================= */
   public function actionModifyVdiMailUser()
   {
	   $userId = Yii::app()->request->getParam("id", "0");
	   $model = New Cloud;
	   $userInfo = $model->getMailUsers($userId);
 	   if( isset($_POST['request_type']) )
	   {
		   # Setting remaining Important Fields
		    $request = Yii::app()->request;
			$ticketModel = new Tickets;
			$departments = $ticketModel->getAllDepartments();  
			#- Variable Assignment to Save in Trelis DAtabase
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->mname		   		=  Yii::app()->user->id; 
			$ticketModel->email		   		=  Yii::app()->user->customer_email;
			$ticketModel->ipadd		   		=  $_SERVER['REMOTE_ADDR'];
 			$ticketModel->date		   		=  time();
			$ticketModel->did            	=  $request->getPost('departments');
			$ticketModel->dname         	=  $departments[$ticketModel->did];
			$ticketModel->message 			=  'Mailbox Username: '.$request->getPost('username').'<br>'.$request->getPost('request_detail'); 
			$ticketModel->subject			=  'Mailbox: '.$request->getPost('request_type').' Request'; 
			$ticketModel->fk_customer_id 	=  Yii::app()->user->pk_customer_id;
			$ticketModel->priority 			=  4; // Urgent Ticket
			# validate user input and redirect to the previous page if valid
			if($ticketModel->validate() && $ticketModel->save()) 
			{
				# Create customer in Members Table of the Trellis database as well
				$ticketModel->saveInTrellis();
				$ticketModel->updateMemId(); // Update Member ID in Tickets
				
				$ticketModel->sendEmail('',$ticketModel->subject, $ticketModel->message);
				Yii::app()->user->setFlash('success', "Your Request has been placed successfully. We will contact you shortly");	
  				$this->redirect(array("cloud/vdiUsers")); 
 			}
		   
	   }
	   $this->render("modifyVdiMailUser", array("user"=>$userInfo)); 
   }
   }