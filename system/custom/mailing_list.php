<?php

if ($_REQUEST['action']=="send_mail_intro"){ send_mail_intro($_REQUEST['mail_id']); }
if ($_REQUEST['action']=="mail_send_confirm") {mail_send_confirm($_REQUEST['mail_id'],$_REQUEST['mailinglist']); }
if ($_REQUEST['action']=="mailinglist_send_mail_now") { send_emails($_POST['mail_id'],$_POST['list_id']);}

function mail_send_confirm($mail_id,$mailinglist_id){
        print "<p class=\"admin_header\">Confirm Mail Send</p>";
        print "<p>You are about to send the following email:</p>";
	print "<form name=\"mailinglistsend\" action=\"administrator.php?action=mailinglist_send_mail_now\" method=\"post\">\n";
	print "<input type=\"hidden\" name=\"mail_id\" value=\"$mail_id\" />\n";
	print "<input type=\"hidden\" name=\"list_id\" value=\"$mailinglist_id\" />\n";
	$maildetailssql="SELECT * from emails where id=$mail_id";
	$res=mysql_query($maildetailssql);
	$mail_details=mysql_fetch_array($res,MYSQL_ASSOC);
	if (!$mail_details['subject']){
		$mail_details['subject']="(No Subject - are you sure you want to send email without a subject?)";
	}
        print "<p><b>Subject:</b> " . $mail_details['subject'];
        print "<p><b>To the following mailing list:</b> ";
        $list_sql="SELECT * from mailing_lists where id = $mailinglist_id";
        $res=mysql_query($list_sql) or format_error("SQL ERROR",1);
		while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
			print $h['list_name'];
		}

	$count_query="SELECT count(*) as total from mailing_list where list_id = $mailinglist_id AND confirmed = 1";
	$res=mysql_query($count_query);
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$number_of_subscribers=$h['total'];
	}
	print " ($number_of_subscribers confirmed subscribers)</p>";
	print "<p><input type=\"submit\" value=\"Send this mail now\"></p>\n";
}



function send_emails($mail_id,$list_id){ // sends emails to the list in $list_id. $mail_id is the id of the email to be sent

	# 1 Get the message in the $message variable
	$message="SELECT subject,content from emails where id = $mail_id";
	$res=mysql_query($message);
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$message=$h['content'];
		$subject=$h['subject'];
	}
	
	#2 Load the recipient list
	$recipients=array();
	$recipient_sql="SELECT * from mailing_list WHERE list_id = $list_id AND confirmed=1";
	$res=mysql_query($recipient_sql) or format_error("Cant load list",1);
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		array_push($recipients,$h['email_address']);
	}

	# 3 Load master mailing list variables
	$mlist_vars_sql="SELECT * FROM mailing_lists WHERE id = $list_id";
	$res=mysql_query($mlist_vars_sql);
	$mlist_vars=mysql_fetch_array($res,MYSQL_ASSOC);
	
	$db_headers = $mlist_vars['headers'];
	$db_headers = preg_replace("/\n+/","\n",$db_headers);
	$db_headers = preg_replace("/\n/","\r\n",$db_headers);

	$headers = "From: ". $mlist_vars['list_email_address'] . "\r\n";
	$headers .= "Content-type: ". $mlist_vars['content_type'] . "\r\n";
	$headers .= $db_headers;

	// update images - this needs to be done dynamically
	$message=preg_replace("/\"images\/email_images\//","\"http://www.sense4csp.org/images/email_images/",$message);
	$message=preg_replace("/\"site.php/","\"http://www.sense4csp.org/site.php",$message);

	
	print "<p>Sending $message with headers";
	print $headers;
	print "</p><hr>";
	foreach ($recipients as $mail_to){
		print "&bull; mailing $subject to $mail_to<br />";
		$message_to_send=preg_replace("/{=email_address}/",$mail_to,$message);
		mail($mail_to,$subject,$message_to_send,$headers) or die ("Cant send mail");
	}
}

function send_mail_intro($mail_id){
	print "<p class=\"admin_header\">Send Email</p>";
	$maildetailssql="SELECT * from emails where id=$mail_id";
	$res=mysql_query($maildetailssql);
	$mail_details=mysql_fetch_array($res,MYSQL_ASSOC);
	print "<p>You are about to send the email '".$mail_details['name']."' with the subject of: '".$mail_details['subject']."'</p><p>";
	print "<p>Please select the mailing list to send this mail to:</p>";
	print "<form name=\"mail_send_intro_form\" method=\"post\" action=\"administrator.php?action=mail_send_confirm\">";
	print "<select name=\"mailinglist\">";
	$list_lists="SELECT * from mailing_lists";
	$res=mysql_query($list_lists) or format_error("Cant list mailing lists",1);
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		print "<option value=\"".$h['id']."\">".$h['list_name']."</option>\n";	
	}
	print "</select>\n";
	print "<input type=\"hidden\" name=\"mail_id\" value=\"$mail_id\">\n";
	print "<input type=\"submit\" value=\"Continue\" />\n";
	print "</form>";
}

?>
