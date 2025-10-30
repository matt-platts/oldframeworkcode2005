<?php

$cart_count=0;
foreach ($_SESSION['cart'] as $item => $itemdata){
	$cart_count++;
	}
foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
	$cart_count++;
	}

if ($_SESSION['order_number_set']){

	print "<div style=\"width:480px;\"><hr size=\"1\" /><p><img width=\"15\" src=\"images/shopping_cart/cart.png\" border=\"0\"> To continue and pay for your pre-order #".$_SESSION['order_id'].", <a href=\"/pre-order-paypal/".$_SESSION['order_id']."/\">Please click here</a></p><hr size=\"1\" /></div>";
	
} else if ($cart_count>0){

	if ($cart_count>1){$plural="s";}
	print "<div style=\"width:420px;\"><hr size=\"1\" /><p><img width=\"15\" src=\"images/shopping_cart/cart.png\" border=\"0\"> You have $cart_count item$plural in your cart. <a href=\"site.php?action=cart_view\">View my cart</a> | <a href=\"checkout.html\">Proceed to checkout</a></p><hr size=\"1\" /></div>";
	
}

?>


