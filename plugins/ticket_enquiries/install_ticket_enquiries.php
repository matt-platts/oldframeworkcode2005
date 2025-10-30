<?php

$add_plugin_sql="INSERT INTO plugins (plugin_name,description,database_tables,plugin_directory,code_link_front,system) values(\"Ticket Enquiries\",\"Ticket Enquiries allows tickets to be raised by users through the front end and answered through the back end.\",\"tickets,ticket_details\",\"ticket_enquiries\",\"ticketing\",1)";

$result=mysql_query($add_plugin_sql);

echo "Ticket Enquiries has been installed successfully.";

?>
