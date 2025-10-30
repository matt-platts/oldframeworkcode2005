<?php

$table=$_GET['t'];
$id=$_GET['id'];
$where=$_GET['w'];
$key_field=$_GET['kf'];
$value_field=$_GET['vf'];
$value=$_GET['v'];
$type=$_GET['type'];
$item_value=$_GET['item_value'];

print_r($_REQUEST);
print "\n\n";
require_once("../config.php");
require_once(LIBPATH."/errors.php");
require_once(LIBPATH."/classes/database.php");
require_once(LIBPATH."/classes/user.php");
session_start();
$db=new database_connection();
$user=new user();
$uid=$user->value("id");
if (!$uid){
   print "Sorry - you don't have permissions to do this.";
   exit;
}

if ($type=="checkbox"){ // on a tinyint, if there are no rows insert it if val is 1
   $testSQL="SELECT * FROM $table WHERE $key_field = \"$id\" AND $value_field = \"$value\"";
   $rv=$db->query($testSQL);
   $num=mysql_num_rows($rv);

print $testSQL;
print " - numrows $num and checked is $item_value" . "\n\n";

   if (!$num && $item_value=="1"){
      $insertSQL="INSERT INTO $table ($key_field,$value_field) VALUES(\"$id\",\"$value\")";
      $insert_result=$db->query($insertSQL);
      print $insertSQL;

   } else if ($num && $item_value=="0"){
      // do nothing, it's already there
      $deleteSQL="DELETE FROM $table WHERE $key_field=\"$id\" AND $value_field=\"$value\"";
      $delete_result=$db->query($deleteSQL);
      print $deleteSQL;
   }
}

exit;

$sql="UPDATE $table SET $key_field = \"$value\" WHERE $where = \"$id\"";
$rv=$db->admin_update_query($sql,$table,$id) or die("error YUJ17");
//print "$sql";
print "1";
?>

