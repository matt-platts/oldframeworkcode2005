<?php

$res=custom_formula_query();

print "result of custom formula query is " . $res;

function custom_formula_query(){
	print "on custom formula query";
	$custom_formula_sql_1 = "SELECT SUM(sales.value_of_sale) AS sale_total_til_game_kicks_in from sales WHERE added_by = 289 and sales_type IN (1,2)";
	$result1=mysql_query($custom_formula_sql_1);
	$custom_formula_sql_2 = "SELECT SUM(value) as value_to_reach from user_saletype_min_vals WHERE id IN (1,2)";
	$result2=mysql_query($custom_formula_sql_2);

	$row1 = mysql_fetch_array($result1);
	$row2 = mysql_fetch_array($result2);

	var_dump ($row1);
	var_dump ($row2);

	if ($row1['sale_total_til_game_kicks_in'] >= $row2['value_to_reach']){
		return 1;
	} else {
		return 0;
	}
}

?>
