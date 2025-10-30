<?php

// run through the system products table and load descriptions from test_releases..

require_once("../library/database.php");

$csv=file_get_contents("../io/sunlight_deal_types.csv") or die ("oops");

$lines=explode("\n",$csv);
$line=str_replace("\r","",$line);
array_shift($lines);
$i=1;
foreach ($lines as $line){
if (!$line){ continue; }
print "$i: ";
$i++;

list ($catno,$dealtype)=explode(",",$line);
if (!$dealtype){ continue; }
if (!$catno){ continue; }
$catno=str_replace("\"","",$catno);
$catno=trim($catno);
$dealtype=str_replace("\"","",$dealtype);
$dealtype=trim($dealtype);
print "C:$catno  - D:$dealtype \n";
$sql= "UPDATE products SET deal_type = \"$dealtype\" WHERE catalogue_number=\"$catno\"";
print $sql . "\n";
$res=mysql_query($sql) or die ("Cant do this " . mysql_error());
}
exit;
