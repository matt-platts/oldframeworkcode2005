<?php

if ($_REQUEST['action']=="remove_user_from_office"){$content .= remove_user_from_office($_POST['office_user'],$_POST['office_id'],$_POST['user_id']); $title="Remove Office";}


function remove_user_from_office($lookup,$oid,$uid){
	$current_user=user_data_from_cookie("ID");
	if ($current_user != $uid){
		return "Error: Cannot remove user";
		exit;
	}
	$sql="DELETE from user_office_lookup WHERE id=$lookup AND office_id=$oid AND user_id=$uid";
	$res=mysql_query($sql) or die(mysql_error());
	$return_string = "<p>User successfully removed from office</p>";
	$return_string .= "<p><a href=\"account.html\">Return to Account Page</a></p>";
	return $return_string;
}

# this is the end
# no this one is
