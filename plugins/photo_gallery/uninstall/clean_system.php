<?

print "<p><div style=\"border-width:2px; border-color:green; border-style:dashed; padding:10px\">";
print "<b>Items to be manually removed:</b><p>Any content items that try to use the photo gallery should manually be removed.<br />";
print "You should also remove two filters 'Edit Photo Gallery' and 'Edit Photo Gallery Inline CMS' and the keys related to these";
print "Any cms entries in the cms table should be removed manually<br />";
print "</div>";

// delete table relations
$relation_sql="DELETE FROM table_relations WHERE table_1 = 'photo_gallery'";
$result=mysql_query($relation_sql) or die("ERROR: ".mysql_error());
print "<p>Deleted Table Relations</p>";

// delete registration of plugin from plugins table
$plugin_sql="DELETE FROM plugins WHERE plugin_name=\"Photo Gallery\"";
$result=mysql_query($plugin_sql) or die("ERROR: ".mysql_error());
print "<p>Deleted plugin registration from plugins table</p>";

// delete cms entries

?>
