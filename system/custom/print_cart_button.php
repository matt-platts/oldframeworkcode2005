<?php

$pid=$argv[1];
$release_date=$argv[2];
$available=$argv[3];
$allow_pre_orders=$argv[4];
//$available_for_pre_order=$argv[5];
//if ($available_for_pre_order){ $available=0;}
$exp_release_date=explode("-",$release_date);
$epoch_release_date=mktime(0,0,0,$exp_release_date[1],$exp_release_date[2],$exp_release_date[0]);
$current_time=time();

if ($allow_pre_orders && ($current_time<$epoch_release_date)){
	print "<script language=\"Javascript\">\n";
	print "document.forms['add_product_to_cart'].action=\"/site.php?action=preorder_cart_add&product_id=$pid\";\n";
	print "</script>\n";
	print "<a href=\"Javascript:document.forms['add_product_to_cart'].submit()\"><img src=\"/images/shopping_cart/cart-add.png\" style=\"border-radius:10px\"></a>";
	print "<br /><span style=\"font-size:12px\">Click to pre-order now!</span>";
} else if ($available){
	print "<a href=\"Javascript:document.forms['add_product_to_cart'].submit()\"><img src=\"/images/shopping_cart/cart-add.png\" style=\"border-radius:10px\"></a>";
}
//print date("jS F Y", $epoch_release_date);
//print date("jS F Y");

?>
