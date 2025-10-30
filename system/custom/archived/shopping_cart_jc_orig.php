<?php

// main shopping cart logic //
if (!$_SESSION){session_start();}

if ($_REQUEST['action']=="update_cart"){ $title="Shopping cart"; update_cart(); $content .= order_header() . view_cart() . order_and_browse_buttons();}

if ($_REQUEST['action']=="cart_categories_browse"){ 
	$title = get_category_breadcrumb($_GET['category_id']);
	$browser_title = browser_title_from_id($_GET['content']); 
	if (!$browser_title){$browser_title="Stationery Ordering";}
	$content = content_from_id($_GET['content']); 
}

if ($_REQUEST['action']=="cart_add"){
	$content .= add_to_cart($_REQUEST['product_id'],$_REQUEST['quantity']);
	$title="Shopping cart";
	print "loading";
	$content .= order_header() . view_cart() . order_and_browse_buttons();
	print "have content";
}

if ($_REQUEST['action']=="cart_view"){
	$title="View cart";
	$content .= order_header() . view_cart() . order_and_browse_buttons();
}

if ($_REQUEST['action']=="cart_remove"){
	$content .= remove_from_cart($_REQUEST['product_id']); 
	$content = order_header() . view_cart() . order_and_browse_buttons();
}

if ($_REQUEST['action']=="confirm_order"){
	$content .= confirm_order();
	$title="Shopping cart";
}

if ($_REQUEST['action']=="place_order"){
	$order_confirmation=view_cart_static();
	$place_order_result = place_order();
	if ($place_order_result==1){
		$title="Order placed";
		$content = "<p>Thank you - your stationery order has been placed successfully.</p>";
		$content .= "<p class=\"subheading\"><b>Your order:</b>\n</p>";
		$content .= $order_confirmation;
		$content .= "<p>Thank You for your order.</p>";
	} else {
		$title="Order error";
		$content="<p>An unknown error has occurred.</p>";
	}
}

if ($_REQUEST['action']=="cats"){
	$title = get_category_breadcrumb(18);
	print $title; 
	exit;
}

// functions //
function confirm_order(){
	global $user;
	$content = "<p class=\"subheading\">You are about to place the following order:</p>\n";
	$content .= view_cart_with_prices();
	$content .= "<form name=\"place_order_form\" method=\"post\" action=\"site.php?s=1&amp;action=place_order\">\n";
	$content .= "<table>";
	$content .= "<tr><td align=\"right\">Delivery Address: </td><td> <select name=\"delivery_address\">";

	$user_id = $user->value('id');
	$sql = "SELECT * FROM user_office_lookup,offices WHERE (user_office_lookup.user_id='$user_id') AND (offices.id=user_office_lookup.office_id) ORDER BY offices.office_name";
	$res=mysql_query($sql) or die (mysql_error());
	while ($h=mysql_fetch_array($res)){
		$option = "<option value=" . $h['office_id']. ">".$h['office_name']."</option>\n";
		$options .= $option;
	}
	$content .= $options;
	
	$content .= "</select></td></tr>";
	$content .= "<tr><td align=\"right\">Please enter a PO number: </td><td> <input type=\"text\" name=\"po_number\" value=\"\"></td></tr>";
	$content .= "<tr><td align=\"right\" valign=\"top\">Please enter any additional comments: </td><td><textarea rows=\"5\" cols=\"30\" name=\"comments\"></textarea></td></tr>";
	$content .= "</table></form>";
	$content .= "<script language=\"Javascript\" type=\"text/javascript\">\n";
	$content .= "function check_po(){\n";
	$content .= "if (!document.forms['place_order_form'].elements['po_number'].value){\nalert('Please enter a PO number');\n}else {\ndocument.forms['place_order_form'].submit();\n}}\n";
	$content .= "</script>\n";
	$content .= "<p><span class=\"order_button\" style=\"float:left;\"><a href=\"Javascript:check_po()\">Place order</a> &nbsp; </span></p>";
	
	return $content;
}

function place_order(){

	global $user;
	if (!$_POST['po_number']){ return 0;}
	$po_number=$_POST['po_number'];
	$delivery_office=$_POST['delivery_address'];
	if (!$delivery_office){$delivery_office=0;}
	$comments=$_POST['comments'];
	$start_transaction_sql=mysql_query("BEGIN");
	
	$mail_cart_to=field_from_record_from_id("setup_variables",2,"value");
	$mail_cart_from=field_from_record_from_id("setup_variables",4,"value");
	$mail_cart_from_email=field_from_record_from_id("setup_variables",5,"value");
	$mail_from="\"$mail_cart_from\" <$mail_cart_from_email>";
	$subject="New Stationery Order on the John Crane Marketing Literature Portal";
	$headers="From: $mail_from\n";
	$headers .= "Content-type:text/html\n\r\n\r";
	$mail_template=field_from_record_from_id("templates",44,"template");
	$order_details=view_cart_static();
	$mail_template=str_replace("{=order_details}",$order_details,$mail_template);
	
	$order_sql = "INSERT INTO orders(ordered_by,order_date,datetime,delivery_address,comment,po_number) values(".$user->value('id').",NOW(),NOW(),$delivery_office,'$comments','$po_number')";
	$order_res = mysql_query($order_sql) or $cart_error = display_mysql_cart_error($order_sql . " gave " . mysql_error());
	$order_id = mysql_insert_id();
	
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$product_sql = "INSERT INTO order_products(order_id,product_id,quantity) values($order_id,$item,".$_SESSION['cart'][$item]['quantity'].")";
		$product_res=mysql_query($product_sql) or $cart_error = display_mysql_cart_error(mysql_error());
		$order_product_id=mysql_insert_id();
		
		if ($_SESSION['cart'][$item]['attributes']){
			foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
				$attr_sql = "INSERT INTO order_product_attributes VALUES(\"\",$order_product_id,\"$attributename\",\"$attributevalue\")";
				$attr_res = mysql_query($attr_sql) or $cart_error = display_mysql_cart_error(mysql_error());
			}
		}
	}

	if ($cart_error){
		$rollback_transaction=mysql_query("ROLLBACK");
		return 0;
	} else {
		$commit_transaction=mysql_query("COMMIT");
		unset($_SESSION['cart']);
		mail ($mail_cart_to,$subject,$mail_template,$headers) or die("cant send mail");
	}

	return 1;
}

function display_mysql_cart_error($mysql_error_message){

	print "An error has occurred in the mysql: $mysql_error_message";
	return 1;

}

function view_cart(){
	$return .= "<script language=\"Javascript\" type=\"text/javascript\">\nfunction showAttributes(divname){\n";
	$return .= "document.getElementById(divname).style.display=\"block\"\n}\n";
	$return .= "\nfunction hideAttributes(divname){\n";
	$return .= "document.getElementById(divname).style.display=\"none\"\n}\n";
	$return .= "\n</script>\n";
	$return .= "<form name=\"cart\" method=\"post\" action=\"site.php?s=1&action=update_cart\"><p><table style=\"margin-left:15px\">";
	$trbg="#f1f1f1";
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$sql="SELECT * from products WHERE ID = $item";
		$res=mysql_query($sql);
		$h=mysql_fetch_array($res,MYSQL_ASSOC);	
		$return .= "<tr bgcolor=\"$trbg\"><td>".$h['title'];
		if ($_SESSION['cart'][$item]['attributes']){
			$return .= "<br /><a href=\"Javascript:showAttributes('attributediv')\" class=\"product_attributes_view_link\">Click here to view product attributes:</a>";
			$return .= "<div id=\"attributediv\" style=\"display:none\">";
			foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
				$return .= "<b>" . $attributename . ":</b> " . $attributevalue . "<br />";
			}
			$return .= "<a href=\"Javascript:hideAttributes('attributediv')\" class=\"product_attributes_hide_link\">Hide attributes</a><br /></div>";
		}
		$return .= "</td><td><input type=\"text\" size=\"3\" value=\"".$_SESSION['cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\"></td><td><a href=\"site.php?s=1&action=cart_remove&product_id=".$h['ID']."\" class=\"order_button\">Remove</a></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}
	if (mysql_num_rows($res)>0){
		$return .= "<tr><td></td><td><a href=\"Javascript:document.forms['cart'].submit()\" class=\"order_button\">Update</a></td><td></td></tr>";
	} else {
		$return .= "<tr><td colspan=\"3\">There are currently no items ready to order.</td></tr>";
	}
	$return .= "</table></p><p></p>";
	$return .= "</form>";
	print "ready to return";
	return $return;
}

function view_cart_with_prices(){

	$return .= "<script language=\"Javascript\" type=\"text/javascript\">\nfunction showAttributes(divname){\n";
	$return .= "document.getElementById(divname).style.display=\"block\"\n}\n";
	$return .= "\nfunction hideAttributes(divname){\n";
	$return .= "document.getElementById(divname).style.display=\"none\"\n}\n";
	$return .= "\n</script>\n";
	$return .= "<form name=\"cart\" method=\"post\" action=\"site.php?s=1&action=update_cart\"><p><table style=\"margin-left:15px\">";
	$trbg="#f1f1f1";
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$sql="SELECT * from products WHERE ID = $item";
		$res=mysql_query($sql);
		$h=mysql_fetch_array($res,MYSQL_ASSOC);	
		$return .= "<tr bgcolor=\"$trbg\"><td>".$h['title'];
		if ($_SESSION['cart'][$item]['attributes']){
			$return .= "<br /><a href=\"Javascript:showAttributes('attributediv')\" class=\"product_attributes_view_link\">Click here to view product attributes:</a>";
			$return .= "<div id=\"attributediv\" style=\"display:none; font-size:12px;\">";
			foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
				$return .= "<b>" . $attributename . ":</b> " . $attributevalue . "<br />";
			}
			$return .= "<a href=\"Javascript:hideAttributes('attributediv')\" class=\"product_attributes_hide_link\">Hide attributes</a><br /></div>";
		}
		$line_cost=calculate_line_cost($item,$_SESSION['cart'][$item]['quantity']);
		$running_total = $running_total + $line_cost;
		$return .= "</td><td><input type=\"text\" size=\"3\" value=\"".$_SESSION['cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\"></td><td>&pound; $line_cost</td><td><a href=\"site.php?s=1&action=cart_remove&product_id=".$h['ID']."\" class=\"order_button\">Remove</a></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}
	if (mysql_num_rows($res)>0){
		$return .= "<tr><td></td><td><a href=\"Javascript:document.forms['cart'].submit()\" class=\"order_button\">Update</a></td><td></td></tr>";
	} else {
		$return .= "<tr><td colspan=\"3\">There are currently no items ready to order.</td></tr>";
	}
	$return .= "</table></p><p></p>";
	$return .= "</form>";
	$return .= "<p><b>Order Total:</b> &pound; " . sprintf("%4.2f",$running_total) . "</p>\n";
	return $return;

}

function view_cart_static(){

	$return .= "<script language=\"Javascript\" type=\"text/javascript\">\nfunction showAttributes(divname){\n";
	$return .= "document.getElementById(divname).style.display=\"block\"\n}\n";
	$return .= "\nfunction hideAttributes(divname){\n";
	$return .= "document.getElementById(divname).style.display=\"none\"\n}\n";
	$return .= "\n</script>\n";
	$return .= "<p><table style=\"margin-left:15px\"><tr style=\"background-color:#cccccc; font-weight:bold;\"><td>Item</td><td>Quantity</td><td>Price</td></tr>";
	$trbg="#f1f1f1";
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$sql="SELECT * from products WHERE ID = $item";
		$res=mysql_query($sql);
		$h=mysql_fetch_array($res,MYSQL_ASSOC);	
		$return .= "<tr bgcolor=\"$trbg\"><td>".$h['title'];
		if ($_SESSION['cart'][$item]['attributes']){
			$return .= "<br /><a href=\"Javascript:showAttributes('attributediv')\" class=\"product_attributes_view_link\">Click here to view product attributes:</a>";
			$return .= "<div id=\"attributediv\" style=\"display:none; font-size:12px;\">";
			foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
				$return .= "<b>" . $attributename . ":</b> " . $attributevalue . "<br />";
			}
			$return .= "<a href=\"Javascript:hideAttributes('attributediv')\" class=\"product_attributes_hide_link\">Hide attributes</a><br /></div>";
		}
		$line_cost=calculate_line_cost($item,$_SESSION['cart'][$item]['quantity']);
		$running_total = $running_total + $line_cost;
		$return .= "</td><td>" . $_SESSION['cart'][$item]['quantity']. "<td>&pound; $line_cost</td></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}
	$return .= "</table></p><p></p>";
	$return .= "<p><b>Order Total:</b> &pound; " . sprintf("%4.2f",$running_total) . "</p>\n";
	return $return;

}

function order_and_browse_buttons(){
	global $mycart;
	$mt=$mycart->value("cart_template");
	$ordermore=$mycart->value("order_more_links_to");
	$return .= "<p><span class=\"jc_button_140\" ><a href=\"$ordermore\">Order more</a></span><br />";
	if ($_SESSION['cart']){
		$return .= "<span class=\"jc_button_140\" ><a href=\"site.php?s=1&amp;action=confirm_order&mt=$mt\">Place order</a></span></p>";
	}
	return $return;
}

function add_to_cart($productid,$quantity){
	$added_extra=0;
	if (!$quantity){$quantity=1;}
	if ($_SESSION['cart'][$productid]){$_SESSION['cart'][$productid]['quantity']++; $added_extra=1;} else { $_SESSION['cart'][$productid]['quantity']=$quantity;}
	// check for attributes
	$attr_sql="SELECT * from products_to_product_attributes INNER JOIN product_attributes ON products_to_product_attributes.attribute_id = product_attributes.id WHERE product_id = $productid";
	$attr_res=mysql_query($attr_sql);
	while ($ah=mysql_fetch_array($attr_res,MYSQL_ASSOC)){
		if ($_POST[str_replace(" ","_",$ah['attribute_name'])]){
			$_SESSION['cart'][$productid]['attributes'][$ah['attribute_name']]=$_POST[str_replace(" ","_",$ah['attribute_name'])];
		}
	}
	$sql="SELECT title from products WHERE ID = $productid";
	$res=mysql_query($sql) or die(mysql_error());
	$h=mysql_fetch_array($res,MYSQL_ASSOC);
	$return_content = "<p>".$h['title']. " has been added to your order.</p>";
	if ($added_extra){ $return_content .= "<p>As this item was already in this order we have added another one to the order.</p>\n";}
	return $return_content;
}

function remove_from_cart($productid){
	if (isset($_SESSION['cart'][$productid])){ unset($_SESSION['cart'][$productid]);}
	return "<p>Product deleted</p>";
}

function update_cart(){
	foreach ($_POST as $key => $val){
		$key = str_replace("item_","",$key);
		if ($_SESSION['cart'][$key]){
			$newkey="item_".$key; 
			$_SESSION['cart'][$key]['quantity']=$_POST[$newkey];
		}
	}
}


function get_category_breadcrumb($current_category){
	$categories=get_categories_as_array($current_category);
	$categories = array_reverse($categories);
	$return=join(" | ",$categories);
	return $return;
}

function get_categories_as_array($current_category){

	$parent_category=$current_category;
	$parents=array();
	$i=0;
	while ($parent_category) {
		@list($parent,$typename,$id)=explode("|",get_parent_category($parent_category));
		if ($id==1){
			array_push ($parents,"<a href=\"stationery.html\">$typename</a>");
		} else {
			array_push ($parents,"<a href=\"site.php?s=1&content=88&category_id=$id&action=cart_categories_browse\">$typename</a>");
		}
		if (!$parent){$parent_category=NULL;} else {$parent_category=$parent;}
		$category_array[$i]['id']=$id;
		$category_array[$i]['name']=$typename;
		$i++;
		if ($i>10){ print "Too many layers"; var_dump($parents); exit;}
	}
	return $parents;
}
function get_category_ids_as_array($current_category){

	$parent_category=$current_category;
	$parents=array();
	$i=0;
	while ($parent_category) {
		@list($parent,$typename,$id)=explode("|",get_parent_category($parent_category));
			array_push ($parents,$id);
		if (!$parent){$parent_category=NULL;} else {$parent_category=$parent;}
		$category_array[$i]['id']=$id;
		$category_array[$i]['name']=$typename;
		$i++;
		if ($i>10){ print "Too many layers"; var_dump($parents); exit;}
	}
	return $parents;
}

function get_parent_category($current_category){

	$sql= "SELECT id,parent,product_type from product_types WHERE id = $current_category";
	$parent=NULL;
	$res=mysql_query($sql) or die(mysql_error());
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$parent=$h['parent'] . "|" . $h['product_type'] . "|" . $h['id'];
	}
	return $parent;

}

function order_header(){
	$return = "<p class=\"subheading\"><b>Your order:</b>\n</p>";
	return $return;
}

function calculate_line_cost($item,$quantity){

	// what category is this product in
	$sql= "SELECT category_name from products where id = $item";
	$res=mysql_query($sql) or die(mysql_error());
	$h=mysql_fetch_array($res);
	$category=$h['category_name'];
	$categories=get_category_ids_as_array($category);
	$categories = array_reverse($categories);
	$category=array_shift($categories);
	$category=array_shift($categories); // we shift twice to get out of 'Stationery'
	
	$sql = "SELECT * from stationery_category_costs WHERE category = $category";
	$res=mysql_query($sql);
	$h=mysql_fetch_array($res);
	$out_cost=$h['out_cost'];
	$percentage=$h['percentage_for_delivery'];
	if (!$h['extra_unit_cost']){
		$out_cost=round(($out_cost*$quantity)+((($out_cost*$quantity)/100)*$percentage));
	} else if ($quantity>1){
		$out_cost=$out_cost+(($h['extra_unit_cost'])*($quantity-1));
		$out_cost=$out_cost+(($out_cost/100)*$percentage);
	} else {
	
	}
	return sprintf("%01.2f", $out_cost); ;

}
