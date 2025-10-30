<?php

// lists products which are in stock at flight with qty > 0 but do not appear in our database
require_once("../../config.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");

$embedded="";
if (!$db && !$mycart && !$basepath){
	$db=new database_connection();
	$mycart=new shopping_cart();
} else {
	$embedded="<br />";
}

$url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rtStockLevels&OmnisLibrary=Stock&OmnisServer=5912&ivCompanySeq=74";

$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$returned_data=curl_exec($ch);
curl_close($ch);

if (empty($returned_data)){
	$return="Unable to get stock levels";
} else {

	$data_pairs=explode("~~~",$returned_data);

	$count=0;
	foreach ($data_pairs as $data_pair){
		@list($catno,$qty)=explode("~",$data_pair);
		//print $catno . "-".$qty."\n";
		$sql="SELECT ID from products where catalogue_number = \"$catno\"";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);
		$product_id=$h['ID'];
		if (!$product_id && $qty > 0){
			print $catno . $embedded . "\n"; 
		}
		
	}
}
	//$counter++;


?>
