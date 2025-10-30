<?php

/*
 * CLASS: custom_extras 
 * Allows extra functions to be put into the main codebase as an interim measure untill the full oo stuff is sorted out - June 2012.
 */
class custom_extras {

function set_trade_order_status(){
	global $db;
	global $user;
	$order_id=$_SESSION['order_id'];
	if (($user->value("type")=="trade_customer" || $user->value("type")=="trade customer" || $user->value("type")=="master") && $order_id){
		$upd_sql="UPDATE orders SET trade_order = 1 WHERE id = $order_id";
		$rv=$db->query($upd_sql);
		$trade_order=1;
	} else {
		$trade_order=0;
	}
	return $trade_order;
}

}
