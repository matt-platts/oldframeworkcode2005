<?php

if ($_GET['action']=="cart_mailer_add_to_my_list"){
	$file=$libpath."/classes/shopping_cart_mailinglist.php";
	require_once($file);
	
	$cart_mailer=new shopping_cart_mailinglist;
	global $user;
	$item=$_REQUEST['cart_mailer_item'];
	$value=$_REQUEST['cart_mailer_value'];
	$user_id=$user->value("id");
	$return_text=$cart_mailer->add_to_my_list($item,$value,$user_id);
	$title="Mailing List";
	$content=$return_text;
}

if ($_GET['action']=="cart_mailer_remove_from_list"){
	$file=$libpath."/classes/shopping_cart_mailinglist.php";
	require_once($file);
	
	$cart_mailer=new shopping_cart_mailinglist;
	global $user;
	$item=$_REQUEST['cart_mailer_item'];
	$value=$_REQUEST['cart_mailer_value'];
	$user_id=$user->value("id");
	$return_text=$cart_mailer->remove_from_my_list($item,$value,$user_id);
	$title="Mailing List";
	$content=$return_text;
}

?>
