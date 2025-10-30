<?php

if ($_REQUEST['action']=="enquiry_response"){
	$content .= enquiry_response($_POST['ticket_id'], $_POST['enquiry']);
	$title="Enquiry Response";
}
if ($_REQUEST['action']=="close_ticket"){ 
	$content .= close_ticket($_REQUEST['ticket_id']);
	$title="Enquiries";
 }
if ($_REQUEST['action']=="create_enquiry"){ 
	$content .= create_new_ticket($_POST['title'], $_POST['enquiry']);
	$title = "Enquiries";
}

function enquiry_response($ticket_id,$response){
	global $user;
	$sql = "INSERT INTO ticket_details (ticket_id,user_id,ticket_text) values($ticket_id,".$user->value("id").",\"$response\")";
	$res=mysql_query($sql) or die(mysql_error());	
	$sql = "UPDATE tickets set last_updated_by=\"user\", status=\"In progress\" WHERE id=$ticket_id";
        $res=mysql_query($sql) or die(mysql_error());
	mail_enquiry($enquiry);
	return "<p>Your response has been posted back successfully.</p><p><a href=\"enquiries.html\" class=\"jc_button_120\">Enquiries Home</a></p>";
}

function close_ticket($ticket_id){
	global $user;
	$sql="UPDATE tickets set status = \"closed\" WHERE id = $ticket_id";
	$res=mysql_query($sql) or die(mysql_error());
	$response = "Enquiry closed by " . $user->value("full_name");
	$sql = "INSERT INTO ticket_details (ticket_id,user_id,ticket_text) values($ticket_id,".$user->value("id").",\"$response\")";
	$res=mysql_query($sql) or die(mysql_error());	
	return "<p>Thank you. Ticket $ticket_id is now closed.</p><p><a href=\"enquiries.html\" class=\"jc_button_120\">Enquiries Home</a></p>";

}

function create_new_ticket($title,$enquiry){
	global $user;
	$sql = "INSERT INTO tickets (id,user_id,title,date_created,date_updated,status,last_updated_by) VALUES(\"\",".$user->value("id").",\"$title\",NOW(),NOW(),\"new\",\"user\")";
	$res=mysql_query($sql) or die(mysql_error());
	$ticket_id = mysql_insert_id();

	$sql = "INSERT INTO ticket_details (ticket_id,user_id,ticket_text) values($ticket_id,".$user->value("id").",\"$enquiry\")";
	$res=mysql_query($sql) or die(mysql_error());
	mail_enquiry($enquiry);
	return "<p>Your new enquiry has been sent successfully.</p><p><a href=\"enquiries.html\" class=\"jc_button_120\">Enquiries Home</a></p>";
}

function mail_enquiry($enquiry){

	// send mail here, but who to?
	$mail_enquiry_to=field_from_record_from_id("setup_variables",6,"value");
	$subject="New enquiry response posted on the John Crane Marketing Portal";
	$mail_cart_from=field_from_record_from_id("setup_variables",4,"value");
	$mail_cart_from_email=field_from_record_from_id("setup_variables",5,"value");
	$mail_from="\"$mail_cart_from\" <$mail_cart_from_email>";
	$headers="From: $mail_from\n";
	$headers .= "Content-type:text/html\n\r\n\r";
	$mail_template=field_from_record_from_id("templates",47,"template");
	$mail_template=str_replace("{=enquiry}",$enquiry,$mail_template);
	mail($mail_enquiry_to,$subject,$mail_template,$headers);

}
