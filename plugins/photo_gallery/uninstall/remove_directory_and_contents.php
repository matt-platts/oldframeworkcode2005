<?php

$image_files=get_directory_list("images/gallery");

foreach ($image_files as $image){
	$filename="images/gallery/$image";
	unlink($filename);
}

rmdir("images/gallery");

?>
