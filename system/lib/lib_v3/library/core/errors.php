<?php
/* 
 * File: errors;php
 * Meta: The global format_error function which can be called instead of die to print friendly and (hopefully) helpful error messages. 
 *       Automatically gives a formatted debug_bactrace response to master and superadmin users 
*/

DEFINE ("LOG_ERRORS",0); // user editable - 1 to log errors in error_logs db table, 0 to turn off

// Server error.log
set_error_handler("process_error");
function process_error($type,$msg,$file,$line,$context){
        switch ($type){
        case E_NOTICE:
        break;
        case E_WARNING:
        break;
        case 2048;
        break;
        default:
        //print "Error of type $type on line $line of $file: $msg <br />";
        break;
        }
}

/* 
 * function format_error
*/
function format_error($error_message,$fatal='',$helplink='',$hidden_message='',$error_code=''){
	global $user;

	$show_backtrace=1;
	
	if ($fatal){$fatal_text="A fatal";} else {$fatal_text="An";}
	$errortext= "<p style=\"background-image:url('".SYSIMGPATH."/icons/exclamation.png"."'); background-repeat:no-repeat;padding-left:20px\"><b>$fatal_text error has occurred.</b></p><p>The system generated the following message: <pre>$error_message</pre></p>";

	// show extra info to master and superadmin types
	if ($user){
		$user_type=$user->value("type");
		if ($hidden_message && ($user_type=="master" || $user_type=="superadmin")){
			$errortext .= "<p><b>Further System Info:</b> <pre>$hidden_message</pre></p>";
		}
	}

	// show help link if a $helplink has been submitted
	if ($helplink){ $errortext .= "<p style=\"background-image:url('".SYSIMGPATH."/icons/help.png"."'); background-repeat:no-repeat; padding-left:20px\"><a href=\"administrator.php?action=helptext&amp;helpid=$helplink\">Help is available about this issue.</a></p>"; }
	
	
	// print debug backtrace if applicable
	$userid = null;
	if ($user || $show_backtrace){
		$user_type=$user->value("type");
		if ($user_type=="master" || $user_type=="superadmin" || !$user_type){
			$userid=$user->value("id");
			ob_start();
			array_walk( debug_backtrace(), create_function( '$a,$b', 'print "<br /><b>". basename( $a[\'file\'] ). "</b>:<font color=\"red\">{$a[\'line\']}</font> &nbsp; <font color=\"green\">{$a[\'function\']} ()</font> &nbsp; -- ". dirname( $a[\'file\'] ). "/";' ) );
			$result=ob_get_contents();
			ob_end_clean();
			$errortext .= $result;
		}
	}

	// log the error in db is this is turned on
	if (LOG_ERRORS){
		global $db;
		if ($db){
			$sql="INSERT INTO error_logs (user_id,debug_backtrace,error_time) VALUES ($userid,\"$result\",NOW())";
			$rv=$db->query($sql);
		}
	}

	if ($fatal){ echo $errortext . "<p>Program Terminating.</p>"; exit;}
	return $errortext;
}

/* 
 * function helptext
 * Param: $helpid (int)
 * Meta: directly prints out any help text to do with the issue
*/
function helptext($helpid){
	global $db;
	$sql="SELECT * from help WHERE id=$helpid";
	$res=$db->query($sql);
	while ($h=$db->fetch_array($res)){
		print "<p class=\"admin_header\">Help: " . $h['title'] . "</p>";
		print $h['content'];
	}
}



?>
