<?php

/* 
 * CLASS: user
 * Meta: contains all the user login functionality, password change and reset, login cookies etc.
*/
class user {

        function __construct(){
                $this->set_value("id",$this->user_data_from_cookie("ID"));
		if ($this->value("id")){
			$this->set_value("type",$this->user_data_from_cookie("Type"));
			$this->set_value("full_name",$this->user_data_from_cookie("Full Name"));
			$this->set_value("first_name",$this->user_data_from_cookie("First Name"));
			$this->set_value("hierarchial_order",$this->user_data_from_cookie("Hierarchial Order"));
			$this->set_value("admin_access",$this->user_data_from_cookie("Admin Access"));
			$this->set_value("email_address",$this->user_data_from_cookie("Email"));
		} else {
			$this->set_value("type",null);
			$this->set_value("full_name",null);
			$this->set_value("hierarchial_order",null);
		}
        }

        function value($of){
                return $this->$of;
        }

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }


/**
 * Function: process_login 
 * Meta: process a user login
 * @param string $entered_username 
 * @param string $entered_password
 * @param string $direct_to - page to show after a successful login
 * @return mixed - may use a redirect header or return html 
 */
public function process_login ($entered_username, $entered_password, $direct_to=''){
	global $CONFIG;
	global $db;
	$login_lifetime_in_minutes=$CONFIG['login_lifetime_in_minutes'];
	$login_main_user_field=$CONFIG['login_main_user_field'];
	$login_lifetime_in_seconds=$login_lifetime_in_minutes*60;
	$entered_username=strtolower($entered_username);
	// no html tags or quotes in usernames or passwords
	if (strlen(strpos($entered_username,"'")) || strlen(strpos($entered_username,">")) || strlen(strpos($entered_password,"'")) || strlen(strpos($entered_password,">")) || strlen(strpos($entered_username,"\""))){print "FATAL ERROR E1851E";exit;}
	if (!$entered_username || !$entered_password){
		print "User name or password is missing. Please go <a href=\"Javascript:history.go(-1)\">back</a> and re-enter your details. ";
		global $page;
		if ($page->value("mui")){ // ajax call, just quit
				 exit; 
		}
	}
	$clear_password=$entered_password;
	// get the password out
	include_once(LIBPATH . "/library/core/bcrypt.php");
	$password_sql=sprintf("SELECT user.password FROM user WHERE $login_main_user_field = '%s'",$db->db_escape($entered_username));
	$password_result = $db->query($password_sql) or format_error("Login failed (Code 8YY717)",1);
	if ($db->num_rows($password_result) != 1) {
		format_error("Login failed (Code 8YY716)",1);
	}
	$row=$db->fetch_array($password_result);
	$retrieved_password=$row['password'];
	$bc = new Bcrypt;
	if (!$bc->verify($entered_password,$retrieved_password)){
		return $this->login_failed();

	}
	$sql_query=sprintf("SELECT user.id AS id,type,user_types.admin_access as admin_access FROM user INNER JOIN user_types ON user.type = user_types.user_type WHERE $login_main_user_field = '%s' AND (password = '%s' OR password_clear = '%s') AND status='active' AND (delete_user IS NULL or delete_user=0)",$db->db_escape($entered_username),$db->db_escape($retrieved_password),$db->db_escape($clear_password));

	$result=$db->query($sql_query) or format_error("Login query failed (Code 8YY718)",1);
	while ($row=$db->fetch_array($result)){
		if ($row['admin_access']){
			$check_admin_sql="SELECT id,user_id FROM administrators WHERE user_id=".$row['id'];
			$admin_res=$db->query($check_admin_sql);
			$admin_h=$db->fetch_array($admin_res);
			if (!$admin_h['user_id']){
				$deny_access=1;
				format_error("Access denied (Code 8YY719)",1); 
				exit;
			} else {
				$log_admin_login_sql="UPDATE administrators SET last_login_time=NOW() WHERE user_id = " . $admin_h['user_id'];
				$log_admin_login=$db->query($log_admin_login_sql) or die ("Access denied (Code 8YY720)");
			}
			$is_administrator=1;
		}
		//$set_login = setcookie("login", "ID".$row['id'], time()+$login_lifetime_in_seconds);	
		$_SESSION['user_id']=$row['id'];
		//$set_login = setcookie("login", "ID".session_id(), time()+$login_lifetime_in_seconds,"/");	
		$set_login = setcookie("login", "ID".session_id(),0,"/");	
		// regenerate the session id once logged in
		if (!isset($_SESSION['initiated'])){
		    session_regenerate_id();
		    $set_login = setcookie("login", "ID".session_id(),0,"/");	
		    $_SESSION['initiated'] = true;
		}
		if ($_POST['xhr']==1){
			print "Login Success"; exit;
		}
		if (!$direct_to){ 
			if ($is_administrator){ 
				$direct_to="/admin/";
			} else {
					$direct_to="/";
			}
		}
		header("Location: $direct_to");	
	}
	if ($db->num_rows($result)==0){
		return $this->login_failed();
	}
}

/* 
 * Function: login_failed
 * Meta: If a login fails, the output from this function is returned to the user
*/
private function login_failed(){
	global $db;
	global $page;
	print "oops!"; 
	if (!stristr($_SERVER['PHP_SELF'],"dministrator")){
		$response_sql="SELECT id,value FROM user_config WHERE variable=\"login_details_not_found\"";
		$rsv=$db->query($response_sql);
		$rsh=$db->fetch_array($rsv);
		$details_not_found_content_id=$rsh['value'];
		if ($details_not_found_content_id){
			global $page;	
			$return_html .= $page->content_from_id($details_not_found_content_id);
		} else {
			$return_html .= "<p>Sorry - this email / password is not a valid login.</p><p><a href='Javascript:history.go(-1)'>Click here to try again.</a></p>";	
			$return_html .= "<p><a href='site.php?action=reset_password'>Forgotten your password? Please click here</a>";
		}
		return $return_html; 
	} else {
		header("Location: /admin/?error=1");
		exit;
	}

}

/*
 * Function: refresh_login_cookie
 * Meta: Update each time the user makes a request
*/
public function refresh_login_cookie() {
	global $login_lifetime_in_minutes; // ugh, global vars!
	$login_lifetime_in_seconds=$login_lifetime_in_minutes*60;
	if (!preg_match("/ID\w+$/",$_COOKIE['login'])){ format_error("login cookie error",1); exit;}
	//$refresh_login = setcookie("login", $_COOKIE['login'], time()+$login_lifetime_in_seconds); 
}

/*
 * Function: process_log_out
*/
public function process_log_out($direct_to) {
	global $db;
        setcookie ("login", "",time()-8400,"/");
        setcookie ("login", "",time()-8400,"start/");
	session_unset();
	session_destroy();
	$_SESSION = array();
        if (!$direct_to){
                return;
        } else {
                $header_string = "Location: " . $db->db_escape($direct_to);
                header($header_string);
        }
}

/* 
 * Function: user_data_from_cookie
 * Meta: one of the original Perl module functions
*/
public function user_data_from_cookie($what_to_return){
	global $db;
	if (!$_COOKIE['login']){ return;}
	$userid = preg_replace("/ID/","",$_COOKIE['login']);
	$userid=(int)$userid; 
	$userid=$_SESSION['user_id'];
	if ($what_to_return=="Id" || $what_to_return=="id" || $what_to_return=="ID") { return $userid;} 
	if (!$userid){ 
		print format_error ("No user id found in an active session",0); 
		if (!stristr($_SERVER['PHP_SELF'],"site.php")){
			$this->kill_session_and_message("Login Error:");
		}
	}
	$sql="SELECT first_name,second_name,type,user.id AS id,email_address,hierarchial_order,admin_access from user INNER JOIN user_types ON user.type=user_types.user_type WHERE user.id = " . $userid;
	$result=$db->query($sql) or $this->kill_session_and_message("<p class=\"dbf_para_info\">You have possibly been logged out of the system after a prolonged period of inactivity. <br /><a href=\"".$_SERVER['PHP_SELF']."?action=dbf_restart\">Please click here to return to the home page and try again.</a></p>");
	while ($row=$db->fetch_array($result)){
		$name = $row['first_name'];
		$first_name = $name;
		$name .= " " . $row['second_name'];
		$usertype = $row['type'];
		$userid = $row['id'];
		$hierarchial_order = $row['hierarchial_order'];
		$admin_access= $row['admin_access'];
		$email=$row['email_address'];
	}
	if ($what_to_return=="Full Name") return $name; 
	if ($what_to_return=="First Name") return $first_name; 
	if ($what_to_return=="Type") return $usertype; 
	if ($what_to_return=="Hierarchial Order") return $hierarchial_order; 
	if ($what_to_return=="Admin Access") return $admin_access; 
	if ($what_to_return=="Email") return $email; 
}

/* 
 * Function: process_registration
*/
public function process_registration($last_inserted_id){
	$entered_username=$_POST['new_email_address'];
	$entered_password=$_POST['new_password'];
	global $db;

	// log_user_in_straight_away
	global $CONFIG;
        $login_lifetime_in_minutes=$CONFIG['login_lifetime_in_minutes'];
        $login_main_user_field=$CONFIG['login_main_user_field'];
        $login_lifetime_in_seconds=$login_lifetime_in_minutes*60;
	if (!$entered_username || !$entered_password){print "User name or password is missing. Please go <a href=\"Javascript:history.go(-1)\">back</a> and re-enter your details. "; exit;}
        $clear_password=$entered_password;
	include_once(LIBPATH . "/library/core/bcrypt.php");
	$bc = new Bcrypt();
	$entered_password = $bc->hash($entered_password); 

        $sql_query=sprintf("SELECT id from user where $login_main_user_field = '%s' AND (password = '%s' OR password_clear = '%s') AND status='active' AND (delete_user IS NULL or delete_user=0)",$db->db_escape($entered_username),$db->db_escape($entered_password),$db->db_escape($clear_password));

        $result=$db->query($sql_query) or format_error("Error 515159I: query failed",1);
	$nr = $db->num_rows($result);
        while ($row=$db->fetch_array($result)){
                //$set_login = setcookie("login", "ID".$row['id'], time()+$login_lifetime_in_seconds);
                $_SESSION['user_id']=$row['id'];
                $set_login = setcookie("login", "ID".session_id(), time()+$login_lifetime_in_seconds);
        }
        if ($nr==0){
                $return_html .= "<p>Email / password pair not found - please try again.</p>";
                $return_html .= "<p><a href='site.php?action=mail_password_front'>Forgotten your password? Please click here</a>";
                return $return_html;
	} else {
		$get_name_vars="SELECT first_name,second_name from user where id = $last_inserted_id";
		$res=$db->query($get_name_vars);
		while ($row=$db->query($res)){
			$first_name=$row['first_name'];
			$second_name=$row['second_name'];
		}
		$this->mail_registration_details($entered_username,$first_name,$second_name); 
	}
	return;
}

/* 
 * Function: mail_registration_details
 * Meta: send a newly registered user an email
*/
public function mail_registration_details($email_address,$first_name,$second_name){
	// note that what should be going on below is all fields from the registration form are passed through...
	$mailvars['to']=$email_address;
	$mailvars['email_address']=$email_address;
	$mailvars['first_name']=$first_name;
	$mailvars['second_name']=$second_name;
	send_system_mail("","Registration Confirmation",$mailvars);
	return;
}

/*
 * Function: reset_password_generate
 * Meta: generates a password reset request and sends an email to the user with a link to click 
*/
public function reset_password_generate($email){
	global $db;
	$email=$db->db_escape($email);
	$usql="SELECT id, first_name,second_name FROM user WHERE email_address = \"$email\"";
	$urv=$db->query($usql);
	print "NR: " . $db->num_rows($urv);
	if ($db->num_rows($urv)==0){
		$return_html="Sorry - the email address $email was not found.";
	} else {
		// there should be an HTML templates for this in the templates table? No text should be in PHP code. Matt.
		$sitename = $CONFIG['site_name'];
		$h=$db->fetch_array($urv);
		$userid=$h['id'];
		$name=$h['first_name'] . " " . $h['second_name'];
		$uuid=uniqid() . "-" . uniqid();	
		$isql="INSERT INTO password_reset_keys (id,user,password_key) values(\"\",$userid,\"$uuid\")";
		$irv=$db->query($isql);
		$message="Dear $name,\nThank you for requesting a link to reset your password for $sitename. Please click on the link below to continue.\n\n";
		$message .= HTTP_PATH . "/site.php?action=reset_password_confirm&uuid=$uuid\n\n";
		$message .= "Thank You.";
		$to=$email;
		$subject="Password Reset Link";
		$headers="From: no-reply@".HTTP_PATH."\r\n";
		mail($to,$subject,$message,$headers);
		$return_html="<p>Thank you - we have sent an email to this address. You will need to click on the link in this email to reset your password.</p>";
		$return_html .= "<p>If you do not recieve this email within a few minutes, please check your junk mail folders or junk mail settings and try again.<p>";
	}

	return $return_html;
}

/*
 * Function: reset_password_confirm
 * Meta: When a user has clicked on a new password link, this is where they generate it
*/
public function reset_password_confirm($uuid){

	global $db;
	$uuid=$db->db_escape($uuid);
	$sql="SELECT * from password_reset_keys WHERE password_key = \"$uuid\"";
	$rv=$db->query($sql);
	$h=$db->fetch_array($rv);
	if ($h['id']){	
		$return_html="<p>Thank You - please continue to choose a new password below:</p>";
		$return_html .= "<form action=\"/site.php?action=reset_password_complete\" method=\"post\">";
		$return_html .= "<table><tr><td>Your new password: </td><td><input type=\"password\" name=\"password\" /></td></tr>";
		$return_html .= "<tr><td>Please confirm your new password: </td><td><input type=\"password\" name=\"confirm_password\" /></td></tr>";
		$return_html .= "<input type=\"hidden\" name=\"uuid\" value=\"$uuid\" />";
		$return_html .= "</table><p><input type=\"submit\" value=\"Reset My Password\"></p>";
		$return_html .= "</form>";
	} else {
		$return_html = "<p>Sorry - this link either does not exist, has been used, or has expired.</p>";
		$return_html .= "<p><a href=\"reset-password.html\">Return to start</a></p>";
	}
	return $return_html;
}

/*
 * Function: reset_password_compelte
 */
public function reset_password_complete(){

	global $db;
	if (!$_POST['password'] || !$_POST['confirm_password']){
		$msg="<p>Please ensure you have entered a password into <u>both</u> fields on the previous form. Please go back and try again.</p>";
	}
	if ($_POST['password'] != $_POST['confirm_password']){
		$msg="New passwords do not match. Please try again.";
	}
	if ($_POST['password'] && $_POST['confirm_password']){
		// do password encoding
		$uuid=$_POST['uuid'];
		$get_uid_sql="SELECT * FROM password_reset_keys WHERE password_key = \"$uuid\"";
		$rv=$db->query($get_uid_sql);
		$h=$db->fetch_array($rv);
		$uid=$h['user'];
		$new_password_to_use=$db->db_escape($_POST['password']);
		include_once(LIBPATH . "/library/core/bcrypt.php");
		$bc = new Bcrypt;
		$new_password_to_use = $bc->hash($new_password_to_use);
		// MATT: This needs to be checked (return values) and done in a transaction
		$sql="UPDATE user SET password = \"$new_password_to_use\" WHERE id = $uid";
		$rv=$db->query($sql);
		$sql="DELETE FROM password_reset_keys WHERE password_key = \"$uuid\"";
		$rv=$db->query($sql);
		$msg="<p>Thank you - your password has been reset successfully. You will need to use this password to log in now and in the future.</p>";
		$msg .= "<p><a href=\"my-account.html\">Continue to log in</a></p>";
	}

	if (!$msg){
		$msg="An error has occurred!";
	}
	return $msg;
}

public function mail_password($password_type){
        if ($password_type=="clear"){ $password_field="password_clear"; } else {$password_field="password";}
        if (!$_POST['email_address']){
                return "<b>Error</b>: The required information was not entered. Please go <a href=\"javascript:history.go(-1)\">Back</a> and try again.";
        }
        global $db;
        error_reporting(E_ALL);
        $sql_query=sprintf("SELECT $password_field from user where email_address = '%s'",$db->db_escape($_POST['email_address']));
        $result=$db->query($sql_query);
        if ($db->num_rows($result)==0){
                return "Email address not found<p>";
                exit;
}
        while ($row=$db->fetch_array($result)){
                $password = $row[$password_field];
        }

        global $current_site;
        $http_path=$current_site['http_path'];

        $get_message_sql="SELECT templates.template FROM templates INNER JOIN user_config ON user_config.value=templates.id WHERE ";
        $get_message_sql .= ' user_config.variable="user_password_resend_mail_template"';
        $get_message_rv=$db->query($get_message_sql) or format_error("Cannot run sql $get_message_sql",1);
        while ($get_message_h=$db->fetch_array($get_message_rv)){
                $message=$get_message_h['template'];
        }

        $to=$_POST['email_address'];
	// subject
        $subject_sql = "SELECT value FROM user_config WHERE variable=\"user_password_resend_mail_subject\""; 
	$subject_rv=$db->query($subject_sql);
	while ($subject_h=$db->fetch_array($subject_rv)){
		$subject=$subject_h['value'];
	}

	// from
        $from_sql = "SELECT value FROM user_config WHERE variable=\"user_password_resend_mail_from_address\""; 
	$from_rv=$db->query($from_sql);
	while ($from_h=$db->fetch_array($from_rv)){
		$mail_from=$from_h['value'];
	}
		
        $from_sql = "SELECT value FROM user_config WHERE variable=\"user_password_resend_mail_from_name\""; 
	$from_rv=$db->query($from_sql);
	while ($from_h=$db->fetch_array($from_rv)){
		$mail_from_name=$from_h['value'];
	}

	$mail_sent_from="\"$mail_from_name\" <" . $mail_from . ">";
        $headers="Content-type:text/html\r\n";
	$headers .= "From: ".$mail_sent_from."\r\n";
	$headers .= "Reply-To: ".$mail_from."\r\n\r\n";

        $message = str_replace("{=password}", $password, $message);
        mail($to,$subject,$message,$headers) or die("Cant send mail");
        open_col2();
        return "Thank you - Your password has been mailed to you at $to. <p>Please use the login form on the right to login once you have received this.";
        close_col();
}

/*
 * Function: generate_random_string
 *
*/
public function generate_random_string($length) {
    $characters = "123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVQXYZ";
    $string = "";    
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }
    return $string;
}

/* 
 * Function: generate_password
 * Meta: DEPRECATED All password change functionality now done with tokens, though the admin change user password still uses this
*/
public function generate_password(){
	$new_pw=$this->generate_random_string(8);
	return $new_pw;
}

/*
 * Function: change_password
*/
public function change_password($password1,$password2){
	global $db;
	global $user;
	if (!$password1 || !$password2){
		$message = "<p>Please ensure that you have both entered and confirmed your new password</p><p>Please go <a href=\"Javascript:history.go(-1)\">back</a> and resubmit this form - thanks.</p>;";
	} else if ($password1 != $password2){
		$message = "<p>The two passwords that you entered were not the same. Please go <a href=\"Javascript:history.go(-1)\">back</a> and resubmit this form - thanks.</p>";
	} else if ($password1 == $password2){
		include_once(LIBPATH . "/library/core/bcrypt.php");
		$bc = new Bcrypt();
		$entered_password = $bc->hash($entered_password); 
		$new_password=$bc->hash($password1);
		$sql = "UPDATE user set password = \"$new_password\", password_clear=\"\" WHERE id = " . $user->value('id');
		$res=$db->query($sql) or format_error("Error in password update function",1);
		$message = "<p><b>Thank You - your password has been updated successfully.</b></p><p>You will need to use this next time you log in.</p><p><a href=\"account.html\">Return to My account</a>";
	}	
	return $message;
}

/* 
 * Function: create_new_password_for_user 
 * Meta: Main admin function for creating a password for a user by admininstrator interaction with the back end
*/
public function create_new_password_for_user($user_id,$new_password,$new_password_confirmed,$generate_random,$auto_mail){
	global $db;
	$new_password=trim($new_password);
	$new_password_confirmed=trim($new_password_confirmed);
	print "<p class=\"admin_header\">Create New Passsword For User Id: $user_id</p>";
	if (!$user_id){
		print format_error("No user id supplied",1);
	}
	if (!$new_password && !$generate_random){
		print "<p>Here you can generate a new password for this user. This can be either a password that you enter in, or one can be randomly generated.</p>";
		print "<form action=\"" . $_SERVER['PHP_SELF'] . "?action=create_new_password_for_user\" method=\"post\" name=\"new_password_form\">";
		print "<input type=\"hidden\" name=\"user_id\" value=\"$user_id\" />";
		print "<b>1.</b> Either enter selected new password: <input type=\"password\" name=\"new_password\" /> and confirm it here: <input type=\"password\" name=\"new_password_confirmed\" /> <br /> &nbsp; or check here to generate a new random password: <input type=\"checkbox\" name=\"create_random\"><br />";
		print "<b>2.</b> Automatically email this user with their new password? <input type=\"checkbox\" name=\"auto_mail_password_to_user\" checked><br />";
		print "<b>3.</b> <input type=\"submit\" value=\"Click here to create the new password for this user\">";
	} else if ($new_password && $new_password_confirmed && ($new_password != $new_password_confirmed)){
		print format_error("New passwords entered are not the same - please go back and rectify this - thanks.",0);
	} else {
		// from here do new password
		if ($new_password && $new_password_confirmed && ($new_password == $new_password_confirmed)) {
			$new_password_to_use=$new_password;
		} else if ($generate_random){
			$new_password_to_use=$this->generate_password();
		}

		$clear_password=$new_password_to_use;
		include_once(LIBPATH . "/library/core/bcrypt.php");
		$bc = new Bcrypt();
		$new_password_to_use = $bc->hash($new_password_to_use);

		$sql="UPDATE user SET password = \"$new_password_to_use\", password_clear=\"\" WHERE id = $user_id";
		$res=$db->query($sql) or format_error("Cannot run sql to update password.",1);

		if ($auto_mail){
			$to="SELECT * from user where id = $user_id";
			$res=$db->query($to) or format_error("Error with automail 1",1);
			$mailvars=$db->fetch_array($res);
			$res=$db->query($to) or format_error("Error with automail 2",1);
			while ($h=$db->fetch_array($res)){
				$to=$h['email_address'];
			}

			$mailvars['password']=$clear_password;
			send_system_mail("","New Password From Admin",$mailvars);
			print "<p>&bull; Sent mail to $to.</p>";
		}
		print "<p>&bull; The password for the user (id:$user_id) has been updated successfully.</p>";
	}
}

/* 
 * Function :admin_change_my_password
 * Meta: change your own password from admin account
*/
public function admin_change_my_password($user_id,$new_password,$new_password_confirmed,$generate_random,$auto_mail){
	global $db;
	if (!$user_id){ print format_error("You must be logged in to do this"); exit;}
	print "its global";
	$new_password=trim($new_password);
	$new_password_confirmed=trim($new_password_confirmed);
	print "<p class=\"admin_header\">Change My Password (User Id: $user_id)</p>";
	print "and now";
exit;
	if (!$user_id){
		print format_error("No user id supplied",1);
	}
	if (!$new_password && !$generate_random){
		print "<p style=\"font-weight:bold\">Set up a new password for yourself:</p>";
		print "<p>It is important that admin passwords are not simple dictionary words, or even words followed by a few numbers.<br />Please use a mixture of lower and upper case letters, numbers, and some punctuation characters.</p>\n";
		print "<form action=\"" . $_SERVER['PHP_SELF'] . "?action=admin_change_my_password\" method=\"post\" name=\"new_password_form\">";
		print "<input type=\"hidden\" name=\"user_id\" value=\"$user_id\" />";
		print "<table><tr><td>Enter a new password: </td><td><input type=\"password\" name=\"new_password\" /></td></tr><tr><td align = \"right\">and confirm it here: </td><td><input type=\"password\" name=\"new_password_confirmed\" /> </td></tr></table>";
		print "<input type=\"submit\" value=\"Click here to create the new password\">";
	} else if ($new_password && $new_password_confirmed && ($new_password != $new_password_confirmed)){
		print format_error("New passwords entered are not the same - please go <a href=\"Javascript:history.go(-1)\">back</a> and rectify this - thanks.",0);
	} else {
		// from here do new password
		if ($new_password && $new_password_confirmed && ($new_password == $new_password_confirmed)) {
		$new_password_to_use=$new_password;
	} else if ($generate_random){
		$new_password_to_use=$this->generate_password();
	}			

	$clear_password=$new_password_to_use;
	include_once(LIBPATH . "/library/core/bcrypt.php");
	$bc = new Bcrypt();
	$new_password_to_use = $bc->hash($new_password_to_use);

	$sql="UPDATE user SET password = \"$new_password_to_use\", password_clear=\"\" WHERE id = $user_id";
	$res=$db->query($sql) or format_error("Cannot run sql to update password.",1);

	if ($auto_mail){
		$to="SELECT * from user where id = $user_id";
		$res=$db->query($to) or format_error("Error with automail 1",1);
		$mailvars=$db->fetch_array($res);
		$res=$db->query($to) or format_error("Error with automail 2",1);
		while ($h=$db->fetch_array($res)){
			$to=$h['email_address'];
		}

		$mailvars['password']=$clear_password;
		send_system_mail("","New Password From Admin",$mailvars);

		print "<p>&bull; Sent mail to $to.</p>";
	}
	print "<p class=\"dbf_para_success\">Your new password has now been set. Please use this to log into the admin in the future.</p>";
	}
}

/* 
 * Function: kill_session_and_message
 * Meta: To be used when a login expires
*/
public function kill_session_and_message($message){
	session_unset();
	session_destroy();
	setcookie ("login", "", time() - 3600);
	$message=$message."<br />";
	$refresh_login = setcookie("login", $_COOKIE['login'], time()-1000); 
	$message="";
	$full_message="<p class=\"dbf_para_info\">".$message."You have been logged out automatically after a prolonged period of inactivity. <br /><a href=\"".$_SERVER['PHP_SELF']."?action=dbf_restart\">Please click here to return to the home page and try again.</a></p>";
	format_error($full_message,1);
	exit;
}

// END USER CLASS
}
?>
