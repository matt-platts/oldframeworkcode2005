<?

// delete registration of plugin from plugins table
$plugin_sql="DELETE FROM plugins WHERE plugin_name=\"Mailform\"";
$result=mysql_query($plugin_sql) or die("ERROR: ".mysql_error());
print "<p>Deleted plugin registration from plugins table</p>";
print "<p>Note: form has been left in append_files directory. You can remove this if you wish."

?>


