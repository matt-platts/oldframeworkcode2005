<?php 

// load extra code required by plugins
$check_plugin_sql="SELECT code_link_front FROM plugins WHERE (code_link_front IS NOT NULL and code_link_front != \"\")";
$res=$db->query($check_plugin_sql) or format_error("Error in $check_plugin_sql: ".mysql_error(),1);
while ($row=$db->fetch_array($res)){
	if ($row['code_link_front']){
		$code_link = "system/custom/".$row['code_link_front'].".php";
		require_once($code_link);
	}
}

// note this is from aearo or some other application which needs to be moduled//
function check_user_totals(){
	global $options;
	var_dump($options);
	include("system/custom/custom_formula_code.php") or die ("Cant include file");
	print "FILE INCLUDED<p>";

	$custom_formula_sql_1 = "SELECT SUM(sales.value_of_sale) AS sale_total_til_game_kicks_in from sales WHERE added_by = 289 and sales_type IN (1,2)";
	$result1=mysql_query($custom_formula_sql_1);
	$custom_formula_sql_2 = "SELECT SUM(value) as value_to_reach from user_saletype_min_vals WHERE id IN (1,2)";
	$result2=mysql_query($custom_formula_sql_2);

	$row1 = mysql_fetch_array($result1);
	$row2 = mysql_fetch_array($result2);

	var_dump ($row1);
	var_dump ($row2);

	if ($row1['sale_total_til_game_kicks_in'] >= $row2['value_to_reach']){
		print "on witht he gamew!";
	} else {
		$options['filter']['after_update_display_content_id']=42;
		print "<p>ID:" . $options['filter']['after_update_display_content_id'];
	}
}

?>
