<?php


// SQL QUERY TO FIND PREORDERS TO PAY FOR
$sql= "SELECT products.id AS product_id, products.catalogue_number AS cat_no, artists.artist AS artist, products.title AS title, DATE_FORMAT(products.release_date,\"%d-%m-%Y\") AS date_released, products.price AS price, products.quantity_in_stock AS qty_in_stock, SUM(order_products.quantity) AS quantity_on_preorder, IF (release_date < NOW(),\"LATE\",\"OK\") AS late FROM products LEFT JOIN order_products on products.id = order_products.product_id LEFT JOIN orders on order_products.order_id = orders.id INNER JOIN artists on products.artist=artists.id WHERE products.allow_pre_orders=1 AND (orders.complete IS NULL OR orders.complete=5) AND (products.release_date =0 OR products.release_date >= DATE_SUB(NOW(),INTERVAL 5 DAY)) AND products.release_date <= DATE_ADD(NOW(),INTERVAL 7 DAY) GROUP BY products.id";
//$sql .= " LIMIT 10";
$rv=$db->query($sql);
while ($h=$db->fetch_array($rv)){
	if ($h['date_released']==0){
		$message .= $h['artist'] . " - " . $h['title'] . " has no release date (" . $h['date_released'] . ") - PLEASE CHECK\n";
	} else if ($h['late']=="LATE" && ($h['qty_in_stock']==0 || !is_numeric($h['qty_in_stock']))){
		$message .= $h['artist'] . " - " . $h['title'] . " was due into stock on " . $h['date_released'] . " and is currently reporting stock of: " . $h['qty_in_stock'] . ". - <font color='red'>LATE</font>\n";
	} else if ($h['qty_in_stock']==0 || !is_numeric($h['qty_in_stock'])){
		$message .= $h['artist'] . " - " . $h['title'] . " is due into stock on " . $h['date_released'] . " and has no stock as yet - DUE VERY SOON -  PLEASE CHECK\n";
	}
	$message .= "<a href=\"javascript:parent.loadPage('/mui-administrator.php?action=edit_table&t=products&edit_type=edit_single&rowid=".$h['product_id']."&dbf_edi=1&dbf_edi=1&dbf_mui=1&jx=1&iframe=1','Products: Edit: ".$h['title']."')\">Edit Product</a><br /><br />\n";
}
if ($message){
	print $message;
}
 else {
	print "No products require attention at the moment.";
}
?>
