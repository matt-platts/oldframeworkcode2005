<?
require("library/database.php");

if ($_REQUEST['table']){
	$table=$_REQUEST['table'];
} else {
	$table="content|title";
}
list($table,$title_field)=explode("|",$table);
print "<table border=0 cellpadding=0 cellspacing=0 width=100%><tr><td>\n";
print "<div class=\"preview_header\">\n";
print "<a href=\"Javascript:nosectionpreview()\" style=\"background-color:#f1f1f1;font-color:#ffffff; font-size:10px;\">CLOSE LIST</a>";
print "</div>\n";
print "<ul class=\"preview_list\">";
$sql="SELECT * FROM ".$table;
$res=mysql_query($sql);
while ($data=mysql_fetch_array($res,MYSQL_ASSOC)){
	print "<li><a style=\"font-size:11px;\" href=\"Javascript:loadPage('administrator.php?action=edit_table&t=$table&edit_type=edit_single&rowid=" . $data['id'] . "&jx=1')\">" . $data[$title_field] . "</a></li>";
}

print "</ul>";
print "</td></tr></table>";
?>
<script language="Javascript">
}
</script>

