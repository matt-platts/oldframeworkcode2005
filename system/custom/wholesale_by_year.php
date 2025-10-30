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
$curmonth=date("m");
$current_year=date("Y");
if ($curmonth >7){
	$year_start=$current_year+1;
} else {
	$year_start=$current_year;
}
$inc=0;
$year_count=$year_start;
while ($year_count>2000){
	$options.="<option value=\"$year_count\"";
	if (!$_REQUEST['year'] && $year_count == $current_year){ $options .= " selected"; } else if ($_REQUEST['year'] == $year_count){ $options .= " selected";}
	$options .= ">$year_count</option>";
	$year_count--;
	$inc++;
if ($inc > 100){   exit;}
}
?>

<h1 style="border-bottom:1px black solid; margin-left:0px; padding-left:0px">Catalogue By year</h1>
<form name="yearform" method="get" action="/wholesale-by-year/">
<select name="year">
<?php echo $options;?>

</select>
<input type="submit" value="Browse Year" />
</form>
<?php


$year=$_REQUEST['year'];
if (!$year){ $year=$current_year;}

$year_start="$year-01-01";
$year_end="$year-12-12";

global $db;
$SQL = "SELECT products.*, artists.artist AS name, price_formats.pdp as pf_pdp, DATE_FORMAT(products.release_date,\"%D %M %Y\") AS datetext FROM products 
INNER JOIN artists ON products.artist=artists.id 
INNER JOIN price_formats ON products.price_format = price_formats.id 
WHERE release_date > \"$year_start\" AND release_date < \"$year_end\" AND (hidden_on_trade_site=0 OR hidden_on_trade_site IS NULL) ORDER BY release_date DESC";

$rv=$db->query($SQL);
print "<p>Found " . mysql_num_rows($rv) . " products released in $year</p>";
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
	print "<br />Dealer price: &pound;" . $h['pf_pdp'];
	if ($h['full_description']){
		$h['full_description']=strip_tags($h['full_description']);
		print "<p>".substr($h['full_description'],0,200).".. <a href=\"$pagelink\"> read more</a></p>";
	} else {
		print "<p><a href=\"$pagelink\">View Release Details</a></p>";
	}

	print "</td></tr></table>";
}

?>

