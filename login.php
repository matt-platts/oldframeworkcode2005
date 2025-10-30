<?php
require_once ("config.php");
require_once ("$libpath/library/core/errors.php");
require_once ("$libpath/library/core/require.php");
$page=new page();
$user=new user();
$db=new database_connection();
$use_built_in_styles=1; // this is a standard admin page, so Im just going to use the defaults.

global $admin_title;
print str_replace("<?php echo \$admin_title; ?>",$admin_title,$page->load_header(1));
// If we have a login request, process it, otherwise if we're logged in, refresh the login cookie
if ($_GET['action']=='process_login'){ $login_result=process_login($_POST['email_address'],$_POST['password'],"site.php?s=1"); } else if ($_COOKIE['login']){ refresh_login_cookie();}

$page=file_get_contents("html_pages/example_login.html") or die ("Cant get file contents");
print $page;
exit;

// If no action, run the home page
open_col2();
?>
	<div id="innerlogindiv" style="width:270px">
	<img src="jpg" border=0> <img src="graphics/application_images/mysql_data_manager_logo_small.jpg" width=190 border=0>
	<h4 style="background-color:#1b2c67; width:270px; color:#ffffff; padding:3px;">Please log in:</h4>
	<hr size="1" style="color:gray" />
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?action=process_login" method="post" name="login_form" onsubmit="check_login()">
	<table border="0" cellpadding="0" cellspacing="0">
	<tr><td align='right' class='form_table'>Email:</td><td><input type="text" class="login_sidebar_textbox" name="email_address" value="" size="15" /></td></tr>
	<tr><td>&nbsp;</td><td></td></tr>
	<tr><td align='right' class='form_table'>Password:</td><td><input type="password" class="login_sidebar_textbox" name="password" value="" size="15" /></td></tr> 
	<tr><td>&nbsp;</td><td></td></tr>
	<tr><td></td><td><input type="submit" value="Log In" class="login_sidebar_button" /></td></tr></table>
	</form>
	<hr size="1" style="color:gray">
	<font color="gray" size=1>Please check your email for your password.<br />If you have any problems logging in, <br /> please mail <a href="mailto:matt.platts@paragon-digital.com">matt.platts@paragon-digital.com</a></font>.
	<hr size="1" style="color:gray">
	</div>
	<script language="Javascript" type="text/javascript">
	<!--
	window.onLoad=document.forms[0].elements[0].focus();
	//-->
	</script>
<?php
close_col();
?>
