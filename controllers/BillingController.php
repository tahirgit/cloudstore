<?php

class BillingController extends Controller
{
	
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
				'actions'=>array('index'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	/*
		Manage Billing Profile
	*/
	public function actionIndex()
	{
		$customer_id = Yii::app()->user->pk_customer_id;
		$customer_model	 = new Customers;
		$billing_model   = new Billings;
		//$customer_model->scenario = "billing";
		$billing_model->scenario  = "billing";
 		
		$customerInfo   = $customer_model->find('pk_customer_id = '.$customer_id);
		
		$profile     	 = $customer_model->find('pk_customer_id = "'.$customer_id.'" or pk_customer_id = '.$customerInfo->parent_id); 
		
		/* Child Accounts Setting */
		 $related_customer = $customer_model->getAllRelatedCustomers( $customer_id );
		 
		if($customerInfo->parent_id == 0)
		$billing = $billing_model->find('fk_customer_id = "'.$customer_id.'"');
		else
		$billing = $billing_model->find('fk_customer_id = "'.$customerInfo->parent_id.'"');
		
		if(count($billing) < 1)
		{
 			$billing = $billing_model;
		} 
		
		/*
			if Submit Form for Update
		*/
		if( isset($_POST['Customers'])  or isset($_POST['Billings']) )
		{ 
			 $customer_model->attributes = $_POST['Customers']; 
 			 $billing_model->attributes  = $_POST['Billings'];
			 
			//if($customer_model->validate() && $this->validate())
			if($billing_model->validate())
 			{	
				$customer_model->updateByPk($customer_id, $_POST['Customers']);
				$billing_model->updateByPk($_POST['billing_id'], $_POST['Billings']);
				
				$subject = "Billing profile updated successfully";
				$message = "Billing profile has been updated successfully by ".Yii::app()->user->fullname;
				
				Yii::app()->user->setFlash('success', "Billing updated Successfully!");	
				$this->redirect(array("billing/index")); 
 			}
		}
		
		
	 
		$this->render('index', array('personal_info'=> $profile, 'billing_profile'=>$billing, "related_customers"=>$related_customer,  
		"model"=>$billing_model,"customer_model"=>$customer_model));
	}

	 
}