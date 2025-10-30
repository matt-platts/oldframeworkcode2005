<?php

$add_plugin_sql="INSERT INTO plugins (plugin_name,description,database_tables,plugin_directory,code_link_front,code_link_back,system) values(\"Mailing List\",\"Mailing List allows a full email mailing list application to run as part of the software. With full admin, subscribe/unsubscribe, email creator and mail sender.\",\"emails,mailing_list\",\"mailing_list_front\",\"mailing_list\",\"mailinglist\",1)";

$result=mysql_query($add_plugin_sql) or format_error("Cannot install mailing list module",1);

echo "Mailing List has been installed successfully.";

// need to create a table too!

exit;
// add an admin content item:
$addcontent="INSERT INTO content (title,value) VALUES ('Select Email Template','Need to include selection page here (dont use pages template?)')";
$result=mysql_query($addcontent);
// filter to save record as new one
$add_filter="INSERT INTO filters (filter_name,source_data) VALUES (\"Create Email From Template\",\"emails\")";
$result=mysql_query($add_filter);
$filterid=mysql_insert_id();
$add_key='INSERT INTO filters (filter_id,name,value) values($filterid,"display_fields","name,content,notes")';
$result=mysql_query($add_key);
$add_key='INSERT INTO filters (filter_id,name,value) values($filterid,"title_text","Create New Email")';
$result=mysql_query($add_key);
$add_key='INSERT INTO filters (filter_id,name,value) values($filterid,"save_as_new_name_from_field","name")';
$result=mysql_query($add_key);
$add_key='INSERT INTO filters (filter_id,name,value) values($filterid,"submit_button_text","Save as new mail")';
$result=mysql_query($add_key);
$add_key='INSERT INTO filters (filter_id,name,value) values($filterid,"after_update","run_code")';
$result=mysql_query($add_key);
$add_key='INSERT INTO filters (filter_id,name,value) values($filterid,"after_update_run_code","load_page(\'administrator.php?action=list_table&t=pages&filter_id=173\')")';
$result=mysql_query($add_key);
echo "Mailing List has been installed successfully.";
?>
