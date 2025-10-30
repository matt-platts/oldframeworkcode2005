<?php

require_once("../../config.php");
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
		$sql="SELECT ID,price,hidden from products where catalogue_number = \"$catno\"";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);
		$product_id=$h['ID'];
		$price=$h['price'];
		$hidden=$h['hidden'];
		if (!$product_id && $qty > 0){
			continue;
		}
		$num_qty=$qty;
		$available=1;
		if (!preg_match("/^\d+$/",$num_qty)){ $num_qty=0; $available=0; }
		if ($qty==0){$available=0;}
		if ($hidden==1){$available=0;}
		if ($price==0 || $price==""){
			$available=0;
		}
		$sql="UPDATE products SET stock_quantity = '$qty', quantity_in_stock = $num_qty, available = $available WHERE ID = $product_id";
		$res=$db->query($sql);
		print "Set quantity for ID: $product_id ($catno) to $qty (Numeric:$num_qty) and available to $available" . $embedded . "\n";
	}
}
	//$counter++;

?>
