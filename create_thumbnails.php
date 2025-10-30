<html>
<head>
<title>
Create Thumbnails</title>
<style type="text/css">
body {font-size:11px; font-family: Trebuchet MS, Tahoma, Verdana, Arial, Helvetica;}
</style>
</head>
<body style="font-family:Tahoma, Trebuchet MS, Verdana">
<?php

if (!$_REQUEST['input_dirs']){
?>
<?php
get_input_dirs();
exit;

}

get_input_dirs();
ini_set("memory_limit","128M");
ini_set("max_execution_time","300");
print "<h4>&bull; Thumbnail Creator</h4>";
print "<hr size=1>";

if ($_REQUEST['input_dirs'] == "ALL"){
	$directory= ".";
	if($handle = opendir($directory)) {
	  // Read in filenames one at a time
	  while(($file = readdir($handle)) !== false) {
	    // Do not add unix 'files' . (current directory) and .. (parent directory) 
	    if($file !== '.' && $file !== '..') {
		if (is_dir($file) && !preg_match("/thumbs$/",$file)){
		      $files[] = $file;
		}
	    }  
	}
	  // Close
	  closedir($handle);
	} 

	sort($files);
	$input_dirs=$files;

} else {
$inputdir = $_REQUEST['input_dirs'];
$input_dirs=array("$inputdir");

}
//$input_dirs=array("winners/barrister_of_the_year","winners/excellence_in_client_service","winners/excellence_in_equality_and_diversity","winners/excellence_in_exporting_legal_services","winners/excellence_in_pioneering_legal_services","winners/excellence_in_practice_standards","winners/excellence_in_marketing","winners/excellence_in_social_responsibility_community_engagement","winners/excellence_in_social_responsibility_pro_bono","winners/junior_lawyer_of_the_year","winners/legal_executive_of_the_year","winners/solicitor_of_the_year","commended/barrister_of_the_year","commended/excellence_in_client_service","commended/excellence_in_equality_and_diversity","commended/excellence_in_exporting_legal_services","commended/excellence_in_pioneering_legal_services","commended/excellence_in_practice_standards","commended/excellence_in_marketing","commended/excellence_in_social_responsibility_community_engagement","commended/excellence_in_social_responsibility_pro_bono","commended/junior_lawyer_of_the_year","commended/legal_executive_of_the_year","commended/solicitor_of_the_year","stage","general");
$thumbnail_width=400;
ini_set("display_errors","2");
ERROR_REPORTING(E_ALL);
foreach ($input_dirs as $input_dir){
	
	$input_directory=$input_dir . "/";
	$output_directory="/var/www/vhosts/mattplatts.com/httpdocs/voiceprint/site/" . $input_dir . "/".$input_dir."_thumbs" . "/";
	if (file_exists($output_directory)){
		print "Confirmed: output directory of $output_directory exists";
	} else {
		print "dirercory $output_directory does not exist!";
		mkdir($output_directory) or print "cant make output";
}
	print "<p><b>Creating Thumbs for $input_dir</b><br /><span style=\"font-size:9px\">(Place thumbs in $output_directory)</span><br />";
	createThumbs($input_directory,$output_directory,$thumbnail_width);

	print "<br/>Finished creating thumbs for $output_directory.";
}

print "<p><b>Thumbnail Creation Complete</b></p>";
print "</body></html>";
exit; 
function createThumbs( $pathToImages, $pathToThumbs, $thumbWidth ){
  // open the directory
  $dir = opendir( $pathToImages );

  // loop through it, looking for any/all JPG files:
  while (false !== ($fname = readdir( $dir ))) {
    // parse path for the extension
    $info = pathinfo($pathToImages . $fname);
    // continue only if this is a JPEG image
    if ( strtolower($info['extension']) == 'jpg' )
    {
      echo " -&gt; Creating thumbnail for {$fname} ";

      // load image and get image size
      $img = imagecreatefromjpeg( "{$pathToImages}{$fname}" );
      $width = imagesx( $img );
      $height = imagesy( $img );

      // calculate thumbnail size
      $new_width = $thumbWidth;
      $new_height = floor( $height * ( $thumbWidth / $width ) );

      // create a new temporary image
      $tmp_img = imagecreatetruecolor( $new_width, $new_height );

      // copy and resize old image into new image
      imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

      // save thumbnail into a file
      imagejpeg( $tmp_img, "{$pathToThumbs}{$fname}" );
	print "<span style=\"font-size:9px\">(saving image in $pathToThumbs as $fname</span>)<br />";
    }
  }
  // close the directory
  closedir( $dir );
}

function get_input_dirs(){
?>
<div id="topdiv" style="padding:8px; border-style:dashed; border-width:1px; background-color:#f1f1f1"><b>Welcome to the thumbnail creator.</b>
<br /><br />
<ul><li> This program will automatically create thumbnails based on <u>jpeg images only</u> found in the directories in the main 'photos' directory. </li>
<li>All photos should be uploaded before using this software.</li>
<li>This script has a maximum execution time of 5 minutes and content will be flushed to the browser every so often. There may be a slight delay whilst the imaging libraries are set up so  <u>please  not click the button below repeatedly whilst you are waiting for something to happen</u>. A warning message will be shown if the program has not completed in five minutes. This is only likely to happen if you are using the 'All Directories' option. In this event, you should create thumbnails for each directory separately. </li>
<br /></div>
<hr size=1>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
Please select which directory to create thumbnails for:

<?
$directory= ".";
if($handle = opendir($directory)) {
  // Read in filenames one at a time
  while(($file = readdir($handle)) !== false) {
    // Do not add unix 'files' . (current directory) and .. (parent directory) 
    if($file !== '.' && $file !== '..') {
	if (is_dir($file) && !preg_match("/thumbs$/",$file)){
	      $files[] = $file;
	      $options_list .= "<option value=\"".$file."\">$file</option>";
	}
    }  
}
  // Close
  closedir($handle);
} 
sort($files);
$input_dirs=$files;
foreach ($input_dirs as $dir){
	      $options_list_2 .= "<option value=\"".$dir."\">$dir</option>";
}
?>

<select name="input_dirs">
<option value="ALL">ALL DIRECTORIES</option>
<?php echo $options_list_2; ?>
</select>
<input type="submit" value="Create Thumbnails">
</form>

<?
}
?>

