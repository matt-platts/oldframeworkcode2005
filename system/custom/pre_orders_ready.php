<?php

if ($user->value("id")){
$sql="SELECT *,IF (((DATE_SUB(NOW(), INTERVAL 7 DAY) > release_date) OR (release_date < NOW())) AND quantity_in_stock>0,\"ready\",\"not ready\") AS ready, IF (release_date < NOW(),\"released\",\"not released\") AS released, if (quantity_in_stock>0 AND release_date < NOW(),\"attention required\",0) as attention_required FROM orders INNER JOIN order_products ON orders.id=order_products.order_id INNER JOIN products ON order_products.product_id = products.ID INNER JOIN artists ON artists.id = products.artist WHERE complete=5 AND orders.ordered_by = " . $user->value("id");
$v=$db->query($sql);
if (mysql_num_rows($v)>0){
	print "<h3 style=\"font-weight:normal; background-image:none; background-color:#990000; border-radius:5px;\" >Your Current Pre-Orders</h3>";
	print "<table>";
	print "<tr style=\"font-weight:bold\"><td>Order No.</td><td>Payment Method</td>";
}
while ($h=$db->fetch_array()){
	print "<tr><td><a href=\"/view_order_details/".$h['order_id']."\">#".$h['order_id']."</a></td><td>";
	if ($h['payment_method']=="sagepay_direct"){ print "Credit Card";}
	if ($h['payment_method']=="paypal_express_checkout"){ print "Paypal"; }
	print "</td><td></td>";
	print "</tr>";
	print "<tr><td colspan=3 style=\"font-size:10px\">".$h['artist']." - " . $h['title'] . "</td></tr>";
	if ($h['released']=="not released"){
		print "<tr><td colspan=3 style=\"font-size:10px\">Due in: ".$h['release_date']."</td></tr>";
	} else {
		print "<tr><td colspan=3 style=\"font-size:10px\">Released on: " . $h['release_date']."</td></tr>";
	}

	if ($h['ready'] == "ready" && $h['payment_method']=="paypal_express_checkout"){
		print "<tr><td colspan=3>This order is now in stock - <a href=\"/pre-order-paypal/".$h['order_id']."/\">Pay Now</a></td></tr>";
	}
	if ($h['ready'] == "ready" && $h['payment_method']=="sagepay_direct"){
		print "<tr><td colspan=3><div style=\"padding:5px; background-color:#f1f1f1\"><p style=\"font-size:11px\">". " This credit card authorisation failed. We keep trying to take payment for one month from release date. Please ensure that you have sufficient funds on your card. You can also <a href=\"/pre-order-paypal/".$h['order_id']."/\">pay Now with Paypal</a>.";

		print "<br />To re-enter card details please <a href=\"/load-order/".$h['order_id']."/\">click here</a>. Please email <a href=\"mailto:admin@gonzomultimedia.co.uk\">admin@gonzomultimedia.co.uk</a> if you would like us to try and put your card through again manually.</p></div>";
		print "</td></tr>";
	}
	if ($h['ready'] == "not ready" && $h['quantity_in_stock']==0){
		print "<tr><td colspan=3><span style=\"font-size:11px\">Currently not in stock.</span></td></tr>";
	}
	print "<tr><td colspan=3><hr size=1 color=\"#f1f1f1\"></td></tr>";
	print "<tr><td>&nbsp;</td></tr>";
}

print "</table>";
}
?>
