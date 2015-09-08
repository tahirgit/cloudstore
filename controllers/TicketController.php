<?php

class TicketController extends Controller
{
	/* --> Ticket Status */
	public $possible_status = array(
										0 => 'NEW',
										1 => 'WAITING REPLY',
										2 => 'REPLIED',
										3 => 'RESOLVED (CLOSED)',
										4 => 'IN PROGRESS',
										5 => 'ON HOLD',
										6 => 'CLOSED',
									);
	/* --> TICKET PRIORITY */
	public $possible_priority = array(
										 
										1 => 'LOW',
										2 => 'MEDIUM',
										3 => 'HIGH',
										4 => 'URGENT',
							    );
								
	public $upgradeReason = array("upgrade"=>"I want to Upgrade my service","downgrade"=>"I want to Downgrade
	my service");
    
	public $cancelReason  = array("Moved to some other provider"=>"Moved to some other provider" ,
										"Don't need service anymore"=>"Don't need service anymore", 
								   
								  "Not satisfied with service"=>"Not satisfied with service",
	 							   "Other"=>"Other");
	
	
	// File Attached Uploading Path							
	public $upload_dir = '../trellis/uploads/';							
	
	// Access functions							
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
		return array(
		 
			array('allow', // allow authenticated users to access all actions
				'actions'=>array('index','create','view','closed', 'createServiceTicket', 'forum','uploadexcel'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}							
	
	/*
		- Shows OPEN ticket Listings 
	*/							
	public function actionIndex()
	{
 		$model 	 = new Tickets;
		$customerModel = new Customers;
		$childCustomers =  $customerModel->findAll("parent_id = ".Yii::app()->user->pk_customer_id);
		$childArr =  array();
		$SameRole = array();
		# billing can view all Billing member ticket and Technical can view All Technical Tickets
		if(Yii::app()->user->parent_id > 0)
		{
			if(Yii::app()->user->user_role == 'billing')
			$SameRoleCustomers =  $customerModel->findAll("(is_billing = 1 or role = 'billing') AND parent_id = ".Yii::app()->user->parent_id);
			elseif(Yii::app()->user->user_role == 'technical')
			$SameRoleCustomers =  $customerModel->findAll("(is_technical = 1 or role = 'tech') AND parent_id = ".Yii::app()->user->parent_id);
		}
		# =========================================
 		if(isset($SameRoleCustomers)) { 
			foreach($SameRoleCustomers as $same){
				 
				$SameRole[] = $same->pk_customer_id;
			}
			  
			 $SameRoleString = implode(',',$SameRole);
		 }
		 # ========================================
  		foreach($childCustomers as $child){
			 
			$childArr[] = $child->pk_customer_id;
		}
		 $childArr[] = Yii::app()->user->pk_customer_id;
 		 $childIDs = implode(',',$childArr);
		 # ========================================
		
		 if(isset($SameRoleString))
		 $childIDs = $childIDs.','.$SameRoleString;
		  
 		$tickets = $model->findAll(array('order'=>'id Desc', 'condition'=>'(fk_customer_id=:x OR fk_customer_id IN ('.$childIDs.') ) and status != 6', 'params'=>array(':x'=>Yii::app()->user->pk_customer_id)));
		
		 
 		$this->render('index', array("tickets"=>$tickets));
	}
    
	/*
		- Shows closed ticket Listings 
	*/							
	public function actionClosed()
	{
 		$model 	 = new Tickets;
		$customerModel = new Customers;
		$childCustomers =  $customerModel->findAll("parent_id = ".Yii::app()->user->pk_customer_id);
		$childArr =  array();
		
		$SameRole = array();
		# billing can view all Billing member ticket and Technical can view All Technical Tickets
		if(Yii::app()->user->parent_id > 0)
		{
			if(Yii::app()->user->user_role == 'billing')
			$SameRoleCustomers =  $customerModel->findAll("(is_billing = 1 or role = 'billing') AND parent_id = ".Yii::app()->user->parent_id);
			elseif(Yii::app()->user->user_role == 'technical')
			$SameRoleCustomers =  $customerModel->findAll("(is_technical = 1 or role = 'tech') AND parent_id = ".Yii::app()->user->parent_id);
		}
		# =========================================
 		if(isset($SameRoleCustomers)) { 
			foreach($SameRoleCustomers as $same){
				 
				$SameRole[] = $same->pk_customer_id;
			}
			  
			 $SameRoleString = implode(',',$SameRole);
		 }
		 # ========================================
 		foreach($childCustomers as $child){
			 
			$childArr[] = $child->pk_customer_id;
		}
		 $childArr[] = Yii::app()->user->pk_customer_id;
 		 $childIDs = implode(',',$childArr);
		 
		 if(isset($SameRoleString))
		 $childIDs = $childIDs.','.$SameRoleString;
		 
		$tickets = $model->findAll('status = "6" AND (fk_customer_id='.Yii::app()->user->pk_customer_id.' OR fk_customer_id IN ('.$childIDs.'))');
  		$this->render('closed', array("tickets"=>$tickets));
	}
    
	
	public function actionUploadexcel()
	{
		$service = Yii::app()->request->getParam("service", "mailbox");
		$this->render("upload_excel",array("service"=>strtoupper($service)));
	}
	/*
		- Creates a new Customer Tickets
	*/
	public function actionCreate()
	{
 		$model 		 = new Tickets;
		$departments = $model->getAllDepartments(); // Return only Customer Group Allowed Department
		if( isset($_POST['Tickets']) )
		{
			# Setting remaining Important Fields
			$attach_id = 0; 
			$model->fk_customer_id =  Yii::app()->user->pk_customer_id;
			$model->mname		   =  Yii::app()->user->id; 
			$model->email		   =  Yii::app()->user->customer_email;
			$model->ipadd		   = $_SERVER['REMOTE_ADDR'];
 			$model->date		   =  time();
			$model->did            =  $_POST['departments'];
			$model->dname          = $departments[$model->did];
			 
			# ===========================================
			# 	Check Any file Attachment
			# ===========================================
			if(isset($_FILES['attachment']) and $_FILES['attachment']['size'] > 0)
			{
				$file_ext = strrchr( $_FILES['attachment']['name'], "." );
				$attachment_name = md5( 'a'. uniqid( rand(), true ) ) . $file_ext;
				$file_safe_name = $this->sanitize_name( $_FILES['attachment']['name'] );  
				
				$attachment_loc = $this->upload_dir .'/'. $attachment_name;
				if ( @ ! move_uploaded_file( $_FILES['attachment']['tmp_name'], $attachment_loc ) )
				{
					Yii::app()->user->setFlash('error', "File Attachement Error");	
					$this->redirect(array("ticket/create")); 
				}
				$attach_id = $model->saveAttachment($_FILES, $attachment_name, $file_safe_name);
			}
			
			$model->attach_id  = $attach_id;
 			$model->attributes = $_POST['Tickets'];
			if( isset($_POST['subject']) && isset($_POST['serviceName']) )
			{
				$model->subject = $model->subject .', '.$_POST['subject'];
				$model->message = $model->message; 
			}
			
			# ================================================================
			# validate user input and redirect to the previous page if valid
			# ================================================================
			if($model->validate() && $model->save())
			{
				# ================================================================
				# Create customer in Members Table of the Trellis database as well
				# ================================================================
				$model->saveInTrellis();
				$model->updateMemId(); // Update Member ID in Tickets
				
				# 2 To creator
				$message_creator = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
				Please login to <a href="https://mycloud.centrica-it.com/">customer panel</a> for 
									future correspondence. Ticket details are as follows:<br><br>
									<p> Ticket ID: '.$model->id.'<br>
										Priority: '.$this->possible_priority[$model->priority].'<br>
										Created: '.date("M d, Y", $model->date).'<br>
										Subject: '.$model->subject.'<br>
										Message: '.$model->message.'<br>
 									</p>';
				$message_support = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
									Ticket details are as follows:<br>
									<p> Ticket ID: '.$model->id.'<br>
										Priority: '.$this->possible_priority[$model->priority].'<br>
										Created: '.date("M d, Y", $model->date).'<br>
										Subject: '.$model->subject.'<br>
										Message: '.$model->message.'<br>
 									</p>';
													
 				# 1 Send Email to Support
				$model->sendEmail("support@centrica-it.com", $model->subject, $message_support, "Support");
				
				# 2 To creator
				$model->sendEmail( Yii::app()->user->customer_email, $model->subject, $message_creator, "Customer" );
				
				# 3 Email to Parent Customer
				$customerModel = new Customers;
				$customerInfo  = $customerModel->find("username ='".Yii::app()->user->id."'"); 
				
				if( Yii::app()->user->parent_id  > 0 )
				{
					$ParentInfo  = $customerModel->find("pk_customer_id = ".Yii::app()->user->parent_id ); 
 					$model->sendEmail( $ParentInfo->email, $model->subject, $message_creator,  "Customer");
				}
				
				
  				$this->redirect(array("ticket/index")); 
 			}
 				 
		}
		# ==============================================================================
		# If subject is comming from  customers/order for Service Upgrade/Downgrade than
		# ==============================================================================
		$subject = '';
		$serviceName = '';
		if(isset($_POST['subject']))
		{
			$subject = $_POST['subject'];
			$serviceName = $_POST['serviceName'];
		}
		
		$this->render('create' , array("model"=>$model, "departments"=>$departments, "subject"=>$subject, "serviceName"=>$serviceName ));
		
	}
	
	#=======================================
	# @ Sanitize Name
	# Remove scary characters. :P
	#=======================================

	function sanitize_name($name)
	{
		$name = str_replace( " ", "_", $name );

		return preg_replace( "[^A-Za-z0-9_\.]", "", $name );
	}
	
	/* ==========================================
		- View Single Ticket and Can Give Reply
	   ========================================== */
	public function actionView()
	{  
	 
	    $model 	   = new Tickets;
		$ticket_id = Yii::app()->request->getParam('tid','default'); 
		if( isset($_POST['Tickets']['message']))
		{
			$res = $model->saveReply($_POST);
			// place redirect to main page
		}

		if(!empty($ticket_id))
		{
			$ticketInfo   = array();
			//$ticketInfo['ticket']	  = $model->find("fk_customer_id='".Yii::app()->user->pk_customer_id."' AND id = '".$ticket_id."'");
			$ticketInfo['ticket']	  = $model->find("id = ".$ticket_id);
			$ticketInfo['allReplies'] = $model->getTicketInfo($ticket_id);
			$this->render('view', array('viewTicket'=>$ticketInfo, "model"=>$model) );
		}	
		
 	}
	
    /* ==========================================
		- Creates a new Customer Tickets
	   ========================================== */
	public function actionCreateServiceTicket()
	{
	 
		$model 		 = new Tickets;
		$departments = $model->getAllDepartments(); // Return only Customer Group Allowed Department
		if( isset($_POST['Tickets']) )
		{	
			# Setting remaining Important Fields
			$attach_id = 0; 
			$model->fk_customer_id =  Yii::app()->user->pk_customer_id;
			$model->mname		   =  Yii::app()->user->id; 
			$model->email		   =  Yii::app()->user->customer_email;
			$model->ipadd		   = $_SERVER['REMOTE_ADDR'];
 			$model->date		   =  time();
			$model->did            =  3 ;
			$model->dname          = $departments[$model->did];
			$model->priority = 4; //urgent
			 
			# ===========================================
			# 	Check Any file Attachment
			# ===========================================
			if(isset($_FILES['attachment']) and $_FILES['attachment']['size'] > 0)
			{
				$file_ext = strrchr( $_FILES['attachment']['name'], "." );
				$attachment_name = md5( 'a'. uniqid( rand(), true ) ) . $file_ext;
				$file_safe_name = $this->sanitize_name( $_FILES['attachment']['name'] );  
				
				$attachment_loc = $this->upload_dir .'/'. $attachment_name;
				if ( @ ! move_uploaded_file( $_FILES['attachment']['tmp_name'], $attachment_loc ) )
				{
					Yii::app()->user->setFlash('error', "File Attachement Error");	
					$this->redirect(array("ticket/create")); 
				}
				$attach_id = $model->saveAttachment($_FILES, $attachment_name, $file_safe_name);
			}
			
			$model->attach_id          = $attach_id;
 			$model->attributes=$_POST['Tickets'];
			
			if( isset($_POST['subject']) && isset($_POST['serviceName']) )
			{
				 
				if($model->subject == 'upgrade')
				$model->subject = $_POST['serviceName'] .' Service Upgrade';
				elseif($model->subject == 'downgrade')
				$model->subject = $_POST['serviceName'] .' Service Downgrade';
 				
				if($_POST['subject'] == 'cancel') { 
				
				$model->message = $model->message."<br> Reason: ".$model->subject ;
				$model->subject = $_POST['serviceName'] .' Cancellation Request';
				 
				}
				
			}
			
			# ================================================================
			# validate user input and redirect to the previous page if valid
			# ================================================================
			if($model->validate() && $model->save())
			{
				# ================================================================
				# Create customer in Members Table of the Trellis database as well
				# ================================================================
				$model->saveInTrellis();
				$model->updateMemId(); // Update Member ID in Tickets
 				
				//$model->sendEmail($model->subject, $model->message);
 				
				# 2 To creator
				$message_creator = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
				Please login to <a href="https://mycloud.centrica-it.com/">customer panel</a> for 
									future correspondence. Ticket details are as follows:<br><br>
									<p> Ticket ID: '.$model->id.'<br>
										Priority: '.$this->possible_priority[$model->priority].'<br>
										Created: '.date('M d, Y', $model->date).'<br>
										Subject: '.$model->subject.'<br>
										Message: '.$model->message.'<br>
 									</p>';
								
				$message_support = 'A new ticket has been successfully created by '.Yii::app()->user->fullname.' with Centrica IT. 
									Ticket details are as follows:<br>
									<p> Ticket ID: '.$model->id.'<br>
										Priority: '.$this->possible_priority[$model->priority].'<br>
										Created: '.date('M d, Y', $model->date).'<br>
										Subject: '.$model->subject.'<br>
										Message: '.$model->message.'<br>
 									</p>';
												
				$model->sendEmail( Yii::app()->user->customer_email, $model->subject, $message_creator, "Customer");
				
				# 3 Email to Parent Customer
				$customerModel = new Customers;
				$customerInfo  = $customerModel->find("username ='".Yii::app()->user->id."'"); 
				
				if( Yii::app()->user->parent_id > 0)
				{
					$ParentInfo  = $customerModel->find("pk_customer_id = ".Yii::app()->user->parent_id ); 
 					$model->sendEmail( $ParentInfo->email, $model->subject, $message_creator,  "Customer");
				}
				
				# 1 Send Email to Support
				$model->sendEmail("support@centrica-it.com",$model->subject, $message_support,'Support');
				
  				$this->redirect(array("ticket/index")); 
 			}
 				 
		}
		# ==============================================================================
		# If subject is comming from  customers/order for Service Upgrade/Downgrade than
		# ==============================================================================
		$subject = '';
		$serviceName = '';
		$reasons = array();
		if(isset($_POST['subject']))
		{
			$subject = $_POST['subject'];
			$serviceName = $_POST['serviceName'];
		}
		if( isset($_POST['subject']) && $_POST['subject'] == "Service Upgrade/Downgrade")
		$reasons = $this->upgradeReason;
		elseif( isset($_POST['subject']) && $_POST['subject'] == "cancel")
		$reasons = $this->cancelReason;
		
		if(count($reasons) < 1)
		$this->redirect(array("customer/order")); 
		
		$this->render('createServiceTicket' , array("model"=>$model, "departments"=>$departments, "subject"=>$subject, "serviceName"=>$serviceName , "reasons"=>$reasons));
		
	} 
	
	public function actionForum()
	{
		$this->render("forum");
	}
}