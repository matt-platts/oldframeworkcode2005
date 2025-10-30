<?php

// functions which may be called by an existing system action should be entered here..

function ftp_banner_images(){
	print "on ftp banner images()";
	print file_get_contents("http://www.gonzomultimedia.com/images/frontpage/products/ftp_banner_images_from_gonzo.php");
	print file_get_contents("http://www.voiceprint.co.uk/images/frontpage/products/ftp_banner_images_from_gonzo.php");
}

function set_active_survey(){
	$table_row_id=$_POST['rowid_for_edit'];
	$active_fieldname="id_$table_row_id" . "_active_question";
	if ($_POST[$active_fieldname]){
		$active=1;
	} else {
		$active=0;
	}
		
	if ($active && $table_row_id){
		// reset all other table rows to 0
		$sql = "UPDATE survey_questions SET active_question = 0 WHERE id !=$table_row_id";
		$res=mysql_query($sql) or die(mysql_error());

	} else {
		//print "This is not the active question.";
	}
}
include_once(BASEPATH . "/system/custom/flight_logistics.php");


?>
