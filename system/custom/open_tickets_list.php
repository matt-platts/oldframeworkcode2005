<?php

print "<table>";
print "<tr style=\"background-color:#f1f1f1; font-weight:bold\"><td>Order From</td><td>Date Placed</td><td></td></tr>";
$sql="SELECT orders.id as orderno, user.first_name,user.second_name,orders.datetime FROM orders INNER JOIN user ON orders.ordered_by = user.id WHERE orders.complete IS NULL ORDER BY datetime DESC";
$res=mysql_query($sql) or die(mysql_error());
while ($h=mysql_fetch_array($res)){
	print "<tr><td>".$h['first_name'] . " " . $h['second_name'] . "</td><td>" . $h['datetime'] . "</td><td><a href=\"Javascript:loadPage('administrator.php?action=admin_view_order&orderno=".$h['orderno']."&jx=1')\">View Order</a></td><td></td></tr>";
}

print "</table>";

?>
