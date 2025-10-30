<?php
// ajax.php
// Herein lie the ajax functions
require("config.php");
require("$libpath/errors.php");
require("$libpath/require.php");
$page=new dbForms();
$user=new user();
$db=new database_connection();


if ($_REQUEST['table']){
	$tablename=$_REQUEST['table'];
	$tablefields = list_fields_in_table($tablename);

	$return_table_fields=implode(":",$tablefields);
	print $return_table_fields;
}

if ($_REQUEST['getDependentFields']){
	print "get dependents";
}

if (!$_REQUEST['table'] && !$_REQUEST['getDependentFields']){
	print "An error has occurred";
}

exit;

?>
