<?php
require_once ("config.php");
require_once ("$libpath/errors.php");
require_once ("$libpath/require.php");
$use_built_in_styles=1; // this is a standard admin page, so Im just going to use the defaults.

global $admin_title;
print str_replace("<?php echo \$admin_title; ?>",$admin_title,$page->load_header(1));
// If we have a login request, process it, otherwise if we're logged in, refresh the login cookie
if ($_GET['action']=='process_registration'){ $registration_result=process_registration($_POST); } else if ($_COOKIE['login']){ $user->refresh_login_cookie();}

$page=$page->content_from_id(113);
print $page;
exit;

function process_registration($POST){

$id = $POST['id'];
$first_name=$POST['first_name'];
$second_name=$POST['second_name'];
$username=$POST['username'];
$organisation=$POST['organisation'];
$officename=$POST['officename'];
$jobtitle=$POST['job_title'];
$addr1 = $POST['addr1'];
$addr2 = $POST['addr2'];
$addr3 = $POST['addr3'];
$town = $POST['town'];
$county = $POST['county'];
$postcode = $POST['postcode'];
$country = $POST['country'];
$telephone = $POST['telephone'];
$email = $POST['email'];
$mobile = $POST['mobile'];
$fax = $POST['fax'];
$datenow = "NOW()";
$sql = "INSERT INTO registration_requests (";
                $sql .= "id,processed,first_name,second_name,job_title,username,organisation,officename,addr1,addr2,addr3,";
                $sql .= "town,county,postcode,country,telephone,email,mobile,fax,";
                $sql .= "date_updated,date_created) VALUES (";
                $sql .= "'','0','$first_name','$second_name','$jobtitle','$username','$organisation','$officename','$addr1','$addr2','$addr3',";
                $sql .= "'$town','$county','$postcode','$country','$telephone','$email','$mobile','$fax',";
                $sql .= "$datenow,$datenow)";

	$result=mysql_query($sql) or die(mysql_error());
	print "<center>";
	print "<img src=\"images/jc_main_page.gif\" border=0><p>";
	print "<b>Your request for registration has been sent. Thank you.</b></p><p>If your registration request is approved then confirmation<br /> including your logon username and password will be emailed to you.</p>";
	exit;
}
