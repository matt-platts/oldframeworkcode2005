<?php
$string_to_function="Here is the string going into the function";
$sysresult = exec("/usr/bin/php product_description_linebreaks.php \"$string_to_function\"",$embed_external_output);

print $sysresult;
print "\n";
print $embed_external_output;

?>
