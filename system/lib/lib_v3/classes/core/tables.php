<?php

/* 
 * CLASS: tables
 * Functions specific to database tables
 * Meta: Needs to be split into two files - tables and table. 
 *       -> Table for table specific functions requiring a table name in the constructor, tables for dealing with general functionality related to tables (plural)
*/
class tables {

	/* 
	 * Function: __construct
	 * Param: $table (string) - actual name of table in db
	 */
	function __construct($table=null){
		$this->tablename=$table;
	}

        function value($of){
                return $this->$of;
        }

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }


	/*
	 * Function: list_fields_in_table
	 * Param: $table (string) - actual name of table in db
	 * Returns: Array - regular array as a list of the fields in the table, in the default db order
	 * Meta: There is an argument for having this as a static function elsewhere to call it on any table..
	*/
	public function list_fields_in_table($table){
		global $db;
		$return_fields=array();
		$sql = "desc " . $table;
		$result=$db->query($sql);
		while ($row = $db->fetch_array($result)){
			array_push ($return_fields, $row['Field']);
		}
		sort($return_fields);
		return $return_fields;
	}

	/* 
	 * Function is_field_in_table 
	 * Meta: There is an argument for having this as a static function elsewhere to call it on any table..
	*/
	public function is_field_in_table($table,$field){
		global $db;
		$found= false;
		$fields_list=$this->list_fields_in_table($table);
		foreach ($fields_list as $table_field){
			if ($table_field==$field){
				$found = true;
				break;
			}	
		}
		return $found;
	}

	/*
	 * Function: get_primary_key
	 * Return: String - primary key field of the table
	*/
	public function get_primary_key($table){
		global $db;
		if (!$table){ format_error("No table has been sent to gpk function. Program terminating"); exit;}
		$pk_sql="DESC " . $table;
		$result=$db->query($pk_sql);
		if ($db->num_rows($result)==0){die (format_error("The following table does not exist: $table")); }
		while ($desc_rows=$db->fetch_array($result)){
			if ($desc_rows['Key']=="PRI"){
				$pk=$desc_rows['Field'];
			}
		}
		if (!$pk){die(format_error("No primary key on table $table. All tables are required to have a primary key as a unique identifier."));}
		return $pk;
	}


	/* 
	 * Function one_to_many_relationship
	 * Param $table (string) 
	 * Return Mixed - Regular array of associative arrays containing information about the relationship
	 */
	public function one_to_many_relationship ($table){
		$one_to_many_relationships=array();
		$table_relations = $this->get_table_relations($table);
		foreach ($table_relations as $table_relation){
			if ($table_relation['relationship']=="one to many"){
				$relation_array = array();
				$relation_array['table_name']=$table_relation['table'];
				$relation_array['table_field']=$table_relation['table_field'];
				$relation_array['table_field2']=$table_relation['table_field_2'];
				$relation_array['relation_id']=$table_relation['relation_id'];
				$relation_array['system_graphic']=$table_relation['system_graphic'];
				$relation_array['hide_from_system_lists']=$table_relation['hide_from_system_lists'];
				array_push($one_to_many_relationships,$relation_array);
			} else {
			}
		}
		return $one_to_many_relationships;
	}

	/* 
	 * Function: get_table_relations
	 * Return: Array (Associative array keyed by an incrementor variable, and various keys describing the relation)
	 * Meta: Gets the table relations from the table_relations system table.
	 */
	public function get_table_relations($table){
		global $db;
		$table_relations_sql = "SELECT * from table_relations where table_1 = '" . $table. "'";
		$table_relations_result=$db->query($table_relations_sql);
		$table_relations=array();
		$table_relations_count=0;
		while ($table_relations_rows=$db->fetch_array($table_relations_result)){
			$table_relations[$table_relations_count]['table_field']=$table_relations_rows['field_in_table_1'];
			$table_relations[$table_relations_count]['table']=$table_relations_rows['table_2'];
			$table_relations[$table_relations_count]['table_field_2']=$table_relations_rows['field_in_table_2'];
			$table_relations[$table_relations_count]['relationship']=$table_relations_rows['relationship'];
			$table_relations[$table_relations_count]['relation_id']=$table_relations_rows['id'];
			$table_relations[$table_relations_count]['system_graphic']=$table_relations_rows['system_graphic'];
			$table_relations[$table_relations_count]['hide_from_system_lists']=$table_relations_rows['hide_from_system_lists'];
			$table_relations_count++;
		}
		return $table_relations;
	}

	/*
 	 * Function: sysTableInfo
	 * Param: $table string - actual name of table in database
	 */
	public function sysTableInfo($table){
		$tabledata['table_name']=$table;
		$metadata=$this->show_metadata_for($table);
		$tabledata['meta_data_full']=$metadata['body'];
		global $db;
		$content=hash_into_admin_template_by_key($tabledata,"table_info");
		return $content;
	}

	/*
	 * Function: show_metadat_for
	 * Param: $table (string) - actual name of table in database
	 * Returns: Associative array of strings containing various parts of html content for the page
	 */
	public function show_metadata_for($table){
		global $db;
		$ret=array();
		$ret['header'] = admin_header("Meta data for table: $table");
		$ret['body'] = "<table class=\"bordered_table\"><tbody>";
		$sql="show table status where name like \"$table\"";
		$res=$db->query($sql) or format_error("Can't display meta data on table $table",1,0,$db->db_error());
		while ($h=$db->fetch_array($res)){;
			foreach ($h as $hkey=>$hval){
				$ret['body'] .= "<tr><td style=\"text-align:right; font-weight:bold;\"><b>$hkey:</b></td><td>" . $h[$hkey] . "</td></tr>";
			}
		}
		$ret['body'] .= "</tbody></table>";
		$ret['footer'] .= $this->table_action_footer($table);
		return $ret;
	}

	/* 
	 * Function: table_action_footer
	 * Param: $table ( real name of the table in db)
	 * Returns: general footer links from other table edit screens
	 */
	public function table_action_footer($table){

		if (!stristr($_SERVER['SCRIPT_FILENAME'],"mui-administrator")){
		$ret = "<p style=\"text-align:left\">Go To: <ul style=\"text-align:left; display:block; list-style-type:square\">\n";
		$ret .= "<li><a href=\"".$_SERVER['PHP_SELF']."?action=sysListTables\">Table Manager</a></li>";
		$ret .= "<li><a href=\"".$_SERVER['PHP_SELF']."?action=list_table&t=$table\">View Table Data</a></li>";
		$ret .= "</ul></p>";
		} else {
			$ret="";
		}
		return $ret;
	}


	/* 
	 * Function: list_tables_basic
	 * Param: $tabletypes string - (all, system, application, null) - note that null returns all tables by default
	 * Returns: array (Regular array containing a list of tables in the system which match the input criterea). 
	*/
	public function list_tables_basic($tabletypes = null){
		global $CONFIG;
		global $db;
		$database_name=$db->dbname();
		$row_header = "Tables_in_" . $database_name;
		$sql="show tables";
		$result=$db->query($sql) or die($db->db_error()); 
		$tables_array=array();
		while ($each_table_row=$db->fetch_array($result)){
			$table_type=get_table_type($each_table_row[$row_header]);
			if ($tabletypes=="application" && $table_type=="system"){
				continue;	
			}
			if ($CONFIG['readable_table_names']){
				$table_name_readable=$each_table_row[$row_header];
				$table_name_readable=str_replace("_"," ",$table_name_readable);
				$table_name_readable=ucfirst($table_name_readable);
				array_push ($tables_array,$each_table_row[$row_header].";;".$table_name_readable);
			} else {
				$table_name_readable=$each_table_row[$row_header];
				array_push ($tables_array,$table_name_readable);
			}
		}
		if ($tabletypes=="include_queries"){
			$sql = "SELECT query_name from queries ORDER BY query_name";
			$res=$db->query($sql) or format_error("Cannot list queries");
			while ($each_query_row=$db->fetch_array($res)){
				array_push($tables_array,"QUERY:".$each_query_row['query_name']);
			}
		}
		return $tables_array;	
	}

	/*
	 * Function: list_tables
	 * Param: tabletypes (all, system, application)
	 * Returns: array (list of tables and meta data - associative array format)
	*/
	public function list_tables($tabletypes){
		global $CONFIG;
		global $db;
		$database_name=$db->dbname();
		$row_header = "Tables_in_" . $database_name;
		$sql="show tables";
		$result=$db->query($sql); 
		$tables_array=array();
		$tables_with_permissions_array=array();
		// filter the list by permissions
		global $user;
		if ($user->value("id")){
			$perm_sql="SELECT * FROM permissions WHERE ((setting=\"sysUserType\" AND value = \"".$user->value("type") . "\") OR ((setting = \"\" OR setting IS NULL) AND value=\"1\"))";
			$perm_rv=$db->query($perm_sql);
			while ($perm_h=$db->fetch_array($perm_rv)){
				array_push($tables_with_permissions_array,$perm_h['tablename']);
			}

		}
		while ($each_table_row=$db->fetch_array($result)){
			//print "RH is " . $each_table_row[$row_header];
			if ($user->value("type")=="master" || in_array($each_table_row[$row_header],$tables_with_permissions_array)){
				array_push ($tables_array,$each_table_row[$row_header]);
			}
		}
		//$tables_array=generate_table_list(0,0,0);
		$return_array=array();

		// Get system tables
		$meta_table_sql="SELECT table_meta.id,table_meta.table_name,table_meta.notes,configuration_sections.section_name,table_meta.system from table_meta INNER JOIN configuration_sections on table_meta.configuration_section = configuration_sections.id";
		$meta_table_result=$db->query($meta_table_sql);
		$system_tables=array();
		$application_tables=array();
		$config_sections=array();
		$table_notes=array();
		while ($all_meta_table_rows=$db->fetch_array($meta_table_result)){
			if ($all_meta_table_rows['system']==1){
				array_push ($system_tables,$all_meta_table_rows['table_name']);
			} else {
				array_push ($application_tables,$all_meta_table_rows['table_name']);
			}
			$config_sections[$all_meta_table_rows['table_name']]=$all_meta_table_rows['section_name'];
			$table_notes[$all_meta_table_rows['table_name']]=$all_meta_table_rows['notes'];
		}

		$return_array=array();
		foreach ($tables_array as $table_line){
			if ($CONFIG['readable_table_names']){
				$table_name_readable=$table_line;
				$table_name_readable=str_replace("_"," ",$table_name_readable);
				$table_name_readable=ucfirst($table_name_readable);
			} else {
				$table_name_readable=$table_line;
			}
			
			$is_system_table=0;
			foreach ($system_tables as $system_table){
				if ($system_table==$table_line){
					$is_system_table=1;
				}
			}	

			if ($is_system_table && $tabletypes != "application"){
				$return_array[$table_line]['name']=$table_name_readable;
				$return_array[$table_line]['real_name']=$table_line;
				$return_array[$table_line]['system']="1";
				$return_array[$table_line]['config_section']=$config_sections[$table_line];
				$return_array[$table_line]['notes']=$table_notes[$table_line];
			} else if (!$is_system_table && $tabletypes == "application"){
				$return_array[$table_line]['name']=$table_name_readable;
				$return_array[$table_line]['real_name']=$table_line;
				$return_array[$table_line]['system']="0";
				$return_array[$table_line]['config_section']=$config_sections[$table_line];
				$return_array[$table_line]['notes']=$table_notes[$table_line];
			} else if (!$tabletypes || preg_match("/all/i",$tabletypes)){
				$return_array[$table_line]['name']=$table_name_readable;
				$return_array[$table_line]['real_name']=$table_line;
				$return_array[$table_line]['system']="0";
				$return_array[$table_line]['config_section']=$config_sections[$table_line];
				$return_array[$table_line]['notes']=$table_notes[$table_line];
			}
		}	
		return $return_array;
	}

	/*
	 * Function: print_list_tables
	 * Meta: Prints a list of all tables in the database separated with <br /> elements
	*/
	public function print_list_tables($tabletypes){
		$this->list_tables_front_page($tabletypes);
		return;
		$all_tables=$this->list_tables($tabletypes);
		foreach ($all_tables as $table){
			print $table['name'] . "<br />";
		}
	}

	/* Function: list_tables_front_page
	 * Meta: This is the main admin page for the table list. No view, this is the full code to return the page.
	 * Returns: string (full html page)
	*/
	public function list_tables_front_page ($tabletypes="all"){

		if ($tabletypes=="all" || $tabletypes=="application"){
			$app_tables=$this->list_tables("application");
		}
		if ($tabletypes=="all" || $tabletypes=="system"){
			$sys_tables=$this->list_tables("system");
		}
		global $db;
		$database_name=$db->dbname();
		$tr_col="#f4f4f4";
		?>
		<div class="table_title"><img src="<?php echo SYSIMGPATH;?>/icons/table_gear.png"> Table Manager for database: <?php echo $database_name;?></div><div class=\"cleardiv\"></div><!--<span class="helptip">Manage your tables - view, add, search, edit and delete data</span>//--><br /><p>
	    <form name="table_manager_form" method="post" action="<?php if (stristr($_SERVER['SCRIPT_FILENAME'],"ui-ad")){ echo "mui-";} ?>administrator.php?action=table_manager_option" onSubmit="return false" />
	<br clear="all" />
	<table><tr><td><b>Table Actions:</b></td><td>
	<select name="table_action">
		    <option value="add_edit_table_fields">Add &amp; Edit Table Fields</option>
		    <option value="drop">Drop (delete table)</option>
		    <option value="duplicate">Duplicate</option>
		    <option value="export_data">Export Data</option>
		    <option value="filters">Filters</option>
		    <option value="import_data">Import Data</option>
		    <option value="meta_data">Meta Data</option>
		    <option value="optimise">Optimise</option>
		    <option value="permissions">Permissions</option>
		    <option value="queries">Queries</option>
		    <option value="relationships">Relationships</option>
		    <option value="repair">Repair</option>
		  </select>
	<script type="text/javascript">
	<!-- 
	//This is system javasript which is why it is included here. There are no reusable components 
	function vardump(arr,level) {
		var dumped_text = "";
		if(!level) level = 0;
		
		//The padding given at the beginning of the line.
		var level_padding = "";
		for(var j=0;j<level+1;j++) level_padding += "    ";
		
		if(typeof(arr) == 'object') { //Array/Hashes/Objects 
			for(var item in arr) {
				var value = arr[item];
				
				if(typeof(value) == 'object') { //If it is an array,
					dumped_text += level_padding + "'" + item + "' ...\n";
					dumped_text += dump(value,level+1);
				} else {
					dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
				}
			}
		} else { //Stings/Chars/Numbers etc.
			dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
		}
		return dumped_text;
	}

	// get_radio_value is currently not used
	function get_radio_value(){
		for (var i=0; i < document.table_manager_form.which_table.length; i++){
		   if (document.table_manager_form.which_table[i].checked){
		        var rad_val = document.table_manager_form.which_table[i].value;
			alert(vardump(rad_val));
			document.forms['table_manager_form'].elements['active_table'].value=rad_val;
		      }
		   }
	}
	//-->
	</script>
	 on <select name="selected_table">
	<?php 
		global $user;
		if ($user->value("type")=="master"){
			$tables=$this->list_tables_basic();
		} else {
			$tables=$this->list_tables_basic("application");
			}
		foreach ($tables as $table){
			list ($tval,$ttext)=explode(";;",$table);
			print "<option value=\"$tval\">$ttext</option>";
		}
	?>

	<script language="Javascript" type="text/javascript">
	<!-- 
	// This is system javascript for this page only - no reusable components

	document.addEventListener('keydown', function(event) {
	    if(event.keyCode == 13) {
		forthis=document.forms[0].elements['search_table'].value;
			counter=0;
			classed_tables=document.getElementsByClassName("tablerows");
			var names="";
			forthis=forthis.replace(/ /g,"_");
			var re = new RegExp(forthis,"gi");
			for (var i = 0; i < classed_tables.length; i++) {
				tabname = classed_tables[i].id;
				if (tabname.match(re)){
					tabname_add=tabname.replace(/^tr_/,"");
					names = names + tabname_add; 
					document.getElementById(tabname).style.display='block';
					counter++;
				} else {
					document.getElementById(tabname).style.display='none';
				};
			}
		if (counter==1){
			urlstr="administrator.php?action=list_table&t="+names;
				parent.loadPage(urlstr);
		} else {
						
			testDiv="tr_" + forthis;
			if (document.getElementById(testDiv)){
				urlstr="administrator.php?action=list_table&t="+forthis;
				parent.loadPage(urlstr);
			}
		}
	    }
	    else if(event.keyCode == 39) {
		alert('Please use the enter button to select a table');
	    }
	});


	function filter_table_list(forthis){
		classed_tables=document.getElementsByClassName("tablerows");
		var names="";
		forthis=forthis.replace(/ /g,"_");
		var re = new RegExp(forthis,"gi");
		for (var i = 0; i < classed_tables.length; i++) {
			tabname = classed_tables[i].id;
			if (tabname.match(re)){
				names = names + tabname; 
				document.getElementById(tabname).style.display='block';
			} else {
				document.getElementById(tabname).style.display='none';
			};
		}
	}

	// document.forms['table_manager_form'].elements['search_table'].focus();
	</script>

	</select> <input type="button" value="go" onClick="document.forms['table_manager_form'].submit()">
	</td></tr><tr><td align="right"><b>Search:</b></td><td>
	<input type="text" name="search_table" onKeyUp="filter_table_list(this.value)" placeholder="Find Table" onMouseOver="this.focus()">
	<!--
	<input type="text" name="active_table" value="Select a table below" style="color:#ccc" />
	//-->
	</td></tr></table>
	<br clear="all" />
	<table width="880" border=0 >
		<thead>
			<tr>
				<?php if ($tabletypes=="all" || $tabletypes=="application"){?>
					<td bgcolor="#666666"><img src="<?php echo SYSIMGPATH;?>/icons/table.png"><b>Application Tables</b><span class="helptip">Tables which are specific to your application</span></td>
				<?php } 
					if ($tabletypes=="all" || $tabletypes=="system"){?>
					<td bgcolor="#666666" ><img src="<?php echo SYSIMGPATH;?>/icons/table_gear.png"><b>System Tables</b><br /><span class="helptip"></span></td>
				<?php } ?>
			</tr>
		</thead>

		<tr><td valign="top" style="vertical-align:top; min-width:400px;">
			<table style="min-width:400px">
			<?php
			global $user;
			if ($user->value("type")=="superadmin" || $user->value("type")=="master"){?>
			<tr><td valign="top" style="vertical-align:top"><a href="<?php echo get_link("administrator.php?action=sysNewTable");?>" style="font-size:9px; color:#1b2c67;"><img src="<?php echo SYSIMGPATH;?>/icons/table_add.png" border=0 /> New Table</a></td></tr>
		<?php } ?>
		<?php
		// User application tables
		foreach ($app_tables as $app_table){
			if (!$app_table['config_section']){
				print "<tr style=\"background-color:".$tr_col."\" id=\"tr_".$app_table['real_name']."\" class=\"tablerows\" style=\"min-width:400px; border:1px orange solid;\"><td style=\"min-width:200px\">";
				// OLD ONE PRE new urls $edit_link = get_link($_SERVER['PHP_SELF'] . "?action=list_table&t=".$app_table['real_name'],$app_table['name']);
				$edit_link = get_link(HTTP_PATH . "/crud/table/".$app_table['real_name']."/action/list_table/",$app_table['name']);
				print "<a href=\"" . $edit_link . "\" alt=\"View Table Data\" title=\"View Table Data\">".$app_table['name']."</a></td>";
				print "<td style=\"text-align:right\" align=\"right\"><a href=\"$edit_link\" alt=\"View Table Data\" title=\"View Table Data\"><img src=\"".SYSIMGPATH."/icons/text_list_bullets.png\" border=0></a> ";
				print "<a href=\"".get_link("administrator.php?action=view_table_schema&table=".$app_table['real_name'])."\" alt=\"View Table Schema\" title=\"View Table Schema\"><img src=\"".SYSIMGPATH."/icons/table_gear.png\" border=0></a> ";
				print " <a href=\"" . get_link($_SERVER['PHP_SELF'] . "?action=export_table_front&t=".$app_table['real_name'])."\" alt=\"Export Table\" title=\"Export Table\"><img src=\"".SYSIMGPATH."/icons/table_go.png\" border=0></a> ";
				print "<a href=\"". get_link($_SERVER['PHP_SELF']."?action=load_table_data_from_file&table=".$app_table['real_name'])."\" alt=\"Import Data\" title=\"Import Data\"><img src=\"".SYSIMGPATH."/icons/table_row_insert.png\" border=0></a> ";
				print "<a href=\"".get_link($_SERVER['PHP_SELF']."?action=export_table_to_file&table=".$app_table['real_name']) . "\" alt=\"Export Table To CSV\" title=\"Export Table To CSV\"><img src=\"".SYSIMGPATH."/icons/table_row_delete.png\" border=0></a> ";
				print "<a href=\"".get_link($_SERVER['PHP_SELF']."?action=sysTableInfo&amp;t=".$app_table['real_name']) . "\" alt=\"More..\" title=\"More\"><img src=\"".SYSIMGPATH."/icons/table_edit.png\" border=0></a> ";
				print "</td></tr>";
				if ($tr_col=="#f4f4f4"){ $tr_col="#f7f7f7"; } else {$tr_col="#f4f4f4";}
			}
		}
		usort($app_tables, 'compare_config_section');
		$current_section="";
		$printed_modules_header=0;
		foreach ($app_tables as $app_table){
			if ($app_table['config_section']){
				if (!$printed_modules_header){ print "<tr style=\"background-color:#e1e1e1; margin:20px; padding:20px;\"><td height=\"30\" colspan=\"3\"><b>Modules</td></tr>"; $printed_modules_header=1;}
				if ($app_table['config_section'] != $current_section){
					print "<tr><td colspan=\"3\" height=\"15\"><b></b></td></tr>";
					print "<tr style=\"background-color:#cccccc\"><td colspan=\"3\"><b>" . $app_table['config_section'] . "</b></td></tr>";
					$current_section=$app_table['config_section'];
				}
				print "<tr style=\"background-color:".$tr_col."\" id=\"tr_".$app_table['real_name']."\" class=\"tablerows\" style=\"min-width:400px\"><td style=\"min-width:200px\">";
				$edit_link = get_link(HTTP_PATH . "/crud/table/".$app_table['real_name']."/action/list_table/",$app_table['name']);
				print "<a href=\"" . $edit_link . "\" alt=\"".$app_table['notes']."\" title=\"".$app_table['notes']."\">".$app_table['name']."</a></td>";
				print "<td style=\"text-align:right\" align=\"right\"><a href=\"$edit_link\" alt=\"View Table Data\" title=\"View Table Data\"><img src=\"".SYSIMGPATH."/icons/text_list_bullets.png\" border=0></a> ";
				print "<a href=\"".get_link("administrator.php?action=view_table_schema&table=".$app_table['real_name'])."\" alt=\"View Table Schema\" title=\"View Table Schema\"><img src=\"".SYSIMGPATH."/icons/table_gear.png\" border=0></a> ";
				print " <a href=\"" . get_link($_SERVER['PHP_SELF'] . "?action=export_table_front&t=".$app_table['real_name'])."\" alt=\"Export Table\" title=\"Export Table\"><img src=\"".SYSIMGPATH."/icons/table_go.png\" border=0></a> ";
				print "<a href=\"". get_link($_SERVER['PHP_SELF']."?action=load_table_data_from_file&table=".$app_table['real_name'])."\" alt=\"Import Data\" title=\"Import Data\"><img src=\"".SYSIMGPATH."/icons/table_row_insert.png\" border=0></a> ";
				print "<a href=\"".get_link($_SERVER['PHP_SELF']."?action=export_table_to_file&table=".$app_table['real_name']) . "\" alt=\"Export Table To CSV\" title=\"Export Table To CSV\"><img src=\"".SYSIMGPATH."/icons/table_row_delete.png\" border=0></a> ";
				print "<a href=\"".get_link($_SERVER['PHP_SELF']."?action=sysTableInfo&amp;t=".$app_table['real_name']) . "\" alt=\"More..\" title=\"More\"><img src=\"".SYSIMGPATH."/icons/table_edit.png\" border=0></a> ";
				print "</td></tr>";
				if ($tr_col=="#f4f4f4"){ $tr_col="#f7f7f7"; } else {$tr_col="#f4f4f4";}
			}
		}

		print "</table></td><td valign=\"top\" style=\"vertical-align:top\"><table border=0 style=\"min-width:400px\">";
		// Core system tables
		foreach ($sys_tables as $sys_table){
			print "<tr style=\"background-color:".$tr_col."\" id=\"tr_".$sys_table['real_name']."\" class=\"tablerows\" style=\"min-width:400px\"><td style=\"min-width:200px\">";

			// OLD ONE PRE NEW URLS $edit_link=get_link($_SERVER['PHP_SELF'] . "?action=list_table&t=".$sys_table['real_name'],$sys_table['name']);
			$edit_link = get_link(HTTP_PATH . "/crud/table/".$sys_table['real_name']."/action/list_table/",$sys_table['name']);

			print "<a href=\"$edit_link\" alt=\"View Table Data\" title=\"View Table Data\">".$sys_table['name']."</a></td>";
			print "<td style=\"text-align:right\" align=\"right\"><a href=\"$edit_link\" alt=\"View Table Data\" title=\"View Table Data\"><img src=\"".SYSIMGPATH."/icons/text_list_bullets.png\" border=0></a> ";
			print "<a href=\"" . get_link($_SERVER['PHP_SELF'] . "?action=view_table_schema&table=".$sys_table['real_name']) . "\" alt=\"View Table Schema\" title=\"View Table Schema\"><img src=\"".SYSIMGPATH."/icons/table_gear.png\" border=0></a> ";
			print " <a href=\"" . get_link($_SERVER['PHP_SELF'] . "?action=export_table_front&t=".$sys_table['real_name']) . "\" alt=\"Export Table\" title=\"Export Table\"><img src=\"".SYSIMGPATH."/icons/table_go.png\" border=0></a> ";
			print "<a href=\"" . get_link($_SERVER['PHP_SELF']."?action=load_table_data_from_file&table=".$sys_table['real_name']) . "\" alt=\"Import Data\" title=\"Import Data\"><img src=\"".SYSIMGPATH."/icons/table_row_insert.png\" border=0></a> ";
			print "<a href=\"" . get_link($_SERVER['PHP_SELF']."?action=export_table_to_file&table=".$sys_table['real_name']) . "\" alt=\"Export Table To CSV\" title=\"Export Table To CSV\"><img src=\"".SYSIMGPATH."/icons/table_row_delete.png\" border=0></a> ";
			print "<a href=\"".get_link($_SERVER['PHP_SELF']."?action=sysTableInfo&amp;t=".$sys_table['real_name']) . "\" alt=\"More..\" title=\"More\"><img src=\"".SYSIMGPATH."/icons/table_edit.png\" border=0></a> ";
			print "</td></tr>";
			if ($tr_col=="#f4f4f4"){ $tr_col="#f7f7f7"; } else {$tr_col="#f4f4f4";}
		}
		print "</table></td></tr></table></form>";
	}

	/* 
	 * Function: format_table_name
	 * Meta: removes underscores and capitalises the first letter. Also removes system table name prexes. Used for ui display and not internal reference.
	 * Returns: string (name of table in more presentable format)
	*/
	public function format_table_name($real_name){
		$print_tablename=ucfirst(str_replace("_"," ",$real_name));
		global $CONFIG;
		if ($CONFIG['table_prefixes']){
				$print_tablename_arr=array();
				$all_table_prefixes=explode(",",$CONFIG['table_prefixes']);
				foreach ($all_table_prefixes as $table_prefix){
					if (preg_match("/^$table_prefix/",$real_name)){
						$format_tablename=explode("_",$real_name);
						array_shift($format_tablename);
					}
				}
		foreach ($format_tablename as $word){
			array_push($print_tablename_arr, ucfirst($word));
		}
		$print_tablename=join(" ",$print_tablename_arr);
		}
		if (!$print_tablename){ $print_tablename=$real_name;}
		return $print_tablename;
	}

	/* 
	 * Function: get_table_type
	 * Meta: Is a table an application table or a system table?
	 * Returns: string (either application or system)
	*/
	public function get_table_type($table){

		$test_sql="SELECT system FROM table_meta WHERE table_name = \"$table\" AND system=1";
		global $db;
		$res=$db->query($test_sql);
		$h=$db->fetch_array($res);
		if (!$db->num_rows($res)){
			$return_type = "application";
		} else {
			$return_type="system";
		}
		return $return_type;
	}
// end class tables
}
?>
