<?php

if ($_REQUEST['pageSize']){
	$_REQUEST['dbf_rpp']=$_REQUEST['pageSize'];
}
$_REQUEST['dbf_next']=$_REQUEST['pageSize']+1;
if ($_REQUEST['page']){
	$_REQUEST['dbf_next']=($_REQUEST['pageSize']*$_REQUEST['page']+1)-$_REQUEST['pageSize'];
}
$str="";
foreach ($_REQUEST as $k=>$v){
	$str .= "$k - $v\n";
}
//file_put_contents("myfile.txt",$str);
if (empty($_SESSION)){session_start();}
require_once ("../config.php");
require_once ("$libpath/errors.php");
require_once ("$libpath/require.php");

require_once("$libpath/classes/filters.php");
$db_filter=new filter();
$dbforms_options=$db_filter->load_options(); // NB this now loads filter as well
$dbforms_options['dbf_output_type']="moodgets_data_store";
$dbforms_options['export']="hash";
$table=$_REQUEST['mgt'];
$res=list_table($table,$dbforms_options) or die("Cant list table");
exit;
//print "{total:15,data:[ {id: '1', zone_name: 'UK'},{id: '2', zone_name: 'Europe EU'},{id: '3', zone_name: 'Europe Non EU'},{id: '4', zone_name: 'USA / Canada'},{id: '5', zone_name: 'Rest of the whole World'}]}";
?>
