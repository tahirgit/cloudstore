<?php

class Store extends CFormModel
{
	

 /*
 	Get All Cart Items list
  */	 
   public function getAllCartItem()
   {
	   if( isset($_SESSION['Shop_key']) )
	   $shop_key = $_SESSION['Shop_key'];
	   else
	   return false;
	  
		$cart_items = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from('cart')
 								->where("session_id = '".$shop_key."'")
								->queryAll(); 	
		$return =  array();
		foreach($cart_items as $key=>$row)
		{
			$customized       = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from('cart_customized_items')
 								->where("cart_id = '".$row['id']."'")
								->queryAll(); 
			$return[$key]['item'] = $row;
			$return[$key]['item']['customized'] = $customized;					
		}
		
 		return $return;						
   }
   
   /*
   		* Remove Item from Shopping Cart
		* @Param - package_type 
		* @Param - Table Name
   */
   public function removeCartItem( $cart_id )
   {
	     
	    $shop_key = $_SESSION['Shop_key'];
		Yii::app()->db->createCommand("DELETE FROM cart WHERE session_id = '".$shop_key."' AND id = '".$cart_id."'")->execute();
		Yii::app()->db->createCommand("DELETE FROM cart_customized_items WHERE cart_id = '".$cart_id."'")->execute();
 		return true;							  
   }
   
   /*
   		Just get Item quantity and Total Price for 
   */
   public function getCartInfo()
   {
	   $shop_key = $_SESSION['Shop_key'];
	
		
		$cart_items = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from('cart')
 								->where("session_id = '".$shop_key."'")
								->queryAll(); 
        $total = 0;$quantity = 0;
		foreach($cart_items as $key=>$row)
		{
			$quantity += $row['quantity'];
			$total += $row['totalAmount'] * $row['quantity'];
		}
		return $quantity ." Item(s), Total: &pound; ".number_format($total,2);						
   }
   
   /*
   		* UPDATE CART Quantity
   */
   function updateCart( $cart_id, $quantity)
   {
 	    $shop_key = $_SESSION['Shop_key'];
	   Yii::app()->db->createCommand()->update('cart', array('quantity'=>$quantity), 'session_id=:shopKey AND id=:cartId', array(":shopKey"=>$shop_key, ":cartId"=>$cart_id));
	   return;
   }
   
  /*
   	 Save an order
	 cart(id, session_id,  service_id = vdi , serviceName, package_type , subtotal, customizedTotal, totalAmount, customized = 1
	 cart_customized_item ( id, cart_id, component, component_value, component_price, unit_price )
  */
   public function saveOrder( $cart_id, $serviceName, $package_type, $package_id, $subtotal, $customizeTotal, $totalAmount, $setup_charges, $customize)
   {
	   # Generate a new key
 	   if ( !isset($_SESSION['Shop_key']) ) 
 		 $_SESSION['Shop_key'] = md5(time());
      
	  # Store Order info in Cart 
	   $command = Yii::app()->db->createCommand();
	   if($cart_id == 0) {
		   $command->insert("cart", array("session_id"=>$_SESSION['Shop_key'], "serviceName"=>$serviceName, 
		   "package_type"=>$package_type,"package_id"=>$package_id, "quantity"=>1, "subtotal"=>$subtotal, 
		   "customizedTotal"=>$customizeTotal, "totalAmount"=>$totalAmount, "setup_charges"=>$setup_charges));
		   $cart_id = Yii::app()->db->getLastInsertId() ; 
		  
		   # Insert Customized Componenet
		   if( count ($customize) > 0 )
		   {
			   
			   foreach($customize as $key=>$value ) { 
			    if(!isset($customize[$key]['component_price']))$customize[$key]['component_price'] = 0;
 				if(!isset($customize[$key]['value']))  $customize[$key]['value'] = $key;
	            if(!isset($customize[$key]['unit_price']))  $customize[$key]['unit_price'] = 0;
	  
				 $command->insert("cart_customized_items", array("cart_id"=>$cart_id, "component"=>$key, 
					"component_value"=>$customize[$key]['value'], "unit_price"=>$customize[$key]['unit_price'],
					"component_price"=>$customize[$key]['component_price']));
			   }
		   }
	   }
	   else # Update a cart Item with the Cart ID
	   {
		    $command->update("cart", array("session_id"=>$_SESSION['Shop_key'], "serviceName"=>$serviceName, 
		   "package_type"=>$package_type,"package_id"=>$package_id, "quantity"=>1, "subtotal"=>$subtotal, 
		   "customizedTotal"=>$customizeTotal, "totalAmount"=>$totalAmount), 'id=:cartId', array(":cartId"=>$cart_id));
 		   Yii::app()->db->createCommand("DELETE FROM cart_customized_items WHERE cart_id = '".$cart_id."'")->execute();
		   # Insert Customized Componenet
		   if( count ($customize) > 0 )
		   {
 			   foreach($customize as $key=>$value ) { 
			   if(!isset($customize[$key]['component_price']))$customize[$key]['component_price'] = 0;
			   
				 $command->insert("cart_customized_items", array("cart_id"=>$cart_id, "component"=>$key, 
					"component_value"=>$customize[$key]['value'], "unit_price"=>$customize[$key]['unit_price'],
					"component_price"=>$customize[$key]['component_price']));
			   }
		   }
	   }
	   
   return;
   }
   
   
   /*
   	  returns a Cart Item Info
	  @Param - Cart Item ID
   */
   public function getCartItemInfo($cart_id)
   {
	   $shop_key = 0;
	   if( isset($_SESSION['Shop_key']) )
	   $shop_key = $_SESSION['Shop_key'];
	   $cart_items = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from('cart')
 								->where("session_id = '".$shop_key."' AND id = '".$cart_id."'")
								->queryRow(); 	
		$return =  array();
		 
			$customized       = Yii::app()->db->createCommand()
	   							->select("*")
	   						    ->from('cart_customized_items')
 								->where("cart_id = '".$cart_id."'")
								->queryAll(); 
			$return['item'] = $cart_items;
			$return['item']['customized'] = $customized;					
		 
		
 		return $return;	
   }
   
   function emptyCart()
   {
	 		   Yii::app()->db->createCommand("DELETE FROM cart WHERE session_id = '".$_SESSION['Shop_key']."'")->execute();
	  	   return;

   }
}
?>   