<?php

class OrderController extends Controller
{
	var $status = array("0"=>"Pending", "1"=>"Completed", '2'=>"Processing");
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
				'actions'=>array('index','trials','online','outstanding'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$model = new Subscriptions;
		$subscriptions = $model->getCustomerSubscriptions();
		$this->render('index', array("subscriptions"=>$subscriptions));
		 
	}
	
	
	public function actionOutstanding()
	{
		$model = new Subscriptions;
		$subscriptions = $model->getCustomerSubscriptions();
		$this->render('outstanding', array("subscriptions"=>$subscriptions));
		 
	}
	
	
	public function actionTrials()
	{
		$model 	= new OrderTrails;
		$trials = $model->findAll("fk_customer_id ='".Yii::app()->user->pk_customer_id."' AND is_trial = '1'"); 
		$this->render('trials', array("trials"=>$trials));
	}
	public function actionOnline()
	{
		$this->layout='order-online';
		$this->render('online');
	}
	
	// get Domain url by ID
	public function getDomainById( $id )
	{
		$model 	   = new OrderTrails;
		$domainUrl = $model->getDomainUrl( $id );
		return $domainUrl; 

	}

	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/
}