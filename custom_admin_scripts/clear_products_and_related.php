<?php

// run through the system products table and load descriptions from test_releases..

require_once("../library/database.php");
$sql="DELETE FROM products WHERE 1;";
$res=mysql_query($sql) or die ("Cannot delete products");

$sql = "DELETE FROM artists WHERE 1";
$res=mysql_query($sql) or die ("Cannot delete artists");

$sql = "DELETE FROM labels WHERE 1";
$res=mysql_query($sql) or die ("Cannot delete labels");

print "\nDeleted products, artists, labels";

?>
