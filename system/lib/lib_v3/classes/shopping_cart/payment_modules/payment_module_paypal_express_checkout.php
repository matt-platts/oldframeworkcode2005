<?

class payment_module_paypal_express_checkout extends shopping_cart {

function __construct(){
	global $db;
	global $user;
	$paypal_config_sql="SELECT * from paypal_config WHERE active=1 LIMIT 1";
	$pp_res_val=$db->query($paypal_config_sql) or die ("Error " . ->db_error());
	$h=$db->fetch_array($pp_res_val);
	$this->API_UserName = urlencode($h['API_username']);
	$this->API_Password = urlencode($h['api_password']);
	$this->API_Signature = urlencode($h['api_signature']);
	$this->currency = urlencode($h['currency']);
	$this->returnURL = urlencode($h['success_url']);
	$this->cancelURL = urlencode($h['error_url']);
	$this->preorder_pay_now = urlencode($h['take_preorder_payments_immediately']);
}
	
function value($of){
	return $this->$of;
}

function set_value($of,$to){
	 $this->$of=$to;
}

function initiate_payment(){
	$environment = 'live';	// or 'beta-sandbox' or 'live'
	if (!$_SESSION){ session_start(); }

	//if ($_SESSION['total_of_all_orders_inc'] >0){
//	$paymentAmount=$_SESSION['total_of_all_orders_inc'];
	//} else {
	//	$paymentAmount=$_SESSION['grand_total'];
	//}
	$paymentAmount=$this->calculate_full_amount();
	$paymentAmount=$_SESSION['grand_total'];
	global $user;
	if ($_SESSION['PayPreordersNow']=="Yes"){
		$paymentAmount=$_SESSION['preorder_grand_total'];
	}
	if (!$paymentAmount){
		format_error("Sorry - we cannot accept pre-orders on paypal at this time. We are currently working to rectify this situation - please use a valid credit or debit card in the mean time",1);
	}
	$paymentAmount = urlencode($paymentAmount);
	//print_debug("pa is " . $paymentAmount);
	//$currencyID = urlencode('GBP');	// or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
	$currencyID = $this->value("currency");
	$paymentType = urlencode('Sale');// Authorisation or 'Sale' or 'Order'
	
	$returnURL = $this->value("returnURL");
	$cancelURL = $this->value("cancelURL");

	// Add request-specific fields to the request string.
	$nvpStr = "&Amt=$paymentAmount&ReturnUrl=$returnURL&CANCELURL=$cancelURL&PAYMENTACTION=$paymentType&CURRENCYCODE=$currencyID";
	// Execute the API operation; see the PPHttpPost function above.
	//print_debug($nvpStr);
	$httpParsedResponseAr = $this->PPHttpPost('SetExpressCheckout', $nvpStr);

	if("Success" == $httpParsedResponseAr["ACK"]) {
		// Redirect to paypal.com.
		$token = urldecode($httpParsedResponseAr["TOKEN"]);
		$payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token";
		if("sandbox" === $environment || "beta-sandbox" === $environment) {
			$payPalURL = "https://www.$environment.paypal.com/webscr&cmd=_express-checkout&token=$token";
		}
		header("Location: $payPalURL");
		exit;
	} else  {
		$content = 'SetExpressCheckout failed: ' . print_r($httpParsedResponseAr, true);
		return $content;
	}

}

function PPHttpPost ($methodName_, $nvpStr){
global $environment;
global $db;
// Set up your API credentials, PayPal end point, and API version.
$API_Endpoint = "https://api-3t.paypal.com/nvp";

$API_UserName=$this->value("API_UserName");
$API_Password=$this->value("API_Password");
$API_Signature=$this->value("API_Signature");

//$API_UserName = urlencode('annemarie_api1.voiceprint.co.uk');
//$API_Password = urlencode('PJP9A3CEWG4F4ANW');
//$API_Signature = urlencode('AH40eI0nmXMpr44muXpQfd3T9T.lAxF9xVXBj0jU2CAirzpQQx2bhYY9');

// Matt
//$API_UserName = urlencode('mattplatts_api1.gmail.com');
//$API_Password = urlencode('BQATD2RY5WFYGDTA');
//$API_Signature = urlencode('AFcWxV21C7fd0v3bYYYRCpSSRl31ARSNCx33rkDnapSHEz6p34rpiuGt');

// Mindhead
//$API_UserName = urlencode('sales_api1.mindheadpublishing.co.uk');
//$API_Password = urlencode('9ATM3K2XBFWFDYGA');
//$API_Signature = urlencode('AR6JRu-eL7IzJK8.FXuxbosMcABVAEoH1xxeITkoEr2SH3fRQ6Jn5fPQ');
if("sandbox" === $environment || "beta-sandbox" === $environment) {
	$API_Endpoint = "https://api-3t.$environment.paypal.com/nvp";
}
$version = urlencode('57.0');

// Set the curl parameters.
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
curl_setopt($ch, CURLOPT_VERBOSE, 1);

// Turn off the server and peer verification (TrustManager Concept).
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);

// Set the API operation, version, and API signature in the request.
$nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr";

// Set the request as a POST FIELD for curl.
curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);

	global $user;
	$ur=$user->value("id") . " " . $user->value("full_name");
	$to="mattplatts@gmail.com";
	$subject="Paypal amount";
	$headers="From: bot@gonzomultimedia.co.uk\r\n";
	$message="Paypal string $nvpreq sent $return_amt from $ur";
	mail($to,$subject,$message,$headers);

// Get response from the server.
$httpResponse = curl_exec($ch);

if(!$httpResponse) {
	exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
	}

	// Extract the response details.
	$httpResponseAr = explode("&", $httpResponse);

	$httpParsedResponseAr = array();
	foreach ($httpResponseAr as $i => $value) {
		$tmpAr = explode("=", $value);
		if(sizeof($tmpAr) > 1) {
			$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
		}
	}

	if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
		exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
	}

	return $httpParsedResponseAr;
}

function paypal_express_checkout_success(){
	$paypal_payment_status=0;
	$token=$_REQUEST['token'];
	$payer_id=$_REQUEST['PayerID'];
	$saleId=parent::getInternalSaleId(); // will be in the shopping_cart class
	if ($_SESSION['PayPreordersNow'] && !$saleId){
		$return_data=$this->paypal_set_preorders_paid($token,$payer_id);
	print ".. and back we go!";
		return $return_data;
		exit;
	} else if (!$saleId){
		format_error("error: the sale id is blank",1);
		exit;
	}

	global $db;
	global $mycart; // required to get the initial values from the objects construct function

	$sql="SELECT grand_total FROM orders WHERE id = $saleId";
	$res=$db->query($sql);
	while ($h=$db->fetch_array($res)){
		$grand_total_without_preorders=$h['grand_total'];
		$pp_amt=$h['grand_total'];
		//$pp_amt=$this->calculate_full_amount();
	}
	$pp_amt=urlencode($pp_amt);
	$pp_currencycode=$this->value("currency");
	$pp_paymentaction="Sale";
	$environment = 'live';  // or 'beta-sandbox' or 'live'
	$payerID = urlencode($payer_id);
	$token = urlencode($token);
	$paymentType = urlencode("Sale"); // or 'Sale' or 'Order' or //Authorization WITH A Z!
	$paymentAmount = urlencode("$pp_amt");
	//$currencyID = urlencode("GBP"); // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
	$currencyID = $this->value("currency"); // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
	$nvpStr = "&TOKEN=$token&PAYERID=$payerID&PAYMENTACTION=$paymentType&AMT=$paymentAmount&CURRENCYCODE=$currencyID";

	$internalSaleId=parent::getInternalSaleId();
	//if (!$internalSaleId){ $internalSaleId = $saleId;}
	if ($grand_total_without_preorders>0){
		$httpParsedResponseAr = $this->PPHttpPost('DoExpressCheckoutPayment', $nvpStr);
		$log_paypal_response=$this->log_paypal_transaction_details($httpParsedResponseAr,$internalSaleId);
	}

	if("Success" == $httpParsedResponseAr["ACK"]) {
		$paypal_payment_status=1;
		$return .= "<p><b>Thank you for your purchase. This transaction is now complete.</b></p>";
		$return .= "<p>Your order number is #$saleId. A confirmation of your order has been sent to you by email.</p>";
		$return .= "<p><a href=\"index.html\">Click Here to return to the home page.</a></p>";
		$paypal_payment_status=1;
		if ($_SESSION['paypal_pay_for_pre_order'] || $_SESSION['pay_for_pre_order']){
			$upd_sql="UPDATE orders set date_paid=NOW(),preorder_date_shipped=NOW() WHERE id = $saleId";
			$upd_rv=$db->query($upd_sql);
		}
	} else if (!$grand_total_without_preorders){ // check this bit of code
		$return .= "<p><b>Thank you for your purchase.</b></p>";
		$return .= "<p>Your order number is #$saleId. A confirmation of your order has been sent to you by email.</p>";
		$return .= "<p>When your pre-orders come into stock you will be notified by email and can complete the transaction.</p>";
		$return .= "<p><a href=\"index.html\">Click Here to return to the home page.</a></p>";
		$paypal_payment_status=2;
	} else {
		$return .= "<p><b>Sorry - Paypal Express Checkout did not accept this transaction.</b></p>";
		$simplemessage = "<p>Possible reasons are: Your paypal account balance is not great enough, or if you have a card attached to your paypal account there may be insufficient funds on it. Please check your paypal account for more information on any of these.</p>";
		$return .= $simplemessage;
		$return .= "<p>The paypal server returned the following response: " . urldecode($httpParsedResponseAr['L_LONGMESSAGE0']);
		$return .= "</p>";
		$return .= "<p>To return to the checkout and try again or select another payment method please click <a href=\"checkout.html\">here</a>.</p><p>";
		$paypal_payment_status=0;
	}

	$return_vars['content']=$return;
	$return_vars['status']=$paypal_payment_status;
	return $return_vars;
}

function paypal_express_checkout_cancel(){
	$return = "<p>Your transaction by Paypal Express Checkout was cancelled.</p>";
	$return .= "<p><a href=\"checkout.html\">Click here to return to the checkout</a>.</p>";
	return $return;
}

function log_paypal_transaction_details($httpParsedResponseAr,$internalSaleId){
	global $db;
        $sql = "INSERT INTO paypal_transaction_details (id,internal_sale_id,token,timestamp,correlation_id,ack,version,build,transaction_id,transaction_type,payment_type,order_time,amount,tax_amount,currency_code,payment_status,pending_reason,reason_code) VALUES(\"\",$internalSaleId,";
        $sql .= "\"". $httpParsedResponseAr['TOKEN'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['TIMESTAMP'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['CORRELATIONID'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['ACK'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['VERSION'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['BUILD'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['TRANSACTIONID'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['TRANSACTIONTYPE'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['PAYMENTTYPE'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['ORDERTIME'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['AMT'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['TAXAMT'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['CURRENCYCODE'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['PAYMENTSTATUS'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['PENDINGREASON'] . "\",";
        $sql .= "\"". $httpParsedResponseAr['REASONCODE'] . "\"";
        $sql .= ")";
        //print $sql;
        $res=$db->query($sql) or format_error("Error inserting payment details into database.",0);
	return 1;
}

function load_payment_success(){
	$paypal_results = $this->paypal_express_checkout_success();
	$content=$paypal_results['content'];
	$payment_status=$paypal_results['status'];
	if ($payment_status){
		global $mycart;
	
		if ($_SESSION['cart']){
			$order_details['order_number']=$_SESSION['order_id'];
			$content=$mycart->complete_order_after_payment_taken_new($order_details['order_number']);
			
		}
		foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
			$order_details['order_number']=$_SESSION['preorder_cart'][$item]['order_number'];
			$order_details['order_type']="preorder";
			$return_preorder_content .= $mycart->complete_order_after_payment_taken_new($order_details['order_number'],$item);
		}
		//require_once("$libpath/classes/flight_logistics_order_post.php");
		//$flight= new flight_logistics_order_post();
		//$flight_post_result = $flight->post_order_to_flight_logistics();
		//unset($_SESSION['cart']);
	}
	return $content;	
}

function load_preorder_only_success(){
	$payment_status=$paypal_results['status'];
	$payment_status=1;
	if ($payment_status){
		global $mycart;
	
		if ($_SESSION['cart']){
			$order_details['order_number']=$_SESSION['order_id'];
			$content=$mycart->complete_order_after_payment_taken_new($order_details['order_number']);
			
		}
		foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
			$order_details['order_number']=$_SESSION['preorder_cart'][$item]['order_number'];
			$order_details['order_type']="preorder";
			$return_preorder_content .= $mycart->complete_order_after_payment_taken_new($order_details['order_number'],$item);
		}
		//require_once("$libpath/classes/flight_logistics_order_post.php");
		//$flight= new flight_logistics_order_post();
		//$flight_post_result = $flight->post_order_to_flight_logistics();
		//unset($_SESSION['cart']);
	}
	//$content .= $return_preorder_content;
	$content = "<p>Thank you for your order!</p>";
	$content .= "<p>You will receive confirmation of your pre-ordered items in a few minutes.</p>";
	$content .= "<p><a href=\"/\">Return to the home page.</a></p>";
	return $content;
}



function load_payment_cancel(){
	//global $libpath;
        //require_once("$libpath/classes/payment_module_paypal_express_checkout.php");
        //$paypal_details=new payment_module_paypal_express_checkout();
	$content=$this->paypal_express_checkout_cancel();
	return $content;
}

function calculate_full_amount(){

	$preorder_running_total=0;
	foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
		$preorder_running_total=$_SESSION['preorder_cart'][$item]['price']*$_SESSION['preorder_cart'][$item]['quantity'] + $_SESSION['preorder_cart'][$item]['preorder_shipping_amount'];
	}
	if ($preorder_running_total>0){
		$return_amt=$_SESSION['grand_total']+$preorder_running_total;	
	} else {
		$return_amt=$_SESSION['grand_total'];
	}
	/*
	global $user;
	$ur=$user->value("id") . " " . $user->value("full name");
	$to="mattplatts@gmail.com";
	$subject="Paypal amount";
	$headers="From: bot@gonzomultimedia.co.uk\r\n";
	$message="Paypal payment amount calculated at $return_amt from $ur";
	mail($to,$subject,$message,$headers);
	*/
	return $return_amt; 
}

function check_preorders(){
	global $mycart;
	global $user;
	if ($user->value("id")==1){
		$this->set_value("preorder_pay_now",1); // this only gives the option, doesnt force it yet..
	}

	if (!$this->value("preorder_pay_now")){
		if ($_SESSION['preorder_cart'] && $_SESSION['cart']){
			$return['message']= "<p style=\"color:#fff; background-color:#222; padding:2px; display:block;\"><b>Pre Ordering Via Paypal</b></p><p>Please note: we will take payment for your immediate orders now. As your pre-orders become available we will email you, and you will be able to pay for each of these via paypal from the link in your email.</p><p>The amount we will take now is: <b>" . $mycart->value("default_currency_symbol") . $_SESSION['grand_total'] . "</b></p>";
		} else if ($_SESSION['preorder_cart']){
			$return['message']="<p style=\"color:#fff; background-color:#222; padding:2px; display:block;\"><b>Pre Ordering Via Paypal</b></p><p>As you have only ordered items for pre-order, and have selected to pay by Paypal, we cannot take payment now.</p><p>As your pre-orders become available we will email you, and you will be able to pay via paypal from the link in your email.</p><p><a href=\"site.php?action=paypal_preorders_only\">Please click here to complete your order.</a>";
			$return['actions']="cancel_forwarding_page_text|cancel_payment_icon";
		} else {
		}
	} else {

		// paypal take payment immediately!
		if ($_SESSION['preorder_cart'] && $_SESSION['cart']){
			$return['message']= "<p style=\"color:#fff; background-color:#222; padding:2px; display:block;\"><b>Pre Ordering Via Paypal</b></p><p>Please note: we will take payment for your immediate orders now. As your pre-orders become available we will email you, and you will be able to pay for each of these via paypal from the link in your email.</p><p>The amount we will take now is: <b>" . $mycart->value("default_currency_symbol") . $_SESSION['grand_total'] . "</b></p>";
		} else if ($_SESSION['preorder_cart']){
			$return['message']="<p style=\"color:#fff; background-color:#222; padding:2px; display:block;\"><b>Pre Ordering Via Paypal</b></p><p>As you have only ordered items for pre-order, and have selected to pay by Paypal, we can give you the option for paying now, or receiving a reminder email when the product is ready for despatch.</p><p>As your pre-orders become available we will email you, and you will be able to pay via paypal from the link in your email.</p>";
			if (!$_SESSION['PayPreordersNow']){
			$return['message'] .= "<form id=\"preorder_pay_now_form\" method=\"post\" action=\"site.php?action=set_preorder_preference\">
					<input type=\"radio\" name=\"preorder_pay_now\" value=\"1\" checked> Pay for my preorders now<br />
					<input type=\"radio\" name=\"preorder_pay_now\" value=\"0\"> Pay for my preorders later<br />
					<input type=\"hidden\" name=\"preorder_payment_method\" value=\"paypal\"> 
					<p><a href=\"Javascript:document.forms['preorder_pay_now_form'].submit()\">Next &gt;</a></p>				
</form>";
			$return['actions']="cancel_forwarding_page_text|cancel_payment_icon";
			} else {
				$return['message']="<p></p>";
			}
		} else {
		}


	}
	return $return; 
}

function paypal_set_preorders_paid($token,$payer_id){
	global $user;
	global $db;
	global $mycart; // required to get the initial values from the objects construct function
	//print "<p>Here we go</p>";
	$pp_amt=0;
	foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
//print_r($itemdata);
//print "<br />";
		$internalSaleId=$itemdata['order_number']; // use the first item as the id to log paypal responses to..
		$saleId = $internalSaleId;
		$sql="SELECT grand_total FROM orders WHERE id = $saleId";
		$res=$db->query($sql);
		while ($h=$db->fetch_array($res)){
			$grand_total_without_preorders=$h['grand_total'];
			$pp_amt=$pp_amt + $h['grand_total'];
		}
	}
	//print "Validating with paypal for $pp_amt<br>";

		$pp_amt=urlencode($pp_amt);
		$pp_currencycode=$this->value("currency");
		$pp_paymentaction="Sale";
		$environment = 'live';  // or 'beta-sandbox' or 'live'
		$payerID = urlencode($payer_id);
		$token = urlencode($token);
//	print "TOKEN IS " . $token;
		$paymentType = urlencode("Sale"); // or 'Sale' or 'Order' or //Authorization WITH A Z!
		$paymentAmount = urlencode("$pp_amt");
		//$currencyID = urlencode("GBP"); // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		$currencyID = $this->value("currency"); // or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		$nvpStr = "&TOKEN=$token&PAYERID=$payerID&PAYMENTACTION=$paymentType&AMT=$paymentAmount&CURRENCYCODE=$currencyID";

		$httpParsedResponseAr = $this->PPHttpPost('DoExpressCheckoutPayment', $nvpStr);
	//print "Logging details for $internalSaleId";
		$log_paypal_response=$this->log_paypal_transaction_details($httpParsedResponseAr,$internalSaleId);

	//print "We are loggred";
	if("Success" == $httpParsedResponseAr["ACK"]) {
		$paypal_payment_status=1;
		$return .= "<p><b>Thank you for your purchase. This transaction is now complete.</b></p>";
		$return .= "<p>Your order number is #$saleId. A confirmation of your order has been sent to you by email.</p>";
		$return .= "<p><a href=\"index.html\">Click Here to return to the home page.</a></p>";
		$paypal_payment_status=1;
		if ($_SESSION['paypal_pay_for_pre_order'] || $_SESSION['pay_for_pre_order']){
			$upd_sql="UPDATE orders set date_paid=NOW(), paid=1, preorder_date_shipped=NOW() WHERE id = $saleId";
			$upd_rv=$db->query($upd_sql);
		}

		// update db
		foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
				$order_id=$itemdata['order_number'];
				$upd_sql="UPDATE orders SET date_paid=NOW(), paid = 1, preorder_pay_immediately=1 WHERE id = $order_id";
				$upd_rv=$db->query($upd_sql);
		}
		
	} else if (!$grand_total_without_preorders){ // check this bit of code
		$return .= "<p><b>Thank you for your purchase.</b></p>";
		$return .= "<p>Your order number is #$saleId. A confirmation of your order has been sent to you by email.</p>";
		$return .= "<p>When your pre-orders come into stock you will be notified by email and can complete the transaction.</p>";
		$return .= "<p><a href=\"index.html\">Click Here to return to the home page.</a></p>";
		$paypal_payment_status=2;
	} else {
		$return .= "<p><b>Sorry - Paypal Express Checkout did not accept this transaction.</b></p>";
		$simplemessage = "<p>Possible reasons are: Your paypal account balance is not great enough, or if you have a card attached to your paypal account there may be insufficient funds on it. Please check your paypal account for more information on any of these.</p>";
		$return .= $simplemessage;
		$return .= "<p>The paypal server returned the following response: " . urldecode($httpParsedResponseAr['L_LONGMESSAGE0']);
		$return .= "</p>";
		$return .= "<p>To return to the checkout and try again or select another payment method please click <a href=\"checkout.html\">here</a>.</p><p>";
		$paypal_payment_status=0;
	}

	$return_vars['content']=$return;
	$return_vars['status']=$paypal_payment_status;
	//print "returning YESS";
	return $return_vars;	
}

}
?>
