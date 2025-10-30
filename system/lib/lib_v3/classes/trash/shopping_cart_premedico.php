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
			$this->customer_email_subject=$row['customer_email_subject'];
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
			$this->price_field=$row['price_field'];
			if ($row['calculate_price_field_by_function']){
				$this->price_field=call_user_func($row['calculate_price_field_by_function']);
			}
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
		$sql_select_list=$products_pk . ",".$this->product_title_fields.",".$this->price_field;
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
	$return .= "<p><b>Final Total: </b>" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</p>";
	$_SESSION['total_price']=sprintf("%4.2f",$running_total);
	return $return;
}

function total_items_in_cart(){
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$counter++;
	}

	return "does $counter match " . count($_SESSION['cart']);
}

function add_to_cart($productid,$quantity){
	global $db;
	if (!is_numeric($productid)){ format_error("Bad Product Id",1); }
	$return_content = "";
	$added_extra=0;
	if (!$quantity){$quantity=1;}
	if ($_SESSION['cart'][$productid]){$_SESSION['cart'][$productid]['quantity']++; $added_extra=1;} else { $_SESSION['cart'][$productid]['quantity']=$quantity;}
	if (!$added_extra || $this->value("allow_multiple_quantities")){
	// check for attributes
	$attr_sql="SELECT * from products_to_product_attributes INNER JOIN product_attributes ON products_to_product_attributes.attribute_id = product_attributes.id WHERE product_id = $productid";
	$attr_res=$db->query($attr_sql);
	while ($ah=$db->fetch_array($attr_res)){
		if ($_POST[str_replace(" ","_",$ah['attribute_name'])]){
			if (!$_SESSION['cart'][$productid]['attributes']){
				$_SESSION['cart'][$productid]['attributes'] = array();
				$new_attribute[$ah['attribute_name']]['value']=$_POST[str_replace(" ","_",$ah['attribute_name'])];
				$new_attribute[$ah['attribute_name']]['quantity']=1;
				array_push($_SESSION['cart'][$productid]['attributes'],$new_attribute);
				$attribute_added=1;
			} else {
				// do we already have this attribute in here?

				$attribute_array_count=0;
				foreach ($_SESSION['cart'][$productid]['attributes'] as $each_attribute){
					foreach ($each_attribute as $attributename => $attributevalue){
							if ($attributevalue['value']==$_POST[str_replace(" ","_",$ah['attribute_name'])]){
							$new_quantity=$attributevalue['quantity']+1;	
							$_SESSION['cart'][$productid]['attributes'][$attribute_array_count][$attributename]['quantity']=$new_quantity;
							$attribute_added=1;
							}
					}
				$attribute_array_count++;
				}
			}
			if (!$attribute_added){
				$new_attribute[$ah['attribute_name']]['value']=$_POST[str_replace(" ","_",$ah['attribute_name'])];
				$new_attribute[$ah['attribute_name']]['quantity']=1;
				array_push($_SESSION['cart'][$productid]['attributes'],$new_attribute);
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
	$return_content .= "<p class=\"cart_header\"><span class=\"product_added_text_product_title\"$>".$this->get_product_title($h). "</span> has been added to your order.</p>";
	if ($added_extra){ $return_content .= "<p class=\"cart_already_ordered_item_message\">As this item was already in this order we have added another one to the order.</p>\n";}
	} else {
		$return_content .= "<p>This download is already in your order.</p>";
	}
	return $return_content;
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

function remove_from_cart($productid){
        if (isset($_SESSION['cart'][$productid])){ unset($_SESSION['cart'][$productid]);}
        return "<p>Product deleted</p>";
}

function order_header(){
	return $return;
}

function get_category_breadcrumb($current_category){
	$categories=$this->get_categories_as_array($current_category);
	$categories = array_reverse($categories);
	$return = "<p class=\"breadcrumb_navigation\"><a href=\"shop.html\" class=\"breadcrumb_navigation_link\">Shop</a> &gt; ";
	$return .= join(" &gt; ",$categories);
	return $return;
}

function get_categories_as_array($current_category){
	$cart_template=$this->value("cart_template");
	$parent_category=$current_category;
	$parents=array();
	$i=0;
	while ($parent_category) {
		@list($parent,$typename,$id)=explode("|",$this->get_parent_category($parent_category));
		if ($id==1){
			array_push ($parents,"<a href=\"list_categories.html\">$typename</a>");
		} else {
			array_push ($parents,"<a href=\"site.php?content=84&amp;category_id=$id&amp;action=cart_categories_browse&mt=$cart_template\">$typename</a>");
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
		@list($parent,$typename,$id)=explode("|",$this->get_parent_category($parent_category));
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
	global $db;
        $sql= "SELECT id,parent,product_type from ".$this->value("categories_table")." WHERE id = $current_category";
        $parent=NULL;
        $res=$db->query($sql) or die(->db_error());
        while ($h=$db->fetch_array($res,MYSQL_ASSOC)){
                $parent=$h['parent'] . "|" . $h['product_type'] . "|" . $h['id'];
        }
        return $parent;
}

function display_mysql_cart_error($->db_error_message){
        print "An error has occurred in the mysql: $->db_error_message";
        return 1;
}

function order_and_browse_buttons(){
	return; // turned off for medico
        $cart_template=$this->value("cart_template");
        $order_more_links_to=$this->value("order_more_links_to");

	$have_products=0;
	foreach ($_SESSION['cart'] as $cartitem){
		$have_products++;
	}

	if ($have_products){ 
		$return .= "<p>Please select an option below:</p>";
		$return .= "<p><span class=\"order_more_button\" ><a href=\"$order_more_links_to\">Return To Catalogue</a></span></p>";
	} else {
		$return .= "<p><span class=\"order_more_button\" ><a href=\"Javascript:history.go(-1)\">Return To Catalogue</a></span></p>";

	}


	if ($have_products){
		$return .= "<p><span class=\"place_order_button\" ><a href=\"checkout.html\">Place this order now</a></span></p>";
	}
       // old url $return .= "<span class=\"place_order_button\" ><a href=\"site.php?action=confirm_order&amp;mt=$cart_template\">Place this order now</a></span></p>";
        return $return;
}
	
function view_cart_OLD_FUNCTIO_OLD_FUNCTIONN(){
	global $db;
	$cart_template=$this->value("cart_template");
	//var_dump($_SESSION);
	$return .= "<form name=\"cart\" method=\"post\" action=\"site.php?action=update_cart&mt=$cart_template\"><p><table style=\"margin-left:15px; color:#333333;\">";
	$trbg="#f1f1f1";
	if ($this->value("allow_multiple_quantities")){
		$return .= "<tr class=\"view_cart_table_row_header\" style=\"font-weight:bold; background-color:#444; color:#fff\"><td>Item</td><td>Unit Price</td><td>Qty</td><td></td><td></td></tr>";
	}
	global $current_site;
	//var_dump($_SESSION['cart']);
	$view_cart_running_total=0;
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$sql="SELECT * from products WHERE ID = $item";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);	
		$return .= "<tr bgcolor=\"$trbg\"><td>";
		$details_page=preg_replace('/{=([\w| |_|-|:|=]+)}/e','$h[$1]',$this->value("product_details_page"));
		$details_page=str_replace(" ","-",$details_page);	
		$details_page=str_replace("---","-",$details_page);	
		$return .= "<a href=\"".HTTP_PATH."/$details_page\" style=\"color:#222\">" . $this->get_product_title($h). "</a>"; 

		$return .= $this->view_cart_print_product_attributes($item);
		
		$qty_in_stock=$this->check_stock_quantity($item);
		if ($qty_in_stock <= $_SESSION['cart'][$item]['quantity'] || $qty_in_stock <=6){
			if ($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown"){
				$returntext = "Sorry - This product is Out Of Stock.";
			} else {
				$returntext = "Please note: only $qty_in_stock in stock.";
			}
			$return .= "<br><span style=\"color:#cc0000\">$returntext</span><br>";
		}
		$return .= "</td><td>" . $this->value("default_currency_symbol") . sprintf("%4.2f",$h[$this->value("price_field")]) . "</td>";

		if ($this->value("allow_multiple_quantities")){
			$return .= "<td><input type=\"text\" size=\"3\" value=\"".$_SESSION['cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\"></td>";
			//$return .= "<td>".$_SESSION['cart'][$item]['quantity']."</td>";
		}
		$view_cart_running_total = sprintf("%4.2f",$view_cart_running_total + ($h[$this->value("price_field")]*$_SESSION['cart'][$item]['quantity']));
		$return .= "<td>".$this->value("default_currency_symbol") . sprintf("%4.2f",$h[$this->value("price_field")]*$_SESSION['cart'][$item]['quantity'])."</td><td><a href=\"site.php?action=cart_remove&mt=$cart_template&product_id=".$h['ID']."\" class=\"remove_button\" style=\"color:#26abd5\">Remove</a></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}
	if ($db->num_rows($res)>0 && $this->value("allow_multiple_quantities")){
		$return .= "<tr><td></td><td></td><td><a href=\"Javascript:document.forms['cart'].submit()\" class=\"order_button\" style=\"text-align:center\">Update</a></td><td class=\"inline_cart_total\">".$this->value("default_currency_symbol").$view_cart_running_total."</td><td></td></tr>";
	} else {
		$return .= "<tr><td colspan=\"5\" style=\"color:white\">There are currently no items in your order.</td></tr>";
	}
	$return .= "</table></p><p></p>";
	$return .= "</form>";
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
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$products_pk=get_primary_key($this->value("products_table"));
		$sql_select_list=$products_pk . ",".$this->product_title_fields.",".$this->price_field.",image";
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
		$cart_data[$cart_lines_counter]['attributes'] = $this->view_cart_print_product_attributes($item);
		$cart_data[$cart_lines_counter]['unit_price'] = $default_currency_symbol . $h[$this->value("price_field")];
		$cart_data[$cart_lines_counter]['line_price'] = $default_currency_symbol . sprintf("%4.2f",$h[$this->value("price_field")] * $_SESSION['cart'][$item]['quantity']);

		if ($_SESSION['cart'][$item]['attributes']){
			$cart_data[$cart_lines_counter]['attributes']=$this->view_cart_print_product_attributes($item);
			$return_cart_lines .= $this->view_cart_print_product_attributes($item);
		}

		$qty_in_stock=$this->check_stock_quantity($item);
		if ($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown"){
			$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - This product is Out Of Stock.</span><br />";
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else if ($qty_in_stock <= $_SESSION['cart'][$item]['quantity']){
			$cart_data[$cart_lines_counter]['stock_alert'] = "<br><span style=\"color:#cc0000\">Sorry - only $qty_in_stock of these are in stock.</span><br />";
			$return_cart_lines .= $cart_data[$cart_lines_counter]['stock_alert'];
		} else {
			//$return .= "<br>$qty_in_stock in stock.</br>";
		}
		if ($this->value("allow_multiple_quantities")){
			$line_cost=$h[$this->value("price_field")]*$_SESSION['cart'][$item]['quantity'];
		} else {
			$line_cost=$h[$this->value("price_field")];
		}
		$running_total = $running_total + $line_cost;
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td>" . $h[$this->value("price_field")] . "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">";
		if ($cart_view_options['allow_update']){
			$return_cart_lines .= "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\">";	
			$cart_data[$cart_lines_counter]['quantity'] = "<input type=\"text\" size=\"2\" class=\"cart_quantity_update_text_field\" value=\"".$_SESSION['cart'][$item]['quantity']."\" name=\"item_".$h['ID']."\">";
		} else {
			$return_cart_lines .= $_SESSION['cart'][$item]['quantity'];
			$cart_data[$cart_lines_counter]['quantity'] = $_SESSION['cart'][$item]['quantity'];
		}
		$return_cart_lines .= "</td>";
		$return_cart_lines .= "<td style=\"color:#333333\">".$this->value("default_currency_symbol").sprintf("%4.2f",$line_cost)."</td>";
		if ($cart_view_options['allow_update']){
		$return_cart_lines .= "<td><a href=\"site.php?action=cart_remove&mt=$cart_template&product_id=".$h['ID']."\" class=\"remove_button\" style=\"color:#26abd5\">Remove</a></td>";
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

	$return .= "<tr><td colspan=\"4\" style=\"height:5px\ style=\"height:5px\"></td></tr>";
	if (!$this->value("allow_multiple_quantities") || !$cart_view_options['allow_update']){
		//$return .= "<tr><td colspan=\"3\" align=\"right\"><b>Sub Total:</b> </td><td>" . $this->value("default_currency_symbol") . sprintf("%4.2f",$running_total) . "</td></tr>\n";
	}
	$return .= "<tr><td colspan=\"4\" style=\"height:5px\ style=\"height:5px\"></td></tr>";

	//if ($this->buy_requires_login && $cart_view_options['run_modules'])
	if ($cart_view_options['run_modules']){
                //$return .= "<tr><td></td><td></td><td></td><td></td><td class=\"inline_cart_total\">".$this->value("default_currency_symbol").sprintf("%4.2f",$running_total)."</td><td></td></tr>";
		$return .= "<tr><td colspan=\"4\" align=\"right\"><b>Shipping: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_shipping() . "</td></tr>"; 
		$itemise_checkout_modules=$this->run_checkout_modules();
		foreach ($itemise_checkout_modules as $checkout_module_name=>$checkout_module_data){
			if ($checkout_module_name != "amount_to_add_to_total"){
				$module_text=$checkout_module_data['checkout_itemisation_text'];
				$module_text=str_replace("{=voucher_text}",$_SESSION['voucher_text'],$module_text);
				$return .= "<tr><td colspan=\"4\" align=\"right\"><b>" . $module_text .":</b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $checkout_module_data['total'] . "</td></tr>";
			} else {
				$_SESSION['checkout_modules_add_to_total']=$checkout_module_data;
			}
		}
		$return .= "<tr><td colspan=\"4\" style=\"height:5px\ style=\"height:5px\"></td></tr>";
		$return.= "<tr><td colspan=\"4\" align=\"right\"><b>Final Total: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</td></tr>";
		$return .= "</table></p><p></p>";
		$return .= "</form>";
	} else {
		$return .= "</table></p><p></p>";
		$return .= "</form>";
		//$return.= "<p>If shipping is applicable this will be quoted on the next page once you have confirmed where we are shipping to.</p>";
	}

	$_SESSION['total_price']=sprintf("%4.2f",$running_total);
	$return_string = $cart_header;
	if ($have_products){
		$return_string .= $cart_header_row; 
		$EXPORT['cart_header_row']=$cart_header_row;
	}
	require_once(LIBPATH . "/classes/recordset.php");
	require_once(LIBPATH . "/classes/recordset_template.php");
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
	$templated = $rst->rs_to_template($rs1,$export_template,$EXPORT); 
	$return_string = $templated; //return_cart_lines;
	//$return_string .= $return;
	return $return_string;
}

function view_cart_with_prices_OLD_FUNCTION(){
	global $db;
	$cart_template=$this->value("cart_template");

	$return .= "<form name=\"cart\" method=\"post\" action=\"site.php?action=update_cart&mt=$cart_template\"><p><table style=\"margin-left:15px\">";
	$return .= "<tr style=\"background-color:#444; color:#fff\"><td>Item</td><td>Quantity</td><td>Price</td></tr>";
	$trbg="#f1f1f1";
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$products_pk=get_primary_key("products");
		$sql_select_list=$products_pk . ",".$this->product_title_fields.",".$this->price_field;
		$sql="SELECT $sql_select_list FROM " . $this->value("products_table") . " WHERE $products_pk = $item";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);	
		$return .= "<tr bgcolor=\"$trbg\"><td class=\"view_cart_static_text_color\">";
		$return .= "<a href=\"".HTTP_PATH."/product_details/".$h['ID']."\" style=\"color:#222\">" . $this->get_product_title($h). "</a>"; 

		if ($_SESSION['cart'][$item]['attributes']){
			$return .= $this->view_cart_print_product_attributes($item);
		}

		$qty_in_stock=$this->check_stock_quantity($item);
		if ($qty_in_stock=="OutOfStock" || $qty_in_stock=="Unknown"){
			$return .= "<br><span style=\"color:#cc0000\">Sorry - This product is Out Of Stock.</span><br />";
		} else if ($qty_in_stock <= $_SESSION['cart'][$item]['quantity']){
			$return .= "<br><span style=\"color:#cc0000\">Sorry - only $qty_in_stock of these are in stock.</span><br />";
		} else {
			//$return .= "<br>$qty_in_stock in stock.</br>";
		}
		if ($this->value("allow_multiple_quantities")){
			$line_cost=$h[$this->value("price_field")]*$_SESSION['cart'][$item]['quantity'];
		} else {
			$line_cost=$h[$this->value("price_field")];
		}
		$running_total = $running_total + $line_cost;
		$return .= "</td>";
		$return .= "<td style=\"color:#333333\">".$_SESSION['cart'][$item]['quantity']."</td><td style=\"color:#333333\">".$this->value("default_currency_symbol").$line_cost."</td></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}
	if ($db->num_rows($res)>0){
		//$return .= "<tr><td></td><td><a href=\"Javascript:document.forms['cart'].submit()\" class=\"order_button\">Update</a></td><td></td></tr>";
	} else {
		$return .= "<tr><td colspan=\"3\" style=\"color:white\">There are currently no items ready to order.</td></tr>";
	}
	$return .= "<tr><td colspan=\"3\" style=\"height:5px\ style=\"height:5px\"></td></tr>";
	$return .= "<tr><td colspan=\"2\" align=\"right\"><b>Sub Total:</b> </td><td>" . $this->value("default_currency_symbol") . sprintf("%4.2f",$running_total) . "</td></tr>\n";
	$return .= "<tr><td colspan=\"3\" style=\"height:5px\ style=\"height:5px\"></td></tr>";


	if ($this->buy_requires_login){
		$return .= "<tr><td colspan=\"2\" align=\"right\"><b>Shipping: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_shipping() . "</td></tr>"; 
		$itemise_checkout_modules=$this->run_checkout_modules();
		foreach ($itemise_checkout_modules as $checkout_module_name=>$checkout_module_data){
			if ($checkout_module_name != "amount_to_add_to_total"){
				$return .= "<tr><td colspan=\"2\" align=\"right\"><b>" . $checkout_module_data['name'] .":</b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $checkout_module_data['total'] . "</td></tr>";
			} else {
				$_SESSION['checkout_modules_add_to_total']=$checkout_module_data;
			}
		}
		$return .= "<tr><td colspan=\"3\" style=\"height:5px\ style=\"height:5px\"></td></tr>";
		$return.= "<tr><td colspan=\"2\" align=\"right\"><b>Final Total: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</td></tr>";
		$return .= "</table></p><p></p>";
		$return .= "</form>";
	} else {
		$return .= "</table></p><p></p>";
		$return .= "</form>";
		//$return.= "<p>If shipping is applicable this will be quoted on the next page once you have confirmed where we are shipping to.</p>";
	}

	$_SESSION['total_price']=sprintf("%4.2f",$running_total);
	return $return;
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
	$return .= $title_fields_template;
	return $return;
}

function view_cart_static_OLD_FUNCTION(){

	global $db;
	$default_currency_symbol = $this->value("default_currency_symbol");

	$return .= "<p><table style=\"margin-left:15px\"><tr style=\"background-color:#222; color:#eee; font-weight:bold;\"><td>Item</td>";
	if ($this->value("allow_multiple_quantities")){
		$return .= "<td>Quantity</td>";
	}
	$return .= "<td>Price</td></tr>";
	$trbg="#f1f1f1";
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$products_pk=get_primary_key("products");
		$sql_select_list=$products_pk . ",".$this->product_title_fields.",".$this->value("price_field");
		$sql="SELECT $sql_select_list FROM " . $this->value("products_table") . " WHERE $products_pk = $item";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);
		$return .= "<tr bgcolor=\"$trbg\"><td class=\"view_cart_static_text_color\">";
		$return .= "<span class=\"view_cart_static_text_color\">" . $this->get_product_title($h) . "</span>";

		if ($_SESSION['cart'][$item]['attributes']){
			$return .= $this->view_cart_print_product_attributes();
		}
		$line_cost=$h[$this->value("price_field")];
		if (!$this->value("allow_multiple_quantities")){
			$running_total = $running_total + $line_cost;
		} else {
			$running_total = $running_total + ($line_cost*$_SESSION['cart'][$item]['quantity']);
		}
		$return .= "</td><td class=\"view_cart_static_text_color\">" . $_SESSION['cart'][$item]['quantity']. "</td><td class=\"view_cart_static_text_color\">$default_currency_symbol " . $line_cost*$_SESSION['cart'][$item]['quantity'] . "</td></tr>";
		if ($trbg=="#f1f1f1"){$trbg="#f9f9f9";} else {$trbg = "#f1f1f1";}
	}


	$return .= "<tr><td colspan=\"3\" style=\"height:5px\ style=\"height:5px\"></td></tr>";
	$return .= "<tr><td colspan=\"2\" align=\"right\"><b>Sub Total:</b> </td><td>" . $this->value("default_currency_symbol") . sprintf("%4.2f",$running_total) . "</td></tr>\n";
	$return .= "<tr><td colspan=\"3\" style=\"height:5px\ style=\"height:5px\"></td></tr>";


	if ($this->buy_requires_login){
		$return .= "<tr><td colspan=\"2\" align=\"right\"><b>Shipping: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_shipping() . "</td></tr>"; 
		$itemise_checkout_modules=$this->run_checkout_modules();
		foreach ($itemise_checkout_modules as $checkout_module_name=>$checkout_module_data){
			if ($checkout_module_name != "amount_to_add_to_total"){
				$return .= "<tr><td colspan=\"2\" align=\"right\"><b>" . $checkout_module_data['name'] .":</b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $checkout_module_data['total'] . "</td></tr>";
			} else {
				$_SESSION['checkout_modules_add_to_total']=$checkout_module_data;
			}
		}
		$return .= "<tr><td colspan=\"3\" style=\"height:5px\ style=\"height:5px\"></td></tr>";
		$return.= "<tr><td colspan=\"2\" align=\"right\"><b>Final Total: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</td></tr>";
		$return .= "</table></p><p></p>";
		$return .= "</form>";
	} else {
		$return .= "<tr><td colspan=\"2\" align=\"right\"><b>Shipping: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_shipping() . "</td></tr>"; 
		$return .= "<tr><td colspan=\"3\" style=\"height:5px\ style=\"height:5px\"></td></tr>";
		$return.= "<tr><td colspan=\"2\" align=\"right\"><b>Final Total: </b></td><td align=\"right\">" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</td></tr>";
		$return .= "</table></p><p></p>";
		$return .= "</form>";
		//$return.= "<p>If shipping is applicable this will be quoted on the next page once you have confirmed where we are shipping to.</p>";
	}

	$_SESSION['total_price']=sprintf("%4.2f",$running_total);
	return $return;
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
		$sql_select_list=$products_pk . ",".$this->product_title_fields.",".$this->value("price_field");
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
		} else {
			$running_total = $running_total + ($line_cost*$_SESSION['cart'][$item]['quantity']);
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
	$cart_template=$this->value("cart_template");
	$golden_account_active=$this->value("golden_account_active");
	if (!$_SESSION['cart']){
		$content="<p class=\"title\">Order Confirmation</p>";	
		$content .= "<p>There are no items in your shopping cart</p>";
		return $content;
	}
	$cart_data_hash=array("allow_update" => "0", "run_modules" => "1");
	$content .= $this->view_cart_general($cart_data_hash);
	
	// start promotional code
	global $db;

	// look at alternative shippings
	$all_shipping_modules=explode(",",$this->value("shipping_modules_installed"));
	if (count($all_shipping_modules)>1 && ($this->value("buy_requires_login") && $user->value("id"))){
		$content .= "<h3 class=\"shopping_cart_section_header shipping_section_header\">Shipping Options</h3>";
		if (!$_SESSION['user_selected_shipping_service']){
			$content .= "<p>We have calculated shipping above by the cheapest option. You may select a different option below:</p>";
		}
		$content .= $this->print_shipping_options_form();
	}

	$vouchers_active=$db->db_quick_match("checkout_modules","active","key_name","gift_vouchers_complex");
	if ($vouchers_active && !$_SESSION['voucher_text']){
		$content .= "<h3 class=\"shopping_cart_section_header givt_vouchers_header\">Gift Vouchers</h3>";
		$content .= "<p>If you have a gift voucher or promotional code, please enter the code below:";
		$content .= "<table width=\"100%\"><tr><td width=30><form style=\"display:inline; margin:0px; margin-left:30px; padding:0px;\" method=\"post\" action=\"site.php?action=enter_promotional_code&amp;mt=$cart_template\">Voucher&nbsp;Code:</td><td><input type=\"text\" name=\"promotional_code\"> <input type=\"submit\" value=\"Submit Voucher\"></form></td></tr></table><br /></p>";
	}

	if (!$this->value("buy_requires_login")){
		$content .= "<p>Shipping (if applicable) will be quoted on the next page once we know where we are delivering to.</p>";
		$content .= $this->print_user_details_form();
	} else {
		$content .= $this->print_cart_submit_form();
	}	

	if ((!$_COOKIE['login'] || !$user->value("id")) && $this->value("buy_requires_login")){
		$content .= "<p>Please <a href=\"log_in.html\">Log In</a> or <a href=\"register.html\">Register</a> to complete your order.</p>";
	} else {

		// golden account codes
		if (!$account_code_validated && $golden_account_active){
			$content .= "<p style=\"background-color:#444444\">If you have a golden account, please enter your account number in the box below:";
			$content .= "<table width=\"100%\" style=\"background-color:#444444\"><tr><td width=30><form style=\"display:inline; margin:0px; margin-left:30px; padding:0px;\" method=\"post\" action=\"site.php?action=enter_golden_account_code&amp;mt=$cart_template\">Account&nbsp;No:</td><td><input type=\"text\" name=\"promotional_code\"> <input type=\"submit\" value=\"Submit Code\"></form></td></tr></table><br /></p>";
		// end golden promotional code
		} else if ($account_code_validated && $golden_account_active){
				$content .= "<p><span class=\"order_button\" style=\"float:left;\"><a href=\"Javascript:checkUserDetails()\">Continue To Retrieve Your Download URLs</a> &nbsp; </span></p>";
		}
		
		if (($golden_account_active && !$account_code_validated) || !$golden_account_active){
			if ($this->value("buy_requires_login")){
				$content .= "<hr size=\"1\">\n";
				$content .= $this->confirm_personal_details();
			}
			$content .= "<hr size=\"1\" style=\"clear:both; margin-top:22px; padding-top:0px; \">\n";
			if (!$this->value("no_delivery_address") && !$this->value("no_billing_address")){
				$cart_form_action="Javascript:check_payment_method()";
				
				if (!$this->value("buy_requires_login")){
					$cart_form_action="Javascript:checkUserDetails()";
				}

				// Check US billing state:
				global $db;
				$check_user_sql="SELECT country,us_billing_state,same_as_billing_address,us_delivery_state FROM user WHERE id = " . $user->value("id");
				$check_user_res=$db->query($check_user_sql);
				$user_h=$db->fetch_array($check_user_res);

				if ($user_h['country']==183 && !$user_h['us_billing_state']){
					$content .= "<p class=\"dbf_para_alert\"><span style=\"color:#1b2c67\">The US State code must be entered into your address details in order to pass our credit card verification checks for US customers. <a href=\"checkout_edit_addresses.html\" style=\"font-weight:bold\"> Please click here to update your user details before continuing. Thank you.</a></span></p>";
				} else {

					// payment modules
					$installed_payment_modules=$this->payment_modules_installed;
					$installed_payment_modules=str_replace(",","','",$installed_payment_modules);
					global $db;
					$sql="SELECT id,checkout_option_text,checkout_option_text_extra from payment_modules WHERE key_name IN ('$installed_payment_modules') AND active=1 ORDER BY order_on_checkout_page ASC";
					$res=$db->query($sql);
					$total_payment_modules=$db->num_rows($res);
					if ($total_payment_modules==1){
						$content .= "<h3 class=\"shopping_cart_section_header\">Payment method:</h3><div id=\"checkout_options\">";
					} else {
						$content .= "<h3 class=\"shopping_cart_section_header\">Please select a payment method:</h3><div id=\"checkout_options\">";
					}
					while ($h=$db->fetch_array($res)){
						$content .= "<div id=\"checkout_option_div\"><span class=\"checkout_option\"><span class=\"checkout_option_text\"><input style=\"float:left\" type=\"radio\" name=\"payment_method\" value=\"".$h['id']."\"";
						if ($total_payment_modules==1){ $content .= " checked"; }
						$content .= ">".$h['checkout_option_text'] . "</span>";
						if ($total_payment_modules==1){ $content .= "<span class=\"checkout_option_text_only_method\">(This is the only payment method available.)</span>"; }
						if ($h['checkout_option_text_extra']){ $content .= "<br /><span class=\"checkout_option_text_extra\">".$h['checkout_option_text_extra'] . "</span>"; }
						$content ."</span><br />";
						$content .= "</div>";
					}
					// end payment modules
					
					$content .= "</div>";
					// checkout terms and conditions - move to template //
					if ($this->value("approve_terms")){
						$content .= "<hr size=\"1\" style=\"clear:both; margin-top:22px; padding-top:0px; \">\n";

						$content .= "<p><b>Terms and conditions of sale</b></p>";
						$content .= "<p>All sales through our web site are subject to our terms and conditions which are available from the 'Terms And Conditions' link at the bottom of all pages, or please <a href=\"Javascript:void terms_and_conditions_popup()\"> click here</a> to read them now.</p>";
						$content .= "<p>Please check the box to confirm that you have read our terms and conditions of sale. <input type=\"checkbox\" name=\"confirm_terms_and_conditions\" value=\"1\" /></p>";

						$content .= "<hr size=\"1\" style=\"clear:both; margin-top:22px; padding-top:0px; \">\n";
					}
					// end checkout terms and conditions - move to template //
					$content .= "<p style=\"float:left; clear:both;\"><span class=\"jc_button_160\" style=\"float:left;\"><a href=\"$cart_form_action\" style=\"font-weight:bold\">Continue to payment</a> &nbsp; </span></p>";
				}
			} else {
				$content .= "<p style=\"text-align:center\">Please complete your address details above to continue</p>";
			}
			//$content .= "<hr size=\"1\" style=\"clear:both; margin-top:22px; padding-top:0px; \">\n";
		}
	}
	if (!$this->value("buy_requires_login")){	
		$content .= "</form>";
	}
	return $content;
}

// this function initially logs the order before getting payment if necessary
function place_order(){

	global $db;
	global $user;
	//if (!$_POST['po_number']){ return 0;}
	$po_number=$_POST['po_number'];
	$delivery_office=$_POST['delivery_address'];
	if (!$delivery_office){$delivery_office=0;}
	$comments=$_POST['comments'];
	$promotional_code=$_POST['promotional_code_validated'];
	$start_transaction_sql=$db->query("BEGIN");
	
	$mail_cart_to=$this->value("mail_orders_to");
	$mail_cart_from=$this->value("mail_orders_from_name");
	$mail_cart_from_email=$this->value("mail_orders_from_address"); 
	
	// global mail vars - THIS DOES NOT YET WORK!
	global $CONFIG;
	if ($_REQUEST_SAFE['s']){ $siteid=$_REQUEST_SAFE['s'];} else { $siteid=$CONFIG['default_site'];}
	if ($siteid){
	$current_site=load_web_site_vars($siteid);
	$site_name=$current_site['site_name'];
	} else {
		$site_name="";
	}

	//$mail_cart_to=field_from_record_from_id("setup_variables",2,"value");
	//$mail_cart_from=field_from_record_from_id("setup_variables",4,"value");
	//$mail_cart_from_email=field_from_record_from_id("setup_variables",5,"value");
	$mail_from="\"$mail_cart_from\" <$mail_cart_from_email>";
	$subject=$this->value("preliminary_notification_subject");
	$headers="From: $mail_from\n";
	$headers .= "Content-type:text/html\n\r\n\r";
	$mail_template=$db->field_from_record_from_id("templates",$this->value('preliminary_notification_template'),"template");
	$order_details=$this->view_cart_general( array("run_modules" => "1" ));
	$mail_template=str_replace("{=order_details}",$order_details,$mail_template);
	$total_amount=$_SESSION['total_price'];	
	$mail_template = str_replace("{=total_amount}",$total_amount,$mail_template);
	$mail_template = str_replace("{=user_name}",$user->value("full_name"),$mail_template);
	$mail_template = str_replace("{=site_name}",$site_name,$mail_template);
	$mail_template = str_replace("{=user_id}",$user->value("id"),$mail_template);
	$quick_user_details=$user->value("full_name") . "(ID:" . $user->value("id") . ")";
	$mail_template = str_replace("{=user_details}",$quick_user_details,$mail_template);
	$ordered_by=$user->value("id");

	if (!$this->value("buy_requires_login")){ // log the user details which were POSTed through

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
		$ordered_by="";
		$user_details_id=$non_account_ordered_by;
		// add to order_details to appear in email as well
		$mail_user_template=$db->field_from_record_from_id("templates",$this->value('email_user_data_template'),"template");
		foreach ($tablefields as $tablefield){
			$replace_str="{=".$tablefield."}";
			$mail_user_template=str_replace($replace_str,$update_assoc[$tablefield],$mail_user_template);
		}
		$mail_template .= $mail_user_template;
	} else {
		$non_account_ordered_by="";
	}

	if (!$ordered_by && !$non_account_ordered_by){
		format_error("An error has occured: No user data found to log this order to",1);
	}
	$payment_method=$_POST['payment_method'];
	$_SESSION['payment_method_in_use']=$payment_method;
	$source_identifier=$this->value("source_identifier");
	$payment_method=$db->field_from_record_from_id("payment_modules",$payment_method,"key_name");
	$log_shipping=$this->value("shipping_total");
	if (!$this->value("buy_requires_login") && !preg_match("/^[-+]?[0-9]*\.?[0-9]+$/",$log_shipping)){
		$log_shipping=0;
	}

	if ($this->value("buy_requires_login")){
		$order_sql = "INSERT INTO orders (ordered_by,order_date,datetime,total_amount,purchased_through_account,payment_method,shipping_total,grand_total,origin) values(".$ordered_by.",NOW(),NOW(),$total_amount,\"$promotional_code\",\"$payment_method\",".$log_shipping.",".$_SESSION['grand_total'].",\"$source_identifier\")";
	} else {
		$order_sql = "INSERT INTO orders (non_account_order,order_date,datetime,total_amount,purchased_through_account,payment_method,shipping_total,grand_total,origin) values(".$non_account_ordered_by.",NOW(),NOW(),$total_amount,\"$promotional_code\",\"$payment_method\",".$log_shipping.",".$_SESSION['grand_total'].",\"$source_identifier\")";
	}

	$order_res = $db->query($order_sql) or $cart_error = $this->display_mysql_cart_error($order_sql . " gave " . ->db_error());
	$order_id = $db->last_insert_id();
	$_SESSION['order_id']=$order_id;
	$mail_template = str_replace("{=order_id}",$order_id,$mail_template);
	
	foreach ($_SESSION['cart'] as $item => $itemdata){
	
		$item_price_sql="SELECT " . $this->value("price_field") . " FROM products WHERE ID = $item";
		$ip_res=$db->query($item_price_sql);
		while ($hp=$db->fetch_array($ip_res)){
			$individual_item_price=$hp[$this->value("price_field")];
		}
		$product_sql = "INSERT INTO order_products (order_id,product_id,quantity,price) values($order_id,$item,".$_SESSION['cart'][$item]['quantity'].",".$individual_item_price.")";
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

		// If buy without login, need to tag the user details with the order id from user_details_id
		if (!$this->value("buy_requires_login")){
			$update_user_details="UPDATE order_user_data SET order_id = $order_id WHERE id = $user_details_id";
			$update_user_details_result=$db->query($update_user_details);
		}

                $set_order_cookie = setcookie("order_id", $order_id);

		// we only need to mail at this stage the preliminary template.
		if ($this->value("preliminary_notification_template")){
			mail ($mail_cart_to,$subject,$mail_template,$headers) or die("cant send mail");
		}
	}

	//print "mail sent and session is ";
	//var_dump($_SESSION); 
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

	require_once(LIBPATH . "/classes/gift_vouchers_complex.php");
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
		$return .= "<p class=\"dbf_para_alert\">Sorry - this voucher code does not exist or is no longer available.</p>";
		$return .= $this->confirm_order();
		return $return;
	} else {
		$code_validated=1;
		$return = "<p class=\"dbf_para_success\">Promotional code validated - successfully - your new total is below.</p>";
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

function calculate_shipping(){

	$shipping_rate=0;
	if (!$this->value("buy_requires_login")){
		$shipping_country=$_POST['new_delivery_country']; // not always but for PGI yes...
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
		$shipping_rate=$this->get_best_shipping_rate($shipping_country);
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
	return $_SESSION['grand_total'];
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
	require_once("$libpath/classes/filters.php");
	$user_details_filter=new filter($this->value("no_login_user_details_form_filter"));
	$options['filter']=$user_details_filter->all_filter_keys();
	$form_content=form_from_table("order_user_data","add_row","","1",$options);
	$form_content=str_replace("</form>","",$form_content);
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
		$h['delivery_address_1']="Deliver to billing address";
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

	$user_details_template=$this->hash_into_template($h,$templateid);
	return $user_details_template;
}

function hash_into_template($hash,$templateid){
	global $db;
	$template=$db->field_from_record_from_id("templates",$templateid,"template");
	foreach ($hash as $key => $value){
		$dbf_tag="{=".$key."}";
		$template=preg_replace("/$dbf_tag/",$value,$template);
	}
	return $template;
}

function load_payment_module(){
        global $libpath;
        require_once("$libpath/classes/payment_module_paypal_express_checkout.php");
        $paypal_details=new payment_module_paypal_express_checkout();
	$content = $paypal_details->initiate_payment();
	return $content;
}

// these 2 functions need to move to the paypal module 
function load_paypal_payment_success(){
	global $libpath;
        require_once("$libpath/classes/payment_module_paypal_express_checkout.php");
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
        require_once("$libpath/classes/payment_module_paypal_express_checkout.php");
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

	$cart_template=$this->value("cart_template");
	$order_confirmation = $this->view_cart_general( array("run_modules" => "1") );
	//$order_confirmation .= "<b>Shipping: </b>" . $this->value("default_currency_symbol") . $this->calculate_shipping(); 
	//$order_confirmation .= "<p><b>Final Total: </b>" . $this->value("default_currency_symbol") . $this->calculate_grand_total() . "</p>";
	$place_order_result=$this->place_order();
	
	if ($place_order_result==1){
		if (!$_POST['promotional_code_validated']){
			$title="Checkout: Payment";
			//$content = "<p>Thank you - your order has been logged. </p>";
			$content .= $order_confirmation;

			$sql="SELECT * from payment_modules WHERE id = " . $_POST['payment_method'];
			global $db;
			$res=$db->query($sql);
			while ($h=$db->fetch_array($res)){
				$payment_icon=$h['payment_icon'];
				$forwarding_page_text=$h['forwarding_page_text'];
				$module_specific_payment_function=$h['module_specific_payment_function'];
				$class_filename=$h['class_filename'];
			}

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
						$content .= $my_payment->$module_specific_payment_function();
					} else {
						print "class file does not exist";
					}
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
		$content="<p>An unknown error has occurred - we were not able to log this order. Please return to the <a href=\"checkout.html\">checkout</a> to try again.</p>";
	}
	$return_content['content']=$content;
	$return_content['title']=$title;
	return $return_content;	
}

function complete_order_after_payment_taken(){
	global $db;
	$internalSaleId=$this->getInternalSaleId();
	if (!$internalSaleId){
		format_error ("No internal sale id to be found!",1); exit;
	}

	if ($this->value("buy_requires_login")){
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
	}

	// store user details for printing in the email in the object
	if ($this->value("buy_requires_login")){
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
	$log_completed_payment_sql="UPDATE orders SET complete = 1, order_country = $user_delivery_country, vatable = $vatable WHERE id = $internalSaleId";
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
	if ($this->value("run_extra_code_on_place_order_success")){
		list($extra_module,$extra_function)=explode("::",$this->run_extra_code_on_place_order_success);
		require_once("$libpath/classes/$extra_module.php");
		$extra_code_module=new $extra_module;
		$build_function = "\$order_post_result = \$extra_code_module->" . $extra_function .= ";";
		$eval_result=eval($build_function);
	}

	unset($_SESSION['cart']);
	$return .= "<p><a href=\"index.html\">Click Here to return to the home page.</a></p>";
	return $return;
}


function mail_final_order_confirmation_to_customer(){
	$internalSaleId=$this->getInternalSaleId();
	if (!$internalSaleId){
		format_error("Error Code: 81881G",1,"","Could not find the internal order number from getInternalSaleId - was it set correctly?");
	}
	global $db;

	$cust_mail_conf_temp = $this->value('customer_email_confirmation_template');
	$message=$db->field_from_record_from_id("templates",$cust_mail_conf_temp,"template");
	$print_order_detail=$this->cart_contents_from_session();
	$print_order_detail=preg_replace("/src ?= ?\"(\w+) (\w+).jpg\"/","src=\"$1%20$2.jpg\"",$print_order_detail);
	$print_download_detail=$this->retrieve_download_urls_from_current_order();
	$print_download_detail=$print_download_detail['text'];

	// global variables for email
	global $current_site;
	$site_name=$current_site['site_name'];

	if (!$this->value("buy_requires_login")){
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
			$user_name=$j['name'];
		}

		$mail_to=$user_email_address;
		$message=str_replace("{=name}",$user_name,$message);
		$message=str_replace("{=site_name}",$site_name,$message);
		$message=str_replace("{=order_details}",$print_order_detail,$message);
		$message=str_replace("{=download_details}",$print_download_detail,$message);
		$message=str_replace("{=order_id}",$internalSaleId,$message);
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
	$headers .= "From: " . $this->value("mail_orders_from_address") . "\r\n";
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
		format_error("Error Code: 81881G-O",1,"","Could not find the internal order number from getInternalSaleId - was it set correctly?");
	}

	$mail_conf_temp = $this->value('email_confirmation_template');
	$message=$db->field_from_record_from_id("templates",$mail_conf_temp,"template");
	$print_order_detail=$this->cart_contents_from_session();
	$print_order_detail=preg_replace("/src ?= ?\"(\w+) (\w+).jpg\"/","src=\"$1%20$2.jpg\"",$print_order_detail);

	// global variables for email
	global $current_site;
	$site_name=$current_site['site_name'];

        if (!$this->value("buy_requires_login")){
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
                        $user_name=$j['name'];
                }

                $mail_to=$user_email_address;
                $message=str_replace("{=name}",$user_name,$message);
                $message=str_replace("{=site_name}",$site_name,$message);
                $message=str_replace("{=order_details}",$print_order_detail,$message);
                $message=str_replace("{=order_id}",$internalSaleId,$message);
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
	$headers .= "From: " . $this->value("mail_orders_from_address") . "\r\n";
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
        $select_order_products_sql="SELECT * from order_products where order_id = $internalSaleId";
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
                        $download_urls_html .= "<p><b>$product_title:</b><br /><a href=\"".HTTP_PATH."/downloads/$download_url.zip\">".HTTP_PATH."/downloads/$download_url.zip</a></p>";
			$download_urls_text .= "$product_title\n".HTTP_PATH."/downloads/$download_url.zip\n\n";
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
		require_once($class_file);
		$checkout_mod_class = new $h['key_name'];
		$total = $checkout_mod_class->itemise_at_checkout(); 
		if ($total && $total != 0){ // this hides any module with a value of 0
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

function store_email_when_out_of_stock($product_id){

	if (!is_numeric($product_id)){ format_error("Bad Product Id",1); }
	global $db;
	global $user;
	if ($user->value("id")){
		// check if product already requested
		$sql="SELECT * from cart_out_of_stock_email_list where product = \"$product_id\" AND user = " . $user->value("id") . " AND mail_sent=0";
		$rv=$db->query($sql);
		if ($db->num_rows($rv)==0){
			$insert_sql="INSERT INTO cart_out_of_stock_email_list (user,product,date_requested,mail_sent) values(".$user->value('id').",$product_id,NOW(),0)";
			$do_insert=$db->query($insert_sql);
			$return_msg="<p><b>Product added to your email list</b></p><p>Thank you - this product has been added to your email request list. You will automatically be emailed when this product comes back into stock.</p>";
		} else {
			$return_msg="<p>Thank you - you have already requested an email notification for this product. We will send you an email when this product is back in stock.</p>";
		}
	} else {
		$return_msg="<h3>Out Of Stock Item - Add To Email List</h3><p>Please note - this item is currently out of stock, however if you wish we can email you when this product comes into stock. You will need an account in order to receive emails from us.</p><p>Please either <a href=\"log_in.html\">Log In</a> or <a href=\"register.html\">Register</a> for an account with us in order to continue.</p>";
	}
	return $return_msg;
}

function get_best_shipping_rate($shipping_country){
	global $libpath;
	#$shipping_module="$libpath/classes/".$this->value("shipping_modules_installed") . ".php";
	$all_shipping_modules=explode(",",$this->value("shipping_modules_installed"));
	$best_shipping_rate="0.00";
	$shipping_rate_set=0;
	global $user;
	if (!$user->value("id") && $this->value("buy_requires_login")){
		$best_shipping_quote="tbc";
		return $best_shipping_quote;
	}
        foreach ($all_shipping_modules as $each_shipping_module){
                $shipping_module_file=$libpath."/classes/".$each_shipping_module.".php";
                if (file_exists($shipping_module_file)){
                        include_once($shipping_module_file);
                        $set_up_new_object_code="\$shipping = new " . $each_shipping_module . ";";
                        $obj_result=eval($set_up_new_object_code);
                        $shipping_rate=$shipping->calculate_shipping_rate($this->value("buy_requires_login"),$shipping_country);
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
                } else { format_error("Shipping module class file $each_shipping_module does not exist",1); }
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
		$shipping_rate=$shipping->calculate_shipping_rate($this->value("buy_requires_login"),$shipping_country);
	} else { format_error("No file of $shipping_module_file exists!",1); }
	if (is_numeric($shipping_rate)){ $shipping_rate=sprintf("%4.2f",$shipping_rate);}
	return $shipping_rate;
}

function print_shipping_options_form(){
	global $libpath;
	global $db;
	#$shipping_module="$libpath/classes/".$this->value("shipping_modules_installed") . ".php";
	$cart_template=$this->value("cart_template");
	$all_shipping_modules=explode(",",$this->value("shipping_modules_installed"));
	$return = "<form action=\"site.php?action=update_shipping&amp;mt=$cart_template\" method=\"post\" name=\"shipping_options_form\"><table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" class=\"shipping_options_table\">";
        foreach ($all_shipping_modules as $each_shipping_module){
                $shipping_module_file=$libpath."/classes/".$each_shipping_module.".php";
                if (file_exists($shipping_module_file)){
                        include_once($shipping_module_file);
                        $set_up_new_object_code="\$shipping = new " . $each_shipping_module . ";";
                        $obj_result=eval($set_up_new_object_code);
                        $shipping_rate=$shipping->calculate_shipping_rate($this->value("buy_requires_login"),$shipping_country);
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
                } else { format_error("Shipping module file $each_shipping_module does not exist or is incorrectly installed",1); }
        }
	$return.= "</table>";
	//$return .= "<p>If you have selected a new shipping method please <a href=\"Javascript:document.forms['shipping_options_form'].submit()\">Update Your Order Total</a></p>\n";
	$return .= "<hr size=\"1\" />\n";
	return $return;
}

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

function update_shipping($shipping_service){
	$_SESSION['user_selected_shipping_service']=$shipping_service;
	return $this->confirm_order();
}

// END SHOPPING CART CLASS
}


?>
