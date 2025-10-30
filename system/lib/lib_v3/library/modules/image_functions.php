<?php

/* 
 * Function round_corners
*/
function round_corners($image_file,$output_image_name,$radius,$end_width,$end_height,$angle,$topleft,$bottomleft,$bottomright,$topright){
	// based on an original script at http://www.assemblysys.com/dataServices/php_roundedCorners.php

	$corner_radius = isset($radius) ? $radius : 20; // The default corner radius is set to 20px
	$angle = isset($angle) ? $angle : 0; // The default angle is set to 0º
	$topleft = (isset($topleft) and $topleft == "no") ? false : true; // Top-left rounded corner is shown by default
	$bottomleft = (isset($bottomleft) and $bottomleft == "no") ? false : true; // Bottom-left rounded corner is shown by default
	$bottomright = (isset($bottomright) and $bottomright == "no") ? false : true; // Bottom-right rounded corner is shown by default
	$topright = (isset($topright) and $topright == "no") ? false : true; // Top-right rounded corner is shown by default

	$images_dir = 'images/';
	$cornersource= LIBPATH ."/image_scripts/images/rounded_corner_20px.png";
	$corner_source = imagecreatefrompng($cornersource);

	$corner_width = imagesx($corner_source);  
	$corner_height = imagesy($corner_source);  
	$corner_resized = ImageCreateTrueColor($corner_radius, $corner_radius);
	ImageCopyResampled($corner_resized, $corner_source, 0, 0, 0, 0, $corner_radius, $corner_radius, $corner_width, $corner_height);

	$corner_width = imagesx($corner_resized);  
	$corner_height = imagesy($corner_resized);  
	$image = imagecreatetruecolor($corner_width, $corner_height);  
	$image = imagecreatefromjpeg($image_file); // replace filename with $_GET['src'] 
	$size = getimagesize($image_file); // replace filename with $_GET['src'] 
	$white = ImageColorAllocate($image,255,255,255);
	$black = ImageColorAllocate($image,0,0,0);

	// Top-left corner
	if ($topleft == true) {
	    $dest_x = 0;  
	    $dest_y = 0;  
	    imagecolortransparent($corner_resized, $black); 
	    imagecopymerge($image, $corner_resized, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);
	} 

	// Bottom-left corner
	if ($bottomleft == true) {
	    $dest_x = 0;  
	    $dest_y = $size[1] - $corner_height; 
	    $rotated = imagerotate($corner_resized, 90, 0);
	    imagecolortransparent($rotated, $black); 
	    imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Bottom-right corner
	if ($bottomright == true) {
	    $dest_x = $size[0] - $corner_width;  
	    $dest_y = $size[1] - $corner_height;  
	    $rotated = imagerotate($corner_resized, 180, 0);
	    imagecolortransparent($rotated, $black); 
	    imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Top-right corner
	if ($topright == true) {
	    $dest_x = $size[0] - $corner_width;  
	    $dest_y = 0;  
	    $rotated = imagerotate($corner_resized, 270, 0);
	    imagecolortransparent($rotated, $black); 
	    imagecopymerge($image, $rotated, $dest_x, $dest_y, 0, 0, $corner_width, $corner_height, 100);  
	}

	// Rotate image
	$image = imagerotate($image, $angle, $white);

	// resize if needs be
	$image_thumb=imagecreatetruecolor($end_width,$end_height) or die ("ouch");
	$width_orig=$size[0];
	$height_orig=$size[1];
	imagecopyresampled($image_thumb, $image, 0, 0, 0, 0, $end_width, $end_height, $width_orig, $height_orig) or die ("no way");
	// Output final image
	print "&bull; A rounded corner thumbnail has been written to $output_image_name";
	//$binary_to_write=imagejpeg($image_thumb,$output_image_name);
	$output_image_tn_name="" . $output_image_name . "_thumb_.jpg";
	$binary_to_write=imagejpeg($image,$output_image_name) or die ("cant write image");
	$binary_to_write=imagejpeg($image_thumb,$output_image_tn_name) or die ("cant write thumbnail");

	imagedestroy($image);  
	imagedestroy($corner_source);
}

/*
 * Function _ckdir
*/
function _ckdir($fn) {
    if (strpos($fn,"/") !== false) {
        $p=substr($fn,0,strrpos($fn,"/"));
        if (!is_dir($p)) {
            _o("Mkdir: ".$p);
            mkdir($p,777,true);
        }
    }
}

/*
 * Function img_resizer
*/
function img_resizer($src,$quality,$w,$h,$saveas) {
    /* v2.5 with auto crop */
    $r=1;
    $e=strtolower(substr($src,strrpos($src,".")+1,3));
    if (($e == "jpg") || ($e == "peg")) {
        $OldImage=ImageCreateFromJpeg($src) or $r=0;
    } elseif ($e == "gif") {
        $OldImage=ImageCreateFromGif($src) or $r=0;
    } elseif ($e == "bmp") {
        $OldImage=ImageCreateFromwbmp($src) or $r=0;
    } elseif ($e == "png") {
        $OldImage=ImageCreateFromPng($src) or $r=0;
    } else {
        _o("Not a Valid Image! (".$e.") -- ".$src);$r=0;
    }
    if ($r) {
        list($width,$height)=getimagesize($src);
        // check if ratios match
        $_ratio=array($width/$height,$w/$h);
        if ($_ratio[0] != $_ratio[1]) { // crop image

            // find the right scale to use
            $_scale=min((float)($width/$w),(float)($height/$h));

            // coords to crop
            $cropX=(float)($width-($_scale*$w));
            $cropY=(float)($height-($_scale*$h));   
           
            // cropped image size
            $cropW=(float)($width-$cropX);
            $cropH=(float)($height-$cropY);
           
            $crop=ImageCreateTrueColor($cropW,$cropH);
            // crop the middle part of the image to fit proportions
            ImageCopy(
                $crop,
                $OldImage,
                0,
                0,
                (int)($cropX/2),
                (int)($cropY/2),
                $cropW,
                $cropH
            );
        }
       
        // do the thumbnail
        $NewThumb=ImageCreateTrueColor($w,$h);
        if (isset($crop)) { // been cropped
            ImageCopyResampled(
                $NewThumb,
                $crop,
                0,
                0,
                0,
                0,
                $w,
                $h,
                $cropW,
                $cropH
            );
            ImageDestroy($crop);
        } else { // ratio match, regular resize
            ImageCopyResampled(
                $NewThumb,
                $OldImage,
                0,
                0,
                0,
                0,
                $w,
                $h,
                $width,
                $height
            );
        }
        _ckdir($saveas);
        ImageJpeg($NewThumb,$saveas,$quality);
        ImageDestroy($NewThumb);
        ImageDestroy($OldImage);
    }
    return $r;
}

?>
