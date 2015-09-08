<?php

class StoreController extends Controller
{
	public $onlineBackup = array("pc25"=>'12.49', 'pc50'=>'22.99', 'pc100'=>'41.99', 'pc250'=>'94.99', 'pc500'=>'169.99', 
	"srvr25"=>'24.99', 'srvr50'=>'44.99', 'srvr100'=>'79.99', 'srvr250'=>'174.99', 'srvr500'=>'299.99');
	
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
		
		$vdiService = $model->getService('vdi');
		$this->render("vdi", array("vdiService"=>$vdiService));
	}
	
	# - VDI package Detail page
	public function actionVdiDetail()
	{
		$this->layout = "store";
		$model  = new Cloud;
		$store  = new Store;
		
		$vdi_id = Yii::app()->request->getParam('id', '0');
		if($vdi_id == 0)
		$this->redirect(array("store/index"));
		
 		$vdiService = $model->getService('vdi');
		# Editing Cart Item if
		$cart_id = Yii::app()->request->getParam('edit', '0');
		$cartItem =  $store->getCartItemInfo($cart_id);
		
		$this->render("vdiDetail", array("vdiService"=>$vdiService, "vdi_id"=>$vdi_id, "cart_id"=>$cart_id, 'cartItem'=>$cartItem));

	}
	
	# - Get Component Info
	public function getComponent($category, $service)
	{
		$model = new Cloud;
		return $model->getComponentInfo($category, $service);
 	}
	
	# - Get Custom Component Info
	public function getCustomServices($service)
	{
		$model = new Cloud;
		$service_id = Yii::app()->request->getParam('id', '0');
		//$CustomServices = $model->getCustomServices('server');
		//$this->render("serverDetail", array('CustomServices'=>$CustomServices));
		return $model->getCustomServicesInfo($service, $service_id);
 	}
  # - Servers   
	public function actionServers() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		
		$serverService = $model->getService('server');
 		$this->render("servers", array("serverService"=>$serverService));
		
	}
	
	# - server package Detail page
	public function actionServerDetail()
	{
		$store = new Store;
		$this->layout = "store";
		$server_id = Yii::app()->request->getParam('id', '0');
		if($server_id == 0)
		$this->redirect(array("store/index"));
		
		$model  = new Cloud;
		$serverService = $model->getService('server');
		# Editing Cart Item if
		$cart_id = Yii::app()->request->getParam('edit', '0');
		$cartItem =  $store->getCartItemInfo($cart_id);
		
		$this->render("serverDetail", array("serverService"=>$serverService, "server_id"=>$server_id,"cart_id"=>$cart_id, 'cartItem'=>$cartItem));
	
	}
	
	# - Servers   
	public function actionVdc() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		
		$vdcService = $model->getService('vdc');
 		$this->redirect(array("store/vdcDetail/id/".$vdcService['vdc'][0]['id']));
		//$this->render("vdc", array("vdcService"=>$vdcService));
	}
	
	# - VDC package Detail page
	public function actionVdcDetail()
	{
		$store = new Store;
		$this->layout = "store";
		$vdc_id = Yii::app()->request->getParam('id', '0');
		if($vdc_id == 0)
		$this->redirect(array("store/index"));
		
		$model  = new Cloud;
		
		# Editing Cart Item if
		$cart_id = Yii::app()->request->getParam('edit', '0');
		$cartItem =  $store->getCartItemInfo($cart_id);
		
		$vdcService = $model->getService('vdc');
 		$this->render("vdcDetail", array("vdcService"=>$vdcService, "vdc_id"=>$vdc_id ,"cart_id"=>$cart_id,'cartItem'=>$cartItem));

	}
	# - Mailbox service  
	public function actionMailbox() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		
		$mailboxService = $model->getService('mailbox');
		$this->render("mailbox", array("mailboxService"=>$mailboxService));
	}
		# - Mailbox package Detail page
	public function actionMailboxDetail()
	{
		$this->layout = "store";
		$store = new Store;
		$mail_id = Yii::app()->request->getParam('id', '0');
		
		if($mail_id == 0)
		$this->redirect(array("store/index"));
		
		# Editing Cart Item if
		$cart_id = Yii::app()->request->getParam('edit', '0');
		$cartItem =  $store->getCartItemInfo($cart_id);
 		 
		$model  = new Cloud;
		$mailbox = $model->getService('mailbox');
 		$this->render("mailboxDetail", array("mailboxService"=>$mailbox, "mail_id"=>$mail_id, "cart_id"=>$cart_id, 'cartItem'=>$cartItem));

	}
	# - cPanel Service  
	public function actionCpanel() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		
		$cpanel = $model->getService('cpannel');
		$this->render("cpanel", array("cpanelService"=>$cpanel));
	}
	
	# - Cpanel package Detail page
	public function actionCpanelDetail()
	{
		# - Request for Edit Package
		 
		 # Editing Cart Item if
		$this->layout = "store";
		$store = new Store;
		$cart_id = Yii::app()->request->getParam('edit', '0');
		$cartItem =  $store->getCartItemInfo($cart_id);
		
		$model = new Cloud;
 		$cpanel_id = Yii::app()->request->getParam('id', '0');
		if($cpanel_id == 0)
		$this->redirect(array("store/index"));
		
		$cpanel = $model->getService('cpannel');
 		$this->render("cpanelDetail", array("cpanelService"=>$cpanel, "cpanel_id"=>$cpanel_id, "cart_id"=>$cart_id, 'cartItem'=>$cartItem));
		
		
		# Store All in Cart's tables
	  /*  $model = new Store;
		
  		$model->saveOrder( 0, $cpanel['cpannel'][$cpanel_id-1]['service_name'], $cpanel['cpannel'][$cpanel_id-1]['package_name'], $cpanel_id, $cpanel['cpannel'][$cpanel_id-1]['price'], 0, $cpanel['cpannel'][$cpanel_id-1]['price'], array());
		$this->redirect( array("store/viewCart/") );*/
		
 	}
	
	
	# - voip  Service  
	public function actionVoip() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		
		$voip = $model->getService('voip');
		$this->redirect(array("store/voipDetail/id/".$voip['voip'][0]['id']));
 		//$this->render("voip", array("voipService"=>$voip));
	}
	# - VOIP package Detail page
	public function actionVoipDetail()
	{
		$this->layout = "store";
 		$voip_id = Yii::app()->request->getParam('id', '0');
		if($voip_id == 0)
		$this->redirect(array("store/index"));
		
		$model  = new Cloud;
		$store = new Store;
		$voip = $model->getService('voip');
		
		# Editing Cart Item if
		$cart_id = Yii::app()->request->getParam('edit', '0');
		$cartItem =  $store->getCartItemInfo($cart_id);
		
 		$this->render("voipDetail", array("voipService"=>$voip, "voip_id"=>$voip_id,  "cart_id"=>$cart_id, 'cartItem'=>$cartItem));

	}
	# - online backup Service  
	public function actionBackup() 
	{  
			$this->layout = "store";
 			$this->render("backup");
	}
	# - Mailbox package Detail page
	public function actionBackupDetail()
	{
		$this->layout = "store";
		$back_id = Yii::app()->request->getParam('pack', '0');
		/*if($back_id == 0)
		$this->redirect(array("store/index"));*/
		# Editing Cart Item if
		$store = new Store;
		$cart_id = Yii::app()->request->getParam('edit', '0');
		$cartItem =  $store->getCartItemInfo($cart_id);
		
  		$this->render("backupDetail", array("back_id"=>$back_id, 'cart_id'=>$cart_id, "cartItem"=>$cartItem));

	}
	
	# - VOIP Service  
/*	public function actionVoip() 
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
	}*/
	
	# - CRM Service  
	public function actionCrm() 
	{  
	    $model = new Cloud;
		$this->layout = "store";
		
		
		$crm = $model->getServiceTbName('service_crm');
		$this->render("crm", array("crm"=>$crm));
	}
	
	
	/*
		SHOPING CART LISTING 
	*/
	
	public function actionCheckout()
	{  
		$request = Yii::app()->request;
       	$serviceName = $request->getPost('servicesName');
	    $package_type = $request->getPost('package_type');
		$subtotal = $request->getPost('subtotal');
		$customizeTotal = $request->getPost('customizeTotal');
        $totalAmount = $request->getPost('totalAmount');
		$package_id = $request->getPost('package_id');
		# - To Edit a cart Item
		$cart_id = $request->getPost('cart_id', '0');
		
		$customize = array();
		
		# Service Discount value
		if($request->getPost('service_discount')) {  
			$customize['Discount']['value'] = $request->getPost('service_discount').'%';
			$customize['Discount']['unit_price'] = $request->getPost('service_discount');
		}
		
		# Domain name for Cpanel & VDI Detail
		if($request->getPost('domainName')) {  
			$customize['Domain Name']['value'] = $request->getPost('domainName');
			$customize['Domain Name']['unit_price'] = $request->getPost('domainName');
		}
		# Domain name for  VDI's mailbox
		if($request->getPost('mailboxdomainName')) {  
			$customize['Mailbox Domain Name']['value'] = $request->getPost('mailboxdomainName');
			$customize['Mailbox Domain Name']['unit_price'] = 0;
		}
		# Smal,large,& ex-large packages (junior)
		if($request->getPost('pkg_add')) {  
			$customize['Package type']['value'] = $request->getPost('addpkg');
			$customize['Package type']['unit_price'] = $request->getPost('pkg_add');
		}
	   # For number of users (Number of accounts found)
		if($request->getPost('user_account')) {	
			$customize['Number of VDI User(s)']['value'] = $request->getPost('user_account'); 
			$customize['Number of VDI User(s)']['unit_price'] = 0;
		}
		# For number of MAilbox for number of users of vdi (Number of mailbox found)
		if($request->getPost('number_of_mailbox')) {	
			$customize['Number of Mailbox(s)']['value'] = $request->getPost('number_of_mailbox'); 
			$customize['Number of Mailbox(s)']['unit_price'] = 0;
			//echo '<pre>'; print_r($customize); exit;
			
		}
		
		# For number of servers (Number of accounts found)
		if($request->getPost('server_account')) {	
			$customize['Number of Server(s)']['value'] = $request->getPost('server_account'); 
			$customize['Number of Server(s)']['unit_price'] = 0;
		}
		
		# For number of Mailbox (Number of mailbox found)
		if($request->getPost('mail_box')) {	
			$customize['Number of mailbox']['value'] = $request->getPost('mail_box'); 
			$customize['Number of mailbox']['unit_price'] = 0;
		}
		
		# CPU
		if($request->getPost('addcpu')) {	
			$customize['cpu']['value'] = $request->getPost('addcpu') . ' MHz'; 
			$customize['cpu']['unit_price'] = $request->getPost('cpu_unit_price');
		}
		
	
		
		
		# COre
		if($request->getPost('addcore')) {	
			$customize['core(s)']['value'] = $request->getPost('addcore'); 
			$customize['core(s)']['unit_price'] = 0;
		}
		
		#Ram
		if($request->getPost('addram')) {
			$customize['ram']['value'] = $request->getPost('addram') . ' GBytes';
			$customize['ram']['unit_price'] = $request->getPost('ram_unit_price');
		}
		
		#Tier 3 - Tier 2 and Tier 1 Drive
		if($request->getPost('drivesVal')) {
			$customize['Additional Storage']['value'] = $request->getPost('drivesVal') . ' GBytes '.$request->getPost('drives');
			$customize['Additional Storage']['unit_price'] = $request->getPost('drive_unit_price');
		}
		#add number of OS cores
		if($request->getPost('osVal')) {	
			$customize['OS']['value'] = $request->getPost('osVal'); 
			$customize['OS']['unit_price'] =  $request->getPost('os_unit_price');
			
		}
		# add mailbox in vdi (junior)
		if($request->getPost('addmailbox')) {
			$customize['per user']['value'] = $request->getPost('mailboxVal') . ' Mailbox(s) '.$request->getPost('mailbox');
			//echo '<pre>'; print_r($customize); exit;
			$customize['per user']['unit_price'] = $request->getPost('mailbox_unit_price');
		}
		# add mailbox Storage per vdi in vdi detail(junior)
		if($request->getPost('addstm')) {
			$customize['Additional Storage per Mailbox']['value'] = $request->getPost('add_stVal') . ' GBytes '.$request->getPost('add_st');
			$customize['Additional Storage per Mailbox']['unit_price'] = $request->getPost('addstm_unit_price');
		}
				
		# Operating System
		if($request->getPost('addos')) {  
			$customize['os']['value'] = $request->getPost('addos');
			$customize['os']['unit_price'] = $request->getPost('os_add');
		}
		#secutrity -McAfee Enterprise Virus Scan
 		if($request->getPost('Macfee_value')) { 
			$customize['SECUrity']['value'] = $request->getPost('Macfee');
			$customize['SECUrity']['unit_price'] = $request->getPost('Macfee_value');
			 echo '<pre>'; print_r($customize); exit;
		}
		#secutrity -intrusion Protection
 		if($request->getPost('IntProt')) { 
			$customize['SECurity']['value'] = $request->getPost('IntProt');
			$customize['SECurity']['unit_price'] = $request->getPost('IntProt_value');
		}
		#secutrity servers (table number one)
 		if($request->getPost('addsecurity')) { 
			$customize['SECURITY']['value'] = $request->getPost('addsecurity');
			$customize['SECURITY']['unit_price'] = $request->getPost('security_value');
		}
		#secutrity servers1 (table number two)
 		if($request->getPost('addsecurity1')) { 
			$customize['Security']['value'] = $request->getPost('addsecurity1');
			$customize['Security']['unit_price'] = $request->getPost('security1_value');
		}
		#security vdi
 		if($request->getPost('addsecurity_0')) { 
			$customize['security']['value'] = $request->getPost('addsecurity_0');
			$customize['security']['unit_price'] = $request->getPost('security_value_0');
		}
		if($request->getPost('addsecurity_1')) { 
			$customize['security ']['value'] = $request->getPost('addsecurity_1');
			$customize['security ']['unit_price'] = $request->getPost('security_value_1');
		}
		
		# Support
 		if($request->getPost('addsupport_0')) {
			$customize['support']['value'] = $request->getPost('addsupport_0');
			$customize['support']['unit_price'] = $request->getPost('support_value_0');
		}
		if($request->getPost('addsupport_1')) {
			$customize['support ']['value'] = $request->getPost('addsupport_1');
			$customize['support ']['unit_price'] = $request->getPost('support_value_1');
		}
		if($request->getPost('addsupport_2')) {
			$customize['support  ']['value'] = $request->getPost('addsupport_2');
			$customize['support  ']['unit_price'] = $request->getPost('support_value_2');
		}
		if($request->getPost('addtech')) {
			$customize['Support']['value'] = $request->getPost('addtech');
			$customize['Support']['unit_price'] = $request->getPost('techVal');
		}
		# For number bandwidth
		if($request->getPost('bandwidth')) {	
			$customize['Bandwidth']['value'] = $request->getPost('bandwidth').' MBytes of '; 
			$customize['Bandwidth']['unit_price'] = $request->getPost('bandwidth_unit_price');
		}
		# For number transferrate
		if($request->getPost('transrate')) {	
			$customize['Transfer Rate']['value'] = $request->getPost('transrate').' GBytes of '; 
			$customize['Transfer Rate']['unit_price'] = $request->getPost('transrate_unit_price');
		}
		# Networking System Additional Public IP Address
		if($request->getPost('addNetOne')) {  
			$customize['Networking']['value'] = $request->getPost('addNetOne');
			$customize['Networking']['unit_price'] = $request->getPost('networkOne');
		}
		# Networking System private vLAN
		if($request->getPost('addNetTwo')) {  
			$customize['Networking ']['value'] = $request->getPost('addNetTwo');
			$customize['Networking ']['unit_price'] = $request->getPost('networkTwo');
		}
		# Networking System  load balancers
		if($request->getPost('addNetThree')) {  
			$customize['Networking  ']['value'] = $request->getPost('addNetThree');
			$customize['Networking  ']['unit_price'] = $request->getPost('networkThree');
		}
		# Networking System DNS record Per host
		if($request->getPost('addNetF')) {  
			$customize['Networking   ']['value'] = $request->getPost('addNetF');
			$customize['Networking   ']['unit_price'] = $request->getPost('networkF');
		}
	 	#Desktop Application  - mailbox
		if($request->getPost('addapplication_0')) { 
			$customize['application']['value'] = $request->getPost('addapplication_0');
			$customize['application']['unit_price'] = $request->getPost('application_value_0');
		}
		if($request->getPost('addapplication_1')) { 
			$customize['application ']['value'] = $request->getPost('addapplication_1');
			$customize['application ']['unit_price'] = $request->getPost('application_value_1');
			//echo '<pre>'; print_r($customize); exit;
		}
		if($request->getPost('addapplication_2')) { 
			$customize['application  ']['value'] = $request->getPost('addapplication_2');
			$customize['application  ']['unit_price'] = $request->getPost('application_value_2');
			//echo '<pre>'; print_r($customize); exit;
		}
		if($request->getPost('addapplication_3')) { 
			$customize['application   ']['value'] = $request->getPost('addapplication_3');
			$customize['application   ']['unit_price'] = $request->getPost('application_value_3');
			//echo '<pre>'; print_r($customize); exit;
		}
		if($request->getPost('addapplication_4')) { 
			$customize['application    ']['value'] = $request->getPost('addapplication_4');
			$customize['application    ']['unit_price'] = $request->getPost('application_value_4');
		}
		
		if($request->getPost('addapplication_5')) { 
			$customize['application     ']['value'] = $request->getPost('addapplication_5');
			$customize['application     ']['unit_price'] = $request->getPost('application_value_5');
			//echo '<pre>'; print_r($customize); exit;
		}
		# mailbox
		if($request->getPost('addpackage')) { 
			$customize['User Accounts']['value'] = $request->getPost('addpackage');
			$customize['User Accounts']['unit_price'] = $request->getPost('package_price');
		}
 		if($request->getPost('addstorage')) { 
			$customize['storage']['value'] = $request->getPost('addstorage')  . ' GBytes';
			$customize['storage']['unit_price'] = $request->getPost('storage_unit_price');
		}
		# coprate disclaimer -mailbox
		if($request->getPost('addCop')) { 
			$customize['Corporate Disclaimer']['value'] = '';
			$customize['Corporate Disclaimer']['unit_price'] = $request->getPost('Cop_add');
		}
		# DNS Management -mailbox
		if($request->getPost('addDns')) { 
			$customize['DNS Management']['value'] = '';
			$customize['DNS Management']['unit_price'] = $request->getPost('Dns_add');
		}
		# Integrated instant messaging service & client -mailbox
		if($request->getPost('addIIMS')) { 
			$customize['Integrated instant messaging service & client']['value'] = '';
			$customize['Integrated instant messaging service & client']['unit_price'] = $request->getPost('IIMS_add');
		}
		# Mail-Mobile (for all your android / apple requirements) -mailbox
		if($request->getPost('addMmobile')) { 
			$customize['Mail Mobile']['value'] = '';
			$customize['Mail Mobile']['unit_price'] = $request->getPost('Mmobile_add');
		}
		#Individual Mailbox Backup/Restore - mailbox
		if($request->getPost('addMailBckup')) { 
			$customize['Individual Mailbox Backup/Restore']['value'] = '';
			$customize['Individual Mailbox Backup/Restore']['unit_price'] = $request->getPost('MailBckup_add');
			// echo '<pre>'; print_r($customize); exit;
		}
		#Mail Valut - mailbox
		if($request->getPost('addMailValut')) { 
			$customize['Mail Vault']['value'] = '';
			$customize['Mail Vault']['unit_price'] = $request->getPost('MailValut_add');
			//echo '<pre>'; print_r($customize); exit;
		}
 		#servers
		if($request->getPost('addbackup')) { 
			$customize['data backup']['value'] = $request->getPost('addbackup') . ' GBytes';
			$customize['data backup']['unit_price'] = $request->getPost('backup_unit_price');
		}
		# database and application talble number one
		if($request->getPost('addDatabase')) { 
			$customize['Database']['value'] = $request->getPost('addDatabase');
			$customize['Database']['unit_price'] = $request->getPost('database_value');
		}
		# database and application table number two
		if($request->getPost('addDatabase1')) { 
			$customize['Database ']['value'] = $request->getPost('addDatabase1');
			$customize['Database ']['unit_price'] = $request->getPost('database1_value');
		}
		# database and application table number two
		if($request->getPost('addDatabase2')) { 
			$customize['Database  ']['value'] = $request->getPost('addDatabase2');
			$customize['Database  ']['unit_price'] = $request->getPost('database2_value');
		}
		if($request->getPost('addRecorvery')) { 
			$customize['Recovery']['value'] = $request->getPost('addRecorvery');
			$customize['Recovery']['unit_price'] = $request->getPost('addRecorvery');
		}
		# - Voip
	   /*if($request->getPost('addNumber')) { 
			$customize['TelePhone Number']['value'] = $request->getPost('addNumber');
			$customize['TelePhone Number']['unit_price'] = $request->getPost('number_value');
		}*/
		//Geographical number
		if($request->getPost('quantity_of_number')) {	
			$customize['Geographical Number(s)']['value'] = $request->getPost('quantity_of_number'); 
			$customize['Geographical Number(s)']['unit_price'] =  $request->getPost('geonumber_value');
			//echo '<pre>'; print_r($customize); exit;
		}
		//Geographical number 0845
		if($request->getPost('quantity_of_number_One')) { 
			$customize['Non-Geographical 0845 Number(s)']['value'] = $request->getPost('quantity_of_number_One');
			$customize['Non-Geographical 0845 Number(s)']['unit_price'] = $request->getPost('nongeonumOne_value');
		}
		//Geographical number 0843
		if($request->getPost('quantity_of_number_Two')) { 
			$customize['Non-Geographical 0843 Number(s)']['value'] = $request->getPost('quantity_of_number_Two');
			$customize['Non-Geographical 0843 Number(s)']['unit_price'] = $request->getPost('nongeonumTwo_value');
		}
		//Geographical number 0800
		if($request->getPost('quantity_of_number_E')) { 
			$customize['Non-Geographical 0800 Number(s)']['value'] = $request->getPost('quantity_of_number_E');
			$customize['Non-Geographical 0800 Number(s)']['unit_price'] = $request->getPost('nongeonumE_value');
		}
		//Geographical number European Number
		if($request->getPost('quantity_of_number_Euo')) { 
			$customize['European Number(s)']['value'] = $request->getPost('quantity_of_number_Euo');
			$customize['European Number(s)']['unit_price'] = $request->getPost('nongeonumEuo_value');
		}
		//Rest of the World
		if($request->getPost('quantity_of_number_Wld')) { 
			$customize['Rest of the world Number(s)']['value'] = $request->getPost('quantity_of_number_Wld');
			$customize['Rest of the world Number(s)']['unit_price'] = $request->getPost('nongeonumWld_value');
		}
		//For Conference
		if($request->getPost('quantity_of_number_Conf')) { 
			$customize['Conference Number(s)']['value'] = $request->getPost('quantity_of_number_Conf');
			$customize['Conference Number(s)']['unit_price'] = $request->getPost('confVal');
		}
		# Online Backup
		if($request->getPost('local_backup')) { 
			$customize['Local Backup Server, Local Backup Data']['value'] = '';
			$customize['Local Backup Server, Local Backup Data']['unit_price'] = 'POA';
		}
		if($request->getPost('full_installation')) { 
			$customize['Full Installation And Setup']['value'] = '';
			$customize['Full Installation And Setup']['unit_price'] = 'POA';
		}
			if($request->getPost('initial')) { 
			$customize['Initial Backup Service']['value'] = '';;
			$customize['Initial Backup Service']['unit_price'] = 'POA';
		}
		if($request->getPost('restore_service')) { 
			$customize['Restore Services']['value'] = '';
			$customize['Restore Services']['unit_price'] = 'POA';
		}
 		if($request->getPost('additional_restore')) { 
			$customize['Additional Test Restores']['value'] = '';
			$customize['Additional Test Restores']['unit_price'] = 'POA';
		}
		if($request->getPost('recovery_planning')) { 
			$customize['IT Disaster Recovery Planning']['value'] = '';
			$customize['IT Disaster Recovery Planning']['unit_price'] = 'POA';
		}
		
		# Cpanel Discount
		if($request->getPost('discount_percent')) {	
			$customize['Discount']['value'] = $request->getPost('cpanel_discount') ." with ".$request->getPost('discount_percent').'%'; 
			$customize['Discount']['unit_price'] = $request->getPost('discount_percent');
		}
		//echo '<pre>'; print_r($customize); exit;
		# Store All in Cart's tables
	    $model = new Store;
		$model->saveOrder( $cart_id, $serviceName, $package_type,$package_id, $subtotal, $customizeTotal, $totalAmount, $customize);
		$this->redirect( array("store/viewCart/") );
 	}
	
	# - Shopping Cart Items Listing
	public function actionViewCart()
	{	
		$model = new Store;	
		
		# - Remove Item Request
		$cart_id = Yii::app()->request->getParam("remove", "0");
 		if( $cart_id != '0' )
		{ 
			$model->removeCartItem( $cart_id );
			$this->redirect(array("store/viewCart"));
		}
		
		# - Edit A shopping cart Item
		$cart_id_edit = Yii::app()->request->getParam("edit", "0");
		if( $cart_id_edit != '0' )
		{ 
			$cartItem = $model->getCartItemInfo( $cart_id_edit );
			$serviceDetailPage =  $cartItem['item']['serviceName']."Detail"; 
			 
			if($cartItem['item']['serviceName'] == 'Online Backup')
			$this->redirect( array("store/backupDetail/pack/".strtolower($cartItem['item']['package_type']."/id/".$cartItem['item']['package_id']."/edit/".$cart_id_edit)) );
			
			$this->redirect( array("store/".$cartItem['item']['serviceName']."Detail/id/".$cartItem['item']['package_id']."/edit/".$cart_id_edit) ); 
 		  
		}
		
		# - Cart Quantity Update
		if( isset($_POST['update_cart']) && isset($_POST['quantity']))
		{
			
			foreach($_POST['quantity'] as $key => $value)
			{
				foreach($value as $cartId=>$quntity)
				{
					if($quntity > 0)
					$model->updateCart( $cartId , $quntity );
				}
			}
			
		}
		# -  Get Visitor Cart Items for Listing
	    $cartItem = $model->getAllCartItem();
		
		$_SESSION['cart_item_count'] = count($cartItem);
		if( isset($_SESSION['Shop_key']) )
		$_SESSION['cart_total'] = $model->getCartInfo();
		$this->layout = "store";
 		$this->render("viewcart", array("cartItems"=>$cartItem));
			
	}
	public function actionEmptyCart()
	{
		 if( isset($_SESSION['Shop_key']) )
		 {
			 $model = new Store;
			 $model->emptyCart();
			 
			 unset($_SESSION['cart_item_count']);
			 if(isset($_SESSION['cart_total']))
			 unset($_SESSION['cart_total']);
			 $this->redirect(array("store/index"));
		}
	}
}
?>