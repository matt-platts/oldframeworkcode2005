<?php

$description=$argv[1];
$words=explode(" ",$description);
$return_desc="";

$description="Celebrating Impending Chaos";
$i=0;
$word_lengths=array();
foreach ($words as $word){
array_push($word_lengths,strlen($word));
$i++;
if ($i==2 && ($word_lengths[0] + $word_lengths[1]) >=18){$i=0; $word_lengths=array(); $word .= "<br />";}
if ($i==3){$i=0; $word .= "<br />"; $word_lengths=array();} else { $word .= " ";}
$return_desc .= $word;
}
print $return_desc;
?>
