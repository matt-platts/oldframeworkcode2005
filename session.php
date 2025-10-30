<?php
session_start();
if ($_GET['clear']){
	session_unset();
	session_destroy();
	unset($_COOKIE['login']);
	print "CLEARED SESSION<br>";
}

print "<p>Dumping cookie data</p>";
print "<pre>";
var_dump($_COOKIE);
print "</pre>";
print "<hr>";
print "<p>Dumping session data</p>";
print "<pre>";
var_dump($_SESSION);
print "</pre>";
print "<p>Dumping cookie data</p>";
print "<pre>";
var_dump($_COOKIE);
print "</pre>";
$_COOKIE="";
unset($_SESSION['order_id']);
$_COOKIE['order_id']="";
?>

