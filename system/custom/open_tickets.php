<?php

print "<table>";
print "<tr style=\"background-color:#f1f1f1; font-weight:bold\"><td>Description</td><td>Status</td><td></td></tr>";
 	$sql="SELECT * from tickets WHERE last_updated_by=\"user\" AND status != \"closed\"";
$res=mysql_query($sql) or die(mysql_error());
while ($h=mysql_fetch_array($res)){
	print "<tr><td>".$h['title'] . "</td><td>" . $h['status'] . "</td><td><a href=\"administrator.php?action=admin_view_ticket&ticketno=".$h['id']."\">View Ticket</a></td><td></td></tr>";
}

print "</table>";

?>
