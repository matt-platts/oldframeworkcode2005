<?php
// Thanks to http://www.assemblysys.com/dataServices/php_roundedCorners.php for this script
header("Content-type:image/jpeg");
$image_file = $_GET['src'];
$corner_radius = isset($_GET['radius']) ? $_GET['radius'] : 20; // The default corner radius is set to 20px
$angle = isset($_GET['angle']) ? $_GET['angle'] : 0; // The default angle is set to 0º
$topleft = (isset($_GET['topleft']) and $_GET['topleft'] == "no") ? false : true; // Top-left rounded corner is shown by default
$bottomleft = (isset($_GET['bottomleft']) and $_GET['bottomleft'] == "no") ? false : true; // Bottom-left rounded corner is shown by default
$bottomright = (isset($_GET['bottomright']) and $_GET['bottomright'] == "no") ? false : true; // Bottom-right rounded corner is shown by default
$topright = (isset($_GET['topright']) and $_GET['topright'] == "no") ? false : true; // Top-right rounded corner is shown by default

$images_dir = 'images/';
$corner_source = imagecreatefrompng('images/rounded_corner_20px.png');

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

// Output final image
imagejpeg($image);

imagedestroy($image);  
imagedestroy($corner_source);
?>

