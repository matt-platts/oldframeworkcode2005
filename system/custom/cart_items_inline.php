<?php

$cart_count=0;
global $user;
foreach ($_SESSION['cart'] as $item => $itemdata){
	$cart_count++;
	}
foreach ($_SESSION['preorder_cart'] as $item => $itemdata){
	$cart_count++;
	}

if ($cart_count>0){
	print "<div id=\"inline_shopping_cart\" style=\"height:115px; width:200px; border-radius:8px; border:1px #333 solid; margin-bottom:10px;\">";
	print "<p style=\"color:#111; font-weight:bold; padding-top:3px; padding-bottom:0px; padding-top:0px; margin-top:4px; background-color:#1b2c67; color:white; position:relative; top:-4px; border-top-left-radius:8px; border-top-right-radius:8px;\">Shopping Cart</p>";
	if ($cart_count>1){$plural="s";}
	print "<p style=\"font-size:12px; line-height:15px\">You have $cart_count item$plural in <br />your shopping cart.</p>";
	print "<p style=\"padding-top:0px; padding-bottom:0px; margin-bottom:0px; margin-top:4px;\"><img src=\"".HTTP_PATH."/images/shopping_cart/cart.png\" width=\"15\"> <a href=\"".HTTP_PATH."/site.php?action=cart_view\">View Cart</a>";
	print " | <img src=\"".HTTP_PATH."/images/shopping_cart/red_card.jpg\" width=\"15\"> &nbsp;<a href=\"".HTTP_PATH."/checkout.html\">Checkout</a></p>";
	print "</div>";
	if ($user->value("id")){
	$sql="SELECT *,IF (((DATE_SUB(NOW(), INTERVAL 7 DAY) > release_date) OR (release_date < NOW())) AND quantity_in_stock>0,\"ready\",\"not ready\") AS ready, IF (release_date < NOW(),\"released\",\"not released\") AS released, if (quantity_in_stock>0 AND release_date < NOW(),\"attention required\",0) as attention_required FROM orders INNER JOIN order_products ON orders.id=order_products.order_id INNER JOIN products ON order_products.product_id = products.ID INNER JOIN artists ON artists.id = products.artist WHERE complete=5 AND orders.ordered_by = " . $user->value("id");
	$v=$db->query($sql);
	if (mysql_num_rows($v)>0){
		print "<div id=\"inline_preorders\" style=\"width:200px; margin-bottom:10px; background-color:white; border-radius:8px; border:1px #1b2c67 solid;\">";
		print "<a href=\"/account.html\">Click here to view your current pre-orders</a>";
		print "</div>";
	}
	}
}

?>
