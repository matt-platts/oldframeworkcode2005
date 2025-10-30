<?php

// write plugin to table.. 
$add_plugin_sql="INSERT INTO plugins (plugin_name,description,database_tables,plugin_directory,code_link_front,system) ";
$add_plugin_sql .= "values(\"Shopping Cart\",\"Shopping cart module fully integrated with dbForms and supports a number of further plugins and options.\",\"\",\"shopping_cart\",\"shopping_cart\",1)";
$result=mysql_query($add_plugin_sql);

echo "Shopping Cart has been installed successfully.";

?>
