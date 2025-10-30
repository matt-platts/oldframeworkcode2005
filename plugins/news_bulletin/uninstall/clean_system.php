<?

print "<p><div style=\"border-width:2px; border-color:green; border-style:dashed; padding:10px\">";
print "<b>Items to be manually removed:</b><p>Any content items that try to use the news (one page was installed by default).<br />";
print "You should also remove four filters 'Display News' 'Edit News Item Through CMS', 'Add News Item Through CMS' and 'List News For Edit', and the keys related to these<br />";
print "Any cms entries in the cms table should be removed manually. Two entries were installed by default.<br />";
print "Three templates were also installed by default as well.<br />";
print "</div>";

// delete table relations
$template_sql="DELETE FROM templates WHERE template_name IN (\"Display News Item\",\"Display News Item Each Row\")";
$result=mysql_query($template_sql) or die("ERROR: ".mysql_error());
print "<p>Deleted Templates</p>";

// delete registration of plugin from plugins table
$plugin_sql="DELETE FROM plugins WHERE plugin_name=\"News Bulletin\"";
$result=mysql_query($plugin_sql) or die("ERROR: ".mysql_error());
print "<p>Deleted plugin registration from plugins table</p>";


?>
