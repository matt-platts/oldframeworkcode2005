<?php

require_once("$basepath/modules/file_manager_macro_actions.php");

function upload_file_front($directory_to,$fileint){
if (!$col2_open){open_col2();}
?>
<h4>File Upload:</h4>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>?action=process_upload_file" method="post" enctype="multipart/form-data">
<table class="form_table">
<tr><td>Browse For File: </td><td><input type="file" name="file_upload">
<input type="hidden" name="directory_to" value="<?php echo $directory_to;?>">
<input type="hidden" name="d" value="<?php echo $directory_to;?>">
<input type="hidden" name="display_options" value="<?php echo $_REQUEST['display_options'];?>">
<input type="hidden" name="options_position" value="<?php echo $_REQUEST['options_position'];?>">
<input type="hidden" name="fileint" value="<?php echo $fileint;?>">
<input type="submit" class="form_button" value="Upload File"></td></tr>
</table>
</form>
<?
}

function upload_file_form($directory_to){
	$file_browse_location="directory_browser";
?>
<form style="display:inline; clear:none; text-align:right;" action="<?php echo $_SERVER['PHP_SELF']; ?>?action=process_upload_file&dir=<?=$directory_to;?>" method="post" enctype="multipart/form-data">
<table class="" style="border: 0px #333 dashed; background-color:transparent;">
<tr><td style=\"font-weight:bold\"><b>Upload File:</b> </td><td><input type="file" name="file_upload">
<input type="hidden" name="directory_to" value="<?php echo $directory_to;?>">
<input type="hidden" name="d" value="<?php echo $directory_to;?>">
<input type="hidden" name="display_options" value="<?php echo $_REQUEST['display_options'];?>">
<input type="hidden" name="options_position" value="<?php echo $_REQUEST['options_position'];?>">
<input type="hidden" name="file_browse_location" value="<?php echo $file_browse_location; ?>">
<input type="submit" class="form_button" value="Upload File"></td></tr>
</table>
</form>
<?
}

function process_upload_file($options){

	global $db;
	// start new bit
	$dir_to=$_POST['directory_to'];
	if ($_POST['file_browse_location']=="directory_browser"){
		$dir_to=$_POST['directory_to'];
		$dir_to= substr_replace($dir_to, "", strlen($dir_to)-1);
	}
	$int_sql="SELECT default_interface from file_manager WHERE directory = '$dir_to'";
	$int_res=$db->query($int_sql) or die(mysql_error());
	$h=$db->fetch_array($int_res);
	$default_interface=$h['default_interface'];
	if ($default_interface){
                $sql = "SELECT * from file_manager_options where interface = '" . $default_interface . "'";
		//print "run $sql";
                $res=$db->query($sql);
                while ($h=$db->fetch_array($res)){
                        $list_dir_options[$h['file_manager_option']] = $h['value'];
                }
		$list_dir_options['fileint']=$default_interface;
		$options=$list_dir_options;
	}
	// end new bit
	global $basepath;
	$filename=$_FILES['file_upload']["name"];
	if (!$filename){ return ;}
	$filesize= $_FILES['file_upload']["size"];
	$tmpname=$_FILES['file_upload']["tmp_name"];
	
	if ($dir_to){$upload_path=$dir_to;} else {$upload_path="file_uploads";}
	$upload_path=trim($upload_path);
	$newname = $basepath . "/$upload_path/$filename";

	if (move_uploaded_file($_FILES['file_upload']['tmp_name'],$newname)){
		$newname_for_print=$newname;
		$newname_for_print=str_replace("/var/www/vhosts/mattplatts.com/httpdocs/voiceprint/site/","",$newname_for_print);
		print "<p nowrap=\"nowrap\" style=\"background-image:url(".SYSIMGPATH."/icons/tick.png); background-repeat:no-repeat; padding-left:20px\">File uploaded successfully as $newname_for_print</p>";
	} else {
		print "<p style=\"\">Error in moving uploaded file: </p>";
	}

        if ($options['resize_to']){
                resize_image($newname,$options);
        }

	if ($options['pass_image_through_code']){
		include_once("library/image_functions.php");
		$function_call = $options['pass_image_through_code'];
		$function_call=str_replace("{=image}",$newname_for_print,$function_call);
		$function_call=str_replace("{=image_new}",$filename,$function_call);
		$function_call .= ";";
		//print "calling $function_call";	
		// NOTE: CHECK FUNCTION EXISTS IN CORRECT FILE BEFORE DOING THIS ON THEFLY
		$result=eval($function_call);
		//round_corners('images/artists/where_i_stand.jpg','images/artists/thumbs_round/where_i_stand.jpg','10');
		$function_call="round_corners";
		$a="images/artists/where_i_stand.jpg";
		$b="images/artists/thumbs_round/where_i_stand.jpg";
		$c=10;
		//$result= (function_exists($function_call)?call_user_func($function_call,$a,$b,$c): die("Function $function_call doesn't exist"));

		//$function_result=call_user_func($function_call) or die("wont work");
		//print "called it";
	}

        // MACRO CODE STARTS HERE

        $macro=value_in_table_field("file_manager_macros","directory","$dir_to","=","row");
        if ($macro){
                foreach ($macro as $macro_action){
                        $macro_id=$macro_action['id'];
			$sql="SELECT * from file_manager_macro_actions WHERE macro_id = $macro_id ORDER BY action_order";
                        $res=$db->query($sql);
                        while ($row=$db->fetch_array($res)){
                                $macro_results=run_file_manager_macro_action($row['action'],$filename,$dir_to,$row['variables']);
                                print $macro_results;
                        }
                }
        }
}

function run_file_manager_macro_action($macro_action,$filename,$directory,$variables){
        $variables=str_replace("&","\",\"",$variables);
        $eval_string=$macro_action . "('" . $directory . "/" . $filename . "'," . $variables . ");";
        //print $eval_string;
        $eval_string="\$return_value = $eval_string";
        $ev_result = eval($eval_string);
        return $return_value;
}

function resize_image($filename,$options){ // generic dbf function for resizing images, with options sent in as an $options associative array. The reisize_mode allows the image to be padded with colour, stretched/compressed as necessary to fit the exact dimensions sent or maintain_aspect_ratio allows the image to fit within the desired dimensions at the correct aspect ratio
        ini_set("memory_limit","128M");
        // Set a maximum height and width
        $resize_to=$options['resize_to'];
        $aspects=explode("x",$resize_to);
        $width=$aspects[0];
        $height=$aspects[1];

	$debug=0;

	if ($debug){print "<p>resizing to $width x $height";}

	$image_height=$height;
	$canvas_height=$height;
	$image_width=$width;
	$canvas_width=$width;
        // Content type
        list($width_orig, $height_orig) = getimagesize($filename);
	$ratio_orig = $width_orig/$height_orig;
	
	if ($options['resize_mode']=="stretch_and_compress_as_necessary" || $options['resize_mode']=="default"){
		$image_width=$width;
		$canvas_width=$width;
		$image_height=$height;
		$canvas_height=$height;	
	} else if ($options['resize_mode']=="maintain_aspect_ratio"){
		// constrains the aspect ratio to the longest side within the resize paramaters
		if ($width/$height > $ratio_orig) { 
			$canvas_width = $height*$ratio_orig;
			$image_width = $height*$ratio_orig;
		} else {
			$canvas_height = $width/$ratio_orig;
			$image_height= $width/$ratio_orig;
		}
	} else if ($options['resize_mode']=="pad_with_colour"){
		if ($width/$height > $ratio_orig) { 
		   $image_width = $width;
		   $image_width = $height*$ratio_orig;
		} else {
		   $canvas_height = $height;
		   $image_height = $width/$ratio_orig;
		}

	}
        // Resample
	if ($debug){print "<p>new canvas is $canvas_width by $canvas_height";}
	
	// Set background color
	if ($options['background_rgb_values']){
		$rgb_vals=explode(",",$options['background_rgb_values']);
		$rgb_r=trim(shift($rgb_values));
		$rgb_g=trim(shift($rgb_values));
		$rgb_b=trim(shift($rgb_values));
	} else if ($options['background_color']){
		if ($options['background_color']=="black"){
			$rgb_r=0; $rgb_g=0; $rgb_b=0;
		} elseif ($options['background_color']=="white"){
			$rgb_r=255; $rgb_g=255; $rgb_b=255;
		} else {
			$rgb_r=255; $rgb_g=255; $rgb_b=255;
		}
	} else {
		$rgb_r=255; $rgb_g=255; $rgb_b=255;
	}

	if ($debug){print "<p>adding rgb values of $rgb_r, $rgb_g, $rgb_b";}
	if (!$canvas_width || !$canvas_height){
		format_error("Cannot create an image using width:$canvas_width and height:$canvas_height as values for file " . basename($filename) . ".",1);
	}
        $image_new = @imagecreatetruecolor($canvas_width, $canvas_height) or die("Cannot Initialize new GD image stream at width $canvas_width and height $canvas_height for file: " . basename($filename) . ".");
	$background_rgb=imagecolorallocate($image_new, $rgb_r,$rgb_g,$rgb_b) or die ("oops");
	if ($debug){ print "<p>creating image from $filename</p>"; }
        $image = imagecreatefromjpeg($filename); // load origina image up
	imagefilledrectangle($image_new, 0, 0, $canvas_width-1, $canvas_height-1, $background_rgb);
        imagecopyresampled($image_new, $image, 0, 0, 0, 0, $image_width, $image_height, $width_orig, $height_orig);
        // Output
	$basename=basename($filename);
        if ($options['new_filename']){ $output_file = $options['new_filename'] . "/" . $basename;} else { $output_file = $filename; }
	if ($debug) { print "<p>making new image called $output_file</p>"; }
        imagejpeg($image_new, $output_file, 100);
        $return_values['canvas_width']=round($canvas_width);
        $return_values['canvas_height']=round($canvas_height);
        return $return_values;
}


function get_file_options(){
	global $db;
        if ($_REQUEST['fileint']){
                $sql = "SELECT * from file_manager_options where interface = '" . $_REQUEST['fileint'] . "'";
                $res=$db->query($sql) or die(mysql_error());
                while ($h=$db->fetch_array($res)){
                        $options[$h['file_manager_option']] = $h['value'];
                        if ($h['file_manager_option']=="list_type"){$list_type=$h['value'];}
                }
        }
	//print "returning options of " . $options;
        return $options;
}

?>
