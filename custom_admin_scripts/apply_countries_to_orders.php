<?php

// run through the system products table and load descriptions from test_releases..
$database_username = "voice2user";
$database_password = "voice2pass";
$database_hostname = "localhost";
$database_name = "voice2";

$dbh = mysql_connect($database_hostname, $database_username, $database_password) or die("Unable to connect to MySQL server.");
if (isset($debug)) {print "Connected to MySQL<br>";}

$selected = mysql_select_db($database_name,$dbh) or die("Could not select database");
if (isset($debug)){ print "selected"; }

/*
$sql="UPDATE products INNER JOIN test_releases ON products.catalogue_number = test_releases.CatNumber SET products.full_description = test_releases.Information";
$res=mysql_query($sql);
print "DONE";
exit;
*/
$sql="SELECT orders.id,IF(user.same_as_billing_address,country,delivery_country) AS order_country from orders INNER JOIN user ON orders.ordered_by=user.id WHERE orders.order_country IS NULL";
$res=mysql_query($sql);

while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
	$eusql="SELECT eu_country FROM countries WHERE ID = " . $h['order_country'];
	$rv=mysql_query($eusql);
	$h1=mysql_fetch_array($rv,MYSQL_ASSOC);
	if ($h1['eu_country']){
		$vatable=1;
	} else {
		$vatable=0;
	}
	
	$updatesql="UPDATE orders SET order_country = " . $h['order_country'] . ", vatable=$vatable WHERE id = " . $h['id'];
	print $updatesql . "\n";
	$r2=mysql_query($updatesql) or die (mysql_error());
}

