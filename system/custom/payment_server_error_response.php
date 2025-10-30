<?php

$order_id=$_SESSION['order_id'];
$sql="SELECT Status FROM sagepay_responses WHERE order_id = $order_id";
$res=$db->query($sql);
while ($h=$db->fetch_array()){
	$status_msg= $h['Status'];
}

$status_msg=str_replace(" - ",": <br />",$status_msg);
print $status_msg;
?>

