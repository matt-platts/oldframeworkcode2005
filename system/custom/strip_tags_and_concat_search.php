<?php

$description=$argv[1];
$id=$argv[2];
$artist=$argv[3];
$description=strip_tags($description);
$description=substr($description,0,500);
if ($description){ $description .= ".. ";}
print $description;

?>
