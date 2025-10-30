<?php

$sql="delete from sys_form_log WHERE gen_time < NOW()-86400";
$rv=$db->query($sql) or format_error("Cannot clear sys form logs: " . mysql_error(),1);
print "<p class=\"dbf_para_success\">System Form Logs have been cleared other than the last 24 hours.</p>";

?>
