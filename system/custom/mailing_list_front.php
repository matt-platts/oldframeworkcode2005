<?php

if ($_REQUEST['action']=="mailing_list_unsubscribe"){
	$content=mailing_list_unsubscribe_front($_REQUEST['email']);
}
if ($_REQUEST['action']=="mailing_list_unsubscribe_confirm"){
	$content=mailing_list_unsubscribe_confirm($_REQUEST['email']);
}
if ($_REQUEST['action']=="mailing_list_confirm_receipt"){
	$return_data=mailing_list_confirm_receipt($_REQUEST['confirmation_id']);
	$content=$return_data['content'];
	$title=$return_data['title'];
}
if ($_REQUEST['action']=="mailing_list_resend_confirmation"){
	$content=mailing_list_resend_confirmation($_POST['reverify_account_email']);
	$title="Mailing list: Resend verification email";
}
if ($_REQUEST['action']=="mailing_list_resend_verification"){
	$verification_email=mysql_real_escape_string($_POST['reverify_account_email']);
	$return_data=mailing_list_resend_verification($verification_email);
	$content="Thank you. We have sent a new verification email to $verification_email.";
	$title="Verification email resent";
}

function mailing_list_resend_confirmation(){
	$content="<p>Please enter your email address and we will resend a confirmation message to you. This will contain a link to click on in order to verify that you are the owner of this email address.</p>
	<form action=\"site.php?action=mailing_list_resend_verification\" method=\"post\">
	<b>Enter email address:</b> <input type=\"text\" name=\"reverify_account_email\"><input type=\"hidden\" name=\"list_id\" value=\"\" /> <input type=\"submit\" value=\"continue\"></form>";
	return $content;		
}


function mailing_list_unsubscribe_confirm($address){
	$title="Unsubscribe from mailing list";
	if (!$address){
		$content="No email address supplied to unsubscribe";
		return $content;
	}

	$update_query="DELETE FROM mailing_list WHERE email_address=\"".$address."\"";
	$run_query=mysql_query($update_query) or format_error("Cannot remove from mailing list",1);
	
	$content = "The email address $address has been successfully removed from the mailing list.";
	return $content;
}

function mailing_list_unsubscribe_front($address){
	
	$title="Unsubscribe from mailing list";
	if (!$address){
		$content="No email address supplied to unsubscribe";
		return $content;
	}
	
	$sql="SELECT * from mailing_list WHERE email_address=\"$address\"";
	$res=mysql_query($sql) or die(mysql_error());
	$count=mysql_num_rows($res);
	if (!$count){
		$content="This email address is not in the mailing list.";
		return $content;
	}
	$content="<p>Are you sure you want to unsubscribe $address from the mailing list?</p>";
	$content .= "<p>To complete your unsubscription please click <a href=\"site.php?action=mailing_list_unsubscribe_confirm&email=$address\">here</a>";
	$content .= "<p>To go to our home page please click <a href=\"index.html\">here</a>";
	return $content;
}

function mailing_list_resend_verification($to){
	global $db;

	// error conditions
	if (!$to){
		print format_error("No email address was submitted - please go back and try again",1);
		exit;
	}
	if (!preg_match("/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i",$to)){
		print format_error("You have not entered a valid email address ($to). Please go back and try again",1);
		exit;
	}

	$mail_uid=md5(uniqid(mt_rand(), true));

	// check exists
	$sql="SELECT id FROM mailing_list WHERE email_address = \"" . mysql_real_escape_string($to) . "\"";
	//print $sql;
	$res=$db->query($sql);
	$results=mysql_num_rows($res);
	if ($results != 1){
		print "error: $results entries found."; exit;
	}

	$sql="UPDATE mailing_list SET confirmation_key_string = \"$mail_uid\", date_added=NOW() WHERE email_address = \"" . mysql_real_escape_string($to) . "\"";
	$res=$db->query($sql) or die ("ERROR 4");
	
	$subject="Please confirm your mailing list subscription";
	$find_email_template=$db->field_from_record_from_id("email_configuration",3,"email_template");
	$message=$db->field_from_record_from_id("templates",$find_email_template,"template");
	$conf_link=HTTP_PATH."/site.php?action=mailing_list_confirm_receipt&confirmation_id=$mail_uid";
	$message = str_replace("{=email_confirmation_link}","<a href=\"$conf_link\">$conf_link</a>\n\n",$message);
	$list_email_address=$db->field_from_record_from_id("mailing_lists","1","list_email_address"); // list id hard coded still...
	$headers="From: $list_email_address\r\n";
	$headers .= "Content-type:text/html\r\n";
	mail($to,$subject,$message,$headers);
}

function mailing_list_confirm($last_insert_id){
	global $db;

	$get_mail_address="SELECT * from mailing_list where id = $last_insert_id";
	$res=$db->query($get_mail_address) or die("oops");
	while ($h=$db->fetch_array($res)){
		//var_dump($h);
		$to = $h['email_address'];
	}

	// error conditions
	if (!$to){
		print format_error("No email address was submitted - please go back and try again",1);
		exit;
	}
	if (!preg_match("/\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i",$to)){
		print format_error("You have not entered a valid email address ($to). Please go back and try again",1);
		exit;
	}

	$mail_uid=md5(uniqid(mt_rand(), true));
	$sql="UPDATE mailing_list SET confirmation_key_string = \"$mail_uid\", date_added=NOW() WHERE id = $last_insert_id";
	$res=$db->query($sql);
	
	$subject="Please confirm your mailing list subscription";
	$find_email_template=$db->field_from_record_from_id("email_configuration",3,"email_template");
	$message=$db->field_from_record_from_id("templates",$find_email_template,"template");
	$conf_link=HTTP_PATH."/site.php?action=mailing_list_confirm_receipt&confirmation_id=$mail_uid";
	$message = str_replace("{=email_confirmation_link}","<a href=\"$conf_link\">$conf_link</a>\n\n",$message);
	$list_email_address=$db->field_from_record_from_id("mailing_lists","1","list_email_address"); // list id hard coded still...
	$headers="From: $list_email_address\r\n";
	$headers .= "Content-type:text/html\r\n";
	mail($to,$subject,$message,$headers);
}

function mailing_list_confirm_receipt($confirmation_id){
	global $db;
	$sql="SELECT * from mailing_list WHERE confirmation_key_string = \"$confirmation_id\"";
	$res=$db->query($sql);
	if (mysql_num_rows($res)==1){
		$sql2="UPDATE mailing_list set confirmed=1, date_confirmed=NOW(), confirmation_key_string = NULL where confirmation_key_string = \"$confirmation_id\"";
		$res=mysql_query($sql2) or format_error("Cannot update confirmation",1);
		$return_data['title']="Thank You - Subscription Complete";
		$return_data['content'] = "<p>Thank you - your email address has been confirmed and your subscription is now complete.</p>";
	} else {
		$return_data['title']="Mailing List Subscription Error";
		$return_data['content'] = "<p>Sorry - we don't recognise this link, which does not appear to come from a valid sign up.</p><p>We recommend that you sign up again if you wish to join this mailing list.</p>";
	}

	return $return_data;
}


?>
