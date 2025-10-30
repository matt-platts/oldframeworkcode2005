<?php
chdir ("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/system/custom");

$web=0;
if ($db){
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
	$db="";
	$mycart="";
	require_once("../../config.php");
}

require_once("$libpath/errors.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");
//$mail_first=cron_mail("Cron: Beginning Stock Update","About to start updating product catalogue from cron.\n");

$embedded="";
if (!$db){
	$db=new database_connection();
}
if (!$mycart){
	$mycart=new shopping_cart();
}

$sql="SELECT ID,catalogue_number,hidden FROM products";
$counter=0;
$res=$db->query($sql);
while ($h=$db->fetch_array($res)){
	$product_id=$h['ID'];
	$hidden=$h['hidden'];
	if (!$h['catalogue_number']){
		$msg = "<span class=\"pcode\"></span><span class=\"err_msg\">There is no catalogue number for product id " . $h['ID'] . ".</span>$embedded";
		if (!$web){
			$msg=strip_tags($msg);
		}
		print $msg;
		continue;
	}
	$stock_qty=$mycart->check_stock_quantity($product_id,5);
	$numeric_stock_quantity=$stock_qty;
	if (!preg_match("/^\d+$/",$numeric_stock_quantity)){ $numeric_stock_quantity=0; }
	if ($numeric_stock_quantity >=0){$available=1; $available_text = "Available"; }else{ $available=0; $available_text="Not Available";}
	if ($stock_qty==0){$available=0; $available_text="Not Available";}
	if ($hidden==1){$available=0; $available_text="Not Available (Product set to hidden in local database)";}
	$update="UPDATE products SET stock_quantity='$stock_qty',quantity_in_stock='$numeric_stock_quantity',available=$available WHERE id = $product_id";
	$msg="<span class=\"pcode\">".$h['catalogue_number']."</span><span class=\"msg\">Set quantity to $stock_qty ($numeric_stock_quantity)</span>$embedded\n";
	if (!$web){
		$msg=strip_tags($msg);
	}
	print $msg;
	$update_result=$db->query($update);
	$counter++;
}

cron_mail("Cron: Completed Stock Update","The web robot has updated $counter products.\n");

function cron_mail($subject,$message){

	$to="mattplatts@gmail.com";
	$subject="Cron: Beginning Stock Update (Medico Beauty)";
	$message="About to start updating product catalogue from cron.\n";
	$headers="From: website@paragon-digital.net\n\r";

	mail($to,$subject,$message,$headers);
	return;

}
?>
