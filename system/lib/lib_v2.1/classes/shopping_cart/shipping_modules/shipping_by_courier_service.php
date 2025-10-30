<?php

class shipping_by_courier_service extends shopping_cart {

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
	// get user country
	$country_sql="SELECT if (same_as_billing_address=1,country,delivery_country) as shipping_country FROM user WHERE id = " . $user->value("id");
	$country_rv=$db->query($country_sql);
	$country_h=$db->fetch_array($country_rv);
	if($country_h['shipping_country'] != 182){
		return "DISALLOW";	
	}
	
		// NEW

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
	
	$shipping_user_type=$user->value("type");
	$sql="SELECT rate FROM courier_service_charges INNER JOIN countries ON countries.shipping_zone=courier_service_charges.zone WHERE countries.id=$delivery_country_id AND customer_type = \"" . $shipping_user_type . "\"";
	$rv=$db->query($sql);
	if ($db->num_rows($rv)==0){
		$sql="SELECT rate FROM courier_service_charges INNER JOIN countries ON countries.shipping_zone=courier_service_charges.zone WHERE countries.id=$delivery_country_id AND customer_type = \"user\"";
		$rv=$db->query($sql);
	}
	while ($h=$db->fetch_array($rv)){
		$rate = $h['rate'];
	}
	return $rate;
}

// END CLASS SHIPPING_RATE_PER_QUANTITY
}

?>
