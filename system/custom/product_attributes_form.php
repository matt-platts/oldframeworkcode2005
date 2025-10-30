<?php

include_once("../../config.php");
include_once("$libpath/classes/database.php");

$db=new database_connection();
$product_id=$argv[1];
$override_option = $argv[2];

$sql = "SELECT * from products_to_product_attributes INNER JOIN product_attributes ON products_to_product_attributes.attribute_id = product_attributes.id WHERE products_to_product_attributes.product_id = $product_id";
$res=$db->query($sql);
$count=0;
while ($h=$db->fetch_array($res)){
	if ($count==0){ print "<table border=\"0\">\n"; }
	print '<tr><td style="text-align:right; font-weight:bold; color:\1b2c67">';
	print $h['attribute_name'];
	$attribute_name=str_replace(" ","_",$h['attribute_name']);
	print ":</td><td><input name=\"$attribute_name\" type=\"text\" ";
	if ($h['field_width']){ print "style=\"width:" . $h['field_width'] ."px\" ";} 
	print "/> ";
	if ($h['append_field_with']){ print $h['append_field_with'];}
	print "</td></tr>";
	$count++;
}
if (mysql_num_rows($res)>=1){
	print "</table>\n";
	print '<p style="color:#1b2c67">Please check this information carefully before confirming your order.</p>';
}


?>
