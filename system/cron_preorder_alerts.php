<?php
chdir ("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/system/custom");
require_once("../../config.php");
require_once("$libpath/errors.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");
if (!$db && !$mycart){
	$db=new database_connection();
}


// SQL QUERY TO FIND PREORDERS TO PAY FOR
$sql= "SELECT products.id AS product_id, products.catalogue_number AS cat_no, artists.artist AS artist, products.title AS title, DATE_FORMAT(products.release_date,\"%d-%m-%Y\") AS date_released, products.price AS price, products.quantity_in_stock AS qty_in_stock, SUM(order_products.quantity) AS quantity_on_preorder, IF (release_date < NOW(),\"LATE\",\"OK\") AS late FROM products LEFT JOIN order_products on products.id = order_products.product_id LEFT JOIN orders on order_products.order_id = orders.id INNER JOIN artists on products.artist=artists.id WHERE products.allow_pre_orders=1 AND (orders.complete IS NULL OR orders.complete=5) AND (products.release_date =0 OR products.release_date >= DATE_SUB(NOW(),INTERVAL 5 DAY)) AND products.release_date <= DATE_ADD(NOW(),INTERVAL 7 DAY) GROUP BY products.id";
//$sql .= " LIMIT 10";
$rv=$db->query($sql);
while ($h=$db->fetch_array($rv)){
	if ($h['date_released']==0){
		$message .= $h['artist'] . " - " . $h['title'] . " has no release date (" . $h['date_released'] . ") - PLEASE CHECK<br />\n";
	} else if ($h['late']=="LATE" && ($h['qty_in_stock']==0 || !is_numeric($h['qty_in_stock']))){
		$message .= $h['artist'] . " - " . $h['title'] . " was due into stock on " . $h['date_released'] . " and is currently reporting stock of: " . $h['qty_in_stock'] . ". - LATE<br />\n";
	} else if ($h['qty_in_stock']==0 || !is_numeric($h['qty_in_stock'])){
		$message .= $h['artist'] . " - " . $h['title'] . " is due into stock on " . $h['date_released'] . " and has no stock as yet - DUE VERY SOON -  PLEASE CHECK<br />\n";
	}
}
if ($message){
	$to="mattplatts@gmail.com, annemarie.hill@gmail.com";
	$headers="From: \"Preorder Web Bot \" <web-bot@gonzomultimedia.co.uk>\r\nContent-Type:text/html\r\n";
	$subject="Preorders needing attention on the Gonzo database";
	$message = "<p>The following titles are late or due into stock in the next week:</p>" . $message;
	mail ($to,$subject,$message,$headers);
}

print $message;
?>
