<?php

class payment_on_account {

	function contstruct(){

	}

	function pay_on_account(){
		// check account balance here
		
		global $user;
		global $db;

		$return = "<h3 class=\"shopping_cart_section_header\"><b>Payment On Account</b></h3>";
		// check the user type
		if ($user->value("type") != "trade customer"){
			$return .= "<p>This payment method is only available to registered trade customers. To register for a trade account please <a href=\"contact-us.html\">Contact Us</a>";
			return $return;
		}

		// check credit limit
		$sql="SELECT direct_debit_setup, bank_transfer_setup, credit_limit, credit_available, account_balance FROM user where id = " . $user->value("id");
		$rv=$db->query($sql);	
		$ac=$db->fetch_array($rv);

		if (!$ac['direct_debit_setup'] && !$ac['bank_transfer_setup']){
			$return .= "<p>Direct debit has not yet been set up for your trade account.</p>";
			$return .= "<p>If you wish to apply for direct debit payments, or check the progress of your application for direct debit payments, please <a href=\"contact-us.html\">contact us via our online form</a> or email us at <a href=\"mailto:order@mattplatts.com\">order@mattplatts.com</a>";
			$return .= "<p><a href=\"checkout.html\">Please click here to return to the checkout and select another payment method.</a></p>";
			return $return;	
		}

		if ($_SESSION['grand_total']>$ac['credit_available']){
			$return .= "<p>Your credit limit is: &pound;".$ac['credit_limit']."<br />Your currently available credit is: <b>&pound;".$ac['credit_available']."</b>.</p><p>You do not have enough credit available to complete this order on account at this time.</p>";
			$return .= "<p><a href=\"checkout.html\">Please click here to return to the checkout and select another payment method.</a></p>";
			return $return;

		} else {
			$return .= "<p>Your credit limit is: &pound;".$ac['credit_limit']."<br />Your currently available credit is: <b>&pound;".$ac['credit_available']."</b>. You have enough credit to place this order now.</p>";
			$return .= "<p>Account payments are taken on the 1st and 15th of every month.</p>";
			$return .= "<p><a href=\"http://www.mattplatts.com/site.php?action=load_payment_success\">Please Cllck here to finalise your order</a></p>";

		}

		return $return;
	}


	function load_payment_success(){
		global $mycart;
		$complete_module=$this->on_order_placed();
		$return_content=$mycart->complete_order_after_payment_taken();
		$return_content="<p>Thank You for your order - your payment has been completed successfully.</p>";
		// optionally rather than returning content a redirect could be done. This would be better to stop double orders?
		return $return_content; 
	}
	
	function on_order_placed(){
		global $db;
		global $user;
		if ($_SESSION['grand_total']){ // which we really should have
			$sql="SELECT credit_limit,credit_available,account_balance FROM user where id = " . $user->value("id");
			$rv=$db->query($sql);	
			$ac=$db->fetch_array($rv);
			$update_sql="UPDATE user SET credit_available=".($ac['credit_available']-$_SESSION['grand_total']).",account_balance=".($ac['account_balance']+$_SESSION['grand_total'])." WHERE id = " . $user->value("id");
			$update_rv=$db->query($update_sql);
		}
		return 1;
	}
}

?>
