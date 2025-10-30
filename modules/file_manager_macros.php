<?php

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
        return "A resized image at " . $return_sizes['canvas_width'] . "x" . $return_sizes['canvas_height'] . "px has been placed in $new_location<br />";
}

function resize_original_image($original_file,$value_x,$value_y,$resize_mode){
        $options['resize_to']=$value_x . "x" . $value_y;
        if ($resize_mode){$options['resize_mode']=$resize_mode;} else { $options['resize_mode']="maintain_aspect_ratio"; }
	if (!preg_match("/.jpg$/i",$original_file)){ return "Cannot resize the file " . basename($original_file) . " - file must be a jpg.<br />"; }
        $return_sizes = resize_image($original_file,$options);
        return "$original_file has been resized to " . $return_sizes['canvas_width'] . "x" . $return_sizes['canvas_height'] . "px<br />";
}





?>


