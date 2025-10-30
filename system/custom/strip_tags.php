<?php

$description=$argv[1];
$orig=$description;
$description=strip_tags($description);
$description=substr($description,0,300);
print $description;

?>
