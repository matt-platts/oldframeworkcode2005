<?php

// AUTOLOADER FOR DYNAMIC CLASS LOADING VIA THE ROUTE
function __autoload($class_name){
	include LIBPATH . "/controllers/" . $class_name . '.php';
}

// INITIALIZE SESSION
if (empty($_SESSION)){
	session_start();
}

// GET REQUIRED FILES
require_once (LIBPATH . "/library/core/errors.php"); // basic error formatting and debugging info
require_once (LIBPATH . "/library/core/require.php"); // load all necessary files and instantiate necessary classes

// GET THE CONTROLLER AND THE ACTION FROM THE ROUTE 
$path = parse_url($_SERVER['REQUEST_URI']);
$urlParts=explode("/",$path['path']);
$controller=$urlParts[1] . "Controller";
$loadAction = $urlParts[2];
if (!$controller && !$loadAction){
	$controller="siteController";
}
// if the second part of the url is numeric and there are no further parts, or there is only one part, route to defaultAction 
if (is_numeric($loadAction) && !$urlParts[3] || !$urlParts[2]){
	$loadAction="defaultAction";
}

// LOAD THE CONTROLLER 
try {
	$loadController = $base->loadController($controller);
}
catch(Exception $e) {
	  $page->content = '<p>Error: ' .$e->getMessage()."</p>";
}

// DEBUGGING IF REQUIRED
$debug=0;
if ($debug){
	print "<div style='position:fixed; top:0px; left:0px; width:100%; background-color:#f1f1f1; opacity:0.9; border:1px red dashed'>";
	var_dump($loadController);
	var_dump("query string",$_SERVER['QUERY_STRING']);
	print "<p>Controller $controller and action $loadAction</p><br />";
	print "</div>";
}

// CALL THE CONTROLLER ACTION 
if (method_exists($loadController,$loadAction)){
	$response = call_user_func(array($loadController,$loadAction));
} else if ($debug) {
	format_error("$controller cannot use $loadAction");
}
if (null === $response){
	
	/********************************** OLD NON-CLASSED routing - from a static page. This is DEPRECATED but allows the old logic to work*/
	$loadController = $base->loadController("SiteMultiActionController");
	ob_start();
	$loadController->defaultAction();
	//include_once(LIBPATH . "/routing_extra_admin.php");
	$page->set_value("content",ob_get_contents());
	ob_end_clean();
	/********************************** END OLD NON-CLASSED ROUTING */


	if (!$page->value("content")){
		if ($debug){
			format_error("$controller (admin routing) did not return a value - possibly function $loadAction does not exist? Multi-action was not able to deal with this either.",1); 
		} else {
			$page->set_value("content",format_error("404",0));
		}
	}
}

// all content is loaded into the view in the page class, we use the view class to print it
if ($_REQUEST['dbf_mui'] || $base->is_mui()){
	$view->set_value("view","mui-administrator.html");
} else {
	$view->set_value("view","administrator.html");
}

$result = $view->printPage();

if (!$result){
	format_error("No response from the view",1);
}

$close_db=$db->close_db();
?>
