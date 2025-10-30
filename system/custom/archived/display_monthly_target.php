<?php

// first get current no. of sales

$sql2="SELECT monthly_min_target from user where id = " . user_data_from_cookie('id');
$result2=mysql_query($sql2);
$row=mysql_fetch_array($result2,MYSQL_ASSOC);
$min=$row['monthly_min_target'];

$get_current_month=date("m");
$get_current_year=date("Y");
$sql = "SELECT SUM(value_of_sale) AS month_sales_so_far from sales WHERE added_by = " . user_data_from_cookie('id') . " AND date_entered LIKE(\"".$get_current_year. "-".$get_current_month . "%\") AND approved IN (0,1)";
$res=mysql_query($sql);
$row=mysql_fetch_array($res,MYSQL_ASSOC);
$sales_so_far=0;
if ($row['month_sales_so_far']){
	$sales_so_far=$row['month_sales_so_far'];
}

// express current as a percentage of min...
$percentage=($sales_so_far/$min)*100;
$percentage_to_print=$percentage;
$percentage_to_flash=$percentage;
$percentage_to_flash=round($percentage);
$suffix=".";
if ($percentage>100){$percentage=100;$suffix="!";}
?>
<script type="text/javascript">
writeFlash({src:'images/flash/thermometer.swf?cVal=<?php echo $percentage_to_flash; ?>',width:'100',height:'450'});
</script>
<p>
<?php
print "<strong>You are currently at $percentage_to_print% of your target amount for " . date("F") . "!</strong>";
// then just load flash movie and feed in the %;








?>
