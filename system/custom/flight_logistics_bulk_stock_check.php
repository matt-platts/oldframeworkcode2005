<?php

/* Connection details - edit the fields below only */
$url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rtStockLevels&OmnisLibrary=Stock&OmnisServer=5912&ivCompanySeq=74";
$flight_external_data_field="catalogue_number";
/* end editable section */

$web=0;
if ($db){
	// we are in main directory as running through the admin
	require_once("config.php");
	global $libpath;
	$web=1;
	print "
	<style type=\"text/css\">
		.pcode {float:left; display:block; clear:both; width:110px;}
		.msg {float:left; display:block;}
		.err_msg {float:left; display:block; color:#cc0000;}
	</style>";
} else {
	require_once("../../config.php");
}


require_once("$libpath/errors.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");


$embedded="";
if (!$db && !$mycart){
	$db=new database_connection();
	$mycart=new shopping_cart();
} else {
	$embedded="<br />";
}


$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$returned_data=curl_exec($ch);
curl_close($ch);

if (empty($returned_data)){
	$return="Temporarily unable to connect to Flight Logistics - please try again shortly. If this problem persists please get in touch with Flight Logistics directly.";
} else {

	$data_pairs=explode("~~~",$returned_data);

	$count=0;
	foreach ($data_pairs as $data_pair){
		@list($catno,$qty)=explode("~",$data_pair);
		if (!$catno || !$qty){ continue; }
		//print $catno . "-".$qty."\n";
		$sql="\nSELECT ID,price,hidden from products where $flight_external_data_field = \"$catno\"";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);
		$product_id=$h['ID'];
		$price=$h['price'];
		$hidden=$h['hidden'];
		if (!$product_id && $qty > 0 || $product_id==""){
			$message =  "<span class=\"pcode\">$catno</span><span class=\"err_msg\"> Flight Logistics returned data for the product code $catno, but no such product exists in the local database.</span>\n";
			if (!$web) { $message = strip_tags($message); }
			print $message;
			continue;
		}
		$num_qty=$qty;
		$available=1;
		$available_text="Available";
		if (!preg_match("/^\d+$/",$num_qty)){ $num_qty=0; $available=0; $available_text = "Not Available";}
		if ($qty==0){$available=0;}
		if ($hidden==1){$available=0;}
		if ($price==0 || $price==""){
			$available=0;
			$available_text="<span style=\"color:#cc0000\">Not Available (No Local Price Set)</span>";
		}
		$sql="UPDATE products SET stock_quantity = '$qty', quantity_in_stock = $num_qty, available = $available WHERE ID = $product_id";
		$res=$db->query($sql);
		$message = "<span class=\"pcode\">$catno</span><span class=\"msg\">Set quantity to $qty (Numeric:$num_qty, Local Id: $product_id) and available to $available_text</span> $embedded \n";
		if (!$web) { $message = strip_tags($message); }
		print $message;
	}
}
	//$counter++;

?>
