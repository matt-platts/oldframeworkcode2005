<?php
?>
var tinyMCELinkList = new Array(
<?php
require_once("../lib/errors.php");
require_once("../lib/classes/database.php");
$db=new database_connection();
$sql = "SELECT * from content order by title";
$res=$db->query($sql);
$output=array();
$output_1=array();
$output_2=array();
$output_header1=array();
$output_header2=array();
array_push($output_header1,"[\"------- WEB SITE PAGES --------\",\"\"]\n");
while ($h=$db->fetch_array($res)){
	array_push($output_1,"[\"".ucfirst($h['title'])."\",\"site.php?s=1&content=".$h['id']."\"]\n");
}

$dir = "../../files/";
    array_push($output_header2,"[\"--------- FILES --------\",\"\"]\n");
if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
	    if (preg_match("/file/",filetype($dir . $file))){
		    $print_filename=ucfirst($file);
		    array_push($output_2,"[\"$print_filename\",\"downloads/$file\"]\n");
	    }
        }
        closedir($dh);
    }
}

sort ($output_1);
sort ($output_2);
$output=array_merge($output_header1,$output_1,$output_header2,$output_2);
$output_string=implode(",",$output);
echo $output_string;
?>
 );
<?php

?>
