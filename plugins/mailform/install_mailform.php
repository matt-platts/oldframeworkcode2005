<?

// first, can we open the file?
$writefile="custom/append_files/contact_form.html";
$fh = fopen($writefile,"w") or die ("Unable to open $writefile for writing. Check the permissions on the directories this script is to be placed in are writeable!");


$readfile="plugins/mailform/install_data/mailform_form_html.html";
$h = fopen($readfile,"r") or die("Cant read file");
$data=fread($h,filesize($readfile));
fclose($h);

fwrite($fh,$data);
fclose($fh);

$add_plugin_sql="INSERT INTO plugins (plugin_name,description,database_tables,plugin_directory,system) values(\"Mailform\",\"A simple form to mail application, where a submitted form is sent to an email address.\",\"\",\"mailform\",1)";
$add_plugin_result=mysql_query($add_plugin_sql) or die("ERROR REGISTERING PLUGIN: " . mysql_error());

print "<p><font color=\"green\"><b>Mailform has been successfully installed. In order to add it to a content page, simply add \"contact_form.html\" to the append_file field in any contact item and it will be included at the bottom.</b></font></p>";


?>


