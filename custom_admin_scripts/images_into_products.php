<?php

// run through the system products table and load descriptions from test_releases..

require_once("../library/database.php");
/*
$sql="UPDATE products INNER JOIN test_releases ON products.catalogue_number = test_releases.CatNumber SET products.full_description = test_releases.Information";
$res=mysql_query($sql);
print "DONE";
exit;
*/
$sql="SELECT catalogue_number FROM products"; 
$found=0;
$lc=0;
$uc=0;
$ucle=0;
$counter=0;
$res=mysql_query($sql);
while ($h=mysql_fetch_array($res)){
	$imagefile="../images/products/".trim($h['catalogue_number']) . ".jpg";
	$sql2="";
	print $imagefile . ": ";
	if (file_exists($imagefile)){
		$sql2="UPDATE products set image = \"". $h['catalogue_number'] . ".jpg\" WHERE products.catalogue_number = \"" . $h['catalogue_number'] . "\"";
		$found++;
		print "file exists\n";
	} elseif (file_exists(strtolower($imagefile))){
		print "lower case file exists\n";
		$sql2="UPDATE products set image = \"". strtolower($h['catalogue_number']) . ".jpg\" WHERE products.catalogue_number = \"" . $h['catalogue_number'] . "\"";
		$lc++;
	} elseif (file_exists(strtoupper($imagefile))){
		print "upper case file exists\n";
		$uc++;
	} elseif (file_exists(str_replace("JPG","jpg",strtoupper($imagefile)))){
		print "upper case file exists with lower case ext.\n";
		$ucle++;
	} else {
		print "does not exist\n";
	}
	if ($sql2){
		$res2=mysql_query($sql2) or die ("Error on line $counter (" . $h['catalogue_number'] . ") in $sql: " . mysql_error());
	}
	$counter++;
}

print "Found $found, $lc in lower case, $uc in upper, $ucle in upper case with le out of $counter";

// noew trim catalogue numbers
//$sql="update products set catalogue_number=TRIM(catalogue_number)";
//$res=mysql_query($sql);
//print "\nAlso trimmed catalogue numbers of whitespace - required for images etc.";

?>
