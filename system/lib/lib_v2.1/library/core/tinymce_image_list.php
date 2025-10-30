<?php
?>
var tinyMCEImageList = new Array(
<?php
if ($_GET['dirlist']){
	$dirlist=str_replace(":::::","/",$_GET['dirlist']);
	$directory_array=explode(",",$dirlist);
	$directory_names_array=array();
	foreach ($directory_array as $arr_dir){
	//	array_push($directory_names_array,array_shift($directory_array));
		$print_dir_name=explode("/",$arr_dir);
		$print_dir_name=ucfirst(array_pop($print_dir_name));
		array_push($directory_names_array,$print_dir_name);
	}
} else {
	$directory_array=array("images/news","images/artists","images/general_images");
	$directory_names_array=array("NEWS IMAGES","ARTIST IMAGES","GENERAL IMAGES");
}

$output=array();
$i=0;
foreach ($directory_array as $dir){
	$dir_from_here="../".$dir;
	array_push($output,"[\"------------ ".$directory_names_array[$i]." --------------\",\"\"]\n");
	if (is_dir($dir_from_here)) {
	    if ($dh = opendir($dir_from_here)) {
		while (($file = readdir($dh)) !== false) {
		    if (preg_match("/file/",filetype($dir_from_here . "/" . $file))){
			    array_push($output,"[\"$file\",\"$dir/$file\"]\n");
		    }
		}
		closedir($dh);
	    } else {
		array_push($output,"[\" Error: Cannot read directory $dir_from_here\",\"\"]\n");
		}
	} else {
		array_push($output,"[\" Error: $dir_from_here is not a directory\",\"\"]\n");
	}
	$i++;
	array_push($output,"[\"\",\"\"]\n");
}
$output_string=implode(",",$output);
echo $output_string;
?>
 );
<?php
?>
