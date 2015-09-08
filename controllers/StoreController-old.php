<?php
class StoreController extends Controller
{
	
	# - Listing Services
	public function actionIndex()
	{
		$this->layout = "store";
		$this->render('index');
	}
	
	# - Virtual Domain Service  
	public function actionVdi() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		$packageSelect = Yii::app()->request->getParam("order", '0');
		if($packageSelect != '0')
		{
			//if(  !isset($_SESSION['service_vds']) ) {
				# - Store in Cart table
 				
				$model->saveInCart('service_vds', $packageSelect, $_POST);
				$_SESSION['service_vds'] = 1;
				$_SESSION['cart'] = 1;
 				$this->redirect(array("store/viewCart"));
			 
  		}
		$vdiService = $model->getService('vdi');
		$this->render("vdi", array("vdiService"=>$vdiService));
	}
	
	# - VDI package Detail page
	public function actionVdiDetail()
	{
		$this->layout = "store";
		$vdi_id = Yii::app()->request->getParam('id', '0');
		$model  = new Cloud;
		$vdiService = $model->getService('vdi');
		//echo '<pre>'; print_r($vdiService); exit;
		$this->render("vdiDetail", array("vdiService"=>$vdiService, "vdi_id"=>$vdi_id));

	}
	
	# - Get Component Info
	public function getComponent($category, $service)
	{
		$model = new Cloud;
		return $model->getComponentInfo($category, $service);
	}
	
	
  # - Servers   
	public function actionServers() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		$packageSelect = Yii::app()->request->getParam("order", '0');
		if($packageSelect != '0')
		{ 
 				$model->saveInCart('service_vds', $packageSelect, $_POST);
				$_SESSION['service_vds'] = 1;
				$_SESSION['cart'] = 1;
 				$this->redirect(array("store/viewCart"));
   		}
		
		$serverService = $model->getService('server');
 		$this->render("servers", array("serverService"=>$serverService));
	}
	
	# - server package Detail page
	public function actionServerDetail()
	{
		$this->layout = "store";
		$server_id = Yii::app()->request->getParam('id', '0');
		$model  = new Cloud;
		$serverService = $model->getService('server');
 		$this->render("serverDetail", array("serverService"=>$serverService, "server_id"=>$server_id));

	}
	# - Virtual Domain Service  
	public function actionMailbox() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		$packageSelect = Yii::app()->request->getParam("order", '0');
		if($packageSelect != '0')
		{
  				
				$model->saveInCart('service_mailbox', $packageSelect, $_POST);
				$_SESSION['service_mailbox'] = 1;
				$_SESSION['cart'] = 1;
 				$this->redirect(array("store/viewCart"));
   		}
		
		$mailbox = $model->getServiceTbName('service_mailbox');
		$this->render("mailbox", array("mailbox"=>$mailbox));
	}
	
	# - cPanel Service  
	public function actionCpanel() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		$packageSelect = Yii::app()->request->getParam("order", '0');
		if($packageSelect != '0')
		{
  				
				$model->saveInCart('service_cpanel', $packageSelect, $_POST);
				$_SESSION['service_cpanel'] = 1;
				$_SESSION['cart'] = 1;
 				$this->redirect(array("store/viewCart"));
   		}
		
		$cpanel = $model->getServiceTbName('service_cpanel');
		$this->render("cpanel", array("cpanel"=>$cpanel));
	}
	
	# - online backup Service  
	public function actionBackup() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		$packageSelect = Yii::app()->request->getParam("order", '0');
		if($packageSelect != '0')
		{
  				
				$model->saveInCart('service_backup', $packageSelect, $_POST);
				$_SESSION['service_backup'] = 1;
				$_SESSION['cart'] = 1;
 				$this->redirect(array("store/viewCart"));
   		}
		
		$backup = $model->getServiceTbName('service_backup');
		$this->render("backup", array("backup"=>$backup));
	}
	
	# - VOIP Service  
	public function actionVoip() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		$packageSelect = Yii::app()->request->getParam("order", '0');
		if($packageSelect != '0')
		{
  				
				$model->saveInCart('service_voip', $packageSelect, $_POST);
				$_SESSION['service_voip'] = 1;
				$_SESSION['cart'] = 1;
 				$this->redirect(array("store/viewCart"));
   		}
		
		$voip = $model->getServiceTbName('service_voip');
		$this->render("voip", array("voip"=>$voip));
	}
	
	# - CRM Service  
	public function actionCrm() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		$packageSelect = Yii::app()->request->getParam("order", '0');
		if($packageSelect != '0')
		{
  				
				$model->saveInCart('service_crm', $packageSelect, $_POST);
				$_SESSION['service_crm'] = 1;
				$_SESSION['cart'] = 1;
 				$this->redirect(array("store/viewCart"));
   		}
		
		$crm = $model->getServiceTbName('service_crm');
		$this->render("crm", array("crm"=>$crm));
	}
	
	
	/*
		SHOPING CART LISTING 
	*/
	# - Shopping Cart Items Listing
	public function actionViewCart()
	{	
		$model = new Store;	
		
		# - Remove Item Request
		$packageType = Yii::app()->request->getParam("remove", "0");
		$tablename   = Yii::app()->request->getParam("tb", "0");  // base64 Encoded string
		if( $packageType != '0' && $tablename != '0')
		{ 
			$model->removeCartItem( $packageType, base64_decode($tablename) );
			$this->redirect(array("store/viewCart"));
		}
		
		# - Cart Quantity Update
		if( isset($_POST['update_cart']) && isset($_POST['quantity']))
		{
			
			foreach($_POST['quantity'] as $key => $value)
			{
				foreach($value as $type=>$quntity)
				{
					if($quntity > 0)
					$model->updateCart($key, $type, $quntity);
				}
			}
			
		}
		# -  Get Visitor Cart Items for Listing
	    $cartItem = $model->getAllCartItem();
		$_SESSION['cart_item_count'] = count($cartItem);
		$_SESSION['cart_total'] = $model->getCartInfo();
		$this->layout = "store";
 		$this->render("viewcart", array("cartItems"=>$cartItem));
			
	}
}
?>