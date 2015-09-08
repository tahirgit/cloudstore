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
									);
	/* --> TICKET PRIORITY */
	public $possible_priority = array(
										0 => 'LOW',
										1 => 'MEDIUM',
										2 => 'HIGH',
										3 => 'CRITICAL',
							    );
	
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
				'actions'=>array('index','create','view','closed'),
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
		$tickets = $model->findAll('status != "3" AND fk_customer_id="'.Yii::app()->user->pk_customer_id.'"');
 		$this->render('index', array("tickets"=>$tickets));
	}
    
	/*
		- Shows closed ticket Listings 
	*/							
	public function actionClosed()
	{
 		$model 	 = new Tickets;
		$tickets = $model->findAll('status = "3" AND fk_customer_id="'.Yii::app()->user->pk_customer_id.'"');
 		$this->render('closed', array("tickets"=>$tickets));
	}
    
	
	/*
		- Creates a new Customer Tickets
	*/
	public function actionCreate()
	{
		$model 	= new Tickets;
		if( isset($_POST['Tickets']) )
		{	
			// Setting remaining Important Fields
			$model->trackid		   =  $this->hesk_createID();
			$model->fk_customer_id =  Yii::app()->user->pk_customer_id;
			$model->name		   =  Yii::app()->user->id; 
			$model->email		   =  Yii::app()->user->customer_email;
			$model->ip			   = $_SERVER['REMOTE_ADDR'];
 			$model->dt			   =  new CDbExpression('NOW()');
			
			$model->attributes=$_POST['Tickets'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->save())
  				$this->redirect(array("ticket/index")); 
				 
		}
		 
		$this->render('create', array("model"=>$model));
		
	}
	
	
	/*
		- View Single Ticket and Can Give Reply
	*/
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
			$ticketInfo['ticket']	  	= $model->find("fk_customer_id='".Yii::app()->user->pk_customer_id."' AND id = '".$ticket_id."'");    
			$ticketInfo['allReplies']   = $model->getTicketInfo($ticket_id);
			/*echo '<pre>'; print_r($ticketInfo); exit;*/
			$this->render('view', array('viewTicket'=>$ticketInfo, "model"=>$model) );
		}	
		
 	}
	
	
  /*
		- Create TrackID for New Ticket
  */
 	public function hesk_createID()
 	{
 
	/*** Generate tracking ID and make sure it's not a duplicate one ***/

	/* Ticket ID can be of these chars */
	$useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';

    /* Set tracking ID to an empty string */
	$trackingID = '';

	/* Let's avoid duplicate ticket ID's, try up to 3 times */
	for ($i=1;$i<=3;$i++)
    {
	    /* Generate raw ID */
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];
	    $trackingID .= $useChars[mt_rand(0,29)];

		/* Format the ID to the correct shape and check wording */
        $trackingID = $this->hesk_formatID($trackingID);

		/* Check for duplicate IDs */
		$model = new Tickets;
		$res   = $model->findAll('trackid = "'.$trackingID.'"');
		 

		if (count($res) == 0)
		{
        	/* Everything is OK, no duplicates found */
			return $trackingID;
        }
        else
		 {
			 // Recursive Call
			 $this->hesk_createID();
		 }
        
    }

  } // END hesk_createID()
   
   
   
   
   /*
   		- Formating new TrackId
   */
   public function hesk_formatID($id)
   {

	$useChars = 'AEUYBDGHJLMNPQRSTVWXZ123456789';

    $replace  = $useChars[mt_rand(0,29)];
    $replace .= mt_rand(1,9);
    $replace .= $useChars[mt_rand(0,29)];
     /*
    Remove 3 letter bad words from ID
    Possiblitiy: 1:27,000
    */
	$remove = array(
    'ASS',
    'CUM',
    'FAG',
    'FUK',
    'GAY',
    'SEX',
    'TIT',
    'XXX',
    );

    $id = str_replace($remove,$replace,$id);

    /*
    Remove 4 letter bad words from ID
    Possiblitiy: 1:810,000
    */
	$remove = array(
	'ANAL',
	'ANUS',
	'BUTT',
	'CAWK',
	'CLIT',
	'COCK',
	'CRAP',
	'CUNT',
	'DICK',
	'DYKE',
	'FART',
	'FUCK',
	'JAPS',
	'JERK',
	'JIZZ',
	'KNOB',
	'PISS',
	'POOP',
	'SHIT',
	'SLUT',
	'SUCK',
	'TURD',
    );

	$replace .= mt_rand(1,9);
    $id = str_replace($remove,$replace,$id);

    /* Format the ID string into XXX-XXX-XXXX format for easier readability */
    $id = $id[0].$id[1].$id[2].'-'.$id[3].$id[4].$id[5].'-'.$id[6].$id[7].$id[8].$id[9];

    return $id;

 } // END hesk_formatID()

}