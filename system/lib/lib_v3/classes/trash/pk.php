<?php

print "ok\n";
require_once("classes/database.php");
require_once("classes/recordset.php");

$rs=new recordset();

$rs->set_value("pk","id");

$fields="id,this,that";
if (!preg_match("/$rs->value('pk'),/",$fields)){
	print "matches";

}

?>
