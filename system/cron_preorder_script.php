<?php
$act=0;
set_time_limit(120);
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
$sql="SELECT orders.id as order_number, orders.grand_total, orders.ordered_by, orders.non_account_order, release_date, DATE_FORMAT(release_date,\"%D %M %Y\")AS release_date_formatted, payment_method, quantity_in_stock, products.title, artists.artist,  
IF (((DATE_SUB(NOW(), INTERVAL 7 DAY) > release_date) OR (release_date < NOW())),\"time\",\"not time\") AS time, 
IF (((DATE_SUB(NOW(), INTERVAL 7 DAY) > release_date) OR (release_date < NOW())) AND quantity_in_stock>0,\"ready\",\"not ready\") AS ready, 
sagepay_responses.* FROM orders LEFT JOIN sagepay_responses ON orders.id=sagepay_responses.order_id INNER JOIN order_products ON orders.id=order_products.order_id INNER JOIN products ON order_products.product_id = products.ID INNER JOIN artists ON products.artist = artists.id WHERE orders.complete=5 AND paypal_preorder_reminder_sent IS NULL AND (preorder_auth_fail IS NULL OR preorder_auth_fail=\"0000-00-00 00:00:00\") AND preorder_date_shipped IS NULL ORDER BY products.release_date ASC";
//$sql .= " LIMIT 10";
$rv=$db->query($sql);
$num_rows=mysql_num_rows($rv);
print "<h2>Pre Order Bot - Running at " . date("D M d, Y G:i a") . "</h2>";
print "<p><b>The pre-order bot found $num_rows pre-orders in the system.</b></p>";
$incrementor=0;
while ($h=$db->fetch_array($rv)){
	$incrementor++;
	//print $h['order_number'] . ": " . $h['Status'] . " - VTxC:" . $h['VPSTxId'] . " - SC: " . $h['SecurityKey'] . " - VTC: " . $h['VendorTxCode'];
	//print "\n";

	if ($h['ready']=="ready"){$col="green"; } else { $col="red"; }
	$readytext="<span style=\"color:$col\">".ucfirst($h['ready'])."</span>";
	print "<p>$incrementor: <b>#".$h['order_number'] . ":</b> " . $h['artist'] . " - " . $h['title'] . "<br />\n";
	print "Stock level is " . $h['quantity_in_stock'] . " - Release date is " . $h['release_date_formatted'] . " - (" . $h['time'] . ") - <b>" . $readytext . "</b>\n";
	if ($h["ready"]!="ready"){
		$action_taken['message'] = "Order #".$h['order_number'] . " is not yet ready to be sent - no action has been taken.";
		$action_taken['status']=0;
		if ($act){
			$report_result=log_preorder_in_report($h['order_number'],$action_taken);
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
	$sagepay_fail=0;
	print "Payment method: " . $h['payment_method'] . "<br />";



	if ($h['payment_method']=="sagepay_direct"){
		$details['order_type']="authorise_preorder";
		$details['relatedVPSTxId']=$h['VPSTxId'];
		$details['relatedSecurityKey']=$h['SecurityKey'];
		$details['relatedVendorTxCode']=$h['VendorTxCode']; // currently not being logged
		print "Would now try and authorise..."; 
		if ($act){
			$payment_result=$attempt_auth->authorise_preorder($details);
			$sagepay_fail=0;
		} else {
			$payment_result['value']=0;
			$sagepay_fail=1;
		}
		print "\nOrder NO: " . $h['order_number'] . " - Got return from sagepay auth of " . $payment_result['value'] . "\n";
	} else if ($h['payment_method']=="paypal_express_checkout"){
		$paypal_message_result="1"; // paypal is not already paid for - we have to send an email.
	}
	print "Payment method is " . $h['payment_method'] . " customer id is " . $h['ordered_by'];
	if (!$h['payment_method']){ print " <span style=\"color:red;\">There is no payment method associated with this transaction!</span>"; print "EXITING"; exit; }
	$action_taken=array();
	if ($payment_result['value']==1){
		print "On the sagepay loop";
		// do the order post to flight logistics
		require_once("$libpath/classes/flight_logistics_order_post.php");
		$flight= new flight_logistics_order_post($details);
		$flight_result=$flight->post_existing_order_to_flight_logistics($details['order_number']);
		// set order as shipped 
		print "Posting to Flight Logistics";
		$update_order_sql="UPDATE orders set preorder_date_shipped = NOW(), complete = 1 WHERE id = " . $details['order_number'];
		$rv=$db->query($update_order_sql);
		// send some email
		$mail_to="mattplatts@gmail.com";
		$subject="Pre Order #".$details['order_number']. " has been charged and sent";
		$message="Order number " . $details['order_number'] . " has been successfully authorised by Sagepay and sent to flight logistics.";
		$headers="From: \"Gonzo Preorder Bot\" <preorders@gonzomultimedia.co.uk>\r\n";
		mail($mail_to,$subject,$message,$headers);
		$action_taken['message'] = "Order #".$details['order_number']." has been successfully authorised by Sagepay, and posted to flight logistics to be sent. The customer will be emailed by Flight Logistics";
		$action_taken['status']=1;

		// send an email to the customer as well...

		// and next preorder!
	} else if ($paypal_message_result==1) {
			print "<h1>on paypal and act is " . $act . "</h1>";
		if ($act){
			send_paypal_email($h['order_number']);
		}
		$mail_to="mattplatts@gmail.com";
		$subject="Pre Order #".$details['order_number']. " has been messaged to the customer.";
		$message="Order number " . $details['order_number'] . " has been successfully messaged to the customer.";
		$headers="From: \"Gonzo Preorder Bot\" <preorders@gonzomultimedia.co.uk>\r\n";
		mail($mail_to,$subject,$message,$headers);
		$action_taken['message'] = "Order #".$h['order_number']." is now available for purchase via paypal - the customer has been emailed to tell them so.";
		$action_taken['status']=1;
	} else if ($sagepay_fail){
		$mail_to="mattplatts@gmail.com";
		$subject="Pre Order #".$details['order_number']. " has failed sagepay authorisation";
		$message="Order number " . $details['order_number'] . " has been unsuccessfully authorised by Sagepay and not sent to flight logistics.";
		$headers="From: \"Gonzo Preorder Bot\" <preorders@gonzomultimedia.co.uk>\r\n";
		mail($mail_to,$subject,$message,$headers);
		if ($act){
			$updsql="UPDATE orders set preorder_auth_fail = NOW() WHERE id = " . $h['order_number'];
			$updrv=$db->query($updsql);
			$action_taken['message'] = "Order #".$details['order_number']." has failed authorisaton at Sagepay, and thus has not been completed or sent to flight logistics.";
			$action_taken['status']=1;
		}
	} else {
		$mail_to="mattplatts@gmail.com";
		$subject="Pre Order #".$details['order_number']. " has encountered an unknown problem";
		$message="Order number " . $details['order_number'] . " has been unsuccessfully authorised by Sagepay and not sent to flight logistics.";
		$headers="From: \"Gonzo Preorder Bot\" <preorders@gonzomultimedia.co.uk>\r\n";
		mail($mail_to,$subject,$message,$headers);
		$updsql="UPDATE orders set preorder_auth_fail = NOW() WHERE id = " . $h['order_number'];
		$updrv=$db->query($updsql);
		$action_taken['message'] = "Order #".$details['order_number']." has encountered an unknown problem, and thus has not been completed or sent to flight logistics.";
		$action_taken['status']=1;
	}
	//var_dump($payment_result['statusdetail']);
	//var_dump($payment_result['relatedVPSTxId']);
	if ($act==2){
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
		print "An impossible scenario has occurred, this program is terminatin before the universe comes to an end.";
	}
}

function log_preorder_in_report($order_id,$action_taken){

	$action_message=$action_taken['message'];
	$action_status=$action_taken['status'];
	$sql="INSERT INTO preorder_bot_report (date,order_id,action_taken,action_status) VALUES (NOW(),$order_id,\"".mysql_real_escape_string($action_message)."\",$action_status)";
	global $db;
	//print $sql;
	$rv=$db->query($sql);
	return 1;

}

function send_paypal_email($order_id){
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
	print $user_sql;
	$user_rv=$db->query($user_sql);
	$user_h=$db->fetch_array($user_rv);
	var_dump($user_h);

	$full_name=$user_h['first_name'] . " " . $user_h['second_name'];
	$email_to=$user_h['email_address'];
	$product_name=$h['artist'] . " - " . $h['title'];


	$headers="From: \"Gonzo Multimedia\" <admin@gonzomultimedia.co.uk>\r\n";
	$headers .= "Content-type:text/html\r\n";
	$subject = "Gonzo Multimedia: Your order #$order_id is in stock and ready to be sent.";
	$message = "<p>Dear $full_name,</p>";
	$message .= "<p>Your recent order for $product_name is now available for purchase at Gonzo Multimedia.</p>";
	$message .= "<table><tr><td valign=\"top\">";
	$message .= "<img src=\"http://www.gonzomultimedia.co.uk/images/product_images/web_quality/".$h['image']."\" width=\"200\" />";
	$message .= "</td><td>".concat_description($h['full_description'])."</td></tr></table>";
	$message .= "<p>The total amount payable as quoted to you previously is &pound;" . $h['grand_total'] . " (&pound;" . $h['bought_price'] . " + &pound;" . $h['shipping_total'] . " for shipping)</p>";
	$message .= "<p><a href=\"http://www.gonzomultimedia.co.uk/pre-order-paypal/$order_id/\">You can complete this transaction by clicking here.</a>";
	mail ($email_to,$subject,$message,$headers);
	$update_order="UPDATE orders SET paypal_preorder_reminder_sent=NOW() where id = $order_id";
	$do_update=$db->query($update_order);
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
?>
