<?php

global $db;
$sql="UPDATE products INNER JOIN price_formats on products.price_format = price_formats.id SET products.price=price_formats.web_price, products.price_in_dollars = price_formats.usd_price";
$res=$db->query($sql) or die("Oops an error happened");

print "<p class=\"dbf_para_success\">All product prices have now been set according to the price formats lookup table.</p>";

$sql2="SELECT ID,catalogue_number,title FROM products WHERE price_format = 0";
$res2=$db->query($sql2) or die("Oops an error happened");

$count_unpriced_products=mysql_num_rows($res2);
if ($count_unpriced_products>=1){
print "<p>There are $count_unpriced_products products with no price format set for them</p>";
print "<p><a href=\"Javascript:parent.loadPage('administrator.php?action=list_query_v2&q=Products No Price Format&filter_id=0&dbf_mui=1&jx=1&iframe=1','Run Query')\">Click here to view these products</a></p>";
} else {
	print "All products updated successfully.";
}

?>

