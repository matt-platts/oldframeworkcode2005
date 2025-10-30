<?php

print "<p>Removing Database Table: ";

$sql = "drop table google_analytics_code";
$result=mysql_query($sql) or format_error("Cannot remove google Analytics.
The following error was received: " . mysql_error() . "
Perhaps this plugin has already been removed?",1);

print "table deleted</p>";

// now we add another template that uses the filter above
print "<p>Google analytics has been uninstalled. Please remove the menu option manually from the menu_items table or through menu manager.</p>";

?>
