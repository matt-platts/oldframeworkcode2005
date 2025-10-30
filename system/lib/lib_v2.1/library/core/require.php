<?php

// First load all the stuff we always need - database and user
require_once (LIBPATH . "/classes/core/database_mysqli.php");
$db=new database_connection();

require_once (LIBPATH . "/classes/core/user.php");
$user=new user();

// controller may use database so moved to include after
require_once(LIBPATH . "/controllers/baseController.php");
$base = new baseController;

// checks login status for the desktop interface
if ($_GET['action']=="dbf_mui_check_login"){
	if ($user->value("id") && $_COOKIE){
		print "ok"; exit;
	}
}

// page and view
require_once (LIBPATH . "/classes/core/page.php");
$page=new page();
require_once (LIBPATH . "/classes/view.php");
$view=new view();

require_once (LIBPATH . "/classes/core/Codeparser.php");
require_once (LIBPATH . "/library/core/load_system_defaults.php"); // loads the configutration table into a hash table
require_once (LIBPATH . "/library/core/general.php"); // general functions - currently in the process of being put into their propper classes 
require_once (LIBPATH . "/library/custom/custom_functions.php"); // custom functions -  user editable only - not required for out of the box 
require_once (LIBPATH . "/library/core/admin_pages.php"); // general functions and pages 
require_once (LIBPATH . "/library/core/templates_and_content.php"); // templating functions
require_once (LIBPATH . "/library/core/database_functions.php"); // database <-> html forms core code module
require_once (LIBPATH . "/library/core/tables.php"); // database <-> html forms core code module
require_once (LIBPATH . "/library/core/filters.php"); // database <-> html forms core code module
require_once (LIBPATH . "/library/modules/cms.php"); // functions for the inline cms
require_once (LIBPATH . "/library/modules/dynamic_css_menu.php"); // module to create the menus from the menu table
require_once (LIBPATH . "/library/core/web_site_manager.php"); // move to cms class
require_once (LIBPATH . "/library/modules/file_manager.php"); // file managers should only be included when required.. 
require_once (LIBPATH . "/library/modules/file_uploader.php");
//require_once (LIBPATH . "/modules/survey.php");

require_once (LIBPATH . "/library/core/queries.php");

// load the admin input limiting for admin pages only
if (preg_match("/dmin_routing\.php$/",$_SERVER['PHP_SELF'])){
	require_once (LIBPATH . "/library/core/limit_input_admin.php");
}

if (preg_match("/admin_routing.php$/",$_SERVER['PHP_SELF']) || preg_match("/ajax\.php$/",$_SERVER['PHP_SELF'])){
	require_once (BASEPATH . "/system/custom/custom_logic_admin_preload.php");
	require_once (LIBPATH . "/library/core/interfaces.php");
	require_once (LIBPATH . "/library/core/plugins.php"); // no point in including this here, only if it is requested - there are only 4 functions
	require_once (LIBPATH . "/library/core/autodoc.php"); // as above
	require_once (LIBPATH . "/library/core/import_export.php"); // as above
	require_once (LIBPATH . "/library/core/transfer.php");
	// Require any site specific modules here
	if ($debug){require_once (LIBPATH . "/library/core/debugging.php");}

// requirements for the front end only 
} else if (strlen(stristr($_SERVER['PHP_SELF'],"site_routing.php"))){
	require_once (LIBPATH . "/library/core/limit_input_site.php");
	require_once(BASEPATH . "/system/custom/custom_logic_front.php");
}

require_once (LIBPATH . "/library/core/debugging.php");
?>
