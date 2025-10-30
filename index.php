<?php
// THE FOLLOWING 3 REQUIRES PULL IN EVERYTHING NECESSARY FOR ANYTHING!
require_once ("config.php"); // set some basic variables - requires change for each installation of this software
require_once(LIBPATH ."/library/core/errors.php");
require_once (LIBPATH . "/library/core/require.php"); // require all other files that we need

global $CONFIG;
print "behaved";
if ($CONFIG['log_out_at_index_page']){
process_log_out(""); // causes a log out on the index
}
if ($CONFIG['site_requires_login'] >=1){
header("Location: login.php");
exit;
}

if ($CONFIG['direct_index_to']){
$location_header="Location: " . $CONFIG['direct_index_to'];
header($location_header); 
exit;

}

if (count($CONFIG) <=1 ){
	if (file_exists("install.php")){
		header("Location:install.php");
	} else {
		print "No database configuration found, no install found either";
	}
}

?>
<!-- CUSTOM CONTENT HERE //-->
<html>
<head>
<title><?php echo $CONFIG['index_page_title']; ?></title>
</head>
<body bgcolor="#ffffff">
<center>
<table height="95%"><tr><td valign="middle">
<a style="color:#b2c67" href="login.php"><img src="http://www.mattplatts.com/mysql_data_manager/graphics/application_images/mysql_data_manager_logo_small.jpg" border=0 /><br /><br /><center><font color="#1b2c67" face="Trebuchet MS, verdana, arial, helvetica">Click To Enter</font></center></a>
</td></tr></table>
</center>
</body>
</html>
