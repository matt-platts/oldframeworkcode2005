<?php

$sale_id=$_REQUEST['sid'];
$sql="SELECT * from game_results WHERE sale_id = $sale_id AND prize_id=\"\"";
$result=mysql_query($sql);

if (mysql_num_rows($result)>1){print "ERROR - game logged multiple times.";exit;}

$row=mysql_fetch_array($result,MYSQL_ASSOC);
$points=$row['points_awarded'];

if (user_data_from_cookie('id')!=$row['user_id']){print "ERROR: User login credentials incorrect\n";exit;}

$select_products_sql="SELECT * FROM products WHERE points = $points";
if ($debug){print $select_products_sql;}
print "<hr size='1'>";
$result=mysql_query($select_products_sql);
while ($row=mysql_fetch_array($result,MYSQL_ASSOC)){
	print "<p><img src=\"".$row['image']."\" border=0><br /><b>" . $row['name'] . "</b><br />" . $row['description'];
	print "</p><p align=\"right\" style=\"text-align:right\"><a href=\"site.php?s=1&content=37&sid=$sale_id&z=".$row['id']."\">Claim This Prize &gt;</a><br />";
	print "<hr size='1'>";
}


?>
