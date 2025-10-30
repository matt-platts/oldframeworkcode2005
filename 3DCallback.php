<?php

print "on callback page!<br>";

require_once("config.php"); // basepath needed by user.php
require_once("$libpath/errors.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");
require_once("$libpath/classes/sagepay_direct.php");

session_start();

$db=new database_connection();
$mycart=new shopping_cart();
$sagepay=new sagepay_direct();
$user=new user();

//Otherwise, create the POST for Sage Pay ensuring to URLEncode the PaRes before sending it
$strMD = $_REQUEST["MD"];
$strPaRes=$_REQUEST["PARes"];

// POST for Sage Pay Direct 3D completion page
$strPost = "MD=" . $strMD . "&PARes=" . urlencode($strPaRes);

//print "Request here<br>";
//var_dump($_REQUEST);
$strVendorTxCode=$_REQUEST["VendorTxCode"];
//print "<hr>";

//Use cURL to POST the data directly from this server to Sage Pay. cURL connection code is in includes.php.
$arrResponse = $sagepay->requestPost($sagepay->value("str3DCallbackPage"), $strPost);
//var_dump($arrResponse);

//print "that was the response, session next<br /><br /><hr size=1>";
//print "all session vars are";
//var_dump($_SESSION);

//Analyse the response from Sage Pay Direct to check that everything is okay
$arrStatus=split(" ",$arrResponse["Status"]);
$strStatus=array_shift($arrStatus);
$arrStatusDetail=split("=",$arrResponse["StatusDetail"]);
$strStatusDetail = array_shift($arrStatusDetail);
		
//Get the results form the POST if they are there
$arrVPSTxId=split(" ",$arrResponse["VPSTxId"]);
$strVPSTxId=array_shift($arrVPSTxId);
$arrSecurityKey=split(" ",$arrResponse["SecurityKey"]);
$strSecurityKey=array_shift($arrSecurityKey);
$arrTxAuthNo=split(" ",$arrResponse["TxAuthNo"]);
$strTxAuthNo=array_shift($arrTxAuthNo);
$arrAVSCV2=split(" ",$arrResponse["AVSCV2"]);
$strAVSCV2=array_shift($arrAVSCV2);
$arrAddressResult=split(" ",$arrResponse["AddressResult"]);
$strAddressResult=array_shift($arrAddressResult);
$arrPostCodeResult=split(" ",$arrResponse["PostCodeResult"]);
$strPostCodeResult=array_shift($arrPostCodeResult);
$arrCV2Result=split(" ",$arrResponse["CV2Result"]);
$strCV2Result=array_shift($arrCV2Result); 
$arr3DSecureStatus=split(" ",$arrResponse["3DSecureStatus"]);
$str3DSecureStatus=array_shift($arr3DSecureStatus);
$arrCAVV=split(" ",$arrResponse["CAVV"]);
$strCAVV=array_shift($arrCAVV);

//Update the database and redirect the user appropriately
if ($strStatus=="OK") {
	$strDBStatus="AUTHORISED - The transaction was successfully authorised with the bank.";
} elseif ($strStatus=="MALFORMED") {
	$strDBStatus="MALFORMED - The StatusDetail was:" . mysql_real_escape_string(substr($strStatusDetail,0,255));
} elseif ($strStatus=="INVALID") {
	$strDBStatus="INVALID - The StatusDetail was:" . mysql_real_escape_string(substr($strStatusDetail,0,255));
} elseif ($strStatus=="NOTAUTHED"){
	$strDBStatus="DECLINED - The transaction was not authorised by the bank.";
} elseif ($strStatus=="REJECTED"){
	$strDBStatus="REJECTED - The transaction was failed by your 3D-Secure or AVS/CV2 rule-bases.";
} elseif ($strStatus=="AUTHENTICATED"){
	$strDBStatus="AUTHENTICATED - The transaction was successfully 3D-Secure Authenticated and can now be Authorised.";
} elseif ($strStatus=="REGISTERED"){
	$strDBStatus="REGISTERED - The transaction was could not be 3D-Secure Authenticated, but has been registered to be Authorised.";
} elseif ($strStatus=="ERROR"){
	$strDBStatus="ERROR - There was an error during the payment process.  The error details are: " . mysql_real_escape_string($strStatusDetail);
} else {
	$strDBStatus="UNKNOWN - An unknown status was returned from Sage Pay.  The Status was: " . mysql_real_escape_string($strStatus) . ", with StatusDetail:" . mysql_real_escape_string($strStatusDetail);
}

// UPDATE THE SQL DATABASE HERE
$insertsql = "INSERT INTO sagepay_responses (VendorTxCode) Values(\"".mysql_real_escape_string($strVendorTxCode)."\")";
$res=$db->query($insertsql) or die("Insert sql problem " . mysql_error());
// Update our database with the results from the Notification POST
$strSQL="UPDATE sagepay_responses set Status='" . $strDBStatus . "'";
if (isset($strVPSTxId)){ if (strlen($strVPSTxId)>0) $strSQL=$strSQL . ",VPSTxId='" . mysql_real_escape_string($strVPSTxId) . "'";}
if (isset($strSecurityKey)){ if (strlen($strSecurityKey)>0) $strSQL=$strSQL . ",SecurityKey='" . mysql_real_escape_string($strSecurityKey) . "'";}
if (isset($strTxAuthNo)){ if (strlen($strTxAuthNo)>0) $strSQL=$strSQL . ",TxAuthNo=" . mysql_real_escape_string($strTxAuthNo);}
if (isset($strAVSCV2)){ if (strlen($strAVSCV2)>0) $strSQL=$strSQL . ",AVSCV2='" . mysql_real_escape_string($strAVSCV2) . "'";}
if (isset($strAddressResult)){ if (strlen($strAddressResult)>0) $strSQL=$strSQL . ",AddressResult='" . mysql_real_escape_string($strAddressResult) . "'";}
if (isset($strPostCodeResult)){ if (strlen($strPostCodeResult)>0) $strSQL=$strSQL . ",PostCodeResult='" . mysql_real_escape_string($strPostCodeResult) . "'";}
if (isset($strCV2Result)){ if (strlen($strCV2Result)>0) $strSQL=$strSQL . ",CV2Result='" . mysql_real_escape_string($strCV2Result) . "'";}
if (isset($strGiftAid)){ if (strlen($strGiftAid)>0) $strSQL=$strSQL . ",GiftAid=" . mysql_real_escape_string($strGiftAid);}
if (isset($str3DSecureStatus)){ if (strlen($str3DSecureStatus)>0) $strSQL=$strSQL . ",ThreeDSecureStatus='" . mysql_real_escape_string($str3DSecureStatus) . "'";}
if (strlen($strCAVV)>0) $strSQL=$strSQL . ",CAVV='" . mysql_real_escape_string($strCAVV) . "'";
if (strlen($strDBStatus)>0) $strSQL=$strSQL . ",Status='" . mysql_real_escape_string($strDBStatus) . "'";
if (strlen($_REQUEST['order_id'])>0) $strSQL=$strSQL . ",order_id='" . mysql_real_escape_string($_REQUEST['order_id']) . "'";
$strSQL=$strSQL . " where VendorTxCode='" . mysql_real_escape_string($strVendorTxCode) . "'";
$result=$db->query($strSQL) or die ("Query '$query' failed with error message: \"" . mysql_error () . '"');

//Work out where to send the customer
$return_content="";
if ($strStatus=="OK" || $strStatus=="AUTHENTICATED" || $strStatus=="REGISTERED"){
	$strCompletionURL=$sagepay->value("strCompletionURL");
	$return_content=$mycart->complete_order_after_payment_taken();
	
	// post order to flight logistics here!
	// and clear the cart if it all worked
} else {
	$strCompletionURL=$sagepay->value("strOrderFailURL");
	$strPageError=$strDBStatus;
}

//Finally, if we're in LIVE then go straight to the success page
//In other modes, we allow this page to display and ask for Proceed to be clicked
$sagepay->redirect($strCompletionURL);

?>
