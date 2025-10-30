<style type="text/css">
h3 {background-image:none; color:black; border-bottom:1px #333 solid;
padding-left:0px;
width:100%;
margin-left:0px;
font-size:13px;
}
img {border:1px black solid; padding:3px; border-radius:4px;}
</style>
<?php
?>

<h1 style="border-bottom:1px black solid; margin-left:0px; padding-left:0px">Forthcoming Products</h1>
<form name="yearform" method="get" action="/wholesale-by-year/">
<?php


global $db;
$SQL = "SELECT products.*, artists.artist AS name, DATE_FORMAT(products.release_date,\"%D %M %Y\") AS datetext FROM products INNER JOIN artists ON products.artist=artists.id WHERE release_date > NOW() ORDER BY release_date ASC";
$rv=$db->query($SQL);
print "<p>Found " . mysql_num_rows($rv) . " products scheduled for upcoming release.</p>";
while ($h=$db->fetch_array()){
	if ($h['release_date'] != $cur_date){
		$cur_date=$h['release_date'];
		print "<br /><h3>".$h['datetext']."</h3>";
	}
	$pagename=$h['name']."_".$h['title'];
	$pagename=str_replace(" ","-",$pagename);
	$pagelink="one-sheet/".$h['ID']."/".$pagename;
	print "<table><td><td><a href=\"$pagelink\"><img src=\"/images/product_images/web_quality/".$h['image']."\" width=\"120\" style=\"margin-right:20px\"></a>";
	print "</td><td valign=\"top\">";
	print "<a href=\"$pagelink\" target=\"_blank\" style=\"color:black; font-weight:bold\">";
	print $h['name'] . " - ";
	print $h['title'];
	print "</a><br />";
	print $h['catalogue_number'];
	print $h['cat_no'];
	print "<br />Dealer price: &pound;" . $h['pdp'];
	if ($h['full_description']){
		$h['full_description']=strip_tags($h['full_description']);
		print "<p>".substr($h['full_description'],0,200).".. <a href=\"$pagelink\"> read more</a></p>";
	} else {
		print "<p><a href=\"$pagelink\">View Release Details</a></p>";
	}

	print "</td></tr></table>";
}

?>

