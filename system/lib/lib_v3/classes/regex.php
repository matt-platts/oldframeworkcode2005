<?php

$input = " 3. Positive AND existence : {=if this+that} GOT BOTH {=end_if} ";

$r = preg_match_all("/{=if ([!?\w\++]+\w+)}(.*?){=end[ _]?if}/ims",$input,$and_results);
var_dump($and_results);
?>
