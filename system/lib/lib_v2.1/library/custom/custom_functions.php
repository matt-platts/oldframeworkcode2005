<?php

function promoted_countries_list() { // must be amended to set $x to be selected 
	global $db;
	$sql="SELECT ID,Name,promoted_order FROM countries WHERE active=1 ORDER BY promoted_order,name";
	$rv=$db->query($sql);
	$promoted_list=array();
	$regular_list=array();
	array_push($promoted_list,"0;;-----------------------");
	while ($h=$db->fetch_array($rv)){
		$country_option_string=$h['ID'] . ";;" . $h['Name'];
		if ($h['promoted_order'] && $h['promoted_order'] <1000){
			array_push($promoted_list,$country_option_string);
		} else {
			array_push($regular_list,$country_option_string);
		}
		
	}
	array_push($promoted_list,"0;;-----------------------");
	$countries_in_order=array_merge($promoted_list,$regular_list);
	$countries_in_order=join(",",$countries_in_order);
	return $countries_in_order;
}

function trade_sales_bulk_add(){
	global $db;
        $q=$db->query("SELECT LAST_INSERT_ID() as sale_id");
        $res=$db->fetch_array($q);
        $sale_id=$res['sale_id'];
        foreach ($_POST as $key=>$val){
                if (preg_match("/select_/",$key)){
                        $item_ids=explode("_",$key,2);
                        $item_id=$item_ids[1];
                        $quantity_var="quantity_" . $item_id;
                        $price_var="price_" . $item_id;
                        $quantity=$_POST[$quantity_var];
                        $price=$_POST[$price_var];
			if ($quantity && $val){
				$insert_sql="INSERT INTO order_products (order_id,product_id,quantity) values(\"$sale_id\",$val,$quantity)";
				$insert_result=$db->query($insert_sql) or die($db->db_error());
			}
                }
        }

	//$mail_template=field_from_record_from_id("templates",45,"template");
	$order_details=print_literature_order_details();
	print "<script language=\"Javascript\" type=\"text/javascript\">\n";
	print "location=\"view_cart.html\"\n";
	print "</script>";
	exit;
	//print $order_details;
	$mail_cart_to="mattplatts@gmail.com";
	$mail_cart_from="Web site orders";
	$mail_cart_from_email="order@mattplatts.com";
	$mail_from="\"$mail_cart_from\" <$mail_cart_from_email>";
	$subject="New Order on the Bulk Order System";
	$headers="From: $mail_from\n";
	$headers .= "Content-type:text/html\n\r\n\r";
	$mail_template=" here is an order<br />{=order_details}";
	$mail_template=str_replace("{=order_details}",$order_details,$mail_template);
	mail ($mail_cart_to,$subject,$mail_template,$headers) or die("cant send mail");
	return $order_details; 
}

function print_literature_order_details(){

	global $db;
	$return ="";
	$office_name=$db->field_from_record_from_id("offices",$_POST['new_delivery_address'],"office_name");
	// temp hack for **** 
	if ($_POST['new_delivery_address']=="1"){ $office_name="Billing Address"; }
	if ($_POST['new_delivery_address']=="2"){ $office_name="Delivery Address"; }
	global $user;
	$return .= "<p><b>Ordered By: </b>" . $user->value("full_name") . "</p>";
	//$return .= "<b>Deliver to:</b> (" . $office_name . ")<br />";
	$return .= "<b>Deliver to:</b> " . $office_name . "<br />";
	/*
	$office_address_sql="SELECT * from offices WHERE id=".$_POST['new_delivery_address'];
	$office_res=$db->query($office_address_sql) or die(->db_error());
	while ($k=$db->fetch_array($office_res)){
		if ($k['business_name']){ $office_data = $k['business_name'] . "<br />";} else { $office_data .= "John Crane<br />";}	
		if ($k['building_name']){ $office_data .= $k['building_name'] . "<br />";}
		$office_data .= $k['addr1'] . "<br />";	
		$office_data .= $k['town'] . "<br />";	
		$office_data .= $k['postcode'] . "<br />";	
		$office_data .= $k['country'] . "<br />";	
	}
	*/
	$return .= $office_data . "</p>";
	$return .= "<table><tr bgcolor=\"#cccccc\" style=\"font-weight:bold\"><td>Item</td><td>Quantity</td><td>Price</td><td>Line Price</td></tr>";

	$running_total=0;
        foreach ($_POST as $key=>$val){
                if (preg_match("/select_/",$key)){
                        $item_ids=explode("_",$key,2);
                        $item_id=$item_ids[1];
                        $quantity_var="quantity_" . $item_id;
                        $price_var="price_" . $item_id;
                        $quantity=$_POST[$quantity_var];
                        $price=$_POST[$price_var];
			if ($quantity && $val){
			$item = $db->field_from_record_from_id("products",$val,"ID");
			$item_id=$item;
			$item .= "-" . $db->field_from_record_from_id("products",$val,"name");
			$item_price = sprintf("%4.2f",$db->field_from_record_from_id("products",$val,"wholesale_price"));
			$line_price=sprintf("%4.2f",$quantity*$item_price);
			$running_total += $line_price;
			$return .= "<tr bgcolor=\"#f4f4f4\"><td>$item </td><td> $quantity</td><td>&pound; $item_price</td><td>&pound;$line_price</td></tr>";
			global $cart;
			if (!$cart){
				require_once(LIBPATH . "/classes/shopping_cart.php");
				$cart=new shopping_cart;
			}
			$cart->add_to_cart($item_id,$quantity);
			}
                }
        }
	$running_total=sprintf("%4.2f",$running_total);
	$return .= "<tr><td colspan=\"3\" style=\"text-align:right; font-weight:bold\">Total: </td><td>&pound;$running_total</td></tr>";
	$return .= "</table><br />";
	$return = ""; // Clear RETURN FOR NOW
	//$return .= $cart->view_cart_general();
	return $return;
}

function log_user_in(){
	$mail_address=$_POST['new_email_address'];
	$password=$_POST['new_password'];
	
	// send matt platts an email
	$to="mattplatts@gmail.com";
	$subject="New Registration Information";
	$message="A new sign up has been registered on " . $_SERVER['SERVER_NAME'] . " from $mail_address";
	$mail_from="mattplatts@gmail.com";
	$headers="From: $mail_from\n";
	$headers .= "Content-type:text/html\n\r\n\r";
	mail ($to,$subject,$message,$headers);
}

function set_product_id($last_insert_id){
	global $db;
	if ($last_insert_id){
		$sql="UPDATE products SET product_id = $last_insert_id WHERE id = $last_insert_id";
		$res=$db->query($sql);
	}
}

function generate_category_options($func_selected_id){
	print "<style type=\"text/css\">
	.optionitem_level0 {padding-left:0px; color:#000;}
	.optionitem_level1 {padding-left:10px; margin-left:10px; color:#444; text-indent:10px;}
	.optionitem_level2 {padding-left:20px; margin-left:20px; color:#666;}
	</style>\n";

		global $db;
		$sql="SELECT * from product_categories order by parent, category_name";
		$all_rows=array();
		$result=$db->query($sql) or die("Error " . $db->db_error());
		$count_after=0;

		while ( $row=$db->fetch_array($result)){
			array_push($all_rows,$row);
		}
		$level=0;
		$options_printed=array();
		$already_printed=array();
		return options_loop($all_rows,$level,0,$func_selected_id); // Or you can get a sub tree: eg. 0,2,5
}

function options_loop($all_rows,$level,$parent,$func_selected_id){
        global $debug;
        global $options_printed;
        global $dbf_geneal_options_loop_optigons_html;
        global $returned_level;
        foreach ($all_rows as $eachrow => $eachrow_array){
                if ($eachrow_array['parent'] != $parent){ continue; }
                if (!$options_printed['parent_'.$parent]['level_'.$level]){
                        $options_printed['parent_'.$parent]['level_'.$level]=1;
                }
                $dbf_geneal_options_loop_optigons_html .= "\n<option value=\"".$eachrow_array['id'] . "\" class=\"optionitem_level$level\"";
		if ($eachrow_array['id']==$func_selected_id){ $dbf_geneal_options_loop_optigons_html .= " selected"; }
		$dbf_geneal_options_loop_optigons_html .= ">";
                if ($level==1){ $dbf_geneal_options_loop_optigons_html .= "- ";}
                if ($level==2){ $dbf_geneal_options_loop_optigons_html .= "- - ";}
                if ($level==3){ $dbf_geneal_options_loop_optigons_html .= "- - - ";}
                if ($level==4){ $dbf_geneal_options_loop_optigons_html .= "- - - - ";}
                if ($level==5){ $dbf_geneal_options_loop_optigons_html .= "- - - - - ";}
                $dbf_geneal_options_loop_optigons_html .= $eachrow_array['category_name']."";
                $level++;
                options_loop($all_rows,$level,$eachrow_array['id'],$func_selected_id);
                $dbf_geneal_options_loop_optigons_html .= "</option>";
                unset($all_rows[$eachrow_array]);
                $returned_level=$level;
                $level--;
        }
        if ($returned_level > $level){
        }
        return $dbf_geneal_options_loop_optigons_html;
}

function admin_add_products_to_order(){
	print "You can now continue to add products to your order:";
}

function admin_complete_place_order_return($orderid){
	// add the field grand_total from order_id to payments with a description
	$user_table="user";
	global $db;
	$paid=$db->db_quick_match("orders","paid_in_full","id",$orderid);
	$osql="SELECT ordered_by FROM orders where id =$orderid";
	$orv=$db->query($osql);
	$oh=$db->fetch_array($orv);
	$ordered_by=$oh['ordered_by'];
	if (!$ordered_by){
		// look up user from order_user_data
		$orderedby=$db->db_quick_match("orders","non_account_order","id",$orderid);
		$user_table="order_user_data";
	}
	if ($paid){
		$grand_total=$db->db_quick_match("orders","grand_total","id",$orderid);
		$orderedby=$db->db_quick_match("orders","ordered_by","id",$orderid);
		$product_return=$db->db_quick_match("orders","product_return","id",$orderid);
		if ($product_return){ $txt="Return"; } else { $txt="Order";}	
		$payment_sql="INSERT INTO payments (user,description,payment_amount,payment_date) VALUES ($orderedby,\"Payment for $txt No. #$orderid\",$grand_total,NOW())";
		$payment_result=$db->query($payment_sql);
		print "As this order / return was marked as paid, an entry has been posted to payments automatically. The order id is $orderid";
	}
	// set the order country
	$order_country=$db->db_quick_match($user_table,"delivery_country","id",$orderedby);
	$use_billing=$db->db_quick_match($user_table,"same_as_billing_address","id",$orderedby);
	if (!$order_country || $use_billing){
		$order_country=$db->db_quick_match($user_table,"country","id",$orderedby);
	}
	if ($order_country){
		$country_sql="UPDATE orders SET order_country=$order_country WHERE id = $orderid";
		$country_result=$db->query($country_sql);
	} else{
		print "No order country assigned.";
	}
}

function log_individual_sagepay_payment(){
        require_once(LIBPATH . "/classes/sagepay_direct.php");
        $attempt_payment=new sagepay_direct();
        $payment_result=$attempt_payment->make_individual_payment();
	print $payment_result;
}

function register_confirm(){
        $mail_address=$db->db_escape($_POST['new_email_address']);
	$first_name=$db->db_escape($_POST['new_first_name']);
	$last_name=$db->db_escape($_POST['new_second_name']);
        $password=$_POST['new_password'];

        // send matt platts an email
        $to="mattplatts@gmail.com";
        $subject="New Registration Information";
        $message="A new sign up has been registered on " . $_SERVER['SERVER_NAME'] . " from $mail_address";
	if ($_POST['new_trade_account_requested']){
		$message .= "<br />This is a trade account request";
	} else {
		$message .= "This is a regular email account";
	}
        $mail_from="website@mattplatts.com";
        $headers="From: $mail_from\n";
        $headers .= "Content-type:text/html\n\r\n\r";
        mail ($to,$subject,$message,$headers);

        // also mail if it is a new trade customer?
	if ($_POST['new_trade_account_requested']){
		$to2="mattplatts@gmail.com";
		$message2="<p>A new user has requested a trade account on the *** web site.</p>";
		$message2 .= "<p>You can review the new request in the web site admin. The user's details are:</p>";
		$message2 .= "Name: " . $first_name . " " . $last_name . "<br />";
		$message2 .= "Email: " . $mail_address;
		$subject2="New Trade Account Request - Mattplatts.com";
		mail($to2,$subject2,$message2,$headers);
	}
}

function lsc_register_confirm(){
        $mail_address=$db->db_escape($_POST['new_email_address']);
	$first_name=$db->db_escape($_POST['new_first_name']);
	$last_name=$db->db_escape($_POST['new_second_name']);
        $password=$_POST['new_password'];

        // send matt platts an email
        $to="mattplatts@gmail.com";
        $subject="New Registration Information";
        $message="A new sign up has been registered on " . $_SERVER['SERVER_NAME'] . " from $mail_address";
	if ($_POST['new_trade_account_requested']){
		$message .= "<br />This is a trade account request";
	} else {
		$message .= "This is a regular account";
	}
        $mail_from="website@mattplatts.com";
        $headers="From: $mail_from\n";
        $headers .= "Content-type:text/html\n\r\n\r";
        mail ($to,$subject,$message,$headers);

	$hash['first_name']=$first_name;
	$hash['second_name']=$last_name;
	$dbf_key_name="register_success";
	global $db;
        $template=$db->db_quick_match("templates","template","dbf_key_name",$dbf_key_name);
	$customer_email_content = record_to_template ($template,$hash);
	$email_footer=$db->db_quick_match("widgets","widget","dbf_key_name","general_email_footer");
	$customer_email_content .= $email_footer;
	$subject="Mattplatts.com - Confirmation of signing up";
	//print "SENDING MAIL TO $mail_address";
	mail ($mail_address,$subject,$customer_email_content,$headers);
	exit;
	//customer reg mail

        // also mail if it is a new trade customer?
	/*
	if ($_POST['new_trade_account_requested']){
		$to2="mattplatts@gmail.com";
		$message2="<p>A new user has requested a trade account on the beauty web site.</p>";
		$message2 .= "<p>You can review the new request in the web site admin. The user's details are:</p>";
		$message2 .= "Name: " . $first_name . " " . $last_name . "<br />";
		$message2 .= "Email: " . $mail_address;
		$subject2="New Trade Account Request - Mattplatts.com";
		mail($to2,$subject2,$message2,$headers);
	}
	*/
	
	// also need to log the user in!
	global $user;
	$login_result=$user->process_login($_POST['new_email_address'],$_POST['new_password'],$_POST['direct_to']);
}


?>
