<?php

// 12.07.2010 - should only be the last 2 url funtions that are now required from this entire file.. other functions have moved to the page class

function url_generator_front(){
	global $db;
	if (!$col2_open){open_col2;}
	print "<p class=\"admin_header\">Url Generator</p>";
	print "<p>You can use this section to generate a URL for basic content pages in templates. You can then copy this URL to use as links in your content pages or in menus or templates.</p><br /><hr size=1><b>";
	$sitesql="SELECT * from web_sites";
	$siteres=$db->query($sitesql);
	if ($db->num_rows($siteres)>1){
		$multisites=1;
	}
	$contentsql="SELECT * from content";
	$templatesql="SELECT * from templates WHERE type = \"master\"";
	$contentres=$db->query($contentsql);
	$templateres=$db->query($templatesql) or die ("Error" . $db->db_error());

	print "<form name=\"urlform\" action=\"".$_SERVER['PHP_SELF']."?action=generate_url\">";
	print "<table><tr><td>Display Content: </td><td><select name=\"content_id\">";
	while ($cdata=$db->fetch_array($contentres)){
		print "<option value=\"" . $cdata['id'] . "\">" . $cdata['title'] . "</option>";
	}
	print "</select></td></tr>";

	print "<tr><td>in template: </td><td><select name=\"template_id\">";
	while ($tdata=$db->fetch_array($templateres)){
		print "<option value=\"" . $tdata['id'] . "\">" . $tdata['template_name'] . "</option>";
	}
	print "</select></td></tr>";

	if ($multisites or !$multisites){
		$siteres=$db->query($sitesql) or die ("Error 91882UY");
		print "<tr><td>for site: </td><td><select name=\"site_id\">";
		while ($sdata=$db->fetch_array($siteres)){
			print "RUNNING";
			print "<option value=\"" . $sdata['id'] . "\">" . $sdata['name'] . "</option>";
		}
		print "</select></td></tr>";

	}
	print "<tr><td></td><td><input type=\"button\" name=\"calculate\" value=\"Get Url\" onClick=\"generate_url()\"></td></tr></table>";
	print "<p><hr size='1'><div id=\"urldiv\"></div>";
	close_col();	
}

function generate_url(){

	$contentid=$_REQUEST['content_id'];
	$templateid=$_REQUEST['template_id'];
	$siteid=$_REQUEST['site_id'];

	$sql = "SELECT * from content WHERE id = $contentid and default_template = $templateid";
	$res=$db->query($sql) or die ("error" . $db->db_error());
	while ($h=$db->fetch_array($res)){
		if ($h['html_page_name']){ $htaccess_url = $h['html_page_name']; }
	}
	$url="site.php?s=$siteid&content=$contentid&mt=$templateid";
	if ($htaccess_url && $siteid==1){
		$url .= "<p>Alternatively, you can use: <b>$htaccess_url</b>, which is mapped to this url.</p>";
	}
	print "<b>Your URL is: </b>$url";
	exit;

}

/* 
 * Function: generate_form_template
 * Param: table (string) - table you are generating a template for
 * Meta: generates a default template for a form using an HTML table and places it into the templates table for you to edit
*/
function generate_form_template($table){

	if (!$_REQUEST['dbf_form_type']){
		print "<p class=\"admin_header\">Generate Form Template</p>";
		print "<p>You are about to generate a form template for the following table or query: <b>$table</b></p>";
		print "<form action=\"".$_SERVER['PHP_SELF']."\" method=\"get\"><fieldset><legend>This template is for: (please select)</legend>\n";
		print "<input type=\"hidden\" name=\"action\" value=\"generate_form_template\">\n";
		print "<input type=\"hidden\" name=\"table\" value=\"$table\">\n";
		print "<ul style=\"list-style-type:none\">";
		print "<li><input type=\"radio\" name=\"dbf_form_type\" value=\"site\">A form to be used on the <b>front end</b> web site which should be placed in the templates section</li>";
		print "<li><input type=\"radio\" name=\"dbf_form_type\" value=\"admin\">A form to be used on the <b>administrator</b> side web site which should be placed in the admin templates section</li>";
		print "</ul></fieldset><input type=\"submit\" value=\"continue\"></form>";
		
	} else {
		$fields=list_fields_in_table($table);
		if ($_REQUEST['dbf_form_type']=="site"){
			$template="<h1>Add / Edit $table</h1>\n";
		} else {
			$template="<p class=\"admin_header\">Add / Edit $table</p>\n";
		}
		$template .= "<div id=\"dbf_form_wrapper\">{=form_header}\n<table>\n";
		foreach ($fields as $field){
			$template .= "<tr><td valign=\"top\" align=\"right\" style=\"font-weight:bold\">{=".$field.":fieldname}</td><td>{=".$field.":input_field}</td></tr>";
		}
		$template .= "</table>\n{=submit_button}\n{=form_footer}</div>";
		$template=$db->db_escape($template);

		if ($_REQUEST['dbf_form_type']=="site"){
			$template_table="templates";
		} else {
			$template_table="admin_templates";
		}
		$insert_template_sql="INSERT INTO $template_table (template_name,template) values(\"Form template for $table\",\"$template\")";
		print $insert_template_sql;
		global $db;
		$res=$db->query($insert_template_sql) or format_error("Cannot add form template",1);
		$templateid=$db->last_insert_id();
		print "<p class=\"admin_header\">Generate form template for $table</p>";
		print "<p>Your form template has been added with an id of $templateid. You can now modify this template as you wish. It is recommended that at the least you now update this record with a name and description.</p>";
		print "<p>To edit this template now please <a href=\"".get_link("administrator.php?action=edit_table&t=templates&edit_type=edit_single&rowid=$templateid")."\">Click Here</a></p>";
	}
}

?>
