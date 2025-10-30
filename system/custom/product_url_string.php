<?php
$artist=str_replace(" ","_",$argv[1]);
$title=str_replace(" ","_",$argv[2]);

$artist=str_replace("/","-",$artist);
$artist=str_replace(",","",$artist);

print $artist."-".$title.".html";
?>
