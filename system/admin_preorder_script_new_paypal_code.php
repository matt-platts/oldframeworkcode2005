<html>
<head><title>Preorders</title></head>
<body style="background-color:white; color:#222;">
<?php
$act=1;
$passive="";
if ($_GET){
	if ($_GET['passive_mode']=="yes"){ $act=0; $passive="(Passive mode) ";}
}
chdir ("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/system/custom");
require_once("../../config.php");
require_once("$libpath/errors.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");
if (!$db && !$mycart){
	$db=new database_connection();
	$mycart=new shopping_cart();
}

require_once("$libpath/classes/sagepay_direct.php");
$attempt_auth=new sagepay_direct();

// SQL QUERY TO FIND PREORDERS TO PAY FOR
//$sql="SELECT * FROM orders WHERE complete = 5 AND payment_method=\"sagepay_direct\"";
//$sql="SELECT orders.id as order_number, orders.grand_total, orders.ordered_by, orders.non_account_order, sagepay_responses.* from orders INNER JOIN sagepay_responses ON orders.id=sagepay_responses.order_id WHERE orders.pre_order=1 AND VendorTxCode != \"\" AND VPSTxId != \"\" AND SecurityKey != \"\" AND orders.grand_total > 0 AND Status LIKE \"REGISTERED%\"";
$sql="SELECT DISTINCT orders.id as order_number, orders.grand_total, orders.ordered_by, orders.non_account_order, release_date, DATE_FORMAT(release_date,\"%D %M %Y\")AS release_date_formatted, payment_method, quantity_in_stock, products.title, artists.artist, products.ID AS product_id, orders.sagepay_auth_attempts, orders.preorder_pay_immediately, orders.paid, orders.date_paid,  
IF (((DATE_SUB(NOW(), INTERVAL 7 DAY) > release_date) OR (release_date < NOW())),\"time\",\"not time\") AS time, sagepay_auth_attempts,  
IF (((DATE_SUB(NOW(), INTERVAL 7 DAY) > release_date) OR (release_date < NOW())) AND quantity_in_stock>0,\"ready\",\"not ready\") AS ready, 
user.first_name, user.second_name, user.email_address, sagepay_responses.*, paypal_transaction_details.payment_status  FROM orders 
LEFT JOIN sagepay_responses ON orders.id=sagepay_responses.order_id 
LEFT JOIN paypal_transaction_details ON orders.id = paypal_transaction_details.internal_sale_id 
INNER JOIN order_products ON orders.id=order_products.order_id 
INNER JOIN products ON order_products.product_id = products.ID 
INNER JOIN artists ON products.artist = artists.id 
INNER JOIN user ON orders.ordered_by = user.id 
WHERE  
quantity_in_stock > 0 AND 
orders.complete=5 AND  
(paypal_preorder_reminder_sent IS NULL OR paypal_preorder_reminder_sent = \"0000-00-00 00:00:00\" OR paypal_preorder_reminder_sent < NOW() - INTERVAL 3 DAY) 
AND (preorder_auth_fail IS NULL OR preorder_auth_fail=\"0000-00-00 00:00:00\" OR (preorder_auth_fail < NOW() - INTERVAL 3 DAY AND (sagepay_auth_attempts<6 OR sagepay_auth_attempts IS NULL))) AND preorder_date_shipped IS NULL 
GROUP BY orders.id 
ORDER BY products.release_date ASC";
//$sql .= " LIMIT 10";
// AND preorder_pay_immediately != 1 
$rv=$db->query($sql);
$num_rows=mysql_num_rows($rv);
print $sql;
print "\n";
print "<h2>Pre Order Bot - $passive Running at " . date("D M d, Y G:i a") . "</h2>";
print "<p><b>The pre-order bot found $num_rows pre-orders in the system.</b></p>";
$incrementor=0;
while ($h=$db->fetch_array($rv)){
	$incrementor++;
	//print $h['order_number'] . ": " . $h['Status'] . " - VTxC:" . $h['VPSTxId'] . " - SC: " . $h['SecurityKey'] . " - VTC: " . $h['VendorTxCode'];
	//print "\n";

	if ($h['ready']=="ready"){$col="green"; } else { $col="red"; }
	$readytext="<span style=\"color:$col\">".ucfirst($h['ready'])."</span>";
	print "<p>$incrementor: <b>#".$h['order_number'] . ":</b> " . $h['artist'] . " - " . $h['title'] . "( pid " . $h['product_id'] . ")<br />\n";
	print "This order is for : " . $h['first_name'] . " " . $h['second_name'] . " - " . $h['email_address'] . "<br />";
	print "Stock level is " . $h['quantity_in_stock'] . " - Release date is " . $h['release_date_formatted'] . " - (" . $h['time'] . ") - <b>" . $readytext . "</b>\n";
	if ($h["ready"]!="ready"){
		$action_taken['message'] = "Order #".$h['order_number'] . " is not yet ready to be sent - no action has been taken.";
		$action_taken['status']=0;
		if ($act){
			//$report_result=log_preorder_in_report($h['order_number'],$action_taken);
		}
		continue;
	}

	print "<b style=\"color:blue\">This preorder is ready to be sent</b><br />\n ";
	// So we have stock and are within a week of the release date
	$details['order_number']=$h['order_number'];
	if ($h['ordered_by']){
		$details['user_id']=$h['ordered_by'];
	} else if ($h['non_account_order']){
		$details['user_id']=$h['non_account_order'];
	}
	$details['amount']=$h['grand_total'];
	$paypal_message_result=0;
	$paypal_paid_result=0;
	$sagepay_fail=0;
	print "Payment method: " . $h['payment_method'] . "<br />";


	/****** 
				Sagepay - try to take payment first 
	************/
	if ($h['payment_method']=="sagepay_direct"){
		$details['order_type']="authorise_preorder";
		$details['relatedVPSTxId']=$h['VPSTxId'];
		$details['relatedSecurityKey']=$h['SecurityKey'];
		$details['relatedVendorTxCode']=$h['VendorTxCode']; // currently not being logged
		print "Will now try and authorise..."; 
		if ($act){
			$payment_result=$attempt_auth->authorise_preorder($details);
			$sagepay_fail=0;
		} else {
			$payment_result['value']=0;
			$sagepay_fail=1;
		}
		print "\nOrder NO: " . $h['order_number'] . " - Got return from sagepay auth of " . $payment_result['value'] . "\n";


	} else if ($h['payment_method']=="paypal_express_checkout"){
		$paypal_message_result="1"; // we set this as a default and overwrite if we need. Basically, paypal is not already paid for - we have to send an email.
		// NEW CODE - *SOME* Paypal orders may be paid for, we need to look this up...
if ($h['preorder_pay_immediately']==1){
		print "GOT A PREORDER WITH IMMEDIATE PAYMENT!!!!!!!!!!!!!!!!!!!!!!!  ";
	print "\n";
	print "Status: " . $h['payment_status'] . " - Paid:" . $h['paid'] . " - Date paid:" . $h['date_paid'] . "\n";
		if ($h['payment_status']=="Completed" && $h['date_paid'] != ""){
			// this one simply needs to send now...
			$paypal_message_result=0;
			$paypal_paid_result=1;
			print "\n *** Paypal paid is 1 - this needs to simply send - no charges **** \n";
		}
}
		if ($h['payment_status']=="Completed" && $h['date_paid'] != ""){
			// this one simply needs to send now...
			$paypal_message_result=0;
			$paypal_paid_result=1;
			print "\n *** Paypal paid is 1 - this needs to simply send - no charges **** \n";
		} else {
			print " NOT ALREADY PAID VIA PAYPAL : " . $h['payment_status'] . " -- " . $h['paid'] . " -- " . $h['date_paid'] . "*********\n";
		}
	}
	print "Payment method is " . $h['payment_method'] . " customer id is " . $h['ordered_by'];
	print "\n paypal_message_result is $paypal_message_result, paypal_send_result is $paypal_send_result "; 
	if (!$h['payment_method']){ print " <span style=\"color:red;\">There is no payment method associated with this transaction!</span>"; print "EXITING"; exit; }
	$action_taken=array();

	/* Sagepay just got authorised.. */
	if ($payment_result['value']==1){
		print "On the sagepay loop for a successful auth";
		if ($act){
			// do the order post to flight logistics
			require_once("$libpath/classes/flight_logistics_order_post.php");
			$flight= new flight_logistics_order_post($details);
			$flight_result=$flight->post_existing_order_to_flight_logistics($details['order_number']);
			// set order as shipped 
			print "Posting to Flight Logistics";
			$update_order_sql="UPDATE orders set preorder_date_shipped = NOW(), complete = 1 WHERE id = " . $details['order_number'];
			$rv=$db->query($update_order_sql);
			log_posted_now($details['order_number']);
		} else {
			print "Would post to flight here and update order status";
		}
		// send some email
		$mail_to="mattplatts@gmail.com";
		$subject="Pre Order #".$details['order_number']. " has been charged and sent";
		$message="Order number " . $details['order_number'] . " has been successfully authorised by Sagepay and sent to flight logistics.";
		$headers="From: \"RW Preorder Bot\" <preorders@gonzomultimedia.co.uk>\r\n";
		mail($mail_to,$subject,$message,$headers);
		$action_taken['message'] = "Order #".$details['order_number']." has been successfully authorised by Sagepay, and posted to flight logistics to be sent. The customer will be emailed by Flight Logistics";
		$action_taken['status']=1;

		if ($act){
			//$log_action=log_preorder_in_report($details['order_number'],$action_taken);
		}
		// send an email to the customer as well...

		// oh crap we've not done this yet.. although flight do send one!

	/* Paypal did not pay in advance and the customer needs a message... */
	} else if ($paypal_message_result==1) {
		if ($act){
			send_paypal_email($h['order_number'],$h['sagepay_auth_attempts']);
		}
		$mail_to="mattplatts@gmail.com";
		$subject="Pre Order #".$details['order_number']. " has been messaged to the customer.";
		$message="Order number " . $details['order_number'] . " has been successfully messaged to the customer.";
		$headers="From: \"RW Preorder Bot\" <preorders@gonzomultimedia.co.uk>\r\n";
		mail($mail_to,$subject,$message,$headers);
		$action_taken['message'] = "Order #".$h['order_number']." is now available for purchase via paypal - the customer has been emailed to tell them so.";
		$action_taken['status']=1;
		if ($act){
			$log_action=log_preorder_in_report($details['order_number'],$action_taken);
		}

	/* Paypal order is already paid */
	} else if ($paypal_paid_result){
		print "<span style='color:pink'>Paypal is paid - this can be sent!</span>";
			if ($act){
				post_to_flight_from_preorder_script($details);
				print " Just posted previously paid paypal to flight \n";
				$action_taken['message']="Paypal order previously paid was posted to flight";
				$action_taken['status']=1;
			} else {
				print "This is a paypal order that is previously paid, would normally send to flight but is in passive mode";
			}

	/* Sagepay auth failed */
	} else if ($sagepay_fail){
		$mail_to="mattplatts@gmail.com";
		$subject="Gonzo Multimedia - Pre Order #".$details['order_number']. " has failed sagepay authorisation";
		$message="Dear " . $h['first_name'] . " " . $h['last_name'] . "<br /><br />We have tried to process your payment for your pre-order number #" . $details['order_number'] . " which is now in stock, and have not been able to do so. If your card is simply low on funds, we will keep trying until we are able. If your card is no longer valid, we are unable to automatically process this transaction - in this case please reorder from <a href=\"http://www.gonzomultimedia.co.uk\">gonzomultimedia.co.uk</a>.<br /><br />To view more details about this order, please log in to our web site and go to My Account.<br /><br />Thanks,<br />Gonzo Multimedia Web Admin.<br /><br />";
		$headers="From: \"Gonzo Preorders\" <orders@gonzomultimedia.co.uk>\r\nContent-type:text/html\r\n";
		mail($mail_to,$subject,$message,$headers);
		if ($act){
			$sp_auth=$h['sagepay_auth_attempts'];
			if (!$sp_auth){ $sp_auth=1;} else { $sp_auth++;}
			$updsql="UPDATE orders set preorder_auth_fail = NOW(), sagepay_auth_attempts = $sp_auth WHERE id = " . $h['order_number'];
			$updrv=$db->query($updsql);
			print "Sagepay has failed for " . $h['order_number']; 

			$mail_to=$h['email_address'];
			mail($mail_to,$subject,$message,$headers);
			$action_taken['message'] = "Order #".$details['order_number']." has failed authorisaton at Sagepay, and thus has not been completed or sent to flight logistics. The customer has been emailed at " . $h['email_address'] . " to notify them.";
			$action_taken['status']=1;
			if ($act){
				$log_action=log_preorder_in_report($details['order_number'],$action_taken);
			}
		}

	/* The final else - no boxes were ticked, this should not happen */
	} else {
		$mail_to="mattplatts@gmail.com";
		$subject="Pre Order #".$details['order_number']. " has encountered an unknown problem";
		$message="Order number " . $details['order_number'] . " has been unsuccessfully authorised by Sagepay and not sent to flight logistics.";
		$headers="From: \"RW Preorder Bot\" <preorders@gonzomultimedia.co.uk>\r\n";
		mail($mail_to,$subject,$message,$headers);
		$updsql="UPDATE orders set preorder_auth_fail = NOW() WHERE id = " . $h['order_number'];
		$updrv=$db->query($updsql);
		$action_taken['message'] = "Order #".$details['order_number']." has encountered an unknown problem, and thus has not been completed or sent to flight logistics.";
		$action_taken['status']=1;
	}
	//var_dump($payment_result['statusdetail']);
	//var_dump($payment_result['relatedVPSTxId']);
	if ($act){
		$log_action=log_preorder_in_report($details['order_number'],$action_taken);
	}
	$log_action=1;
	print "<br /> - ";
	if ($log_action && $act){
	print "Action logged.. moving on...";
	} else if (!$log_action && $act){
		print "action log failed"; print "EXITING"; exit;
	} else if (!$log_action && !$act){
		print "action not logged, but program is running in safe mode so no problem";
	} else {
		print "An impossible scenario has occurred, this program is terminating before the universe comes to an end.";
	}
	print " - Reached end of loop, moving on to next preorder..";
	continue;
}

print "</body></html>";

function log_preorder_in_report($order_id,$action_taken){

	$action_message=$action_taken['message'];
	$action_status=$action_taken['status'];
	$sql="INSERT INTO preorder_bot_report (date,order_id,action_taken,action_status) VALUES (NOW(),$order_id,\"".mysql_real_escape_string($action_message)."\",$action_status)";
	global $db;
	//print $sql;
	$rv=$db->query($sql);
	return 1;

}

function send_paypal_email($order_id,$attempts){
	global $act;
	if (!$act){ return; }
	print "on send paypal email function";
	global $db;
	$sql="SELECT products.ID,products.title,products.image,products.full_description, order_products.price as bought_price, orders.grand_total, orders.shipping_total, artists.artist,orders.ordered_by, orders.non_account_order FROM order_products INNER JOIN products on products.ID = order_products.product_id INNER JOIN artists on products.artist=artists.id INNER JOIN orders on order_products.order_id = orders.id INNER JOIN product_formats ON products.format = product_formats.id WHERE order_products.order_id = $order_id";
	$rv=$db->query($sql);
	$h=$db->fetch_array($rv);
        if ($h['ordered_by']){
		$user_id=$h['ordered_by'];
		$table="user";
        } else if ($h['non_account_order']){
		$user_id=$h['non_account_order'];
		$table="order_user_data";
        }
	$user_sql="SELECT first_name,second_name,email_address FROM $table WHERE id = " . $user_id;
	//print $user_sql;
	$user_rv=$db->query($user_sql);
	$user_h=$db->fetch_array($user_rv);
	var_dump($user_h);

	$full_name=$user_h['first_name'] . " " . $user_h['second_name'];
	$email_to=$user_h['email_address'];
	$product_name=$h['artist'] . " - " . $h['title'];


	$headers="From: \"Gonzo Multimedia\" <admin@gonzomultimedia.co.uk>\r\n";
	$headers .= "Bcc: mattplatts@gmail.com\r\n";
	$headers .= "Content-type:text/html\r\n";
	$subject = "Gonzo Multimedia: Your order #$order_id is in stock and ready to be sent.";
	$message = "<p>Dear $full_name,</p>";
	$message .= "<p>Your recent order for $product_name is now available for purchase at gonzomultimedia.co.uk.</p>";
	$message .= "<table><tr><td valign=\"top\">";
	$message .= "<img src=\"http://www.gonzomultimedia.co.uk/images/product_images/web_quality/".$h['image']."\" width=\"200\" />";
	$message .= "</td><td>".concat_description($h['full_description'])."</td></tr></table>";
	$message .= "<p>The total amount payable as quoted to you previously is &pound;" . $h['grand_total'] . " (&pound;" . $h['bought_price'] . " + &pound;" . $h['shipping_total'] . " for shipping)</p>";
	$message .= "<p><a href=\"http://www.gonzomultimedia.co.uk/pre-order-paypal/$order_id/\">You can complete this transaction by clicking here.</a>";
	if ($act){
		mail ($email_to,$subject,$message,$headers);
		$attempts = $attempts+1;
		$update_order="UPDATE orders SET paypal_preorder_reminder_sent=NOW(),sagepay_auth_attempts=$attempts where id = $order_id";
		$do_update=$db->query($update_order);
	}
	log_paypal_message_sent($order_id);
}

function concat_description($desc){
	$desc=strip_tags($desc);
	$desc = substr($desc,0,420);
	$words=explode(" ",$desc);
	$discard=array_pop($words);
	$desc=join(" ",$words);
	$desc .= "..";
	return $desc;
}

function post_to_flight_from_preorder_script($details){
	global $db;
	global $libpath;
	// do the order post to flight logistics
	print "here we are on the post function";
	require_once("$libpath/classes/flight_logistics_order_post.php");
	$flight= new flight_logistics_order_post($details);
	print "loaded module";
	$flight_result=$flight->post_existing_order_to_flight_logistics($details['order_number']);
	// set order as shipped 
	print "Posting order #" . $details['order_number'] . " to Flight Logistics";
	$update_order_sql="UPDATE orders set preorder_date_shipped = NOW(), complete = 1 WHERE id = " . $details['order_number'];
	$rv=$db->query($update_order_sql);
	log_posted_now($details['order_number']);
}

/* Logging of bot actions in the few functions below */

function log_bot_action($order_id, $action){
	return;
	global $db;
	$sql="INSERT INTO preorder_bot_logs (order_id,paid_now) values (\"$order_id\",\"$action\")";
	$rv=$db->query($sql);
	return;

}

function log_paid_now($order_id){
	return;
	global $db;
	$sql="INSERT INTO preorder_bot_logs (order_id,paid_now) values (\"$order_id\",\"1\")";
	$rv=$db->query($sql);
	return;

}

function log_posted_now($order_id){
	return;
	global $db;
	$sql="INSERT INTO preorder_bot_logs (order_id,posted_to_flight) values (\"$order_id\",\"1\")";
	$rv=$db->query($sql);
	return;
}

function log_paypal_message_sent($order_id){
	return;
	global $db;
	$sql="INSERT INTO preorder_bot_logs (order_id,message_sent) values (\"$order_id\",\"1\")";
	$rv=$db->query($sql);
	return;
}

?>
