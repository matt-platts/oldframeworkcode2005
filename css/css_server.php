<?php

$csspage=$_GET['cssfile'];
if (preg_match("/\.\.\//",$csspage)){
	exit;
}
if (!preg_match("/.css$/",$csspage)){
	exit;
}

$cssfile=file_get_contents($csspage);
$https="";

if (array_key_exists("HTTPS",$_SERVER)){
	$https="on";
}
if ($https=="on"){
	$cssfile=str_replace("http://","https://",$cssfile);
}
header("Content-type:text/css");
print $cssfile;
?>
