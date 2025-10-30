<?php

$action=$_GET['action'];

function remove_user_from_office($lookup,$oid,$uid){
	$sql="DELETE from user_office_lookup WHERE id=$lookup AND office_id=$oid AND user_id=$uid";
	$res=mysql_query($sql) or die(mysql_error());
	print "<p class=\"admin_header\">User successfully removed from office</p>";
	print "<a href=\"administrator.php?action=display_content&content=108&user=$uid\">Return to View Offices</a>";
}

function approve_registration($reg_id){
	
	$sql="SELECT * from registration_requests WHERE id = $reg_id";
	$res=mysql_query($sql) or die ("Cannot select from registrations: " . mysql_error());
	$h=mysql_fetch_array($res);
	$password=generate_password();
	$md5_password=md5($password);
	$insert_sql = "INSERT INTO user (username, first_name, second_name, email_address, telephone_no, mobile_no, job_title, password_clear, status, type) values (\"".$h['username']."\",\"".$h['first_name']."\",\"".$h['second_name']."\",\"".$h['email']."\",\"".$h['direct_telephone']."\",\"".$h['mobile']."\",\"".$h['job_title']."\",\"".$password."\",\"active\",\"user\")";	
	$insert_res=mysql_query($insert_sql) or die ("Error adding user to user: " . mysql_error());
	
	$insert_res=mysql_query($insert_sql);
	$last_insert=mysql_insert_id();

	// relate user to office
	$officesql = "INSERT into user_office_lookup (user_id,office_id) VALUES($last_insert,".$h['officename'].")";
	$officeres=mysql_query($officesql) or die ("Error adding to oul" . mysql_error());
	
	$message="You are approved for the John Crane Marketing Portal. Please log in using the the following details:\n\n";
	$message .= "User name: " . $h['username'] . "\n";
	$message .= "Password: " . $password . "\n\n";
	$message .= "Please change your password to something that you can remember easily as soon as you log in for the first time.\n\n";
	$message .= "Thanks,\nJohn Crane Marketing Portal.\n";
	$subject="John Crame Marketing Portal - Site Approval";
	$headers="From: \"John Crane Marketing\" <website@johncranemarketingportal.com>\r\n";
	$headers .= "Reply-to: website@johncranemarketingportal.com\r\n";
	mail ($h['email'],$subject,$message,$headers);
	
	$update_reg_sql = "UPDATE registration_requests SET processed = 1 WHERE id = $reg_id";
	$update_res=mysql_query($update_reg_sql) or die(mysql_error());

	print "<p>Registration Complete.</p><p><a href=\"javascript:loadPage('administrator.php?action=display_content&content=112&jx=1')\">Return to Registration Requests</a></p>";
}

function admin_view_ticket($ticketno){

	print "<div class=\"table_title\">View Enquiry</div><div class=\"cleardiv\"></div>";
	print "<p><b>Enquiry no: </b>$ticketno</p>";
	$sql="SELECT * FROM tickets WHERE id = $ticketno";
	$res=mysql_query($sql) or die(mysql_error());
	while ($h=mysql_fetch_array($res)){
		print "<p><b>Description:</b> " . $h['title'] . "</p>";
		print "<table>";
		$sql="SELECT * from ticket_details INNER JOIN user on ticket_details.user_id=user.id where ticket_id=$ticketno order by ticket_details.id desc";
		$res2=mysql_query($sql) or die(mysql_error());
		$count=0;
print '<form name="ticketresponse" action="administrator.php?action=respond_to_ticket" method="post">';
print '<input type="hidden" name="ticketid" value="'.$ticketno.'">';
print '<input type="hidden" name="close" value="">';
print '<textarea rows="3" cols="40" name="response"></textarea>';
print "<table><tr><td>";
print "<span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"Javascript:document.forms['ticketresponse'].submit()\">Respond to enquiry</a></span>";
print "</td><td>";
print "<span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"Javascript:document.forms['ticketresponse'].elements['close'].value='1'; document.forms['ticketresponse'].submit()\">Respond / close</a></span>";

print '</table></form>';
print "<table>";
		while ($h=mysql_fetch_array($res2)){
			if ($count==0){$col="#000000";}else{$col="#444444";}
			print "<tr style=\"color:$col; font-weight:bold; background-color:#f1f1f1\"><td style=\"color:$col\">".$h['date_updated']. " by " . $h['email_address']. "</td></tr><tr style=\"color:$col; border-bottom-width:1px; border-bottom-style:solid; border-bottom-color:#333333\"><td>".$h['ticket_text'] . "<br /><br /></td></tr>";
			$count++;
		}
	}
	print "</table>";

}

function enquiry_response($ticket_id,$response,$close){
	global $user;
        $sql = "INSERT INTO ticket_details (ticket_id,user_id,ticket_text) values($ticket_id,".$user->value("id").",\"$response\")";
        $res=mysql_query($sql) or die(mysql_error());
	if ($close){
		$sql = "UPDATE tickets set last_updated_by=\"admin\", status=\"closed\" WHERE id=$ticket_id";
	} else {
		$sql = "UPDATE tickets set last_updated_by=\"admin\", status=\"Action Required\" WHERE id=$ticket_id";
	}
        $res=mysql_query($sql) or die(mysql_error());
	print "<p class=\"admin_header\">Enquiry Response</p><p>Your response has been posted.</p><p><a href=\"administrator.php?action=list_table&t=tickets&filter_id=124\">Return to enquiries</a>";
}

function admin_view_literature_order($orderno){
	print "<div id=\"table_title_div\">View Order</div><div class=\"cleardiv\"></div>";
	print "<p><b>Order no: </b>$orderno</p>";
	$sql="SELECT date_placed,complete,user.email_address,user.first_name,user.second_name, cost_centre_no, delivery_address,offices.* FROM literature_sales INNER JOIN user on literature_sales.ordered_by= user.id LEFT JOIN offices ON offices.id = literature_sales.delivery_address WHERE literature_sales.id = $orderno";
	$res=mysql_query($sql) or die(mysql_error());
	while ($h=mysql_fetch_array($res)){
		if ($h['complete']==1){$order_completed=1;}
		if ($h['complete']==2){$order_on_order=1;}
		print "<p><b>Ordered by :</b> " . $h['first_name'] . " " . $h['second_name'] . " (<a href=\"mailto:" . $h['email_address'] . "\">" . $h['email_address'] . "</a>)</p>";
		print "<p><b>Order Date / Time:</b> " . $h['date_placed'] . "</p>";
		print "<p><b>Cost Centre no: </b> " . $h['cost_centre_no'] . "</p>";
		print "<p><b>Delivery Office: </b> " . $h['office_name'] . "</p>";
		print "<p><b>Delivery Office Address: </b> <br />";
		$office_address_sql="SELECT * from offices WHERE id=".$h['id'];
		$office_res=mysql_query($office_address_sql) or die("Error getting office detail" . mysql_error());
		while ($k=mysql_fetch_array($office_res,MYSQL_ASSOC)){
			if ($k['business_name']){ $office_data = $k['business_name'] . "<br />";} else { $office_data .= "John Crane<br />";}	
			if ($k['building_name']){ $office_data .= $k['building_name'] . "<br />";}
			$office_data .= $k['addr1'] . "<br />";	
			$office_data .= $k['town'] . "<br />";	
			$office_data .= $k['postcode'] . "<br />";	
			$office_data .= $k['country'] . "<br />";	
		}
		print $office_data . "</p>";
		print "<p><b>The following products were ordered:</b></p>";
		print "<table><tr style=\"font-weight:bold; background-color:#f1f1f1\"><td>Product</td><td>Quantity</td></tr>";
		$sql="SELECT literature_sales_products.*,literature_products.description FROM literature_sales_products INNER JOIN literature_products on literature_sales_products.product_id=literature_products.id WHERE literature_sales_products.sale_id=$orderno";
		$res2=mysql_query($sql) or die(mysql_error());
		while ($h=mysql_fetch_array($res2)){
			print "<tr><td>".$h['description']."</td><td>".$h['quantity']."</td></tr>";
		}
	}
	print "</table><p></p>";

	if (!$order_on_order){
		print "<div align=\"left\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"administrator.php?action=set_literature_order_as_ordered&orderno=$orderno\">Set as processing</a></span></div>";
	}

	if (!$order_completed){
		print "<div align=\"left\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"administrator.php?action=set_literature_order_as_complete&orderno=$orderno\">Set order as despatched</a></span></div>";
	}
		print "<div align=\"left\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"javascript:loadPage('administrator.php?action=list_table&t=literature_sales&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1&filter_id=122&jx=1')\">Return to orders</a></span></div>";
}

function set_literature_order_as_ordered($orderno){
	$sql="UPDATE literature_sales set complete=2 WHERE id=$orderno";
	$res=mysql_query($sql) or die ("Error completing order: Cann ot run $sql:" . mysql_error());	
	print "<p><b>Literature order $orderno has been set to 'On Order'.</b></p>";
	print "<div align=\"left\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"javascript:loadPage('administrator.php?action=list_table&t=literature_sales&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1&filter_id=122&jx=1')\">Return To Orders</a></span></div>";
	mail_updated_order_status("literature_sales",$orderno);
}

function set_order_as_despatched($orderno){
	$sql="UPDATE orders set complete=1 WHERE id=$orderno";
	$res=mysql_query($sql) or die ("Error completing order");	
	print "<p><b>Order $orderno has been set to 'Despatched'.</b></p>";
	print "<div align=\"left\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"Javascript:loadPage('administrator.php?action=list_table&t=orders&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1&filter_id=121&jx=1')\">Return To Orders</a></span></div>";
	mail_updated_order_status("orders",$orderno);
}

function set_order_as_ordered($orderno){
	$sql="UPDATE orders set complete=2 WHERE id=$orderno";
	$res=mysql_query($sql) or die ("Error completing order");	
	print "<p><b>Order $orderno has been set to 'on order'.</b></p>";
	print "<div align=\"right\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"Javascript:loadPage('administrator.php?action=list_table&t=orders&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1&filter_id=121&jx=1')\">Return To Orders</a></span></div>";
	mail_updated_order_status("orders",$orderno);
}

function set_order_as_cancelled($orderno){
	$sql="UPDATE orders set complete=4 WHERE id=$orderno";
	$res=mysql_query($sql) or die ("Error completing order");	
	print "<p><b>Order $orderno has been set to 'cancelled'.</b></p>";
	print "<div align=\"right\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"Javascript:loadPage('administrator.php?action=list_table&t=orders&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1&filter_id=121&jx=1')\">Return To Orders</a></span></div>";
	mail_updated_order_status("orders",$orderno);
}

function generate_password_old_jc_function($length=9, $strength=5) {
	$vowels = 'aeuy';
	$consonants = 'bdghjmnpqrstvz';
	if ($strength & 1) {
		$consonants .= 'BDGHJLMNPQRSTVWXZ';
	}
	if ($strength & 2) {
		$vowels .= "AEUY";
	}
	if ($strength & 4) {
		$consonants .= '23456789';
	}
	if ($strength & 8) {
		$consonants .= '@#$%';
	}
	$password = '';
	$alt = time() % 2;
	for ($i = 0; $i < $length; $i++) {
		if ($alt == 1) {
			$password .= $consonants[(rand() % strlen($consonants))];
			$alt = 0;
		} else {
			$password .= $vowels[(rand() % strlen($vowels))];
			$alt = 1;
		}
	}
	return $password;
}

// function will mail the user (who placed the order) with the new status (processing, despatched etc)
// will only work currently on users in the user table and not on the fly users..
function mail_updated_order_status($table,$orderno){
	return;
	// get mail template
	$sql="SELECT * FROM templates where id = 46";
	$res=mysql_query($sql);
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$mail_template=$h['template'];
	}

	if ($table == "literature_sales"){$order_type=="literature";}	
	if ($table == "orders"){$order_type=="stationery";}	

	$sql="SELECT $table.*, user.first_name,user.second_name,user.email_address FROM $table INNER JOIN user on $table.ordered_by=user.id WHERE $table.id = $orderno";
	$res=mysql_query($sql) or die(mysql_error());
	
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		if ($h['complete']=="1"){$order_status="Despatched";}
		if ($h['complete']=="2"){$order_status="On Order";}
		// first_name,second_name,order_type,order_status		
		$mail_template = str_replace("{=first_name}",$h['first_name'],$mail_template);
		$mail_template = str_replace("{=second_name}",$h['second_name'],$mail_template);
		$mail_template = str_replace("{=order_type}",$order_type,$mail_template);
		$mail_template = str_replace("{=order_status}",$order_status,$mail_template);

		$to=$h['email_address'];
		$subject="Your order no. " . $h['id'] . " from the John Crane Marketing Portal"; 
		$from="admin@johncranemarketingportal.com";
		$headers="Content-type:text/html\r\nFrom:$from\r\n\r\n";
		
		mail ($to,$subject,$mail_template,$headers) or die ("Cant send mail");	

	}

}

function sagepay_manual_pay_authorisation($order_id){

        $continue_manual=$_GET['continue_manual_pay_authorisation'];
        print "<p class=\"admin_header\">Pay Authorisation</p>";
        global $db;
        $sql="SELECT ordered_by,grand_total FROM orders where id = $order_id";
        print $sql;
        $rv=$db->query($sql);
        $h=$db->fetch_array($rv);
        $grand_total=$h['grand_total'];
        $user_id=$h['ordered_by'];
        print "<p>About to authorise order #$order_id for &pound;$grand_total</p>";
        if (!$continue_manual){
                print "<p><a href=\"mui-administrator.php?action=sagepay_manual_pay_authorisation&order_id=$order_id&continue_manual_pay_authorisation=1\">Continue</a></p>";
        } else {
                print "Posting the following details: <br />";
                $sql="SELECT * from sagepay_responses WHERE order_id = $order_id AND status like \"%AUTHORISED%\"";
                $rv=$db->query($sql);
                $row=$db->fetch_array($rv);

                //$row['VendorTxCode']="535e376081d7c";
                //$row['SecurityKey']="IUIIE5RQSI";
                //$row['VPSTxId']="{EDEB8934-2D90-20A8-01B3-5A16371C3047}";
                //VPSProtocol=2.23&TxType=AUTHORISE&Vendor=gonzomultimedia&VendorTxCode=53d77bda8df3d&Amount=999.99&Currency=GBP&Description=Preorder authorise for order no 202&RelatedVPSTxId={EDEB8934-2D90-20A8-01B3-5A16371C3047}&RelatedSecurityKey=IUIIE5RQSI&RelatedVendorTxCode=535e376081d7c

                $details['relatedVPSTxId']=$row['VPSTxId'];
                $details['relatedSecurityKey']=$row['SecurityKey'];
                $details['relatedVendorTxCode']=$row['VendorTxCode'];
                global $libpath;
                require_once("$libpath/classes/shopping_cart.php");
                $mycart=new shopping_cart();
                require_once("$libpath/classes/sagepay_direct.php");
                $attempt_auth=new sagepay_direct();
                $details['order_type']="authorise_preorder";
                $details['amount']=$grand_total;
                $details['user_id']=$user_id;
                $details['order_number']=$order_id;
                foreach ($details as $detailname=>$detail){
                        print $detailname . " - " . $detail  . "<br>";
                }
                $payment_result=$attempt_auth->authorise_preorder($details);
                print "<p><b>Got return value from sagepay auth process of " . $payment_result['value'] . ": </b></p>";
                print "<p>Status: " . $payment_result['status'] . "<br />Details: " . $payment_result['statusdetail'] . "</p>";
                if ($payment_result['value']==0){
                        print "<p style=\"color:red\">We have not been able to take monies for this transaction.</p>";
                } else if ($payment_result['value']==1){
                        print "<p style=\"color:orange\">This order needs to be posted to flight logistics separately.</p>";
                }
                print "<p>";
                var_dump($payment_result);
                print "</p>";
        }

}
