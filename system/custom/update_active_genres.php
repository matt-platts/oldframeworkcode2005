<?php

global $db;
$sql="UPDATE genres SET products_available=0 WHERE 1";
$res=$db->query($sql) or die("Point 1 error");
$sql="UPDATE genres LEFT JOIN products ON products.genre=genres.id SET genres.products_available=(genres.products_available+1) WHERE products.price != \"\" AND products.price IS NOT NULL AND products.available=1";
$res=$db->query($sql) or die("Oops an error happened");
print "<p>Genres have been updated to only display where products are available in them.</p>";

?>
