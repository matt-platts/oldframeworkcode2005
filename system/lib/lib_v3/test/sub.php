<?php

/*$t= sprintf("%4.2f",132.33+(132.33/100*20));
print $t;
exit;
*/

$q='SELECT orders.id AS order_number, user.first_name AS first_name, user.second_name AS second_name, 
user.trade_customer AS trade_customer, user.sage_account_name as sage_account_name, orders.total_amount AS product_total, 
orders.shipping_total AS shipping, 
(SELECT amount AS VAT FROM order_total_extras WHERE module="vat_itemisation" AND order_total_extras.order_id=orders.id), 
(SELECT amount AS Volume_Discount FROM order_total_extras WHERE module="volume_discount" AND order_total_extras.order_id=orders.id), 
(SELECT amount AS Vouchers FROM order_total_extras WHERE module="gift_vouchers_complex" AND order_total_extras.order_id=orders.id), 
orders.grand_total AS grand_total FROM orders INNER JOIN user on orders.ordered_by=user.id 
WHERE orders.complete=1';

$pattern="/\( ?SELECT \w+ AS \w+ FROM .*\)/";
$match=preg_match_all($pattern,$q,$matches);
foreach ($matches[0] as $each){
	$orig=$each;
	print $each . "\n\n";
	$each=str_replace("(","",$each);
	$each=str_replace("SELECT","",$each);
	$bits=explode("FROM",$each);
	$each=trim($bits[0]);
	print $each . "\n";
	$q=str_replace($orig,$each,$q);
}


$actual_query=$q;
$queryfields=preg_split("/ FROM /i",$actual_query);
$queryfields=$queryfields[0];
$queryfields = preg_replace("/SELECT /i","",$queryfields);
$queryfields = trim($queryfields);

//if ($queryfields = "*"){ print "its all fields"; }
print "<!-- mattplatts - returning $queryfields //-->";



?>
