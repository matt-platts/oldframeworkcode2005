<?php

class db_updater {
	
	function __construct(){

	}

function update_table($table,$data,$options){

	$permissions_result = check_dbf_permissions($table, "edit_all" );
	if ($permissions_result['Status']==0){
		return $permissions_result['Message']; exit;
        }


	$pk=get_primary_key($table);	
	//$pk="id";

	global $db;
	// update rows...
	$start_sql="UPDATE $table SET";
	$data_to_add=array();
	$update_results=array();
	array_push($update_results,$data);
	foreach ($data as $rowid =>$row_array){
		array_push($update_results,"On rowid $rowid:");
		if (stristr($rowid,"NEWROW_")){
			array_push($data_to_add,$row_array);
			array_push($update_results,"We got it!");
		} else {
			$update_fields=array();
			foreach ($row_array as $key => $val){
				if ($key==$pk){ continue; }
				array_push($update_fields, "$key = \"$val\"");
			}

			$main_sql = join(", ", $update_fields);
			$where_sql = "WHERE $pk = $rowid";
			//print $main_sql . "\n";
			$full_sql = $start_sql . " " . $main_sql . " " . $where_sql;
			
			$update_result=$db->query($full_sql);
			array_push($update_results, $update_result);
		}
	}

	if ($data_to_add){
		$start_sql="INSERT INTO $table";
		foreach ($data_to_add as $add_row){
			$fields=array();
			$values=array();
			foreach ($add_row as $field => $value){
				array_push($fields,$field);		
				array_push($values,$value);
			}
			$main_sql= " (" . join(",",$fields) . ") VALUES(\"" . join("\",\"",$values) . "\")";
			$full_sql = $start_sql . $main_sql;
			$add_result = $db->query($full_sql);
			array_push($update_results,"$full_sql: $add_result");
		}
	}
	
	return $update_results;
	
}

function delete_records($table,$data,$options){

	$permissions_result = check_dbf_permissions($table, "delete" );
	if ($permissions_result['Status']==0){
		return $permissions_result['Message']; exit;
        }

	$pk=get_primary_key($table);

	$start_sql = "DELETE FROM $table WHERE $pk IN(";
	$delete_results=array();
	$delete_rows=array();
	foreach ($data as $rowid => $row_array){
		array_push($delete_rows,$rowid);
	}
	// now have a list of pks to delete
	$main_sql=join(",",$delete_rows);
	$end_sql = ")";
	$full_sql = $start_sql.$main_sql.$end_sql;	
	global $db;
	$delete_result=$db->query($full_sql);
	array_push($delete_results,$delete_result);
	return $full_sql . " - " . $delete_results;
}

// end class
}
/*
$data=array (
  1 =>
  array (
    'id' => '1',
    'user_type' => 'usererwer',
    'user_type_description' => 'A basic web site user. Use for front login, web site members etc.',
    'hierarchial_order' => '6',
    'admin_access' => '0',
    'system' => '1',
  ),
);
$dbu=new db_updater();
$res=$dbu->update_table("user_types",$data,"");
*/
?>
