<?php

$action=$_GET['action'];

if ($action=="admin_view_order"){admin_view_order($_GET['orderno']);}
if ($action=="set_order_as_complete"){set_order_as_despatched($_GET['orderno']);}
if ($action=="set_order_as_cancelled"){set_order_as_cancelled($_GET['orderno']);}
if ($action=="set_order_as_ordered"){set_order_as_ordered($_GET['orderno']);}
if ($action=="sagepay_manual_pay_authorisation"){ sagepay_manual_pay_authorisation($_REQUEST['order_id']);}

function admin_view_order($orderno){
	global $db;
	global $page;
	$export_vars['order_number']=$orderno;
	$sql="SELECT orders.*, if(orders.complete=0 or orders.complete IS NULL,'Received, Unpaid',if(orders.complete=1,'Ordered By Customer',if(orders.complete=2,'Processing',if(orders.complete=3,'Despatched',if(orders.complete=5,'On Pre Order','Incompleted By Customer (Unpaid)'))))) as order_status,user.email_address, user.id AS userid,user.*, countries.Name as countryname, flight_logistics_order_post_responses.flight_return_string as fl_response, flight_logistics_order_post_responses.post_time as flight_post_date FROM orders LEFT JOIN user on orders.ordered_by=user.id LEFT JOIN countries ON user.country = countries.ID LEFT JOIN flight_logistics_order_post_responses ON orders.id = flight_logistics_order_post_responses.order_id WHERE orders.id = $orderno";
	$res=$db->query($sql) or die($db->db_error());
	while ($h=$db->fetch_array($res)){
		if ($h['complete']==1){$order_completed=1;}
		if ($h['complete']==2){$order_on_order=1;}
		//print "<p><b>PO Number:</b> " . $h['po_number'] . "</p>";
		$export_vars['ordered_by']=$h['first_name'] . " " . $h['second_name'] . "(".$h['email_address'] . ")";
		//print " <a href=\"View User Details\">View User Details</a>";
		$export_vars['address_1']=$h['address_1'];
		$export_vars['address_2']=$h['address_2'];
		$export_vars['address_3']=$h['address_3'];
		$export_vars['county_or_state']=$h['county_or_state'];
		$export_vars['us_billing_state']=$h['us_billing_state'];
		$export_vars['zip_or_postal_code']=$h['zip_or_postal_code'];
		$export_vars['country']=$h['countryname'];
		if ($h['same_as_billing_address']){
			$export_vars['delivery_address_1']="Same as billing address";
		} else {
			$export_vars['delivery_address_1']=$h['delivery_address_1'];
			$export_vars['delivery_address_2']=$h['delivery_address_2']; 
			$export_vars['delivery_address_3']=$h['delivery_address_3'];
			$export_vars['delivery_county_or_state']=$h['delivery_county_or_state'];
			$export_vars['us_delivery_state']=$h['us_delivery_state'];
			$export_vars['delivery_zip_or_postal_code']=$h['delivery_zip_or_postal_code'];
			if ($h['delivery_country']){
				$dc_sql="SELECT Name from countries WHERE id = " . $h['delivery_country'];
				$dc_res=$db->query($dc_sql);
				$dc_h=$db->fetch_array($dc_res);
			}
			$export_vars['delivery_country']=$dc_h['Name'];
		}
		$export_vars['order_date']=$h['datetime'];
		if ($h['pre_order']){
			$export_vars['pre_order_data']="<p class=\"dbf_para_info\" style=\"color:#cc0000\"><span style=\"background-color:#f1f1f1\">This is a pre-order</span>";
			if ($h['preorder_date_shipped']){
				$export_vars['pre_order_data'] .= " - it was shipped on " . $h['preorder_date_shipped'];
			} else {
				$export_vars['pre_order_data'] .= " - it has not yet been shipped.";
			}
		}
		$export_vars['pre_order_data'].= "</p>";
		$export_vars['order_details']= "<table><tr style=\"font-weight:bold; background-color:#f1f1f1\"><td>Product</td><td>Quantity</td><td>Price</td><td>Line Price</td></tr>";
		//$sql="SELECT order_products.id AS att_id,order_products.*,products.title FROM orders INNER JOIN order_products on orders.id=order_products.order_id INNER JOIN products ON order_products.product_id = products.id WHERE orders.id=$orderno";
		$sql="SELECT order_products.id AS att_id,order_products.*,products.title,artists.artist, if (products.release_date > NOW(),DATE_FORMAT(products.release_date,\"%D %M %Y\"),\"\") AS release_info FROM orders INNER JOIN order_products on orders.id=order_products.order_id INNER JOIN products ON order_products.product_id = products.id INNER JOIN artists ON products.artist=artists.id WHERE orders.id=$orderno";
		$res2=mysql_query($sql) or die($db->db_error());
		while ($h2=mysql_fetch_array($res2)){
			//print "<tr><td>".$h2['title']."</td><td>".$h2['quantity']."</td><td>&pound;".$h2['price']."</td><td>&pound;".$h2['quantity']*$h2['price']."</td></tr>";	
			$export_vars['order_details'] .= "<tr><td valign=\"top\">".$h2['artist']." - " . $h2['title'];
			if ($h2['release_info']){
				$export_vars['order_details'] .= "<br /><span style=\"color:#1b2c69; font-size:10px\">Due: ".$h2['release_info']."</span>";
			}
			$export_vars['order_details'] .= "</td><td valign=\"top\">".$h2['quantity']."</td><td valign=\"top\">&pound;".$h2['price']."</td><td valign=\"top\">&pound;".$h2['quantity']*$h2['price']."</td></tr>";	
			$att_sql="SELECT * FROM order_product_attributes where order_product_id=".$h2['att_id'];
			$att_res=mysql_query($att_sql);
			if ($db->num_rows($att_res)>=1){
				$export_vars['order_details'] .= "<div style=\"border-width:0px; border-style:solid; border-color:#444444; color:#444444; margin-bottom:10px;\"><b>Additional Details:</b><br />";
				while ($j=mysql_fetch_array($att_res)){
					$export_vars['order_details'] .= $j['attribute_name'] . " - " . $j['attribute_value'] . "<br />";	
				}
				$export_vars['order_details'] .= "</div>";
			}
		}
		$export_vars['order_details'] .= "</table><p></p>";
		$export_vars['order_details'] .= "<p><b>Order Total: &pound;</b> " . $h['total_amount'] . "<br />"; 
		$export_vars['order_details'] .= "<b>Shipping Total: &pound;</b> " . $h['shipping_total'] . "<br />"; 
		$export_vars['order_details'] .= "<b>Grand Total: &pound;</b> " . $h['grand_total'] . "</p>"; 
		$export_vars['order_status'] = $h['order_status'];
		if ($h['posted_to_flight']==1){ $flight="Yes"; } else { $flight = "No"; }
		$export_vars['posted_to_flight_logistics'] = $flight;
		$export_vars['flight_logistics_response_string'] = $h['fl_response'];
		if ($h['flight_post_date']){
			$export_vars['flight_logistics_response_string'] .= "<br /><b>Date posted:</b> " . $h['flight_post_date'] . "\n";
		}
		if (!$h['fl_response'] && $flight=="No"){ $export_vars['flight_logistics_response_string']=" None (Not posted)";}
		$export_vars['origin']=$h['origin'];
		$export_vars['payment_method']=$h['payment_method'];
		if ($h['paypal_preorder_reminder_sent']){
			$export_vars['paypal_preorder_reminder_sent']= "<br />The customer was sent a message requesting payment by paypal on " . $h['paypal_preorder_reminder_sent'];
		}
		if ($h['payment_method']=="sagepay_direct"){
			// AUTHORISED is for immediate payments, OK is when a REGISTERED PAYMENT goes through OK
			$pm_sql="SELECT * from sagepay_responses WHERE order_id = $orderno AND (Status LIKE \"AUTHORISED%\" OR Status LIKE \"OK %\")";
			$pm_res=$db->query($pm_sql) or die ("SQL ERROR");
			$positive_transaction_count=$db->num_rows($pm_res);
			if (!$positive_transaction_count){
				$export_vars['payment_result'] = "<span style=\"color:#cc0000\">This transaction was not paid successfully.</span>";
			} else {	 
				$export_vars['payment_result'] = "<span style=\"color:green; font-weight:bold\">This order was paid for successfully.</span>";
			}
			if (!$positive_transaction_count){
				$check_preorder_sql="SELECT * from sagepay_responses WHERE order_id = $orderno AND (Status LIKE \"REGISTERED%\")";
				$pre_res=$db->query($check_preorder_sql);
				$positive_auth_count=$db->num_rows($pre_res);
				if ($positive_auth_count){
					$export_vars['payment_result'] = "<span style=\"color:#0000cc\">This transaction has been authorised for preorder but not paid.</span>";
					$export_vars['auth_payment_link'] = "<p style=\"background-image:url(/system/graphics/icons/coins_add.png); background-position:left; background-repeat:no-repeat; padding-left:20px;\"><span id=\"auth_payment_link\"><a href=\"Javascript:parent.loadPage('mui-administrator.php?action=sagepay_manual_pay_authorisation&amp;order_id=$orderno','Sagepay - Manual Transaction',1,'','','Opening sagepay Window')\" class=\"mb\">Collect Monies Now</a></span></p>";
				}
			}
			while ($pm_h=$db->fetch_array($pm_res)){
				foreach ($pm_h as $pm_key=>$pm_val){
					if ($pm_key != "full_post_string"){
						$export_vars['full_payment_results'] .= "<b>$pm_key:</b> $pm_val<br>";
					}
				}
			}

			$pm_sql="SELECT * from sagepay_responses WHERE order_id = $orderno AND (Status NOT LIKE \"AUTHORISED%\" AND Status NOT LIKE \"OK %\")";
			$pm_res=$db->query($pm_sql) or die ("SQL ERROR");
			$neg_trans_count=$db->num_rows($pm_res);
			if ($neg_trans_count){
				$export_vars['additional_payment_attempt_results'] = "<br />Additional Sagepay Response Details:<br /><p style=\"color:orange\">There are extra authorisations or failed payment attempts on this order. All details related to this order are below:</p>";
			}
			while ($pm_h=$db->fetch_array($pm_res)){
				foreach ($pm_h as $pm_key=>$pm_val){
					if ($pm_key != "full_post_string"){
						$export_vars['additional_payment_attempt_results'] .= "<b>$pm_key:</b> $pm_val<br>";
					}
				}
				$export_vars['additional_payment_attempt_results'] .= "<hr size=\"1\" />";
			}

		} else if ($h['payment_method']=="paypal_express_checkout"){
			$pp_sql="SELECT * from paypal_transaction_details WHERE internal_sale_id = $orderno AND ack = \"Success\"";
			$pp_res=$db->query($pp_sql) or die ("SQL ERROR");
			$positive_transaction_count=$db->num_rows($pp_res);
			if (!$positive_transaction_count){ 
				$export_vars['payment_result'] = "<span style=\"color:#cc0000\">This order has not been paid for.</span>";
			} else { 
				$export_vars['payment_result'] = "<span style=\"color:green; font-weight:bold\">This order was paid successfully.</span>";
			}
			while ($pp_h=$db->fetch_array($pm_res)){
				foreach ($pp_h as $pp_key=>$pp_val){
					$export_vars['full_payment_results'] .= "<b>$pp_key:</b> ".urldecode($pp_val)."<br>";
				}
			}
		} else {
			$export_vars['payment_result'] = "There is no payment method associated with this transaction.";
		}
	}
	
	if (!$order_on_order){
		$export_vars['button_actions'] .= "<div align=\"left\"><span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"administrator.php?action=set_order_as_ordered&orderno=$orderno\">Set as processing</a></span></div>";
	}

	if (!$order_completed){
		$export_vars['button_actions'] .= "<span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"administrator.php?action=set_order_as_complete&orderno=$orderno\">Set order as despatched</a></span>";
		$export_vars['button_actions'] .= "<span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"Javascript:parent.loadPage('administrator.php?action=set_order_as_cancelled&orderno=$orderno')\">Set order as cancelled</a></span>";
	}
		if (!$page->value("useAjax")){
			$export_vars['button_actions'] .= "<span class=\"jc_button_160\" style=\"font-size:12px\"><a href=\"Javascript:loadPage('administrator.php?action=list_table&t=orders&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1&filter_id=121&jx=1')\">Return to orders</a></span>";
		}
	$order_details_template=$db->db_quick_match("template_registry","template","interface","Admin View Order");
	print hash_into_admin_template_by_key($export_vars,"cart_admin_view_order");
	//print hash_into_template($export_vars,$order_details_template);
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
	$res=mysql_query($sql) or die($db->db_error());
	
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

?>
