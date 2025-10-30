<?php

/* 
 * File : debugging.php
 * Meta: This file should only be included if you are debugging
*/

$debug=0; // global var turns on debugging

/*
* Function debug_info
 * Meta: Helpter function to print nicely formatted debug_backgrace info - used in development only
*/
function print_debug_info(){
	array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<br /><b>". basename( $a[\'file\'] ). "</b>:<font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
}

function console_log($msg){
	array_push($console_messages,$msg);	
}

function console_print(){
	foreach ($console_messages AS $msg){
		if (is_array($msg)){
			print_r($msg);
		} else {
			print "<p>$msg</p>";
		}
	}
}

function print_debug(){
	global $print_debug;
        global $user;
	if (!$user){ return; }
	$print_debug=1;
	if ($print_debug==1){
		if ($user->value("id")==1 || $user->value("id")==5){
			if (is_string($msg)){
				print "<p style=\"dbf_para_alert\">$msg</p>";
			} else {
				print "<pre>";
				var_dump($msg);
				print "</pre>";
			}
		}
        }
}

function display_all_cookies(){
	print "Cookies:";
	print $_COOKIE['login'];
	if (isset($_COOKIE['login'])) {
	    foreach ($_COOKIE['cookie'] as $name => $value) {
		echo "$name : $value <br />\n";
	    }
	} else {
		print "no cookies";
	}
}

function get_cookie_data(){
        $str="<p><b>Cookies:</b></p>";
	$str .="<p>Login: " .  $_COOKIE['login'] . "</p>";
        foreach ($_COOKIE['cookie'] as $name => $value) {
	    $str .= "$name : $value <br />\n";
        }
	if (isset($_COOKIE['login'])) {
	} else {
		$str .= "No cookies set or stored by the web browser.";
	}
	$str .= "<p><a href=\"".$_SERVER['PHP_SELF']."?action=reset_cookies\">Click here to clear all cookies set by this web site. You will need to log in again after this.</a></p>";
	return $str;
}

function reset_cookies(){
// unset cookies
	session_unset();
	session_destroy();
	if (isset($_COOKIE)) {
	    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
	    foreach($cookies as $cookie) {
		$parts = explode('=', $cookie);
		$name = trim($parts[0]);
		setcookie($name, '', time()-1000);
		setcookie($name, '', time()-1000, '/');
	    }
	}	
	$str = "Cookies cleared. Please try and log in again. Thanks.";
	return $str;
}

?>
