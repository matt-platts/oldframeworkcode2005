<?php

mkdir("images/gallery") or plugin_install_error_terminate("Unable to create directory 'images/gallery'. Does it already exist? Also check that the images directory is writeable (chmod 777)");

print "Created images/gallery directory";


?>
