<?php

// Runs all the sql to add extra content, cms pages and filters for the photo_gallery plugin
// Note that everything needs to be added in the correct order as mysql_insert_id()s are used later on. Eg, the filter contains the content ids

// First add 3 content items. These need to be added in reverse order of navigation (eg. Pic then gallery_items then list galleries) as we use the insert ids for subsequent content items
$sql3 = "INSERT INTO content(title,value) VALUES(\"Display Image\",\"Photo Gallery<br /><br /><div align=\\\"center\\\">{=SQL:select * from photo_gallery_items where id={=image_id}}<img src=\\\"images/gallery/{=image}\\\" border=\\\"0\\\" /><br /><strong>{=title}</strong>{=endsql}<br /><br />&lt; <a href=\\\"Javascript:history.go(-1)\\\">Return To List</a></div><br /><br /><a href=\\\"Javascript:history.go(-1)\\\"></a>\")";
$result3=mysql_query($sql3);
$display_image_id=mysql_insert_id();

$sql2 = "INSERT INTO content(title,value) VALUES(\"Display Gallery\",\"<p>PHOTO GALLERY: {=SQL:select name from photo_gallery WHERE id={=gallery_id}}{=name}{=endsql}</p><p>&nbsp;</p><table border=0 width=231 style=\\\"height: 16px\\\"><tbody><tr>{=SQL:select id,title,image from photo_gallery_items WHERE gallery_id={=gallery_id} AND active=1}<td align=\\\"center\\\"><table border=0 width=75 style=\\\"height: 52px\\\"><tbody><tr><td><a href=\\\"site.php?s=1&amp;content=$display_image_id&amp;image_id={=id}\\\"><img src=\\\"images/gallery/{=image}\\\" border=0 height=100/></a></td></tr><tr><td style=\\\"font-size: 10px; text-align: center\\\">{=title}</td></tr></tbody></table></td>{=each:3}</tr><tr>{=endeach}{=endSQL}</tr></tbody></table><p><br /><a href=\\\"Javascript:history.go(-1)\\\"></a></p>\")";
$result2=mysql_query($sql2);
$each_gallery_id=mysql_insert_id();

$sql1 = "INSERT INTO content(title,value) VALUES(\"Display Gallery\",\"PHOTO GALLERY<p>Please select a section below:</p><ul>{=SQL:select * from photo_gallery}<li><a href=\\\"site.php?s=1&amp;content=$each_gallery_id&amp;gallery_id={=id}\\\">{=name}</a></li>{=end_sql}</ul>\")";
$result1=mysql_query($sql1);
$gallery_list_id=mysql_insert_id();

// add 2 lines to the CMS to allow editing of the pages inline. First inserts the link, second inserts the text for the link
$cms_sql = "INSERT INTO cms(content_id,cms_variable,value) VALUES($gallery_list_id,\"display_extra_link\",\"site.php?s=1&action=list_table&t=photo_gallery&content=$gallery_list_id\")";
$cms_result1=mysql_query($cms_sql);

$cms_sql2 = "INSERT INTO cms(content_id,cms_variable,value) VALUES($gallery_list_id,\"display_extra_text\",\"Edit Photo Gallery\")";
$cms_result2=mysql_query($cms_sql2);

// add the table relation so that the system knows that photo_gallery is related to photo_gallery_items as a one to many relationship
$relation_sql="INSERT INTO table_relations(table_1,field_in_table_1,table_2,field_in_table_2,relationship,system) VALUES (\"photo_gallery\",\"id\",\"photo_gallery_items\",\"gallery_id\",\"one to many\",1)";
$relation_result=mysql_query($relation_sql);
$relation_id=mysql_insert_id();

// now add the filters used to add and edit items in the galleries. As a file upload is used, there are a fair few keys
$first_filter_sql="INSERT INTO filters(filter_name,system) values(\"Edit Photo Gallery\",1)";
$first_filter_result=mysql_query($first_filter_sql);
$first_filter_id=mysql_insert_id();

// here enter all the filter keys from filter 29 in capital! We should be able to use an array to do this....
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"export\",\"\",\"html\",1)";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"title_text\",\"\",\"Edit Photo Gallery\",1)";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"relations_link_url\",\"\",\"site.php?action=list_table&t=photo_gallery_items&dbf_ido=1&dbf_edi=1&dbf_add=1&relation_iid=$relation_id&relation_key=1&filter_id=$first_filter_id\",\"1\")"; 
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"textarea_rows\",\"description\",\"10\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"textarea_cols\",\"description\",\"45\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"names_on_separate_lines\",\"\",\"1\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"add_row_link\",\"\",\"site.php?s=1&action=edit_table&t=photo_gallery_items&edit_type=add_row&filter_id=$first_filter_id&content=$gallery_list_id\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"edit_row_link\",\"\",\"site.php?s=1&action=edit_table&t=photo_gallery_items&edit_type=edit_single&rowid={=id}&dbf_edi=1&dbf_edi=1&filter_id=29&content=28\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"add_string_to_form_post_query\",\"\",\"s=1&content=$gallery_list_id&filter_id=$first_filter_id\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"after_update\",\"\",\"continue\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"delete_row_link\",\"\",\"site.php?s=1&action=delete_row_from_table&t=photo_gallery_items&rowid={=id}&preUrl={=coded_query_string}\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"file_upload_display_current\",\"image\",\"display_current\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"file_upload_inline_image_preview\",\"\",\"x=50,y=50\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"file_upload_current_link\",\"image\",\"link_to_current\",\"\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"field_config\",\"active\",\"select list\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"field_type\",\"active\",\"select\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());
$sql="INSERT INTO filter_keys (filter_id,name,field,value,system) VALUES($first_filter_id,\"select_value_list\",\"active\",\"1;;Yes,23;;No\",\"1\")";
$result=mysql_query($sql) or die("ERROR: " . mysql_error());

// now add the filter and filter keys for the INLINE cms. This filter is a child of the previous filter which needs to be stated as a parent in the filter itself
$filter_sql = "INSERT INTO filters(filter_name,system) values(\"Edit Gallery In Inline CMS\",1)";
$filter_result=mysql_query($filter_sql);
$second_filter_id=mysql_insert_id();

$filter_key_sql="INSERT INTO filter_keys(filter_id,name,field,value,system) VALUES($second_filter_id,\"export\",\"\",\"html\",1)";
$filter_key_sql2="INSERT INTO filter_keys(filter_id,name,field,value,system) VALUES($second_filter_id,\"relations_link_url\",\"site.php?s=1&action=list_table&t=photo_gallery&dbf_ido=1&dbf_edi=1&dbf_add=1&relation_id=$relation_id&relation_key=1&filter_id=$first_filter_id"; // BETTER LOOK AT WHAT THIS RELATION KEY IS! 

$add_plugin_sql="INSERT INTO plugins (plugin_name,description,database_tables,plugin_directory,system) values(\"Photo Gallery\",\"A multi tier photo gallery where a number of galleries can be created and browsed individually\",\"photo_gallery,photo_gallery_items\",\"photo_gallery\",1)";
$add_plugin_result=mysql_query($add_plugin_sql) or die("ERROR REGISTERING PLUGIN: " . mysql_error());

print "<p>Written database records to Content and CMS tables.";

?>
