<?php
session_start();
if ($_GET['menu']=="dev"){
	$_SESSION['devmenutop']=(str_replace("px","",$_GET['menutop'])-2)."px";
	$_SESSION['devmenuleft']=(str_replace("px","",$_GET['menuleft'])-2)."px";
} else if ($_GET['menu']=="quickedit"){
	$_SESSION['quickeditmenutop']=$_GET['menutop'];
	$_SESSION['quickeditmenuleft']=$_GET['menuleft'];
}
print "1";
?>
