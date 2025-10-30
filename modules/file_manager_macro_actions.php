<?php

/* file_manager_macro_actions.php
   Copyright Matt Platts 2010 */

/* Place in here code that runs as a macro action on an uploaded file
   Each of these functions will be called for each image as a batch job or on upload if the macro is applied
   to a directory
   The first argument of each function must be the file including the path from the root directory - 
   this is the only restriction. The code should do whatever is required for the macro and optionally return
   an html message that will be printed to the screen when the macro has run */
   
/* For Images: A generic resize function can be called - 'resize_image' which accepts the following paramaters:
   filename (string) - name of the file including the path from the base install directory
   options (associated array (hash)) - which should in turn contain the following paramaters:
	resize_to - pixels to resize to in the format widthxheight. Append the width and height together as a string
	simply separated by 'x' - eg. 200x200, 640x480 etc.
	resize_mode - default is 'maintain_aspect_ratio' which resizes to the largest paramater and constrains the smaller
		      to maintain the aspect ration. Other options are 'stretch_and_compress_as_necessary' in order to
		      resize the image to the exact pixels you have specified. 'pad_with_colour' maintains the aspect
		      ratio and places the image on a coloured background specified in 'background_rgb_values'
	background_rgb_values - for the pad_with_colour option above, send an array of 3 paramaters from 0-255
	background_color - in place of background_rgb_values you can simply set background_colour to either black or white
*/
 	

function gif_from_png($png, $background = array(255, 255, 255), $dest='images/frontpage'){
        //print "on $png";
        $size = getimagesize($png);
        $img = imagecreatefrompng($png);
        $image = imagecreatetruecolor($width = $size[0], $height = $size[1]);
        imagefill($image, 0, 0, $bgcolor = imagecolorallocate($image, $background[0], $background[1], $background[2]));
        imagecopyresampled($image, $img, 0, 0, 0, 0, $width, $height, $width, $height) or print "NO";
        imagecolortransparent($image, $bgcolor);
        imagegif($image, str_ireplace('.png', '.gif', $dest.DIRECTORY_SEPARATOR.basename($png)), 100);
        //print "<br>done imagefid and made: " . str_ireplace('.png','.gif', $dest.DIRECTORY_SEPARATOR.basename($png));
        imagedestroy($image);
        return "A gif file has been created from the png '$png'";
}

function make_resized_copy($original_file,$value_x,$value_y,$new_location,$resize_mode){
// this function is called by the macro action only..
        $options['resize_to']=$value_x . "x" . $value_y;
        if ($new_location){ $options['new_filename']=$new_location; }
        if ($resize_mode){$options['resize_mode']=$resize_mode;} else { $options['resize_mode']="maintain_aspect_ratio"; }
	if (!preg_match("/.jpg$/i",$original_file)){ return "Cannot resize the file " . basename($original_file) . " - file must be a jpg.<br />"; }
        $return_sizes = resize_image($original_file,$options);
        return "A resized image of ".basename($original_file)." at " . $return_sizes['canvas_width'] . "x" . $return_sizes['canvas_height'] . "px has been placed in $new_location<br />";
}

function resize_original_image($original_file,$value_x,$value_y,$resize_mode){
        $options['resize_to']=$value_x . "x" . $value_y;
        if ($resize_mode){$options['resize_mode']=$resize_mode;} else { $options['resize_mode']="maintain_aspect_ratio"; }
	if (!preg_match("/.jpg$/i",$original_file)){ return "Cannot resize the file " . basename($original_file) . " - file must be a jpg.<br />"; }
        $return_sizes = resize_image($original_file,$options);
        return "$original_file has been resized to " . $return_sizes['canvas_width'] . "x" . $return_sizes['canvas_height'] . "px<br />";
}





?>
