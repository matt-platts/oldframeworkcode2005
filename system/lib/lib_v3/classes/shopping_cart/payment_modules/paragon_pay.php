<?php

class paragon_pay extends shopping_cart {

	function contstruct(){

	}

	function pay_by_paragon_pay(){
		return "Paying via paragon digital payment. <a href=\"http://www.paragon-digital.net/paragon_pay?success_url=http://www.londonsteakhousecompany.co.uk/site.php?action=load_payment_success&failure_url=http://www.londonsteakhousecompany.co.uk/site.php?action=load_payment_fail\">Click here to finalise your order</a>";
	}


	function load_payment_success(){
		global $mycart;
		$return_content=$mycart->complete_order_after_payment_taken(); // could this not be parent as well?

		// If you return content, this will be printed.
		$return_content="<p>Thank You for your order - your payment has been completed successfully.</p>";

		// optionally rather than returning content a redirect could be done. This is better and helps to stop duplicate orders. 
		//parent::redirect("order_thanks.html");

		return $return_content; 
	}
}

?>
