<?php

$description=$argv[1];
$words=explode(" ",$description);
array_pop($words);
$return_desc="";

$i=0;
foreach ($words as $word){
$i++;
if ($i==3){$i=0; $word .= "<br />";} else { $word .= " ";}
$return_desc .= $word;
}
chop($return_desc);
print $return_desc . "..";
?>
