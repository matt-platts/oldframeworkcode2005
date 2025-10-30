<?php

class shopping_cart {

	function __construct(){
		if (array_key_exists("action",$_GET)){
			$this->cart_action=$_GET['action'];
		} else {
			$this->cart_action="";
		}
		global $db;
		// get data from config table
		$sql="SELECT * from shopping_cart_configuration";
		$res=$db->query($sql);
		while ($row=$db->fetch_array($res)){
			$this->products_table=($row['cart_template']==0)? 0 : $row['cart_template'];
			$this->email_confirmation_template=$row['email_confirmation_template'];
			$this->user_details_for_emails=array();
			$this->customer_email_confirmation_template=$row['customer_email_confirmation_template'];
			$this->customer_preorder_email_confirmation_template=$row['customer_preorder_email_confirmation_template'];
			$this->customer_email_subject=$row['customer_email_subject'];
			$this->send_preliminary_order_notifications=$row['send_preliminary_order_notifications'];
			$this->preliminary_notification_template=$row['preliminary_notification_template'];
			$this->preliminary_notification_subject=$row['preliminary_notification_subject'];
			$this->mail_orders_to=$row['mail_orders_to'];
			$this->mail_orders_subject=$row['mail_orders_subject'];
			$this->mail_orders_from_name=$row['mail_orders_from_name'];
			$this->mail_orders_from_address=$row['mail_orders_from_address'];
			$this->email_user_data_template=$row['email_user_data_template'];
			$this->products_table=$row['products_table'];
			$this->categories_table=$row['categories_table'];
			$this->default_currency_symbol=str_replace("£","&pound;",$row['default_currency_symbol']);
			$this->shipping_modules_installed=$row['shipping_modules_installed'];
			$this->payment_modules_installed=$row['payment_modules_installed'];
			$this->allow_multiple_quantities=$row['allow_multiple_quantities'];
			$this->category_list_page=$row['category_list_page'];
			$this->products_list_page=$row['products_list_page'];
			$this->order_more_links_to=$row['order_more_links_to'];
			$this->cart_template=$row['cart_template'];
			$this->cart_default_web_site=$row['cart_default_web_site'];
			$this->golden_account_active=$row['golden_account_active'];
			$this->buy_requires_login=$row['buy_requires_login'];
			$this->no_login_user_details_form_filter=$row['no_login_user_details_form_filter'];
			$this->product_title_fields=$row['product_title_fields'];
			$this->check_stock_at_checkout=$row['check_stock_at_checkout'];
			$this->price_field=$row['price_field'];
			if ($row['calculate_price_field_by_function']){
				$this->price_field=call_user_func($row['calculate_price_field_by_function']);
			}
			if (!$this->price_field){ print "Error: no price field from " . $row['calculate_price_field_by_function'];}
			$this->hide_product_attributes_in_cart=$row['hide_product_attributes_in_cart'];
			$this->product_title_fields_template=$this->product_title_fields; // retain the template
			$this->product_title_no_of_cols=count(explode("|",$this->product_title_fields)); // no of cols to display product in shopping cart lists
			// WE PROB DONT WANT TO DO THE ABOVE AS IT OVERWRITES
			$this->product_title_fields=$this->get_product_title_fields($this->product_title_fields); // comma separated list of db fields used to make up the main title
			$this->product_details_page=$row['product_details_page']; // comma separated list of db fields used to make up the main title
			$this->user_details_confirm_template=$row['user_details_confirm_template'];
			$this->external_stock_check_function=$row['external_stock_check_function'];
			$this->external_order_post_function=$row['external_order_post_function'];
			$this->run_extra_code_on_place_order_success=$row['run_code_on_place_order_success'];
			$this->approve_terms=$row['approve_terms'];
			$this->source_identifier=$row['source_identifier'];
			$this->record_payments_separately=$row['record_payments_separately'];
			$this->email_when_back_in_stock=$row['email_when_back_in_stock'];
		}
	}

	function get_product_title_fields($formatted_field){

		if (preg_match("/{=\w+}/",$formatted_field)){
			$all_fields=preg_match_all("/{=(\w+)}/ims",$formatted_field,$all_field_matches);
			$product_title_fields=join(",",$all_field_matches[1]);
		} else {
			$product_title_fields=$formatted_field;
		}
		return $product_title_fields;
	
	}

	function value($of){
		return $this->$of;
	}

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }

function product_title_column_number(){
// number of table columns required to list product title
	$product_title_fields_array=explode("|",$this->product_title_fields);
	return count($product_title_fields_array); 
}

function cart_contents_from_session(){

	$view_cart_options['run_modules']=1;
	$view_cart_options['allow_update']=0;
	$view_cart_options['email']=1;
	$return=$this->view_cart_general($view_cart_options);
	return $return;
	exit;
/*
	global $db;
	$running_total=0;
	$cart_template=$this->value("cart_template");
	$product_title_fields_array=explode(",",$this->value("product_title_fields"));
	$product_title_fields_template=$this->value("product_title_fields_template");
	$default_currency_symbol = $this->value("default_currency_symbol");
	$return .= "<p><table style=\"margin-left:15px\"><tr style=\"background-color:#222222; font-weight:bold; color:#eeeeee;\"><td>Item</td>";
	if ($this->value("allow_multiple_quantities")){
		$return .= "<td>Quantity</td>";
	}
	$return .= "<td>Price</td></tr>";
	$trbg="#f1f1f1";
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$products_pk="ID";
		$sql_select_list=$products_pk . ",".$this->value("product_title_fields").",".$this->price_field.",image";
		$sql="SELECT $sql_select_list FROM " . $this->value("products_table") . " WHERE $products_pk = $item";
		$res=$db->query($sql);
		$h=$db->fetch_array($res,MYSQL_ASSOC);	
		$return .= "<tr bgcolor=\"$trbg\"><td>";
		$return .= $this->get_product_title($h); 

		if ($_SESSION['cart'][$item]['attributes']){
			$attribute_display_css="block";
			if ($this->value("hide_product_attributes_in_cart")){
				$return .= "<br /><a href=\"Javascript:showDiv('attributediv')\" class=\"product_attributes_view_link\">Click here to view product attributes:</a>";
				$attribute_display_css="none";
			}
			$return .= "<div id=\"attributediv\" style=\"display:$attribute_display_css; font-size:12px;\">";
			foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
				$return .= "<b>" . $attributename . ":</b> " . $attributevalue . "<br />";
			}
			if ($this->value("hide_product_attributes_in_cart")){
				$return .= "<a href=\"Javascript:hideDiv('attributediv')\" class=\"product_attributes_hide_link\">Hide attributes</a><br /></div>";
			} else {
				$return .= "</div>";
			}
		}
		$line_cost=$h[$this->value("price_field")];
		if (!$this->value("allow_multiple_quantities")){
			$running_total = $running_total + $line_cost;
		} else {
			$running_total = $running_total + ($line_cost*$_SESSION['cart'][$item]['quantity']);
		}
		$return .= "</td><td style=\"color:#333333\">" . $_SESSION['cart'][$item]['quantity']. "</td><td style=\"color:#333333\">$default_currency_symbol " . $line_cost*$_SESSION['cart'][$item]['quantity'] . "</td></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}
	$return .= "</table></p><p></p>";
	$return .= "<p><b>Order Total:</b> $default_currency_symbol " . sprintf("%4.2f",$running_total) . "</p>\n";
	$return .= "<b>Shipping: </b>" . $this->value("default_currency_symbol") . $this->calculate_shipping(); // here we need the country! 
	$return .= "<p><b>Total: </b>" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</p>";
	$_SESSION['total_price']=sprintf("%4.2f",$running_total);
	return $return;
*/
}

function total_items_in_cart(){
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$counter++;
	}

	return "does $counter match " . count($_SESSION['cart']);
}

function add_to_cart($productid,$quantity){
	if ($_SESSION['order_number_set'] && !$_SESSION['allow_add_once']){
		$return_content=$this->unable_to_edit_order();
		print $return_content;
		exit;
	}
	//if ($_SESSION['cart'][$productid]){ return; }
	global $db;
	if (!$productid){ format_error("No product id sent",1);}
	if (!is_numeric($productid)){ format_error("Bad Product Id of " . $productid . "!",1); }
	$return_content = "";
	$added_extra=0;
	if (!$quantity){$quantity=1;}
	if ($db->field_exists_in_table("products","master_product")){
		$check_master_product="SELECT master_product FROM products WHERE ID = $productid";
		$check_master_product_rv=$db->query($check_master_product);
		$check_master_product_h=$db->fetch_array($check_master_product_rv);
		if($check_master_product_h['master_product']==1){
			ob_end_clean();
			header("Location: /product_details/$productid/Select-Size/");
			exit;
		}
	}
	
	// this is the actual add line. Change from product id to a uuid then.
	foreach ($_SESSION['cart'] as $item_in_cart => $itemdata){
			if ($itemdata['product_id']==$productid){
				$added_extra=1;
				$already_got_this_product=1;
				$session_product_identifier = $item_in_cart;
				if ($_REQUEST['user_can_choose_price']){
					if (is_numeric($_REQUEST['user_can_choose_price']) && $_REQUEST['user_can_choose_price']>0){
						if ($_REQUEST['user_can_choose_price'] != $itemdata['price']){
							$added_extra=0;
						} else {
						}
					}
				}
			}
	}

	if (!$added_extra){
		$session_product_identifier=uniqid();
		$_SESSION['cart'][$session_product_identifier]['quantity'] = $quantity;
	} else {
		$_SESSION['cart'][$session_product_identifier]['quantity'] = $_SESSION['cart'][$session_product_identifier]['quantity'] + $quantity;
	}

	// store the price in the session
	$sql="SELECT " . $this->value("price_field") . " FROM products WHERE id = $productid";
	$result=$db->query($sql);
	$product_price=$db->fetch_array($result);

	$_SESSION['cart'][$session_product_identifier]['price']=$product_price[$this->value("price_field")];
	$_SESSION['cart'][$session_product_identifier]['product_id']=$productid;
	if ($_REQUEST['user_can_choose_price']){
		if (is_numeric($_REQUEST['user_can_choose_price']) && $_REQUEST['user_can_choose_price']>0){
			$_SESSION['cart'][$session_product_identifier]['price']=$db->db_escape($_REQUEST['user_can_choose_price']);
		}
	}
	// end store the price in the session

	if (!$added_extra || $this->value("allow_multiple_quantities")){
	// check for attributes
	$attr_sql="SELECT * from products_to_product_attributes INNER JOIN product_attributes ON products_to_product_attributes.attribute_id = product_attributes.id WHERE product_id = $productid";
	$attr_res=$db->query($attr_sql);
	while ($ah=$db->fetch_array($attr_res)){
		if ($_POST[str_replace(" ","_",$ah['attribute_name'])]){
			if (!$_SESSION['cart'][$session_product_identifier]['attributes']){
				$_SESSION['cart'][$session_product_identifier]['attributes'] = array();
				$new_attribute[$ah['attribute_name']]['value']=$_POST[str_replace(" ","_",$ah['attribute_name'])];
				$new_attribute[$ah['attribute_name']]['quantity']=1;
				array_push($_SESSION['cart'][$session_product_identifier]['attributes'],$new_attribute);
				$attribute_added=1;
			} else {
				// do we already have this attribute in here?

				$attribute_array_count=0;
				foreach ($_SESSION['cart'][$session_product_identifier]['attributes'] as $each_attribute){
					foreach ($each_attribute as $attributename => $attributevalue){
							if ($attributevalue['value']==$_POST[str_replace(" ","_",$ah['attribute_name'])]){
							$new_quantity=$attributevalue['quantity']+1;	
							$_SESSION['cart'][$session_product_identifier]['attributes'][$attribute_array_count][$attributename]['quantity']=$new_quantity;
							$attribute_added=1;
							}
					}
				$attribute_array_count++;
				}
			}
			if (!$attribute_added){
				$new_attribute[$ah['attribute_name']]['value']=$_POST[str_replace(" ","_",$ah['attribute_name'])];
				$new_attribute[$ah['attribute_name']]['quantity']=1;
				array_push($_SESSION['cart'][$session_product_identifier]['attributes'],$new_attribute);
			}

		}
		/* 
		old format
		if ($_POST[str_replace(" ","_",$ah['attribute_name'])]){
			$_SESSION['cart'][$productid]['attributes'][$ah['attribute_name']]=$_POST[str_replace(" ","_",$ah['attribute_name'])];
		}
		*/
	}

	$sql="SELECT " . $this->value("product_title_fields") . " FROM products WHERE ID = $productid";
	$res=$db->query($sql) or die(->db_error());
	$h=$db->fetch_array($res);
	$return_content .= "<p class=\"cart_header\"><span class=\"product_added_text_product_title\">".$this->get_product_title($h). "</span> has been added to your order.</p>";
	if ($added_extra){ $return_content .= "<p class=\"cart_already_ordered_item_message\">As this item was already in this order we have added another one to the order.</p>\n";}
	} else {
		$return_content .= "<p>This download is already in your order.</p>";
	}
	//var_dump($_SESSION['cart']); exit;
	return $return_content;
}

function add_to_preorder_cart($productid,$quantity){
	if ($_SESSION['order_number_set'] && !$_SESSION['allow_add_once']){
		$return_content=$this->unable_to_edit_order();
		print $return_content;
		exit;
	}
	global $db;
	if (!is_numeric($productid)){ format_error("Bad Product Id",1); }
	$return_content = "";
	$added_extra=0;
	if (!$quantity){$quantity=1;}
	if ($_SESSION['preorder_cart'][$productid]){$_SESSION['preorder_cart'][$productid]['quantity']++; $added_extra=1;} else {
		$_SESSION['preorder_cart'][$productid]['quantity']=$quantity;
		$_SESSION['preorder_cart'][$productid]['preorder_cart_id']=uniqid();
	}
	if (!$added_extra || $this->value("allow_multiple_quantities")){
	// check for attributes
	$attr_sql="SELECT * from products_to_product_attributes INNER JOIN product_attributes ON products_to_product_attributes.attribute_id = product_attributes.id WHERE product_id = $productid";
	$attr_res=$db->query($attr_sql);
	while ($ah=$db->fetch_array($attr_res)){
		if ($_POST[str_replace(" ","_",$ah['attribute_name'])]){
			if (!$_SESSION['preorder_cart'][$productid]['attributes']){
				$_SESSION['preorder_cart'][$productid]['attributes'] = array();
				$new_attribute[$ah['attribute_name']]['value']=$_POST[str_replace(" ","_",$ah['attribute_name'])];
				$new_attribute[$ah['attribute_name']]['quantity']=1;
				array_push($_SESSION['preorder_cart'][$productid]['attributes'],$new_attribute);
				$attribute_added=1;
			} else {
				// do we already have this attribute in here?

				$attribute_array_count=0;
				foreach ($_SESSION['preorder_cart'][$productid]['attributes'] as $each_attribute){
					foreach ($each_attribute as $attributename => $attributevalue){
							if ($attributevalue['value']==$_POST[str_replace(" ","_",$ah['attribute_name'])]){
							$new_quantity=$attributevalue['quantity']+1;	
							$_SESSION['preorder_cart'][$productid]['attributes'][$attribute_array_count][$attributename]['quantity']=$new_quantity;
							$attribute_added=1;
							}
					}
				$attribute_array_count++;
				}
			}
			if (!$attribute_added){
				$new_attribute[$ah['attribute_name']]['value']=$_POST[str_replace(" ","_",$ah['attribute_name'])];
				$new_attribute[$ah['attribute_name']]['quantity']=1;
				array_push($_SESSION['preorder_cart'][$productid]['attributes'],$new_attribute);
			}

		}
	}

	$sql="SELECT " . $this->value("product_title_fields") . ", " . $this->price_field." FROM products WHERE ID = $productid";
	$res=$db->query($sql) or die(->db_error());
	$h=$db->fetch_array($res);
	$_SESSION['preorder_cart'][$productid]['price']=$h[$this->value("price_field")];
	$return_content .= "<p class=\"cart_header\"><span class=\"product_added_text_product_title\">".$this->get_product_title($h). "</span> has been added to your pre-orders.</p>";
	if ($added_extra){ $return_content .= "<p class=\"cart_already_ordered_item_message\">As this item was already in your pre-order list, we have added another one to the order.</p>\n";}
	} else {
		$return_content .= "<p>This download is already in your order.</p>";
	}
	return $return_content;
}

function update_cart(){
	if ($_SESSION['order_number_set'] && !$_SESSION['allow_add_once']){
		$return_content=$this->unable_to_edit_order();
		print $return_content;
		exit;
	}
        foreach ($_POST as $key => $val){
                $key = str_replace("item_","",$key);
                if ($_SESSION['cart'][$key]){
                        $newkey="item_".$key;
                        $_SESSION['cart'][$key]['quantity']=$_POST[$newkey];
                }
        }
}

function update_preorder_cart(){
	if ($_SESSION['order_number_set'] && !$_SESSION['allow_add_once']){
		$return_content=$this->unable_to_edit_order();
		print $return_content;
		exit;
	}
        foreach ($_POST as $key => $val){
                $key = str_replace("item_","",$key);
                if ($_SESSION['preorder_cart'][$key]){
                        $newkey="item_".$key;
                        $_SESSION['preorder_cart'][$key]['quantity']=$_POST[$newkey];
                }
        }
}

function remove_from_cart($productid){
        if (isset($_SESSION['cart'][$productid])){ unset($_SESSION['cart'][$productid]);}
        return "<p>Product deleted</p>";
}

function remove_from_preorder_cart($productid){
        if (isset($_SESSION['preorder_cart'][$productid])){ unset($_SESSION['preorder_cart'][$productid]);}
        return "<p>Product deleted</p>";
}

function order_header(){
	return $return;
}

function get_category_breadcrumb($current_category){
	$categories=$this->get_categories_as_array($current_category);
	$categories = array_reverse($categories);
	$return = "<p class=\"breadcrumb_navigation\"><a href=\"shop.html\" class=\"breadcrumb_navigation_link\">Home</a> &gt; ";
	$return .= join(" &gt; ",$categories);
	return $return;
}

function get_categories_as_array($current_category){
	$cart_template=$this->value("cart_template");
	$parent_category=$current_category;
	$base_category_content_page=93;
	$inner_category_content_age=88;
	$parents=array();
	$i=0;
	$htaccess_urls_on=1;
	while ($parent_category) {
		@list($parent,$typename,$id,$pagename)=explode("|",$this->get_parent_category($parent_category));
		if ($pagename){
			$link_to=HTTP_PATH . "/" . $pagename;
		} else if ($parent_category==0 || !$parent){
			if ($htaccess_urls_on==1){
				$link_to=HTTP_PATH . "/categories/$id/Category/content/$base_category_content_page/mt/$cart_template";
			} else {
				$link_to=HTTP_PATH . "/site.php?action=cart_categories_browse&content=$base_category_content_page&amp;category_id=$id&amp;mt=$cart_template";
			}
		} else {
			if ($htaccess_urls_on==1){
				$link_to=HTTP_PATH . "/categories/$id/Category/$parent/Subcategory/content/$inner_category_content_page/mt/$cart_template";
			} else {	
				$link_to=HTTP_PATH . "/site.php?action=cart_categories_browse&content=$inner_category_content_page&amp;category_id=$id&master_category_id=$parent&amp;mt=$cart_template";
			}
		}

		array_push ($parents,"<a href=\"$link_to\">$typename</a>");
		if (!$parent){$parent_category=NULL;} else {$parent_category=$parent;}
		$category_array[$i]['id']=$id;
		$category_array[$i]['name']=$typename;
		$category_array[$i]['html_page_name']=$pagename;
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
		@list($parent,$typename,$id,$pagename)=explode("|",$this->get_parent_category($parent_category));
			array_push ($parents,$id);
		if (!$parent){$parent_category=NULL;} else {$parent_category=$parent;}
		$category_array[$i]['id']=$id;
		$category_array[$i]['name']=$typename;
		$category_array[$i]['pagename']=$typename;
		$i++;
		if ($i>10){ print "Too many layers"; var_dump($parents); exit;}
	}
	return $parents;
}


function get_parent_category($current_category){
	global $db;
        $sql= "SELECT id,parent,html_page_name,category_name from ".$this->value("categories_table")." WHERE id = $current_category";
        $parent=NULL;
        $res=$db->query($sql) or die(->db_error());
        while ($h=$db->fetch_array($res,MYSQL_ASSOC)){
                $parent=$h['parent'] . "|" . $h['category_name'] . "|" . $h['id'] . "|" . $h['html_page_name'];
        }
        return $parent;
}

function display_mysql_cart_error($->db_error_message){
        print "An error has occurred in the mysql: $->db_error_message";
        return 1;
}

function order_and_browse_buttons(){
	//return; // turned off for medico
        $cart_template=$this->value("cart_template");
        $order_more_links_to=$this->value("order_more_links_to");

	$have_products=0;
	foreach ($_SESSION['cart'] as $cartitem){
		$have_products++;
	}
	foreach ($_SESSION['preorder_cart'] as $cartitem){
		$have_products++;
	}

	global $db;
	if ($have_products){ 
		$widget_template=$db->db_quick_match("widgets","widget","dbf_key_name","shopping_cart_buttons");
	} else {
		$widget_template=$db->db_quick_match("widgets","widget","dbf_key_name","shopping_cart_empty");
	}

	if ($widget_template){
		$return=$widget_template;
	}
        return $return;
}

// Options for view_cart
// allow_update - 1 to include the form
// run_modules - include all the extra modules under the basic cart 
// email - 1 to trigger the email templates
function view_cart_general($cart_view_options){
	global $db;
	$cart_template = $this->value("cart_template");
	$default_currency_symbol = $this->value("default_currency_symbol");

	$cart_header="";
	if ($cart_view_options['allow_update']){
		$cart_header = "<form name=\"cart\" method=\"post\" action=\"site.php?action=update_cart&mt=$cart_template\">\n";
	}
	$cart_header .= "<table width=\"100%\" class=\"cart_view_table\">";
	$cart_header_row = "<tr class=\"view_cart_table_header_row\">";
	$cart_header_row .= "<td colspan=\"2\">Product</td>";
	if ($this->value("allow_multiple_quantities")){
		$cart_header_row .= "<td>Quantity</td>";
	}
	$cart_header_row .= "<td>Price Each</td>";
	$cart_header_row .= "<td>Total Price</td>";
	$cart_header_row .= "<td></td>";
	$cart_header_row .= "</tr>";
	$have_products=0;
	$trbg="#f1f1f1";
	$cart_data=array();
	$cart_lines_counter=0;
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$in_cart_out_of_stock=0;
		$products_pk=get_primary_key($this->value("products_table"));
		$sql_select_list=$products_pk . ",".$this->value("product_title_fields").",".$this->price_field.",price_ex_vat,image,is_download,user_can_choose_price,vat";
		if (is_field_in_table($this->value("products_table"),"child_product") && is_field_in_table($this->value("products_table"),"parent_product")){
			$sql_select_list .= ",child_product,parent_product";
		}
		$sql_select_list=str_replace(",,",",",$sql_select_list);
		$sql="SELECT $sql_select_list FROM " . $this->value("products_table") . " WHERE $products_pk = " . $itemdata['product_id'];
		if (!$item){ continue;}
		$res=$db->query($sql);
		$h=$db->fetch_array($res);	
		$return_cart_lines .= "<tr bgcolor=\"$trbg\"><td class=\"view_cart_static_text_color\">";
		$return_cart_lines .= "<a href=\"".HTTP_PATH."/product_details/".$h['ID']."\" style=\"color:#222\">" . $this->get_product_title($h). "</a>"; 
		//$cart_lines_counter=$h[$products_pk];
		$cart_lines_counter=$item;
		//$cart_data[$cart_lines_counter]['primary_key'] = $h[$products_pk];
		$cart_data[$cart_lines_counter]['primary_key'] = $item;
		$cart_data[$cart_lines_counter]['title'] = $this->get_product_title($h);
		$cart_data[$cart_lines_counter]['image'] = $h['image'];
		if (!$h['image'] && $h['child_product']){
			$master_image_sql="SELECT image FROM products WHERE id = " . $h['parent_product'];
			$master_image_rv=$db->query($master_image_sql);
			$master_h=$db->fetch_array($master_image_rv);
			if ($master_h['image']){
				$cart_data[$cart_lines_counter]['image'] = $master_h['image'];
			}
		}

		$qty_in_stock=$this->check_stock_quantity($itemdata['product_id']);
		//print "GOT A STOCK Q OF $qty_in_stock FOR " . $itemdata['product_id'];
		if (($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown") && !$cart_view_options['email']){
			if (!$this->value("email_when_back_in_stock")){
				$set_out_of_stock_sql="UPDATE products set stock_quantity=0,available=0 where ID = " . $h['ID'];
				$out_of_stock_rv=$db->query($set_out_of_stock_sql);
				$in_cart_out_of_stock=1;
				$h[$this->value("price_field")]=0.00;
			}
			unset($_SESSION['cart'][$h['ID']]);
			$h[$this->value("price_field")]=0.00;
		}

		if ($_REQUEST['user_can_choose_price']){
		//	if (is_numeric($_REQUEST['user_can_choose_price']) && $_REQUEST['user_can_choose_price']>0){
		//		$h[$this->value("price_field")]=$_REQUEST['user_can_choose_price'];
		//	}
		}
		if ($h['user_can_choose_price']){
			$h[$this->value("price_field")]=$_SESSION['cart'][$item]['price'];
		}

		$cart_data[$cart_lines_counter]['attributes'] = $this->view_cart_print_product_attributes($item);
		$cart_data[$cart_lines_counter]['unit_price'] = $default_currency_symbol . $h[$this->value("price_field")];
		$cart_data[$cart_lines_counter]['line_price'] = $default_currency_symbol . sprintf("%4.2f",$h[$this->value("price_field")] * $_SESSION['cart'][$item]['quantity']);
		// is this product vatable?
		$sql="SELECT product_categories.vatable FROM product_categories INNER JOIN products ON products.category = product_categories.id WHERE products.ID = " . $h['ID'];
		$result=$db->query($sql);
		while ($hash = $db->fetch_array()){
			if ($hash['vatable']){
				//$cart_data[$cart_lines_counter]['vat_amount']=$default_currency_symbol . sprintf("%4.2f",($h[$this->value("price_field")]*$_SESSION['cart'][$item]['quantity'])/100*16.666666);
				$this_item_actual_vat=$h['vat'];
				$cart_data[$cart_lines_counter]['vat_amount']=$default_currency_symbol . sprintf("%4.2f",($h['vat']));
				$cart_data[$cart_lines_counter]['unit_price']=$default_currency_symbol . sprintf("%4.2f",($h['price_ex_vat']));
			} else {
				$this_item_actual_vat=0;
				$cart_data[$cart_lines_counter]['vat_amount']=$default_currency_symbol . "0.00";
			}
		}


		if ($_SESSION['cart'][$item]['attributes']){
			$cart_data[$cart_lines_counter]['attributes']=$this->view_cart_print_product_attributes($item);
			$return_cart_lines .= $this->view_cart_print_product_attributes($item);
		}

		if (($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown") && !$nh['is_download'] && $this->value("check_stock_at_checkout")){
			$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - This product is out of stock and has been removed from your shopping cart.</span><br />";
			if ($this->value("email_when_back_in_stock")){
				$cart_data[$cart_lines_counter]['stock_alert'] .= "<a href=\"site.php?action=add_product_to_email_list&amp;product=".$item."&mt=31\" rel=\"#add_to_email_list_overlay\">Email me when this item is back in stock</a>";
			}
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else if ($qty_in_stock < $_SESSION['cart'][$item]['quantity'] && !$h['is_download'] && $this->value("check_stock_at_checkout")){
			if (!$qty_in_stock || $qty_in_stock=""){
				$cart_data[$cart_lines_counter]['stock_alert'] = "<br>Ohh<span style=\"color:#cc0000\">Sorry - This product is out of stock.</span><br />";
			} else {
				$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - only '$qty_in_stock' of these are in stock.<br />Please adjust your order quantity.</span><br />";
			}
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else {
			//$return .= "<br>$qty_in_stock in stock.</br>";
		}
		if ($this->value("allow_multiple_quantities")){
			if ($cart_data[$cart_lines_counter]['vat_amount']){
				$line_cost=($h[$this->value("price_field")]+$this_item_actual_vat)*$_SESSION['cart'][$item]['quantity'];
				$line_cost_ex_vat=($h[$this->value("price_field")])*$_SESSION['cart'][$item]['quantity'];
			} else {
				$line_cost=$h[$this->value("price_field")]*$_SESSION['cart'][$item]['quantity'];
				$line_cost_ex_vat=$h[$this->value("price_field")]*$_SESSION['cart'][$item]['quantity'];
			}
		} else {
			if ($cart_data[$cart_lines_counter]['vat_amount']){
				$line_cost=$h[$this->value("price_field")]+$this_item_actual_vat;
				$line_cost_ex_vat=$h[$this->value("price_field")];
			} else {
				$line_cost=$h[$this->value("price_field")];
				$line_cost_ex_vat=$h[$this->value("price_field")];
			}
		}
		$cart_data[$cart_lines_counter]['line_price'] = $default_currency_symbol . sprintf("%4.2f",$line_cost);
		$running_total = $running_total + $line_cost_ex_vat;
		$running_vat=$this_item_actual_vat+$_SESSION['cart'][$item]['quantity'];
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td>";
		$return_cart_lines .= $h[$this->value("price_field")];
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">";
		if ($cart_view_options['allow_update']){
			$return_cart_lines .= "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['cart'][$item]['quantity']."\" name=\"item_".$item."\">";	
			$cart_data[$cart_lines_counter]['quantity'] = "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['cart'][$item]['quantity']."\" name=\"item_".$item."\">";
		} else {
			$return_cart_lines .= $_SESSION['cart'][$item]['quantity'];
			$cart_data[$cart_lines_counter]['quantity'] = $_SESSION['cart'][$item]['quantity'];
		}
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">".$this->value("default_currency_symbol").sprintf("%4.2f",$line_cost)."</td>";
		if ($cart_view_options['allow_update']){
		$return_cart_lines .= "<td><a href=\"site.php?action=cart_remove&mt=$cart_template&cart_product_id=".$h['ID']."\" class=\"remove_button\" style=\"color:#26abd5\">Remove</a></td>";
		$return_cart_lines .= "</tr>";
		}
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
		$have_products++;
	}

        if ($have_products && $this->value("allow_multiple_quantities") && $cart_view_options['allow_update']){
//                $return .= "<tr><td></td><td></td><td><a href=\"Javascript:document.forms['cart'].submit()\" class=\"order_button\" style=\"text-align:center\">Update</a></td><td></td><td class=\"inline_cart_total\">".$this->value("default_currency_symbol").sprintf("%4.2f",$running_total)."</td><td></td></tr>";
	} else if (!$have_products){
                $return .= "<tr><td colspan=\"5\">There are currently no items in your order.</td></tr>";
        }

	$return .= "<tr><td colspan=\"4\" style=\"height:5px; border:0px;\"></td></tr>";
	if (!$this->value("allow_multiple_quantities") || !$cart_view_options['allow_update']){
		//$return .= "<tr><td colspan=\"3\" align=\"right\"><b>Sub Total:</b> </td><td>" . $this->value("default_currency_symbol") . sprintf("%4.2f",$running_total) . "</td></tr>\n";
	}
	$return .= "<tr><td colspan=\"4\" style=\"height:5px; border:0px;\"></td></tr>";
	$_SESSION['total_price']=sprintf("%4.2f",$running_total);

	//if ($this->buy_requires_login && $cart_view_options['run_modules'])
	if ($cart_view_options['run_modules']){
                //$return .= "<tr><td></td><td></td><td></td><td></td><td class=\"inline_cart_total\">".$this->value("default_currency_symbol").sprintf("%4.2f",$running_total)."</td><td></td></tr>";
		if (!$cart_view_options['email']){
			$EXPORT['shipping_total']=$this->value("default_currency_symbol") . $this->calculate_shipping();
			$_SESSION['shipping_amount']=$this->value("shipping_total");
		} else {
			$EXPORT['shipping_total']=$_SESSION['shipping_amount'];
			$this->set_value("shipping_total",$_SESSION['shipping_total']);
		}
		$return .= "<tr><td colspan=\"5\" align=\"right\"><b>Shipping: </b></td><td align=\"right\">" . $EXPORT['shipping_total'] . "</td></tr>"; 
		$itemise_checkout_modules=$this->run_checkout_modules();
		$EXPORT['misc_total']=$this->value("default_currency_sumbol") . "0";
		$EXPORT['checkout_modules_text']="";
		$EXPORT['checkout_modules_total']="";
		foreach ($itemise_checkout_modules as $checkout_module_name=>$checkout_module_data){
			if ($checkout_module_name != "amount_to_add_to_total"){
				$module_text=$checkout_module_data['checkout_itemisation_text'];
				$module_text=str_replace("{=voucher_text}",$_SESSION['voucher_text'],$module_text);
				$return .= "<tr><td colspan=\"5\" align=\"right\"><b>" . $module_text .":</b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $checkout_module_data['total'] . "</td>";
				if (!$cart_view_options['email']){ $return .= "<td valign=\"middle\"> " . $checkout_module_data['post_text'] . "</td>";}
				$return .= "</tr>";
				$EXPORT[$checkout_module_name]=$checkout_module_data['total'];
				$EXPORT['checkout_modules_text'] .= $checkout_module_data['checkout_itemisation_text'] . "<br />";
				$EXPORT['checkout_modules_total'] .= $this->value("default_currency_symbol") . $checkout_module_data['total']. "<br />";
			} else {
				$_SESSION['checkout_modules_add_to_total']=$checkout_module_data;
			}
			$EXPORT['misc_total'] = $this->value("default_currency_symbol") . sprintf("%4.2f",$EXPORT['misc_total'] + $checkout_module_data['total']);
		}
		$return .= "<tr><td colspan=\"5\" style=\"height:5px; border:0px;\"></td></tr>";
		if ($cart_view_options['email']){
			$EXPORT['grand_total']=$this->value("default_currency_symbol") . $_SESSION['store_grand_total_for_emails'];
		} else {
			$EXPORT['grand_total']=$this->value("default_currency_symbol") . $this->calculate_grand_total();
		}
		$return.= "<tr><td colspan=\"5\" align=\"right\"><b>Total: </b></td><td align=\"right\">" . $EXPORT['grand_total'] . "</td></tr>";

		if ($this->value("post_order_total_values")){
			$itemise_checkout_modules_post_order_total=$this->run_checkout_modules_post();








			foreach ($itemise_checkout_modules_post_order_total as $checkout_module_name=>$checkout_module_data){
				if ($checkout_module_name != "amount_to_add_to_post_total"){
					$module_text=$checkout_module_data['checkout_itemisation_text'];
					$module_text=str_replace("{=voucher_text}",$_SESSION['voucher_text'],$module_text);
					$return .= "<tr><td colspan=\"5\" align=\"right\"><b>Payment by " . $module_text .":</b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $checkout_module_data['total'] . "</td>";
					if (!$cart_view_options['email']){ $return .= "<td valign=\"middle\"> " . $checkout_module_data['post_text'] . "</td>";}
					$return .= "</tr>";
					$EXPORT[$checkout_module_name]=$checkout_module_data['total'];
					$EXPORT['checkout_modules_text'] .= $checkout_module_data['checkout_itemisation_text'] . "<br />";
					$EXPORT['checkout_modules_total'] .= $this->value("default_currency_symbol") . $checkout_module_data['total']. "<br />";
				} else {
					$_SESSION['checkout_modules_add_to_total']=$checkout_module_data;
				}
				$EXPORT['misc_total'] = $this->value("default_currency_symbol") . sprintf("%4.2f",$EXPORT['misc_total'] + $checkout_module_data['total']);
			}

		$EXPORT['final_post_total']=$this->calculate_grand_total();
		if ($EXPORT['final_post_total']<0){
			$EXPORT['final_post_total']=0;
			$_SESSION['total_for_further_payment']=0;
			$_SESSION['voucher_only_payment']=1;
		}
		$return.= "<tr><td colspan=\"5\" align=\"right\"><b>Total Outstanding: </b></td><td align=\"right\">&pound;" . $EXPORT['final_post_total'] . "</td></tr>";
		if ($EXPORT['final_post_total']<0){
			$return.= "<tr><td colspan=\"5\" align=\"right\">Please note: We cannot give change for gift vouchers.</td><td align=\"right\">&pound;" . $EXPORT['final_post_total'] . "</td></tr>";
		}





		}

		//$return .= "</table></p><p></p>";
		$return .= "</form>";
	} else if ($cart_view_options['notify_shipping_after_address']){
		//MAY 2011
		//$return .= "<tr><td colspan=\"5\">Shipping will be quoted after you have logged in or entered your address details.</td></tr>";
		//$return .= "</table></p><p></p>";
		$return .= "</form>";
	} else {
		//$return .= "</table></p><p></p>";
		$return .= "</form>";
		//$return.= "<p>If shipping is applicable this will be quoted on the next page once you have confirmed where we are shipping to.</p>";
	}

	$return_string = $cart_header;
	if ($have_products){
		$return_string .= $cart_header_row; 
		$EXPORT['cart_header_row']=$cart_header_row;
	}
	require_once(LIBPATH . "/classes/core/recorset.php");
	require_once(LIBPATH . "/classes/core/recorset_template.php");
	$rs1 = new recordset();
	$rst = new recordset_template();
	
	$EXPORT['rows']=$cart_data;
	$EXPORT['form_header']=$cart_header;
	$EXPORT['sub_total']=$this->value("default_currency_symbol") . sprintf("%4.2f",$running_total);
	$EXPORT['form_totals']= $return;
	$EXPORT['form_footer']= "</table></form>";
	if ($cart_view_options['allow_update']){
		$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_standard");
	} else if ($cart_view_options['email']){
		$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_email");
		if (!$export_template){
			$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_standard");
		}
	} else {
		$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_static");
	}
	//$export_template=$db->field_from_record_from_id("templates",$export_template,"template");
	if (!$have_products){
		$export_template=$db->db_quick_match("templates","template","dbf_key_name","view_cart_empty");
	}
	if (!$export_template){ format_error("No template found to display shopping cart data",1); }

	if ($cart_view_options['cart_update_message']){
		$EXPORT['cart_update_message'] = $cart_view_options['cart_update_message'];
	}
	$templated = $rst->rs_to_template($rs1,$export_template,$EXPORT); 
	$return_string = $templated; //return_cart_lines;
	//$return_string .= $return;
	if (array_key_exists("show_preorders",$cart_view_options) && $cart_view_options['show_preorders']==0){
		$show_preorders=0;
	} else {
		$show_preorders=1;
	}
	if ($_SESSION['preorder_cart'] && $show_preorders){
		if ($_GET['action']=="place_order" || $_GET['action']=="confirm_order"){
			$return_string .= $this->view_preorder_cart_separates($cart_view_options);
		} else {
			$return_string .= $this->view_preorder_cart($cart_view_options);
		}
	}
	if ($_SESSION['cart'] && $_SESSION['preorder_cart'] && $show_preorders){
		$return_string .= $this->print_cart_and_preorder_total($cart_view_options['run_modules']);
	}
	return $return_string;
}

function view_preorder_cart($cart_view_options){
	$preorder=1; // used to notify shipping functions
	global $db;
	$cart_template = $this->value("cart_template");
	$default_currency_symbol = $this->value("default_currency_symbol");

	$cart_header="";
	if ($cart_view_options['allow_update']){
		$cart_header = "<form name=\"preorder_cart\" method=\"post\" action=\"site.php?action=update_preorder_cart&mt=$cart_template\">\n";
	}
	$cart_header .= "<table width=\"100%\">";
	$cart_header_row = "<tr class=\"view_cart_table_header_row\">";
	$cart_header_row .= "<td colspan=\"2\">Product</td>";
	if ($this->value("allow_multiple_quantities")){
		$cart_header_row .= "<td>Quantity</td>";
	}
	$cart_header_row .= "<td>Price Each</td>";
	$cart_header_row .= "<td>Total Price</td>";
	$cart_header_row .= "<td></td>";
	$cart_header_row .= "</tr>";
	$have_products=0;
	$trbg="#f1f1f1";
	$cart_data=array();
	$cart_lines_counter=0;
	foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
		$in_cart_out_of_stock=0;
		$products_pk=get_primary_key($this->value("products_table"));
		$sql_select_list=$products_pk . ",".$this->value("product_title_fields").",".$this->price_field.",image,allow_pre_orders,release_date";
		if (is_field_in_table($this->value("products_table"),"child_product") && is_field_in_table($this->value("products_table"),"parent_product")){
			$sql_select_list .= ",child_product,parent_product";
		}
		$sql_select_list=str_replace(",,",",",$sql_select_list);
		$sql="SELECT $sql_select_list FROM " . $this->value("products_table") . " WHERE $products_pk = $item";
		if (!$item){ continue;}
		$res=$db->query($sql);
		$h=$db->fetch_array($res);	
		$return_cart_lines .= "<tr bgcolor=\"$trbg\"><td class=\"view_cart_static_text_color\">";
		$return_cart_lines .= "<a href=\"".HTTP_PATH."/product_details/".$h['ID']."\" style=\"color:#222\">" . $this->get_product_title($h). "</a>"; 
		$cart_lines_counter=$h[$products_pk];
		$cart_data[$cart_lines_counter]['primary_key'] = $h[$products_pk];
		$cart_data[$cart_lines_counter]['title'] = $this->get_product_title($h);
		$cart_data[$cart_lines_counter]['image'] = $h['image'];
		if (!$h['image'] && $h['child_product']){
			$master_image_sql="SELECT image FROM products WHERE id = " . $h['parent_product'];
			$master_image_rv=$db->query($master_image_sql);
			$master_h=$db->fetch_array($master_image_rv);
			if ($master_h['image']){
				$cart_data[$cart_lines_counter]['image'] = $master_h['image'];
			}
		}

		/* no need to check stock quantities on preorders 
		$qty_in_stock=$this->check_stock_quantity($item);
		if (($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown") && !$cart_view_options['email']){
			if (!$this->value("email_when_back_in_stock")){
				$set_out_of_stock_sql="UPDATE products set stock_quantity=0,available=0 where ID = " . $h['ID'];
				$out_of_stock_rv=$db->query($set_out_of_stock_sql);
				$in_cart_out_of_stock=1;
				$h[$this->value("price_field")]=0.00;
			}
			unset($_SESSION['preorder_cart'][$h['ID']]);
			$h[$this->value("price_field")]=0.00;
		}
		*/
		$cart_data[$cart_lines_counter]['attributes'] = $this->view_cart_print_product_attributes($item);
		$cart_data[$cart_lines_counter]['unit_price'] = $default_currency_symbol . $h[$this->value("price_field")];
		$cart_data[$cart_lines_counter]['line_price'] = $default_currency_symbol . sprintf("%4.2f",$h[$this->value("price_field")] * $_SESSION['preorder_cart'][$item]['quantity']);

		if ($_SESSION['preorder_cart'][$item]['attributes']){
			$cart_data[$cart_lines_counter]['attributes']=$this->view_cart_print_product_attributes($item);
			$return_cart_lines .= $this->view_cart_print_product_attributes($item);
		}

		//if ($h['allow_pre_orders']){
			$due_in_date=$h['release_date'];
			$cart_data[$cart_lines_counter]['stock_alert'] = "<br /><br /><span style=\"color:#000066\">Due In: $due_in_date</span><br />";	
		//}
		/* No need to do stock checking on pre-orders
		if ($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown"){
			$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - This product is Out Of Stock and has been removed from your shopping cart.</span><br />";
			if ($this->value("email_when_back_in_stock")){
				$cart_data[$cart_lines_counter]['stock_alert'] .= "<a href=\"site.php?action=add_product_to_email_list&amp;product=".$item."&mt=31\" rel=\"#add_to_email_list_overlay\">Email me when this item is back in stock</a>";
			}
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else if ($qty_in_stock < $_SESSION['preorder_cart'][$item]['quantity']){
			if (!$qty_in_stock || $qty_in_stock=""){
				$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - This product is Out Of Stock.</span><br />";
			} else {
				$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - only '$qty_in_stock' of these are in stock.<br />Please adjust your order quantity.</span><br />";
			}
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else {
			//$return .= "<br>$qty_in_stock in stock.</br>";
		}
		*/
		if ($this->value("allow_multiple_quantities")){
			$line_cost=$h[$this->value("price_field")]*$_SESSION['preorder_cart'][$item]['quantity'];
		} else {
			$line_cost=$h[$this->value("price_field")];
		}
		$running_total = $running_total + $line_cost;
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td>";
		$return_cart_lines .= $h[$this->value("price_field")];
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">";
		if ($cart_view_options['allow_update']){
			$return_cart_lines .= "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['preorder_cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\">";	
			$cart_data[$cart_lines_counter]['quantity'] = "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['preorder_cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\">";
		} else {
			$return_cart_lines .= $_SESSION['preorder_cart'][$item]['quantity'];
			$cart_data[$cart_lines_counter]['quantity'] = $_SESSION['preorder_cart'][$item]['quantity'];
		}
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">".$this->value("default_currency_symbol").sprintf("%4.2f",$line_cost)."</td>";
		if ($cart_view_options['allow_update']){
		$return_cart_lines .= "<td><a href=\"site.php?action=cart_remove&mt=$cart_template&cart_product_id=".$h['ID']."\" class=\"remove_button\" style=\"color:#26abd5\">Remove</a></td>";
		$return_cart_lines .= "</tr>";
		}
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
		$have_products++;
	}

        if ($have_products && $this->value("allow_multiple_quantities") && $cart_view_options['allow_update']){
//                $return .= "<tr><td></td><td></td><td><a href=\"Javascript:document.forms['cart'].submit()\" class=\"order_button\" style=\"text-align:center\">Update</a></td><td></td><td class=\"inline_cart_total\">".$this->value("default_currency_symbol").sprintf("%4.2f",$running_total)."</td><td></td></tr>";
	} else if (!$have_products){
                $return .= "<tr><td colspan=\"5\">There are currently no items in your order.</td></tr>";
        }

	$return .= "<tr><td colspan=\"4\" style=\"height:5px\"></td></tr>";
	if (!$this->value("allow_multiple_quantities") || !$cart_view_options['allow_update']){
		//$return .= "<tr><td colspan=\"3\" align=\"right\"><b>Sub Total:</b> </td><td>" . $this->value("default_currency_symbol") . sprintf("%4.2f",$running_total) . "</td></tr>\n";
	}
	$return .= "<tr><td colspan=\"4\" style=\"height:5px\"></td></tr>";
	$_SESSION['preorder_total_price']=sprintf("%4.2f",$running_total);

	//if ($this->buy_requires_login && $cart_view_options['run_modules'])
	if ($cart_view_options['run_modules']){
                //$return .= "<tr><td></td><td></td><td></td><td></td><td class=\"inline_cart_total\">".$this->value("default_currency_symbol").sprintf("%4.2f",$running_total)."</td><td></td></tr>";
		$return .= "<tr><td colspan=\"4\" align=\"right\"><b>Shipping: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_shipping($preorder) . "</td></tr>"; 
		$EXPORT['shipping_total']=$this->value("default_currency_symbol") . $this->calculate_shipping($preorder);
		print "<!-- calc is " . $this->calculate_shipping() . "//-->";
		$_SESSION['preorder_shipping_amount']=$this->value("shipping_total");
		$itemise_checkout_modules=$this->run_checkout_modules();
		$EXPORT['misc_total']=$this->value("default_currency_sumbol") . "0";
		$EXPORT['checkout_modules_text']="";
		$EXPORT['checkout_modules_total']="";
		foreach ($itemise_checkout_modules as $checkout_module_name=>$checkout_module_data){
			if ($checkout_module_name != "amount_to_add_to_total"){
				$module_text=$checkout_module_data['checkout_itemisation_text'];
				$module_text=str_replace("{=voucher_text}",$_SESSION['preorder_voucher_text'],$module_text);
				$return .= "<tr><td colspan=\"4\" align=\"right\"><b>" . $module_text .":</b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $checkout_module_data['total'] . "</td></tr>";
				$EXPORT[$checkout_module_name]=$checkout_module_data['total'];
				$EXPORT['checkout_modules_text'] .= $checkout_module_data['checkout_itemisation_text'] . "<br />";
				$EXPORT['checkout_modules_total'] .= $this->value("default_currency_symbol") . $checkout_module_data['total']. "<br />";
			} else {
				$_SESSION['preorder_checkout_modules_add_to_total']=$checkout_module_data;
			}
			$EXPORT['misc_total'] = $this->value("default_currency_symbol") . sprintf("%4.2f",$EXPORT['misc_total'] + $checkout_module_data['total']);
		}
		$return .= "<tr><td colspan=\"4\" style=\"height:5px\"></td></tr>";
		$return.= "<tr><td colspan=\"4\" align=\"right\"><b>Total: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</td></tr>";
		$EXPORT['grand_total']=$this->value("default_currency_symbol") . $this->calculate_grand_total();
		$return .= "</table></p><p></p>";
		$return .= "</form>";
	} else if ($cart_view_options['notify_shipping_after_address']){
		$return .= "<tr><td colspan=\"5\">Shipping will be quoted after you have logged in or entered your address details.</td></tr>";
		$return .= "</table></p><p></p>";
		$return .= "</form>";
	} else {
		$return .= "</table></p><p></p>";
		$return .= "</form>";
		//$return.= "<p>If shipping is applicable this will be quoted on the next page once you have confirmed where we are shipping to.</p>";
	}

	$return_string = $cart_header;
	if ($have_products){
		$return_string .= $cart_header_row; 
		$EXPORT['cart_header_row']=$cart_header_row;
	}
	require_once(LIBPATH . "/classes/core/recorset.php");
	require_once(LIBPATH . "/classes/core/recorset_template.php");
	$rs1 = new recordset();
	$rst = new recordset_template();
	
	$EXPORT['rows']=$cart_data;
	$EXPORT['form_header']=$cart_header;
	$EXPORT['sub_total']=$this->value("default_currency_symbol") . sprintf("%4.2f",$running_total);
	$EXPORT['form_totals']= $return;
	$EXPORT['form_footer']= "</table></form>";
	if ($cart_view_options['allow_update']){
		$export_template=$db->db_quick_match("templates","template","dbf_key_name","preorder_cart_view_standard");
	} else if ($cart_view_options['email']){
		$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_email");
		if (!$export_template){
			$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_standard");
		}
	} else {
		$export_template=$db->db_quick_match("templates","template","dbf_key_name","preorder_cart_view_static");
	}
	//$export_template=$db->field_from_record_from_id("templates",$export_template,"template");
	if (!$have_products){
		// no need to notify of no preorders
		//$export_template=$db->db_quick_match("templates","template","dbf_key_name","view_cart_empty");
	}
	if (!$export_template){ format_error("No template found to display shopping cart data",1); }

	if ($cart_view_options['cart_update_message']){
		$EXPORT['cart_update_message'] = $cart_view_options['cart_update_message'];
	}
	$templated = $rst->rs_to_template($rs1,$export_template,$EXPORT); 
	$return_string = $templated; //return_cart_lines;
	//$return_string .= $return;
	return $return_string;
}

function view_preorder_cart_separates($cart_view_options){
	$preorder=1; // used to notify shipping functions
	global $db;
	$cart_template = $this->value("cart_template");
	$default_currency_symbol = $this->value("default_currency_symbol");

	$running_total=0; // stores the value of all pre orders as a running total
	$templated="";
	foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
		if ($cart_view_options['order_number']){
			if ($itemdata['order_number'] != $cart_view_options['order_number']){
				continue;
			}
		}
		$return="";
		$preorder_running_total=0; // running total for each preorder inc shipping etc.
		$cart_header="";
		if ($cart_view_options['allow_update']){
			$cart_header = "<form name=\"cart\" method=\"post\" action=\"site.php?action=update_cart&mt=$cart_template\">\n";
		}
		$cart_header .= "<table width=\"100%\">";
		$cart_header_row = "<tr class=\"view_cart_table_header_row\">";
		$cart_header_row .= "<td colspan=\"2\">Product</td>";
		if ($this->value("allow_multiple_quantities")){
			$cart_header_row .= "<td>Quantity</td>";
		}
		$cart_header_row .= "<td>Price Each</td>";
		$cart_header_row .= "<td>Total Price</td>";
		$cart_header_row .= "<td></td>";
		$cart_header_row .= "</tr>";
		$have_products=0;
		$trbg="#f1f1f1";
		$cart_data=array();
		$cart_lines_counter=0;
		$in_cart_out_of_stock=0;
		$products_pk=get_primary_key($this->value("products_table"));
		$sql_select_list=$products_pk . ",".$this->value("product_title_fields").",".$this->price_field.",image,allow_pre_orders,release_date";
		if (is_field_in_table($this->value("products_table"),"child_product") && is_field_in_table($this->value("products_table"),"parent_product")){
			$sql_select_list .= ",child_product,parent_product";
		}
		$sql_select_list=str_replace(",,",",",$sql_select_list);
		$sql="SELECT $sql_select_list FROM " . $this->value("products_table") . " WHERE $products_pk = $item";
		if (!$item){ continue;}
		$res=$db->query($sql);
		$h=$db->fetch_array($res);	
		$return_cart_lines .= "<tr bgcolor=\"$trbg\"><td class=\"view_cart_static_text_color\">";
		$return_cart_lines .= "<a href=\"".HTTP_PATH."/product_details/".$h['ID']."\" style=\"color:#222\">" . $this->get_product_title($h). "</a>"; 
		$cart_lines_counter=$h[$products_pk];
		$cart_data[$cart_lines_counter]['primary_key'] = $h[$products_pk];
		$cart_data[$cart_lines_counter]['title'] = $this->get_product_title($h);
		$cart_data[$cart_lines_counter]['image'] = $h['image'];
		if (!$h['image'] && $h['child_product']){
			$master_image_sql="SELECT image FROM products WHERE id = " . $h['parent_product'];
			$master_image_rv=$db->query($master_image_sql);
			$master_h=$db->fetch_array($master_image_rv);
			if ($master_h['image']){
				$cart_data[$cart_lines_counter]['image'] = $master_h['image'];
			}
		}

		$qty_in_stock=$this->check_stock_quantity($item);
		if (($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown") && !$cart_view_options['email'] && !$h['allow_pre_orders']){
			if (!$this->value("email_when_back_in_stock")){
				$set_out_of_stock_sql="UPDATE products set stock_quantity=0,available=0 where ID = " . $h['ID'];
				$out_of_stock_rv=$db->query($set_out_of_stock_sql);
				$in_cart_out_of_stock=1;
				$h[$this->value("price_field")]=0.00;
			}
			unset($_SESSION['preorder_cart'][$h['ID']]);
			$h[$this->value("price_field")]=0.00;
		}

		$cart_data[$cart_lines_counter]['attributes'] = $this->view_cart_print_product_attributes($item);
		$cart_data[$cart_lines_counter]['unit_price'] = $this->value("default_currency_symbol") . $h[$this->value("price_field")];
		$cart_data[$cart_lines_counter]['line_price'] = $this->value("default_currency_symbol") . sprintf("%4.2f",$h[$this->value("price_field")] * $_SESSION['preorder_cart'][$item]['quantity']);

		if ($_SESSION['preorder_cart'][$item]['attributes']){
			$cart_data[$cart_lines_counter]['attributes']=$this->view_cart_print_product_attributes($item);
			$return_cart_lines .= $this->view_cart_print_product_attributes($item);
		}

		if ($h['allow_pre_orders']){
			$due_in_date=$h['release_date'];
			$cart_data[$cart_lines_counter]['stock_alert'] = "<br /><br /><span style=\"color:#000066\">Due In: $due_in_date</span><br />";	
		}
		if (($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown") && !$h['allow_pre_orders']){
			$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - This product is Out Of Stock and has been removed from your shopping cart.</span><br />";
			if ($this->value("email_when_back_in_stock")){
				$cart_data[$cart_lines_counter]['stock_alert'] .= "<a href=\"site.php?action=add_product_to_email_list&amp;product=".$item."&mt=31\" rel=\"#add_to_email_list_overlay\">Email me when this item is back in stock</a>";
			}
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else if ($qty_in_stock < $_SESSION['preorder_cart'][$item]['quantity'] && !$h['allow_pre_orders']){
			if (!$qty_in_stock || $qty_in_stock=""){
				$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - This product is Out Of Stock.</span><br />";
			} else {
				$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - only '$qty_in_stock' of these are in stock.<br />Please adjust your order quantity.</span><br />";
			}
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else {
			//$return .= "<br>$qty_in_stock in stock.</br>";
		}
		if ($this->value("allow_multiple_quantities")){
			$line_cost=$h[$this->value("price_field")]*$_SESSION['preorder_cart'][$item]['quantity'];
		} else {
			$line_cost=$h[$this->value("price_field")];
		}
		$running_total = $running_total + $line_cost; // addto total of all preorders
		$preorder_running_total=$preorder_running_total +$line_cost; // add to total of each preorder
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td>";
		$return_cart_lines .= $h[$this->value("price_field")];
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">";
		if ($cart_view_options['allow_update']){
			$return_cart_lines .= "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['preorder_cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\">";	
				$cart_data[$cart_lines_counter]['quantity'] = "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['preorder_cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\">";
		} else {
			$return_cart_lines .= $_SESSION['preorder_cart'][$item]['quantity'];
			$cart_data[$cart_lines_counter]['quantity'] = $_SESSION['preorder_cart'][$item]['quantity'];
		}
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">".$this->value("default_currency_symbol").sprintf("%4.2f",$line_cost)."</td>";
		if ($cart_view_options['allow_update']){
		$return_cart_lines .= "<td><a href=\"site.php?action=cart_remove&mt=$cart_template&cart_product_id=".$h['ID']."\" class=\"remove_button\" style=\"color:#26abd5\">Remove</a></td>";
		$return_cart_lines .= "</tr>";
		}
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}

		$return .= "<tr><td colspan=\"4\" style=\"height:5px\"></td></tr>";
		$return .= "<tr><td colspan=\"4\" style=\"height:5px\"></td></tr>";
		$_SESSION['preorder_total_price']=sprintf("%4.2f",$running_total);
		$_SESSION['preorder_total_price_each']=sprintf("%4.2f",$preorder_running_total);
		//$_SESSION['preorder_total_price']= sprintf("%4.2f",($_SESSION['preorder_total_price'] + $line_cost));
		//if ($this->buy_requires_login && $cart_view_options['run_modules'])
		if ($cart_view_options['run_modules']){
			//$return .= "<tr><td></td><td></td><td></td><td></td><td class=\"inline_cart_total\">".$this->value("default_currency_symbol").sprintf("%4.2f",$running_total)."</td><td></td></tr>";
			$preorder=$item;
			$item_shipping_amt=$this->calculate_shipping($preorder);
			$_SESSION['preorder_cart'][$item]['preorder_shipping_amount']=$item_shipping_amt;
			$this->set_value("all_preorders_shipping_total",$this->value("all_preorders_shipping_total") + $item_shipping_amt);
			$return .= "<tr><td colspan=\"4\" align=\"right\"><b>Shipping: </b></td><td align=\"right\">" . $item_shipping_amt . "</td></tr>"; 
			$EXPORT['shipping_total']=$this->value("default_currency_symbol") . $item_shipping_amt;
			$_SESSION['preorder_total_price_each']=sprintf("%4.2f",$_SESSION['preorder_total_price_each']+$item_shipping_amt);
			$_SESSION['preorder_shipping_amount']=$this->value("shipping_total");
			$itemise_checkout_modules=$this->run_checkout_modules();
			$EXPORT['misc_total']=$this->value("default_currency_sumbol") . "0";
			$EXPORT['checkout_modules_text']="";
			$EXPORT['checkout_modules_total']="";
			foreach ($itemise_checkout_modules as $checkout_module_name=>$checkout_module_data){
				if ($checkout_module_name != "amount_to_add_to_total"){
					$module_text=$checkout_module_data['checkout_itemisation_text'];
					$module_text=str_replace("{=voucher_text}",$_SESSION['preorder_voucher_text'],$module_text);
					$return .= "<tr><td colspan=\"4\" align=\"right\"><b>" . $module_text .":</b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $checkout_module_data['total'] . "</td></tr>";
					$EXPORT[$checkout_module_name]=$checkout_module_data['total'];
					$EXPORT['checkout_modules_text'] .= $checkout_module_data['checkout_itemisation_text'] . "<br />";
					$EXPORT['checkout_modules_total'] .= $this->value("default_currency_symbol") . $checkout_module_data['total']. "<br />";
				} else {
					$_SESSION['preorder_checkout_modules_add_to_total']=$checkout_module_data;
				}
				$EXPORT['misc_total'] = $this->value("default_currency_symbol") . sprintf("%4.2f",$EXPORT['misc_total'] + $checkout_module_data['total']);
			}
			$return .= "<tr><td colspan=\"4\" style=\"height:5px\"></td></tr>";
			$EXPORT['grand_total']=$this->value("default_currency_symbol") . $this->calculate_grand_total_preorder_each($item_shipping_amount);
			$return.= "<tr><td colspan=\"4\" align=\"right\"><b>Total: </b></td><td align=\"right\">".$EXPORT['grand_total']."</td></tr>";
			// now store correct session values for preorder
			$EXPORT['all_preorders_grand_total']=$this->calculate_grand_total_preorder();
			$_SESSION['all_preorders_grand_total']=$EXPORT['all_preorders_grand_total'];
			$return .= "</table></p><p></p>";
			$return .= "</form>";
		} else if ($cart_view_options['notify_shipping_after_address']){
			$return .= "<tr><td colspan=\"5\">Shipping will be quoted after you have logged in or entered your address details.</td></tr>";
			$return .= "</table></p><p></p>";
			$return .= "</form>";
		} else {
			$return .= "</table></p><p></p>";
			$return .= "</form>";
			//$return.= "<p>If shipping is applicable this will be quoted on the next page once you have confirmed where we are shipping to.</p>";
		}

		$return_string = $cart_header;
		if ($have_products){
			$return_string .= $cart_header_row; 
			$EXPORT['cart_header_row']=$cart_header_row;
		}
		require_once(LIBPATH . "/classes/core/recorset.php");
		require_once(LIBPATH . "/classes/core/recorset_template.php");
		$rs1 = new recordset();
		$rst = new recordset_template();
		
		$EXPORT['rows']=$cart_data;
		$EXPORT['form_header']=$cart_header;
		$EXPORT['sub_total']=$this->value("default_currency_symbol") . sprintf("%4.2f",$line_cost);
		$EXPORT['form_totals']= $return;
		$EXPORT['form_footer']= "</table></form>";
		if ($cart_view_options['allow_update']){
			$export_template=$db->db_quick_match("templates","template","dbf_key_name","preorder_cart_view_standard");
		} else if ($cart_view_options['email']){
			$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_email");
			if (!$export_template){
				$export_template=$db->db_quick_match("templates","template","dbf_key_name","cart_view_standard");
			}
		} else {
			$export_template=$db->db_quick_match("templates","template","dbf_key_name","preorder_cart_view_static");
		}
		//$export_template=$db->field_from_record_from_id("templates",$export_template,"template");
		if (!$have_products){
			// no need to notify of no preorders
			//$export_template=$db->db_quick_match("templates","template","dbf_key_name","view_cart_empty");
		}
		if (!$export_template){ format_error("No template found to display shopping cart data",1); }

		if ($cart_view_options['cart_update_message']){
			$EXPORT['cart_update_message'] = $cart_view_options['cart_update_message'];
		}
		$templated .= $rst->rs_to_template($rs1,$export_template,$EXPORT); 

	}

	$return_string = $templated; //return_cart_lines;
	//$return_string .= $return;
	return $return_string;
}

function print_cart_and_preorder_total($run_modules){
	$_SESSION['total_of_all_orders']=$_SESSION['total_price'] + $_SESSION['preorder_total_price'];
	$_SESSION['total_of_all_orders_inc']=$_SESSION['grand_total']+ $_SESSION['preorder_grand_total'];
	if ($run_modules){
		$print_total=$_SESSION['total_of_all_orders_inc'];
		$cart_total_msg="The total that you will be billed at the checkout is ";
		$cart_total_msg="Grand Total: ";
		$shipping_note="";
		global $user;
		if ($this->value("buy_requirs_login")==1 && !$user->value("id")){
			$cart_total_msg="Total amount of all orders: ";
			$shipping_note="<br />Shipping (if applicable) will be applied at the checkout.";
		}
	} else {
		// IF run_modules but no user, need to add the shipping value as well - look at buy_requires_login too
		$print_total=$_SESSION['total_of_all_orders'];
		$cart_total_msg="Total amount of all orders: ";
		$shipping_note="<br />Shipping (if applicable) will be applied at the checkout.";
	}
	// pre orders as well?
		//$content .= "<p>Your main order and pre-orders will be billed in one transaction. You will receive a separate order number and confirmation email for your main order and each pre-order.</p>"; 
		$content .= "<p>$cart_total_msg<strong> " . $this->value("default_currency_symbol") . $print_total . "</strong>.$shipping_note</p>";
	return $content;
}

function get_product_title($title_fields){
	global $db;
	$product_title_fields_array=explode(",",$this->value("product_title_fields"));
	$title_fields_template=$this->value("product_title_fields_template");
	foreach ($product_title_fields_array as $product_title_field){
		if ($product_title_field=="artist"){
			$title_field_value = $db->field_from_record_from_id("artists",$title_fields[$product_title_field],"artist");
		} else if ($product_title_field=="format") {
			$title_field_value = $db->field_from_record_from_id("product_formats",$title_fields[$product_title_field],"format");
		} else {
			$title_field_value = $title_fields[$product_title_field] . " ";
		}
		$title_fields_template=preg_replace("/{=$product_title_field}/",$title_field_value,$title_fields_template);
	}
	$return .= $this->format_product_title($title_fields_template);
	return $return;
}

function format_product_title($title){
	$title = preg_replace("/(\d+( |k)?)g$/i","<span style=\"font-variant:normal;\">$1g</span>",$title);
	return $title;
}


function print_cart_for_email(){
	global $db;
	$default_currency_symbol = $this->value("default_currency_symbol");
	$return="";
	$return .= "<p><table style=\"margin-left:15px\"><tr style=\"background-color:#222; color:#eee; font-weight:bold;\"><td>Item</td>";
	if ($this->value("allow_multiple_quantities")){
		$return .= "<td>Quantity</td>";
	}
	$return .= "<td>Price</td></tr>";
	$trbg="#f1f1f1";
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$products_pk=get_primary_key("products");
		$sql_select_list=$products_pk . ",".$this->value("product_title_fields").",".$this->value("price_field");
		$sql="SELECT $sql_select_list FROM " . $this->value("products_table") . " WHERE $products_pk = $item";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);
		$return .= "<tr bgcolor=\"$trbg\"><td>";
		$return .= $this->get_product_title($h);

		if ($_SESSION['cart'][$item]['attributes']){
			$return .= "<br /><a href=\"Javascript:showDiv('attributediv')\" class=\"product_attributes_view_link\">Click here to view product attributes:</a>";
			$return .= "<div id=\"attributediv\" style=\"display:none; font-size:12px;\">";
			foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
				$return .= "<b>" . $attributename . ":</b> " . $attributevalue . "<br />";
			}
			$return .= "<a href=\"Javascript:hideDiv('attributediv')\" class=\"product_attributes_hide_link\">Hide attributes</a><br /></div>";
		}
		$line_cost=$h[$this->value("price_field")];
		if (!$this->value("allow_multiple_quantities")){
			$running_total = $running_total + $line_cost;
			$preorder_running_total=$preorder_running_total + $line_cost;
		} else {
			$running_total = $running_total + ($line_cost*$_SESSION['cart'][$item]['quantity']);
			$preorder_running_total = $preorder_running_total + ($line_cost*$_SESSION['cart'][$item]['quantity']);
		}
		$return .= "</td><td style=\"color:#333333\">" . $_SESSION['cart'][$item]['quantity']. "</td><td style=\"color:#333333\">$default_currency_symbol " . $line_cost*$_SESSION['cart'][$item]['quantity'] . "</td></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}
	$return .= "</table></p><p></p>";
	$return .= "<p><b>Order Total:</b> $default_currency_symbol " . sprintf("%4.2f",$running_total) . "</p>\n";
	return $return;
}

function confirm_order($account_code_validated){

	global $user;
	global $db;
	if (!$user){

	}
	if ($user->value("id")){
		// we have a user, we should be able to work out the delivery post code
		$sql="SELECT zip_or_postal_code,delivery_zip_or_postal_code, same_as_billing_address FROM user WHERE id = " . $user->value("id");
		$rv1=$db->query($sql);
		$h1=$db->fetch_array();
		if ($h1['same_as_billing_address']){
			$_SESSION['delivery_postcode']=$h1['zip_or_postal_code'];
		} else {
			$_SESSION['delivery_postcode']=$h1['delivery_zip_or_postal_code'];
		}
		
	}

	// 1 get shppping cart data to print as content
	$cart_template=$this->value("cart_template");
	$golden_account_active=$this->value("golden_account_active");
	if (!$_SESSION['cart'] && !$_SESSION['preorder_cart']){
		$widget_template=$db->db_quick_match("widgets","widget","dbf_key_name","shopping_cart_empty");
		if ($widget_template){
			$content=$widget_template;
		} else{
			$content="<p>You don't currently have any items in your shopping cart.</p>";
		}
		return $content;
	}
	$cart_data_hash=array("allow_update" => "0", "run_modules" => "1");
	if (!$this->value("buy_requires_login") || ($this->value("buy_requires_login")==2 && !$user->value("id") )){
		$cart_data_hash['run_modules']=0;
		$cart_data_hash['notify_shipping_after_address']=1;
	}
	$checkout_template['view_cart'] = $this->view_cart_general($cart_data_hash);
	$content .= $checkout_template['view_cart'];
	
	// 2 look at alternative shippings
	$checkout_template['shipping_options']="";
	if ($_SESSION['shipping_message']){
		$checkout_template['shipping_options'] .= "<p>".$_SESSION['shipping_message']."</p>";
	}
	$all_shipping_modules=explode(",",$this->value("shipping_modules_installed"));
	if (count($all_shipping_modules)>1 && ($this->value("buy_requires_login") && $user->value("id"))){
		$checkout_template['shipping_options'] .= $this->print_shipping_options_form();
		$content .= $checkout_template['shipping_options'];
	} else if (count($all_shipping_modules)==1){
		global $libpath;
		$shipping_module_file=$libpath."/classes/shopping_cart/shipping_modules/".$all_shipping_modules[0].".php";
		if (file_exists($shipping_module_file)){
			include_once($shipping_module_file);
			$set_up_new_object_code="\$delivery = new " . $all_shipping_modules[0]. ";";
			$obj_result=eval($set_up_new_object_code);
			$sdo=$delivery->shipping_delivery_options();
			$checkout_template['shipping_options'] .= $sdo;
		}
	}

	// page modules (gift vouchers) 
	$vouchers_active=$db->db_quick_match("checkout_modules","active","key_name","gift_vouchers_complex");
	if ($vouchers_active && !$_SESSION['voucher_text'] && ($this->value("buy_requires_login") && $user->value("id"))){
		$voucher_code_form = $db->db_quick_match("templates","template","dbf_key_name","voucher_code_form");
		$voucher_code_form=str_replace("{=cart_template}",$cart_template,$voucher_code_form);
		$checkout_template['gift_vouchers']=$voucher_code_form;
		$content .= $checkout_template['gift_vouchers'];
	}

	// 4 print user details or cart submit form depending on if login is required
	$checkout_template['user_details']="";
	if (!$this->value("buy_requires_login")){
		$checkout_template['user_details'] .= "<p>Shipping (if applicable) will be quoted on the next page after you have filled in a delivery address.</p>";
		//$checkout_template['user_details'] .= $this->print_user_details_form(); // MATTPLATTS2013
		$_SESSION['checkout_without_login']=1;
	} else if ($this->value("buy_requires_login")==1){
		$checkout_template['user_details'] .= $this->print_cart_submit_form();
		$_SESSION['checkout_without_login']=0;
	} else if ($this->value("buy_requires_login")==2 && $user->value("id")){
		$checkout_template['user_details'] .= $this->print_cart_submit_form();
		$_SESSION['checkout_without_login']=0;
	} else {
		$_SESSION['checkout_without_login']=0;
		// the double option
	}



	if ((!$_COOKIE['login'] || !$user->value("id")) && $this->value("buy_requires_login")==1){
		$checkout_login_prompt = $db->db_quick_match("widgets","widget","dbf_key_name","checkout_login_prompt_inline");
		if ($checkout_login_prompt){
			$checkout_template['user_details'] .= $checkout_login_prompt;
		} else {
			$checkout_template['user_details'] .= '<p>Please <a href="/log_in.html">Log In</a> or <a href="/register.html">Register</a> to complete your order.</p>';
		}
	} else if (($this->value("buy_requires_login")==2 || $this->value("buy_requires_login")==0) && (!$_COOKIE['login'] || !$user->value("id"))){ // NB a problem was found with a cookie set but not the user id
		$_SESSION['checkout_without_login']=1;
		if ($this->value("buy_requires_login")==0){
			$checkout_template['user_details'] .= $db->db_quick_match("templates","template","dbf_key_name","checkout_intro_no_login");
		} else {
			$checkout_template['user_details'] .= $db->db_quick_match("templates","template","dbf_key_name","checkout_intro_optional_login");
		}
		$checkout_template['user_details'] .= $this->print_user_details_form();
		if ($this->value("approve_terms")){
			$checkout_template['terms_and_conditions'] = $db->db_quick_match("templates","template","dbf_key_name","checkout_terms_and_conditions");
		}
		// payment modules
		$installed_payment_modules=$this->payment_modules_installed;
		$installed_payment_modules=str_replace(",","','",$installed_payment_modules);
		global $db;
		$sql="SELECT id,checkout_option_text,checkout_option_text_extra,restricted_to_user_types from payment_modules WHERE key_name IN ('$installed_payment_modules') AND active=1 ORDER BY order_on_checkout_page ASC";
		$res=$db->query($sql);
		// loop and check inclusion
		$total_payment_modules=0;
		while ($h=$db->fetch_array($res)){
			if ($h['restricted_to_user_types']){
				$compare=",".$h['restricted_to_user_types'].",";
				if (!stristr($compare,$user->value("type"))){
					continue;
				}
			}
			$total_payment_modules++; // its ok
		}
		//$total_payment_modules=$db->num_rows($res);
		$checkout_template['payment_method'] = "<div class=\"shopping_cart_block\">";
		if ($total_payment_modules==1){
			$checkout_template['payment_method'].= "<h3 class=\"shopping_cart_section_header\">Payment method:</h3><div id=\"checkout_options\">\n";
		} else {
			$checkout_template['payment_method'] .= "<h3 class=\"shopping_cart_section_header\">Please select a payment method:</h3><div id=\"checkout_options\">\n";
		}
		$res=$db->query($sql);
		while ($h=$db->fetch_array($res)){
			if ($h['restricted_to_user_types']){
				$compare=",".$h['restricted_to_user_types'].",";
				if (!stristr($compare,$user->value("type"))){
					continue;
				}
			}
			$checkout_template['payment_method'] .= "<div id=\"checkout_option_div\"><span class=\"checkout_option\"><span class=\"checkout_option_text\"><input style=\"float:left\" type=\"radio\" name=\"payment_method\" id=\"payment_method\" value=\"".$h['id']."\"";
			if ($total_payment_modules==1){ $checkout_template['payment_method'] .= " checked"; }
			$checkout_template['paymen_method'] .= ">".$h['checkout_option_text'] . "</span>";
			if ($total_payment_modules==1){ $checkout_template['payment_method'] .= "<span class=\"checkout_option_text_only_method\">(This is the only payment method available.)</span>"; }
			if ($h['checkout_option_text_extra']){ $checkout_template['payment_method'] .= "<span class=\"checkout_option_text_extra\">".$h['checkout_option_text_extra'] . "</span>"; }
			$checkout_template['payment_method'] ."</span><br />";
			$checkout_template['payment_method'] .= "</div>";
			}
			$checkout_template['payment_method'] .= "</div>";
			$checkout_template['payment_method'].= "</div>";
			// end payment modules

			$checkout_template['payment_method'] .= "<br clear=\"all\" />";
			$checkout_template['payment_method'] .= "<hr size=\"1\" style=\"width:100%\" /><br clear=\"all\">";
			$cart_form_action="Javascript:checkUserDetails()";
			$checkout_template['submit'] .= "<div class=\"shopping_cart_block continue_to_payment_block\"><p><span class=\"jc_button jc_button_160\"><a href=\"$cart_form_action\" style=\"font-weight:bold\">Continue to payment</a> &nbsp; </span></p><br clear=\"all\" /><!--<p>Click above to continue to the payment pages.</p>//--></div>";
	} else {

		// golden account codes
		if (!$account_code_validated && $golden_account_active){
			$checkout_template['golden_account'] = "<div class=\"shopping_cart_block\"><p style=\"background-color:#444444\">If you have a golden account, please enter your account number in the box below:";
			$checkout_template['golden_account'] .= "<table width=\"100%\" style=\"background-color:#444444\"><tr><td width=30><form style=\"display:inline; margin:0px; margin-left:30px; padding:0px;\" method=\"post\" action=\"site.php?action=enter_golden_account_code&amp;mt=$cart_template\">Account&nbsp;No:</td><td><input type=\"text\" name=\"promotional_code\"> <input type=\"submit\" value=\"Submit Code\"></form></td></tr></table><br /></p></div>";
		// end golden promotional code
		} else if ($account_code_validated && $golden_account_active){
				$checkout_template['golden_account'] .= "<p><span class=\"order_button\" style=\"float:left;\"><a href=\"Javascript:checkUserDetails()\">Continue To Retrieve Your Download URLs</a> &nbsp; </span></p>";
		}
		$content .= $checkout_template['golden_account'];

		if (($golden_account_active && !$account_code_validated) || !$golden_account_active){
			if ($this->value("buy_requires_login")){
				$checkout_template['user_details'] .= "<hr size=\"1\">\n";
				$checkout_template['user_details'] .= $this->confirm_personal_details();
			}
			$checkout_template['user_details'] .= "<br clear=\"all\"><br /><hr size=\"1\" style=\"clear:both; margin-top:22px; padding-top:0px; \">\n";
			if (!$this->value("no_delivery_address") && !$this->value("no_billing_address")){
				if ($_SESSION['total_for_further_payment']===0){
					$cart_form_action="Javascript:no_check_payment_method()";
				}
				if (!$this->value("buy_requires_login")){
					$cart_form_action="Javascript:checkUserDetails()";
				}

				// Check US billing state:
				global $db;
				$check_user_sql="SELECT country,us_billing_state,same_as_billing_address,us_delivery_state FROM user WHERE id = " . $user->value("id");
				$check_user_res=$db->query($check_user_sql);
				$user_h=$db->fetch_array($check_user_res);

				if ($user_h['country']==183 && !$user_h['us_billing_state']){
					$checkout_template['user_details'] .= "<p class=\"dbf_para_alert\"><span style=\"color:#1b2c67\">The US State code must be entered into your address details in order to pass our credit card verification checks for US customers. <a href=\"checkout_edit_addresses.html\" style=\"font-weight:bold\"> Please click here to update your user details before continuing. Thank you.</a></span></p>";
				} else {

					// payment modules
					$installed_payment_modules=$this->payment_modules_installed;
					$installed_payment_modules=str_replace(",","','",$installed_payment_modules);
					global $db;
					$sql="SELECT id,checkout_option_text,checkout_option_text_extra,restricted_to_user_types from payment_modules WHERE key_name IN ('$installed_payment_modules') AND active=1 ORDER BY order_on_checkout_page ASC";
					$res=$db->query($sql);
					// loop and check inclusion
					$total_payment_modules=0;
					while ($h=$db->fetch_array($res)){
						if ($h['restricted_to_user_types']){
							$compare=",".$h['restricted_to_user_types'].",";
							if (!stristr($compare,$user->value("type"))){
								continue;
							}
						}
						$total_payment_modules++; // its ok
					}

					$checkout_template['payment_method'] = "<div class=\"shopping_cart_block\">\n";
					//$total_payment_modules=$db->num_rows($res);
					if ($total_payment_modules==1){
						$checkout_template['payment_method'] .= "<h3 class=\"shopping_cart_section_header\">Payment method:</h3><div id=\"checkout_options\">\n";
					} else {
						$checkout_template['payment_method'] .= "<h3 class=\"shopping_cart_section_header\">Please select a payment method:</h3><div id=\"checkout_options\">\n";
					}

					if ($_SESSION['total_for_further_payment']===0){
						$checkout_template['payment_method'].="<p>No further payment is required.</p>";
						$checkout_template['payment_method'] .="<input type=\"hidden\" name=\"payment_method\" value=\"voucher_only\">";
					} else {
						$res=$db->query($sql);
						while ($h=$db->fetch_array($res)){
							if ($h['restricted_to_user_types']){
								$compare=",".$h['restricted_to_user_types'].",";
								if (!stristr($compare,$user->value("type"))){
									continue;
								}
							}
							$checkout_template['payment_method'] .= "<div id=\"checkout_option_div\"><span class=\"checkout_option\"><span class=\"checkout_option_text\"><input style=\"float:left\" type=\"radio\" name=\"payment_method\" id=\"payment_method\" value=\"".$h['id']."\"";
							if ($total_payment_modules==1){ $checkout_template['payment_method'] .= " checked"; }
							$checkout_template['payment_method'] .= ">".$h['checkout_option_text'] . "</span>";
							if ($total_payment_modules==1){ $checkout_template['payment_method'] .= "<span class=\"checkout_option_text_only_method\">(This is the only payment method available.)</span>"; }
							if ($h['checkout_option_text_extra']){ $checkout_template['payment_method'] .= "<span class=\"checkout_option_text_extra\">".$h['checkout_option_text_extra'] . "</span>"; }
							$checkout_template['payment_method'] ."</span><br />";
							$checkout_template['payment_method'] .= "</div>";
						}
						// end payment modules
					}

					$checkout_template['payment_method'] .= "</div><!-- closes checkout_options //-->";
					$checkout_template['payment_method'] .= "</div><!-- chloses shopping cart block //-->";
					// checkout terms and conditions - move to template //
					if ($this->value("approve_terms")){
						$checkout_template['terms_and_conditions'] = $db->db_quick_match("templates","template","dbf_key_name","checkout_terms_and_conditions");
					}
					// end checkout terms and conditions - move to template //
					$checkout_template['submit'] .= "<div class=\"shopping_cart_block\"><p><span class=\"jc_button jc_button_160\"><a href=\"$cart_form_action\" style=\"font-weight:bold\">Continue to payment</a> &nbsp; </span></p><br clear=\"all\" /><!--<p>Click above to continue to the payment pages.</p>//--></div>";
				}
			} else {
				$checkout_template['user_details'] .= "<p style=\"text-align:center\">Please complete your address details above to continue</p>";
			}
			//$content .= "<hr size=\"1\" style=\"clear:both; margin-top:22px; padding-top:0px; \">\n";
		}
	}

	if ($db->table_exists("orders_extra_data")){
		$field_list=list_fields_in_table("orders_extra_data");
		$extra_order_fields_array=array();
		foreach ($field_list as $table_field_extra){
			$dbf_key="dbf_".$table_field_extra."_checkout_template";
			$arr_name="extra_order_fields_$table_field_extra";
			$checkout_template[$arr_name] = $db->db_quick_match("templates","template","dbf_key_name",$dbf_key);
		}
	}
	

	if (!$this->value("buy_requires_login")){
		$checkout_template['submit'] .= "</form>";
	}
	$content .= $checkout_template['user_details'];
	$content .= $checkout_template['payment_method'];
	$content .= $checkout_template['terms_and_conditions'];
	$content .= $checkout_template['submit'];
	$template_for_checkout_sql="SELECT id FROM templates WHERE dbf_key_name = \"checkout_template\"";
	$rv=$db->query($template_for_checkout_sql);
	$template_for_checkout_h=$db->fetch_array($rv);
	if ($template_for_checkout_h['id']){
		$new_content=$this->hash_into_template($checkout_template,$template_for_checkout_h['id']);
		$new_content=preg_replace("/{=.*?}/","",$new_content);	
	}
	if ($new_content){
		$content=$new_content;
	}
	return $content;
}

// this function initially logs the order before getting payment if necessary
function place_order(){

	global $db;
	global $user;

	// for after shipping confirmation on checkout without login..
	if ($_SESSION['no_login_form_data']){
		$_POST=$_SESSION['no_login_form_data'];
		foreach ($_SESSION['no_login_form_data'] as $k=>$v){
			$_REQUEST[$k]=$v;	
		}
	} 

	// old stuff
	//$po_number=$_POST['po_number']; 
	//$delivery_office=$_POST['delivery_address']; 
	//if (!$delivery_office){$delivery_office=0;} 
	$comments=$_POST['comments']; 
	$promotional_code=$_POST['promotional_code_validated'];

	$ordered_by=$user->value("id");
	$total_amount=$_SESSION['total_price'];	
	$start_transaction_sql=$db->query("BEGIN");

	// If its not a logged i user, log the user details which were POSTed through
	if (!$this->value("buy_requires_login") || $_SESSION['checkout_without_login']){

		// start with a field list of the order_user_data table
		$tablefields=list_fields_in_table("order_user_data");
		$update_fields=array();
		$update_values=array();
		$update_assoc=array();
		foreach ($tablefields as $tablefield){
			$request_field="new_" . $tablefield;
			if ($_REQUEST[$request_field]){
				array_push($update_fields,$tablefield);
				array_push($update_values,$db->db_escape($_REQUEST[$request_field]));
				$update_assoc[$tablefield]=$_REQUEST[$request_field];
			}
		}
		$order_user_data_sql="INSERT INTO order_user_data (" . join(",",$update_fields) . ") VALUES(\"";
		$order_user_data_sql.= join("\",\"",$update_values);
		$order_user_data_sql .= "\")";
		$order_user_res = $db->query($order_user_data_sql) or format_error("Cannot add user data to database. $order_user_data_sql " . ->db_error(),1,"","SQL:$order_user_data_sql<br />Message: " . ->db_error());
		// ORDERED BY - if we don't have a user_id we use the id of the user_details instead...
		$non_account_ordered_by=$db->last_insert_id();
		$_SESSION['non_account_user_id']=$non_account_ordered_by;
		$ordered_by="";
		$user_details_id=$non_account_ordered_by;
	} else {
		$non_account_ordered_by="";
	}
	if (!$ordered_by && !$non_account_ordered_by){
		format_error("An error has occured: No user data found to log this order to",1);
	}
	$payment_method=$_POST['payment_method'];
	$_SESSION['payment_method_in_use']=$payment_method;
	$source_identifier=$this->value("source_identifier");
	if ($payment_method != "voucher_only" && !$_SESSION['voucher_only_payment']){
		$payment_method=$db->field_from_record_from_id("payment_modules",$payment_method,"key_name");
	}
	$log_shipping=$this->value("shipping_total");
	if (!$this->value("buy_requires_login") && !preg_match("/^[-+]?[0-9]*\.?[0-9]+$/",$log_shipping)){
		$log_shipping=0;
	}

	if ($this->value("buy_requires_login")==1 || ($this->value("buy_requires_login")==2 && !$_SESSION['checkout_without_login'])){
		$order_sql = "INSERT INTO orders (ordered_by,order_date,datetime,complete,paid,total_amount,purchased_through_account,payment_method,shipping_total,grand_total,origin) values(".$ordered_by.",NOW(),NOW(),0,0,$total_amount,\"$promotional_code\",\"$payment_method\",".$log_shipping.",".$_SESSION['grand_total'].",\"$source_identifier\")";
	} else {
		$order_sql = "INSERT INTO orders (non_account_order,order_date,datetime,complete,paid,total_amount,purchased_through_account,payment_method,shipping_total,grand_total,origin) values(".$non_account_ordered_by.",NOW(),NOW(),0,0,$total_amount,\"$promotional_code\",\"$payment_method\",".$log_shipping.",".$_SESSION['grand_total'].",\"$source_identifier\")";
	}

	$order_res = $db->query($order_sql) or $cart_error = $this->display_mysql_cart_error($order_sql . " gave " . ->db_error());
	$order_id = $db->last_insert_id();
		// extra order fiels
		if ($db->table_exists("orders_extra_data")){
			$field_list=list_fields_in_table("orders_extra_data");
			$extra_order_fields_array=array();
			$extra_order_fields_data_array=array();
			$have_extra_fields=0;
			foreach ($field_list as $table_field_extra){
				//print "Checking $table_field_extra<br >";
				if ($_POST[$table_field_extra]){
					$have_extra_fields++;
					array_push($extra_order_fields_array,$table_field_extra);
				 //print "Got a POST of " . $_POST[$table_field_extra] . "<br />";
					array_push($extra_order_fields_data_array,$db->db_escape($_POST[$table_field_extra]));
				} 
				if ($_SESSION['cart_vars']['extra_order_fields'][$table_field_extra] && !$_POST['table_field_extra']){
				 //print "Got a session of " . $_SESSION['cart_vars']['extra_order_fields'][$table_field_extra] . "<br />";
					$have_extra_fields++;
					array_push($extra_order_fields_array,$table_field_extra);
					array_push($extra_order_fields_data_array,$db->db_escape($_SESSION['cart_vars']['extra_order_fields'][$table_field_extra]));
				}
			}
			if ($have_extra_fields){
				$log_extra_data_sql="INSERT INTO orders_extra_data (order_id,".join(",",$extra_order_fields_array).") VALUES ($order_id,\"" . join("\",\"",$extra_order_fields_data_array) . "\")";
				$log_extras_rv=$db->query($log_extra_data_sql);
				//print $log_extra_data_sql;
				//print "<br>RV is $log_extras_rv";
			}
		}

	// update order_user_data at this point
	if (!$this->value("buy_requires_login") || $_SESSION['checkout_without_login']){
		$update_order_user_data_sql="UPDATE order_user_data SET order_id = $order_id WHERE id = " . $_SESSION['non_account_user_id'];
		$update_order_user_data_res=$db->query($update_order_user_data_sql);
	}
	$_SESSION['order_id']=$order_id;
	
	foreach ($_SESSION['cart'] as $item => $itemdata){

		$item_price_sql="SELECT " . $this->value("price_field") . ",vat,price AS price_inc_vat FROM products WHERE ID = \"" . $itemdata['product_id'] . "\"";
		$ip_res=$db->query($item_price_sql);
		while ($hp=$db->fetch_array($ip_res)){
			$individual_item_price=$hp[$this->value("price_field")];
			$vat_on_item=$hp['vat'];
			$price_inc_vat=$hp['price_inc_vat'];
		}
		// DONT NEED THE ABOVE ANY MORE WITH PRICE CONFIGURABLE PRODUCTS
		$individual_item_price=$_SESSION['cart'][$item]['price'];
		if ( $individual_item_price && ($vat_on_item<=0 && !$price_inc_vat<=0)){$price_inc_vat=$individual_item_price;} /// this is basically using user_can_choose_price - at least that is what this hack is designed to do..
		$product_sql = "INSERT INTO order_products (order_id,product_id,quantity,price,vat,price_inc_vat) values($order_id,\"".$itemdata['product_id']."\",".$_SESSION['cart'][$item]['quantity'].",".$individual_item_price.",".$vat_on_item.",".$price_inc_vat.")";
		$product_res=$db->query($product_sql) or $cart_error = $this->display_mysql_cart_error(->db_error());
		$order_product_id=$db->last_insert_id();
		
		if ($_SESSION['cart'][$item]['attributes']){
			foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
				$attr_sql = "INSERT INTO order_product_attributes VALUES(\"\",$order_product_id,\"$attributename\",\"$attributevalue\")";
				$attr_res = $db->query($attr_sql) or $cart_error = $this->display_mysql_cart_error(->db_error());
			}
		}
	}

	if ($cart_error){
		$rollback_transaction=$db->query("ROLLBACK");
		return 0;
	} else {
		$commit_transaction=$db->query("COMMIT");
		//unset($_SESSION['cart']);
		$_SESSION['userid']=$_COOKIE['login'];
		$_SESSION['order_id']=$order_id;

		// log the checkout modules too...
		$cm_total=0;
		$active_checkout_modules="SELECT key_name FROM checkout_modules WHERE active=1 ORDER BY ordering";
		$cm_rv=$db->query($active_checkout_modules);
		while ($cm_h=$db->fetch_array($cm_rv)){
			$check_mod_session_var=$cm_h['key_name'] . "_total";
			if ($_SESSION[$check_mod_session_var]){
				// we have a total for this module which needs storing
				$insert_cm_sql="INSERT INTO order_total_extras (order_id,module,amount) VALUES ($order_id,\"".$cm_h['key_name']."\",$_SESSION[$check_mod_session_var])";
				$icm_rv=$db->query($insert_cm_sql);
				$cm_total=$cm_total + $_SESSION[$check_mod_session_var];
			}
		}	

		// If buy without login, need to tag the user details with the order id from user_details_id
		if (!$this->value("buy_requires_login")){
			$update_user_details="UPDATE order_user_data SET order_id = $order_id WHERE id = $user_details_id";
			$update_user_details_result=$db->query($update_user_details);
		}

		$set_order_cookie = setcookie("order_id", $order_id);

		// we only need to mail at this stage the preliminary template.
		if ($this->value("send_preliminary_order_notifications")){
			global $CONFIG;
			if ($_REQUEST_SAFE['s']){ $siteid=$_REQUEST_SAFE['s'];} else { $siteid=$CONFIG['default_site'];}
			if ($siteid){
				$current_site=load_web_site_vars($siteid);
				$site_name=$current_site['site_name'];
			} else {
				$site_name="";
			}

			$mail_cart_to=$this->value("mail_orders_to");
			$mail_cart_from=$this->value("mail_orders_from_name");
			$mail_cart_from_email=$this->value("mail_orders_from_address"); 
			$mail_from="\"$mail_cart_from\" <$mail_cart_from_email>";
			$subject=$this->value("preliminary_notification_subject");
			$headers = "From: $mail_from\n";
			$headers .= "Reply-To: $mail_from\n";
			$headers .= "Content-type:text/html\n\r\n\r";
			$mail_template=$db->field_from_record_from_id("templates",$this->value('preliminary_notification_template'),"template");
			$order_details=$this->view_cart_general( array("run_modules" => "1", "email" => "1" ));
			$mail_template=str_replace("{=order_details}",$order_details,$mail_template);
			$mail_template = str_replace("{=total_amount}",$total_amount,$mail_template);
			$mail_template = str_replace("{=user_name}",$user->value("full_name"),$mail_template);
			$mail_template = str_replace("{=site_name}",$site_name,$mail_template);
			$mail_template = str_replace("{=user_id}",$user->value("id"),$mail_template);
			if (!$user->value("id")){
				$quick_user_details="Non account order - please view the order to see customer information";
			} else{
				$quick_user_details=$user->value("full_name") . "(ID:" . $user->value("id") . ")";
			}
			$mail_template = str_replace("{=user_details}",$quick_user_details,$mail_template);
			// add to order_details to appear in email as well
			$mail_user_template=$db->field_from_record_from_id("templates",$this->value('email_user_data_template'),"template");
			foreach ($tablefields as $tablefield){
				$replace_str="{=".$tablefield."}";
				$mail_user_template=str_replace($replace_str,$update_assoc[$tablefield],$mail_user_template);
			}
			$mail_template .= $mail_user_template;
			$mail_template = str_replace("{=order_id}",$order_id,$mail_template);
			mail ($mail_cart_to,$subject,$mail_template,$headers) or die("cant send mail");
		}
	}

	//print "mail sent and session is ";
	//var_dump($_SESSION); 
	if ($_SESSION['preorder_cart']){
		$pre_order_result=$this->place_pre_orders();
	}
	if ($_SESSION['cart']){
		return 1;
	} else {
		return 0;
	}
}

function place_pre_orders(){

	global $db;
	global $user;

	$ordered_by=$user->value("id");
	if ($_SESSION['non_account_user_id']){
		$non_account_ordered_by=$_SESSION['non_account_user_id'];
	}

	$payment_method=$_POST['payment_method'];
	$_SESSION['payment_method_in_use']=$payment_method;
	$source_identifier=$this->value("source_identifier");
	$payment_method=$db->field_from_record_from_id("payment_modules",$payment_method,"key_name");

	
	foreach ($_SESSION['preorder_cart'] as $item => $itemdata){

		$item_price_sql="SELECT " . $this->value("price_field") . " FROM products WHERE ID = $item";
		$ip_res=$db->query($item_price_sql);
		while ($hp=$db->fetch_array($ip_res)){
			$individual_item_price=$hp[$this->value("price_field")];
		}

		// retrieve the following values..
		$this_preorder_shipping=$_SESSION['preorder_cart'][$item]['preorder_shipping_amount'];
		$this_preorder_promotional_code="";
		$this_preorder_total_amount=$individual_item_price; // the price of the preorder on its own
		$this_preorder_grand_total=$this_preorder_total_amount+$this_preorder_shipping;

		if (!$this->value("buy_requires_login") && !preg_match("/^[-+]?[0-9]*\.?[0-9]+$/",$log_shipping)){
			$log_shipping=0;
		}
		$start_transaction_sql=$db->query("BEGIN");
		if ($this->value("buy_requires_login")==1 || ($this->value("buy_requires_login")==2 && !$_SESSION['checkout_without_login'])){
			$order_sql = "INSERT INTO orders (ordered_by,order_date,datetime,complete,paid,total_amount,purchased_through_account,payment_method,shipping_total,grand_total,origin,pre_order) values(".$ordered_by.",NOW(),NOW(),0,0,$this_preorder_total_amount,\"$this_preorder_promotional_code\",\"$payment_method\",".$this_preorder_shipping.",".$this_preorder_grand_total.",\"$source_identifier\",1)";
		} else {
			$order_sql = "INSERT INTO orders (non_account_order,order_date,datetime,complete,paid,total_amount,purchased_through_account,payment_method,shipping_total,grand_total,origin,pre_order) values(".$non_account_ordered_by.",NOW(),NOW(),0,0,$this_preorder_total_amount,\"$this_preorder_promotional_code\",\"$payment_method\",".$this_preorder_shipping.",".$this_preorder_grand_total.",\"$source_identifier\",1)";
		}
	
		$order_res = $db->query($order_sql) or $cart_error = $this->display_mysql_cart_error($order_sql . " gave " . ->db_error());
		$order_id = $db->last_insert_id();
		// extra order fiels
		if ($db->table_exists("orders_extra_data")){
			$field_list=list_fields_in_table("orders_extra_data");
			$extra_order_fields_array=array();
			$extra_order_fields_data_array=array();
			$have_extra_fields=0;
			foreach ($field_list as $table_field_extra){
				if ($_POST[$table_field_extra]){
					$have_extra_fields++;
					array_push($extra_order_fields_array,$table_field_extra);
					array_push($extra_order_fields_data_array,$db->db_escape($_POST[$table_field_extra]));
				}
			}
			if ($have_extra_fields){
				$log_extra_data_sql="INSERT INTO orders_extra_data (order_id,".join(",",$extra_order_fields_array).") VALUES ($order_id,\"" . join("\",\"",$extra_order_fields_data_array) . "\")";
				$log_extras_rv=$db->query($log_extra_data_sql);
			}
		}

		$_SESSION['preorder_cart'][$item]['order_number']=$order_id;
		// products for each preorder
		$product_sql = "INSERT INTO order_products (order_id,product_id,quantity,price) values($order_id,$item,".$_SESSION['preorder_cart'][$item]['quantity'].",".$individual_item_price.")";
		$product_res=$db->query($product_sql) or $cart_error = $this->display_mysql_cart_error(->db_error());
		$order_product_id=$db->last_insert_id();
		
		if ($_SESSION['preorder_cart'][$item]['attributes']){
			foreach ($_SESSION['preorder_cart'][$item]['attributes'] as $attributename => $attributevalue){
				$attr_sql = "INSERT INTO order_product_attributes VALUES(\"\",$order_product_id,\"$attributename\",\"$attributevalue\")";
				$attr_res = $db->query($attr_sql) or $cart_error = $this->display_mysql_cart_error(->db_error());
			}
		}
		if ($cart_error){
			$rollback_transaction=$db->query("ROLLBACK");
			return 0;
		} else {
			$commit_transaction=$db->query("COMMIT");

			// If buy without login, need to tag the user details with the order id from user_details_id
			if (!$this->value("buy_requires_login")){
				$update_user_details="UPDATE order_user_data SET order_id = $order_id WHERE id = $user_details_id";
				$update_user_details_result=$db->query($update_user_details);
			}
		}
	} // end foreach item
	return 1;
}

function enter_golden_account_code($promo_code){
	// lookup code
	global $db;
	global $user;
	$sql = "select orders.id,total_amount,order_products.*,download_urls.download_url,user.id as userid,user.first_name,user.second_name from orders INNER JOIN order_products ON orders.id=order_products.order_id INNER JOIN download_urls ON orders.id=download_urls.order_id INNER JOIN user on orders.ordered_by=user.id WHERE download_urls.download_url = \"$promo_code\"";
	$res=$db->query($sql);
	while ($h=$db->fetch_array($res)){
		if ($h['userid']==$user->value('id')){
			$code_validates=1;
		}
	}
	if (!$code_validates){	
		$return .= "<p style=\"color:red\">Invalid Code</p>";
		$return .= $this->confirm_order();
		return $return;
	} else {
		$code_validated=1;
		$return = "<p style=\"color:green\">Code Validated</p>";
		$return .= $this->confirm_order($promo_code);
		return $return;
	}
}


function enter_promotional_code($promo_code){
	// lookup code
	global $db;
	global $user;

	require_once(LIBPATH . "/classes/shopping_cart/gift_vouchers_complex.php");
	$gv = new gift_vouchers_complex();

	$code_validates=$gv->process_enter_promotional_code($promo_code);

	$sql = "select orders.id,total_amount,order_products.*,download_urls.download_url,user.id as userid,user.first_name,user.second_name from orders INNER JOIN order_products ON orders.id=order_products.order_id INNER JOIN download_urls ON orders.id=download_urls.order_id INNER JOIN user on orders.ordered_by=user.id WHERE download_urls.download_url = \"$promo_code\"";
	$res=$db->query($sql);
	while ($h=$db->fetch_array($res)){
		if ($h['userid']==$user->value('id')){
			$code_validates=1;
		}
	}
	if (!$code_validates){
		$return .= "<p class=\"dbf_para_alert\">Sorry - this is not a valid promotional code.</p>";
		$return .= $this->confirm_order();
		return $return;
	} else {
		$code_validated=1;
		$return = "<p class=\"dbf_para_success\">Thank you - your promotional code has been validated. Your new order total is below.</p>";
		$return .= $this->confirm_order($promo_code);
		return $return;
	}
}

function getInternalSaleId(){
	$debug=0;
	if (!$_COOKIE['order_id'] && $debug){
		print "unable to find internal order id from cookie at point 1";
		var_dump($_COOKIE);
		var_dump($COOKIE['order_id']);
	print "<p>Session:</p>";
		var_dump($_SESSION);
	}
	return $_COOKIE['order_id'];
}

function calculate_shipping($preorder){

	$shipping_rate=0;
	if (!$this->value("buy_requires_login") || $this->value("buy_requires_login")==2){
	if ($_POST['new_delivery_country']){
		$shipping_country=$_POST['new_delivery_country']; // not always but for PGI yes...
	} else if ($_POST['new_country'] && $_POST['new_same_as_billing_address']){
		$shipping_country=$_POST['new_country'];
	} else {
	}
	if (!$shipping_country && stristr($_SERVER['QUERY_STRING'],"ion=place_order") && $_SESSION['no_login_form_data']){
		if ($_SESSION['no_login_form_data']['new_delivery_country']){
			$shipping_country=$_SESSION['no_login_form_data']['new_delivery_country'];
		} else if ($_SESSION['no_login_form_data']['new_country'] && $_SESSION['no_login_form_data']['new_same_as_billing_address']){
			$shipping_country=$_SESSION['no_login_form_data']['new_country'];
		} else {
		}
		
	}
	if (!$shipping_country){ // grab it from user data...
		$internalsaleid=$this->getInternalSaleId();
		if ($internalsaleid){
			global $db;
			$sql="SELECT * from orders INNER JOIN order_user_data ON orders.id=order_user_data.order_id WHERE orders.id = ". $internalsaleid; 
			$rv=$db->query($sql);
			$h=$db->fetch_array($rv);
			$shipping_country=$h['delivery_country']; // again note this field is hard coded
		}
	}
} else {
	$shipping_country=""; // not required as module will get it from user details
}
if ($_SESSION['user_selected_shipping_service']){
	$shipping_rate=$this->load_specific_shipping_module($_SESSION['user_selected_shipping_service']);
	} else {
		$shipping_options_to_best_rate['shipping_country']=$shipping_country;
		$shipping_options_to_best_rate['preorder']=$preorder;
		$shipping_rate=$this->get_best_shipping_rate($shipping_options_to_best_rate);
	}
	if (is_numeric($shipping_rate)){ $shipping_rate=sprintf("%4.2f",$shipping_rate);} 
	$this->set_value("shipping_total",$shipping_rate);
	return $shipping_rate;
}

function calculate_sub_total(){
	$_SESSION['order_total'] = $_SESSION['total_price'] + $this->value("shipping_total");
	$_SESSION['grand_total'] = $_SESSION['total_price'] + $this->value("shipping_total");
	if (preg_match("/[a-zA-Z]/",$this->value("shipping_total"))){
		$_SESSION['order_total'] = "";
	}
	return $_SESSION['grand_total'];
}

function calculate_grand_total(){
	$_SESSION['order_total'] = sprintf("%4.2f",$_SESSION['total_price'] + $this->value("shipping_total"));
	$_SESSION['grand_total'] = sprintf("%4.2f",$_SESSION['total_price'] + $this->value("shipping_total"));
	if (preg_match("/[a-zA-Z]/",$this->value("shipping_total"))){
		$_SESSION['order_total'] = "";
	}
	if ($_SESSION['checkout_modules_add_to_total']){
		$_SESSION['grand_total']=sprintf("%4.2f",$_SESSION['grand_total'] + $_SESSION['checkout_modules_add_to_total']);
	}
	$_SESSION['store_grand_total_for_emails']=$_SESSION['grand_total'];
	return $_SESSION['grand_total'];
}

function calculate_grand_total_preorder(){
	$_SESSION['preorder_order_total'] = sprintf("%4.2f",$_SESSION['preorder_total_price'] + $this->value("all_preorders_shipping_total"));
	$_SESSION['preorder_grand_total'] = sprintf("%4.2f",$_SESSION['preorder_total_price'] + $this->value("all_preorders_shipping_total"));
	if (preg_match("/[a-zA-Z]/",$this->value("shipping_total"))){
		$_SESSION['preorder_order_total'] = "";
	}
	if ($_SESSION['checkout_modules_add_to_total']){
		$_SESSION['preorder_grand_total']=sprintf("%4.2f",$_SESSION['grand_total'] + $_SESSION['checkout_modules_add_to_total']);
	}
	return $_SESSION['preorder_grand_total'];
}

function calculate_grand_total_preorder_each($item_shipping){
	$_SESSION['preorder_order_total'] = sprintf("%4.2f",$_SESSION['preorder_total_price_each'] + $item_shipping);
	$_SESSION['preorder_grand_total'] = sprintf("%4.2f",$_SESSION['preorder_total_price_each'] + $item_shipping);
	if (preg_match("/[a-zA-Z]/",$this->value("shipping_total"))){
		$_SESSION['preorder_order_total'] = "";
	}
	if ($_SESSION['checkout_modules_add_to_total']){
		$_SESSION['preorder_grand_total']=sprintf("%4.2f",$_SESSION['grand_total'] + $_SESSION['checkout_modules_add_to_total']);
	}
	return $_SESSION['preorder_grand_total'];
}

function print_cart_submit_form(){
	// if user is already logged in
	$cart_template=$this->value("cart_template");
	$cart_default_web_site=$this->value("cart_default_web_site");
	$content="";
	$content .= "<!-- form here for standard logged in method //-->";
	$content .= "<form name=\"place_order_form\" method=\"post\" action=\"site.php?s=$cart_default_web_site&action=place_order&mt=$cart_template\">\n";
	$content .= "<input type=\"hidden\" name=\"promotional_code_validated\" value=\"$account_code_validated\">";
	return $content;
}

function print_user_details_form(){
	global $libpath;
	require_once("$libpath/classes/core/filters.php");
	$user_details_filter=new filter($this->value("no_login_user_details_form_filter"));
	$options['filter']=$user_details_filter->all_filter_keys();
	$form_content=form_from_table("order_user_data","add_row","","1",$options);
	$form_content=str_replace("</form>","",$form_content);
	$form_content .= "<input type=\"hidden\" name=\"checkout_without_login\" value=\"1\" />";
	return $form_content;
	//{=FORM:table=user&filter=127&formtype=edit_single&rowid=user_data_from_cookie('id')}

	//global $db;
	//$form_content='<form action="site.php?action=place_order&amp;mt=" method="post" name="place_order_form">' . "\n";
	//$form_content .= '<input name="promotional_code_validated" type="hidden" />' . "\n";
	//$form_content .= $db->field_from_record_from_id("templates",93,"template");
	//return $form_content;
}

function confirm_personal_details(){
	global $user;
	global $db;
	$usersql="SELECT * from user WHERE id = " . $user->value("id");
	$res=$db->query($usersql);
	$h=$db->fetch_array($res);
	$h['country']=$db->field_from_record_from_id("countries",$h['country'],"Name");
	$h['delivery_country']=$db->field_from_record_from_id("countries",$h['delivery_country'],"Name");
	$templateid=$this->value("user_details_confirm_template");
	if ($h['same_as_billing_address']){
		$h['delivery_address_1']="Deliver this order to my billing address";
		$h['delivery_address_2']="";
		$h['delivery_address_3']="";
		$h['delivery_county_or_state']="";
		$h['delivery_city']="";
		$h['us_delivery_state']="";
		$h['delivery_zip_or_postal_code']="";
		$h['delivery_country']="";
	}
	if (!$h['same_as_billing_address'] && (!$h['delivery_country'] || !$h['delivery_address_1'])){
		$this->set_value("no_delivery_address",1);
		$h['delivery_address_1']="<a href=\"checkout_edit_addresses.html\">Please set your address details - click here</a>";
		$h['delivery_address_2']="";
		$h['delivery_address_3']="";
		$h['delivery_county_or_state']="";
		$h['us_delivery_state']="";
		$h['delivery_city']="";
		$h['delivery_zip_or_postal_code']="";
		$h['delivery_country']="";
	}
	if (!$h['address_1'] || !$h['zip_or_postal_code'] || !$h['country']){
		$this->set_value("no_billing_address",1);
		$h['address_1']="<a href=\"checkout_edit_addresses.html\">No address details found - click here to add them</a>";
	}

	$_SESSION['delivery_postcode']=$h['delivery_zip_or_postal_code'];

	$user_details_template=$this->hash_into_template($h,$templateid);
	return $user_details_template;
}

function hash_into_template($hash,$templateid){
	global $db;
	$template=$db->field_from_record_from_id("templates",$templateid,"template");
	foreach ($hash as $key => $value){
		$dbf_tag="{=".$key."}";
		$template=str_replace("$dbf_tag",$value,$template);
	}
	return $template;
}

function load_payment_module(){
	global $libpath;
	require_once("$libpath/classes/shopping_cart/payment_modules/payment_module_paypal_express_checkout.php");
	$paypal_details=new payment_module_paypal_express_checkout();
	$content = $paypal_details->initiate_payment();
	return $content;
}

// these 2 functions need to move to the paypal module 
function load_paypal_payment_success(){
	global $libpath;
	require_once("$libpath/classes/shopping_cart/payment_modules/payment_module_paypal_express_checkout.php");
	$paypal_details=new payment_module_paypal_express_checkout();
	$paypal_results = $paypal_details->paypal_express_checkout_success();
	$content=$paypal_results['content'];
	$payment_status=$paypal_results['status'];
	if ($payment_status){
		global $mycart;
		$content=$mycart->complete_order_after_payment_taken();
		//require_once("$libpath/classes/flight_logistics_order_post.php");
		//$flight= new flight_logistics_order_post();
		//$flight_post_result = $flight->post_order_to_flight_logistics();
		//unset($_SESSION['cart']);
	}
	return $content;	
}

function load_paypal_payment_cancel(){
	global $libpath;
	require_once("$libpath/classes/shopping_cart/payment_modules/payment_module_paypal_express_checkout.php");
	$paypal_details=new payment_module_paypal_express_checkout();
	$content=$paypal_details->paypal_express_checkout_cancel();
	return $content;
}

function get_item_price($product_id){
	global $db;
	// get price field - this is called from another module as parent so...
	$sql="SELECT price_field FROM shopping_cart_configuration LIMIT 1";
	$rv=$db->query($sql);
	$h=$db->fetch_array();
	$price_field=$h['price_field'];
	$sql="SELECT " . $price_field . ", special_web_price FROM products where id = $product_id"; 
	$res=$db->query($sql);
	$h=$db->fetch_array($res);
	if ($h['special_web_price'] && $h['special_web_price']>0){
		$return_price=$h['special_web_price'];
	} else {
		//$return_price=$h[$this->value($price_field)];
		$return_price=$h[$price_field];
	}
	//print "Returning price of " . $return_price;
	return $return_price;
}

function check_stock_quantity($product_id,$qty_ordered){
	global $db;
	if (!$this->external_stock_check_function){
		$sql="SELECT stock_quantity FROM products where id = $product_id";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);
		$qty=$h['stock_quantity'];
	} else {
		global $libpath;
		list($module,$function)=explode("::",$this->external_stock_check_function);
		require_once("$libpath/classes/$module.php");
		$stock_check_mod=new $module;
		$stock_level=eval("new ".$module.";");
		$match_data_fields=preg_match_all("/{=(\w+)}/i",$function,$field_matches);
		//var_dump($field_matches[1]);
		$sql_fields=array();
		foreach ($field_matches[1] as $field_match){
			array_push($sql_fields,$field_match) or die("cant push");
		}
		$sql_field_list=implode(",",$sql_fields);
		$sql="SELECT $sql_field_list FROM products WHERE id = $product_id";
		//print "sql is $sql";
		$res=$db->query($sql);
		$build_function=$function;
		while ($h=$db->fetch_array($res)){
			foreach ($h as $key => $value){
				//print "on key of $key<br>";
				$build_function=str_replace("{=$key}",$value,$build_function);
			}
		}
		// now call the external function
		$build_function .= ";";
		$build_function="\$qty = \$stock_check_mod->" . $build_function;
		//print "function to eval is $build_function";
		$evel_result=eval($build_function);
	}
	return $qty;
}

function log_order_pre_payment(){

	if (!$_POST['payment_method']){ // not going to work
		$return_content['title']="Error";
		$return_content['content']="<p>Sorry, this page has expired permanently.</p><p>Any order you have placed in order to reach this place has either been placed, or you need to restart the checkout process by clicking <a href=\"checkout.html\" style=\"text-decoration:underline\">here</a>. </p><p>You may also like to view recent orders placed on your account by clicking <a href=\"my-account.html\" style=\"text-decoration:underline\">here</a>";
		return $return_content;
	}

	$cart_template=$this->value("cart_template");
	$order_confirmation = $this->view_cart_general( array("run_modules" => "1") );
	print "placing";
	$place_order_result=$this->place_order();

	if ($place_order_result==1){
		if (!$_POST['promotional_code_validated']){
			$title="Checkout: Payment";
			//$content = "<p>Thank you - your order has been logged. </p>";
			$content .= $order_confirmation;

			if ($_SESSION['voucher_only_payment']){
				// payment covered by voucher in full
			} else {
				$sql="SELECT * from payment_modules WHERE id = " . $_POST['payment_method'];
				global $db;
				$res=$db->query($sql);
				while ($h=$db->fetch_array($res)){
					$payment_icon=$h['payment_icon'];
					$forwarding_page_text=$h['forwarding_page_text'];
					$module_specific_payment_function=$h['module_specific_payment_function'];
					$class_filename=$h['class_filename'];
				}
			}
			// virtual voucher_only_payment - we have got here! 
			$content_pre_forwarding_page_text=$content;
			if ($forwarding_page_text){
				$content .= "<hr size=\"1\"><p>$forwarding_page_text</p>";
			}

			// load the actual payment widget from the specific module here
			if ($module_specific_payment_function){
				if ($class_filename){
					global $libpath;
					$obj_name=str_replace(".php","",$class_filename);
					$class_filename = "$libpath/classes/$class_filename";
					
					if (file_exists($class_filename)){
						require_once($class_filename);
						$my_payment = new $obj_name();
						$returned_content = $my_payment->$module_specific_payment_function();
						if (is_array($returned_content) && $returned_content['message']){
							$content .= $returned_content['message'];
							$content_pre_forwarding_page_text .= $returned_content['message'];
						} else {
							$content .= $returned_content;
							$content_pre_forwarding_page_text .= $returned_content;
						}
					} else {
						print "class file does not exist";
					}
				}
			}
			
			$module_actions=explode("|",$returned_content['actions']);
			foreach ($module_actions as $module_action){
				if ($module_action=="cancel_forwarding_page_text"){
					$content=$content_pre_forwarding_page_text;
				}
				if ($module_action=="cancel_payment_icon"){
					$payment_icon="";
				}
			}

			if ($payment_icon){
				$content .= "<p><a href=\"site.php?action=load_payment_module\"><img src=\"$payment_icon\" align=\"left\" style=\"margin-right:7px;\" border=0></a></p>";
			}
		
			// sagepay link
		} else {
			$title = "Order logged";
			//$content = "<p>Thank you - your order has been logged. </p>";
			$content .= $order_confirmation;
			$promo_code_validated = $_POST['promotional_code_validated'];
			$content .= "<form name=\"golden_account_order\" method=\"post\" action=\"site.php?action=place_order_on_account&amp;mt=$cart_template\"><input type=\"hidden\" name=\"promotional_code_validated\" value=\"$promo_code_validated\"></form>";
			$content .= "<p><span class=\"order_button\" style=\"float:left;\"><a href=\"Javascript:document.forms['golden_account_order'].submit()\">Continue To Retrieve Your Download URLs</a> &nbsp; </span></p>";
		}
	} else {
		// $mycart->place_order failed
		$title="Order error";
		$content="<p>This page has permanently expired. Please return to the <a href=\"checkout.html\">checkout</a> to try again.</p>";
	}
	$return_content['content']=$content;
	$return_content['title']=$title;
	return $return_content;	
}

function complete_order_after_payment_taken($order_number){
	global $db;
	if (!$order_number){
		$internalSaleId=$this->getInternalSaleId();
		if (!$internalSaleId){
			format_error ("No internal sale id to be found!",1); exit;
		}
		if (!$_SESSION['order_id']){
			return;
		}
	} else {
		$internalSaleId=$order_number;
	}
	if ($this->value("buy_requires_login")==1 || ($this->value("buy_requires_login")==2 && !$_SESSION['checkout_without_login'])){
		// user address data for the order if we havent logged them already
		global $user;
		$user_id=$user->value("id");
		$user_full_name=$user->value("full_name");
		$get_user_address_details="SELECT * from user WHERE id = $user_id";
		$all_user_details=$db->query($get_user_address_details);
		while ($uh=$db->fetch_array($all_user_details)){
			$user_billing_country=$uh['country'];
			$user_billing_address=$uh['address_1'] . " " . $uh['address_2'] . " " . $uh['address_3'] . " " . $uh['city'] . " " . $uh['county_or_state'] . " " . $uh['us_billing_state'] . " " . $uh['zip_or_postal_code'];
			if ($uh['same_as_billing_address']){
				$user_delivery_country=$user_billing_country;
				$user_delivery_address=$user_billing_address;
			} else {
				$user_delivery_country=$uh['delivery_country'];
				$user_delivery_address=$uh['delivery_address_1'] . " " . $uh['delivery_address_2'] . " " . $uh['delivery_address_3'] . " " . $uh['delivery_city'] . " " . $uh['delivery_county_or_state'] . " " . $uh['us_delivery_state'] . " " . $uh['delivery_zip_or_postal_code'];
			}
			$user_email=$uh['email_address'];

			// all regular fields
			$in_address_1=$uh['address_1'];
			$in_address_2=$uh['address_2'];
			$in_address_3=$uh['address_3'];
			$in_city=$uh['city'];
			$in_county=$uh['county_or_state'];
			$in_zip=$uh['zip_or_postal_code'];
			$in_us_billing_state=$uh['us_billing_state'];
			$in_country=$uh['country'];
	
			if ($uh['same_as_billing_address']){
				$in_del_address_1=$uh['address_1'];
				$in_del_address_2=$uh['address_2'];
				$in_del_address_3=$uh['address_3'];
				$in_del_city=$uh['city'];
				$in_del_county=$uh['county_or_state'];
				$in_del_zip=$uh['zip_or_postal_code'];
				$in_del_us_billing_state=$uh['us_billing_state'];
				$in_del_country=$uh['country'];
			} else {
				$in_del_address_1=$uh['delivery_address_1'];
				$in_del_address_2=$uh['delivery_address_2'];
				$in_del_address_3=$uh['delivery_address_3'];
				$in_del_city=$uh['delivery_city'];
				$in_del_county=$uh['delivery_county_or_state'];
				$in_del_zip=$uh['delivery_zip_or_postal_code'];
				$in_del_us_delivery_state=$uh['delivery_us_billing_state'];
				$in_del_country=$uh['delivery_country'];
			}
			$in_same_as_billing_address=$uh['same_as_billing_address'];
		}
		$log_user_details="INSERT INTO order_user_data (order_id,name,email,billing_address,billing_country,delivery_address,delivery_country,address_1,address_2,address_3,city,county_or_state,us_billing_state,zip_or_postal_code,country,delivery_address_1,delivery_address_2,delivery_address_3,delivery_city,delivery_county_or_state,us_delivery_state,delivery_zip_or_postal_code,same_as_billing_address) VALUES($internalSaleId,\"$user_full_name\",\"$user_email\",\"$user_billing_address\",\"$user_billing_country\",\"$user_delivery_address\",\"$user_delivery_country\",\"$in_address_1\",\"$in_address_2\",\"$in_address_3\",\"$in_city\",\"$in_county\",\"$in_us_billing_state\",\"$in_zip\",\"$in_country\",\"$in_del_address_1\",\"$in_del_address_2\",\"$in_del_address_3\",\"$in_del_city\",\"$in_del_county\",\"$in_del_us_delivery_state\",\"$in_del_zip\",\"$in_same_as_billing_address\")";
		$log_user_details_res=$db->query($log_user_details);

	}

	// store user details for printing in the email in the object
	if ($this->value("buy_requires_login")==1 || ($this->value("buy_requires_login")==2 && $_SESSION['checkout_without_login'])){
		$this->user_details_for_emails['billing_address']=$user_billing_address;
		$this->user_details_for_emails['delivery_address']=$user_delivery_address;
		$this->user_details_for_emails['billing_country']=$user_billing_country;
		$this->user_details_for_emails['delivery_country']=$user_delivery_country;
	} else {
		// no login? Get the user details now..
		$sql="SELECT * from order_user_data WHERE order_id=$internalSaleId";
		$rv=$db->query($sql);
		while ($h=$db->fetch_array($rv)){
			$user_billing_country=$h['country'];
			$user_delivery_country=$h['delivery_country'];
			$user_billing_address=$h['billing_address']; // note: not set yet, must concatenate
			$user_delivery_address=$h['delivery_address']; // also not set, must concatenate
		}
	}

	// is it vatable? check now..
	$vatable=$db->field_from_record_from_id("countries",$user_delivery_country,"eu_country");
	if (!$vatable){$vatable=0;}

	// update order in the database to notify success
	if (!$user_delivery_country){
		$user_delivery_country=0;
	}
	$log_completed_payment_sql="UPDATE orders SET complete = 1, paid = 1, date_paid=NOW(), order_country = $user_delivery_country, vatable = $vatable WHERE id = $internalSaleId";
	$complete_order_result=$db->query($log_completed_payment_sql);

	// confirmation emails
	$this->mail_final_order_confirmation_to_customer();
	$this->mail_final_order_confirmation_to_office();

	$return .= "<p>Thank you for your purchase - this transaction is now complete.</p>";
	$return .= "<p>A confirmation of your order has been sent to you by email.</p>"; #matt# - $user_email would not pick up here??!

	// post the order to flight logistics
	//global $libpath;
	//require_once("$libpath/classes/flight_logistics_order_post.php");
	//$flight= new flight_logistics_order_post();
	//$flight_post_result = $flight->post_order_to_flight_logistics();
	// This is the NEW way of posting the order to flight logistics
	global $libpath;
	if ($this->value("external_order_post_function")){
		list($post_module,$post_function)=explode("::",$this->external_order_post_function);
		require_once("$libpath/classes/$post_module.php");
		$order_post_module=new $post_module;
		$build_function = "\$order_post_result = \$order_post_module->" . $post_function .= ";";
		$eval_result=eval($build_function);	
	}
	$record_payment_result=$this->record_payment();
	if ($this->value("run_extra_code_on_place_order_success")){
		$all_codes=explode(";",$this->run_extra_code_on_place_order_success);
		foreach ($all_codes as $each_code){
			$extra_module=""; $extra_function="";
			list($extra_module,$extra_function)=explode("::",$each_code);
			if ($extra_module){
				if ($extra_module != "this"){
					require_once("$libpath/classes/$extra_module.php");
					$extra_code_module=new $extra_module;
				} else { 
					$extra_code_module = "this";
				}
				$build_function = "\$order_post_result = \$extra_code_module->" . $extra_function .= ";";
				$eval_result=eval($build_function);
			}
		}
	}

	if (!$order_number){ // order number supplied means going through the new sagepay wrapper
		unset($_SESSION['cart']);
		unset($_SESSION['total_price']);
		unset($_SESSION['order_id']);
		unset($_SESSION['grand_total']);
		unset($_SESSION['shipping_service']);
		unset($_SESSION['user_selected_shipping_service']);
		unset($_SESSION['payment_method_in_use']);
		unset($_SESSION['gift_vouchers_complex_code']);
		unset($_SESSION['gift_vouchers_complex_total']);
		unset($_SESSION['voucher_text']);
		unset($_SESSION['order_number_set']);
	}
	$return .= "<p><a href=\"index.html\">Click Here to return to the home page.</a></p>";
	return $return;
}

function complete_order_after_payment_taken_new($order_number,$preorder){
	$internalSaleId=$order_number;
	global $db;

	if ($this->value("buy_requires_login")==1 || ($this->value("buy_requires_login")==2 && !$_SESSION['checkout_without_login'])){
		// user address data for the order if we havent logged them already
		global $user;
		$user_id=$user->value("id");
		$user_full_name=$user->value("full_name");
		$get_user_address_details="SELECT * from user WHERE id = $user_id";
		$all_user_details=$db->query($get_user_address_details);
		while ($uh=$db->fetch_array($all_user_details)){
			$user_billing_country=$uh['country'];
			$user_billing_address=$uh['address_1'] . " " . $uh['address_2'] . " " . $uh['address_3'] . " " . $uh['city'] . " " . $uh['county_or_state'] . " " . $uh['us_billing_state'] . " " . $uh['zip_or_postal_code'];
			if ($uh['same_as_billing_address']){
				$user_delivery_country=$user_billing_country;
				$user_delivery_address=$user_billing_address;
			} else {
				$user_delivery_country=$uh['delivery_country'];
				$user_delivery_address=$uh['delivery_address_1'] . " " . $uh['delivery_address_2'] . " " . $uh['delivery_address_3'] . " " . $uh['delivery_city'] . " " . $uh['delivery_county_or_state'] . " " . $uh['us_delivery_state'] . " " . $uh['delivery_zip_or_postal_code'];
			}
			$user_email=$uh['email_address'];
		}
		$log_user_details="INSERT INTO order_user_data (order_id,name,email,billing_address,billing_country,delivery_address,delivery_country) VALUES($internalSaleId,\"$user_full_name\",\"$user_email\",\"$user_billing_address\",\"$user_billing_country\",\"$user_delivery_address\",\"$user_delivery_country\")";
		$log_user_details_res=$db->query($log_user_details); 

		$this->user_details_for_emails['billing_address']=$user_billing_address;
		$this->user_details_for_emails['delivery_address']=$user_delivery_address;
		$this->user_details_for_emails['billing_country']=$user_billing_country;
		$this->user_details_for_emails['delivery_country']=$user_delivery_country;
	} else {
		// no login? Get the user details now..
		$sql="SELECT * from order_user_data WHERE order_id=$internalSaleId";
		$rv=$db->query($sql);
		while ($h=$db->fetch_array($rv)){
			$user_billing_country=$h['country'];
			$user_delivery_country=$h['delivery_country'];
			$user_billing_address=$h['billing_address']; // note: not set yet, must concatenate
			$user_delivery_address=$h['delivery_address']; // also not set, must concatenate
		}
	}

	// is it vatable? check now..
	$vatable=$db->field_from_record_from_id("countries",$user_delivery_country,"eu_country");
	if (!$vatable){$vatable=0;}

	// update order in the database to notify success
	if (!$user_delivery_country){
		$user_delivery_country=0;
	}
	// line below should identify preorder and set complete to something completely different...
	$complete_value=1;
	$paid_value=1;
	if ($preorder){ $complete_value=5; $paid_value=0; }
	$log_completed_payment_sql="UPDATE orders SET complete = $complete_value, paid=$paid_value, order_country = $user_delivery_country, vatable = $vatable WHERE id = $internalSaleId";
	$complete_order_result=$db->query($log_completed_payment_sql);

	// confirmation emails
	$this->mail_final_order_confirmation_to_customer_new($order_number);
	$this->mail_final_order_confirmation_to_office_new($order_number);

	$return .= "<p>Thank you for your purchase - this transaction is now complete.</p>";
	$return .= "<p>A confirmation of your order has been sent to you by email.</p>"; #matt# - $user_email would not pick up here??!

	// post the order to flight logistics
	//global $libpath;
	//require_once("$libpath/classes/flight_logistics_order_post.php");
	//$flight= new flight_logistics_order_post();
	//$flight_post_result = $flight->post_order_to_flight_logistics();
	// This is the NEW way of posting the order to flight logistics
	global $libpath;
	if ($this->value("external_order_post_function")){
		list($post_module,$post_function)=explode("::",$this->external_order_post_function);
		require_once("$libpath/classes/$post_module.php");
		$order_post_module=new $post_module;
		$build_function = "\$order_post_result = \$order_post_module->" . $post_function .= ";";
		$eval_result=eval($build_function);	
	}
	$record_payment_result=$this->record_payment();
	if ($this->value("run_extra_code_on_place_order_success")){
		$all_codes=explode(";",$this->run_extra_code_on_place_order_success);
		foreach ($all_codes as $each_code){
			$extra_module=""; $extra_function="";
			list($extra_module,$extra_function)=explode("::",$each_code);
			if ($extra_module){
				if ($extra_module != "this"){
					require_once("$libpath/classes/$extra_module.php");
					$extra_code_module=new $extra_module;
				} else { 
					$extra_code_module = "this";
				}
				$build_function = "\$order_post_result = \$extra_code_module->" . $extra_function .= ";";
				$eval_result=eval($build_function);
			}
		}
	}

	if (!$preorder){ // order number supplied means going through the new sagepay wrapper
		unset($_SESSION['cart']);
		unset($_SESSION['total_price']);
		unset($_SESSION['order_id']);
		unset($_SESSION['grand_total']);
		unset($_SESSION['shipping_service']);
		unset($_SESSION['user_selected_shipping_service']);
		unset($_SESSION['payment_method_in_use']);
	} else {
		unset($_SESSION['preorder_cart'][$preorder]);
	}
	$return .= "<p><a href=\"index.html\">Click Here to return to the home page.</a></p>";
	return $return;
}

function mail_final_order_confirmation_to_customer($order_number){
	if(!$order_number){
		$internalSaleId=$this->getInternalSaleId();
		if (!$internalSaleId){
			format_error("Error Code: 81881G",1,"","Could not find the internal order number from getInternalSaleId - was it set correctly?");
		}
	} else {
		$internalSaleId=$order_number;
	}
	global $db;

	$cust_mail_conf_temp = $this->value('customer_email_confirmation_template');
	$message=$db->field_from_record_from_id("templates",$cust_mail_conf_temp,"template");
	$cart_data_hash=array("allow_update" => "0", "run_modules" => "1","email" => "1", "order_number" => $internalSaleId);
	$print_order_detail=$this->view_cart_general($cart_data_hash);
	$print_order_detail=preg_replace("/src ?= ?\"(\w+) (\w+).jpg\"/","src=\"$1%20$2.jpg\"",$print_order_detail);
	$print_download_detail=$this->retrieve_download_urls_from_current_order($internalSaleId);
	$print_download_detail=$print_download_detail['text'];

	// global variables for email
	global $current_site;
	$site_name=$current_site['site_name'];

	if (!$this->value("buy_requires_login") || $this->value("buy_requires_login")==2 && $_SESSION['checkout_without_login']){
		// in this case we get the user details from order_user_data. The id in this table is in the ordered_by field of the orders table, which we can look up from the order number...
		//$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.ordered_by=order_user_data.id WHERE orders.id = $internalSaleId";
		$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.id=order_user_data.order_id WHERE orders.id = $internalSaleId";
		$res1=$db->query($order_user_sql) or format_error("Error 81871C",1,"","SQL: $order_user_sql<br />Error: " .->db_error());
		$total_rows=$db->num_rows($res1);
		if (!$total_rows){
			print "Error: no rows returned from query of $order_user_sql !!!!";
		}
		while ($j=mysql_fetch_array($res1)){
			$user_email_address=$j['email'];
			if ($j['name']){
				$user_name=$j['name'];
			} else {
				$user_name=$j['first_name'] . " " . $j['second_name'];
			}
			$billing_address=$j['address_1'] .", ";
			if ($j['address_2']){ $billing_address.=$j['address_2'].", ";}
			if ($j['address_3']){ $billing_address.=$j['address_3'].", ";}
			if ($j['city']){ $billing_address.=$j['city'].", ";}
			if ($j['county_or_state']){ $billing_address.=$j['county_or_state'].", ";}
			if ($j['zip_or_postal_code']){ $billing_address.=$j['zip_or_postal_code'].", ";}
			if ($j['country']){ $billing_address.=$j['country'].", ";}
			$delivery_address=$j['delivery_address_1'] .", ";
			if ($j['delivery_address_2']){ $delivery_address.=$j['address_2'].", ";}
			if ($j['delivery_address_3']){ $delivery_address.=$j['address_3'].", ";}
			if ($j['delivery_city']){ $delivery_address.=$j['city'].", ";}
			if ($j['delivery_county_or_state']){ $delivery_address.=$j['county_or_state'].", ";}
			if ($j['delivery_zip_or_postal_code']){ $delivery_address.=$j['zip_or_postal_code'].", ";}
			if ($j['delivery_country']){ $delivery_address.=$j['country'].", "; }
			if ($j['same_as_billing_address']){ $delivery_address="Same as billing address"; }
		}

		$mail_to=$user_email_address;
		$message=str_replace("{=name}",$user_name,$message);
		$message=str_replace("{=site_name}",$site_name,$message);
		$message=str_replace("{=order_details}",$print_order_detail,$message);
		$message=str_replace("{=download_details}",$print_download_detail,$message);
		$message=str_replace("{=order_id}",$internalSaleId,$message);
		$message=str_replace("{=address}",$address,$message);
		$message=str_replace("{=delivery_address}",$delivery_address,$message);
		$user_details_string="Address: ".$billing_address."<br />Delivery Address: " . $delivery_address;
		$message=str_replace("{=user_details}",$user_details_string,$message);
	} else {
		global $user;
		$current_user=$user->value("id");
		$user_sql="SELECT * FROM user WHERE id = $current_user";
		$result=$db->query($user_sql);
		while ($h=mysql_fetch_array($result,MYSQL_ASSOC)){
			$mail_to=$h['email_address'];
			$first_name=$h['first_name'];
			$second_name=$h['second_name'];
			$message=str_replace("{=name}","$first_name $second_name",$message);
			$message=str_replace("{=site_name}",$site_name,$message);
			$message=str_replace("{=order_details}",$print_order_detail,$message);
			$message=str_replace("{=download_details}",$print_download_detail,$message);
			$message=str_replace("{=order_id}",$internalSaleId,$message);
			//$message .= $download_urls_text . "\n\n";
		}
	}

	// now send a mail to the user, so get the user details and put it all togeter
	$headers="Content-type:text/html; charset=\"UTF-8\"\r\n";
	$headers .="MIME-Version: 1.0\r\n";
	$headers .= "From: \"".$this->value("mail_orders_from_name")."\" <" . $this->value("mail_orders_from_address") . ">\r\n";
	//Bcc: mattplatts@gmail.com\r\nContent-type:text/html\r\n\r\n";
	$subject=$this->value("customer_email_subject");
	//print "now mailing $mail_to with subject $subject and message<br />$message";
	$mail_send_result=mail($mail_to,$subject,$message,$headers) or die("Mail send error!");
	//print "so did the mail go at all? Result was $mail_send_result";
	return;
}

function mail_final_order_confirmation_to_customer_new($order_number){
	$internalSaleId=$order_number;
	if (!$internalSaleId){
		format_error("Error Code: 81881G-O9-N",1,"","Could not find the internal order number from getInternalSaleId - was it set correctly?");
	}
	global $db;

	$cust_mail_conf_temp = $this->value('customer_email_confirmation_template');
	$cart_data_hash=array("allow_update" => "0", "run_modules" => "1","email" => "1", "order_number" => $internalSaleId, "show_preorders" => 0);
	if ($order_number==$_SESSION['order_id']){
		$print_order_detail=$this->view_cart_general($cart_data_hash);
	} else {
		$print_order_detail=$this->view_preorder_cart_separates($cart_data_hash);
		$cust_mail_conf_temp = $this->value('customer_preorder_email_confirmation_template');
	}
	$message=$db->field_from_record_from_id("templates",$cust_mail_conf_temp,"template");
	$print_order_detail=preg_replace("/src ?= ?\"(\w+) (\w+).jpg\"/","src=\"$1%20$2.jpg\"",$print_order_detail);
	$print_download_detail=$this->retrieve_download_urls_from_current_order($internalSaleId);
	$print_download_detail=$print_download_detail['text'];

	// global variables for email
	global $current_site;
	$site_name=$current_site['site_name'];

	if (!$this->value("buy_requires_login") || $this->value("buy_requires_login")==2 && $_SESSION['checkout_without_login']){
		// in this case we get the user details from order_user_data. The id in this table is in the ordered_by field of the orders table, which we can look up from the order number...
		//$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.ordered_by=order_user_data.id WHERE orders.id = $internalSaleId";
		$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.id=order_user_data.order_id WHERE orders.id = $internalSaleId";
		$res1=$db->query($order_user_sql) or format_error("Error 81871C",1,"","SQL: $order_user_sql<br />Error: " .->db_error());
		$total_rows=$db->num_rows($res1);
		if (!$total_rows){
			print "Error: no rows returned from query of $order_user_sql !!!!";
		}
		while ($j=mysql_fetch_array($res1)){
			$user_email_address=$j['email'];
			if ($j['name']){
				$user_name=$j['name'];
			} else {
				$user_name=$j['first_name'] . " " . $j['second_name'];
			}
			$billing_address=$j['address_1'] .", ";
			if ($j['address_2']){ $billing_address.=$j['address_2'].", ";}
			if ($j['address_3']){ $billing_address.=$j['address_3'].", ";}
			if ($j['city']){ $billing_address.=$j['city'].", ";}
			if ($j['county_or_state']){ $billing_address.=$j['county_or_state'].", ";}
			if ($j['zip_or_postal_code']){ $billing_address.=$j['zip_or_postal_code'].", ";}
			if ($j['country']){ $billing_address.=$j['country'].", ";}
			$delivery_address=$j['delivery_address_1'] .", ";
			if ($j['delivery_address_2']){ $delivery_address.=$j['address_2'].", ";}
			if ($j['delivery_address_3']){ $delivery_address.=$j['address_3'].", ";}
			if ($j['delivery_city']){ $delivery_address.=$j['city'].", ";}
			if ($j['delivery_county_or_state']){ $delivery_address.=$j['county_or_state'].", ";}
			if ($j['delivery_zip_or_postal_code']){ $delivery_address.=$j['zip_or_postal_code'].", ";}
			if ($j['delivery_country']){ $delivery_address.=$j['country'].", "; }
			if ($j['same_as_billing_address']){ $delivery_address="Same as billing address"; }
		}

		$mail_to=$user_email_address;
		$message=str_replace("{=name}",$user_name,$message);
		$message=str_replace("{=site_name}",$site_name,$message);
		$message=str_replace("{=order_details}",$print_order_detail,$message);
		$message=str_replace("{=download_details}",$print_download_detail,$message);
		$message=str_replace("{=order_id}",$internalSaleId,$message);
		$message=str_replace("{=address}",$address,$message);
		$message=str_replace("{=delivery_address}",$delivery_address,$message);
		$user_details_string="Address: ".$billing_address."<br />Delivery Address: " . $delivery_address;
		$message=str_replace("{=user_details}",$user_details_string,$message);
	} else {
		global $user;
		$current_user=$user->value("id");
		$user_sql="SELECT * FROM user WHERE id = $current_user";
		$result=$db->query($user_sql);
		while ($h=mysql_fetch_array($result,MYSQL_ASSOC)){
			$mail_to=$h['email_address'];
			$first_name=$h['first_name'];
			$second_name=$h['second_name'];
			$message=str_replace("{=name}","$first_name $second_name",$message);
			$message=str_replace("{=site_name}",$site_name,$message);
			$message=str_replace("{=order_details}",$print_order_detail,$message);
			$message=str_replace("{=download_details}",$print_download_detail,$message);
			$message=str_replace("{=order_id}",$internalSaleId,$message);
			//$message .= $download_urls_text . "\n\n";
		}
	}

	// now send a mail to the user, so get the user details and put it all togeter
	$headers="Content-type:text/html\r\n";
	$headers .= "From: \"".$this->value("mail_orders_from_name")."\" <" . $this->value("mail_orders_from_address") . ">\r\n";
	//Bcc: mattplatts@gmail.com\r\nContent-type:text/html\r\n\r\n";
	$subject=$this->value("customer_email_subject");
	//print "now mailing $mail_to with subject $subject and message<br />$message";
	$mail_send_result=mail($mail_to,$subject,$message,$headers) or die("Mail send error!");
	//print "so did the mail go at all? Result was $mail_send_result";
	return;
}

function mail_final_order_confirmation_to_office(){

	global $db;

	$internalSaleId=$this->getInternalSaleId();
	if (!$internalSaleId){
		format_error("Error Code: 81881G-O-N",1,"","Could not find the internal order number from getInternalSaleId - was it set correctly?");
	}

	$mail_conf_temp = $this->value('email_confirmation_template');
	$message=$db->field_from_record_from_id("templates",$mail_conf_temp,"template");
	$cart_data_hash=array("allow_update" => "0", "run_modules" => "1","email" => "1");
	$print_order_detail=$this->view_cart_general($cart_data_hash);
	$print_order_detail=preg_replace("/src ?= ?\"(\w+) (\w+).jpg\"/","src=\"$1%20$2.jpg\"",$print_order_detail);

	// global variables for email
	global $current_site;
	$site_name=$current_site['site_name'];

	if (!$this->value("buy_requires_login") || $this->value("buy_requires_login")==2 && $_SESSION['checkout_without_login']){
		// in this case we get the user details from order_user_data. The id in this table is in the ordered_by field of the orders table, which we can look up from the order number...
		//$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.ordered_by=order_user_data.id WHERE orders.id = $internalSaleId";
		$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.id=order_user_data.order_id WHERE orders.id = $internalSaleId";
		$res1=$db->query($order_user_sql) or format_error("Error 81871O",1,"","SQL: $order_user_sql<br />Error: " .->db_error());
		$total_rows=$db->num_rows($res1);
		if (!$total_rows){
			print "Error: no rows returned from query of $order_user_sql !!!!";
		}
		while ($j=mysql_fetch_array($res1)){
			$user_email_address=$j['email'];
			if ($j['name']){
				$user_name=$j['name'];
			} else {
				$user_name=$j['first_name'] . " " . $j['second_name'];
			}
			$billing_address=$j['address_1'] .", ";
			if ($j['address_2']){ $billing_address.=$j['address_2'].", ";}
			if ($j['address_3']){ $billing_address.=$j['address_3'].", ";}
			if ($j['city']){ $billing_address.=$j['city'].", ";}
			if ($j['county_or_state']){ $billing_address.=$j['county_or_state'].", ";}
			if ($j['zip_or_postal_code']){ $billing_address.=$j['zip_or_postal_code'].", ";}
			if ($j['country']){ $billing_address.= $db->field_from_record_from_id("countries",$j['country'],"Name") .", ";}
			$delivery_address=$j['delivery_address_1'] .", ";
			if ($j['delivery_address_2']){ $delivery_address.=$j['delivery_address_2'].", ";}
			if ($j['delivery_address_3']){ $delivery_address.=$j['delivery_address_3'].", ";}
			if ($j['delivery_city']){ $delivery_address.=$j['delivery_city'].", ";}
			if ($j['delivery_county_or_state']){ $delivery_address.=$j['delivery_county_or_state'].", ";}
			if ($j['delivery_zip_or_postal_code']){ $delivery_address.=$j['delivery_zip_or_postal_code'].", ";}
			if ($j['delivery_country']){ $delivery_address.=$db->field_from_record_from_id("countries",$j['delivery_country'],"Name") . ","; }
			if ($j['same_as_billing_address']){ $delivery_address="Same as billing address"; }
		}

		$mail_to=$user_email_address;
		$message=str_replace("{=name}",$user_name,$message);
		$message=str_replace("{=site_name}",$site_name,$message);
		$message=str_replace("{=order_details}",$print_order_detail,$message);
		$message=str_replace("{=order_id}",$internalSaleId,$message);
		$message=str_replace("{=address}",$address,$message);
		$message=str_replace("{=delivery_address}",$delivery_address,$message);
		$message=str_replace("{=user_details}","Address: ".$billing_address."<br />Delivery Address: " . $delivery_address,$message);
	} else {
		$user_billing_details=$this->user_details_for_emails['billing_address'] . " " . $this->user_details_for_emails['billing_country'];
		$user_delivery_details=$this->user_details_for_emails['delivery_address'] . " " . $this->user_details_for_emails['delivery_country'];
		$full_user_details="Customer Billing Address: $user_billing_details<br>";
		$full_user_details .= "Customer Delivery Address: $user_delivery_details";
		global $user;
		$current_user=$user->value("id");
		$user_sql="SELECT * FROM user WHERE id = $current_user";
		$result=$db->query($user_sql);
		while ($h=mysql_fetch_array($result,MYSQL_ASSOC)){
			$mail_to=$h['email_address'];
			$first_name=$h['first_name'];
			$second_name=$h['second_name'];
			$message=str_replace("{=name}","$first_name $second_name",$message);
			$message=str_replace("{=site_name}",$site_name,$message);
			$message=str_replace("{=order_details}",$print_order_detail,$message);
			$message=str_replace("{=order_id}",$internalSaleId,$message);
			$message=str_replace("{=user_details}",$full_user_details,$message);
			$message=str_replace("{=billing_address}",$user_billing_details,$message);
			$message=str_replace("{=delivery_address}",$user_delivery_details,$message);
			//$message .= $download_urls_text . "\n\n";
		}
	}

	$headers = "Content-type:text/html\r\n";
	$headers .= "From: \"".$this->value("mail_orders_from_name")."\" <" . $this->value("mail_orders_from_address") . ">\r\n";
	$mail_send_result=mail($this->value("mail_orders_to"),$this->value("mail_orders_subject"),$message,$headers);
	//print "Mail send result to office was $mail_send_result";
	return;
}

function mail_final_order_confirmation_to_office_new($order_number){
	$internalSaleId=$order_number;
	if (!$internalSaleId){
		format_error("Error Code: 81881G-O",1,"","Could not find the internal order number from getInternalSaleId - was it set correctly?");
	}
	global $db;

	$mail_conf_temp = $this->value('email_confirmation_template');
	$message=$db->field_from_record_from_id("templates",$mail_conf_temp,"template");
	$cart_data_hash=array("allow_update" => "0", "run_modules" => "1","email" => "1", "order_number" => $internalSaleId, "show_preorders" => 0);
	if ($order_number==$_SESSION['order_id']){
		$print_order_detail=$this->view_cart_general($cart_data_hash);
		$order_type="regular";
	} else {
		$print_order_detail=$this->view_preorder_cart_separates($cart_data_hash);
		$order_type="preorder";
	}
	$print_order_detail=preg_replace("/src ?= ?\"(\w+) (\w+).jpg\"/","src=\"$1%20$2.jpg\"",$print_order_detail);

	// global variables for email
	global $current_site;
	$site_name=$current_site['site_name'];

	if (!$this->value("buy_requires_login") || $this->value("buy_requires_login")==2 && $_SESSION['checkout_without_login']){
		// in this case we get the user details from order_user_data. The id in this table is in the ordered_by field of the orders table, which we can look up from the order number...
		//$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.ordered_by=order_user_data.id WHERE orders.id = $internalSaleId";
		$order_user_sql="SELECT * from orders INNER JOIN order_user_data ON orders.id=order_user_data.order_id WHERE orders.id = $internalSaleId";
		$res1=$db->query($order_user_sql) or format_error("Error 81871O",1,"","SQL: $order_user_sql<br />Error: " .->db_error());
		$total_rows=$db->num_rows($res1);
		if (!$total_rows){
			print "Error: no rows returned from query of $order_user_sql !!!!";
		}
		while ($j=mysql_fetch_array($res1)){
			$user_email_address=$j['email'];
			if ($j['name']){
				$user_name=$j['name'];
			} else {
				$user_name=$j['first_name'] . " " . $j['second_name'];
			}
			$billing_address=$j['address_1'] .", ";
			if ($j['address_2']){ $billing_address.=$j['address_2'].", ";}
			if ($j['address_3']){ $billing_address.=$j['address_3'].", ";}
			if ($j['city']){ $billing_address.=$j['city'].", ";}
			if ($j['county_or_state']){ $billing_address.=$j['county_or_state'].", ";}
			if ($j['zip_or_postal_code']){ $billing_address.=$j['zip_or_postal_code'].", ";}
			if ($j['country']){ $billing_address.= $db->field_from_record_from_id("countries",$j['country'],"Name") .", ";}
			$delivery_address=$j['delivery_address_1'] .", ";
			if ($j['delivery_address_2']){ $delivery_address.=$j['delivery_address_2'].", ";}
			if ($j['delivery_address_3']){ $delivery_address.=$j['delivery_address_3'].", ";}
			if ($j['delivery_city']){ $delivery_address.=$j['delivery_city'].", ";}
			if ($j['delivery_county_or_state']){ $delivery_address.=$j['delivery_county_or_state'].", ";}
			if ($j['delivery_zip_or_postal_code']){ $delivery_address.=$j['delivery_zip_or_postal_code'].", ";}
			if ($j['delivery_country']){ $delivery_address.=$db->field_from_record_from_id("countries",$j['delivery_country'],"Name") . ","; }
			if ($j['same_as_billing_address']){ $delivery_address="Same as billing address"; }
		}

		$mail_to=$user_email_address;
		$message=str_replace("{=name}",$user_name,$message);
		$message=str_replace("{=site_name}",$site_name,$message);
		$message=str_replace("{=order_details}",$print_order_detail,$message);
		$message=str_replace("{=order_id}",$internalSaleId,$message);
		$message=str_replace("{=address}",$address,$message);
		$message=str_replace("{=delivery_address}",$delivery_address,$message);
		$message=str_replace("{=user_details}","Address: ".$billing_address."<br />Delivery Address: " . $delivery_address,$message);
	} else {
		$user_billing_details=$this->user_details_for_emails['billing_address'] . " " . $this->user_details_for_emails['billing_country'];
		$user_delivery_details=$this->user_details_for_emails['delivery_address'] . " " . $this->user_details_for_emails['delivery_country'];
		$full_user_details="Customer Billing Address: $user_billing_details<br>";
		$full_user_details .= "Customer Delivery Address: $user_delivery_details";
		global $user;
		$current_user=$user->value("id");
		$user_sql="SELECT * FROM user WHERE id = $current_user";
		$result=$db->query($user_sql);
		while ($h=mysql_fetch_array($result,MYSQL_ASSOC)){
			$mail_to=$h['email_address'];
			$first_name=$h['first_name'];
			$second_name=$h['second_name'];
			$message=str_replace("{=name}","$first_name $second_name",$message);
			$message=str_replace("{=site_name}",$site_name,$message);
			$message=str_replace("{=order_details}",$print_order_detail,$message);
			$message=str_replace("{=order_id}",$internalSaleId,$message);
			$message=str_replace("{=user_details}",$full_user_details,$message);
			$message=str_replace("{=billing_address}",$user_billing_details,$message);
			$message=str_replace("{=delivery_address}",$user_delivery_details,$message);
			//$message .= $download_urls_text . "\n\n";
		}
	}

	$headers = "Content-type:text/html\r\n";
	$headers .= "From: \"".$this->value("mail_orders_from_name")."\" <" . $this->value("mail_orders_from_address") . ">\r\n";
	$mail_send_result=mail($this->value("mail_orders_to"),$this->value("mail_orders_subject"),$message,$headers);
	//print "Mail send result to office was $mail_send_result";
	return;
}

function view_cart_print_product_attributes($item){
	$return="";
	if ($_SESSION['cart'][$item]['attributes']){
		$attribute_display_css="block";
		if ($this->value("hide_product_attributes_in_cart")){
			$return .= "<br /><a href=\"Javascript:showDiv('attributediv')\" class=\"product_attributes_view_link\">Click here to view product attributes:</a>";
			$attribute_display_css="none";
		}
		$return .= "<div id=\"attributediv\" style=\"display:$attribute_display_css; font-size:12px;\">";
		$attribute_item_count=1;
		$total_attribute_items=count($_SESSION['cart'][$item]['attributes']);
		foreach ($_SESSION['cart'][$item]['attributes'] as $each_attribute){
		//$attributename => $attributevalue
			if ($total_attribute_items>1){
				$return .= "<b>$attribute_item_count: </b>";
			}
			foreach ($each_attribute as $attributename => $attributevalue){
				$return .= "<b>" . $attributename . ": </b> " . $attributevalue['value'] . " (Quantity: " . $attributevalue['quantity'] . ") <a href=\"site.php?action=remove_cart_attribute&pid=$item&attribute=$attributename&attributevalue=".$attributevalue['value']."\" class=\"remove_attribute_link\">Remove</a><br />";
			}
			$attribute_item_count++;
		}
		/* old attribute code
		foreach ($_SESSION['cart'][$item]['attributes'] as $attributename => $attributevalue){
			$return .= "<b>" . $attributename . ":</b> " . $attributevalue . "<br />";
		}
		*/
		if ($this->value("hide_product_attributes_in_cart")){
			$return .= "<a href=\"Javascript:hideDiv('attributediv')\" class=\"product_attributes_hide_link\">Hide attributes</a><br /></div>";
		} else {
			$return .= "</div>";
		}
	}
	return $return;
}

function remove_cart_attribute($productid,$attribute,$value){
	// how many are in there?
	//print "<p>Att is $attribute</p><pre>";
	$rebuild_attribute_array=array();
	$qty_removed=0;
	foreach ($_SESSION['cart'][$productid]['attributes'] as $product_attributes){
		$attribute_count=0;
		//print "Product attributes is " . "\n";
		//var_dump($product_attributes);
		foreach ($product_attributes as $v => $each_attribute){
			//print "Each atrribute is \n" . $each_attribute;
			//var_dump($each_attribute);
			//print "And value is \n";
			//var_dump($each_attribute['value']);
			if ($each_attribute['value']!=$value){
				//print "---- adding " . $each_attribute['value']. " to rebuild!";
				$newarray[$v]=$each_attribute;
				array_push($rebuild_attribute_array,$newarray);	
			} else {
				//print "---- not adding " . $each_attribute['value']. " to rebuild\n\n";
				$qty_removed=$each_attribute['quantity'];
				$new_total_qty=$_SESSION['cart'][$productid]['quantity']-$qty_removed;
				//print "new total qty is " . $new_total_qty;
				if ($new_total_qty>=1){
					$_SESSION['cart'][$productid]['quantity']=$new_total_qty;
				} else {
					$this->remove_from_cart($productid);					
					$entire_item_removed=1;
				}
			}
		$attribute_count++;
		}
	}
	//print "<hr>";
	//print "Rebuild is \n";
	//var_dump($rebuild_attribute_array);
	//print "Original was \n";
	//var_dump($_SESSION['cart'][$productid]['attributes']);
	if (!$entire_item_removed){
		$_SESSION['cart'][$productid]['attributes']=$rebuild_attribute_array;
	}
	return $return;
}

function create_download_urls(){
	global $db;
	$internalSaleId=$this->getInternalSaleId();
	$download_urls_array=array();
	$select_order_products_sql="SELECT * from order_products where order_id = $internalSaleId";
	$res=$db->query($select_order_products_sql);
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$product_id=$h['id'];
		$separator="-";
		$uid=md5(uniqid(mt_rand(), true));
		$download_url=$product_id.$separator.$uid;
		$insert_download_sql="INSERT INTO download_urls (id,order_id,product_data_id,download_url) values(\"\",$internalSaleId,$product_id,\"$download_url\")";
		$insert_url_res=mysql_query($insert_download_sql) or die("Cant run $insert_download_sql: " . ->db_error());

		// get product title
		    $get_title_sql="SELECT " . $this->value("product_title_fields") . " FROM products WHERE id = " . $h['product_id'];
		    $title_res=mysql_query($get_title_sql) or die (->db_error());
		    while ($j=mysql_fetch_array($title_res,MYSQL_ASSOC)){
			$product_title = $j['title'];
		    }
		    if ($product_title != "Golden Account" && $product_title != "Test Account"){
			$download_urls_array['products'][$product_id]['title']=$product_title;
			$download_urls_array['products'][$product_id]['download_url']=$download_url . ".zip";
			//$download_urls_array['products'][$product_id]['text'] .= "$product_title\nhttp://www.theheroesofwoodstockdownloads.com/downloads/$download_url.zip\n\n";
		} else {
			//print "<p><b>$product_title:</b><br />Your passcode for the golden account is : $download_url</p>";
			$goldsql="INSERT INTO golden_account_codes (userid,golden_account_code) values (".$_COOKIE['login'].",$download_url)";
			$goldres=$db->query($goldsql);
			$download_urls_array['golden_account_url']=$download_url;
			//$download_urls_text .= "$product_title pass code:\n$download_url\n\n";
		}
	}
	return $download_urls_array;
}

function retrieve_download_urls_from_current_order(){
	global $db;
	$internalSaleId=$this->getInternalSaleId();
	$select_order_products_sql="SELECT order_products.*,products.category FROM order_products INNER JOIN products on order_products.product_id=products.id WHERE order_products.order_id = $internalSaleId AND products.is_download = 1";
	$res=$db->query($select_order_products_sql);
	//print "<p>You can download your files from the following URLs:<br />(NB: If you have purchased the golden Account your passcode will be listed below in place.)</p>";
	//print "<div style=\"margin:5px; padding:15px; border-width:1px; border-color:#ffffff; border-style:dashed;\">";
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$product_id=$h['id'];
		$separator="-";
		$uid=md5(uniqid(mt_rand(), true));
		$download_url=$product_id.$separator.$uid;
		$insert_download_sql="INSERT INTO download_urls (id,order_id,product_data_id,download_url) values(\"\",$internalSaleId,$product_id,\"$download_url\")";
		$insert_url_res=mysql_query($insert_download_sql) or die("Cant run $insert_download_sql: " . ->db_error());

		// get product title
		$get_title_sql="SELECT * FROM products WHERE id = " . $h['product_id'] . " AND is_download = 1";
		$product_title="";
		$title_res=$db->query($get_title_sql) or die (->db_error());
		while ($j=$db->fetch_array($title_res)){
			$product_title = $this->get_product_title($j);
		}
		if (!$product_title){ continue; }
		if ($product_title != "Golden Account" && $product_title != "Test Account"){
			/* is the product a voucher? */
			if ($h['category']==3){ 
				continue;
				// it's a voucher innit
			}
			$download_urls_html .= "<p><b>$product_title:</b><br /><a href=\"".HTTP_PATH."/downloads/$download_url.zip\">".HTTP_PATH."/downloads/$download_url.zip</a></p>";
			$download_urls_text .= "$product_title\n".HTTP_PATH."/downloads/$download_url.zip\n\n";
			/* end is the product a voucher? */
		} else {
			$download_urls_html .= "<p><b>$product_title:</b><br />Your passcode for the golden account is : $download_url</p>";
			$goldsql="INSERT INTO golden_account_codes (userid,golden_account_code) values (".$_COOKIE['login'].",$download_url)";
			$goldres=mysql_query($goldsql);
			$download_urls_text .= "$product_title pass code:\n$download_url\n\n";
		}
	$count_downloads++;
	}
	$return_array['html']=$download_urls_html;
	$return_array['text']=$download_urls_text;
	return $return_array;
}

function run_checkout_modules(){

	$module_totals=array();
	$add_to_total=0;
	global $db;
	$sql="SELECT name,key_name,checkout_itemisation_text,class_file from checkout_modules WHERE active=1 ORDER BY ordering";
	$rv=$db->query($sql);
	while ($h=$db->fetch_array($rv)){
		$class_file=LIBPATH."/classes/".$h['class_file'];
		if ($_SESSION[$h['key_name']]['itemise_post_order_total']){ $this->set_value("post_order_total_values",1); continue; } 
		require_once($class_file);
		$checkout_mod_class = new $h['key_name'];
		$total = $checkout_mod_class->itemise_at_checkout(); 
		if (method_exists($checkout_mod_class, "post_text")){
			if ($checkout_mod_class->post_text()){
				$module_totals[$h['key_name']]['post_text']=$checkout_mod_class->post_text();
			}
		}

		if ($total && $total != 0 || ($checkout_mod_class->value("always_display_if_post_text"))){ // this hides any module with a value of 0
			if (method_exists($checkout_mod_class, "post_text")){
				$module_totals[$h['key_name']]['post_text']=$checkout_mod_class->post_text();
			}
			$module_totals[$h['key_name']]['total']=sprintf("%4.2f",$total);
			$module_totals[$h['key_name']]['name']=$h['name'];
			$module_totals[$h['key_name']]['checkout_itemisation_text']=$h['checkout_itemisation_text'];
			if ($checkout_mod_class->value("add_to_total")){
				$add_to_total = $add_to_total + $total;
			}
		}
	}
	$module_totals['amount_to_add_to_total']=$add_to_total;
	return $module_totals;
}
function run_checkout_modules_post(){

	$module_totals=array();
	$add_to_total=0;
	global $db;
	$sql="SELECT name,key_name,checkout_itemisation_text,class_file from checkout_modules WHERE active=1 ORDER BY ordering";
	$rv=$db->query($sql);
	while ($h=$db->fetch_array($rv)){
		$class_file=LIBPATH."/classes/".$h['class_file'];
		if (!$_SESSION[$h['key_name']]['itemise_post_order_total']){ continue; } 
		require_once($class_file);
		$checkout_mod_class = new $h['key_name'];
		$total = $checkout_mod_class->itemise_at_checkout(); 
		if (method_exists($checkout_mod_class, "post_text")){
			$module_totals[$h['key_name']]['post_text']=$checkout_mod_class->post_text();
		}

		if ($total && $total != 0 || ($checkout_mod_class->value("always_display_if_post_text"))){ // this hides any module with a value of 0
			if (method_exists($checkout_mod_class, "post_text")){
				$module_totals[$h['key_name']]['post_text']=$checkout_mod_class->post_text();
			}
			$module_totals[$h['key_name']]['total']=sprintf("%4.2f",$total);
			$module_totals[$h['key_name']]['name']=$h['name'];
			$module_totals[$h['key_name']]['checkout_itemisation_text']=$h['checkout_itemisation_text'];
			if ($checkout_mod_class->value("add_to_total")){
				$add_to_total = $add_to_total + $total;
			}
		}
	}
	$module_totals['amount_to_add_to_post_total']=$add_to_total;
	return $module_totals;
}



function store_email_when_out_of_stock($product_id){

	if (!is_numeric($product_id)){ format_error("Bad Product Id",1); }
	global $db;
	global $user;

	// product title
	$sql="SELECT * from products where ID = $product_id";
	$prv=$db->query($sql);
	while ($prh=$db->fetch_array($prv)){
		$product_title=$this->get_product_title($prh);
	}

	if ($user->value("id")){
		// check if product already requested
		$sql="SELECT * from cart_out_of_stock_email_list where product = \"$product_id\" AND user = " . $user->value("id") . " AND mail_sent=0";
		$rv=$db->query($sql);
		if ($db->num_rows($rv)==0){
			$insert_sql="INSERT INTO cart_out_of_stock_email_list (user,product,date_requested,mail_sent) values(".$user->value('id').",$product_id,NOW(),0)";
			$do_insert=$db->query($insert_sql);
			$return_msg="<p><b>Product added to your email list:</b></p><p><b>$product_title</b></p><p>Thank you - $product_title has been added to your email request list. You will automatically be emailed when this product comes back into stock.</p>";
		} else {
			$return_msg="<p><b>Product added to your email list:</b></p><p><b>$product_title</b></p><p>Thank you - our records show that you have already requested an email notification for this product: $product_title. We will send you an email when this product is back in stock.</p>";
		}
	} else {
		$return_msg="<h3>Out Of Stock Item - Add To Email List</h3><p>Please note - $product_title is currently out of stock, however if you wish we can email you when this product comes into stock. You will need an account in order to receive emails from us.</p><p>Please either <a href=\"log_in.html\">Log In</a> or <a href=\"register.html\">Register</a> for an account with us in order to continue.</p>";
	}
	return $return_msg;
}

function get_best_shipping_rate($shipping_options){
	$shipping_country=$shipping_options['shipping_country'];
	$preorder=$shipping_options['preorder'];
	global $libpath;
	#$shipping_module="$libpath/classes/".$this->value("shipping_modules_installed") . ".php";
	$all_shipping_modules=explode(",",$this->value("shipping_modules_installed"));
	$best_shipping_rate="0.00";
	$shipping_rate_set=0;
	global $user;
	if (!$user->value("id") && ($this->value("buy_requires_login")==1 || $this->value("buy_requires_login")==2)){
		if (!stristr($_SERVER['QUERY_STRING'],"confirm_shipping") && !stristr($_SERVER['QUERY_STRING'],"ion=place_order")) { // will use the default form value
			//print $_SESSION['shipping_service'];
			//print "<br>";
			//print $_SESSION['shipping_amount'];

			// LSC HACK
			$best_shipping_quote=0;
			return $best_shipping_quote;
			// END LSCL HACK - the 2 PREVIOUS lines were in before anyuway
			//$best_shipping_quote="tbc";
			//return $best_shipping_quote;
		}
	}
	foreach ($all_shipping_modules as $each_shipping_module){
		if (!$each_shipping_module){ continue; }
		$shipping_module_file=$libpath."/classes/".$each_shipping_module.".php";
		if (file_exists($shipping_module_file)){
			include_once($shipping_module_file);
			$set_up_new_object_code="\$shipping = new " . $each_shipping_module . ";";
			$obj_result=eval($set_up_new_object_code);
			$shipping_options['buy_requires_login']=$this->value("buy_requires_login");
			$shipping_options['country_if_no_login']=$shipping_country;
			$shipping_options['preorder']=$preorder;
			$shipping_rate=$shipping->calculate_shipping_rate($shipping_options);
			//print "This one gives a rate of $shipping_rate!";
			if (is_numeric($shipping_rate)){
				if ($best_shipping_rate && $shipping_rate_set){
					if ($shipping_rate<=$best_shipping_rate){
						$best_shipping_rate=$shipping_rate;
						$_SESSION['shipping_service']=$each_shipping_module;
						$shipping_rate_set=1;
					}
				} else {
					$best_shipping_rate=sprintf("%4.2f",$shipping_rate);
					$shipping_rate_set=1;
				}
			}
		} else {
			format_error("Shipping module class file $each_shipping_module does not exist",1); 
		}
	}
	return $best_shipping_rate;
}

function load_specific_shipping_module($user_set){
	global $libpath;
	$shipping_module=$libpath."/classes/".$user_set; // compare to db entries first!
	$shipping_module_file=$shipping_module.".php";
	if (file_exists($shipping_module_file)){
		include_once($shipping_module_file);
		$set_up_new_object_code="\$shipping = new " . $user_set. ";";
		$obj_result=eval($set_up_new_object_code);
		$shipping_options['buy_requires_login']=$this->value("buy_requires_login");
		$shipping_options['country_if_no_login']=$shipping_country;
		$shipping_options['preorder']=$preorder; // not yet set!
		$shipping_rate=$shipping->calculate_shipping_rate($shipping_options);
	} else { 
		format_error("No file of $shipping_module_file exists!",1); 
	}
	if (is_numeric($shipping_rate)){ $shipping_rate=sprintf("%4.2f",$shipping_rate);}
	return $shipping_rate;
}

function print_shipping_options_form(){
	global $libpath;
	global $db;
	$return = "<h3 class=\"shopping_cart_section_header shipping_section_header\">Shipping Options</h3>";
	if (!$_SESSION['user_selected_shipping_service']){
		$return .= "<p>We have calculated shipping above by the cheapest option. You may select a different option below:</p>";
	}
	#$shipping_module="$libpath/classes/".$this->value("shipping_modules_installed") . ".php";
	$cart_template=$this->value("cart_template");
	$all_shipping_modules=explode(",",$this->value("shipping_modules_installed"));
	$return .= "<form action=\"site.php?action=update_shipping&amp;mt=$cart_template\" method=\"post\" name=\"shipping_options_form\"><table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" class=\"shipping_options_table\">";
	$shipping_modules_included=0;
	foreach ($all_shipping_modules as $each_shipping_module){
		$shipping_module_file=$libpath."/classes/".$each_shipping_module.".php";
		if (file_exists($shipping_module_file)){
			include_once($shipping_module_file);
			$set_up_new_object_code="\$shipping = new " . $each_shipping_module . ";";
			$obj_result=eval($set_up_new_object_code);
			$shipping_options['buy_requires_login']=$this->value("buy_requires_login");
			$shipping_options['country_if_no_login']=$shipping_country;
			$shipping_rate=$shipping->calculate_shipping_rate($shipping_options);
			if ($shipping_rate=="DISALLOW") {continue;}
			$shipping_modules_included++;
			$shipping_vars_sql="SELECT * from shipping_modules where class_file = \"$each_shipping_module\" AND active=1";
			$shipping_vars_rv=$db->query($shipping_vars_sql);
			$h=$db->fetch_array($shipping_vars_rv);
			$shipping_txt=$h['checkout_itemisation_text'];
			$shipping_extra_txt=$h['extra_information'];
			$return .= "<tr onmouseover=\"this.className='shipping_tr_over'\" onmouseout=\"this.className='shipping_tr_out'\"><td><input type=\"radio\" onclick=\"document.forms['shipping_options_form'].submit()\" name=\"shipping_service\" value=\"$each_shipping_module\"";
			if ($_SESSION['user_selected_shipping_service']==$each_shipping_module){ $return .= " checked"; }
			else if ($_SESSION['shipping_service']==$each_shipping_module){ $return .= " checked"; }
			$return .= "><td>".$shipping_txt."<br /><span class=\"shipping_extra_info_text\">$shipping_extra_txt</span></td><td style=\"padding-left:15px;\">".$this->value("default_currency_symbol").$shipping_rate."</td><td></td></tr>";
			if (is_numeric($shipping_rate)){
				if ($best_shipping_rate){
					if ($shipping_rate<=$best_shipping_rate){
						$best_shipping_rate=$shipping_rate;
					}
				} else {
					$best_shipping_rate=sprintf("%4.2f",$shipping_rate);
				}
			}
		} else {
			format_error("Shipping module file $each_shipping_module does not exist or is incorrectly installed",1); 
		}
	}
	$return.= "</table>";
	$return .= "</form>";
	//$return .= "<p>If you have selected a new shipping method please <a href=\"Javascript:document.forms['shipping_options_form'].submit()\">Update Your Order Total</a></p>\n";
	$return .= "<hr size=\"1\" />\n";
	if ($shipping_modules_included<=1){
		$return="";
	}
	return $return;
}

//Function to redirect browser
function redirect($url){
	if (!headers_sent()) {
		 header('Location: '.$url);
	} else {
		echo '<script type="text/javascript">';
		echo 'window.location.href="'.$url.'";';
		echo '</script>';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
		echo '</noscript>';
	}
}

function update_shipping($shipping_service){
	$_SESSION['user_selected_shipping_service']=$shipping_service;
	return $this->confirm_order();
}

function start_individual_payment(){
	$payment_amount=$_POST['payment_amount'];
	$_SESSION['individual_payment_amount']=$payment_amount;
	global $page;
	$return=$page->content_from_id(256);
	return $return;
}

function record_payment(){
	// records the regular web order as paid in the payments table
	global $db;
	global $user;
	$record_payment=0;
	$payment_method=$_SESSION['payment_method_in_use'];
	$payment_method=$db->field_from_record_from_id("payment_modules",$payment_method,"key_name");
	if ($payment_method=="sagepay_direct"){
		if ($this->value("record_payments_separately")){
			$record_payment=1;
		}
	}

	if ($record_payment){
		$sql="INSERT INTO payments (user,description,payment_amount,payment_date,order_number) VALUES(".$user->value("id").",\"Web Order - Immediate Payment via Credit Card - Order #".$_SESSION['order_id']." \",".$_SESSION['grand_total'].",NOW(),".$_SESSION['order_id'].")";
		$rv=$db->query($sql);
	}
}

function confirm_shipping(){
	global $user;
	global $db;

	$_SESSION['no_login_form_data']=$_POST;

	// 1 get shppping cart data to print as content
	$cart_template=$this->value("cart_template");
	$golden_account_active=$this->value("golden_account_active");
	if (!$_SESSION['cart']){
		$content="<p class=\"title\">Order Confirmation</p>";	
		$content .= "<p>There are no items in your shopping cart</p>";
		return $content;
	}
	$cart_data_hash=array("allow_update" => "0", "run_modules" => "1");
	$content .= $this->view_cart_general($cart_data_hash);
	
	// 2 look at alternative shippings
	$all_shipping_modules=explode(",",$this->value("shipping_modules_installed"));
	if (count($all_shipping_modules)>1 && ($this->value("buy_requires_login") && $user->value("id"))){
		$content .= $this->print_shipping_options_form();
	}
	$cart_form_action="site.php?action=place_order&mt=".$this->value("cart_template");
	$content .= "<br clear=\"all\" /><div class=\"shopping_cart_block continue_to_payment_block\"><p><span class=\"jc_button jc_button_160\"><a href=\"$cart_form_action\" style=\"font-weight:bold\">Continue to payment</a> &nbsp; </span></p><br clear=\"all\" /><p>Please review your order, and click above to continue to the payment pages.</p></div><br clear=\"all\" />";
	return $content;
}
function claim_vat_relief(){
	$_SESSION['vat_relief']=1;
	$_SESSION['vat_relief_reason']=$_REQUEST['vat_relief_reason'];
	if ($_SESSION['checkout_without_login']){
		$header_loc="site.php?action=place_order&mt=".$this->value("cart_template");
		header("Location: $header_loc");
	} else {
		header("Location: checkout.html");
	}
}
function remove_vat_relief(){
	$_SESSION['vat_relief']=0;
	$_SESSION['vat_relief_reason']="";
	if ($_SESSION['checkout_without_login']){
		$header_loc="site.php?action=place_order&mt=".$this->value("cart_template");
		header("Location: $header_loc");
	} else {
		header("Location: checkout.html");
	}
}
function explain_vat_relief(){
	global $page;
	$text=$page->content_from_id(257);
	return $text;
}

function test_mails(){
	$this->mail_final_order_confirmation_to_customer();
	$this->mail_final_order_confirmation_to_office();
	print "Emails sent";
}

function shopping_cart_orders_complete(){
	if (count($_SESSION['completed_orders'])==1){ $plural=""; }else { $plural="s";}
	$content="<p>Thank you for your order$plural.</p>";
	foreach ($_SESSION['completed_orders'] as $completed_order){
		$order .= "";
		if ($completed_order['status']=="OK"){
			$order="<p>Order number #".$completed_order['order_number'] . " has been paid successfully and will be sent out shortly.<br />You have been sent a confirmation of this order by email.</p>";
			$order .= "<p><a href=\"view_order_details/".$completed_order['order_number']."\">Click here to view this order online.</a></p>";
		} else if ($completed_order['status']=="REGISTERED"){
			$order="<p>Order number #".$completed_order['order_number'] . " has been authorised successfully. As this is a pre-order it will be sent out when it is in stock.<br />You have been sent a confirmation of this order by email.</p>";
			$order .= "<p><a href=\"view_order_details/".$completed_order['order_number']."\">Click here to view this order online.</a></p>";
		} else if ($completed_order['status']=="NOTAUTHED"){
			$order="<p>Order number #".$completed_order['order_number'] . " has not been authorised by your card company.</p>";
			$order .= "<p><a href=\"/checkout.html\">Click here to return to the checkout and try again.</a></p>";
		} else {
			$order="<p>Order number #".$completed_order['order_number'] . " has not been authorised by your card company.</p>";
			$order .= "<p><a href=\"/checkout.html\">Click here to return to the checkout and try again.</a></p>";
		}
		$content .= $order;
		$content .= "<hr>";

	}
	$content .= "<p><b>Questions?</b></p>";
	$content .= "<p>See our <a href=\"http://www.gonzomultimedia.co.uk/help_and_support.html\">help and support pages here</a>&nbsp;and feel free to <a href=\"http://www.gonzomultimedia.co.uk/contact.html\">contact us</a>&nbsp;if you have any questions about your order.</p>";
	return $content;
}

function paypal_pay_for_pre_order($order_id){
	$content = "<p><b>Order #$order_id</b></p>";
        global $db;
	//$sql = "SELECT products.ID as product_id,products.title,products.image,checkout_modules.id AS payment_method,products.full_description, order_products.quantity, order_products.price as bought_price, orders.grand_total, orders.shipping_total, artists.artist,orders.ordered_by, orders.non_account_order FROM^ order_products INNER JOIN products on products.ID = order_products.product_id INNER JOIN artists on products.artist=artists.id INNER JOIN orders on order_products.order_id = orders.id LEFT JOIN checkout_modules ON checkout_modules.key_name = orders.payment_method INNER JOIN product_formats ON products.format = product_formats.id WHERE order_products.order_id = $order_id"; 
	$sql = "SELECT products.ID as product_id,products.title,products.image,payment_modules.id AS payment_method,products.full_description, order_products.quantity, order_products.price as bought_price, orders.grand_total, orders.shipping_total, artists.artist,orders.ordered_by, orders.non_account_order, orders.paid,orders.preorder_date_shipped FROM orders 
INNER JOIN order_products ON orders.id = order_products.order_id 
LEFT JOIN products on products.id = order_products.product_id 
INNER JOIN artists on products.artist=artists.id 
LEFT JOIN payment_modules ON orders.payment_method = payment_modules.key_name 
INNER JOIN product_formats ON products.format = product_formats.id 
WHERE order_products.order_id = $order_id";
        $rv=$db->query($sql);
        $h=$db->fetch_array($rv);
        if ($h['ordered_by']){
                $user_id=$h['ordered_by'];
                $table="user";
        } else if ($h['non_account_order']){
                $user_id=$h['non_account_order'];
                $table="order_user_data";
        }
	$product_id=$h['product_id'];
	$qty=$h['quantity'];
	$product_name=$h['artist'] . " - " . $h['title'];
	$_SESSION['allow_add_once']=1;
	$this->add_to_cart($product_id,$qty);
	$_SESSION['allow_add_once']=0;
        $user_sql="SELECT first_name,second_name,email_address FROM $table WHERE id = " . $user_id;
        $user_rv=$db->query($user_sql);
        $user_h=$db->fetch_array($user_rv);
	$message .= "<p>Your recent order for $product_name is now available for purchase.</p>";
        $message .= "<table><tr><td valign=\"top\">";
        $message .= "<img src=\"http://www.gonzomultimedia.co.uk/images/product_images/web_quality/".$h['image']."\" width=\"200\" />";
        $message .= "</td><td>"."</td></tr></table>";
	$message .= "<p><b>Quantity ordered:</b> " . $qty . "</p>";
        $message .= "<p>The total amount payable as quoted to you previously is &pound;" . $h['grand_total'] . " (&pound;" . $h['bought_price'] . " + &pound;" . $h['shipping_total'] . " for shipping)</p>";
	$content .= $message;
	$content .= '<br /><hr size="1"><p>Click below to continue to the paypal web site to complete your payment:</p><p><a href="/site.php?action=load_payment_module"><img src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" align="left" style="margin-right:7px;" border=0></a></p>';
	$_SESSION['order_number_set']=$order_id;
	$_SESSION['order_id']=$order_id;
	$_SESSION['payment_method_in_use']=$h['payment_method'];
	$_SESSION['grand_total']=$h['grand_total'];
	$_SESSION['total_amount']=$h['total_amount'];
	$_SESSION['paypal_pay_for_pre_order']=1;
	$set_order_cookie = setcookie("order_id", $order_id,0,"/");
	global $user;
	if (!$user->value("id")){
		header("Location: /log_in.html");
		exit;
	}
	if ($h['preorder_date_shipped']>0 && $h['paid']){
		$content = "<p><b>Order #$order_id</b></p>";
		$content .= "This order has already been paid";
	}
	return $content;
}

function unable_to_edit_order(){
	$x_content ="<p>Sorry - as you have visited this site to pay for an existing pre-order, you cannot amend this order as it has already been placed.</p>";
	$x_content .= "<p>If you wish to order other items with this title, we will cancel your previous order and you can make a new order now. <a href=\"site.php?action=escape_pre_order_payment\">Cancel my pre-order and start a new order</a>. Your pre-order will remain in your current shopping basket.</p>";
	$x_content .= "<p>To continue and pay for your pre-order, <a href=\"/pre-order-paypal/".$_SESSION['order_id']."/\">Please click here</a></p>";
	return $x_content;	
}

function escape_pre_order_payment(){
	// SQL here to update the status to cancelled
	$order_id=$_SESSION['order_id'];
	$sql="UPDATE orders set status=\"4\" WHERE id = $order_id";
	global $db;
	//$rv=$db->query($sql);
	unset($_SESSION['paypal_pay_for_pre_order']);
	unset($_SESSION['grand_total']);
	unset($_SESSION['total_amount']);
	unset($_SESSION['order_id']);
	unset($_SESSION['order_number_set']);
	unset($_SESSION['payment_method_in_use']);
	$content = "<p class=\"dbf_para_info\">Your pre-order has been cancelled. This item is now in your shopping cart to be placed as a new order.</p>";
	$cart_view_options['allow_update']=1;
	$cart_view_options['run_modules']=0;
	$content .= $this->view_cart_general($cart_view_options);
	$content .= $this->order_and_browse_buttons();
	return $content;
}

function paypal_place_preorders_only(){
	require_once(LIBPATH . "/classes/shopping_cart/payment_modules/payment_module_paypal_express_checkout.php");
	$paypal_details=new payment_module_paypal_express_checkout();
	$content=$paypal_details->load_preorder_only_success();
	$return = $content; 
	return  $return;
}

function cancel_voucher(){
	require_once(LIBPATH . "/classes/shopping_cart/gift_vouchers_complex.php");
	$checkout_mod_class = new gift_vouchers_complex; 
	$checkout_mod_class->clear_down_vouchers();
	header("Location: checkout.html");	
	exit;
}

// END SHOPPING CART CLASS
}
/*

		unset($_SESSION['checkout_modules_add_to_total']);
		unset($_SESSION['total_of_all_orders']);
		unset($_SESSION['total_of_all_orders_inc']);
		unset($_SESSION['all_preorders_grand_total']);
		unset($_SESSION['preorder_shipping_amount']);
		unset($_SESSION['preorder_shipping_rate']);
		unset($_SESSION['preorder_total_price']);
		unset($_SESSION['preorder_checkout_modules_add_to_total']);
*/
?>
