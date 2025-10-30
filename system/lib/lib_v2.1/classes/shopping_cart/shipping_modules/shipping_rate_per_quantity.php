<?php

class shipping_rate_per_quantity extends shopping_cart {

function __construct(){
	// note: none if this is required any more!
	$this->initial_rate_uk=1;
	$this->initial_rate_europe=2;
	$this->initial_rate_usa=3;
	$this->initial_rate_row=3;

	$this->additional_item_uk=.5;
	$this->additional_item_europe=1;
	$this->additional_item_usa=1;
	$this->additional_item_row=1;

}

function value($of){
	return $this->$of;
}

function calculate_shipping_rate($shipping_options){
	$buy_requires_login=$shipping_options['buy_requires_login'];
	$country_if_no_login=$shipping_options['country_if_no_login'];
	$preorder=$shipping_options['preorder'];
	global $db;
	global $user;
	if ($user->value("type")=="trade customer"){ return "DISALLOW"; }
	// need value of buy_requires_login and also the shipping country
	if ($buy_requires_login==1 && !$user->value("id")){ return "Please log in for a shipping quote"; }
	if ((!$buy_requires_login || $buy_requires_login==2) && $country_if_no_login){
		$delivery_country_id=$country_if_no_login;
	} else {
		// get the delivery country
		$delivery_country_id=$db->field_from_record_from_id("user",$user->value("id"),"delivery_country");
		if (!$delivery_country_id || $db->field_from_record_from_id("user",$user->value("id"),"same_as_billing_address")){
			$delivery_country_id=$db->field_from_record_from_id("user",$user->value("id"),"country");
		}
	}
	if (!$delivery_country_id){
		return "Please specify a delivery country in your account for a shipping quote";
	}
	$continent=$db->field_from_record_from_id("countries",$delivery_country_id,"continent");
	//print "user is in $continent";

	$local_shipping_rates="SELECT initial_item_rate,additional_item_rate,countries.shipping_zone AS shipping_zone FROM shipping_rate_per_quantity_by_zone INNER JOIN countries ON countries.shipping_zone = shipping_rate_per_quantity_by_zone.zone WHERE countries.id=" . $delivery_country_id;
	$local_shipping_rates_rv=$db->query($local_shipping_rates);
	$shipping_rates_h=$db->fetch_array($local_shipping_rates_rv);
	$initial=$shipping_rates_h['initial_item_rate'];
	$additional=$shipping_rates_h['additional_item_rate'];
	$shipping_zone=$shipping_rates_h['shipping_zone'];

	$no_of_items=0;
	$rate=0;
	$running_quantity=0;

	$priority_shipping_rate_items=array();
	$standard_shipping_rate_items=array();
	if($preorder){ $cart_type="preorder_cart"; } else { $cart_type = "cart"; }
	foreach ($_SESSION[$cart_type] as $cartitem => $cartvalues){
		if ($preorder && ($cartitem != $preorder)){ continue; }
		$shipping_override_sql="SELECT initial_item_rate, additional_item_rate FROM shipping_rates_per_product WHERE product = $cartitem AND zone = $shipping_zone";
		$shipping_override_sql_rv=$db->query($shipping_override_sql);
		$shipping_override_h=$db->fetch_array($shipping_override_sql_rv);
		if ($shipping_override_h['initial_item_rate'] && $shipping_override_h['additional_item_rate']){
			$priority_shipping_rate_items[$cartitem]=$cartvalues;
		} else {
			$standard_shipping_rate_items[$cartitem]=$cartvalues;
		}
	}

	$all_cart_items=$priority_shipping_rate_items; // this gets rebuilt 2 lines down in the code -its a quick way to merge..
	foreach ($standard_shipping_rate_items as $cartitem => $cartvalues){
		$all_cart_items[$cartitem]=$cartvalues;
	}

	foreach ($all_cart_items as $cartitem => $cartvalues){
		//print_debug("<p>On running quantity of $running_quantity");
		// hack for yes union on the line below - multi quantities per item
		$shipping_override_sql="SELECT initial_item_rate, additional_item_rate FROM shipping_rates_per_product WHERE product = $cartitem AND zone = $shipping_zone";
		$shipping_override_sql_rv=$db->query($shipping_override_sql);
		$shipping_override_h=$db->fetch_array($shipping_override_sql_rv);
		if ($shipping_override_h['initial_item_rate'] && $shipping_override_h['additional_item_rate']){
			if ($cartvalues['quantity']==1){
				// below, if we don't check the running_quantity we always get the initial item rate...
				if ($running_quantity>=1){
					$shipping_for_this_item=$shipping_override_h['additional_item_rate'];
					if ($user->value("id")==1){
						print "additional rate of $shipping_for_this_item used";
					}
				} else {
					$shipping_for_this_item=$shipping_override_h['initial_item_rate'];
				}
				//$shipping_for_this_item=$shipping_override_h['initial_item_rate'];
			} else {
				$shipping_for_this_item=$shipping_override_h['initial_item_rate'] + $shipping_override_h['additional_item_rate']*($cartvalues['quantity']-1);
			}
		} else {
			if ($running_quantity==0){
				if ($cartvalues['quantity']==1){
					$shipping_for_this_item=$initial;
				} else {
					$shipping_for_this_item=$initial+ ($additional*($cartvalues['quantity']-1));
				}
			} else {
				$shipping_for_this_item=$additional*$cartvalues['quantity'];
			}
		}

		// do product attributes kill it?
		// modify this bit to only run if the correct attribute has been found..
		if ($cartvalues['attributes']){
			foreach ($cartvalues['attributes'] as $eachattribute){
				foreach ($eachattribute as $v => $data){

					// $v could equal delivery here, $data['value'] the text Collect on door (No delivery or booking fee) only. We need to add the id of the attribute option!
					// for now a quick hack is to do a hack on Collect on door (No delivery or booking fee) as the attribute value
					if ($data['value']=="Collect on door (No delivery or booking fee)"){
						$att_sql="SELECT product_attribute_action_options.action from products_to_product_attributes INNER JOIN product_attributes ON products_to_product_attributes.attribute_id = product_attributes.id INNER JOIN product_attribute_options ON product_attributes.id = product_attribute_options.attribute INNER JOIN product_attribute_action_options ON product_attribute_options.attribute_action = product_attribute_action_options.id WHERE product_id = $cartitem AND product_attribute_action_options.action=\"Free Shipping On This Item\"";
						$att_rv=$db->query($att_sql);
						while ($att_h=$db->fetch_array($att_rv)){
							$shipping_for_this_item=0;
						}
					} // end hack loop

				}	
			}
		}
		// end the attributes lookup!


		//print_debug ("<br />-- Shipping for this item here is " . $shipping_for_this_item . "<br />");
		//if ($user->value("id")=="1"){
		//	print "<p>$cartitem: $shipping_for_this_item (at qty $running_quantity).</p>";
		//}
		if ($cartitem==13922){
			$no_of_items=$no_of_items+(4*$cartvalues['quantity']);
		} else {
			$no_of_items = $no_of_items + $cartvalues['quantity'];
		}
		$running_quantity=$running_quantity+$cartvalues['quantity'];
		$rate = $rate + $shipping_for_this_item;
		//print_debug("On item $cartitem with s $shipping_for_this_item and $rate");
	}
	
	/*if ($no_of_items == 1){
		$rate=$initial; 
	} else if (count($_SESSION['cart'])==0){
		$rate=0;
	} else if ($no_of_items>1){
		$rate=$initial+(($no_of_items-1)*$additional);
	} else {
		format_error("Negative Cart Items",1);
	}
	*/
	
	//if (count($_SESSION['cart'])>1){ $rate=$initial+((count($_SESSION['cart'])-1)*$additional); } else

	// finally, an override for free shipping
	if ($db->field_exists_in_table("shipping_rate_per_quantity_by_zone","free_shipping_level")){
		$local_shipping_rates="SELECT free_shipping_level FROM shipping_rate_per_quantity_by_zone INNER JOIN countries ON countries.shipping_zone = shipping_rate_per_quantity_by_zone.zone WHERE countries.id=" . $delivery_country_id;
		$local_shipping_rates_query=$db->query($local_shipping_rates);
		$lsr_h=$db->fetch_array($local_shipping_rates_query);
		/*
		if ($lsr_h['free_shipping_level'] && $lsr_h['free_shipping_level']>0){
			if ($_SESSION['total_price']>=$lsr_h['free_shipping_level']){
				$rate=0;
				$_SESSION['shipping_message']="As you have ordered over &pound;".$lsr_h['free_shipping_level'] . " of goods you have received free shipping on this order.";
			} else {
				$_SESSION['shipping_message']="Did you know that if you order over &pound;" . $lsr_h['free_shipping_level'] . " worth of goods you can get free shipping?";
			}
		} else {
			unset($_SESSION['shipping_message']);
		}
		*/

	}

	$rate=sprintf("%4.2f",$rate);
	if ($preorder){
		$_SESSION['preorder_shipping_rate']=$rate;
	} else {	
		$_SESSION['shipping_rate']=$rate;
	}
	return $rate;
}

// END CLASS SHIPPING_RATE_PER_QUANTITY
}

?>
