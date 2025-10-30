<?php

print "<p>Inserting template:</p>";

// first add the template for displaying each news item as a row 
$sql="INSERT INTO templates (template_name,type,template_description,template) values(\"Display News Item Each Row\",\"Inner\",\"Row of each news item in a content page\",\"<p><strong>{=title}</strong></p><p>{=item_text}</p></a></p>\")";
$result=mysql_query($sql) or die ("Error adding template for Display News Item Each Row: " . mysql_error());
$news_template_each_row_id=mysql_insert_id(); // used in the outer template to point to where the each row template is
print "Added tempalte with id of " . $news_template_each_row_id."<p>";


// this is the template that embeds the each row template above
$sql="INSERT INTO templates (template_name,type,template_description,template) values(\"Display News Item\",\"Inner\",\"Displays a news item in a content page\",\"<p>LATEST NEWS:</p><p>{=templateid:$news_template_each_row_id}<a href=\\\"javascript:history.go(-1)\\\"></a></p>\")";
$result=mysql_query($sql) or die ("Error adding template for Display News Item: " . mysql_error());
$display_news_template_id=mysql_insert_id(); // this is used in the filter below
print "Added tempalte with id of " . $display_news_template_id."<p>";

// add filter for Display news
$sql="INSERT INTO filters (filter_name,system) values(\"Display News\",1)";
$result=mysql_query($sql) or die("Error: cant add Display News to filters: ".mysql_error());
$display_news_filter_id=mysql_insert_id();

$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($display_news_filter_id,\"field_equals\",\"\",\"id=\$_GET['news_item']\",1)";
$result=mysql_query($sql) or die("Error: cant add field_equals key to filter_keys: ".mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($display_news_filter_id,\"display_in_template\",\"\",\"$display_news_template_id\",1)";
$result=mysql_query($sql) or die("Error: cant add field_equals key to filter_keys: ".mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($display_news_filter_id,\"export\",\"\",\"html\",1)";
$result=mysql_query($sql) or die("Error: cant add field_equals key to filter_keys: ".mysql_error());

// now we add another template that uses the filter above
// list news item for edit ******** NEED TO GET edit_filter_id first **************


// Edit News Item Through CMS first as it only uses the content id. We can add the filter now, then the content to get the content id then the keys;
$sql = "INSERT INTO filters (filter_name) VALUES (\"Edit News Item Through CMS\")";
$result=mysql_query($sql) or die ("Cant add edit through cms filter to filters: " . mysql_error());
$edit_filter_id=mysql_insert_id();

$sql="INSERT INTO templates (template_name,type,template_description,template) values(\"Display News Item Each Row\",\"Inner\",\"List news items for edit in inline cms\",\"<p><strong>Please select a news item to edit:</strong></p><ul>{=SQL:select * from news order by id DESC}<li><a href=\\\"site.php?s=1&action=edit_table&t=news&rowid={=id}&filter_id=$edit_filter_id\\\">{=title}</a></li>{=end_sql}</ul>\"></a></p>\")"; 
$result=mysql_query($sql);
$list_news_for_edit_template_id=mysql_insert_id();

// now we have our 3 templates. 
// Add a content page
$sql="INSERT INTO content (title,value) VALUES(\"News Items\",\"<p><b>LATEST NEWS</b></p><ul>{=SQL:select id,title from news}<li><a href='site.php?s=1&action=list_table&t=news&news_item={=id}&filter_id=$display_news_filter_id'>{=title}</a><span style='background-color: #ffffff'> {=end_sql}</span></li></ul>\")";
$result=mysql_query($sql) or die ("Error adding News Items page to content: " . mysql_error());
$list_news_content_id=mysql_insert_id();

// now the keys for this filter..
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"textarea_rows\",\"item_text\",\"50\",1)";
$result = mysql_query($sql) or die ("Cant add textarea_rows key to filter: $edit_filter_id: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"textarea_cols\",\"item_text\",\"50\",1)";
$result = mysql_query($sql) or die ("Cant add textarea_cols key to filter: $edit_filter_id: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"title_text\",\"\",\"Edit News Item:\",1)";
$result = mysql_query($sql) or die ("Cant add title_text key to filter: $edit_filter_id: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"display_fields\",\"\",\"title,item_text\",1)";
$result = mysql_query($sql) or die ("Cant add display_fields key to filter: $edit_filter_id: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"names_on_separate_lines\",\"\",\"1\",1)";
$result = mysql_query($sql) or die ("Cant add names_on_separate_lines key to filter: $edit_filter_id: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"add_string_to_form_post_query\",\"\",\"s=1&content=\$_GET['content']\",1)";
$result = mysql_query($sql) or die ("Cant add add_string_to_form_post_query key to filter: $edit_filter_id: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"after_update\",\"\",\"display_content\",1)";
$result = mysql_query($sql) or die ("Cant add after_update key to filter: $edit_filter_id: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES ($edit_filter_id,\"after_update_display_content_id\",\"\",\"$list_news_content_id\",1)";
$result = mysql_query($sql) or die ("Cant add adfer_update_display_content_id key to filter: $edit_filter_id: " . mysql_error());

// Add item uses the previous filter as a parent, and we have the id for this now, so add it
$sql = "INSERT INTO filters (filter_name,parent_filter) VALUES (\"Add News Item Through CMS\",$edit_filter_id)";
$result=mysql_query($sql) or die ("Cant add \"Add News Item Through Cms\" filter to filters: " . mysql_error());
$add_filter_id=mysql_insert_id();
$sql = "INSERT INTO filter_keys (filter_id,name,value,system) VALUES ($add_filter_id,\"title_text\",\"Add News Item:\",1)";
$result=mysql_query($sql) or die ("Cant add title_text key to filter: $add_filter_id: " . mysql_error());

//Add the list_news_for_edit filter:
$sql="INSERT INTO filters (filter_name,system) values(\"List News For Edit\",1)";
$result=mysql_query($sql) or die("Error: cant add filter \"List News For Edit\"".mysql_error());
$list_news_for_edit_filterid=mysql_insert_id();
// keys for this filter
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($list_news_for_edit_filterid,\"display_fields\",\"\",\"title\",1)";
$result=mysql_query($sql) or die("Error: cant add display_fields key to filter_keys: ".mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($list_news_for_edit_filterid,\"export\",\"\",\"html\",1)";
$result=mysql_query($sql) or die("Error: cant add export key to filter for $list_news_for_edit_filterid: ".mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($list_news_for_edit_filterid,\"edit_row_link\",\"\",\"site.php?s=1&action=edit_table&t=news&edit_type=edit_single&rowid={=id}&dbf_edi=1&filter_id=$edit_filter_id&content=$list_news_content_id\",1)";
$result=mysql_query($sql) or die("Error: cant add edit_row_link key to filter for $list_news_for_edit_filterid: ".mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,value,system) VALUES($list_news_for_edit_filterid,\"title_text\",\"Edit News Items\",1)";
$result=mysql_query($sql) or die("Error: cant add title_text key to filter $list_news_for_edit_filterid: " . mysql_error());
$sql = "INSERT INTO filter_keys (filter_id,name,value,system) VALUES ($list_news_for_edit_filterid,\"add_row_link\",\"site.php?s=1&action=edit_table&t=news&edit_type=add_row&filter_id=$add_filter_id&content=$list_news_content_id\",1)"; 
$result=mysql_query($sql) or die("Error: cant add add_row_link key to filter: $list_news_for_edit_filterid: " . mysql_error());

// two records to add links to the inline cms
$sql="INSERT INTO cms (content_id,cms_variable,value,system) VALUES($list_news_content_id,\"display_extra_link\",\"site.php?action=list_table&t=news&filter_id=$list_news_for_edit_filter_id&dbf_edi=1&dbf_ido=1&dbf_add=1&content=$list_news_content_id\",1)";
$result=mysql_query($sql) or die("Error adding cms record for display_extra_link: " . mysql_error());
$sql="INSERT INTO cms (content_id,cms_variable,value,system) VALUES($list_news_content_id,\"display_extra_text\",\"Edit News Items\",1)";
$result=mysql_query($sql) or die("Error adding cms record for display_extra_text: " . mysql_error());

// finally register the plugin with the system 
$add_plugin_sql="INSERT INTO plugins (plugin_name,description,database_tables,plugin_directory,system) values(\"News Bulletin\",\"A basic news bulletin plugin that lists items from a news table on one page and displays the content of them on another. Integrates into the client side cms.\",\"news\",\"news_bulletin\",1)";
$add_plugin_result=mysql_query($add_plugin_sql) or die("ERROR REGISTERING PLUGIN: " . mysql_error());


print "<p>Added content, templates, filters and cms records.";

?>
