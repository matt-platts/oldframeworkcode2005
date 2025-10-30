<?php

if($_SERVER['SERVER_NAME'] != 'shop.paragon-digital.net'){
header('Location: http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'On')? 's' : '').'://shop.paragon-digital.net'. $_SERVER['REQUEST_URI'], true, 301);
}

/* EDIT THIS SECTION ONLY */
$libpath_local="system/lib"; // this should be the path to where the lib (core code library) is - no trailing forward slash /
$libpath_abs="/var/www/vhosts/paragon-digital.net/shop.paragon-digital.net/system/lib/lib_v3"; // OR you can specify an absolute directory
$iopath_local="/system/io";
$sysimgpath_local="/system/graphics";
$systempath_local="system";
$http_path="http://shop.paragon-digital.net";
$http_root="http://shop.paragon-digital.net";
$desktop_dir="desktop";

if (!array_key_exists("REDIRECT_URL",$_SERVER)){
	//format_error ("Cannot access site.php without a redirect url",1);
	//exit;
}
/*END EDITABLE INFO */

$basepath=getcwd(); 
// normalise the basepath in case we are starting from somewhere else.
if (preg_match("/system\/custom/",$basepath)){$basepath .= "/../.."; } // custom code can call config directly
if (preg_match("/$desktop_dir/",$basepath)){$basepath .= "/.."; } // start can call config directly
if (preg_match("/downloads/",$basepath)){$basepath .= "/.."; } // downloads for purchased downloads can call config directly
if (preg_match("/radio/",$basepath)){$basepath .= "/.."; } 
if (preg_match("/ajax/",$basepath)){$basepath .= "/.."; } 
if (preg_match("/$desktop_dir\/scripts/",$basepath)){$basepath .= "/.."; } // scripts can call config directly - note the line before already adds one step!
if (preg_match("/documentation\/system_documentation\/files/",$basepath)){$basepath .= "/../../.."; } // custom code can call config directly

/* THE FOLLOWING SECTION DOES NOT NEED TO BE EDITED! */
if ($libpath_abs){
	$libpath=$libpath_abs;
} else {
	$libpath=$basepath . "/" . $libpath_local;
}
$iopath=$iopath_local;
$system_path=getcwd() . "/" . $systempath_local; // as specified above

// LOAD INTO CONFIG
$CONFIG['basepath']=$basepath;
$CONFIG['libpath']=$libpath;

// AND MAKE CONSTANTS
define("LIBPATH", $libpath);
define("BASEPATH", $basepath);
define("IOPATH", $iopath);
define("SYSIMGPATH",$sysimgpath_local);
define("SYSTEM_PATH", $basepath);
define("HTTP_PATH", $http_path);
define("HTTP_ROOT", $http_root);

?>
