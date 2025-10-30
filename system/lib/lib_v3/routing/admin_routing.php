<?php

// 1 AUTOLOADER FOR DYNAMIC CONTROLLER CLASS LOADING VIA THE ROUTE
function __autoload($class_name) {
	include LIBPATH . "/controllers/" . $class_name . '.php';
}

// 2 INITIALIZE
if (empty($_SESSION)){session_start();}  // fire up the session
require_once (LIBPATH . "/library/core/errors.php");  // basic error formatting included first 
require_once (LIBPATH . "/library/core/require.php"); // load all necessary files and instantiate necessary classes
// END INITIALIZE


/* 3 GET THE CONTROLLER AND THE ACTION FROM THE ROUTE 
*    The first parameter is the controller, the second is the function within the controller.
*    Further paramaters are used by that controller as required.
*/
$path = $base->getPath(); 
$urlParts=explode("/",$path['path']);

// Authentication starts here - move to user class 

	// 4 User not logged in trying to go somewhere else? Redirect to home..
	if (!$user->value("id")){
		if (($urlParts[1] != "admin" && array_key_exists("action",$_GET)) || $urlParts[2]){
			if ($_REQUEST['dbf_mui']) {
				print format_error("You are not logged in",1);
			}
		}
	}


	// 5 If we have a login request, process it, otherwise if we're logged in, refresh the login cookie
	if ($script_action=='process_login'){
		$login_result=$user->process_login($_POST['email_address'],$_POST['password'],$_SERVER['PHP_SELF']);
	} else if ($_COOKIE['login']){
		$user->refresh_login_cookie();
	}

	// 6 block access to types other than admins etc.
	if ($user->value("id") && !$user->value("admin_access")){
		session_unset();
		session_destroy();
		$_SESSION = array();
		header("Location: /");
	}

	// 7 if log out
	if ($_GET['action']=="process_log_out"){
		if (!$_GET['dir_to']) {
			$dir_to = $_SERVER['PHP_SELF'];
		} else {
			$dir_to = $_GET['dir_to'];
		}
		$user->process_log_out($dir_to);
	}

// END AUTHENTICATION



// if it's admin this is part of the display url and not the path, so shift it off the array
if ($urlParts[1]=="admin" && $urlParts[2]){
	array_shift($urlParts);
}
if ($urlParts[1]=="desktop" && $urlParts[2]){ #MATT2020
	array_shift($urlParts);
}
if ($urlParts[1]=="mui" && $urlParts[2]){ #MATT2020
	array_shift($urlParts);
}

/* 4 Assign the controller and the action in the controller from the url parts */
$controller=$urlParts[1] . "Controller";
$loadAction = $urlParts[2];
if (!$controller && !$loadAction){
	$controller="adminController"; // default to the admin controller if no further variables. This simply deals with the admin login and logout.
}

/* 5 If the second part of the url is numeric and there are no further parts, or there is only one part, route to defaultAction  */
if (is_numeric($loadAction) && !$urlParts[3] || !$urlParts[2]){
	$loadAction="defaultAction";
}

// 6 LOAD THE CONTROLLER 
try {
	$loadController = $base->loadController($controller);
}
catch(Exception $e) {
	  $page->content = '<p>Error: ' .$e->getMessage()."</p>";
}

// DEBUGGING IF REQUIRED
$debug=0;
if ($debug){
	print "<div  width:100%; background-color:#f1f1f1; opacity:0.9; border:1px red dashed'>";
	var_dump($loadController);
	var_dump("query string",$_SERVER['QUERY_STRING']);
	print "<p>Controller $controller and action $loadAction</p><br />";
	print "</div>";
}

/* 7 CALL THE CONTROLLER ACTION 
*  The response is boolean true if successful. Content should be stored in the page class at this point
*/
if (method_exists($loadController,$loadAction)){
	$response = call_user_func(array($loadController,$loadAction));
} else if ($debug) {
	format_error("$controller cannot use $loadAction");
}

if (null === $response){
	
	/********************************** 
	* OLD NON-CLASSED routing - This is DEPRECATED but allows the old existing logic to work
	* Essentially if we haven't managed to get content from a class we can call defaultAction which will analyse all other url types.
	* All these other actions need to be put into their own classes at some point
	*/
	
	$loadController = $base->loadController("MultiActionController");
	ob_start();
	$loadController->defaultAction();
	//include_once(LIBPATH . "/routing_extra_admin.php");
	$page->set_value("content",ob_get_contents());
	ob_end_clean();
	/********************************** END OLD NON-CLASSED ROUTING */

}

/* 8 CHECK THE PAGE CLASS HAS CONTENT */
if (!$page->value("content")){
	$debug=1;
	if ($debug){
		format_error("The controller '$controller' (admin routing) did not return a value - possibly function $loadAction does not exist?<br />The fallback controller was not able to deal with this either. Fallbacksearched for " . $page->value("script_action") . "",1); 
	} else {
		$page->set_value("content",format_error("404",0));
	}
}

/* 9 ASSIGN THE VIEW, AND PRINT  */
if ($_REQUEST['dbf_mui'] || $base->is_mui()){
	$view->set_value("view","mui-administrator.html");
} else {
	$view->set_value("view","administrator.html");
}
$result = $view->printPage();

if (!$result){
	format_error("No response from the view",1);
}

/* 10 TIDY UP */
$db->close_db();
?>
