<?php

print "<p>Creating Database Table: ";

$sql = "create table google_analytics_code (id int auto_increment primary key, site_id int, code text)";
$result=mysql_query($sql) or die ("Cannot install google Analytics. The following error was received: " . mysql_error());

print "table created</p>";
print "<p>Adding Link to Menu: ";

$sql = 'insert into menu_items values("",1,"Update Google Analytics Code","administrator.php?action=edit_table&t=google_analytics_code&edit_type=edit_single&rowid=1&dbf_edi=1&dbf_edi=1",1,5,0,"","")';
$result=mysql_query($sql) or die ("Cannot install google Analytics. The following error was received: " . mysql_error());


// now we add another template that uses the filter above
print "<p>Added table and menu item successfully.<br /><br />A menu item has been added to the 'Content Manager' menu.";

?>
