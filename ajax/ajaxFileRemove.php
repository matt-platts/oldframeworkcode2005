<?php

// Think THIS IS DEPRECATED - CODE MOVED INTO MAIN BASE??:w


// do we need to add the classes in here to run this? probably! - note that this has not been checked yet!
$page=new dbForms();
$user=new user();
$db=new database_connection();

var_dump($_REQUEST);
if (!$_REQUEST['t'] || !$_REQUEST['f'] || !$_REQUEST['id']){
	print "Unable to remove file.\n"; exit;
}
$table=$_REQUEST['table'];
$field=$_REQUEST['f'];
$id=$_REQUEST['id'];

$permissions_result=check_dbf_permissions($table,"delete",$id);
if ($permissions_result['Status']==0){
	print $permissions_result['Message'];
	exit;
}
//exit;
$sql="SELECT $field from $table WHERE id=$id";
$res=mysql_query($sql);
$h=mysql_fetch_array($res,MYSQL_ASSOC);
$filename=$h[$field];

$sql="UPDATE $table set $field = '' WHERE id = $id";
$res=mysql_query($sql);

print "File Deleted";
exit;

?>
