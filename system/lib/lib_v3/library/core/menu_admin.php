<?php 

/*
 * File: menu_admin.php
 * Meta: extremely old - from first version
*/

/*
 * Function -  menu_admin_top
*/
function menu_admin_top(){
	ob_start();
	global $page;
	if (!$page->value("useAjax")){
		global $col1_open;
		if (!$col1_open){ open_col1();}
		print "<h2 class=\"menu_header\">" . $CONFIG['admin_page_title']; 
		if ($CONFIG['admin_page_title_description']){
			print "<br /><font color='gray'><sub>" . $CONFIG['admin_page_title_description'] . "</sub></font>";
		}
		print "</h2>";
	}

	global $user;
	if ($_COOKIE['login'] && $user->value("id")) {
		if (!$page->value("useAjax")){
			print "<div id=\"menuspanleft\">";
			print "<b>Logged in as: </b>";
			print $user->value("full_name");
			if ($user->value("type")=="administrator"){
				$account_type_text="site administrator";
			} elseif ($user->value("type")=="superadmin"){
				$account_type_text="superadmin";
			} elseif ($user->value("type")=="master"){
				$account_type_text="master account";
			}
			print "<br /><span style=\"font-size:10px\">($account_type_text)</span>";
			print "</div><!-- close menuspan left //-->";
			close_col();
	 
			$floating_menu_types_array=explode(",",$CONFIG['system_menu']);
			foreach ($floating_menu_types_array as $float_menu_usertype){
				if ($user->value("type")==$float_menu_usertype){
					$display_floating_menu=1;
				}
			}
			if ($display_floating_menu){
				print_graphic_menu("mobile");
			} 
		}
	} else { // not_logged_in
		display_admin_homepage();
		close_col();
		print "</div>";
		print "</div>";
		print "</body>";
		print "</html>";
	ob_end_clean();
	return "";
	}
	$contents = ob_get_contents();
	ob_end_clean();
	return $contents;
	
}

/*
 * Function print_graphic_menu
*/
function print_graphic_menu($menu_type){

	if ($menu_type=="mobile"){
		print_mobile_menu();
		return;
	} else if ($menu_type=="side"){
		echo '<div id="graphic_menu" style="position:relative; top:95px; left:-4px;">';
		print "<table width=\"200\">";
	} else {
		echo '<div id="graphic_menu" style="position:relative;">';
		print "<table><tr><td valign=\"top\"><table width=\"400\">";
		print "<tr><td></td><td><b>Standard Options</b><br></td></tr>";
	}	
?>

<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=templates&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/page_white_office.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=templates&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>">Template Manager</a><span class="helptip">Manage templates for web sites and displaying data</span></td></tr>
<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=content&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_row_insert.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=content&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1');?>">Content Manager</a><span class="helptip">Add and edit content</span></td></tr>
<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=style_sheets&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/color_swatch.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=style_sheets&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1'); ?>">Style Manager</a><span class="helptip">Edit Style sheets</span></td></tr>
<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=filters&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_filter.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=filters&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1');?>">Filters &amp; Interfaces</a><span class="helptip">Filter your data by values, ordering, and into forms and templates</span></td></tr>
<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=menu&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/text_list_bullets.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=menu&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1');?>">Menu Manager</a><span class="helptip">Build hierarchial menus, and use style sheets to turn them into dynamic menus</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=sysListTables'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_gear.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=sysListTables'); ?>">Table Manager</a><span class="helptip">Manage your tables - view, add, search, edit and delete data</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=user&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_filter=1&dbf_sort=1&dbf_rpp_sel=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/user.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=user&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_filter=1&dbf_sort=1&dbf_rpp_sel=1'); ?>">User Manager</a><span class="helptip">Add, Edit and Delete users, or expand the users table</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=file_manager'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/folder_page_white.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=file_manager'); ?>">File Manager</a><span class="helptip">Manage Files And Directories</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=tools_frpmt'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/wrench_orange.png" /></a></td><td><a href="">Tools</a><span class="helptip">A collection of useful tools for using on your data</span></td></tr>
<?php
if ($menu_type!="side"){
	print "</td></tr></table></td><td valign=\"top\"><table width=\"400px\"><tr><td valign=\"top\">";
	print "<tr><td valign=\"top\"></td><td><b>Advanced</b><br></td></tr>\n";
} else {
	print " <tr><td valign=\"top\"><hr size=1 /></td><td><hr size=1 /><br /><b>Advanced</b><br></td></tr>";
}
?>
<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=table_relations'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_relationship.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=table_relations'); ?>">Table Relationships</a><span class="helptip">Describe the relationships between tables to the system to allow advanced logical interfaces and queries to be built.</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=query_builder'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_question_mark.png" /></a></td><td><a href="">Query Builder</a><span class="helptip">Create interfaces for your data based on queries across multiple tables. Generate lists, forms, reports and more.</span></td></tr>
<tr><td valign="top"><hr size=1 /></td><td><hr size=1 /><br /><b>System</b><br></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=configuration&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_filter=1&dbf_sort_dir=1&dbf_rpp_sel=1&dbf_sort=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/cog_edit.png" /></a></td><td><a href="">Global Configuration</a><span class="helptip">Edit the global configuration of the entire system, configure how things work.</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=permissions&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/application_key.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=permissions&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1');?>">Security</a><span class="helptip">Set permissions on users and tables - secure your application.</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=sysIo'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/page_refresh.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=sysIo'); ?>">Import / Export</a><span class="helptip">Import and export data, databases, tables, software configurations and software upgrades. So powerful you can rewrite the entire system functionality from here.</span></td></tr>
<tr><td><a href="<?php echo get_link('administrator.php?action=process_log_out'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/disconnect.png" /></a></td><td><a href="">Log Out</a></td></tr>
</tr>
</table>
<?php
if ($menu_type!="side"){
	print "</td></tr></table>";
}

?>
</div><!-- finished printing graphic menu //-->
<?php
}

/*
 * Function print_mobile_menu
*/
function print_mobile_menu(){
	if ($_SESSION['devmenutop']) { $devmenutop=$_SESSION['devmenutop']; } else { $devmenutop=0; }
	if ($_SESSION['devmenuleft']) { $devmenuleft=$_SESSION['devmenuleft']; } else { $devmenuleft=0; }
	?>
	<div id="frame" style="position:absolute;left:<?php echo $devmenuleft;?>;top:<?php echo $devmenutop;?>;width:190px;border:0px outset #eeeeee;background:transparent;visibility:hidden;"></div>
	<div id="titlebar" style="position:absolute;border:none;background:#4455aa;overflow:hidden;visibility:hidden;"><span style="position:relative;left:2px;top:2px;padding:0px;color:white;font-weight:bold;font-size:11px;font-family:Verdana,Geneva,sans-serif;">&nbsp;Developer Menu</span></div>
	<div id="clientarea" style="position:absolute;border:0px inset #cccccc;background:white;overflow:auto;visibility:hidden;">
	     
	<table width="170">
	<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=templates&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1');?>"><img src="<?php echo SYSIMGPATH;?>/icons/page_white_office.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=templates&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1');?>">Template Manager</a><span class="helptip">Manage templates for web sites and displaying data</span></td></tr>
	<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=content&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_row_insert.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=content&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1');?>">Content Manager</a><span class="helptip">Add and edit content</span></td></tr>

	<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=style_sheets&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/color_swatch.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=style_sheets&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1'); ?>">Style Manager</a><span class="helptip">Edit Style sheets</span></td></tr>
	<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=filters&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_filter.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=filters&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>">Filters &amp; Interfaces</a><span class="helptip">Filter your data by values, ordering, and into forms and templates</span></td></tr>
	<tr><td valign="middle"><a href="<?php echo get_link('administrator.php?action=list_table&t=menu&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/text_list_bullets.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=menu&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>">Menu Manager</a><span class="helptip">Build hierarchial menus, and use style sheets to turn them into dynamic menus</span></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=sysListTables'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_gear.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=sysListTables'); ?>">Table Manager</a><span class="helptip">Manage your tables - view, add, search, edit and delete data</span></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=user&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_filter=1&dbf_sort=1&dbf_rpp_sel=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/user.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=list_table&t=user');?>">User Manager</a><span class="helptip">Add, Edit and Delete users, or expand the users table</span></td></tr>

	<tr><td><a href="<?php echo get_link('administrator.php?action=file_manager'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/folder_page_white.png" /></a></td><td><a href="<?php echo get_link('administrator.php?action=file_manager'); ?>">File Manager</a><span class="helptip">Manage Files And Directories</span></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=tools_frpmt'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/wrench_orange.png" /></a></td><td><a href="">Tools</a><span class="helptip">A collection of useful tools for using on your data</span></td></tr>
	<tr><td valign="top"><hr size=1 /></td><td><hr size=1 /><br /><b>Advanced</b><br></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=table_relations'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_relationship.png" /></a></td><td><a href="">Table Relationships</a><span class="helptip">Describe the relationships between tables to the system to allow advanced logical interfaces and queries to be built.</span></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=query_builder'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/table_question_mark.png" /></a></td><td><a href="">Query Builder</a><span class="helptip">Create interfaces for your data based on queries across multiple tables. Generate lists, forms, reports and more.</span></td></tr>
	<tr><td valign="top"><hr size=1 /></td><td><hr size=1 /><br /><b>System</b><br></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=configuration&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_filter=1&dbf_sort_dir=1&dbf_rpp_sel=1&dbf_sort=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/cog_edit.png" /></a></td><td><a href="">Global Configuration</a><span class="helptip">Edit the global configuration of the entire system, configure how things work.</span></td></tr>

	<tr><td><a href="<?php echo get_link('administrator.php?action=list_table&t=permissions&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/application_key.png" /></a></td><td><a href="">Security</a><span class="helptip">Set permissions on users and tables - secure your application.</span></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=sysIo'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/page_refresh.png" /></a></td><td><a href="">Import / Export</a><span class="helptip">Import and export data, databases, tables, software configurations and software upgrades. So powerful you can rewrite the entire system functionality from here.</span></td></tr>
	<tr><td><a href="<?php echo get_link('administrator.php?action=process_log_out'); ?>"><img src="<?php echo SYSIMGPATH;?>/icons/disconnect.png" /></a></td><td><a href="">Log Out</a></td></tr>
	</tr>
	</table>
	  </div>
	  <img name="resizebutton" src="<?php echo SYSIMGPATH;?>/dragdrop/button_up_outset.gif" width="16" height="14" alt="" style="visibility:hidden;">
	<?php
}
?>
