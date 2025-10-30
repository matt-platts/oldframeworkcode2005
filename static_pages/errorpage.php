<?php

if (preg_match("/.jpg/i",$_SERVER['REDIRECT_URL'])){
	$img=file_get_contents("images/artist_images/no_image_available.jpg");
	header("Content-type:image/jpeg");
	print $img;
} else if (preg_match("/.png/i",$_SERVER['REDIRECT_URL'])){
	$img=file_get_contents("images/artist_images/no_image_available.jpg");
	header("Content-type:image/jpeg");
	print $img;

} else {
	header("Location: index.html");
}
?>
