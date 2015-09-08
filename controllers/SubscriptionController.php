<?php

class SubscriptionController extends Controller
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
				'actions'=>array('index','view', 'completed'),
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

	/*
		* View Subscription Details
	*/ 
	public function actionView()
	{
		$sub_id = Yii::app()->request->getParam("id", "default");
		// create instance of model and get subscription details
		$model = new Subscriptions;
  		$subscription = $model->getSubscriptionByID($sub_id);
 		$this->render('view', array("subscription"=>$subscription));
		
	}
	
	public function actionCompleted()
	{
		$model = new Subscriptions;
		$subscriptions = $model->getCustomerSubscriptions();
		$this->render('completed', array("subscriptions"=>$subscriptions));
	}
}