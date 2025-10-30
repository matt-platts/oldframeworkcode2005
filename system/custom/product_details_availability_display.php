<?php

$id=$argv[1];
$available=$argv[2];
$stock_quantity=$argv[3];
$price=$argv[4];
if ($stock_quantity>=10){
	$available="In Stock"; 
	//$available=$stock_quantity . " in stock";
} else if ($stock_quantity=="OutOfStock"){
	$available="Sorry - currently out of stock.";
} else if ($stock_quantity==0){
	$available="Sorry - Not currently available";
} else if ($stock_quantity <10){
	//$available="Less than 10 in stock.";
	$available=$stock_quantity . " in stock";
}
if ($price==0 || $price==""){ $available="Sorry - Not currently available"; }
$output = "<b>Available:</b> $available<br>";
print "$output";
?>


