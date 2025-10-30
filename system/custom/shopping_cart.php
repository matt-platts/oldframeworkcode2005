<?php
global $code_validated;
require_once("$libpath/classes/shopping_cart/shopping_cart.php");
$mycart=new shopping_cart();
if (!$_SESSION){session_start();}

if ($_REQUEST['action']=="shopping_cart_orders_complete"){
	$title="Your Orders";
	$content .= $mycart->shopping_cart_orders_complete();
}

if ($_REQUEST['action']=="set_preorder_preference"){
	$mycart->set_preorder_preference($_POST['preorder_pay_now']);
	$content_returned = $mycart->log_order_pre_payment();
	$content=$content_returned['content'];
	$title=$content_returned['title'];
}

if ($_REQUEST['action']=="update_cart"){
	$title="Shopping cart"; 
	$mycart->update_cart(); 
	$content .= $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
}
if ($_REQUEST['action']=="update_preorder_cart"){
	$title="Shopping cart"; 
	$mycart->update_preorder_cart(); 
	$content .= $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
}
if ($_REQUEST['action']=="cart_categories_browse"){
	$title = $mycart->get_category_breadcrumb($_GET['category_id']);
	$browser_title = $page->browser_title_from_id($_GET['content']); 
	if (!$browser_title){$browser_title="Shopping Cart";}
	$content .= $page->content_from_id($_GET['content']); 
}
if ($_REQUEST['action']=="cart_add"){
	$content = $mycart->add_to_cart($_REQUEST['product_id'],$_REQUEST['quantity']);
	$title="Shopping cart";
	$content .= $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
}
if ($_REQUEST['action']=="preorder_cart_add"){
	$content = $mycart->add_to_preorder_cart($_REQUEST['product_id'],$_REQUEST['quantity']);
	$title="Shopping cart";
	$content .= $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
}
if ($_REQUEST['action']=="cart_view"){
	$title="View cart";
	$content .= $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
	print "ok done it";
}
if ($_REQUEST['action']=="cart_remove"){
	$content .= $mycart->remove_from_cart($_REQUEST['product_id']); 
	$title = "Your Cart";
	$content = $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
}
if ($_REQUEST['action']=="preorder_cart_remove"){
	$content .= $mycart->remove_from_preorder_cart($_REQUEST['product_id']); 
	$title = "Your Cart";
	$content = $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
}
if ($_REQUEST['action']=="remove_cart_attribute"){
	$remove_attribute_result = $mycart->remove_cart_attribute($_REQUEST['pid'],$_REQUEST['attribute'],$_REQUEST['attributevalue']);
	$title="View Cart";
	$content = $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
}
if ($_REQUEST['action']=="confirm_order"){
	$content .= $mycart->confirm_order();
	$title="Please confirm your order:";
}
if ($_REQUEST['action']=="enter_promotional_code"){
	$content .= $mycart->enter_promotional_code($_REQUEST['promotional_code']);
	$title="Shopping cart";
}
if ($_REQUEST['action']=="enter_golden_account_code"){
	$content .= $mycart->enter_golden_account_code($_REQUEST['promotional_code']);
	$title="Shopping cart";
}
if ($_REQUEST['action']=="place_order"){
	$content_returned = $mycart->log_order_pre_payment();
	$content=$content_returned['content'];
	$title=$content_returned['title'];
}

if ($_REQUEST['action']=="add_product_to_email_list"){
	$content_returned = $mycart->store_email_when_out_of_stock($_GET['product']);
	$title="Email me when this product comes into stock";
	$content=$content_returned;
}

if ($_REQUEST['action']=="place_order_on_account"){
	$title = "Thank You";
	$content = place_order_on_account();
}

if ($_REQUEST['action']=="cats"){
	$title = get_category_breadcrumb(18);
	print $title; 
	exit;
}

if ($_REQUEST['action']=="pre_order_paypal"){
	$title="Pay for Pre-Order via Paypal";
	$content=$mycart->paypal_pay_for_pre_order($_GET['order_id']);
}

if ($_REQUEST['action']=="load_order_into_cart"){
	$title="Complete Your Order";
	$content=$mycart->pay_for_pre_order($_GET['order_id']);
}

if ($_REQUEST['action']=="load_payment_module"){
	$result=$mycart->load_payment_module();
	$content=$result;
}

if ($_REQUEST['action']=="user_reauth_preorder"){
	$title="Reauthorise Preorder #" . $_GET['orderno'];
	$content=$mycart->user_reauth_preorder($_GET['orderno']);
}

if ($_REQUEST['action']=="paypal_preorders_only"){
	$result=$mycart->paypal_place_preorders_only();
	$content=$result;
	$title="Order complete.";
}

if ($_REQUEST['action']=="escape_pre_order_payment"){
	$result=$mycart->escape_pre_order_payment();
	$content=$result;
	$title="Pre-order cancelled - start a new order";
}

if ($_REQUEST['action']=="load_payment_success"){
	// which payment method are we using here?
	$payment_method_used=$_SESSION['payment_method_in_use'];
	if (!$payment_method_used){ format_error("No payment method found, it appears that your session has expired. To return to the checkout please click <a href=\"checkout.html\">here</a>",1);}
	global $db;
	$sql="SELECT class_filename,class_name FROM payment_modules WHERE id = $payment_method_used";
	$payment_query=$db->query($sql);
	while ($h=$db->fetch_array($payment_query)){
		$load_class=$h['class_filename'];	
		$new_class=$h['class_name'];
	}
	if (!$load_class || !$new_class){
		format_error("An error has occurred. Payment module appears to be improperly installed.",1,"","Cannot find class '$load_class' in '$new_class'");
	}
	$load_file=LIBPATH."/classes/$load_class";
	//print "<p>Loading class $load_class or $load_file</p>";
	require_once($load_file);
	$payment_class=new $new_class;
	$payment_result=$payment_class->load_payment_success();
	//$result=$mycart->load_paypal_payment_success();
	$title="Payment Complete";
	$content=$payment_result;
	// do we have downloads?
	// if we only have pre-orders, we dont!
	$int=$mycart->getInternalSaleId();
	if ($int){
		$download_urls_array=$mycart->create_download_urls();
		$retrieve_urls = $mycart->retrieve_download_urls_from_current_order();
		if ($retrieve_urls['html']){
			$content .= "<p>The URLS for downloading products have been emailed to you, and are also listed below:</p>";
			$content .= $retrieve_urls['html']; 
		}
	}
	return $content;
}

if ($_REQUEST['action']=="load_payment_cancel"){
	$result=$mycart->load_paypal_payment_cancel();
	$title="Payment Cancelled";
	$content=$result;
}



// custom function used for john crane marketing portal
function calculate_line_cost($item,$quantity){

	// what category is this product in
	$sql= "SELECT category from products where id = $item";
	$res=mysql_query($sql) or die(mysql_error());
	$h=mysql_fetch_array($res);
	$category=$h['category'];
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

function place_order_on_account(){

	global $mycart;
	// 1. Internal sale id needs to be got
	$internalSaleId=$mycart->getInternalSaleId();
	// 1. Log the order as completed:
	$log_completed_payment_sql="UPDATE orders SET complete = 1 WHERE id = $internalSaleId";
	$complete_order_result=mysql_query($log_completed_payment_sql) or die(mysql_error());

	// now create some download urls
	$select_products_sql="SELECT * from order_products where order_id = $internalSaleId";
	$res=mysql_query($select_products_sql);	
	$return .= "<p>You can download your files from the following URLs:<br /></p>";
	$return .= "<div style=\"margin:5px; padding:15px; border-width:1px; border-color:#ffffff; border-style:dashed;\">";
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$product_id=$h['id'];
		$separator="-";
		$uid=md5(uniqid(mt_rand(), true));
		$download_url=$product_id.$separator.$uid;
		$insert_download_sql="INSERT INTO download_urls(id,order_id,product_data_id,download_url) values(\"\",$internalSaleId,$product_id,\"$download_url\")";
		$insert_url_res=mysql_query($insert_download_sql) or die("Cant run $insert_download_sql: " . mysql_error());
	
		// get product title
		$get_title_sql="SELECT title FROM products WHERE id = " . $h['product_id'];
		$title_res=mysql_query($get_title_sql) or die (mysql_error());
		while ($j=mysql_fetch_array($title_res,MYSQL_ASSOC)){
			$product_title = $j['title'];
		}
		if ($product_title != "Golden Account" && $product_title != "Test Account"){
			$return .= "<p><b>$product_title:</b><br /><a href=\"http://www.theheroesofwoodstockdownloads.com/downloads/$download_url.zip\">http://www.theheroesofwoodstockdownloads.com/downloads/$download_url.zip</a></p>";
			$download_urls_text .= "$product_title\nhttp://www.theheroesofwoodstockdownloads.com/downloads/$download_url.zip\n\n";
		} else {
		}
	}

	return $return; 
}

function load_shopping_cart_configuration(){

	require once ("$libpath/classes/shoping_cart.php");
	$cart = new shopping_cart();

}

function get_category_breadcrumb($current_category){
        $categories=get_categories_as_array($current_category);
        $categories = array_reverse($categories);
	$return = "<p class=\"breadcrumb_navigation\"><a href=\"shop.html\" class=\"breadcrumb_navigation_link\">Shop</a> &gt; ";
        $return.=join(" &gt; ",$categories) . "</p>";
        return $return;
}


function get_categories_as_array($current_category){
	$parent_category=$current_category;
	$parents=array();
	$i=0;
	while ($parent_category) {
		@list($parent,$typename,$id)=explode("|",get_parent_category($parent_category));
		array_push ($parents,"<a href=\"site.php?content=88&category_id=$id&action=cart_categories_browse\" class=\"breadcrumb_navigation_link\">$typename</a>");
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



?>
