<?php

/**
 * This is the model class for table "td_tickets".
 *
 * The followings are the available columns in table 'td_tickets':
 * @property integer $id
 * @property string $tkey
 * @property integer $did
 * @property string $dname
 * @property integer $mid
 * @property string $mname
 * @property integer $amid
 * @property string $amname
 * @property string $email
 * @property string $subject
 * @property integer $priority
 * @property string $message
 * @property integer $date
 * @property integer $last_reply
 * @property integer $last_reply_staff
 * @property integer $last_mid
 * @property string $last_mname
 * @property string $ipadd
 * @property integer $replies
 * @property integer $votes
 * @property double $rating
 * @property double $rating_total
 * @property string $notes
 * @property integer $status
 * @property integer $close_mid
 * @property string $close_mname
 * @property string $close_reason
 * @property integer $auto_close
 * @property integer $attach_id
 * @property string $cdfields
 * @property integer $guest
 * @property integer $guest_email
 * @property integer $fk_customer_id
 */
class Tickets extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Tickets the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return CDbConnection database connection
	 */
	public function getDbConnection()
	{
		return Yii::app()->db2;
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'td_tickets';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('message, subject,fk_customer_id', 'required'),
			array('did, mid, amid, priority, date, last_reply, last_reply_staff, last_mid, replies, votes, status, close_mid, auto_close, attach_id, guest, guest_email, fk_customer_id', 'numerical', 'integerOnly'=>true),
			array('rating, rating_total', 'numerical'),
			array('tkey, dname, mname, amname, email, subject, last_mname, close_mname', 'length', 'max'=>255),
			array('ipadd', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, tkey, did, dname, mid, mname, amid, amname, email, subject, priority, message, date, last_reply, last_reply_staff, last_mid, last_mname, ipadd, replies, votes, rating, rating_total, notes, status, close_mid, close_mname, close_reason, auto_close, attach_id, cdfields, guest, guest_email, fk_customer_id', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'tkey' => 'Tkey',
			'did' => 'Did',
			'dname' => 'Dname',
			'mid' => 'Mid',
			'mname' => 'Mname',
			'amid' => 'Amid',
			'amname' => 'Amname',
			'email' => 'Email',
			'subject' => 'Subject',
			'priority' => 'Priority',
			'message' => 'Message',
			'date' => 'Date',
			'last_reply' => 'Last Reply',
			'last_reply_staff' => 'Last Reply Staff',
			'last_mid' => 'Last Mid',
			'last_mname' => 'Last Mname',
			'ipadd' => 'Ipadd',
			'replies' => 'Replies',
			'votes' => 'Votes',
			'rating' => 'Rating',
			'rating_total' => 'Rating Total',
			'notes' => 'Notes',
			'status' => 'Status',
			'close_mid' => 'Close Mid',
			'close_mname' => 'Close Mname',
			'close_reason' => 'Close Reason',
			'auto_close' => 'Auto Close',
			'attach_id' => 'Attach',
			'cdfields' => 'Cdfields',
			'guest' => 'Guest',
			'guest_email' => 'Guest Email',
			'fk_customer_id' => 'Fk Customer',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('tkey',$this->tkey,true);
		$criteria->compare('did',$this->did);
		$criteria->compare('dname',$this->dname,true);
		$criteria->compare('mid',$this->mid);
		$criteria->compare('mname',$this->mname,true);
		$criteria->compare('amid',$this->amid);
		$criteria->compare('amname',$this->amname,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('subject',$this->subject,true);
		$criteria->compare('priority',$this->priority);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('date',$this->date);
		$criteria->compare('last_reply',$this->last_reply);
		$criteria->compare('last_reply_staff',$this->last_reply_staff);
		$criteria->compare('last_mid',$this->last_mid);
		$criteria->compare('last_mname',$this->last_mname,true);
		$criteria->compare('ipadd',$this->ipadd,true);
		$criteria->compare('replies',$this->replies);
		$criteria->compare('votes',$this->votes);
		$criteria->compare('rating',$this->rating);
		$criteria->compare('rating_total',$this->rating_total);
		$criteria->compare('notes',$this->notes,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('close_mid',$this->close_mid);
		$criteria->compare('close_mname',$this->close_mname,true);
		$criteria->compare('close_reason',$this->close_reason,true);
		$criteria->compare('auto_close',$this->auto_close);
		$criteria->compare('attach_id',$this->attach_id);
		$criteria->compare('cdfields',$this->cdfields,true);
		$criteria->compare('guest',$this->guest);
		$criteria->compare('guest_email',$this->guest_email);
		$criteria->compare('fk_customer_id',$this->fk_customer_id);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	/*
		* Returns an are of Ticket replys
		@Param ticke_id Ticket ID to be searched
	*/
	public function getTicketInfo($ticket_id)
	{ 	
		 
		$replies	= $replies = Yii::app()->db2->createCommand()
					->select('*')
					->from('td_replies')
					->where('tid=:id', array(":id"=>$ticket_id))
					->queryAll();
		return $replies;						
	}
	
	public function saveReply($post)
	{ 
		if(strlen($post['Tickets']['message']) > 5)
		{
			$ticket_id = $post['ticket_id'];
			$message   = $post['Tickets']['message'];
			$name	   = Yii::app()->user->id;
			$date	   = time();
			$sql="INSERT INTO td_replies (tid, mname, message, date) VALUES(:id, :name, :message, :dt)";
			$command= Yii::app()->db2->createCommand($sql);
			$command->bindParam(":id", $ticket_id, PDO::PARAM_INT);
			$command->bindParam(":name",$name,PDO::PARAM_STR);
			$command->bindParam(":message",$message,PDO::PARAM_STR);
			$command->bindParam(":dt",$date, PDO::PARAM_STMT);
			$command->execute();
			return;
		}
	}
	
	// Get All department from db2
	
	public function getAllDepartments()
	{
		// First Find Allowed Departments for Customer Group
		$customerGroup = Yii::app()->db2->createCommand()
					   ->select("g_id, g_m_depart_perm")
					   ->from("td_groups")
					   ->where("g_name = 'Customer'")
 					   ->queryAll();
		$dept_perm    = $customerGroup[0]['g_m_depart_perm'];
		$dept_perm    = unserialize($dept_perm);
		$ids = array();
		foreach( $dept_perm as $key => $value )
		{
			$ids[] = $key; 
		}
		$dept_perm    = implode("," , $ids);
	 
		$departments = Yii::app()->db2->createCommand()
					   ->select("id, name")
					   ->from("td_departments")
					   ->where("id IN (".$dept_perm.")")
					   ->order("name Asc")
					   ->queryAll();
		$return = array();			   
		foreach($departments as $depart)
		{
			$return[$depart['id']] = $depart['name'];
		}
		return $return;			   
	}
	
	/*
		Save and Create customer in Trellis Database in Customer Group
	*/
	public function saveInTrellis() {
		
		// Check whether already exists
		$memberExists = Yii::app()->db2->createCommand()
					   ->select("name")
					   ->from("td_members")
					   ->where("name = '".Yii::app()->user->id."'")
 					   ->queryRow();
					   
		if( is_array($memberExists) && count($memberExists) > 0) {
		    Yii::app()->db2->createCommand()->update("td_members", array("open_tickets"=>1), 
			"name=:name", array(":name"=>Yii::app()->user->id ));
			return; // already exists
		}
					   
		// Password
		$rand = 123456;
		$pass_salt = substr( md5( 'ps' . uniqid( rand(), true ) ), 0, 9 );
		$pass_hash = sha1( md5( $rand . $pass_salt ) );
		
		// Customer Group ID
		$customerGroup = Yii::app()->db2->createCommand()
					   ->select("g_id")
					   ->from("td_groups")
					   ->where("g_name = 'Customer'")
 					   ->queryAll();
		
		// Insert it in td_member			   
		Yii::app()->db2->createCommand("INSERT INTO td_members (name, email, password, pass_salt, mgroup, joined, open_tickets, ipadd)
									   VALUES ('".Yii::app()->user->id."', '".Yii::app()->user->customer_email."',
									   '".$pass_hash."', '".$pass_salt."', '".$customerGroup[0]['g_id']."', 
									   '".time()."', 1, '".$_SERVER['REMOTE_ADDR']."')")->execute();
								    
		return;							   
	}
	
	/*
		Add Member ID in ticket table
	*/
	public function updateMemId()
	{
		// Check whether already exists
		$member_id = Yii::app()->db2->createCommand()
					   ->select("id")
					   ->from("td_members")
					   ->where("name = '".Yii::app()->user->id."'")
 					   ->queryRow();
					  
		// add member id in td_tickets
		 Yii::app()->db2->createCommand()->update("td_tickets" , array("mid"=>$member_id['id']), 
												  "mname=:name", array(":name"=>Yii::app()->user->id ));
 		return;										
	}
	
	function saveAttachment($_FILES, $attachment_name, $file_safe_name)
	{
			$member_id = Yii::app()->db2->createCommand()
					   ->select("id")
					   ->from("td_members")
					   ->where("name = '".Yii::app()->user->id."'")
 					   ->queryRow();
					   
		Yii::app()->db2->createCommand()->insert("td_attachments", array(
								  'tid'				=> 0,
								  'real_name'		=> $attachment_name,
								  'original_name'	=> $file_safe_name,
								  'mid'				=> $member_id['id'],
								  'mname'			=> Yii::app()->user->id,
								  'size'			=> $_FILES['attachment']['size'],
								  'mime'			=> $_FILES['attachment']['type'],
								  'ipadd'			=> $_SERVER['REMOTE_ADDR'],
								  'date'			=> time(),
								 )
								);
		return Yii::app()->db2->getLastInsertId();							
		
	}
	
	/* ===============================================
	  - Send Email to Centrica Support
	 * ============================================== */
	 public function sendEmail($to='support@centrica-it.com',$subject, $message, $fullname='')
	 {
		
		 /*$emailer = new DX_Mailer; 
		 $emailer->Sender = Yii::app()->params->adminEmail;
		 $emailer->FromName = "Centrica-IT";
		 $emailer->From     = "support@centrica-it.com";
		 $emailer->Subject = $subject;
		 $emailer->Body = $message ;
		  $emailer->AddAddress = Yii::app()->params->adminEmail;
 		 
		 $emailer->Send();*/
		 $template = file_get_contents(Yii::app()->getBaseUrl(true)."/email_template/email_template.html");
		 $template = str_replace("#from_name#", $fullname, $template);  
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