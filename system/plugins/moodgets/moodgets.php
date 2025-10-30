<?php

class moodgets{

function __construct(){
}

function moodgets_data_store(){

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
	global $libpath;
	require_once("$libpath/classes/filters.php");
	$db_filter=new filter();
	$dbforms_options=$db_filter->load_options(); // NB this now loads filter as well
	$dbforms_options['dbf_output_type']="moodgets_data_store";
	$dbforms_options['export']="hash";
	$table=$_REQUEST['mgt'];
	$res=list_table($table,$dbforms_options) or die("Cant list table");
	exit;
	//print "{total:15,data:[ {id: '1', zone_name: 'UK'},{id: '2', zone_name: 'Europe EU'},{id: '3', zone_name: 'Europe Non EU'},{id: '4', zone_name: 'USA / Canada'},{id: '5', zone_name: 'Rest of the whole World'}]}";

}

function moodgets_save_data(){
	ob_end_clean();
	$str="";
	foreach ($_REQUEST as $k=>$v){
		$str .= "$k = $v\n";
	}

	$table=$_REQUEST['t'];
	// get the primary key of the table
	$pk=get_primary_key($table);		

	// updated data
	if ($_REQUEST['updated']){
		$update_data=$this->mg_json_decode($_REQUEST['updated'],$pk);
		$rets=var_export($update_data,true);
		global $libpath;
		include_once("$libpath/classes/db_updater.php");
		$upd=new db_updater();
		$update_results=$upd->update_table($table,$update_data,"");
		$full_update_results=var_export($update_results,true);
		$str .= "Full results: $full_update_results\n";
	}
	if ($_REQUEST['deleted']){
		$delete_data=$this->mg_json_decode($_REQUEST['deleted'],$pk);
		$del=new db_updater();
		$del_results=$del->delete_records($table,$delete_data,"");

	}
	file_put_contents("moodgets_data_latest.txt",$str);
	exit;
}

function mg_json_decode($json,$pk){
	$json=preg_replace("/\[/","",$json);
	$json=preg_replace("/\]/","",$json);
	$each=explode("},{",$json);
	$addcounter=1;
	foreach ($each as $each_row){
		$each_row=str_replace("{","",$each_row);	
		$each_row=str_replace("}","",$each_row);	
		$fields=explode("\",\"",$each_row);
		foreach ($fields as $pair){
			list($var,$val)=explode("\":\"",$pair);
			$var=str_replace("\"","",$var);
			$val=preg_replace("/\"$/","",$val);
			$row[$var]=$val;
			if ($var==$pk){
				// if no primary key, or pk=space, use an addcounter...
				if ($val=="&nbsp;" || !$val) {
					$rowid="ADDNEWROW_".$addcounter;
					$addcounter++;
				} else {
					 $rowid=$val; 
				}
			}
		}
	$rows[$rowid]=$row;
	}
	return $rows;
}

// end class
}

// example usage
/*
$updated = '[{"id":"2","user_type":"administrators","user_type_description":"A web site administrator who can use the administrator functions of the application that you build.","hierarchial_order":"4","admin_access":"1","system":"1"},{"id":"4","user_type":"masterwe","user_type_description":"Master is used to create superadmins and is not normally given out to the client but retained for yourself, and can create superadmins, administrators and users, and also change the system tables.","hierarchial_order":"2","admin_access":"1","system":"1"}]';
$mood=new moodgets();
$result=$mood->mg_json_decode($updated,"id");
var_dump($result);
print "\n";
print var_dump($result["4"]['user_type']);
*/
?>
