<?php
// configuration - put the absolute paths here
$basepath="/var/www/vhosts/paragon-digital.net/shop.paragon-digital.net";
$libpath="/var/www/vhosts/paragon-digital.net/shop.paragon-digital.net/system/lib/lib_v2.1";
$httppath="http://shop.paragon-digital.net/admin";

define("BASEPATH", $basepath);
define("HTTP_PATH",$httppath);
define("LIBPATH",$libpath);

if (empty($_SESSION)){session_start();}

require_once("$libpath/library/core/errors.php");
require_once("$libpath/library/core/require.php");


$user=new user();

$script_action=$_GET['action'];
?>
