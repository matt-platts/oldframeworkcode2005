<?php

global $db;
$sql="UPDATE artists SET active =0 WHERE 1";
$res=$db->query($sql) or die("Point 1 error");
$sql="UPDATE artists LEFT JOIN products ON products.artist = artists.id SET artists.active=1 WHERE products.price != \"\" AND products.price IS NOT NULL AND (products.available=1 OR (products.allow_pre_orders=1 AND products.release_date IS NOT NULL))";
$res=$db->query($sql) or die("Oops an error happened " . mysql_error());
print "<p>Artists have been updated to only display where products are available in them.</p>";
print "<p><b>Active artists:</b></p>";

$sql="SELECT DISTINCT artists.artist as artist FROM artists LEFT JOIN products ON products.artist = artists.id WHERE products.price != \"\" AND products.price IS NOT NULL AND (products.available=1 OR (products.allow_pre_orders=1 AND products.release_date IS NOT NULL))";
$rv=$db->query($sql);
while ($h=$db->fetch_array()){
	print $h['artist'] . "<br />\n";
}
?>
