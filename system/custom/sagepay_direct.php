<?php

function post_order_to_sagepay(){
	// in a function like this, ob_start has already been called so we just print what we want
	global $user;
	if ($_SESSION['preorder_cart']){
		sagepay_multiple_transactions();
		return;
	}
	global $libpath;
	require_once("$libpath/classes/sagepay_direct.php");
	$attempt_payment=new sagepay_direct();	
	$payment_result=$attempt_payment->make_payment();	
	
}

function sagepay_multiple_transactions(){

	global $libpath;
	require_once("$libpath/classes/sagepay_direct.php");
	$attempt_payment=new sagepay_direct();
	$completed_orders_array=array();
	// main order
	if ($_SESSION['cart']){
		$order_details['order_number']=$_SESSION['order_id'];
		$order_details['order_type']="immediate";
		$order_details['amount']=$_SESSION['grand_total'];
		$order_details['purchase_url']="https://live.sagepay.com/gateway/service/vspdirect-register.vsp";
		//$order_details['purchase_url']="https://test.sagepay.com/gateway/service/vspdirect-register.vsp";
		$main_order_result=$attempt_payment->make_payment_2($order_details);
		//print "<p>Got main order result<br>";
		//var_dump($main_order_result);
		//print "<hr>";
		if (($main_order_result['status']=="OK")||($main_order_result['status']=="REGISTERED")){
			global $mycart;
			//print "now to complete the order with order number " . $order_details['order_number'];
			$return_content=$mycart->complete_order_after_payment_taken_new($order_details['order_number']);
		} else {

		}
		$main_order_result['order_number']=$order_details['order_number'];
		array_push($completed_orders_array,$main_order_result);
	}

	// loop through preorder cart
	$return_preorder_content="";
	foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
		//print "on preorder cart bit for $item";
		$order_details['order_number']=$_SESSION['preorder_cart'][$item]['order_number'];
		$order_details['order_type']="preorder";
		$order_details['amount']=($_SESSION['preorder_cart'][$item]['price']*$_SESSION['preorder_cart'][$item]['quantity']) + $_SESSION['preorder_cart'][$item]['preorder_shipping_amount'];
		//print "on preorder set amount to " . $order_details['amount'];
		//var_dump($_SESSION['preorder_cart'][$item]);
		$order_details['purchase_url']="https://live.sagepay.com/gateway/service/vspdirect-register.vsp";
		//$order_details['purchase_url']="https://test.sagepay.com/gateway/service/vspdirect-register.vsp";
		$preorder_result=$attempt_payment->make_payment_2($order_details);
		//print "Got preorder result for $item:<br>";
		//var_dump($preorder_result);
		if (($preorder_result['status']=="OK")||($preorder_result['status']=="REGISTERED")){
			global $mycart;
			//print "<br />- off to complete preorder with order number of " . $order_details['order_number'];
			$return_preorder_content .= $mycart->complete_order_after_payment_taken_new($order_details['order_number'],$item);
		}
		$preorder_result['order_number']=$order_details['order_number'];
		array_push($completed_orders_array,$preorder_result);
	}

	//print "<hr>";
	//print $return_content . $return_preorder_content;
	/* Kill the session variables */

	$_SESSION['completed_orders']=$completed_orders_array;
	global $mycart;
	$redirect_page=$mycart->redirect("site.php?action=shopping_cart_orders_complete");
	
}
?>
