<?php

$sql="delete from page_logs WHERE view_time < DATE_SUB(current_date,INTERVAL 20 DAY)";
$rv=$db->query($sql) or format_error("Cannot clear page logs: " . mysql_error(),1);
print "<p class=\"dbf_para_success\">Page logs have been cleared other than the last 20 days which have been retained.</p>";

?>
