<?php

$description=$argv[1];
$id=$argv[2];
$artist=$argv[3];
$description=strip_tags($description);
$description=substr($description,0,200);
if ($description){ $description .= ".. <p class=\"read_more_products\"><a href=\"wholesale-artist-page/$id/$artist\">Read More</a></p>";}
print $description;

?>
