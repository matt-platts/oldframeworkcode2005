<?php

// run through the system products table and load descriptions from test_releases..

require_once("../library/database.php");
/*
$sql="UPDATE products INNER JOIN test_releases ON products.catalogue_number = test_releases.CatNumber SET products.full_description = test_releases.Information";
$res=mysql_query($sql);
print "DONE";
exit;
*/
$sql="SELECT catNumber,releaseDate FROM tis_releases";
$counter=0;
$res=mysql_query($sql);
while ($h=mysql_fetch_array($res)){
	$sql="UPDATE products set release_date = \"". $h['releaseDate'] . "\" WHERE products.catalogue_number = \"" . $h['catNumber'] . "\"";
	$res2=mysql_query($sql) or die ("Error on line $counter (" . $h['catNumber'] . ") in $sql: " . mysql_error());
	$counter++;
}

print "DONE $counter lines";
exit;
// noew trim catalogue numbers
$sql="update products set catalogue_number=TRIM(catalogue_number)";
$res=mysql_query($sql);
print "\nAlso trimmed catalogue numbers of whitespace - required for images etc.";

?>
