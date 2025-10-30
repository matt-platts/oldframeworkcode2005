<?php

require_once("../config.php");
require_once("../dbconf.php");
require_once("/var/www/vhosts/software/lib/classes/database.php");
$db=new database_connection;

$sql="SELECT * FROM product_tracklists";
$rv=mysql_query($sql);
while ($h=mysql_fetch_array($rv,MYSQL_ASSOC)){
	print "got";
}

print "done";
exit;

