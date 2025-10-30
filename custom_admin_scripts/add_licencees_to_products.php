<?php

require_once("../library/database.php");

$csv=file_get_contents("../io/sunlight_final.csv");
$rows=explode("\n",$csv);
$i=0;
foreach ($rows as $row){
	if ($i==0){$i++; continue;}
	$fields=explode(";",$row);
	$fields[4]=str_replace("\"","",$fields[4]);
	$fields[58]=str_replace("\"","",$fields[58]);
	if (!$fields[4] || !$fields[58]){ continue; }
	print $i . " - " . $fields[4] . " - " . $fields[58] . "\n";
	$sql= "SELECT * from licencees where contact_name = \"" . $fields[58] . "\"";
	$res=mysql_query($sql) or die (mysql_error());
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		print $h['id'] . "\n";
		$licencee_id=$h['id'];
	}
	$sql= "SELECT * from products where catalogue_number = \"" . $fields[4] . "\"";
	$res=mysql_query($sql) or die (mysql_error());
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		print $h['ID'] . "\n";
		$product_id=$h['ID'];
	}

	$usql="INSERT INTO product_licencees (product,licencee) values(\"$product_id\",\"$licencee_id\")";
	$ures=mysql_query($usql) or die (mysql_error());
	print "updated $i with $product_id, $licencee_id\n";
	$i++;
}
exit;
