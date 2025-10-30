<?php

class loginController {

function process_login(){
	global $user;
	global $page;
	global $db;
	$email_address=$db->db_escape($_POST['email_address']);
	$password=$db->db_escape($_POST['password']);
	$result = $user->process_login($email_address,$password);
	$page->set_value("content",$result);
	return 1;
}



}

?>
