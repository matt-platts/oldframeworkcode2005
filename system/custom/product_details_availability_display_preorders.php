<?php

$id=$argv[1];
$available=$argv[2];
$stock_quantity=$argv[3];
$price=$argv[4];
$release_date=$argv[5];
$allow_pre_orders=$argv[6];
$exp_release_date=explode("-",$release_date);
$epoch_release_date=mktime(0,0,0,$exp_release_date[1],$exp_release_date[2],$exp_release_date[0]);
if ($stock_quantity==0){ $stock_quantity="";}

if ($stock_quantity>=10){
	$available="In stock"; 
	//$available=$stock_quantity . " in stock";
} else if ($stock_quantity=="OutOfStock"){
	$available="Sorry - currently out of stock.";
} else if ($stock_quantity==0){
	if($allow_pre_orders && $current_time < $epoch_release_date){
		$available="For Pre Order";
	} else{
		$available="Sorry - Not currently available";
	}
} else if ($stock_quantity <10){
	//$available="Less than 10 in stock.";
	$available=$stock_quantity . " in stock";
}
if ($price==0 || $price==""){ $available="Sorry - Not currently available"; }
$output = "<b>Available:</b> $available<br>";
print "$output";
?>


