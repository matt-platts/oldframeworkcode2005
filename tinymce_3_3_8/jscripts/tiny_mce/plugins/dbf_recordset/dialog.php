<?php
require_once("../../../../../system/lib/classes/database.php");
$db=new database_connection();
$filter_sql="SELECT id,filter_name FROM filters ORDER BY filter_name";
$filter_rv=$db->query($filter_sql);
$filters=array();
while ($filter_h=$db->fetch_array($filter_rv)){
	array_push($filters,$filter_h['id'] . ";;".$filter_h['filter_name']);
}
$filters=join(",",$filters);
require_once("../../../../../system/lib/database_functions.php");
$filters_options=build_select_option_list($filters,"","","1","","","");
$filters_options=preg_replace("/value=\"(.*)\"/i","value=\"\\1\" id=\"filteroption_\\1\"",$filters_options);
require_once("../../../../../system/lib/tables.php");
$tables_list=list_tables_basic("include_queries");
$tables_list=join(",",$tables_list);
$tables_listing=build_select_option_list($tables_list,"","","1","","","");
$tables_listing=preg_replace("/value=\"(.*)\"/i","value=\"\\1\" id=\"tableoption_\\1\"",$tables_listing);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Dynamic Recordset</title>
	<script type="text/javascript" src="../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="js/dialog.js"></script>
</head>
<body>

<form onsubmit="DbfRecordsetDialog.insert();return false;" action="#">
	<p style="font-weight:bold">Insert / Edit Dynamic Recordset</p>
	<hr size="1" />
	<p><b>Selected text: </b></td><td><input id="someval" name="someval" type="text" class="text" style="width:320px" /></p>
	<hr size="1" />
	<p style="font-weight:bold">Data Source: <br />
	<?php print "<select id=\"dbf_table\" name=\"dbf_table\">$tables_listing</select>";?>
	<p style="font-weight:bold">Filter: <br />
	<?php print "<select id=\"dbf_filter\" name=\"dbf_filter\">$filters_options</select>";?>
	<!--
	<tr><td align="right">
	<p>Form Type: </td><td><input id="dbf_formtype" name="dbf_formtype" type="text" class="text" /></p></td></tr>
	<tr><td align="right">
	<p>Row Id: </td><td><input id="dbf_rowid" name="dbf_rowid" type="text" class="text" /></p></td></tr>
	//-->

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="insert" name="insert" value="{#insert}" onclick="DbfRecordsetDialog.insert();" />
		</div>

		<div style="float: right">
			<input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
		</div>
	</div>
</form>

</body>
</html>
