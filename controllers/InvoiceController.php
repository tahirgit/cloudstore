<?php

class InvoiceController extends Controller
{
	// Invoice Unpaid status
	public $invoiceUpaidStatus = array('2'=>'Creditcard PaybyLink','3'=>'Creditcard PaybyPhone', '4'=>'DirectDebit PaybyLink');
	
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
				'actions'=>array('viewpdf', 'invoiceCron'),
				'users'=>array('*'),
			),
			
			array('allow', // allow authenticated users to access all actions
				'actions'=>array('index','viewInvoice','checkCreate'),
				'users'=>array('@'),
			),
			 
		);
	}
	
	public function actionIndex()
	{
 		$customer_id = Yii::app()->user->pk_customer_id;
		$model = new Invoices("search");
		$subscription_invoices = $model->getAllInvoices( $customer_id );
 		$this->render('index', array('model'=>$model,'subs_invoice'=>$subscription_invoices));
	}
	public function actionviewInvoice()
	{
		$model 			= new Invoices;
		$billing		= new Billings;
		$customer_id 	= Yii::app()->user->pk_customer_id;
		$invoice_id 	= Yii::app()->request->getParam('id','default');
		$invoice 		= $model->getInvoicesById( $invoice_id );
	
		$payment		= $model->getInvoicePayment( $invoice_id );
		$billing_info 	= $billing->find("fk_customer_id = '".$customer_id."'");
		$this->render('viewInvoice', array('invoice'=>$invoice, "billing_info"=>$billing_info, "payment"=>$payment));
	}
    
	public function actionviewpdf()
	{
		$model 			= new Invoices;
		$billing		= new Billings;
		$custModel    	= new Customers;
 		$invoice_id 	= Yii::app()->request->getParam('id','default');
 		 
		$invoice 		= $model->getInvoicesById( $invoice_id );
		 
		$payment		= $model->getInvoicePayment( $invoice_id );
		$billing_info 	= $billing->find("fk_customer_id = '".$invoice['invoice']->fk_customer_id."'");
		
		$cust_info     = $custModel->find('pk_customer_id = '.$invoice['invoice']->fk_customer_id);
		
		$mPDF1 = Yii::app()->ePdf->mpdf();
        # You can easily override default constructor's params
        $mPDF1 = Yii::app()->ePdf->mpdf('', 'A4');
        
 		# renderPartial (only 'view' of current controller)
        $mPDF1->WriteHTML($this->renderPartial('viewpdf',  array('invoice'=>$invoice, "billing_info"=>$billing_info, "payment"=>$payment, 'customer_info'=>$cust_info), true));
         # Outputs ready PDF
         $mPDF1->Output();
 
      
	}
	// Uncomment the following methods and override them if needed
	
	/*
		check invoice if created for shopping cart payment and create if doesnt exist
	*/
	public function actionCheckCreate()
	{
		if( !isset($_SESSION['invoice_id']) ) {
			$model = new Invoices;
			$invoice_id = $model->createCartItemInvoice();
			$_SESSION['invoice_id'] = $invoice_id;
		}
		exit;
		//return $this->render("paymentForm");
	}
	
	/*
		Cron script to create monthly invoices, Execute daily
	*/
	 public function actionInvoiceCron()
	 {
	 	$model = new Invoices;
		$invoices = $model->findAll("next_invoice_date = '".date("Y-m-d")."'");
		$i = 0;
		if(!empty($invoices)) { 
			
			foreach( $invoices as $inv )
			{
				$flag = $model->createInvoiceFromInvoice($inv);
				$i++;
			}
		}
		echo $i.' invoice created successfully';
	 }
}