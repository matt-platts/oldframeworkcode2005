<?php

if ($_SESSION['cart']){
$text="<li><span style=\"background-color:#000;\"><img src=\"".HTTP_PATH."/images/shopping_cart/cart.png\" width=\"15\" height=\"14\" style=\"position:relative; top:-1px;\"></span> <a href=\"".HTTP_PATH."/view_cart.html\"><span style=\"color:orange\">View Cart</span></a></li>";
$text.="<li><span style=\"background-color:#000;\"><img src=\"".HTTP_PATH."/images/shopping_cart/card_red.png\" width=\"18\" height=\"13\" style=\"position:relative; top:-1px;\"></span> <a href=\"".HTTP_PATH."/checkout.html\"><span style=\"color:#dd0000\">Checkout</span></a></li>";
}
print $text;

?>
