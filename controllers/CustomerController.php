<?php

class CustomerController extends Controller
{
	// Title With Customer name 
	public $possible_title 		= array("Mr."=>"Mr.", "Ms."=>"Ms.", "Mrs."=>"Mrs.");
	//Security questions in Profile form
	public $security_question 	= array("0"=>"[ Select Security Question ]", "Mother's birthplace?"=>"Mother's birthplace?", 
										"Favourite teacher?"=>"Favourite teacher?", 
										"Favourtie historical place?"=>"Favourtie historical place?",
										"Best childhood friend?"=>"Best childhood friend?",
										"Name of first pet?"=>"Name of first pet?",
										"Grandfather's occupation?"=>"Grandfather's occupation?",
										);
	public $roles = array("billing"=>"Billing","tech"=>"Tech");									
	
	
	
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}
	
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
		$arr = array("login");
		if( isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "admin" ) # Admin Role
			{
				$arr = array('index','domainSummary','order','profile','logout','contactCustomer','deleteContact','serviceRedirect','addContact','editContact'); 
				 
			}
 			elseif(isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "billing") # Billing Role
			{
				$arr = array('index','domainSummary','order','profile','logout','contactCustomer','serviceRedirect'); 
			}
		  elseif(isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "technical") # Technical Role
			{ 
				$arr = array('index','domainSummary','order','profile','logout','contactCustomer','serviceRedirect'); 
			}
		elseif(isset(Yii::app()->user->user_role) and  Yii::app()->user->user_role == "billing-technical") 
			{
				$arr = array('index','domainSummary','order','profile','logout','contactCustomer','serviceRedirect','addContact'); 
			}
		
		return array(
			array('allow',  // allow all users to access 'index' and 'view' actions.
				'actions'=>array('login','resetpassword','register', 'captcha'),
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
		 
		// Get Customer and Domains
		
		
		$model = new Customers;
		$info = $model->getCustomerInfo(); 
		
		$subModel = new Subscriptions;
		$services = $subModel->getSubscriptionName();
 		//============================================
		
		$subscriptions = new Subscriptions;
		$subs = $subscriptions->getCustomerSubscriptions(); 
		 	
		# Find Customer Subscriptions
		$services2 = array();
		$mailboxDomain = array();
		$vdiDomain = array();
		 
		foreach($subs as $key=>$value) {
		
 		    $subscriptionDetails = $subscriptions->getSubscriptionByID($value['pk_sub_id']);
 			if(in_array("VDI", $value)) 
			{ 	
			  $services2[]  = "VDI";
			  if(!empty($value['domain_url'])) 
 			  $vdiDomain[$value['domain_url']] = $value['domain_url']; 	
			  foreach($subscriptionDetails['components'] as $keyCustom=>$custom )
				{
 					if($custom['component'] == "Mailbox Domain Name")
					$mailboxDomain[$custom['component_value']]  = $custom['component_value']; 
 				}
			}
			 
			if(in_array("Mailbox", $value)){ $services2[] 	= "Mailbox";
			if(!empty($value['domain_url']))  
				$mailboxDomain[$value['domain_url']] = $value['domain_url']; 
				
			}
			
		}
		  
 		//============================================		
		$this->render('index', array("customer"=>$info,'model'=>$model, "serviceDropDown"=>$services,"mailboxDomain"=>$mailboxDomain, "vdiDomain"=>$vdiDomain) );
	}

 	public function actionOrder()
	{
		$subModel = new Subscriptions;
		$services = $subModel->getSubscriptionName();
 		$this->render("order", array("serviceDropDown"=>$services));
	}
 
 
   
	/*
		Customer Profile View/Update
	*/
    public function actionProfile()
	{
		$customer_id = Yii::app()->user->pk_customer_id;
		$model 		 = new Customers;
		$profile     = $model->find('pk_customer_id = "'.$customer_id.'"'); 
		if( isset($_POST['Customers']) )
		{
		    
		    $model->scenario = 'update'; // Use Rules that are set for update
 			if($_POST['Customers']['security_question'] != '0') { 
			$model->scenario = 'answer_required'; // Use Rules that are set for update 
			 
			}
			
 			$model->attributes=$_POST['Customers'];
			// Save info
			if( $model->validate() )
			{
				 
				$model->updateByPk($customer_id,$_POST['Customers']);
				if( $_POST['password1'] == $_POST['password2'] && strlen($_POST['password1']) > 5 )
				$model->updateByPk( $customer_id , array("password"=>md5($_POST['password1'])) );
				Yii::app()->user->setFlash('success', "Profile updated successfully!");	
   				$this->redirect(array("customer/profile")); 
			}
			else
		    {/*echo '<pre>'; print_r($model->getErrors());*/}	
		}
		 
		$this->render("profile", array('profile'=>$profile, 'model'=>$model));
	}
	 
	
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(array("customer/login"));
	}
	
	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
 		 
		$model=new Customers;
 		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['Customers']))
		{
			$model->attributes=$_POST['Customers'];
			// validate user input and redirect to the previous page if valid
			 
			if($model->login())
			{	// $test = $_POST['Customers']['username'];
				 // echo '<pre>'; print_r($test); exit; ;
				 $this->redirect(array('customer/index'));
			}
			else
				Yii::app()->user->setFlash('error', "Login has failed. Incorrect login/password!");	
		}
		// ==============================
		//	 Forget Password case
		// ==============================
		if(isset($_POST['emailForPass']) && strlen($_POST['emailForPass']) >= 5)
		{
			if( $this->forgetPassword($_POST['emailForPass']) )
			{
				Yii::app()->user->setFlash('success', "Password reset link has been sent to your email. Please check your email!");	
			}
		}
		// Password reset by Security question
		if(isset($_POST['security_question']) && !empty($_POST['security_answer']) )
		{
			
		   $q = Yii::app()->request->getPost("security_question");
		   $s = Yii::app()->request->getPost("security_answer");	
		   $custInfo = $model->find('security_question= "'.$q .'" AND security_answer = "'.$s.'"');	
		  if( isset($custInfo->email) && strlen($custInfo->username) >= 2)
		  {
			   
			 if( $this->forgetPassword($custInfo->email) )
			{
				Yii::app()->user->setFlash('success', "Password reset link has been sent to your email. Please check your email!");	
			}
			
		  } 
		  else Yii::app()->user->setFlash('error', "Invalid security question/answer!");	
		    
		}
		// display the login form
		$this->layout = "loginlayout";
		
		 
 		$this->render('login',array('model'=>$model ));
	}
	
	/*
		* Send Password Reset link to use email address
	*/
	private function forgetPassword($email)
	{
		$model = new Customers;	
		
		// 1. set temporary Password
		$tempPassword = time();
		$flag = $model->changePassword($tempPassword , $email);
		if($flag) {
			// 2. send email first
			$subject = "Reset Your Centrica IT Password";
			$message = "Visit the following link to reset you password \n";
		    $message .= "<a href='".Yii::app()->getBaseUrl(true)."/my.php/customer/resetpassword/sess/".$tempPassword."'>". Yii::app()->getBaseUrl(true) ."/my.php/customer/resetpassword/sess/".$tempPassword."</a>"; 
			 
			# 1.  Send Email to current User 
			$this->sendEmail($email, $subject, $message, $flag->firstname.' '.$flag->lastname);
			
			$message = "A password reset request has been initiated by ".$flag->firstname.' '.$flag->lastname.". 
			A password reset link has been sent to ".$email;
		    //$flag->phone
			
			# 2. Send Email To Support
			$this->sendEmail("support@centrica-it.com", "Password reset request initiated", $message, "Support");
			
			 Yii::app()->user->setFlash('success', "Password reset link has been sent to your email. Please visit that link to reset your password");	
 			$this->redirect(array('/customer/login'));
			
		}
		else
		{
			  Yii::app()->user->setFlash('error', "Email doesn't exist!");	
 				$this->redirect(array('/customer/login'));
		}
	}
	
	public function actionResetpassword()
	{
		$model = new Customers;
		$userInfo = $model->find("password = '".Yii::app()->request->getParam("sess", "default")."'");
		if(isset($_POST['password']))
		{
		    $userInfo = $model->find("password = '".$_POST['oldpassword']."'");
  			// change password now
			$flag = $model->changePassword(md5($_POST['password']) , $userInfo['email']);
			if($flag) {
			
			$subject = "Centrica IT password change confirmation";
			$message = "Congratulations! You've successfully changed your password."; 
 			# 1.  Send Email to current User 
			$this->sendEmail($flag->email, $subject, $message, $flag->firstname.' '.$flag->lastname);
			
			     Yii::app()->user->setFlash('success', "Password reset successfully!");	
 				$this->redirect(array('/customer/login'));
			}
			else
			{
				    Yii::app()->user->setFlash('error', "Unable to reset your password. You can change your password using this link only once.");	
			}
			
		}
		$this->layout='loginlayout';
		if(!is_object($userInfo))
		$this->redirect(array('/customer/login'));
 		$this->render("resetpassword", array("username"=>$userInfo->username));
	}
	/*
		Domain summary of a customer
	*/
	public function actionDomainSummary()
	{
		// Get Customer and Domains
		$model = new Customers;
		$info = $model->getCustomerInfo(); 		
		$this->render('domain_summary', array("customer"=>$info));
	}
	
	/*
		* Related Login Customers
	*/
	public function actionContactCustomer()
	{
		$customer_id = Yii::app()->user->pk_customer_id;
		$model 		 = new Customers;
		$parent = $model->find("pk_customer_id = ".$customer_id);
		 
		if($parent->parent_id > 0)
		$Allcustomers   = $model->findAll("(pk_customer_id = ".$parent->parent_id." 
										  OR parent_id = '".$customer_id."' OR 
										  parent_id = ".$parent->parent_id .') OR pk_customer_id = '.$customer_id);
		else
		$Allcustomers   = $model->findAll("parent_id = ".$customer_id." or pk_customer_id = ".$customer_id);
		
		$relatedCustomers 	= $model->getAllRelatedCustomers( $customer_id , $parent->parent_id  );
		
		if( isset($_POST['is_emergency_contact']) )
		{
				$model->saveContact($_POST);
				$this->redirect(array("customer/ContactCustomer"));
		}
  		$this->render( "contactCustomer", array("AllCustomer"=>$Allcustomers, "customers"=>$relatedCustomers) ); 
		
	}
	
	/*
		Delete Contact Customer
	*/
	public function actionDeleteContact()
	{
		$id = Yii::app()->request->getParam("id", "default");
		$model 	= new Customers;
		$model->deleteByPk($id);
		Yii::app()->user->setFlash('success', "User deleted successfully!");
		$this->redirect(array("customer/ContactCustomer"));
		exit;
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
				
				 Yii::import('application.controllers.CloudController');
 				CloudController::registerSendEmail($_POST['Customers']); 
   				$this->redirect(array("customer/index")); 
			}
			else
			{
				  Yii::app()->user->setFlash('reg_error', "Please fill all important * fields");	
				 $this->redirect(array("customer/register")); 
				 
			}
		}
		
		 $this->render("register" ,array('model'=>$model));
	}
	
	function actionServiceRedirect()
	{
	    $serviceName = Yii::app()->request->getPost("serviceName");
		 
		if($serviceName == 'VDI') { 
		Yii::app()->session['domain'] = Yii::app()->request->getPost('vdiDomainId');
		$this->redirect(array("cloud/vdiUsers")); 
 		}
		if($serviceName == 'Mailbox')  { 
		Yii::app()->session['domain'] = Yii::app()->request->getPost('mailboxDomainId');
		$this->redirect(array("cloud/mailUsers"));  
		}
	}
	
	/* ======================================================
	   Add Secondary Account info from ContactCustomer page
	   ====================================================== */
	public function actionAddContact()
	{
		 
		$model = new Customers;
		
		if(isset($_POST['Customers']))
		{
 			 $model->scenario = 'add_contact'; // Use Rules that are set for update
 			 $model->attributes=$_POST['Customers'];
			// Save info
			if( $model->validate() )
			{
			 	$model->saveRegister($_POST['Customers']);
				
				 
				# 2. Send email to newly created user
				Yii::import('application.controllers.CloudController');
 				CloudController::registerSendEmail($_POST['Customers']);
				
				# -  Email Send to support Centrica
		 		/*$message2 = '<p> A new user has been registered by '.Yii::app()->user->fullname.' with the following info:</p>
		 					<p> Full Name: '.$model->firstname .' '.$model->lastname.'</p>
							<p> Username: '.$model->username.'</p>
							<p> Email: '.$model->email.'</p>
							<p> Telephone: '.$model->phone.'</p>
						   '; 
						 
		 		$template2 = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html");
		 		$template2 = str_replace("#from_name#", "Support", $template2);  
		 		$template2 = str_replace("#message#", $message2, $template2);
 				$this->sendEmail("support@centrica-it.com", "New Contact Added Successfully", $template2, "Support");*/
				
				# 3. Send email to the Login current user
				$message = "<p> You have successfully added a new contact   ".$model->firstname.' '.$model->lastname. " 
				with following details:</p>
				<p> Username: ".$model->username ."<br> 
				Password: ".$model->password;
				$message .= "<br> Role: ".$model->role."<br> 
				Email: ".$model->email.'<br> 
				Telephone: '.$model->phone.'<br>
				Role: '.$model->role.'
				</p>';
				
				$this->sendEmail(Yii::app()->user->customer_email, "New Contact Added Successfully", $message, Yii::app()->user->fullname) ;
				
   				$this->redirect(array("customer/contactCustomer")); 
			}
			 
		}
		
		 $this->render("addContact" ,array('model'=>$model));
	}
	
	/* ======================================================
	  Edit Secondary Account info from ContactCustomer page
	   ====================================================== */
	public function actionEditContact()
	{
		$model = new Customers;
		
		$id    		 = Yii::app()->request->getParam("id", '0');
		$profile 	 = $model->find("pk_customer_id = ".$id);  
 		
		if(isset($_POST['Customers']))
		{
 			 $model->scenario = 'edit_contact'; // Use Rules that are set for update
 			 $model->attributes=$_POST['Customers'];
			// Save info
			if( $model->validate() )
			{
				
				if( !empty($_POST['Customers']['password']) )
				$_POST['Customers']['password'] = md5($_POST['Customers']['password']);
				else
				unset($_POST['Customers']['password']);
			   
				if($model->role=='tech') {
				    $_POST['Customers']['is_billing'] = 0;
					$_POST['Customers']['is_technical'] = 1;
 				}
				if($model->role=='billing') {
				    $_POST['Customers']['is_billing'] = 1;
					$_POST['Customers']['is_technical'] = 0;
 				}
                $model->updateByPk($_POST['customer_id'],$_POST['Customers']); 
    			$this->redirect(array("customer/contactCustomer")); 
			}
			 
		}
		
		 $this->render("editContact" ,array('model'=>$model, 'profile'=>$profile));
	}
	/* ================================================
	   Email Sending
	====================================================*/
	function sendEmail($to, $subject, $message, $toName = '')
	{
		 /*$emailer = new DX_Mailer; 
		 $emailer->IsHTML(true); 
		 $emailer->Sender = Yii::app()->params->adminEmail;
		 $emailer->FromName = "Centrica-IT";
		 $emailer->From     = "support@centrica-it.com";
		 
		 $emailer->Subject = "Welcome To Centrica-IT, <p>".$subject;*/
		 
		  # Email Template
		  $template = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html");
 		// $template = $this->renderPartial("mail_register", null, true);
		 $template = str_replace("#from_name#", $toName, $template);  
		 $template = str_replace("#message#", $message, $template);
		 
		 $name='=?UTF-8?B?'.base64_encode("Centrica IT").'?=';
		 $subject='=?UTF-8?B?'.base64_encode($subject).'?=';
		 $headers="From: $name <support@centrica-it.com>\r\n".
					"Reply-To: support@centrica-it.com\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-type: text/html; charset=UTF-8";
		 @mail($to, $subject, $template, $headers, "-f support@centrica-it.com");
	}
}