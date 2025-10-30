<?php
?>
var tinyMCELinkList = new Array(
<?php
require_once("classes/database_mysql.php");
$db=new database_connection();
$sql = "SELECT * from content";
$res=$db->query($sql);
$output=array();
while ($h=$db->fetch_array($res)){
	if ($h['html_page_name']){
		array_push($output,"[\"".$h['title']."\",\"".$h['html_page_name']."\"]\n");
	} else {
		array_push($output,"[\"".$h['title']."\",\"site.php?content=".$h['id']."\"]\n");
	}
}
$output_string=implode(",",$output);
echo $output_string;
?>
 );
<?php

?>
