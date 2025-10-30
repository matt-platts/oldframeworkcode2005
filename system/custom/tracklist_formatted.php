<?php

$tracklist=$argv[1];
print $tracklist;
exit;
$tracklist=strip_tags($argv[1]);
//$tracklist=strip_tags($argv[1]);
$tracklist=convert_smart_quotes(str_replace("\n",", ",$tracklist));
$tracklist=preg_replace("/, $/","",$tracklist);
print $tracklist.".";


function convert_smart_quotes($string) 
{ 
    $search = array(chr(145), 
                    chr(146), 
                    chr(147), 
                    chr(148), 
                    chr(151)); 
 
    $replace = array("'", 
                     "'", 
                     '"', 
                     '"', 
                     '-'); 
 
    return str_replace($search, $replace, $string); 
} 

?>
