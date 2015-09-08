<?php
Yii::import('application.vendors.*');
require_once('gocardless/lib/GoCardless.php');
require_once('gocardless/lib/GoCardless/Exceptions.php');
require_once('gocardless/lib/GoCardless/Request.php');
require_once('gocardless/lib/GoCardless/Resource.php');
require_once('gocardless/lib/GoCardless/Client.php');
require_once('gocardless/lib/GoCardless/Subscription.php');
require_once('gocardless/lib/GoCardless/PreAuthorization.php');
require_once('gocardless/lib/GoCardless/Utils.php');

require_once('gocardless/lib/GoCardless/Merchant.php');
require_once('gocardless/lib/GoCardless/User.php');
require_once('gocardless/lib/GoCardless/Bill.php');

 
class PaymentController  extends Controller {
 
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
				'actions'=>array('index','paymentcard','cardsuccess','paynow', 'paymentdebitcard','cartPayment'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	} 
	
	/*
		* Payment method Lists
	*/
	public function actionIndex()
	{
		$ticketModel = new Tickets;
 		$invoiceID 		= Yii::app()->request->getParam("id","0");
		$invoiceModel   = new Invoices;
		$payment		= $invoiceModel->getInvoicePayment( $invoiceID );
		# - Customer Informations
		$customerName = Yii::app()->user->fullname;
		$customerEmail = Yii::app()->user->customer_email; 
		$customerParent = Yii::app()->user->parent_id; 
 
 		# - check if not already paid
		if( !isset($payment[0]['invoice_status']) || ($payment[0]['invoice_status'] == 1 && $payment[0]['payment_status'] == 1)   )
		{
			//$this->redirect(array("invoice/index/"));
		}
		
		  # - Check whether the request is come for invoice payment or Shopping cart items
		  if( count($payment) > 0)
			$_SESSION['invoice_id'] = $payment[0]['fk_invoice_id'];
		   	
		
		if( isset($_POST['payment_method_select']) )
		{   
		    /***********************************/
			   # Payment by CreditCard
			/***********************************/
			if( ($_POST['payment_method_select'] == 'creditcard')  && isset($_POST['payment_method'])
			) {   
				switch($_POST['payment_method'])
				{
					case "creditcard":
					$this->redirect(array("payment/paymentcard"));
					break;
					
					case "paymentByLink":
					$invoiceModel->sendPaymentRequest( $_SESSION['invoice_id'], "Credit Card", "Pay By Link Request" );
					#1. Current Customer
					$message = "A Pay By Link request using Credit Card for invoice No: ".$_SESSION['invoice_id']." has been placed successfully 
				by ".Yii::app()->user->fullname.". We'll shortly e-mail ".Yii::app()->user->customer_email." a payment link to collect payment."; 
					
 					 #1. Support  
					$message_support = Yii::app()->user->fullname." has sent you a Pay By Link request using Credit Card. 
					Please create and e-mail a payment link to ".Yii::app()->user->customer_email." for invoice No:".$_SESSION['invoice_id'];
					 
					# Send Email to support and the current user
 					$ticketModel->sendEmail('support@centrica-it.com','Pay by Link request using Credit Card',$message_support, "Support");
					$ticketModel->sendEmail($customerEmail,'Pay by Link request using Credit Card', $message, "Customer");
					if( $customerParent > 0 )
					{
						$custModel = new Customers;
						$custInfo = $custModel->find("pk_customer_id=".$customerParent);
						$ticketModel->sendEmail($custInfo->email,'Pay by Link request using Credit Card', $message, "Customer");
					}
					
					 $this->createLinkTicket($_SESSION['invoice_id'], "Credit Card");
					$this->render("payment_request", array('message'=>$message));
					exit;
 					break;
					
					case "phone":
					$invoiceModel->sendPaymentRequest( $_SESSION['invoice_id'], "Credit Card", "Pay by Phone Request" );
					$this->createTicket($_SESSION['invoice_id'], 'Credit Card');
					$message = "Your Payment Request has been placed successfully. 
					We'll call you shortly to collect payment information.";
					$this->render("payment_request", array('message'=>$message));
					exit;
 					break;
					
					default:
					$this->redirect(array("payment/index/".$_SESSION['invoice_id']));
					break;
				}
			}
			 /***********************************/
			   # - Payment By Debit Card
			 /***********************************/
 			elseif($_POST['payment_method_select'] == 'debitcard'   && isset($_POST['payment_method']))
			{
				switch($_POST['payment_method'])
				{
					case "debitcard":
					$this->redirect(array("payment/paymentdebitcard"));
					break;
					
					case "paymentByLink":
					$invoiceModel->sendPaymentRequest( $_SESSION['invoice_id'], "Debit Card", "Pay By Link Requested" );
 					
					$message = "A Pay By Link request using Debit Card for invoice No: ".$_SESSION['invoice_id']." 
					has been placed successfully 
				by ".Yii::app()->user->fullname.". We'll shortly e-mail ".Yii::app()->user->customer_email." a payment 
				link to collect payment."; 
					 
					
					 #1. Support  
					$message_support = Yii::app()->user->fullname." has sent you a Pay By Link request using Debit Card. 
					Please create and e-mail a payment link to ".Yii::app()->user->customer_email." for invoice No:".$_SESSION['invoice_id'];
					
					# Send Email to support and the current user
 					$ticketModel->sendEmail('support@centrica-it.com','Pay by Link request using Debit Card', $message_support, "Support");
					$ticketModel->sendEmail($customerEmail,'Pay by Link request using Debit Card', $message, "Customer");
					
					if( $customerParent > 0 )
					{
						$custModel = new Customers;
						$custInfo = $custModel->find("pk_customer_id=".$customerParent);
						$ticketModel->sendEmail($custInfo->email,'Pay by Link request using Debit Card',$message, "Customer");
					}
					$this->createLinkTicket($_SESSION['invoice_id'], "Debit Card");
					$this->render("payment_request", array('message'=>$message));
					exit;
 					break;
					
					case "phone":
					$invoiceModel->sendPaymentRequest( $_SESSION['invoice_id'], "Debitcard", "Pay by Phone Requested" );
					$this->createTicket($_SESSION['invoice_id'], 'Debit card');
					$message = "Your Payment Request has been placed successfully. 
					We'll call you shortly to collect payment information.";
					$this->render("payment_request", array('message'=>$message));
					exit;
 					break;
					
					default:
					$this->redirect(array("payment/index/".$_SESSION['invoice_id']));
					break;
				}
			}
			 /***********************************/
			  # -  Payment by GoCardless 
			 /***********************************/
			elseif($_POST['payment_method_select'] == 'direct_debit' && isset($_POST['payment_method'])) {  // Direct Debit selected
				
				switch($_POST['payment_method'])
				{
					// Onetime Payment
					case "paynow":
					$this->redirect(array("payment/paynow/"));
					break;
					// Subscription
					case "subscription":
					$duration = $_POST['subscription_duration'];
					$this->redirect(array("payment/paynow/duration/".$duration."/type/subscription/"));
 					break;
					// Pre-Authorization
					case "pre-authorization":
					$duration = $_POST['authorization_duration'];
					$authorization_amount = $_POST['authorization_amount'];
					$this->redirect(array("payment/paynow/duration/".$duration."/type/authorization/amount/".$authorization_amount));
					break;
					
					case "paylink":
 					$invoiceModel->sendPaymentRequest( $_SESSION['invoice_id'], "Direct Debit", "Pay By Link Requested" );
					
				$message = "A Pay By Link request using Direct Debit for invoice No: ".$_SESSION['invoice_id']." has been placed successfully 
				by ".Yii::app()->user->fullname.". We'll shortly e-mail ".Yii::app()->user->customer_email." a payment link 
				to collect payment."; 
				 
					 #1. Support  
  					$message_support = Yii::app()->user->fullname." has sent you a Pay By Link request using Direct Debit. 
					Please create and e-mail a payment link to ".Yii::app()->user->customer_email." for invoice No:".$_SESSION['invoice_id'];
 					# Send Email to support and the current user
 					$ticketModel->sendEmail('support@centrica-it.com','Pay by Link request using Direct Debit', $message_support, "Support");
					$ticketModel->sendEmail($customerEmail,'Pay by Link request using Direct Debit', $message, "Customer");
					if( $customerParent > 0 )
					{
						$custModel = new Customers;
						$custInfo = $custModel->find("pk_customer_id=".$customerParent);
						$ticketModel->sendEmail($custInfo->email,'Pay by Link request using Direct Debit', $message, "Customer");
					}
					$this->createLinkTicket($_SESSION['invoice_id'], "Direct Debit");
					$this->render("payment_request", array('message'=>$message));
					//$this->sendDirectLink($_SESSION['invoice_id']);
					exit;
 					break;
 					
					default:
					$this->redirect(array("payment/paynow/"));
					break;
				}
			}
		}
		$this->render("index", array("payment"=>$payment));
	}
	
	/*
		* Function to create ticket for Phone payment request
	*/
	public function createTicket($invoice_id, $method)
	{
		$model 		 = new Tickets;
		$customerModel = new Customers;
		$custInfo = $customerModel->find('pk_customer_id = '.Yii::app()->user->pk_customer_id);
		
 		$url = Yii::app()->getBaseUrl(true).'/index.php/ticket/create/';
		$department = 3; // customer support
		$priority = 4; // urgent
		$subject = "Pay By Phone request using ".$method;
		$message = Yii::app()->user->fullname ." has created you a Pay By Phone request using ".$method . ' for invoice No: '.$invoice_id;
 		 
        # Setting remaining Important Fields
		$attach_id = 0; 
		$model->fk_customer_id =  Yii::app()->user->pk_customer_id;
		$model->mname		   =  Yii::app()->user->id; 
		$model->email		   =  Yii::app()->user->customer_email;
		$model->ipadd		   = $_SERVER['REMOTE_ADDR'];
 		$model->date		   =  time();
		$model->did            =  3;
		$model->dname          = "Customer support";	
		$model->subject = $subject;		
		$model->priority = $priority;
		$model->message = $message;
		if( $model->save() )
		{
				# ================================================================
				# Create customer in Members Table of the Trellis database as well
				# ================================================================
				$model->saveInTrellis();
				$model->updateMemId(); // Update Member ID in Tickets
 				# 1 Send Email to Support
				$model->sendEmail("support@centrica-it.com",$model->subject, $model->message, 'Support');
				$body = "A Pay By Phone request using ".$method." for invoice No: ".$invoice_id." has been placed successfully 
				by ".Yii::app()->user->fullname.". We'll call ".$custInfo->phone." shortly to collect payment information. ";
						 
				# 2 To creator
				$model->sendEmail( Yii::app()->user->customer_email, $model->subject, $body,  "Customer");
				
				# 3 Email to Parent Customer
 				
				if( Yii::app()->user->parent_id > 0)
				{
					$ParentInfo  = $customerModel->find("pk_customer_id = ".Yii::app()->user->parent_id); 
 					$model->sendEmail( $ParentInfo->email, $model->subject, $body, "Customer");
				}
	 		  
 			}		  
	}
	
	/*
		* Payment Form By creditcard -> CardSave
	*/
	public function actionPaymentcard()
	{ 
		
		if( isset($_SESSION['invoice_id']) )
		{
			$invoiceModel  = new Invoices;
			$payment		= $invoiceModel->getInvoicePayment( $_SESSION['invoice_id'] );
			# Add 2% of the total in the invoice total for creditcard only
			$twoPercentAmount = ($payment[0]['payment_amount'] * 2)/100;
			$payment[0]['payment_amount'] += round($twoPercentAmount, 2);
			$this->render("cardForm", array("payment"=>$payment));
 		}
		
	}
	
	/*
		* Payment Form By Debit Card -> CardSave
	*/
	public function actionPaymentdebitcard()
	{ 
		
		if( isset($_SESSION['invoice_id']) )
		{
			$invoiceModel  = new Invoices;
			$payment		= $invoiceModel->getInvoicePayment( $_SESSION['invoice_id'] );
 			$this->render("cardForm", array("payment"=>$payment));
 		}
		
	}
	
	
	/*
		* Payment Form By Direct Debit Redirect -> GoCardLess  for OneTime Payment.
	*/
	public function actionPaynow()
	{ 
	    // Duration is for subscription
		$duration = Yii::app()->request->getParam("duration", "0");
		// subscription or pre-authorization
		$type = Yii::app()->request->getParam("type", "0");
		 
		if( isset($_SESSION['invoice_id']) )
		{
		 
		$invoiceModel  = new Invoices;
	    $payment		= $invoiceModel->getInvoicePayment( $_SESSION['invoice_id'] );
		// Sandbox
		GoCardless::$environment = 'sandbox';
		
		// Config vars
		$account_details = array(
		  'app_id'        => 'HXC009A5B8B2AX6A0C6HY4950B0JXM1Y8HEGZB9Q6CD51TDG1RR34BTG6N6H1PVK',
		  'app_secret'    => 'V7MTFFF67K4SEH0AWWTHEEXWRKK7JQ4MJ0QWTZ3EP0YMG65XG9S1TCA77R2PMDYC',
		  'merchant_id'   => '07N2GAPV0D',
		  'access_token'  => '0T6JYNA5B1S6H2DWWA6SJ43CBTG7QVMSZR79Z1ENTWZWK59Z8616H5GWBHDDTX02'
		);
		
		// Fail nicely if no account details set
		if ( ! $account_details['app_id'] && ! $account_details['app_secret']) {
		  echo '<p>First sign up to <a href="http://gocardless.com">GoCardless</a> and
		copy your sandbox API credentials from the \'Developer\' tab into the top of
		this script.</p>';
		  exit();
		}
		
		// Initialize GoCardless
		GoCardless::set_account_details($account_details);
		
		if (isset($_GET['resource_id']) && isset($_GET['resource_type'])) {
		  // Get vars found so let's try confirming payment
		
		  $confirm_params = array(
			'resource_id'   => $_GET['resource_id'],
			'resource_type' => $_GET['resource_type'],
			'resource_uri'  => $_GET['resource_uri'],
			'signature'     => $_GET['signature']
		  );
		  
		  // State is optional
		  if (isset($_GET['state'])) {
			$confirm_params['state'] = $_GET['state'];
		  }
		
		  $confirmed_resource = GoCardless::confirm_resource($confirm_params);
	  
		  if( isset($confirmed_resource->id)) { // Successfull
		     
			$invoiceId = $_SESSION['invoice_id']; 
		   
			// Set to paid
		    $invoiceModel->updateGocardlessPayment( $confirmed_resource,  $_SESSION['invoice_id']); 
			$this->sendInvoice( $invoiceId );
 			$this->render("paynow");
			 unset($_SESSION['invoice_id']);
		  }
		  else {
		  	$this->redirect(array("invoice/index/".$_SESSION['invoice_id']));
		  	
		  }exit;
		}
		
         /*************** *****************
		   Payments Arrays 
		  *********************************/	
			// Onetime Payment
			if($duration == 0)     
			{
				$payment_details = array(
				  'amount'  => $payment[0]['payment_amount'],
				  'name'    => 'Invoice Payment'
				  
				);
				
			   $payment_url = GoCardless::new_bill_url($payment_details);
			}
			//Need a subscription
			elseif($duration > 0 && $type == 'subscription') 
			{
				$payment_details = array(
				  'amount'          => $payment[0]['payment_amount'],
				  'interval_length' => $duration,   
				  'interval_unit'   => 'month',
				  'name'            => 'Monthly Subscription',
				);
				$payment_url = GoCardless::new_subscription_url($payment_details);
			}
			//Need a Pre Authorization
			elseif($duration > 0 && $type == 'authorization') 
			{
				# - Amount Taken from Customer
				$authorization_amount = Yii::app()->request->getParam('amount','0');
				if($authorization_amount >= $payment[0]['payment_amount'])
					$amount = $authorization_amount;
				else
					$amount = $payment[0]['payment_amount'];
					
				$payment_details = array(
				  'max_amount'      => $amount,
				  'interval_length' => $duration,   
				  'interval_unit'   => 'month',
				  'name'            => 'Pre-Authorization',
				);
				$payment_url = GoCardless::new_pre_authorization_url($payment_details);
			}	
				# - Redirecting it to Payment Gateway - GoCardless
				$this->redirect( $payment_url );
		}
		
	}
	// It will send Invoice in PDF form
	public function sendInvoice( $invoice_id )
	{
		$invoiceModel  = new Invoices;
		$customerModel = new Customers;
		$invoice = $invoiceModel->find("pk_invoice_id = ".$invoice_id);
		$customer = $customerModel->find('pk_customer_id = '.$invoice->fk_customer_id);
		
 		$body = "<p> Thank You for your payment made for purchase of ".$invoice->invoice_description.". 
		 			  Please find attached fully paid invoice No.<b>".$invoice_id ."</b>. </p> 
		 			  <p> You have paid <b> &pound;". $invoice->invoice_total."</b> through Direct Debit on ".date("d/m/Y H:i:s").".</p>";			  
 		 # Email Template
		// $template = $template = $this->renderPartial("mail_register", null, true);
		 $template = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html");
		 $template = str_replace("#from_name#", $customer->firstname.' '. $customer->lastname, $template);  
		 $template = str_replace("#message#", $body, $template);
		 
		  
		  # ======================================================================= 
		 # Send PDF file in attachment                                             
		 # =======================================================================
		  $email_message = $template;
		  $file  = "downloads/invoice_".$invoice_id.".pdf";
		  $invoicef = file_get_contents(Yii::app()->getBaseUrl(true).'/index.php/invoice/viewpdf/id/'.$invoice_id);
  		  $ftp = fopen($file, "w");
		  fwrite($ftp, $invoicef);
		  fclose($ftp); 
		  
		$from_name='Centrica IT';
    	$from_mail='support@centrica-it.com';
    	$replyto='support@centrica-it.com';
    	$filename= basename($file);
    
    	$file_size = filesize($file);
    	$handle = fopen($file, "r");
    	$content = fread($handle, $file_size);
    	fclose($handle);
    	$content = chunk_split(base64_encode($content));
    	$uid = md5(uniqid(time()));
		$name = basename($file);
		$header = "From: ".$from_name." <".$from_mail.">\r\n";
		$header .= "Reply-To: ".$replyto."\r\n";
		$header .= "MIME-Version: 1.0\r\n";
		$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
		$header .= "This is a multi-part message in MIME format.\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-type:text/html; charset=iso-8859-1\r\n";
		$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
		$header .= $email_message."\r\n\r\n";
		$header .= "--".$uid."\r\n";
		$header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
		$header .= "Content-Transfer-Encoding: base64\r\n";
		$header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
		$header .= $content."\r\n\r\n";
		$header .= "--".$uid."--";
		   
		  
		  //$name='=?UTF-8?B?'.base64_encode("Centrica-IT").'?=';
		  $subject='=?UTF-8?B?'.base64_encode('Your payment with Centrica IT Successfully Completed').'?=';
		  #. 1. Email to current user
		  @mail($customer->email, $subject, $email_message, $header, "-f support@centrica-it.com");
		  
		  #. 2. Email to Admin user
		  if($customer->parent_id > 0) 
		  {
		  	$customer2 = $customerModel->find('pk_customer_id = '.$customer->parent_id);
			$template = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html"); 
		 	$template = str_replace("#from_name#",$customer2->firstname.' '. $customer2->lastname, $template);  
			$body = "<p> Thank You for payment made for purchase of ".$invoice->invoice_description." by ".
			$customer->firstname .' '.$customer->lastname.'.  
		 			  Please find attached fully paid invoice No.<b>'.$invoice_id .'</b>. </p> 
		 			  <p> You have paid <b> &pound;'. $invoice->invoice_total."</b> through Direct Debit on ".date("d/m/Y H:i:s").".</p>";
					  
		 	$template = str_replace("#message#", $body, $template);
  	        @mail($customer2->email, $subject, $email_message, $header, "-f support@centrica-it.com");
  		  }
		  
		 # 3. Email to Support 
		 $template = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html"); 
		 $template = str_replace("#from_name#","Support", $template); 
		 # 3. send to support personal
		  $body = "<p> A new payment has been made for purchase of ".$invoice->invoice_description.". 
		 			  Please find attached fully paid invoice No.<b>".$invoice_id ."</b>. </p> 
		 			  <p> ".$customer->firstname .' '.$customer->lastname." has paid <b> &pound;". $invoice->invoice_total."</b> through Credit Card on ".date("d/m/Y H:i:s").". Other details are as follows:</p>";
		  $body .=  '<p> Username: '.$customer->username.'</p>
						<p> Email: '.$customer->email.'</p>
						<p> Telephone: '.$customer->phone.'</p>';	
						 
		 $template = str_replace("#message#", $body, $template);
		 @mail('support@centrica-it.com', "A new successful payment made", $email_message, $header, "-f support@centrica-it.com");
		
	}
	
	public function sendDirectLink($invoice_id)
	{
		$invoiceModel  = new Invoices;
		$customerModel = new Customers;
		$invoice = $invoiceModel->find("pk_invoice_id = ".$invoice_id);
		$customer = $customerModel->find('pk_customer_id = '.$invoice->fk_customer_id);
		
		$body = "<p> ".$customer->firstname." ".$customer->lastname ." has sent you a PAY BY LINK payment request for 
		". $invoice->invoice_description.". invoice #".$invoice_id .". </p>"  ;
		
		 $headers = "From: Support <support@centrica-it.com>\r\n".
					"Reply-To: support@centrica-it.com\r\n";
          $headers .= "\nMIME-Version: 1.0\n" .
            "Content-Type: text/html;\n";
			
	    @mail("support@centrica-it.com", "Pay by Link Payment Request", $body, $headers, "-f support@centrica-it.com");

		 			  
	}
	public function cardsuccess()
	{
		if( isset($_SESSION['invoice_id']) )
		{
			$invoiceModel  = new Invoices;
			$update		= $invoiceModel->updateToPaid( $_SESSION['invoice_id'] );
			unset($invoiceModel);
			 
		}
	}
	
	/*
		Cart Item Payment
	*/
	public function actionCartPayment()
	{   
	    # -  Get Visitor Cart Items for Listing
		$model = new Store;	
 	    $cartItem = $model->getAllCartItem();
 		
		# Create invoice for the subscriptions
		$invoiceModel  = new Invoices;
		$invoice_id    = $invoiceModel->createInvoiceByCart( $cartItem );
		 
 		# Create Subscription for each Item and create relation with the invoice
		$subModel	   = new Subscriptions;
		$subs          = $subModel->createSubscriptionByCart( $invoice_id, $cartItem );
		
		$_SESSION['invoice_id'] = $invoice_id;
 		$this->render("cartPayment", array("cartItems"=>$cartItem));
	}
   
   
   
   public function createLinkTicket($invoice_id, $method)
   
   {
		$model 		 = new Tickets;
		$customerModel = new Customers;
		$custInfo = $customerModel->find('pk_customer_id = '.Yii::app()->user->pk_customer_id);
		
 		$url = Yii::app()->getBaseUrl(true).'/index.php/ticket/create/';
		$department = 3; // customer support
		$priority = 4; // urgent
		$subject = "Pay By Link request using ".$method;
		$message = Yii::app()->user->id ." has created a Pay By Link request using ".$method . ' for invoice No: '.$invoice_id;
		
 		 
        # Setting remaining Important Fields
		$attach_id = 0; 
		$model->fk_customer_id =  Yii::app()->user->pk_customer_id;
		$model->mname		   =  Yii::app()->user->id; 
		$model->email		   =  Yii::app()->user->customer_email;
		$model->ipadd		   = $_SERVER['REMOTE_ADDR'];
 		$model->date		   =  time();
		$model->did            =  3;
		$model->dname          = "Customer support";	
		$model->subject = $subject;		
		$model->priority = $priority;
		$model->message = $message;
		if( $model->save() )
		{
				# ================================================================
				# Create customer in Members Table of the Trellis database as well
				# ================================================================
				$model->saveInTrellis();
				$model->updateMemId(); // Update Member ID in Tickets
 				# 1 Send Email to Support
				/*$model->sendEmail("support@centrica-it.com",$model->subject, $model->message, 'Support');
				$body = "A Pay By Link request using ".$method." for invoice No: ".$invoice_id." has been placed successfully 
				by ".Yii::app()->user->fullname.". We'll shortly e-mail ".$custInfo->email." a payment link to collect payment. ";
						 
				# 2 To creator
				$model->sendEmail( Yii::app()->user->customer_email, $model->subject, $body,  "Customer");
				
				# 3 Email to Parent Customer
 				
				if( Yii::app()->user->parent_id > 0)
				{
					$ParentInfo  = $customerModel->find("pk_customer_id = ".Yii::app()->user->parent_id); 
 					$model->sendEmail( $ParentInfo->email, $model->subject, $body, "Customer");
				}
	 		  */
 			}		  
	} 
}

?>