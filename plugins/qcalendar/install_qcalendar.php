<?php

$writefile="custom/qcalendar.php";
$fh = fopen($writefile,"w") or format_error("Unable to open $writefile for writing.
Check that the permissions on the '/custom' directory allow it to be written to.",1);

$readfile="plugins/qcalendar/custom/qcalendar.php";
$h = fopen($readfile,"r") or die("Cant read file");
$data=fread($h,filesize($readfile));
fclose($h);

fwrite($fh,$data);
fclose($fh);

$add_plugin_sql="INSERT INTO plugins (plugin_name,description,database_tables,plugin_directory,system) values(\"qcalendar\",\"Quick calendar - allows a front end view and back end administration.\",\"qcalendar,qcalendar_categories\",\"qcalendar\",1)";
$add_plugin_result=mysql_query($add_plugin_sql) or die("ERROR REGISTERING PLUGIN: " . mysql_error());


?>


