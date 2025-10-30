<?php
if ($_GET['action']=="moodgets_data_store"){
	include_once("system/plugins/moodgets/moodgets.php");
	$mood=new moodgets();
	$ds=$mood->moodgets_data_store();
}
if ($_GET['action']=="moodgets_save"){
	include_once("system/plugins/moodgets/moodgets.php");
	$mood=new moodgets();
	$save_data=$mood->moodgets_save_data();
	exit;
}
?>
