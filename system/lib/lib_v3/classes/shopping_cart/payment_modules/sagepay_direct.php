<?php

class sagepay_direct extends shopping_cart {

	function __construct(){
		global $db;
		$sp_conf_sql="SELECT * from sagepay_configuration WHERE active=1";
		$sp_conf_rv=$db->query($sp_conf_sql);
		$sp_vars=$db->fetch_array($sp_conf_rv);

		$this->strConnectTo=$sp_vars['server'];
		//$this->strConnectTo="TEST";
		//$this->strConnectTo="SIMULATOR";
		$this->strYourSiteFQDN=$sp_vars['FQDN'];
		$this->strVendorName=$sp_vars['vendor_name']; // Vendor Name assigned by Sage Pay 
		$this->strVendorNameForSimulator=$sp_vars['simulator_vendor_name']; // Vendor Name assigned by Sage Pay 
		$this->strCurrency=$sp_vars['currency']; // Merchant number must be in this currency 
		$this->strTransactionType=$sp_vars['transaction_type']; // PAYMENT, DEFERRED or AUTHENTICATE 
		$this->strPartnerID=$sp_vars['partner_id']; /** Optional - If you are a Sage Pay Partner and wish to flag with your id . **/
		$this->strProtocol=$sp_vars['protocol'];
		$this->strCompletionURL=$sp_vars['completion_url'];
		$this->strOrderFailURL=$sp_vars['failure_url'];
		$this->strNotAuthedMessageTemplate=$sp_vars['not_authed_message'];
		$this->strMalformedMessageTemplate=$sp_vars['malformed_message'];
		
		// THE SECTION BELOW TO BE DELETED WHEN ABOVE IS TESTED 
		//$this->strConnectTo="LIVE";
		//$this->strConnectTo="TEST";
		//$this->strConnectTo="SIMULATOR";
		//$this->strYourSiteFQDN="http://www.gongomultimedia.co.uk/";
		//$this->strVendorName="gongomultimedia"; // Vendor Name assigned by Sage Pay 
		//$this->strVendorNameForSimulator="gongo"; // Vendor Name assigned by Sage Pay 
		//$this->strCurrency="GBP"; // Merchant number must be in this currency 
		//$this->strTransactionType="PAYMENT"; // PAYMENT, DEFERRED or AUTHENTICATE 
		//$this->strPartnerID=""; /** Optional - If you are a Sage Pay Partner and wish to flag with your id . **/
		//$this->strProtocol="2.23";
		//$this->strCompletionURL="order_thanks.html";
		//$this->strOrderFailURL="adminorder_error.html";

		if ($this->strConnectTo=="LIVE"){
		  $this->strAbortURL="https://live.sagepay.com/gateway/service/abort.vsp";
		  $this->strAuthoriseURL="https://live.sagepay.com/gateway/service/authorise.vsp";
		  $this->strCancelURL="https://live.sagepay.com/gateway/service/cancel.vsp";
		  $this->strPurchaseURL="https://live.sagepay.com/gateway/service/vspdirect-register.vsp";
		  $this->strRefundURL="https://live.sagepay.com/gateway/service/refund.vsp";
		  $this->strReleaseURL="https://live.sagepay.com/gateway/service/release.vsp";
		  $this->strRepeatURL="https://live.sagepay.com/gateway/service/repeat.vsp";
		  $this->strVoidURL="https://live.sagepay.com/gateway/service/void.vsp";
		  $this->str3DCallbackPage="https://live.sagepay.com/gateway/service/direct3dcallback.vsp";
		  $this->strPayPalCompletionURL="https://live.sagepay.com/gateway/service/complete.vsp";
		} elseif ($this->strConnectTo=="TEST") {
		  $this->strAbortURL="https://test.sagepay.com/gateway/service/abort.vsp";
		  $this->strAuthoriseURL="https://test.sagepay.com/gateway/service/authorise.vsp";
		  $this->strCancelURL="https://test.sagepay.com/gateway/service/cancel.vsp";
		  $this->strPurchaseURL="https://test.sagepay.com/gateway/service/vspdirect-register.vsp";
		  $this->strRefundURL="https://test.sagepay.com/gateway/service/refund.vsp";
		  $this->strReleaseURL="https://test.sagepay.com/gateway/service/release.vsp";
		  $this->strRepeatURL="https://test.sagepay.com/gateway/service/repeat.vsp";
		  $this->strVoidURL="https://test.sagepay.com/gateway/service/void.vsp";
		  $this->str3DCallbackPage="https://test.sagepay.com/gateway/service/direct3dcallback.vsp";
		  $this->strPayPalCompletionURL="https://test.sagepay.com/gateway/service/complete.vsp";
		} else {
		  $this->strAbortURL="https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorAbortTx";
		  $this->strAuthoriseURL="https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorAuthoriseTx";
		  $this->strCancelURL="https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorCancelTx";
		  $this->strPurchaseURL="https://test.sagepay.com/simulator/VSPDirectGateway.asp";
		  $this->strRefundURL="https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorRefundTx";
		  $this->strReleaseURL="https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorReleaseTx";
		  $this->strRepeatURL="https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorRepeatTx";
		  $this->strVoidURL="https://test.sagepay.com/simulator/VSPServerGateway.asp?Service=VendorVoidTx";
		  $this->str3DCallbackPage="https://test.sagepay.com/simulator/VSPDirectCallback.asp";
		  $this->strPayPalCompletionURL="https://test.sagepay.com/simulator/paypalcomplete.asp";
		}
	}

	function value($of){
		return $this->$of;
	}

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }

/**************************************************************************************************
* Useful functions for all pages in this kit
**************************************************************************************************/

//Function to redirect browser
function redirect($url){
   if (!headers_sent()) { header('Location: '.$url);
	} else {
	echo '<script type="text/javascript">';
    	echo 'window.location.href="'.$url.'";';
       	echo '</script>';
       	echo '<noscript>';
       	echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
       	echo '</noscript>';
   }
}

// Filters unwanted characters out of an input string.  Useful for tidying up FORM field inputs
function cleanInput($strRawText,$strType){

	if ($strType=="Number") {
		$strClean="0123456789.";
		$bolHighOrder=false;
	}
	else if ($strType=="VendorTxCode") {
		$strClean="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$bolHighOrder=false;
	}
	else {
  		$strClean=" ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789.,'/{}@():?-_&£$=%~<>*+\"";
		$bolHighOrder=true;
	}
	
	$strCleanedText="";
	$iCharPos = 0;
		
	do {
    	// Only include valid characters
		$chrThisChar=substr($strRawText,$iCharPos,1);
			
		if (strspn($chrThisChar,$strClean,0,strlen($strClean))>0) { 
			$strCleanedText=$strCleanedText . $chrThisChar;
		}
		else if ($bolHighOrder==true) {
				// Fix to allow accented characters and most high order bit chars which are harmless 
				if (bin2hex($chrThisChar)>=191) {
					$strCleanedText=$strCleanedText . $chrThisChar;
				}
			}
			
		$iCharPos=$iCharPos+1;
		} while ($iCharPos<strlen($strRawText));
		
  	$cleanInput = ltrim($strCleanedText);
	return $cleanInput;
}

/* Base 64 Encoding function **
** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/
function base64Encode($plain) {
  // Initialise output variable
  $output = "";
  // Do encoding
  $output = base64_encode($plain);
  // Return the result
  return $output;
}

/* Base 64 decoding function **
** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/

function base64Decode($scrambled) {
  // Initialise output variable
  $output = "";
  // Fix plus to space conversion issue
  $scrambled = str_replace(" ","+",$scrambled);
  // Do encoding
  $output = base64_decode($scrambled);
  // Return the result
  return $output;
}

// Function to check validity of email address entered in form fields
function is_valid_email($email) {
  $result = TRUE;
  if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email)) {
    $result = FALSE;
  }
  return $result;
}

/*************************************************************
	Send a post request with cURL
		$url = URL to send request to
		$data = POST data to send (in URL encoded Key=value pairs)
*************************************************************/
function requestPost($url, $data){
	// Set a one-minute timeout for this script
	set_time_limit(60);
	// Initialise output variable
	$output = array();
	// Open the cURL session
	$curlSession = curl_init();
	// Set the URL
	curl_setopt ($curlSession, CURLOPT_URL, $url);
	// No headers, please
	curl_setopt ($curlSession, CURLOPT_HEADER, 0);
	// It's a POST request
	curl_setopt ($curlSession, CURLOPT_POST, 1);
	// Set the fields for the POST
	curl_setopt ($curlSession, CURLOPT_POSTFIELDS, $data);
	// Return it direct, don't print it out
	curl_setopt($curlSession, CURLOPT_RETURNTRANSFER,1); 
	// This connection will timeout in 30 seconds
	curl_setopt($curlSession, CURLOPT_TIMEOUT,30); 
	//The next two lines must be present for the kit to work with newer version of cURL
	//You should remove them if you have any problems in earlier versions of cURL
    	curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, FALSE);
    	curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 1);

	//Send the request and store the result in an array
	$rawresponse = curl_exec($curlSession);
	//Store the raw response for later as it's useful to see for integration and understanding 
	$_SESSION["rawresponse"]=$rawresponse;
	//Split response into name=value pairs
	$response = explode(chr(10), $rawresponse);
	// Check that a connection was made
	if (curl_error($curlSession)){
		// If it wasn't...
		$output['Status'] = "FAIL";
		$output['StatusDetail'] = curl_error($curlSession);
	}

	// Close the cURL session
	curl_close ($curlSession);

	// Tokenise the response
	for ($i=0; $i<count($response); $i++){
		// Find position of first "=" character
		$splitAt = strpos($response[$i], "=");
		// Create an associative (hash) array with key/value pairs ('trim' strips excess whitespace)
		$output[trim(substr($response[$i], 0, $splitAt))] = trim(substr($response[$i], ($splitAt+1)));
	} // END for ($i=0; $i<count($response); $i++)

	// Return the output
	return $output;
	

} // END function requestPost()

function make_payment(){
	/* make protx payment */
	$strPost="VPSProtocol=" . $this->value("strProtocol");
	$strPost=$strPost . "&TxType=" . $this->value("strTransactionType"); //PAYMENT by default.  You can change this in the includes file
	$strPost=$strPost . "&Vendor=" . $this->value("strVendorName");
	$strVendorTxCode = uniqid();
	$strPost=$strPost . "&VendorTxCode=" . $strVendorTxCode; //As generated above

	// Optional: If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id, it should be passed here
	if (strlen($strPartnerID) > 0){
		$strPost=$strPost . "&ReferrerID=" . URLEncode($strPartnerID);  //You can change this in the includes file
	}
	$sngTotal=$_SESSION['checkout_payment_amount'];
	if (!$_SESSION['checkout_payment_amount'] && $_SESSION['grand_total']){
		$sngTotal=$_SESSION['grand_total'];
	}
	$strPost=$strPost . "&Amount=" . number_format($sngTotal,2); //Formatted to 2 decimal places with leading digit but no commas or currency symbols **
	$strPost=$strPost . "&Currency=" . $this->value("strCurrency");
	// Up to 100 chars of free format description
	$strPost=$strPost . "&Description=" . urlencode("Online Sales");

	// Card details
	$strCardNumber=$_POST['new_card_number'];
	$strCardHolder=urlencode($_POST['new_name_on_card']);
	//$strStartDate=str_replace("/","",$_POST['new_start_date']);
	//$strExpiryDate=str_replace("/","",$_POST['new_expiry_date']);
	$sendStartDate=trim($_POST['new_start_date-0']);
	$sendStartDate2=trim($_POST['new_start_date-1']);
	$sendExpDate=trim($_POST['new_expiry_date-0']);
	$sendExpDate2=trim($_POST['new_expiry_date-1']);
	if (strlen($sendStartDate)==1){ $sendStartDate="0" . $sendStartDate;}
	if (strlen($sendStartDate2)==1){ $sendStartDate2="0" . $sendStartDate2;}
	if (strlen($sendExpDate)==1){ $sendExpDate="0" . $sendExpDate;}
	if (strlen($sendExpDate2)==1){ $sendExpDate2="0" . $sendExpDate2;}

	$strStartDate=$sendStartDate . $sendStartDate2;
	$strExpiryDate=$sendExpDate . $sendExpDate2;
	$strIssueNumber=$_POST['new_issue_number'];
	$strCV2=$_POST['new_cv2'];
	$strCardType=$_POST['new_card_type'];
	$strPost=$strPost . "&CardHolder=" . $strCardHolder;
	$strPost=$strPost . "&CardNumber=" . $strCardNumber;
	if (strlen($strStartDate)>0){ $strPost=$strPost . "&StartDate=" . $strStartDate; }
	$strPost=$strPost . "&ExpiryDate=" . $strExpiryDate;
	if (strlen($strIssueNumber)>0) {$strPost=$strPost . "&IssueNumber=" . $strIssueNumber; }
	$strPost=$strPost . "&CV2=" . $strCV2;
	$strPost=$strPost . "&CardType=" . $strCardType;
			
	// Billing Details 
	global $user;
	global $db;
	global $mycart;
	$buy_requires_login=$mycart->value("buy_requires_login");
	if (!$buy_requires_login || ($buy_requires_login==2 && $_SESSION['checkout_without_login'])){
		$usersql="SELECT * from order_user_data WHERE id = " . $_SESSION['non_account_user_id'];
	} else {
		$usersql="SELECT * from user WHERE id = " . $user->value("id");
	}
	$res=$db->query($usersql);
	$h=$db->fetch_array($res);
	$first_name=$h['first_name'];
	$second_name=$h['second_name'];
	$address_1=$h['address_1'];
	$address_2=$h['address_2'];
	$address_3=$h['address_3'];
	$city=$h['city'];
	$us_state_code=$h['us_billing_state'];
	$county_or_state=$h['county_or_state'];
	$zip_or_postal_code=$h['zip_or_postal_code'];
	$country=$db->field_from_record_from_id("countries",$h['country'],"Name");
	$country_iso_2_code=$db->field_from_record_from_id("countries",$h['country'],"ISO_2_letter_code");

	$strPost=$strPost . "&BillingFirstnames=" . urlencode($first_name);
	$strPost=$strPost . "&BillingSurname=" . urlencode($second_name);
	$strPost=$strPost . "&BillingAddress1=" . urlencode($address_1);
	if (strlen($address_2) > 0) $strPost=$strPost . "&BillingAddress2=" . urlencode($address_2);
	$strPost=$strPost . "&BillingCity=" . urlencode($city);
	$strPost=$strPost . "&BillingPostCode=" . urlencode($zip_or_postal_code);
	$strPost=$strPost . "&BillingCountry=" . urlencode($country_iso_2_code);
	if ($us_state_code){
		$strPost=$strPost . "&BillingState=" . urlencode($us_state_code);
	}
	//if (strlen($_SESSION["strBillingState"]) > 0) $strPost=$strPost . "&BillingState=" . urlencode($_SESSION["strBillingState"]);
	//if (strlen($_SESSION["strBillingPhone"]) > 0) $strPost=$strPost . "&BillingPhone=" . urlencode($_SESSION["strBillingPhone"]);

	// Delivery Details
	$strPost=$strPost . "&DeliveryFirstnames=" . urlencode($first_name);
	$strPost=$strPost . "&DeliverySurname=" . urlencode($second_name);
	$strPost=$strPost . "&DeliveryAddress1=" . urlencode($address_1);
	if (strlen($address_2) > 0) $strPost=$strPost . "&DeliveryAddress2=" . urlencode($address_2);
	$strPost=$strPost . "&DeliveryCity=" . urlencode($city);
	$strPost=$strPost . "&DeliveryPostCode=" . urlencode($zip_or_postal_code);
	$strPost=$strPost . "&DeliveryCountry=" . urlencode($country_iso_2_code);
        if ($us_state_code){
                $strPost=$strPost . "&DeliveryState=" . urlencode($us_state_code);
        }

		
	/* For PAYPAL cardtype only: Fully qualified domain name of the URL to which customers are redirected upon 
	** completion of a PAYPAL transaction. Here we are getting strYourSiteFQDN & strVirtualDir from  
	** the includes file. Must begin with http:// or https:// */

	// Set other optionals
	//$strPost=$strPost . "&CustomerEMail=" . urlencode($_SESSION["strCustomerEMail"]);
	//$strPost=$strPost . "&Basket=" . urlencode($strBasket); //As created above

	// For charities registered for Gift Aid, set to 1 to makr this as a Gift Aid transaction
	//$strPost=$strPost . "&GiftAidPayment=0";
			
	/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default
	** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
	if ($strTransactionType!=="AUTHENTICATE") {$strPost=$strPost . "&ApplyAVSCV2=1"; } // MATTPLATTS
		
	// Send the IP address of the person entering the card details
	$strPost=$strPost . "&ClientIPAddress=" . $_SERVER['REMOTE_ADDR'];

	/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default and uses the online rulebase! **
	** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
	$strPost=$strPost . "&Apply3DSecure=0";
			
	/* Send the account type to be used for this transaction.  Web sites should us E for e-commerce **
	** If you are developing back-office applications for Mail Order/Telephone order, use M **
	** If your back office application is a subscription system with recurring transactions, use C **
	** Your Sage Pay account MUST be set up for the account type you choose.  If in doubt, use E **/
	$strPost=$strPost . "&AccountType=E";

	/* The full transaction registration POST has now been built **
	** Send the post to the target URL
	** if anything goes wrong with the connection process:
	** - $arrResponse["Status"] will be 'FAIL';
	** - $arrResponse["StatusDetail"] will be set to describe the problem 
	** Data is posted to strPurchaseURL which is set depending on whether you are using SIMULATOR, TEST or LIVE */
	//print "<p>Post string is <textarea rows=\"12\" cols=\"70\">" . $strPost . "</textarea>";
	$arrResponse = $this->requestPost($this->value("strPurchaseURL"), $strPost);
	//var_dump($arrResponse);	
	/* Analyse the response from Sage Pay Direct to check that everything is okay
	** Registration results come back in the Status and StatusDetail fields */
	$strStatus=$arrResponse["Status"];
	$strStatusDetail=$arrResponse["StatusDetail"];
	$dbStatus=$strStatus . " - " . $strStatusDetail;
							
	//$insertsql = "INSERT INTO sagepay_responses (VendorTxCode, Amount, Status, order_id) Values(\"".$db->db_escape($strVendorTxCode)."\",$sngTotal,\"".$db->db_escape($dbStatus)."\",".$db->db_escape($_SESSION['order_id']).")";
	//$res=$db->query($insertsql);

	if ($strStatus=="NOTAUTHED"){
		$log_response=$this->log_sagepay_response($user->value("id"),$_SESSION['order_id'],$strVendorTxCode,$sngTotal,$strPost,$arrResponse);
		$not_authed_message=$db->field_from_record_from_id("templates",$this->value("strNotAuthedMessageTemplate"),"template");
		$not_authed_message=str_replace("{=status_detail}",$strStatusDetail,$not_authed_message);
		print $not_authed_message;
		return;
	} elseif ($strStatus=="MALFORMED"){
		$log_response=$this->log_sagepay_response($user->value("id"),$_SESSION['order_id'],$strVendorTxCode,$sngTotal,$strPost,$arrResponse);
		$malformed_message=$db->field_from_record_from_id("templates",$this->value("strMalformedMessageTemplate"),"template");
		$malformed_message=str_replace("{=status_detail}",$strStatusDetail,$malformed_message);
		print $malformed_message;
		return;
	}
	if ($strStatus=="3DAUTH") {
		$log_response=$this->log_sagepay_response($user->value("id"),$_SESSION['order_id'],$strVendorTxCode,$sngTotal,$strPost,$arrResponse);
		/* This is a 3D-Secure transaction, so we need to redirect the customer to their bank
		** for authentication.  First get the pertinent information from the response */
		$strMD=$arrResponse["MD"];
		$strACSURL=$arrResponse["ACSURL"];
		$strPAReq=$arrResponse["PAReq"];
		$strPageState="3DRedirect";
?>
	<!--<SCRIPT LANGUAGE="Javascript"> function OnLoadEvent() { document.form.submit(); } </SCRIPT>//-->
<?
	print "<FORM name=\"threed_s_form\" action=\"" . $strACSURL . "\" method=\"POST\" target=\"3DIFrame\"/>
		<input type=\"hidden\" name=\"PaReq\" value=\"" . $strPAReq . "\"/>
		<input type=\"hidden\" name=\"TermUrl\" value=\"" . $this->value("strYourSiteFQDN") . "3DCallback.php?VendorTxCode=" . $strVendorTxCode . "\"/>
		<input type=\"hidden\" name=\"MD\" value=\"" . $strMD . "\"/>
		<p>You will now be directed to your own bank to verify your card as part of Verified By Visa / Mastercard Securecode 3D Authentication.</p>
		<p>Please click the button below to Authenticate your card</p><input type=\"submit\" value=\"Authenticate\"/></p>
		</form> ";
		return;
	} elseif ($strStatus=="PPREDIRECT") {
			$log_response=$this->log_sagepay_response($user->value("id"),$_SESSION['order_id'],$strVendorTxCode,$sngTotal,$strPost,$arrResponse);
		    /* The customer needs to be redirected to a PayPal URL as PayPal was chosen as a card type or
		    ** payment method and PayPal is active for your account. A VPSTxId and a PayPalRedirectURL are
		    ** returned in this response so store the VPSTxId in your database now to match to the response
		    ** after the customer is redirected to the PayPalRedirectURL to go through PayPal authentication */
		    $strPayPalRedirectURL=$arrResponse["PayPalRedirectURL"];
		    $strVPSTxId=$arrResponse["VPSTxId"];
		    $strPageState="PayPalRedirect";

		    // Update the current order in the database to store the newly acquired VPSTxId 
		    $strSQL="UPDATE sagepay_responses SET VPSTxId='" . $db->db_escape($strVPSTxId) . "' WHERE VendorTxCode='" . $db->db_escape($strVendorTxCode) . "'";
				$result=$db->query($strSQL) or die ("Query '$query' failed with error message: \"" . ->db_error () . '"');
				$strSQL="";
		    
		    // Redirect customer to go through PayPal Authentication
				ob_end_flush();
				$this->redirect($strPayPalRedirectURL);
				exit();
	} else {
		$log_response=$this->log_sagepay_response($user->value("id"),$_SESSION['order_id'],$strVendorTxCode,$sngTotal,$strPost,$arrResponse);
			global $db;
			/* If this isn't 3D-Auth, then this is an authorisation result (either successful or otherwise) **
			** Get the results form the POST if they are there */
			$strVPSTxId=$arrResponse["VPSTxId"];
			$strSecurityKey=$arrResponse["SecurityKey"];
			$strTxAuthNo=$arrResponse["TxAuthNo"];
			$strAVSCV2=$arrResponse["AVSCV2"];
			$strAddressResult=$arrResponse["AddressResult"];
			$strPostCodeResult=$arrResponse["PostCodeResult"];
			$strCV2Result=$arrResponse["CV2Result"];
			$str3DSecureStatus=$arrResponse["3DSecureStatus"];
			$strCAVV=$arrResponse["CAVV"];
					
			// Update the database and redirect the user appropriately
			if ($strStatus=="OK")
				$strDBStatus="AUTHORISED - The transaction was successfully authorised with the bank.";
			elseif ($strStatus=="MALFORMED")
				$strDBStatus="MALFORMED - The StatusDetail was:" . $db->db_escape(substr($strStatusDetail,0,255));
			elseif ($strStatus=="INVALID")
				$strDBStatus="INVALID - The StatusDetail was:" . $db->db_escape(substr($strStatusDetail,0,255));
				
			elseif ($strStatus=="NOTAUTHED")
				$strDBStatus="DECLINED - The transaction was not authorised by the bank.";
			elseif ($strStatus=="REJECTED")
				$strDBStatus="REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
			elseif ($strStatus=="AUTHENTICATED")
				$strDBStatus="AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
			elseif ($strStatus=="REGISTERED")
				$strDBStatus="REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
			elseif ($strStatus=="ERROR")
				$strDBStatus="ERROR - There was an error during the payment process.  The error details are: " . $db->db_escape($strStatusDetail);
			else
				$strDBStatus="UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . $db->db_escape($strStatus) . ", with StatusDetail:" . $db->db_escape($strStatusDetail);


			// Update our database with the results from the Notification POST
			$strSQL="UPDATE sagepay_responses set Status='" . $strDBStatus . "'";
			if (strlen($strVPSTxId)>0) $strSQL=$strSQL . ",VPSTxId='" . $db->db_escape($strVPSTxId) . "'";
			if (strlen($strSecurityKey)>0) $strSQL=$strSQL . ",SecurityKey='" . $db->db_escape($strSecurityKey) . "'";
			if (strlen($strTxAuthNo)>0) $strSQL=$strSQL . ",TxAuthNo=" . $db->db_escape($strTxAuthNo);
			if (strlen($strAVSCV2)>0) $strSQL=$strSQL . ",AVSCV2='" . $db->db_escape($strAVSCV2) . "'";
			if (strlen($strAddressResult)>0) $strSQL=$strSQL . ",AddressResult='" . $db->db_escape($strAddressResult) . "'";
			if (strlen($strPostCodeResult)>0) $strSQL=$strSQL . ",PostCodeResult='" . $db->db_escape($strPostCodeResult) . "'";
			if (strlen($strCV2Result)>0) $strSQL=$strSQL . ",CV2Result='" . $db->db_escape($strCV2Result) . "'";
			if (strlen($strGiftAid)>0) $strSQL=$strSQL . ",GiftAid=" . $db->db_escape($strGiftAid);
			if (strlen($str3DSecureStatus)>0) $strSQL=$strSQL . ",ThreeDSecureStatus='" . $db->db_escape($str3DSecureStatus) . "'";
			if (strlen($strCAVV)>0) $strSQL=$strSQL . ",CAVV='" . $db->db_escape($strCAVV) . "'";
			if (strlen($strDBStatus)>0) $strSQL=$strSQL . ",Status='" . $db->db_escape($strDBStatus) . "'";
			if (strlen($_SESSION['order_id'])>0) $strSQL=$strSQL . ",order_id='" . $db->db_escape($_SESSION['order_id']) . "'";
			$strSQL=$strSQL . " where VendorTxCode='" . $db->db_escape($strVendorTxCode) . "'";

			$result=$db->query($strSQL) or die ("Query '$query' failed with error message: \"" . ->db_error () . '"');

		// Work out where to send the customer
		//$_SESSION["VendorTxCode"]=$strVendorTxCode;
		if (($strStatus=="OK")||($strStatus=="AUTHENTICATED")||($strStatus=="REGISTERED")) {
			$strCompletionURL=$this->value("strCompletionURL");
		} else {
			$strPageError=$strDBStatus;
			$this->set_value("strOrderFailURL",$this->value("strOrderFailURL")) . "?e_msg=".$strPageError;
		}
		// Finally, if we're in LIVE then go stright to the success page
		//In other modes, we allow this page to display and ask for Proceed to be clicked
		if ($this->value("strConnectTo")=="LIVE" || $this->value("strConnectTo")=="TEST") {
			if (($strStatus=="OK")||($strStatus=="AUTHENTICATED")||($strStatus=="REGISTERED")) {
				ob_end_clean();
				// here we clear the cart contents and do the appropriate emails...
				global $mycart;
				// before the session vars for the cart are cleared down, store the order_id
				$order_id=$_SESSION['order_id'];
				$return_content=$mycart->complete_order_after_payment_taken();
				$order_complete_url=$this->value("strCompletionURL");
				$order_complete_url=str_replace("{=order_id}","$order_id",$order_complete_url);
				$this->redirect($order_complete_url); // this function is now in the parent class, can be called using parent::
			} else {
				ob_end_clean();
				$this->redirect($this->value("strOrderFailURL")); // this function is now in the parent class, can be called using parent::
			}
			exit();
		} else {
			print "This is neither the test or live server.";
		}
	}
}

function make_payment_2($details){
	/* details contains the following */
	$order_number=$details['order_number'];
	$payment_amount=$details['amount'];
	$order_type=$details['order_type']; // either immediate or preorder
	$purchase_url=$details['purchase_url'];
	/* make protx payment */
	$strPost="VPSProtocol=" . $this->value("strProtocol");
	if ($order_type=="preorder"){
		$transaction_type="AUTHENTICATE";
	} else {
		$transaction_type="PAYMENT";
	}

	$strPost=$strPost . "&TxType=" . $transaction_type; //PAYMENT by default.  You can change this in the includes file
	$strPost=$strPost . "&Vendor=" . $this->value("strVendorName");
	$strVendorTxCode = uniqid();
	$strPost=$strPost . "&VendorTxCode=" . $strVendorTxCode; //As generated above

	// Optional: If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id, it should be passed here
	if (strlen($strPartnerID) > 0){
		$strPost=$strPost . "&ReferrerID=" . URLEncode($strPartnerID);  //You can change this in the includes file
	}
	$strPost=$strPost . "&Amount=" . number_format($payment_amount,2); //Formatted to 2 decimal places with leading digit but no commas or currency symbols **
	$strPost=$strPost . "&Currency=" . $this->value("strCurrency");
	// Up to 100 chars of free format description
	$strPost=$strPost . "&Description=" . urlencode("Online Sales");

	// Card details
	$strCardNumber=$_POST['new_card_number'];
	$strCardHolder=urlencode($_POST['new_name_on_card']);
	//$strStartDate=str_replace("/","",$_POST['new_start_date']);
	//$strExpiryDate=str_replace("/","",$_POST['new_expiry_date']);
	$sendStartDate=trim($_POST['new_start_date-0']);
	$sendStartDate2=trim($_POST['new_start_date-1']);
	$sendExpDate=trim($_POST['new_expiry_date-0']);
	$sendExpDate2=trim($_POST['new_expiry_date-1']);
	if (strlen($sendStartDate)==1){ $sendStartDate="0" . $sendStartDate;}
	if (strlen($sendStartDate2)==1){ $sendStartDate2="0" . $sendStartDate2;}
	if (strlen($sendExpDate)==1){ $sendExpDate="0" . $sendExpDate;}
	if (strlen($sendExpDate2)==1){ $sendExpDate2="0" . $sendExpDate2;}

	$strStartDate=$sendStartDate . $sendStartDate2;
	$strExpiryDate=$sendExpDate . $sendExpDate2;
	$strIssueNumber=$_POST['new_issue_number'];
	$strCV2=$_POST['new_cv2'];
	$strCardType=$_POST['new_card_type'];
	$strPost=$strPost . "&CardHolder=" . $strCardHolder;
	$strPost=$strPost . "&CardNumber=" . $strCardNumber;
	if (strlen($strStartDate)>0){ $strPost=$strPost . "&StartDate=" . $strStartDate; }
	$strPost=$strPost . "&ExpiryDate=" . $strExpiryDate;
	if (strlen($strIssueNumber)>0) {$strPost=$strPost . "&IssueNumber=" . $strIssueNumber; }
	$strPost=$strPost . "&CV2=" . $strCV2;
	$strPost=$strPost . "&CardType=" . $strCardType;
			
	// Billing Details 
	global $user;
	global $db;
	global $mycart;
	$buy_requires_login=$mycart->value("buy_requires_login");
	if (!$buy_requires_login || ($buy_requires_login==2 && $_SESSION['checkout_without_login'])){
		$usersql="SELECT * from order_user_data WHERE id = " . $_SESSION['non_account_user_id'];
	} else {
		$usersql="SELECT * from user WHERE id = " . $user->value("id");
	}
	$res=$db->query($usersql);
	$h=$db->fetch_array($res);
	$first_name=$h['first_name'];
	$second_name=$h['second_name'];
	$address_1=$h['address_1'];
	$address_2=$h['address_2'];
	$address_3=$h['address_3'];
	$city=$h['city'];
	$us_state_code=$h['us_billing_state'];
	$county_or_state=$h['county_or_state'];
	$zip_or_postal_code=$h['zip_or_postal_code'];
	$country=$db->field_from_record_from_id("countries",$h['country'],"Name");
	$country_iso_2_code=$db->field_from_record_from_id("countries",$h['country'],"ISO_2_letter_code");

	$strPost=$strPost . "&BillingFirstnames=" . urlencode($first_name);
	$strPost=$strPost . "&BillingSurname=" . urlencode($second_name);
	$strPost=$strPost . "&BillingAddress1=" . urlencode($address_1);
	if (strlen($address_2) > 0) $strPost=$strPost . "&BillingAddress2=" . urlencode($address_2);
	$strPost=$strPost . "&BillingCity=" . urlencode($city);
	$strPost=$strPost . "&BillingPostCode=" . urlencode($zip_or_postal_code);
	$strPost=$strPost . "&BillingCountry=" . urlencode($country_iso_2_code);
	if ($us_state_code){
		$strPost=$strPost . "&BillingState=" . urlencode($us_state_code);
	}
	//if (strlen($_SESSION["strBillingState"]) > 0) $strPost=$strPost . "&BillingState=" . urlencode($_SESSION["strBillingState"]);
	//if (strlen($_SESSION["strBillingPhone"]) > 0) $strPost=$strPost . "&BillingPhone=" . urlencode($_SESSION["strBillingPhone"]);

	// Delivery Details
	$strPost=$strPost . "&DeliveryFirstnames=" . urlencode($first_name);
	$strPost=$strPost . "&DeliverySurname=" . urlencode($second_name);
	$strPost=$strPost . "&DeliveryAddress1=" . urlencode($address_1);
	if (strlen($address_2) > 0) $strPost=$strPost . "&DeliveryAddress2=" . urlencode($address_2);
	$strPost=$strPost . "&DeliveryCity=" . urlencode($city);
	$strPost=$strPost . "&DeliveryPostCode=" . urlencode($zip_or_postal_code);
	$strPost=$strPost . "&DeliveryCountry=" . urlencode($country_iso_2_code);
        if ($us_state_code){
                $strPost=$strPost . "&DeliveryState=" . urlencode($us_state_code);
        }

		
	/* For PAYPAL cardtype only: Fully qualified domain name of the URL to which customers are redirected upon 
	** completion of a PAYPAL transaction. Here we are getting strYourSiteFQDN & strVirtualDir from  
	** the includes file. Must begin with http:// or https:// */

	// Set other optionals
	//$strPost=$strPost . "&CustomerEMail=" . urlencode($_SESSION["strCustomerEMail"]);
	//$strPost=$strPost . "&Basket=" . urlencode($strBasket); //As created above

	// For charities registered for Gift Aid, set to 1 to makr this as a Gift Aid transaction
	//$strPost=$strPost . "&GiftAidPayment=0";
			
	/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default
	** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
	if ($strTransactionType!=="AUTHENTICATE") {$strPost=$strPost . "&ApplyAVSCV2=1"; } // MATTPLATTS
		
	// Send the IP address of the person entering the card details
	$strPost=$strPost . "&ClientIPAddress=" . $_SERVER['REMOTE_ADDR'];

	/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default and uses the online rulebase! **
	** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
	$strPost=$strPost . "&Apply3DSecure=0";
			
	/* Send the account type to be used for this transaction.  Web sites should us E for e-commerce **
	** If you are developing back-office applications for Mail Order/Telephone order, use M **
	** If your back office application is a subscription system with recurring transactions, use C **
	** Your Sage Pay account MUST be set up for the account type you choose.  If in doubt, use E **/
	$strPost=$strPost . "&AccountType=E";

	/* The full transaction registration POST has now been built **
	** Send the post to the target URL
	** if anything goes wrong with the connection process:
	** - $arrResponse["Status"] will be 'FAIL';
	** - $arrResponse["StatusDetail"] will be set to describe the problem 
	** Data is posted to strPurchaseURL which is set depending on whether you are using SIMULATOR, TEST or LIVE */
	//print "<p>Post string is <textarea rows=\"12\" cols=\"70\">" . $strPost . "</textarea>";
	$arrResponse = $this->requestPost($purchase_url, $strPost);
	//var_dump($arrResponse);	
	/* Analyse the response from Sage Pay Direct to check that everything is okay
	** Registration results come back in the Status and StatusDetail fields */
	$strStatus=$arrResponse["Status"];
	$strStatusDetail=$arrResponse["StatusDetail"];
	$dbStatus=$strStatus . " - " . $strStatusDetail;
							
	//$insertsql = "INSERT INTO sagepay_responses (VendorTxCode, Amount, Status, order_id) Values(\"".$db->db_escape($strVendorTxCode)."\",$payment_amount,\"".$db->db_escape($dbStatus)."\",".$db->db_escape($_SESSION['order_id']).")";
	//$res=$db->query($insertsql);

	$return_values['status']=$strStatus;	

	if ($strStatus=="NOTAUTHED"){
		$log_response=$this->log_sagepay_response($user->value("id"),$order_number,$strVendorTxCode,$payment_amount,$strPost,$arrResponse);
		$not_authed_message=$db->field_from_record_from_id("templates",$this->value("strNotAuthedMessageTemplate"),"template");
		$not_authed_message=str_replace("{=status_detail}",$strStatusDetail,$not_authed_message);
		$return_values['print_message']=$not_authed_message;
		return $return_values;
	} elseif ($strStatus=="MALFORMED"){
		$log_response=$this->log_sagepay_response($user->value("id"),$order_number,$strVendorTxCode,$payment_amount,$strPost,$arrResponse);
		$malformed_message=$db->field_from_record_from_id("templates",$this->value("strMalformedMessageTemplate"),"template");
		$malformed_message=str_replace("{=status_detail}",$strStatusDetail,$malformed_message);
		$return_values['print_message']=$malformed_message;
		return $return_values;
	}
	if ($strStatus=="3DAUTH") {
		$log_response=$this->log_sagepay_response($user->value("id"),$order_number,$strVendorTxCode,$payment_amount,$strPost,$arrResponse);
		/* This is a 3D-Secure transaction, so we need to redirect the customer to their bank
		** for authentication.  First get the pertinent information from the response */
		$strMD=$arrResponse["MD"];
		$strACSURL=$arrResponse["ACSURL"];
		$strPAReq=$arrResponse["PAReq"];
		$strPageState="3DRedirect";
		print "<FORM name=\"threed_s_form\" action=\"" . $strACSURL . "\" method=\"POST\" target=\"3DIFrame\"/>
		<input type=\"hidden\" name=\"PaReq\" value=\"" . $strPAReq . "\"/>
		<input type=\"hidden\" name=\"TermUrl\" value=\"" . $this->value("strYourSiteFQDN") . "3DCallback.php?VendorTxCode=" . $strVendorTxCode . "\"/>
		<input type=\"hidden\" name=\"MD\" value=\"" . $strMD . "\"/>
		<p>You will now be directed to your own bank to verify your card as part of Verified By Visa / Mastercard Securecode 3D Authentication.</p>
		<p>Please click the button below to Authenticate your card</p><input type=\"submit\" value=\"Authenticate\"/></p>
		</form> ";
		return;
	} elseif ($strStatus=="PPREDIRECT") {
			$log_response=$this->log_sagepay_response($user->value("id"),$order_number,$strVendorTxCode,$payment_amount,$strPost,$arrResponse);
		    /* The customer needs to be redirected to a PayPal URL as PayPal was chosen as a card type or
		    ** payment method and PayPal is active for your account. A VPSTxId and a PayPalRedirectURL are
		    ** returned in this response so store the VPSTxId in your database now to match to the response
		    ** after the customer is redirected to the PayPalRedirectURL to go through PayPal authentication */
		    $strPayPalRedirectURL=$arrResponse["PayPalRedirectURL"];
		    $strVPSTxId=$arrResponse["VPSTxId"];
		    $strPageState="PayPalRedirect";

		    // Update the current order in the database to store the newly acquired VPSTxId 
		    $strSQL="UPDATE sagepay_responses SET VPSTxId='" . $db->db_escape($strVPSTxId) . "' WHERE VendorTxCode='" . $db->db_escape($strVendorTxCode) . "'";
				$result=$db->query($strSQL) or die ("Query '$query' failed with error message: \"" . ->db_error () . '"');
				$strSQL="";
		    
		    // Redirect customer to go through PayPal Authentication
				ob_end_flush();
				$this->redirect($strPayPalRedirectURL);
				exit();
	} else {
		$log_response=$this->log_sagepay_response($user->value("id"),$order_number,$strVendorTxCode,$payment_amount,$strPost,$arrResponse);
			global $db;
			/* If this isn't 3D-Auth, then this is an authorisation result (either successful or otherwise) **
			** Get the results form the POST if they are there */
			$strVPSTxId=$arrResponse["VPSTxId"];
			$strSecurityKey=$arrResponse["SecurityKey"];
			$strTxAuthNo=$arrResponse["TxAuthNo"];
			$strAVSCV2=$arrResponse["AVSCV2"];
			$strAddressResult=$arrResponse["AddressResult"];
			$strPostCodeResult=$arrResponse["PostCodeResult"];
			$strCV2Result=$arrResponse["CV2Result"];
			$str3DSecureStatus=$arrResponse["3DSecureStatus"];
			$strCAVV=$arrResponse["CAVV"];
					
			// Update the database and redirect the user appropriately
			if ($strStatus=="OK")
				$strDBStatus="AUTHORISED - The transaction was successfully authorised with the bank.";
			elseif ($strStatus=="MALFORMED")
				$strDBStatus="MALFORMED - The StatusDetail was:" . $db->db_escape(substr($strStatusDetail,0,255));
			elseif ($strStatus=="INVALID")
				$strDBStatus="INVALID - The StatusDetail was:" . $db->db_escape(substr($strStatusDetail,0,255));
				
			elseif ($strStatus=="NOTAUTHED")
				$strDBStatus="DECLINED - The transaction was not authorised by the bank.";
			elseif ($strStatus=="REJECTED")
				$strDBStatus="REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
			elseif ($strStatus=="AUTHENTICATED")
				$strDBStatus="AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
			elseif ($strStatus=="REGISTERED")
				$strDBStatus="REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
			elseif ($strStatus=="ERROR")
				$strDBStatus="ERROR - There was an error during the payment process.  The error details are: " . $db->db_escape($strStatusDetail);
			else
				$strDBStatus="UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . $db->db_escape($strStatus) . ", with StatusDetail:" . $db->db_escape($strStatusDetail);


			// Update our database with the results from the Notification POST
			$strSQL="UPDATE sagepay_responses set Status='" . $strDBStatus . "'";
			if (strlen($strVPSTxId)>0) $strSQL=$strSQL . ",VPSTxId='" . $db->db_escape($strVPSTxId) . "'";
			if (strlen($strSecurityKey)>0) $strSQL=$strSQL . ",SecurityKey='" . $db->db_escape($strSecurityKey) . "'";
			if (strlen($strTxAuthNo)>0) $strSQL=$strSQL . ",TxAuthNo=" . $db->db_escape($strTxAuthNo);
			if (strlen($strAVSCV2)>0) $strSQL=$strSQL . ",AVSCV2='" . $db->db_escape($strAVSCV2) . "'";
			if (strlen($strAddressResult)>0) $strSQL=$strSQL . ",AddressResult='" . $db->db_escape($strAddressResult) . "'";
			if (strlen($strPostCodeResult)>0) $strSQL=$strSQL . ",PostCodeResult='" . $db->db_escape($strPostCodeResult) . "'";
			if (strlen($strCV2Result)>0) $strSQL=$strSQL . ",CV2Result='" . $db->db_escape($strCV2Result) . "'";
			if (strlen($strGiftAid)>0) $strSQL=$strSQL . ",GiftAid=" . $db->db_escape($strGiftAid);
			if (strlen($str3DSecureStatus)>0) $strSQL=$strSQL . ",ThreeDSecureStatus='" . $db->db_escape($str3DSecureStatus) . "'";
			if (strlen($strCAVV)>0) $strSQL=$strSQL . ",CAVV='" . $db->db_escape($strCAVV) . "'";
			if (strlen($strDBStatus)>0) $strSQL=$strSQL . ",Status='" . $db->db_escape($strDBStatus) . "'";
			if (strlen($order_number)>0) $strSQL=$strSQL . ",order_id='" . $db->db_escape($order_number) . "'";
			$strSQL=$strSQL . " where VendorTxCode='" . $db->db_escape($strVendorTxCode) . "'";
			print "logging order no $order_number!<br>";
			$result=$db->query($strSQL) or die ("Query '$query' failed with error message: \"" . ->db_error () . '"');

		// Work out where to send the customer
		//$_SESSION["VendorTxCode"]=$strVendorTxCode;
		if (($strStatus=="OK")||($strStatus=="AUTHENTICATED")||($strStatus=="REGISTERED")) {
			$strCompletionURL=$this->value("strCompletionURL");
		} else {
			$strCompletionURL=$this->value("strOrderFailURL");
			$strPageError=$strDBStatus;
			$strCompletionURL .= "?e_msg=".$strPageError;
		}
		// Finally, if we're in LIVE then go stright to the success page
		//In other modes, we allow this page to display and ask for Proceed to be clicked
		if ($this->value("strConnectTo")=="LIVE" || $this->value("strConnectTo")=="TEST") {
			if (($strStatus=="OK")||($strStatus=="AUTHENTICATED")||($strStatus=="REGISTERED")) {
				//ob_end_clean();
				// here we clear the cart contents and do the appropriate emails...
				//global $mycart;
				//$return_content=$mycart->complete_order_after_payment_taken($order_number);
			} else {
				//ob_end_clean();
			}
			$return_values['redirect_url']=$strCompletionURL;
			return $return_values;
			//$this->redirect($strCompletionURL); // this function is now in the parent class, can be called using parent::
			exit();
		} else {
			print "This is neither the test or live server.";
		}
	}
}

function authorise_preorder($details){

	$related_vendor_tx_code=$details['relatedVendorTxCode']; // must be sent through with the details from the order
	$relatedVPSTxId=$details['relatedVPSTxId'];
	$related_security_key=$details['relatedSecurityKey'];
	$user_id=$details['user_id'];

	$amount=$details['amount']; // must be sent through with the details from the order
        $order_number=$details['order_number'];
        $payment_amount=$details['amount'];
        $order_type=$details['order_type']; // either immediate or preorder
        $purchase_url=$details['purchase_url'];

	$description="Preorder authorise for order no $order_number";

	$transaction_type="AUTHORISE";
	$strVendorTxCode = uniqid();
	$strPost="VPSProtocol=" . $this->value("strProtocol");
	$strPost=$strPost . "&TxType=" . $transaction_type;
	$strPost=$strPost . "&Vendor=" . $this->value("strVendorName");
	$strPost=$strPost . "&VendorTxCode=" . $strVendorTxCode;
	$strPost=$strPost . "&Amount=" . $amount;
	$strPost=$strPost . "&Currency=" . $this->value("strCurrency");
	$strPost=$strPost . "&Description=" . $description;
	$strPost=$strPost . "&RelatedVPSTxId=" . $relatedVPSTxId;
	$strPost=$strPost . "&RelatedSecurityKey=" . $related_security_key;
	$strPost=$strPost . "&RelatedVendorTxCode=" . $related_vendor_tx_code;
	//print "POST IS $strPost\n\n";

	$this->strAuthoriseURL="https://live.sagepay.com/gateway/service/authorise.vsp";
	$arrResponse = $this->requestPost($this->value("strAuthoriseURL"), $strPost);
	//print "resp is ";
	//var_dump($arrResponse);
	//print "\n\n";

	$strStatus=$arrResponse["Status"];
	$strStatusDetail=$arrResponse["StatusDetail"];
	$dbStatus=$strStatus . " - " . $strStatusDetail;
							
	$log_response=$this->log_sagepay_auth_response($user_id,$order_number,$strVendorTxCode,$amount,$strPost,$arrResponse);
	$return['value']=0;
	if ($strStatus=="OK"){
		$return['value']=1;
	}	
	$return['status']=$strStatus;
	$return['statusdetail']=$strStatusDetail;
	$return['relatedVPSTxId']=$relatedVPSTxId;
	return $return;
}

// authorise preorders
function log_sagepay_auth_response($user_id,$order_id,$strVendorTxCode,$payment_amount,$strPost,$arrResponse){
	$strStatus=$arrResponse["Status"];
	$strStatusDetail=$arrResponse["StatusDetail"];
	$dbStatus=$strStatus . " - " . $strStatusDetail;

	$strVPSTxId=$db->db_escape($arrResponse["VPSTxId"]);
	$strSecurityKey=$db->db_escape($arrResponse["SecurityKey"]);
	$strTxAuthNo=$db->db_escape($arrResponse["TxAuthNo"]);
	$strAVSCV2=$db->db_escape($arrResponse["AVSCV2"]);
	$strAddressResult=$db->db_escape($arrResponse["AddressResult"]);
	$strPostCodeResult=$db->db_escape($arrResponse["PostCodeResult"]);
	$strCV2Result=$db->db_escape($arrResponse["CV2Result"]);

	global $db;
	$insertsql = "INSERT INTO sagepay_responses (";
	$insertsql .= "VendorTxCode, Amount, Status, order_id, user_id, VPSTxId,SecurityKey,TxAuthNo,AVSCV2,AddressResult,PostCodeResult,CV2Result,full_post_string,log_point) Values(\"".$db->db_escape($strVendorTxCode)."\",$payment_amount,\"".$db->db_escape($dbStatus)."\",".$db->db_escape($order_id).",$user_id,\"$strVPSTxId\",\"$strSecurityKey\",\"$strTxAuthNo\",\"$strAVSCV2\",\"$strAddressResult\",\"$strPostCodeResult\",\"$strCV2Result\",\"".$db->db_escape($strPost)."\",\"INS:log_sagepay_auth_response\")";
	$res=$db->query($insertsql);

	return 1;
}

function capture_payment_card_details(){
	//$content = "<hr size=\"1\" />";
	//$content .= "<h3>Secure Credit Card Form</h3>\n";
	//$content .= "<p class=\"dbf_para_secure\">Your credit card details are taken over a secure connection and sent directly to our payment provider.</p>";
	//$content .= "<p><b>Please enter your card details below:</b></p>\n";
	global $user;
	$dbforms_options=array();
	$dbforms_options['filter']=database_functions::load_dbforms_filter(189); // need to dynamically look up the filter id here, and also use the filters module...
	$cc_form = database_functions::form_from_table("credit_card_details","add_row","","",$dbforms_options);
	if (is_array($cc_form)){
		$content .= $cc_form['Message'];
	}
	$content .= $cc_form;
	return $content;
}

function log_sagepay_response($user_id,$order_id,$strVendorTxCode,$payment_amount,$strPost,$arrResponse){

	$strStatus=$arrResponse["Status"];
	$strStatusDetail=$arrResponse["StatusDetail"];
	$dbStatus=$strStatus . " - " . $strStatusDetail;
	$user_id=$db->db_escape($user_id);
	if (!$user_id){
		if ($_SESSION['non_account_user_id']){
			$user_id=$_SESSION['non_account_user_id'];
		}
	}

	$strVPSTxId=$db->db_escape($arrResponse["VPSTxId"]);
	$strSecurityKey=$db->db_escape($arrResponse["SecurityKey"]);
	$strTxAuthNo=$db->db_escape($arrResponse["TxAuthNo"]);
	$strAVSCV2=$db->db_escape($arrResponse["AVSCV2"]);
	$strAddressResult=$db->db_escape($arrResponse["AddressResult"]);
	$strPostCodeResult=$db->db_escape($arrResponse["PostCodeResult"]);
	$strCV2Result=$db->db_escape($arrResponse["CV2Result"]);
	$str3DSecureStatus=$db->db_escape($arrResponse["3DSecureStatus"]);
	$strCAVV=$db->db_escape($arrResponse["CAVV"]);

	global $db;
	$insertsql = "INSERT INTO sagepay_responses (";
	$insertsql .= "VendorTxCode, Amount, Status, order_id, user_id, VPSTxId,SecurityKey,TxAuthNo,AVSCV2,AddressResult,PostCodeResult,CV2Result,ThreeDSecureStatus,CAVV,full_post_string,log_point) Values(\"".$db->db_escape($strVendorTxCode)."\",$payment_amount,\"".$db->db_escape($dbStatus)."\",".$db->db_escape($order_id).",$user_id,\"$strVPSTxId\",\"$strSecurityKey\",\"$strTxAuthNo\",\"$strAVSCV2\",\"$strAddressResult\",\"$strPostCodeResult\",\"$strCV2Result\",\"$str3DSecureStatus\",\"$strCAVV\",\"".$db->db_escape($strPost)."\",\"INS:log_resp_func_in_class\")";
	$res=$db->query($insertsql);

	return 1;
}

function make_individual_payment(){
	/* make protx payment */
	$strPost="VPSProtocol=" . $this->value("strProtocol");
	$strPost=$strPost . "&TxType=" . $this->value("strTransactionType"); //PAYMENT by default.  You can change this in the includes file
	$strPost=$strPost . "&Vendor=" . $this->value("strVendorName");
	$strVendorTxCode = uniqid();
	$strPost=$strPost . "&VendorTxCode=" . $strVendorTxCode; //As generated above

	// Optional: If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id, it should be passed here
	if (strlen($strPartnerID) > 0){
		$strPost=$strPost . "&ReferrerID=" . URLEncode($strPartnerID);  //You can change this in the includes file
	}
	$sngTotal=$_SESSION['grand_total'];
	$strPost=$strPost . "&Amount=" . number_format($sngTotal,2); //Formatted to 2 decimal places with leading digit but no commas or currency symbols **
	$strPost=$strPost . "&Currency=" . $this->value("strCurrency");
	// Up to 100 chars of free format description
	$strPost=$strPost . "&Description=" . urlencode("Online Sales");

	// Card details
	$strCardNumber=$_POST['new_card_number'];
	$strCardHolder=urlencode($_POST['new_name_on_card']);
	//$strStartDate=str_replace("/","",$_POST['new_start_date']);
	//$strExpiryDate=str_replace("/","",$_POST['new_expiry_date']);
	$sendStartDate=trim($_POST['new_start_date-0']);
	$sendStartDate2=trim($_POST['new_start_date-1']);
	$sendExpDate=trim($_POST['new_expiry_date-0']);
	$sendExpDate2=trim($_POST['new_expiry_date-1']);
	if (strlen($sendStartDate)==1){ $sendStartDate="0" . $sendStartDate;}
	if (strlen($sendStartDate2)==1){ $sendStartDate2="0" . $sendStartDate2;}
	if (strlen($sendExpDate)==1){ $sendExpDate="0" . $sendExpDate;}
	if (strlen($sendExpDate2)==1){ $sendExpDate2="0" . $sendExpDate2;}

	$strStartDate=$sendStartDate . $sendStartDate2;
	$strExpiryDate=$sendExpDate . $sendExpDate2;
	$strIssueNumber=$_POST['new_issue_number'];
	$strCV2=$_POST['new_cv2'];
	$strCardType=$_POST['new_card_type'];
	$strPost=$strPost . "&CardHolder=" . $strCardHolder;
	$strPost=$strPost . "&CardNumber=" . $strCardNumber;
	if (strlen($strStartDate)>0){ $strPost=$strPost . "&StartDate=" . $strStartDate; }
	$strPost=$strPost . "&ExpiryDate=" . $strExpiryDate;
	if (strlen($strIssueNumber)>0) {$strPost=$strPost . "&IssueNumber=" . $strIssueNumber; }
	$strPost=$strPost . "&CV2=" . $strCV2;
	$strPost=$strPost . "&CardType=" . $strCardType;
			
	// Billing Details 
	global $user;
	global $db;
	$usersql="SELECT * from user WHERE id = " . $user->value("id");
	$res=$db->query($usersql);
	$h=$db->fetch_array($res);
	$first_name=$h['first_name'];
	$second_name=$h['second_name'];
	$address_1=$h['address_1'];
	$address_2=$h['address_2'];
	$address_3=$h['address_3'];
	$city=$h['city'];
	$us_state_code=$h['us_billing_state'];
	$county_or_state=$h['county_or_state'];
	$zip_or_postal_code=$h['zip_or_postal_code'];
	$country=$db->field_from_record_from_id("countries",$h['country'],"Name");
	$country_iso_2_code=$db->field_from_record_from_id("countries",$h['country'],"ISO_2_letter_code");

	$strPost=$strPost . "&BillingFirstnames=" . urlencode($first_name);
	$strPost=$strPost . "&BillingSurname=" . urlencode($second_name);
	$strPost=$strPost . "&BillingAddress1=" . urlencode($address_1);
	if (strlen($address_2) > 0) $strPost=$strPost . "&BillingAddress2=" . urlencode($address_2);
	$strPost=$strPost . "&BillingCity=" . urlencode($city);
	$strPost=$strPost . "&BillingPostCode=" . urlencode($zip_or_postal_code);
	$strPost=$strPost . "&BillingCountry=" . urlencode($country_iso_2_code);
	if ($us_state_code){
		$strPost=$strPost . "&BillingState=" . urlencode($us_state_code);
	}
	//if (strlen($_SESSION["strBillingState"]) > 0) $strPost=$strPost . "&BillingState=" . urlencode($_SESSION["strBillingState"]);
	//if (strlen($_SESSION["strBillingPhone"]) > 0) $strPost=$strPost . "&BillingPhone=" . urlencode($_SESSION["strBillingPhone"]);

	// Delivery Details
	$strPost=$strPost . "&DeliveryFirstnames=" . urlencode($first_name);
	$strPost=$strPost . "&DeliverySurname=" . urlencode($second_name);
	$strPost=$strPost . "&DeliveryAddress1=" . urlencode($address_1);
	if (strlen($address_2) > 0) $strPost=$strPost . "&DeliveryAddress2=" . urlencode($address_2);
	$strPost=$strPost . "&DeliveryCity=" . urlencode($city);
	$strPost=$strPost . "&DeliveryPostCode=" . urlencode($zip_or_postal_code);
	$strPost=$strPost . "&DeliveryCountry=" . urlencode($country_iso_2_code);
        if ($us_state_code){
                $strPost=$strPost . "&DeliveryState=" . urlencode($us_state_code);
        }

		
	/* For PAYPAL cardtype only: Fully qualified domain name of the URL to which customers are redirected upon 
	** completion of a PAYPAL transaction. Here we are getting strYourSiteFQDN & strVirtualDir from  
	** the includes file. Must begin with http:// or https:// */

	// Set other optionals
	//$strPost=$strPost . "&CustomerEMail=" . urlencode($_SESSION["strCustomerEMail"]);
	//$strPost=$strPost . "&Basket=" . urlencode($strBasket); //As created above

	// For charities registered for Gift Aid, set to 1 to makr this as a Gift Aid transaction
	//$strPost=$strPost . "&GiftAidPayment=0";
			
	/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default
	** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
	if ($strTransactionType!=="AUTHENTICATE") {$strPost=$strPost . "&ApplyAVSCV2=1"; } // MATTPLATTS
		
	// Send the IP address of the person entering the card details
	$strPost=$strPost . "&ClientIPAddress=" . $_SERVER['REMOTE_ADDR'];

	/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default and uses the online rulebase! **
	** It can be changed dynamically, per transaction, if you wish.  See the Sage Pay Direct Protocol document */
	$strPost=$strPost . "&Apply3DSecure=0";
			
	/* Send the account type to be used for this transaction.  Web sites should us E for e-commerce **
	** If you are developing back-office applications for Mail Order/Telephone order, use M **
	** If your back office application is a subscription system with recurring transactions, use C **
	** Your Sage Pay account MUST be set up for the account type you choose.  If in doubt, use E **/
	$strPost=$strPost . "&AccountType=E";

	/* The full transaction registration POST has now been built **
	** Send the post to the target URL
	** if anything goes wrong with the connection process:
	** - $arrResponse["Status"] will be 'FAIL';
	** - $arrResponse["StatusDetail"] will be set to describe the problem 
	** Data is posted to strPurchaseURL which is set depending on whether you are using SIMULATOR, TEST or LIVE */
	//print "<p>Post string is <textarea rows=\"12\" cols=\"70\">" . $strPost . "</textarea>";
	$arrResponse = $this->requestPost($this->value("strPurchaseURL"), $strPost);
	//var_dump($arrResponse);	
	/* Analyse the response from Sage Pay Direct to check that everything is okay
	** Registration results come back in the Status and StatusDetail fields */
	$strStatus=$arrResponse["Status"];
	$strStatusDetail=$arrResponse["StatusDetail"];
	$dbStatus=$strStatus . " - " . $strStatusDetail;
							
	$log_response=$this->log_sagepay_response($user->value("id"),$_SESSION['order_id'],$strVendorTxCode,$sngTotal,$strPost,$arrResponse);
	//$insertsql = "INSERT INTO sagepay_responses (VendorTxCode, Amount, Status, order_id) Values(\"".$db->db_escape($strVendorTxCode)."\",$sngTotal,\"".$db->db_escape($dbStatus)."\",".$db->db_escape($_SESSION['order_id']).")";
	//$res=$db->query($insertsql);

	if ($strStatus=="NOTAUTHED"){
		$not_authed_message=$db->field_from_record_from_id("templates",$this->value("strNotAuthedMessageTemplate"),"template");
		$not_authed_message=str_replace("{=status_detail}",$strStatusDetail,$not_authed_message);
		print $not_authed_message;
		return;
	} elseif ($strStatus=="MALFORMED"){
		$malformed_message=$db->field_from_record_from_id("templates",$this->value("strMalformedMessageTemplate"),"template");
		$malformed_message=str_replace("{=status_detail}",$strStatusDetail,$malformed_message);
		print $malformed_message;
		return;
	}
	if ($strStatus=="3DAUTH") {
		/* This is a 3D-Secure transaction, so we need to redirect the customer to their bank
		** for authentication.  First get the pertinent information from the response */
		$strMD=$arrResponse["MD"];
		$strACSURL=$arrResponse["ACSURL"];
		$strPAReq=$arrResponse["PAReq"];
		$strPageState="3DRedirect";
?>
	<!--<SCRIPT LANGUAGE="Javascript"> function OnLoadEvent() { document.form.submit(); } </SCRIPT>//-->
<?
	print "<FORM name=\"threed_s_form\" action=\"" . $strACSURL . "\" method=\"POST\" target=\"3DIFrame\"/>
		<input type=\"hidden\" name=\"PaReq\" value=\"" . $strPAReq . "\"/>
		<input type=\"hidden\" name=\"TermUrl\" value=\"" . $this->value("strYourSiteFQDN") . "3DCallback-payment.php?VendorTxCode=" . $strVendorTxCode . "\"/>
		<input type=\"hidden\" name=\"MD\" value=\"" . $strMD . "\"/>
		<p style=\"font-weight:bold\">Thank you for entering your payment details.</p><p> As part of the 3D secure program, you will now be directed to your own bank to verify your card details with them.</p>
		<p>Please click the button below to continue and authenticate your card</p><input type=\"submit\" value=\"Authenticate with my bank\"/></p>
		</form> ";
		return;
	} elseif ($strStatus=="PPREDIRECT") {
		    /* The customer needs to be redirected to a PayPal URL as PayPal was chosen as a card type or
		    ** payment method and PayPal is active for your account. A VPSTxId and a PayPalRedirectURL are
		    ** returned in this response so store the VPSTxId in your database now to match to the response
		    ** after the customer is redirected to the PayPalRedirectURL to go through PayPal authentication */
		    $strPayPalRedirectURL=$arrResponse["PayPalRedirectURL"];
		    $strVPSTxId=$arrResponse["VPSTxId"];
		    $strPageState="PayPalRedirect";

		    // Update the current order in the database to store the newly acquired VPSTxId 
		    $strSQL="UPDATE sagepay_responses SET VPSTxId='" . $db->db_escape($strVPSTxId) . "' WHERE VendorTxCode='" . $db->db_escape($strVendorTxCode) . "'";
				$result=$db->query($strSQL) or die ("Query '$query' failed with error message: \"" . ->db_error () . '"');
				$strSQL="";
		    
		    // Redirect customer to go through PayPal Authentication
				ob_end_flush();
				$this->redirect($strPayPalRedirectURL);
				exit();
	} else {
			global $db;
			/* If this isn't 3D-Auth, then this is an authorisation result (either successful or otherwise) **
			** Get the results form the POST if they are there */
			$strVPSTxId=$arrResponse["VPSTxId"];
			$strSecurityKey=$arrResponse["SecurityKey"];
			$strTxAuthNo=$arrResponse["TxAuthNo"];
			$strAVSCV2=$arrResponse["AVSCV2"];
			$strAddressResult=$arrResponse["AddressResult"];
			$strPostCodeResult=$arrResponse["PostCodeResult"];
			$strCV2Result=$arrResponse["CV2Result"];
			$str3DSecureStatus=$arrResponse["3DSecureStatus"];
			$strCAVV=$arrResponse["CAVV"];
					
			// Update the database and redirect the user appropriately
			if ($strStatus=="OK")
				$strDBStatus="AUTHORISED - The transaction was successfully authorised with the bank.";
			elseif ($strStatus=="MALFORMED")
				$strDBStatus="MALFORMED - The StatusDetail was:" . $db->db_escape(substr($strStatusDetail,0,255));
			elseif ($strStatus=="INVALID")
				$strDBStatus="INVALID - The StatusDetail was:" . $db->db_escape(substr($strStatusDetail,0,255));
				
			elseif ($strStatus=="NOTAUTHED")
				$strDBStatus="DECLINED - The transaction was not authorised by the bank.";
			elseif ($strStatus=="REJECTED")
				$strDBStatus="REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
			elseif ($strStatus=="AUTHENTICATED")
				$strDBStatus="AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
			elseif ($strStatus=="REGISTERED")
				$strDBStatus="REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
			elseif ($strStatus=="ERROR")
				$strDBStatus="ERROR - There was an error during the payment process.  The error details are: " . $db->db_escape($strStatusDetail);
			else
				$strDBStatus="UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . $db->db_escape($strStatus) . ", with StatusDetail:" . $db->db_escape($strStatusDetail);


			// Update our database with the results from the Notification POST
			$strSQL="UPDATE sagepay_responses set Status='" . $strDBStatus . "'";
			if (strlen($strVPSTxId)>0) $strSQL=$strSQL . ",VPSTxId='" . $db->db_escape($strVPSTxId) . "'";
			if (strlen($strSecurityKey)>0) $strSQL=$strSQL . ",SecurityKey='" . $db->db_escape($strSecurityKey) . "'";
			if (strlen($strTxAuthNo)>0) $strSQL=$strSQL . ",TxAuthNo=" . $db->db_escape($strTxAuthNo);
			if (strlen($strAVSCV2)>0) $strSQL=$strSQL . ",AVSCV2='" . $db->db_escape($strAVSCV2) . "'";
			if (strlen($strAddressResult)>0) $strSQL=$strSQL . ",AddressResult='" . $db->db_escape($strAddressResult) . "'";
			if (strlen($strPostCodeResult)>0) $strSQL=$strSQL . ",PostCodeResult='" . $db->db_escape($strPostCodeResult) . "'";
			if (strlen($strCV2Result)>0) $strSQL=$strSQL . ",CV2Result='" . $db->db_escape($strCV2Result) . "'";
			if (strlen($strGiftAid)>0) $strSQL=$strSQL . ",GiftAid=" . $db->db_escape($strGiftAid);
			if (strlen($str3DSecureStatus)>0) $strSQL=$strSQL . ",ThreeDSecureStatus='" . $db->db_escape($str3DSecureStatus) . "'";
			if (strlen($strCAVV)>0) $strSQL=$strSQL . ",CAVV='" . $db->db_escape($strCAVV) . "'";
			if (strlen($strDBStatus)>0) $strSQL=$strSQL . ",Status='" . $db->db_escape($strDBStatus) . "'";
			if (strlen($_SESSION['order_id'])>0) $strSQL=$strSQL . ",order_id='" . $db->db_escape($_SESSION['order_id']) . "'";
			$strSQL = $strSQL . ",log_point='UPD:non_3d_in_class'";
			$strSQL=$strSQL . " where VendorTxCode='" . $db->db_escape($strVendorTxCode) . "'";

			$result=$db->query($strSQL) or die ("Query '$query' failed with error message: \"" . ->db_error () . '"');

		// Work out where to send the customer
		//$_SESSION["VendorTxCode"]=$strVendorTxCode;
		if (($strStatus=="OK")||($strStatus=="AUTHENTICATED")||($strStatus=="REGISTERED")) {
			$strCompletionURL=$this->value("strCompletionURL");
		} else {
			$strCompletionURL=$this->value("strOrderFailURL");
			$strPageError=$strDBStatus;
			$strCompletionURL .= "?e_msg=".$strPageError;
		}
		// Finally, if we're in LIVE then go stright to the success page
		//In other modes, we allow this page to display and ask for Proceed to be clicked
		if ($this->value("strConnectTo")=="LIVE" || $this->value("strConnectTo")=="TEST") {
			if (($strStatus=="OK")||($strStatus=="AUTHENTICATED")||($strStatus=="REGISTERED")) {
				ob_end_clean();
				// here we clear the cart contents and do the appropriate emails...
				global $mycart;
				$return_content=$mycart->complete_order_after_payment_taken();
			} else {
				ob_end_clean();
			}


			unset($_SESSION['checkout_modules_add_to_total']);
			unset($_SESSION['total_of_all_orders']);
			unset($_SESSION['total_of_all_orders_inc']);
			unset($_SESSION['all_preorders_grand_total']);
			unset($_SESSION['preorder_shipping_amount']);
			unset($_SESSION['preorder_shipping_rate']);
			unset($_SESSION['preorder_total_price']);
			unset($_SESSION['preorder_checkout_modules_add_to_total']);


			$this->redirect($strCompletionURL); // this function is now in the parent class, can be called using parent::
			exit();
		} else {
			print "This is neither the test or live server.";
		}
	}
}


} // end class

//$protx=new sagepay_direct();
//var_dump($protx->value("strConnectTo"));

?>
