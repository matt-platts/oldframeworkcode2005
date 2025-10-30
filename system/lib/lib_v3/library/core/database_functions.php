<?php

/* 
 * CLASS: database_functions
 * Meta: Static functions - interfaces to all CRUD operations
 */
class database_functions {

/* 
 * Function list_table
 * Meta: Lists a tables contents either by direct outptut, output into a template, or may return a hash data structure for further processing (depending on filter keys).
 * Also lists queries from the queries table
 * Returns null - this function completes the process
*/
static function list_table($tablename,$options){
	global $db;
	global $col2_open;
	global $use_built_in_styles;
	global $CONFIG;

	$basepath=$CONFIG['basepath'];
	$libpath=$CONFIG['libpath'];
	include_once("$libpath/classes/core/recordset.php");
	$rs=new recordset($tablename,$options);
	$rs->memorize_filter();
	$rs->recall_filter();
	$rs->merge_recall_with_request_vars();
	$output_text="";
	//$querytables=list_tables_in_query($rs->actual_query); // NOT USED!
	if (!$rs->value("on_query")){
		$permissions_result = self::check_dbf_permissions($rs->value("recordset_source"), "view" );
		if ($permissions_result['Status']==0){ if (stristr($_SERVER['PHP_SELF'],"administrator.php")){print "<p class=\"dbf_para_alert\">Permissions Error - sorry you don't have permissions to do that.";} return $permissions_result['Message']; exit; }
	}

	global $page;
	//if (!$col2_open && $use_built_in_styles && !$page->value("pureAjax")){open_col2();print "<!-- col2 open from list_table //-->";}
	$EXPORT=array();
	
	// print delete form now, as we're going to be printing the header form shortly..need to get it out of the way (or do it after..)
	// also only do this for admin or for site where it is expressly asked for!
	if (stristr($_SERVER['PHP_SELF'],"administrator.php") || stristr($_SERVER['PHP_SELF'],"routing.php") && ($options['filter']['include_delete_option'] || $options['filter']['imd'])){
		$EXPORT['delete_form']=$rs->delete_record_form($_REQUEST['s']);
		$output_text .= $EXPORT['delete_form'];
	} else {
		$EXPORT['delete_form']="";
	}

	// print form header for widgets
	// open form tag if any filters are present. for now open it anyway
	$output_text .= $rs->filter_form_header();
	$EXPORT['form_header'] = $rs->filter_form_header();

	$prs_rvs = $rs->print_recordset_header();
	$output_text .= $prs_rvs['output_text'];
	if ($prs_rvs['EXPORT']){ $EXPORT=array_merge($EXPORT,$prs_rvs['EXPORT']);}

	$output_text .= $rs->print_header_interface_buttons();

	$recordset_filtering = $rs->print_recordset_filtering();
	$output_text .= $recordset_filtering['filtering_html'];
	$EXPORT['search_button']=$recordset_filtering['search_button'];
	$EXPORT['search_input_field']=$recordset_filtering['search_input_field'];
	$EXPORT['form_header'] .= $recordset_filtering['form_header'];
	$EXPORT['sort_selector'] .= $recordset_filtering['sort_selector'];
	$EXPORT['records_per_page_select'] .= $recordset_filtering['records_per_page_select'];
	$EXPORT['records_per_page_full'] .= $recordset_filtering['records_per_page_full'];
	$EXPORT['az_icons'] .= $recordset_filtering['az_icons'];
	$EXPORT['clear_search_button'] .= $recordset_filtering['clear_search_button'];
	$EXPORT['drop_down_filter'] .= $recordset_filtering['drop_down_filter'];
	/// eeeee

	if ($sub_title_text){print $sub_title_text;} // dont think this will work or do anything

	// We need the table description as part of the where clause construction so get it now and fill in display_fields key
	if (!$rs->set_fields_to_display()){ format_error("Cant set fields to display or no fields to display",1); }
	$EXPORT['field_headers']=$rs->filter_key_value("display_fields");

	// 1. Set value of the primary key 
	if (!$rs->value("on_query")){
		$rs->set_value("pk",get_primary_key($rs->value("recordset_source")));
	} else {
		// first field acts as primary key
		$rs->set_value("pk",array_shift(explode(",",$rs->value("fields_to_display_from_query")))); 
	}

	$sql=$rs->recordset_sql();

	if ($rs->filter_key_value("custom_search_function")){
		require_once(LIBPATH . "/classes/search/".$rs->filter_key_value("custom_search_function").".php");
		$cust_search_func=$rs->filter_key_value("custom_search_function");
		$pjws = new $cust_search_func();
		$result_set=$pjws->custom_search($_REQUEST['dbf_search_for'],$rs->filter_key_value("display_fields"));
		$non_limited_result=sizeof($result_set['results']);
		$total_results_for_non_limited_query=$non_limited_result;
	} else {
		$non_limited_result=$sql;
		$total_result=$db->query($sql) or format_error("Error 88172: Cannot evaluate sql statement. <br /><br /><a href=\"".$_SERVER['PHP_SELF']."?action=list_table&t=$tablename&clear_filter_memory=1\">Click here to clear filters</a> - this may help if you have formulated an invalid statement.",1,"","SQL: $sql<br /><br />Error Message: " . $db->db_error());
		$total_results_for_non_limited_query = $db->num_rows($total_result);
	}

	$output_text .= "<input name=\"results_for_non_limited_query\" type=\"hidden\" value=\"$total_results_for_non_limited_query\">";
	$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"results_for_non_limited_query\" value=\"$total_results_for_non_limited_query\">\n";

	// 8. Do we have a limit filter? Add this to the very end
	
	$sql=$rs->add_limits_to_sql($sql,$total_results_for_non_limited_query);

	global $user;
	global $page;
	if (($user->value("type")=="master" || $user->value("type")=="superadmin") && strlen(stristr($_SERVER['PHP_SELF'],"ministrator")) && !$page->value("pureAjax")){
	// add sql to source so we can see it for super and master
/*
		print "<script language=\"javascript\" type=\"text/javascript\">\n";
		print "sql = '".$db->db_escape($sql)."'";
		print "</script>";
*/
	}

	$debug=0;
	if ($debug){print $sql;}
	$result=$db->query($sql) or print format_error($db->db_error(),"1");

	if (!$rs->check_permissions_on_returned_records($permissions_result,$result)){

		format_error("No permissions available to view this recordset as at least one row returned failed on a permissions check.",1); exit;
	}

	$paging_return_vars=$rs->paging_and_sub_display($result,$total_results_for_non_limited_query);
	$output_text .= $paging_return_vars['output_text'];
	$EXPORT=array_merge($EXPORT,$paging_return_vars['EXPORT']);
	$EXPORT['form_header'] .= $EXPORT['append_to_form_header'];

	$output_text .= "</form>\n"; // again, check for filters before arbritrily doing this
	$EXPORT['form_footer'] = "</form>\n"; // again, check for filters before arbritrily doing this

	$EXPORT['az_icons'] .= "<script language=\"Javascript\" type=\"text/javascript\">\n" . "popUpAZ();\n</script>\n";
	$fields_to_display="," . $rs->filter_key_value("display_fields") . ",";
	if ($rs->value("on_query")){$fields_to_display=",".query_functions::queryfields_as_csv_taking_care_of_brackets($rs->filter_key_value("display_fields")) . ",";}

	// start multiple records form (checkboxes on each row)
	include_once("$libpath/classes/core/form.php");
	
	$multi_records_form=new dynamic_form($rs->value("recordset_source"),"delete","","","");
	$sys_form_id=$multi_records_form->dbf_system_log_form_generation();
	$output_text .= "<div id=\"dbf_data_container\"><form name=\"multi_records_form\" id=\"multi_records_form\" method=\"post\" action=\"administrator.php?action=process_multiple_records\">";
	$output_text .= "<input type=\"hidden\" name=\"t\" value=\"".$rs->value("original_recordset_source")."\">\n";
	$output_text .= "<input type=\"hidden\" name=\"multi_records_action\" value=\"delete\">\n";
	$output_text .= "<input type=\"hidden\" name=\"dbf_cur_recordset_start\" value=\"$cur_recordset_start\">\n";
	$output_text .= "<input type=\"hidden\" name=\"dbf_sys_form_id\" value=\"$sys_form_id\">\n";

	$output_text .= "<table class=\"dbf_list_table_norestrict\" id=\"dbf_data_table\">";

	$output_text .= $rs->table_header_row();

	// if totalling any columns up, store in $total_cols hash
	if ($rs->filter_key_value("total_columns")){
		$total_columns=explode(",",$rs->filter_key_value("total_columns"));
		foreach ($total_columns as $total_col){
			$varname=$total_col;
			$total_cols[$varname]=0;
		}
	}

	// any more links from table relations we dont have?
	/* NOT REQUIRED AS LOADED WITH ORIGINAL OPTIONS/FILTER FROM THE TABLE RELATION FOR LIST AND FORMS (in classes/core/filters.php)
	if (!$_REQUEST['always_raw_data']){
		$rs->load_select_values_from_table_relations();
	}
	*/

	if ($rs->filter_key_value("custom_search_function")){
		$printrows=$rs->print_rows_from_custom_search($result_set['results'],$fields_to_display,$total_cols);
	} else {
		$printrows=$rs->print_rows($result,$fields_to_display,$total_cols);
	}

	if ($result_set['category_results'] && $user->value("id")==1){
			$cat_output .= "<p><b>Suggested categories (click to browse category):</b></p>";
		foreach ($result_set['category_results'] AS $cr=>$cr_values){
			$cat_output .= "<p><a href=\"".$cr_values['html_page_name']."\">".$cr_values['category_name']."</a></p>";
		}
		$EXPORT['category_suggestions']=$cat_output;
	}

	$output_text .= $printrows['output_text'];
	$total_cols=$printrows['total_cols'];
	foreach ($printrows['EXPORT'] as $exvar=> $exval){
		$EXPORT[$exvar]=$exval;
	}
	if ($rs->filter_key_value("dbf_output_type")=="moodgets_data_store"){
		ob_end_clean();
		$rs->moodgets_data_store($EXPORT['rows'],$rs->value("recordset_source"),$total_results_for_non_limited_query,$options);
		exit;
	}
	if ($total_cols){
		$output_text .= $rs->column_totals($total_cols);
	}

	$output_text .= "</table>";
	$output_text .= "</form><!--ah close multi records form, next data container//--></div>"; // this is the form that holds the table that allows multi delete etc.
	// print or return output text if we're not displaying in a tempalte
	if ((!$rs->filter_key_value("display_in_template") && !$rs->filter_key_value("display_in_admin_template")) && !$rs->filter_key_value("export") && !$_REQUEST['dbf_output_type']){ print $output_text; }
	if ($rs->filter_key_value("export")=="text" || $rs->filter_key_value("export")=="html" && (!$rs->filter_key_value("display_in_template") && !$rs->filter_key_value("display_in_admin_template"))){ return $output_text;}

	if ($_REQUEST['dbf_output_type']=="excel"){
		$rs->data_to_excel_spreadsheet($EXPORT['rows'],ucfirst($rs->value("recordset_source")),$options);
		exit;
	}
	if ($_REQUEST['dbf_output_type']=="moodgets_data_grid"){
		$rs->data_to_moodgets_grid($EXPORT['rows'],$rs->value("recordset_source"),$options);
	}
	// from here, we are displaying in a template, so need to load it
	if ($rs->filter_key_value("display_in_template")){ $dbf_template_table="templates"; $dbf_template_key="display_in_template"; $key_column="id";}
	if ($rs->filter_key_value("display_in_admin_template")){ $dbf_template_table="admin_templates"; $dbf_template_key="display_in_admin_template"; $key_column="dbf_key_name";}

	if ($dbf_template_table){
		$sql="SELECT * from $dbf_template_table where $key_column = \"" . $rs->filter_key_value($dbf_template_key) . "\"";
		$result=$db->query($sql);
		while ($row=$db->fetch_array($result)){
			$template=$row['template'];
		}

		global $libpath;
		require_once("$libpath/classes/core/recordset_template.php");
		$rt=new recordset_template();
		// global variables
		if ($options['filter']['export_global_variable']){
			$gvar=explode("=",$options['filter']['export_global_variable']);
			$EXPORT[$gvar[0]]=$gvar[1];
		}
		$template=Codeparser::parse_request_vars($template);
		$template=$rt->rs_to_template($rs,$template,$EXPORT);

		if ($rs->filter_key_value("export")=="hash" || $rs->filter_key_value("export")=="text" || $rs->filter_key_value("export")=="html"){
			if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
				// mattplatts had to comment this oneout at a late date, see if it causes problems anywhere else
				// comment out was done for view statement on medico site
				//print $template;
			} 
			return $template;
		} else {
			format_error("No export variables found in filter",1);
			exit;
			print $template;
		}		
	}
	if ($total_cols){
		print $rs->column_totals_report_at_base($total_cols);
	}
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// form from table. Prints an html form basead on table structure and filter paramaters.			//
// Form allows both add and edit of data									//
//														//
// Args: tablename = name of table in database, 								//
//       formtype = (edit_all, edit_row, add_or_edit, add_multiple, add_row)					//
//	 rowid_for_edit = id of the row to edit - optional							//
//	 add_data = number of empty lines to print for adding records - optional				//
//	 options = able to contain various strings of text for a number of options - see the documentation	//
//														//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
static function form_from_table($tablename,$formtype,$rowid_for_edit,$add_data,$options){
	global $db;
	global $col2_open;
	global $use_built_in_styles;
	global $use_system_messages;
	global $page;
	global $CONFIG;
	
	$basepath=$CONFIG['basepath'];
	$libpath=$CONFIG['libpath'];
	include_once("$libpath/classes/core/form.php");
	if ($options['filter']['form_name']){ $form_name=$options['filter']['form_name']; } else { $form_name=""; }
	if ($formtype=="add_row" && !$rowid_for_edit && $options['filter']['pre_generate_insert_id']){
		$get_pk_sql = "DESC " . $tablename;
		$result=$db->query($get_pk_sql);
		while ($row=$db->fetch_array($result)){
			array_push($table_fields,$row['Field']);
			array_push($table_field_types,$row['Type']);
			$table_description[$row['Field']]=$row['Type'];
			if ($row['Key']=="PRI"){$pk=$row['Field'];}
		}
		$insert_now="INSERT INTO $tablename ($pk) values('')";
		$insert_now_result=$db->query($insert_now);
		$last_insert_id=$db->last_insert_id();
		$formtype="edit_single";
		$options['filter']['id_is_pre_generated']=1;
		$edit_form_instead=self::form_from_table($tablename,$formtype,$last_insert_id,"",$options);
		return $edit_form_instead;
		exit;
	}
	$form=new dynamic_form($tablename,$formtype,$rowid_for_edit,$add_data,$options,$form_name);

	$permissions_result=self::check_dbf_permissions($tablename,$formtype,$rowid_for_edit,$options);
	if ($permissions_result['Status']==0){ if (!$options['filter']['continue_silently_on_permissions_errors']){ return $permissions_result; exit; } else { return; exit; }}
	//if (!$col2_open && $use_built_in_styles && !$page->value("subform_mode")){open_col2();print "<!-- col2 open from form_from_table //-->";}
	
	if ($CONFIG['enable_form_logging']){
		$sys_form_id=$form->dbf_system_log_form_generation();
		$form->set_value("sys_form_id",$sys_form_id);
	}
	$EXPORT = array();
	if ($_REQUEST['subform_mode']){
 	//Check for missing request vars
	$sql_filter=$options['filter']['sql_filter'];
	$rvs=preg_match_all("/_REQUEST\['(.*?)']/",$options['filter']['sql_filter'],$matches); // ? makes it non greedy
	foreach ($matches[1] as $requestVar){
		if (!$_REQUEST[$requestVar]){
			print "<p class=\"dbf_para_alert\">";
			if ($options['filter""']['subform_no_master_record_message']){
				print $options['filter']['subform_no_master_record_message'];
			} else {
				print "The master record has not yet been saved or does not exist, and therefore does not have an ID. Please save the master record and this sub-form will become active.<br /><br /><span style=\"font-size:9px;\">Further information: the following request variable is blank: $requestVar</span>";
			}
			print "</p>";
		exit;
		}
	}
	}

	$default_size="18"; // default size for text input boxes;

	// Print name of table at top
	$formatted_tablename=format_table_name($tablename);
	if (preg_match("/edit/",$formtype)){
		$table_action="Edit";
		if ($_REQUEST['action']=="process_update_table"){
			$table_action="Continue Editing";
		}
	} else {
		 $table_action = "Add Record to";
		if ($_REQUEST['action']=="process_update_table"){
			$table_action = "[+] Add another record to";
		}
	 }

	if ($options['filter']['link_to_list_table_from_add_and_edit'] || ($CONFIG['link_to_list_table_from_add_and_edit'] && !stristr($_SERVER['PHP_SELF'],"site.php"))){ // values to be: after add one, always, never
		if ($_REQUEST['relation_key'] && $_REQUEST['relation_id']){ $include_relations = "&relation_key=".$_REQUEST['relation_key'] . "&relation_id=" . $_REQUEST['relation_id'];}
		$link_to_list_table = "<div id=\"list_table_link\" class=\"rightFloat\"><a href=\"Javascript:loadPage('" . $_SERVER['PHP_SELF'] . "?action=list_table&t=$tablename$include_relations')\"><img src=\"".SYSIMGPATH."/application_images/button_previous_beige_29x28.png\" alt=\"Return to $formatted_tablename list\" title=\"Return to $formatted_tablename list\" border=0/></a></div>";

	} else {$link_to_list_table="";}

	if ($options['filter']['title_text']){
		$options['filter']['title_text']=str_replace("{=id}",$rowid_for_edit,$options['filter']['title_text']);
		if ((!$rowid_for_edit || ($rowid_for_edit && $options['filter']['pre_generate_insert_id'] && $options['filter']['id_is_pre_generated'])) && $options['filter']['title_text_add_record']){
			$options['filter']['title_text']=$options['filter']['title_text_add_record'];
		}
		$output_text =  $link_to_list_table . "<div class=\"table_title,sys_form_".$tablename."_$formtype"."_title\">" . $options['filter']['title_text'] . "</div>";
		$output_text =  $link_to_list_table . "<div class=\"table_title\">" . $options['filter']['title_text'] . "</div>";
		$EXPORT['title_text'] = $options['filter']['title_text'];
	} else {


		if ($_POST['rowid_for_edit']){
			$post_name="id_".$_POST['rowid_for_edit']."_";
		} else {
			$post_name="new_";
		}
		if ($_POST[$post_name."name"]){
			$output_text_record .= ": " . $_POST[$post_name."name"];
		} else if ($_POST[$post_name."title"]){
			$output_text_record = ": " . $_POST[$post_name."title"];
		}

		$output_text =  $link_to_list_table . "<div class=\"table_title\">" . $table_action . " " . $formatted_tablename . $output_text_record . "</div>";  
		$EXPORT['title_text'] = $table_action . " " . $formatted_tablename . $output_text_record;
	}
	//print $EXPORT['title_text'];
	if ($options['filter']['title_text']=="{=none}" || strlen($options['filter']['title_text'])==0 && isset($options['filter']['title_text'])){ $output_text="";} else {
		$output_text .= "<div class=\"cleardiv\"></div>";
	}

        if ($options['filter']['sub_title_text']){
                $output_text .= "<div id=\"sub_title_text\">".$options['filter']['sub_title_text']."</div>";
                $EXPORT['sub_title_text']= "<div id=\"sub_title_text\">".$options['filter']['sub_title_text']."</div>";
        }

	$EXPORT['link_to_list_table']=$link_to_list_table;

	if ($options['filter']['quick_style']){
		$output_text .= "<style type=\"text/css\">\n".$options['filter']['quick_style']."\n</style>\n";
		$EXPORT['quick_style'] = "<style type=\"text/css\">\n".$options['filter']['quick_style']."\n</style>\n";
	}	

	// Hidden Form for deleting a record - print now
	if (stristr($_SERVER['PHP_SELF'],"ministrator.php") || $options['filter']['front_end_record_delete']){
		$EXPORT['delete_form']=$form->delete_record_form();
		$output_text .= $EXPORT['delete_form'];
	} else {
		$EXPORT['delete_form']="";
	}

	// Print our main form header and open the table here including the input paramaters we use to generate the form	
	$output_text .= $form->form_header();	
	$set_js=$form->set_js_vars_for_custom_functionality($rowid_for_edit);
	$output_text .= $set_js;
	$EXPORT['form_header'] .= $set_js;
	//print "Matts debugging text: got header html of <textarea rows=3 cols=100>" . $form->form_header() . "</textarea>";
	// Load a list of table fields into the array $table_fields, and a corresponding list of types into the array $table_field_types - now overwritten with all in a $table_description hash
	$table_fields=array();
	$table_field_types=array();
	if ($options['filter']['table_class']){$output_text .= "<table class=\"".$options['filter']['table_class']."\">\n";}else{$output_text .= "<div class=\"dbf_form_table_container\"><table class=\"dbf_form_table\">\n";}
	$sql = "DESC " . $tablename;
	$result=$db->query($sql);
	while ($row=$db->fetch_array($result)){
		array_push($table_fields,$row['Field']);
		array_push($table_field_types,$row['Type']);
		$table_description[$row['Field']]=$row['Type'];
		if ($row['Key']=="PRI"){$pk=$row['Field'];}
	}
	if (!$pk){print format_error("No primary key defined on $tablename or table does not exist",1,3); exit;}	

	// MAIN OPTION !!!			EDIT ALL OR ADD MULTIPLE
	// Option: If this db interface is for editing all records or adding multiple records, we list the table fields accross the top and the row ids down the left, loading in all the data as we go.	
	// CLOSE MAIN OPTION !!
	if ($formtype == "edit_all" or $formtype == "add_multiple"){
		// start a row, format and print the column headers..
		$rowcount=0;
		$output_text .= "<tr bgcolor=\"#f1f1f1\">";
		// if theres a dynamic field list present on the edit all then load it..
		if ($_SESSION['recordset_filters'][$tablename]['dbf_dynamic_fields_to_display_list']){
			$options['filter']['display_fields']=$_SESSION['recordset_filters'][$tablename]['dbf_dynamic_fields_to_display_list'];
		}
		if ($_POST['dbf_dynamic_fields_to_display_list']){
			$options['filter']['display_fields']=$_POST['dbf_dynamic_fields_to_display_list'];
		}
		if ($_POST['dbf_dynamic_mootools_fields_to_display_list']){
			$options['filter']['display_fields']=$_POST['dbf_dynamic_mootools_fields_to_display_list'];
		}
		
		// we need to get the fields list to filter the search, get it now. NOTE: filtering is not currently enabled on edit_all..

		if (!$options['filter']['display_fields']){ // display all from desc instead of list and load into same vars
				$all_table_fields = array();
				$sql_for_tablefields="DESC " . $tablename;
				$desc_result = $db->query($sql_for_tablefields);
				while ($desc_row = $db->fetch_array($desc_result)){
					array_push($all_table_fields,$desc_row['Field']);
					if ($options['filter']['filter_field_between_dates']==$desc_row['Field']){$date_filter_type=$desc_row['Type'];}
				}	
				$options['filter']['display_fields']=implode(",",$all_table_fields);
		}

		if ($options['filter']['display_fields']){
			// add primary key if it is not in display fields list
			$add_commas_to_display=",".$options['filter']['display_fields'].",";
			if (!stristr($add_commas_to_display,",$pk,")){
				$options['filter']['display_fields']=$pk.",".$options['filter']['display_fields'];
			}
			$display_table_fields=explode(",",$options['filter']['display_fields']);
		}
		foreach ($display_table_fields as $fieldname){
			$names=explode("_",$fieldname);
			$formatted_fieldname=array();
			foreach ($names as $name){
				$name = ucfirst($name);
				array_push ($formatted_fieldname, $name);
				$rowcount++;
			}
			$new_fieldname= implode(" ", $formatted_fieldname);
			if ($options['filter'][$fieldname]['field_type'] != "hidden"){
				if ($options['filter'][$fieldname]['fieldname_text']){
					$output_text .= "<td><b>" . $options['filter'][$fieldname]['fieldname_text'] . "</b></td>";
				} else {
					$output_text .= "<td><b>" . $new_fieldname . "</b></td>";
				}
			} else {
			}
			if ($options['filter'][$fieldname]['fieldname_text']){
				$EXPORT['fields'][$fieldname]['formatted_fieldname'] = $options['filter'][$fieldname]['fieldname_text'];
			} else {
				$EXPORT['fields'][$fieldname]['formatted_fieldname'] = $new_fieldname;
			}
		}		
		$output_text .= "</tr>";
		//$output_text .="<td></td>"; // note blank cell is left for delete buttons etc.

		// print current data into form
		$data_query="SELECT " . $options['filter']['display_fields'] . " FROM " . $tablename;

		#start extra filtering for edit all
		$where_clauses = array();
		// add basic filter options to the where. This is id_from, id_to, order_by, order_by_direction and field_equals;
		if ($options['filter']['id_from']){ array_push($where_clauses, "$pk > " . $options['filter']['id_from']); }
		if ($options['filter']['id_to']){ array_push($where_clauses, "$pk > " . $options['filter']['id_to']); }
		if ($options['filter']['order_by']){ $orderby = " ORDER BY " . $options['filter']['order_by'] . " "; } else {$orderby = " ORDER BY $pk ";}
		if ($options['filter']['order_by_direction']){ $orderby .= $options['filter']['order_by_direction'] . " "; }
		if ($options['filter']['field_equals']){ 
			$field_equals = explode("=",$options['filter']['field_equals']);
			$field_equals_string = $field_equals[0] . " = \"" . Codeparser::parse_request_vars($field_equals[1]) . "\"";
			array_push($where_clauses,$field_equals_string);
		 } 

		// 3. If we have an sql filter option, this needs to be parsed and then added
		if ($options['filter']['sql_filter']){
			$options['filter']['sql_filter']=Codeparser::parse_request_vars($options['filter']['sql_filter']);
			$bits=explode(" = ",$options['filter']['sql_filter']);
			$filter_fieldname=$bits[0];
			$filter_value=$bits[1];
			$match_result=preg_match_all("/{=.*}/",$filter_value,$matches);
			$matches=$matches[0];
			foreach ($matches as $each_match){
				$each_match_var=str_replace("{=","",$each_match);
				$each_match_var=str_replace("}","",$each_match_var);
				if (strlen(strpos($each_match_var,"SQL:"))){ // if it is an sql query
					$sql_filter_query = preg_replace("/SQL: ?/i","",$each_match_var);

					// anything to evalute?
					$querybits=explode("=",$sql_filter_query);
					if ($querybits[1]==" user_data_from_cookie('id')"){ global $user; $querybits[1]=$user->value('id');}
					$sql_filter_query=implode("=",$querybits);
					print "now is'$sql_filter_query'<p>";
					//end evaluate

					//$sql_filter_query="SELECT user_area from user where user.id=1";
					//print "now is '$sql_filter_query' <p>";
					
					$filter_result=$db->query($sql_filter_query) or format_error("<b>Problem with $sql_filter_query:</b><p>" . $db->db_error());
					while ($filter_row = $db->fetch_array($filter_result)){
						array_push($where_clauses, $bits[0] . "='" . $filter_row[0] . "'");
					}
				}
			}// close for each $match
			if (!$matches){
				$options['filter']['sql_filter']=str_replace("SQL:","",$options['filter']['sql_filter']);
				$options['filter']['sql_filter']=self::eval_request($options['filter']['sql_filter']);
				array_push($where_clauses, $options['filter']['sql_filter']);
			}
		}

		// 4.5 - DBF Search - there is NO difference between this function and the one in list_records. The options['filter']['display_fields'] block of 10 lines is in a different place but that is all!

		$search_for_fields=array();
		if ($_REQUEST['dbf_search_fields']){$fields_to_search=$_REQUEST['dbf_search_fields'];} else if ($options['filter']['dbf_search_fields']){$fields_to_search=$options['filter']['dbf_search_fields'];}
		if ($fields_to_search=="All Fields"){$fields_to_search=$options['filter']['display_fields'];}
		if ($_REQUEST['dbf_search_for'] || strlen($_REQUEST['dbf_search_for'])){
			if ($fields_to_search){
				$each_search_fields=explode(",",$fields_to_search);
				foreach ($each_search_fields as $each_search_field){
					// is this field from a select list?
					if (preg_match("/SQL:/",$options['filter'][$each_search_field]['select_value_list'])){
						$possible_values=self::search_linked_field($options['filter'][$each_search_field]['select_value_list'],$_REQUEST['dbf_search_for']);	
						if ($possible_values){
							$search_for_field_string = $each_search_field . " IN (" . $possible_values . ")";
						} else { 
						
							//print format_error("Warning: no select list values found for the following field (Code 919): $each_search_field",0,2);
							//- but there don't have to be results - if you searched through a set of fields for something present in one but not in the SQL. this would always happen!
						}
					} else {
						$search_for_field_string = $each_search_field . " LIKE \"%" . $_REQUEST['dbf_search_for'] . "%\"";
					}
					array_push($search_for_fields,$search_for_field_string);	
				}
			} else {
				// run the above code over a table description here..
			}
			$sql_search_paramaters = "(" . implode(" OR ",$search_for_fields) . ")";
			array_push($where_clauses,$sql_search_paramaters);
		}

		// 5. There are also where clauses if we have an inherent relation id
		if ($_REQUEST['relation_id'] && $_REQUEST['relation_key']){
			$get_relation_from_id_sql=$db->record_from_id("table_relations",$_REQUEST['relation_id']);
			$relation_query = $get_relation_from_id_sql['field_in_table_2'] . "= " . $_REQUEST['relation_key'];
			array_push ($where_clauses, $relation_query); 
		}

		// 5.5 Drop down filters
		if ($options['filter']['dbf_drop_down_filter_field']){
			$all_drop_down_filter_fields=explode(",",$options['filter']['dbf_drop_down_filter_field']); 
			$dffcount=1;
			foreach ($all_drop_down_filter_fields as $dbf_drop_down_filter_field){
				$dffname="dbf_drop_down_filter-$dffcount";
				if ($_REQUEST[$dffname]){
					$drop_down_filter_query = $dbf_drop_down_filter_field . " =\"". $_REQUEST[$dffname] . "\"";
					$where_clauses[]=$drop_down_filter_query;
				}
			}
			
		}

		// 6. Now we can flatten the where clauses onto the query
		$all_sql_paramaters = implode(" AND ", $where_clauses);
		if ($all_sql_paramaters){	
			$data_query .= " WHERE " . $all_sql_paramaters . " ";
		}

		// 7. ordering
		if ($orderby){
			$data_query .= " $orderby";
		}
		
		// 8. Do we have a limit filter? Add this to the very end
		if ($options['filter']['limit'] && $options['filter']['limit'] != "All" && $options['filter']['limit'] !="Nil"){ 
			if ($options['filter']['limit_from']){
				$data_query .= " LIMIT " . $options['filter']['limit_from'] . "," . $options['filter']['limit'] . " "; 
			} else {
				$data_query .= " LIMIT " . $options['filter']['limit'] . " "; 
			}
		} else if ($options['filter']['limit']=="Nil"){
				$data_query .=" LIMIT 0";
		}
		# END FILTERING FOR EDIT_ALL
		$result=$db->query($data_query);
		if ($use_system_messages && $db->num_rows($result)==0){
			$output_text .= "<tr><td colspan=\"" . $rowcount . "\"><font color='cc0000'>Note: table is currently empty</font></td></tr>\n";
		}

		// done this already $options['filter']['display_fields'] = $pk . "," . $options['filter']['display_fields'];
		$total_up_columns=",".$options['filter']['total_columns'].",";

		$fields_to_display_arr=explode(",",$options['filter']['display_fields']);
		while ($row=$db->fetch_array($result)){
			$row_id=$row[$pk];
			$output_text .= "<tr>";
			foreach ($fields_to_display_arr as $tablefield){
				// get the field type
				$test_totals=",".$tablefield.",";
				if (strlen(strpos($total_up_columns,$test_totals))){
					if(is_numeric($row[$tablefield])){
						$running_total=$running_total+$row[$tablefield];
					}
					
				}
				$fieldtype=$table_description[$tablefield];
				if ($tablefield == $pk){
					$size=3; 
					$readonly = " disabled"; 
				} else {
					$size=$default_size; 
					$readonly=""; 
				}
				if (stristr($fieldtype,"int(")){$size=3;}
				if (stristr($fieldtype,"float")){$size=4;}
				if (stristr($fieldtype,"decimal")){$size=4;}
				if (stristr($fieldtype,"enum")){$fieldtype=$form->options_from_enum_type($fieldname,$fieldtype);}

				if ($options['filter'][$fieldname]['field_size']){$size=$options['filter'][$fieldname]['field_size'];}
				if ($options['filter'][$tablefield]['field_type']){$fieldtype=$options['filter'][$tablefield]['field_type'];}

				$EXPORT['rows'][$row_id][$tablefield]['field_value']=$row[$tablefield];

				// set object stuff
				$obj_fieldname="id_" . $row_id . "_" . $tablefield;
				$form->add_to_field($tablefield,"value",$row[$tablefield]);
				$form->add_to_field($tablefield,"name",$obj_fieldname);
				$form->options['filter'][$tablefield]['field_size']=$size; 
				if ($readonly){
					$form->options[$tablefield]['readonly']="disabled"; 
					$form->options['filter'][$tablefield]['readonly']="disabled"; 
				}
				// end set object stuff
				// select

				$valign = "middle";
				if ($fieldtype != "hidden"){
					$output_text .= "<td valign=\"$valign\">";
				}

				if ($fieldtype == "select"){
					$select_text = $form->draw_select_input_field($tablefield);
					$output_text .= $select_text;
				} elseif ($fieldtype=="checkboxes"){
					$output_text .= $form->draw_multiple_checkbox_input_field($tablefield);
				
				} elseif ($fieldtype == "text" || $fieldtype=="mediumtext"){
					$output_text .= "<textarea name=\"id_" . $row_id . "_" . $tablefield . "\" rows=2 cols=30>" . $row[$tablefield] . "</textarea>";
					$EXPORT['rows'][$row_id][$tablefield]['input_field'] .= "<textarea name=\"id_" . $row_id . "-" . $tablefield . "\" rows=2 cols=30>" . $row[$tablefield] . "</textarea>";
				// text_as_text - this is the default though!
				// checkbox
				} elseif (($fieldtype=="checkbox" && $options['filter'][$fieldname]['field_config']=="bool") || ($fieldtype=="tinyint(1)" && $CONFIG['use_tinyint(1)_as_bool'])){
					$checkbox_text = $form->draw_checkbox_input_field($tablefield);
					$output_text .= $checkbox_text;
					$EXPORT['rows'][$row_id][$tablefield]['input_field'] .= $checkbox_text;
				// default
				} elseif ($fieldtype=="enum"){
					$EXPORT['rows'][$rowid][$fieldname]['input_field']=$form->draw_radio_input_field($fieldname,$fieldvalues[$fieldname]);
					$output_text .= $EXPORT['rows'][$rowid][$fieldname]['input_field'];
				} elseif ($fieldtype == "date"){
					$selected_date="";
					if (!$row[$tablefield] && $options['filter'][$tablefield]['default_prefill_value']){if ($options['filter'][$tablefield]['default_prefill_value']=="{=today}"){$options['filter'][$tablefield]['default_prefill_value']=date("Y-m-d",time());} $selected_date=$options['filter'][$tablefield]['default_prefill_value'];} else {$selected_date=$row[$tablefield];}
					$date_options=$options['filter'][$fieldname];
					//$EXPORT['rows'][$row_id][$tablefield]['input_field'] .= self::date_input_field($selected_date,$row_id,$fieldname,$date_options);
					$EXPORT['rows'][$row_id][$tablefield]['input_field'] .= $form->draw_date_input_field($fieldname);
					$output_text .= $EXPORT['rows'][$row_id][$tablefield]['input_field'];
				} elseif ($fieldtype == "datepicker") {
					$date_options=$options['filter'][$fieldname];
					if ($row[$tablefield]=="0000-00-00"){ $row[$tablefield]=""; }
					$EXPORT['rows'][$row_id][$tablefield]['input_field'] .= self::datepicker_input_field($row[$tablefield],$row_id,$fieldname,$date_options);
					$output_text .= self::datepicker_input_field($row[$tablefield],$row_id,$fieldname,$date_options);
				} else if ($fieldtype == "hidden"){
					$EXPORT['rows'][$row_id][$tablefield]['input_field'] .= $form->draw_hidden_input_field($tablefield); 
					$output_text .= $EXPORT['rows'][$row_id][$tablefield]['input_field'];
				} else if ($options['filter'][$tablefield]['field_type'] == "file"){
					$EXPORT['rows'][$row_id][$tablefield]['input_field'] .= $form->draw_file_input_field($tablefield); 
					$output_text .= $EXPORT['rows'][$row_id][$tablefield]['input_field'];
				} else {
					$EXPORT['rows'][$row_id][$tablefield]['input_field'] = $form->draw_default_input_field($tablefield);
					$output_text .= $EXPORT['rows'][$row_id][$tablefield]['input_field'];
				}
				if ($fieldtype != "hidden"){ $output_text .= "</td>";}
			}
			if ($options['filter']['include_delete_option']){
				$libpath=$CONFIG['libpath'];
				require_once("$libpath/classes/core/tables.php");
				$tables=new tables;
				if ($tables->one_to_many_relationship($tablename)){$has_child_records=2;}
				$output_text .= "<td><a href=\"Javascript:deleterow(" . $row_id . ",'$has_child_records')\"><img src=\"".SYSIMGPATH."/application_images/button_trash_beige_29x28.png\" border=0></a></td>";
				$EXPORT['rows'][$row_id][$fieldname]['delete_option'] = "<a href=\"Javascript:deleterow(" . $row_id . ",'$has_child_records')\" class=\"a_deletrow\">X</a>";
			} else {
				$output_text .= "<td><!--No Delete option//--></td>";
			}
			$output_text .= "</tr>\n";
		}
		if ($running_total){
			$output_text .= "<input type=\"hidden\" name=\"column_total\" value=\"$running_total\">";
		} else {
			$output_text .= "<input type=\"hidden\" name=\"column_total\" value=\"0\">";
		}	
		// now print new row for adding data
		if ($add_data==1){
			$output_text .= "<tr>";
			foreach ($fields_to_display_arr as $tablefield){
				$fieldtype=$table_description[$tablefield];
				$size=$default_size;
				if ($tablefield==$pk || strpos(stristr($fieldtype,"int("))){$size=3;}
				if (stristr($fieldtype,"int(")){$size=3;}
				if (stristr($fieldtype,"float")){$size=4;}
				if (stristr($fieldtype,"imal")){ $size=4; }
				if ($options['filter'][$tablefield]['field_type']){$fieldtype=$options['filter'][$tablefield]['field_type'];}
				if ($options['filter'][$tablefield]['field_type_for_add_in_multi_edit']){$fieldtype=$options['filter'][$tablefield]['field_type_for_add_in_multi_edit'];}
				$obj_fieldname="new_" . $tablefield;
				$form->add_to_field($tablefield,"name",$obj_fieldname);
				$form->add_to_field($tablefield,"value","");
				$form->options['filter'][$tablefield]['field_size']=$size; 
				if ($fieldtype != "hidden"){
					$output_text .= "<td>";
				}
				if ($tablefield == $pk){
					$output_text .= "<span class='new_record'>ADD NEW:</span>";
				} else if ($fieldtype == "select"){
					$EXPORT['fields'][$tablefield]['input_field'] .= $form->draw_select_input_field($tablefield);
				} else if ($fieldtype == "text" || $fieldtype=="mediumtext") {
					$EXPORT['fields'][$tablefield]['input_field'] .= "<textarea name='new_" . $tablefield . "' rows='2' cols='30'></textarea>";
				} else if ($fieldtype == "hidden"){
					if ($options['filter'][$tablefield]['default_prefill_value']){$insert_value=Codeparser::parse_request_vars($options['filter'][$tablefield]['default_prefill_value']);}
					$EXPORT['fields'][$tablefield]['input_field'] .= "<input type='hidden' name='new_" . $tablefield . "' value='$insert_value'>";					
				} else if ($fieldtype == "date"){
					$selected_date="";
					if ($options['filter'][$tablefield]['default_prefill_value']){
					if ($options['filter'][$tablefield]['default_prefill_value']=="{=today}"){
						$options['filter'][$tablefield]['default_prefill_value']=date("Y-m-d",time());
					} 
					$selected_date=$options['filter'][$tablefield]['default_prefill_value'];
					} else {
						$selected_date="";
					}
					//$EXPORT['fields'][$tablefield]['input_field'] = self::date_input_field($selected_date,$rowid,$fieldname,$date_options);
					$EXPORT['fields'][$tablefield]['input_field'] = $form->draw_date_input_field($fieldname);
				} else if (($fieldtype=="checkbox" && $options['filter'][$tablefield]['field_config']=="bool") || ($fieldtype=="tinyint(1)" && $CONFIG['use_tinyint(1)_as_bool'])){
					$checkbox_text = $form->draw_checkbox_input_field($tablefield);
					$EXPORT['fields'][$tablefield]['input_field'] = $checkbox_text;
				} else {
					$EXPORT['fields'][$tablefield]['input_field'] .= $form->draw_default_input_field($tablefield);
				}
				$output_text .= $EXPORT['fields'][$tablefield]['input_field'];
				if ($fieldtype != "hidden"){
					$output_text .= "</td>";
				}
			}
		}
		$output_text .= "</tr>\n";
		$output_text .= "<tr><td></td><td>";
		$button_text="Save";
		if ($options['filter']['submit_button_text']){$button_text=$options['filter']['submit_button_text'];}
		if ($formtype=="edit_all" && $_REQUEST['relation_id'] && $_REQUEST['relation_key']){
			$output_text .= "<input type=\"hidden\" name=\"relation_id\" value=\"".$_REQUEST['relation_id']."\">";
			$output_text .= "<input type=\"hidden\" name=\"relation_key\" value=\"".$_REQUEST['relation_key']."\">";
		}		
		if($button_text){
			$EXPORT['submit_button'] .= "<input type=\"submit\" value=\"$button_text\" class=\"dbf_submit_button\">";
		} else {
			$EXPORT['submit_button'] .= "<input type=\"image\" src=\"/".SYSIMGPATH."/application_images/save_beige_43x39.png\">\n";
		}
			$EXPORT['submit_button'] .= "<input type=\"hidden\" name=\"after_update_page_element\" value=\"\">";
		//if ($options['filter']['save_and_continue_button']){
			$EXPORT['submit_continue_edit_button'] .= "<input type=\"image\" src=\"/".SYSIMGPATH."/application_images/save_edit_beige_43x39.png\" onClick=\"document.forms['".$form_name."'].elements['after_update_page_element'].value='continue';\" alt=\"Save and continue editing\" title=\"Save and continue editing\">\n";
		//}

		if ($formtype=="edit_all"){
				$EXPORT['submit_continue_edit_button'] = "";
		}

		$output_text .= $EXPORT['submit_button']; 

		if ($EXPORT['submit_continue_edit_button']){ $output_text .= " " .$EXPORT['submit_continue_edit_button']; }

	
	} else {
		// SINGLE ROWS ONLY: From here type is either add_row or edit_row or possibly the new add_or_edit
		// load table headers
		$rowcount=0;
		if ($rowid_for_edit){
			if (preg_match("/^\w+ ?: ?\w+$/",$rowid_for_edit)){
				@list($lookup_field,$lookup_value)=explode(":",$rowid_for_edit);
				$lookup_field=trim($lookup_field);
				$rowid_for_edit=trim($lookup_value);
			} else {
				$lookup_field=$pk;
			}
			if (preg_match("/^\w+ ?:$/",$rowid_for_edit)){
				print "<p class=\"dbf_para_alert\">Please save the master record before entering any data into here - thanks.</p>";
			}
			$data_query="SELECT * from " . $tablename . " WHERE $lookup_field = '" . $rowid_for_edit . "'";
			$result=$db->query($data_query);
			$rows_of_data_returned_from_query=$db->num_rows($result);
			if ($use_system_messages && $db->num_rows($result)==0){ $output_text .= "<tr bgcolor=\"#f9f9f9\"><td colspan=\"" . $rowcount . "\"><font color='cc0000'>Note: table is currently empty</font></td></tr>"; }

			$fieldvalues=array();
			while ($row=$db->fetch_array($result)){
				$row_id=$row[$pk];
				$output_text .= "<tr>";
				foreach ($table_fields as $tablefield){
					$fieldvalues[$tablefield]=$row[$tablefield];
					$form->add_to_field($tablefield,"value",$row[$tablefield]);
					$repstring="{=".$tablefield."}";
					if (!$EXPORT['title_text']){
						$options['filter']['title_text']=str_replace($repstring,$row[$tablefield],$options['filter']['title_text']);
						$EXPORT['title_text']=$db->db_escape($options['filter']['title_text']);
					}
				}
			}
		}
		// editing a row and no filter?
		if (!$options['filter']['title_text']){
			$done_title=0;
			foreach ($table_description as $fieldname => $fieldtype){
				if ($fieldname=="name" || $fieldname == "title" && !$done_title){
					$EXPORT['title_text'] .= " : ";
					$EXPORT['title_text'] .= $fieldvalues[$fieldname]; 
					$done_title=1;
				}
			}
		}

		foreach ($table_description as $fieldname => $fieldtype){
			$no_output_text=0;
			if ($fieldtype=="timestamp" && ($fieldname=="date_created" || $fieldname=="date_updated")){ $no_output_text=1; }
			$names=explode("_",$fieldname);
			$formatted_fieldname=array();

			foreach ($names as $name){
				//$name = ucfirst($name);
				array_push ($formatted_fieldname, $name);
				$rowcount++;
			}

			$new_fieldname= ucfirst(implode(" ", $formatted_fieldname));
			if (strpos($new_fieldname," Id")>1 && $options['filter'][$fieldname]['field_type']=="select"){
				$new_fieldname = str_replace(" Id","",$new_fieldname);
			}	
			if ($options['filter'][$fieldname]['fieldname_text']){ $new_fieldname=$options['filter'][$fieldname]['fieldname_text']; }
			$size=40;		
			$readonly="";
			$updatebutton="";
			if ($fieldname==$pk){$form->add_to_field_filter($fieldname,"readonly"," disabled"); $form->add_to_field_filter($fieldname,"field_size",3); $updatebutton="<input type=\"image\" src=\"".SYSIMGPATH."/application_images/save_beige_43x39.png\">"; $updatebutton="";}
			if (stristr($fieldtype,"int(")){$form->add_to_field_filter($fieldname,"field_size",3);}
			if (stristr($fieldtype,"bigint(")){$form->add_to_field_filter($fieldname,"field_size",20);}
			if (stristr($fieldtype,"float")){$form->add_to_field_filter($fieldname,"field_size",3);}
			if (stristr($fieldtype,"decimal")){$form->add_to_field_filter($fieldname,"field_size",4);}
			if (stristr($fieldtype,"enum")){$fieldtype=$form->options_from_enum_type($fieldname,$fieldtype);}
			if (stristr($fieldtype,"char(1)")){$form->add_to_field_filter($fieldname,"field_size",2);}
			if (stristr($fieldtype,"varchar(")){
				$vcval=str_replace("varchar(","",$fieldtype);
				$vcval=str_replace(")","",$vcval);
	/*
                                if (is_numeric($vcval)){
                                        if ($vcval<120){
                                                $fieldtype="text";
                                        } else if ($vcval>120 && $vcval <=249){
                                                $rowval=2;
                                                $colval=40;
                                        } else if ($vcval>250 && $vcval <=449){
                                                $rowval=3;
                                                $colval=50;
                                        } else {
                                                $rowval=4;
                                                $colval=60;
                                        }

					$form->add_to_field_filter($fieldname,"style_editor","0");
					$form->add_to_field_filter($fieldname,"textarea_rows",$rowval);
					$form->add_to_field_filter($fieldname,"textarea_cols",$colval);
				}
*/

			}
			


			if ($options['filter']['display_fields']){
				if ($options['filter']['display_fields']=="ALL"){
					$hide_field=0;
					$fields_to_display=array();
					$fields_to_display="," . $options['filter']['display_fields'] . ",";
					if (!strlen(strpos($fields_to_display,",$fieldname,"))){$hide_field=1;};
				}
			}

			if (!$hide_field){// if not hiding field, print it
				$cell_align="middle";
				if ($options['filter']['names_on_separate_lines']=="1"){$next_cell="</td></tr><tr><td colspan=2 valign=\"$cell_align\">";}else{$next_cell="</td><td valign=\"$cell_align\">";}
				if ($fieldtype == "text" || $fieldtype == "mediumtext"){$cell_align="top";}
				if ($options['filter'][$fieldname]['fieldname_text']){$field_text=$options['filter'][$fieldname]['fieldname_text'];} else {$field_text=$new_fieldname;}
				if ($fieldname != "id" && $options['filter'][$fieldname]['field_type'] != "hidden" && !$no_output_text){$output_text .= "<tr><td align=\"left\" valign=\"$cell_align\" align=\"right\"><b>" . $field_text. ":</b> $next_cell";} else {} 
				$EXPORT[$fieldname]['formatted_fieldname']=$new_fieldname;
				$EXPORT[$fieldname]['value']=$fieldvalues[$fieldname];
				$EXPORT[$fieldname]['input_field']="";
				$fieldname_with_id = "id_" . $rowid_for_edit . "_" . $fieldname;	
				$fieldname_for_new = "new_" . $fieldname;	
				if ((preg_match("/add/",$formtype) && !$rowid_for_edit) || $rows_of_data_returned_from_query==0 && $formtype=="add_or_edit"){$fieldname_with_prefix = $fieldname_for_new;} else {$fieldname_with_prefix = $fieldname_with_id;}	
				$form->make_field($fieldname,$fieldname_with_prefix);
				// load options
				if ($options['filter'][$fieldname]){
					if ($options['filter'][$fieldname]['field_type']){$fieldtype=$options['filter'][$fieldname]['field_type'];}
					if ($options['filter'][$fieldname]['field_value_always']){
						$fieldvalues[$fieldname]=$options['filter'][$fieldname]['field_value_always'];
						$form->add_to_field_filter($fieldname,"field_value_always",$options['filter'][$fieldname]['field_value_always']);
					}
					if ($fieldvalues[$fieldname]=="user_data_from_cookie('id')"){global $user; $fieldvalues[$fieldname]=$user->value("id");}
				}

				// do we have a set of previous values that have been entered into a form erroneously?
				if ($options['filled_in_values'][$fieldname]){
					$fieldvalues[$fieldname]=$options['filled_in_values'][$fieldname];
					$form->add_to_field($fieldname,"value",$options['filled_in_values'][$fieldname]);
				}

				$form->add_to_field($fieldname,"db_field_type",$fieldtype);

				if ($fieldtype == "select"){
					$EXPORT[$fieldname]['input_field']=$form->draw_select_input_field($fieldname);
					$output_text .= "<b>" . $EXPORT[$fieldname]['input_field'] . "</b>";
					if ($form->get_field_data($fieldname,'override_value')){
						$EXPORT[$fieldname]['formatted_value']= $form->get_field_data($fieldname,'override_value');
					}
				} elseif ($fieldtype == "select_or_upload"){
					$EXPORT[$fieldname]['input_field']=$form->draw_select_or_upload_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// dynamic list
				}  elseif ($fieldtype == "dynamic_list"){
					$EXPORT[$fieldname]['input_field']=$form->draw_dynamic_list_input_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// dbf-imagepicker
				} elseif ($fieldtype == "dbf_imagepicker"){
					$EXPORT[$fieldname]['input_field']=$form->draw_dbf_image_picker_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// dbf-imagepicker with uploiad
				} elseif ($fieldtype == "dbf_imagepicker_with_upload"){
					$EXPORT[$fieldname]['input_field']=$form->draw_imagepicker_with_upload_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// mui-imagepicker
				} elseif ($fieldtype == "mui_imagepicker"){
					$EXPORT[$fieldname]['input_field']=$form->draw_mui_image_picker_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// file
				} elseif ($fieldtype == "file"){
					$EXPORT[$fieldname]['input_field']=$form->draw_file_input_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];	
				// textarea
				} elseif ($fieldtype == "text" || $fieldtype=="mediumtext" || $fieldtype == "textarea") {
					$EXPORT[$fieldname]['input_field']=$form->draw_textarea_input_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// enum to radio buttons
				} elseif ($fieldtype == "enum"){
					$EXPORT[$fieldname]['input_field']=$form->draw_radio_input_field($fieldname,$fieldvalues[$fieldname]);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// id fieldname gets disabled.. !
				} elseif ($fieldname=="id"){ // change to $pk
					$output_text .= $EXPORT[$fieldname]['input_field'] .= "<input type=\"hidden\"" . $readonly . " name=\"$fieldname_with_prefix\" value=\"$fieldvalues[$fieldname]\" size=\"$size\">";
				// multiple text fields version of date
				//} elseif ($fieldtype =="multiple_text_fields" && $options['filter'][$fieldname]['field_config']=="date"){
				} elseif ($fieldtype =="multiple_text_fields"){
					$EXPORT[$fieldname]['input_field'] .= $form->draw_multiple_textfield_input_field($fieldname); 
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// multiple select 
				} elseif ($fieldtype =="multiple_select_fields"){
					$EXPORT[$fieldname]['input_field'] .= $form->draw_multiple_select_input_field($fieldname); 
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// hidden
				} else if ($fieldtype=="hidden") {
					$EXPORT[$fieldname]['input_field'] .= $form->draw_hidden_input_field($fieldname); 
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// checkbox
				} else if (($fieldtype=="checkbox" && $options['filter'][$fieldname]['field_config']=="bool") || ($fieldtype=="tinyint(1)" && $CONFIG['use_tinyint(1)_as_bool'])){
					$EXPORT[$fieldname]['input_field']=$form->draw_checkbox_input_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// date or datetime
				} else if ($fieldtype=="date" || $fieldtype=="datetime") {
					$EXPORT[$fieldname]['input_field']=$form->draw_date_input_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
					if ($EXPORT[$fieldname]['value']=="0000-00-00"){
						$EXPORT[$fieldname]['formatted_value']="0000-00-00";
						$EXPORT[$fieldname]['value']="";
					}
				// datepicker
				} else if ($fieldtype=="datepicker"){
					$date_options=$options['filter'][$fieldname];
					$EXPORT[$fieldname]['input_field'] .= self::datepicker_input_field($fieldvalues[$fieldname],$rowid,$fieldname_with_prefix,$date_options);
					$output_text .= "<td valign=\"$valign\">".self::datepicker_input_field($fieldvalues[$fieldname],$rowid,$fieldname_with_prefix,$date_options);
				// password
				} else if ($fieldtype=="password"){
					$EXPORT[$fieldname]['input_field']=$form->draw_password_input_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// display_only
				} else if ($fieldtype=="display_only"){
					$EXPORT[$fieldname]['input_field']=$form->draw_display_only_input_field($fieldname);
					//$EXPORT[$fieldname]['input_field'] .= $fieldvalues[$fieldname];
					$output_text .= $EXPORT[$fieldname]['input_field'];
				// default
				} else if ($fieldtype=="timestamp"){
					$EXPORT[$fieldname]['input_field'] .= $form->draw_default_input_field($fieldname);
					if (!$no_output_text){
						$output_text .= $EXPORT[$fieldname]['input_field'];
					}
				} else {
					$EXPORT[$fieldname]['input_field'] .= $form->draw_default_input_field($fieldname);
					$output_text .= $EXPORT[$fieldname]['input_field'];
				}
				if ($fieldname != $pk && $fieldtype != "hidden"){$output_text .= "</td><td>$updatebutton</td></tr>\n";}
			}
		}
		
		$output_text .= "<tr><td colspan=\"2\" style=\"height:5px\"></td></tr>";
		$output_text .= "<tr><td></td><td style=\"background-color:#dddddd\">";
		// need to add hidden form fields for relation keys if they exist on edit single
		if (($formtype=="edit_single" || $formtype=="add_row") && $_REQUEST['relation_id'] && $_REQUEST['relation_key']){
			$output_text .= "<input type=\"hidden\" name=\"relation_id\" value=\"".$_REQUEST['relation_id']."\">";
			$output_text .= "<input type=\"hidden\" name=\"relation_key\" value=\"".$_REQUEST['relation_key']."\">";
		}		

		$EXPORT['reset_button']=$form->draw_form_reset_button();
		$output_text .= "<div class=\"dbf_form_reset_button\">" . $EXPORT['reset_button'] . "</div>";

		$EXPORT['submit_button']=$form->draw_form_submit_button();
		$EXPORT['save_and_continue_button']=$form->draw_save_and_continue_button();
		if ($formtype=="add_row"){
			$EXPORT['save_and_add_button']=$form->draw_save_and_add_button();
		}
		$EXPORT['submit_continue_edit_button']=$EXPORT['save_and_continue_button'];
		$output_text .= "<div class=\"dbf_save_continue_button\">" . $EXPORT['submit_button'] . " " . $EXPORT['save_and_continue_button'] . " " . $EXPORT['save_and_add_button'] . "</div>";

		// what on earth are these 2 below?!
		if ($options['filter']['include_delete_button']){
			$output_text .= "<input type=\"button\" class=\"general_button\" value=\"" . $options['filter']['include_delete_button'] . "\">";
		}
		if ($options['filter']['add_button_text'] && $options['filter']['add_button_url']){
			$options['filter']['add_button_url']=str_replace("{=id}",$_REQUEST['rowid'],$options['filter']['add_button_url']);
			$output_text .= "<input type=\"button\" class=\"general_button\" value=\"" . $options['filter']['add_button_text'] . "\" onClick=\"goToUrl('" . $options['filter']['add_button_url'] . "')\">";
		}
		$output_text .= "</td></tr>\n";
			// print current data into form
	}
	$output_text .= "</table></div></form>";

	// OUTPUT THE TEXT EITHER STRAIGHT OR THROUGH A FILTER
	if (!$options['filter']['display_in_template'] && !$options['filter']['display_in_admin_template']){
		if ($options['filter']['export']=="html"){ 
			return $output_text;
		} else {
			print $output_text;
		}
	} else {
		if ($options['filter']['display_in_template']){ $template_source="templates"; }
		if ($options['filter']['display_in_admin_template']){ $template_source="admin_templates"; }
		$template=$form->add_form_data_to_template($EXPORT,$template_source);
                if ($options['filter']['export']=="html"){
			$return = "<!-- DBF PRESERVE TAGS //-->";
                        $return = $form->form_header();
                        $return .= $template;
			$return .= $form->form_footer();
			$return .= "<!-- CLOSE DBF PRESERVE TAGS //-->";
                        return $return;
                } else {
                        print $form->form_header();
                        print $template;
                }
	}
}

//////////////////////////////////////////////////////////
// process_update_table					//
// Updates a table by both inserting and updating rows	//
//////////////////////////////////////////////////////////
static function process_update_table($options){
	// set global vars, initialise arrays, load table structure
	$edit_type=$_POST['edit_type'];
	if ($edit_type != "add_row" && $edit_type != "edit_single" && $edit_type != "add_or_edit" && $edit_type != "edit_all"){
	format_error("The specified edit type does not exist in the current version, or edit type was not supplied. Edit type was '$edit_type'.",1);
	}
	global $db;
	$tablename = $db->db_escape($_POST['tablename']);
	$form_identifier=$db->db_escape($_POST['dbf_sys_form_id']);
	if (!$options['filter']['no_internal_form_checking'] && $CONFIG['enable_form_logging']){
		$check_form_integrity=check_web_form_integrity($form_identifier,$tablename,$db->db_escape($_POST['edit_type']),$options['filter']['dbf_filter_id'],$db->db_escape($_POST['rowid_for_edit']));
		if (!$check_form_integrity){
			if (!$form_identifier) { $extramsg="No form identifier was sent through";}
			format_error("Internal form check error - cannot update. $tablename, $form_identifier. $extramsg",1);
		}
	}
	// if corresponds to directory also need to save the file out again
	$to_sql="SELECT * from table_options where table_name= \"$tablename\"";
	$to_res=$db->query($to_sql);
	while ($h=$db->fetch_array($to_res)){
		if ($h['table_option']=="update_type" && $h['option_value']=="immediate"){$update_corresponding_file=1;}
		if ($h['table_option']== "corresponds_to_directory"){ $corresponds_to_directory=$h['option_value'];}
		if ($h['table_option']== "field_as_filename"){ $field_as_filename=$h['option_value']; }
		if ($h['table_option'] == "field_as_contents"){ $field_as_contents=$h['option_value']; }
		if ($h['table_option'] == "file_extension"){ $file_extension=$h['option_value']; }
	}

	$table_fields=array();
	$field_types=array();
	$sql = "DESC " . $tablename;
	$result=$db->query($sql);
	while ($row=$db->fetch_array($result)){
		array_push($table_fields,$row['Field']);
		array_push($field_types,$row['Type']);
		$table_description[$row['Field']]=$row['Type'];
		if ($row['Key']=="PRI"){$pk=$row['Field'];}
		if ($row['Key']=="PRI" && $row['Extra']=="auto_increment"){$autoinc_pk=1;}
	}		



	// process inserts first	
	if ($edit_type=="add_row" || $edit_type=="add_or_edit" || $edit_type=="edit_all"){
		$insert=1;
		$have_one_field_value=0;
		$field_errors=array();
		$unique_field_errors=array();
		$unique_field_error_messages=array();
		$form_error_messages=array();

		/*
		$all_insert_fields=list_fields_in_table($tablename);
		$insert_fields_array=array();
		foreach ($all_insert_fields as $insert_field){
			$post_data_name="new_" . $insert_field; 
			if ($_POST[$post_data_name]){
				array_push($insert_fields_array,$insert_field);
			}
		}
		print_r($all_insert_fields);
		print_r($insert_fields_array);
		*/


		$insert_sql="INSERT INTO " . $tablename . " ";  
		$inputarray=array();
		$insert_fields_array=array();
		foreach ($table_description as $tablefield => $table_field_type){

			if ($tablefield == $pk && $autoinc_pk){continue;} // Now this is done because its an auto incrementing field

			// load previous_values
			$options['filled_in_values'][$tablefield]=$_POST[$inputdata];

			// reset inputstring var for each field
			$inputstring=""; 

			$inputdata = "new_" . $tablefield;

			if ($options['filter']['save_as_new_name_from_field']){$inputdata="id_" . $_REQUEST['rowid_for_edit'] . "_" . $tablefield;}
			// note the next line has been altered to always allow inserts whether all fields are filled in or not. See comment on line below for how to deal with this properly
			if (!$_POST[$inputdata]){$insert=1;} // any blank field and we don't insert, this needs to change by reading table again
			if ($_POST[$inputdata]){
				if ($options['filter'][$tablefield]['do_not_store_field']){
					continue;	
				}
				array_push($insert_fields_array,$tablefield);
				// Ensure there is at least one field or no point in inserting anything (for now at least) 
				$have_one_field_value++;
				//print "Got one field value and its for $inputdata with a value of " . $_POST[$inputdata]."<br>";

					if ($_REQUEST['relation_key'] && $_REQUEST['relation_id']){
						$relation_id_field=$db->field_from_record_from_id("table_relations",$_REQUEST['relation_id'],"field_in_table_2");
						if ($relation_id_field==$tablefield){ 
		//					print "This is from a disabled select<br>";
							$have_one_field_value--;
						}
					} else if ($options['filter'][$tablefield]['default_prefill_value']) {
						$have_one_field_value--; // default prefill but nothing else? NOW there may be instances when we need this? hmm. An override filter key may be required at some point.
					}
				$inputstring = "\"" . $db->db_escape($_POST[$inputdata]) . "\""; 
				if ($table_field_type == "timestamp" && $tablefield == "date_updated"){$inputstring="NULL";} // add a null to insert current date
				// if we have a file, process the upload file and return the saved name
				if ($_FILES[$inputdata] && $_FILES[$inputdata]['tmp_name']){
					$inputstring = "\"" . addslashes(self::process_file_upload($inputdata,$options)) . "\"";
				} else if ($_FILES[$inputdata] && !$_FILES[$inputdata]['tmp_name']){
					//$vardump=var_export($_FILES);
					//print format_error("File upload error: A temporary name has not been generated, file cannot be uploaded. This is a server error, please contact your host or sys admin and send the data below:<br /><br />Tmp name: " . $_FILES[$inputdata]['tmp_name'] . "<br /><pre>" . $vardump . "</pre>",0);
				}

				// multiple field inputs? Process here
				if ($options['filter'][$tablefield]['field_type']=="multiple_text_fields" || $options['filter'][$tablefield]['field_type']=="multiple_select_fields"){
					$number_of_fields=$options['filter'][$tablefield]['field_quantity'];
					$mfq=0;
					$multiple_field_input_array=array();
					do {
						$mf_fieldname = "new_" . $tablefield . "-" . $mfq;
						array_push($multiple_field_input_array,$_POST[$mf_fieldname]);
						$mfq++;
					} while ($mfq < $number_of_fields);	
					$inputstring = "\"" . implode($options['filter'][$tablefield]['field_delimiter'],$multiple_field_input_array) . "\"";
				}	
				if ($table_field_type=="date" || $table_field_type=="datetime" && !$_POST[$inputdata]){
					$fieldname_add_year="new_" . $tablefield . "-3";
					$fieldname_add_month="new_" . $tablefield . "-2";
					$fieldname_add_day="new_" . $tablefield . "-1";
					$date_add=$_POST[$fieldname_add_year].$_POST[$fieldname_add_month].$_POST[$fieldname_add_day];
					$inputstring = "\"" . $date_add. "\"";
				}
			
				if ($options['filter'][$tablefield]['field_type']=="password" && $options['filter'][$tablefield]['password_hash_type']=="md5"){
					$inputstring = "\"" . md5($_POST[$inputdata]) . "\"";
				}
				if ($options['filter'][$tablefield]['field_type']=="password" && $options['filter'][$tablefield]['password_hash_type']=="sha1"){
					$inputstring = "\"" . sha1($_POST[$inputdata]) . "\"";
				}

				// dynamic list below but only if its a key,value lookup, otherwise it will be fine as it is
				if ($options['filter'][$tablefield]['field_type']=="dynamic_list" && $_POST[$inputdata]){
					//print "we are on a dynamic list here!";
					// do a reverse sql lookup on the name
					if (preg_match("/SELECT \w+ ?, ?\w+  ?FROM/i",$options['filter'][$tablefield]['select_value_list'])){// this is for id+key lookups only, if its just one list then its fine to add the text as it was
						$sql=$options['filter'][$tablefield]['select_value_list'];
						$sql=trim(str_replace("SQL:","",$sql));
						$sqlbits=preg_split("/ FROM /i",$sql);
						$sqlbits[0]=preg_replace("/SELECT /i","",$sqlbits[0]);
						$id_and_name=explode(",",$sqlbits[0]);
						$id_field=trim($id_and_name[0]);
						$name_field=trim($id_and_name[1]);
						$lookup_table=$sqlbits[1];
						$lookup_table = preg_replace("/ ORDER BY \w+,?\w+?/i","",$lookup_table);
						$sql="SELECT $id_field FROM $lookup_table WHERE $name_field = \"" . $_POST[$inputdata] . "\"";
						print "Dynlist: running $sql<br>";
						$dynamic_res=$db->query($sql);
						$rows_returned=$db->num_rows($dynamic_res);
						//print "rows returned is $rows_returned";
						if ($rows_returned==1){
							while ($h=$db->fetch_array($dynamic_res)){
								$field_value=$h[$id_field];
								//print "set field value to $field_value";
							}
						} else if ($rows_returned>1){
							// ambiguous entry, needs to generate an error
							array_push($form_error_messages, "The text entered was ambiguous (could relate to more than one value) for the field: $tablefield.");
						} else if ($rows_returned ==0){
							// an addition
							if ($options['filter'][$tablefield]['dynamic_list_allow_record_add']){
								// add the data to the source table
								$add_dynlist_sql="INSERT INTO $lookup_table ($name_field) values(\"".$_POST[$inputdata]."\")";
								$add_dynlist_res=$db->query($add_dynlist_sql);
								$dynlist_insert_id=$db->last_insert_id();
								//print "added record of $dynlist_insert_id";
								$field_value=$dynlist_insert_id;
							} else {
								array_push($form_error_messages, "The text entered must be in the list for the field: $tablefield.");
								$form_error_messages_present=1;
							} //end if add key
						}
						$inputstring = "\"" . $field_value . "\"";
					}
				}
			} 


			// validate entry for non multiple text fields
			if ($options['filter']['required_field_list']){
				// is the field required and is a value present?
				if (stristr($options['filter']['required_field_list'],$tablefield)){
					if (!$_POST[$inputdata]){
						array_push($field_errors,$tablefield);
					}
				}
			}

			// check for unique field values
			if ($options['filter'][$tablefield]['unique_value_if_value_present'] && $_POST[$inputdata]){
				$options['filter'][$tablefield]['field_contains_unique_value']=1;
			}
			if ($options['filter'][$tablefield]['field_contains_unique_value']){
				$check_unique_sql="SELECT id from $tablename WHERE $tablefield = \"" . $_POST[$inputdata] . "\"";
				$check_unique_res=$db->query($check_unique_sql) or format_error($db->db_error());
	 //                       print "Checking $check_unique_sql<p>";
				if ($db->num_rows($check_unique_res)>=1){
					array_push($unique_field_errors,$tablefield);
									if ($options['filter'][$tablefield]['status_message_unique_error']){
						array_push($unique_field_error_messages,$options['filter'][$tablefield]['status_message_unique_error']);
					}
					$got_unique_field_error=1;
				}
			}

			// check for validation on fields
			if ($options['filter'][$tablefield]['validate_entry']){
				if ($options['filter'][$tablefield]['validate_entry']=="email"){
					$options['filter'][$tablefield]['validation_regex']="[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+\.[a-zA-Z.]{2,6}$";
				} else if ($options['filter'][$tablefield]['validate_entry']=="posint"){
					$options['filter'][$tablefield]['validation_regex']="^\d+$";
				} else if ($options['filter'][$tablefield]['validate_entry']=="telephone"){
					$options['filter'][$tablefield]['validation_regex']="[0-9\+ ]+$";
				} else if ($options['filter'][$tablefield]['validate_entry']=="maxchars" && $options['filter'][$tablefield]['validation_max_chars']){
					$options['filter'][$tablefield]['validation_regex']="[.*]{1,".$options['filter'][$tablefield]['validation_max_chars'] . "}";
				} else if ($options['filter'][$tablefield]['validate_entry']=="image"){
					$options['filter'][$tablefield]['validation_regex']="[a-zA-Z0-9-_]+";
				} else if ($options['filter'][$tablefield]['validate_entry']=="used_in_url"){
					$options['filter'][$tablefield]['validation_regex']="[a-zA-Z0-9-_]+";
				}
			}

			if ($options['filter'][$tablefield]['validation_regex']){
				// does it match the regex
				$regex=$options['filter'][$tablefield]['validation_regex'];
				if (!preg_match("/$regex/",$_POST[$inputdata])){
					if ($options['filter'][$tablefield]['field_validation_error_message']){
						array_push($form_error_messages,$options['filter'][$tablefield]['field_validation_error_message']);
					} else {
						array_push($form_error_messages,"The value entered in " . ucfirst(str_replace("_"," ",$inputdata)) . " is not allowed.");
					}
					$form_error_messages_present=1;
				}
			}

			if ($inputstring && $tablefield){
				console_log("Adding array value for $inputstring for $tablefield<br>");
				array_push($inputarray,$inputstring);
			}
		}

		// Now if we have at least one field to insert, insert it (at least one, oh this so has to change fast)
		if ($insert && $have_one_field_value && !$field_errors && !$unique_field_errors && !$form_error_messages_present){
			
			$insert_sql .= "(".join($insert_fields_array,",").") VALUES (";
			$insert_sql .= implode(",", $inputarray);
			console_log($inputarray);
			$insert_sql .= ")";
			$insert_sql = str_replace("\"NOW()\"","NOW()",$insert_sql); // doesn't need to be quoted - wont work if it is
			$insert_sql = str_replace("\"current_datetime()\"","NOW()",$insert_sql); // doesn't need to be quoted - wont work if it is
			console_log($insert_sql); 
			$lock_query ="LOCK TABLES " . $tablename . " WRITE";
			$lock_result=$db->query($lock_query);
			$result=$db->query($insert_sql) or $dbf_insert_error=1;
			if ($dbf_insert_error){
				$dbf_insert_error_message=$db->db_error();
			}
			$max_pk_sql="SELECT MAX(id) AS LAST_ID FROM ".$tablename;
			$max_pk=$db->query($max_pk_sql);
			$max_pk=$db->fetch_array($max_pk);
			$unlock=$db->query("UNLOCK TABLES");
			if ($debug){print "Just run sql of " . $insert_sql . " and insert id is " . $last_insert_id;}
			if ($dbf_insert_error){
				print format_error("Error running insert statement - $dbf_insert_error_message<p>$insert_sql",0);
				$options['filter']['after_update']="repeat";
			} else {
				$record_added=1;
				$last_insert_id=$max_pk['LAST_ID'];
			}
			// do we have any files to upload?
			if ($_FILES){
				//process_file_upload();
			}

			if ($options['filter']['set_global_variable']){
				global $page;
				@list($gvar_name,$gvar_val,$more)=explode("=",$options['filter']['set_global_variable']);
				if ($more){ $gvar_val=$gvar_val."=".$more; }
				$page->set_global_var($gvar_name,$gvar_val);
				
			}
			if ($record_added==1){
				$datetimestring = date("d/m/Y : H:i:s", time());
				if (array_key_exists("status_message_success",$options['filter']) && !$options['filter']['status_message_success']){
					// this clears the status message
				} else if (array_key_exists("status_message_success",$options['filter'])){
					$status_message =  "<p class=\"dbf_para_success\"><span class=\"status_message_success\">" . str_replace("{=datetime}",$datetimestring,$options['filter']['status_message_success']) . "</span></p>";
				} else {
					$status_message =  "<p class=\"dbf_para_success\"><span class=\"status_message_success\">Record Added Succesfully at " . $datetimestring . "</span></p>";
				}
			} else {
					#print "An error has occurred adding this record. Please contact support if you require assistance with this problem.";
			}
				global $user;
				if ($user->value("type")=="user"){
					$status_message="";
				}
				if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
					open_status_message();
					print $status_message;
					close_col();
				}
		} else if ($field_errors) {
		//var_dump($field_errors);
			$status_message = "<p class=\"dbf_para_alert\"><b>The following required fields were not filled in:</b><br /><br /><font color=\"#111111\">";
			foreach ($field_errors as $display_field_error){
				if ($options['filter'][$display_field_error]['fieldname_text']){
					$print_field_error=$options['filter'][$display_field_error]['fieldname_text'];
				} else {
					$print_field_error=$display_field_error;
				}
				$status_message .= "&bull; " . ucfirst(str_replace("_"," ",$print_field_error)) . "<br />";
			}
			$status_message .= "</font><br />Please amend these fields in the form below and resend the form. Thank You<br /></p>\n";
			$options['filter']['after_update']="repeat";
			if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
				if (!$status_message_open){
					open_status_message();
					print $status_message;
					close_col();
				}
			}

		} else if ($unique_field_errors){
		if ($unique_field_error_messages){
				$status_message = "<p class=\"dbf_para_alert\">".$unique_field_error_messages[0]."</p>";
			} else {
				 $status_message = "<p class=\"dbf_para_info\"><b>This form cannot be processed as a duplicate entry has been found on the following fields: <br /><br />&bull; " . implode(", ",str_replace("_"," ",$unique_field_errors)) . "</b><br /><br />These fields require unique values and the same values entered here already exist in other records.<br /><br />Please amend these fields in the form below and resend the form. Thank You</p>\n";

			}
			$options['filter']['after_update']="repeat";
			if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
				if (!$status_message_open){
					open_status_message();
					print $status_message;
					close_col();
				}
			}

		} else if ($form_error_messages_present){
			$options['filter']['after_update']="repeat"; // was cancel
			// done later? $add_to_content=form_from_table($tablename,$_POST['edit_type'],$_POST['rowid_for_edit'],$_POST['add_data'],$options);
			foreach ($form_error_messages as $form_error_message){
				$status_message .= "<p class=\"dbf_para_alert\">$form_error_message</p>";
			}

			if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
				if (!$status_message_open){
					open_status_message();
					print $status_message;
					close_col();
				}
			}
		} else {
			if ($edit_type=="edit_all"){
				$status_message="<p class=\"dbf_para_neutral\">No new records were added.</p>";
			} else {
				$status_message="<p class=\"dbf_para_alert\">Record not added - no data to add.</p>";
			}
			if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
				if (!$status_message_open){
					open_status_message();
					print $status_message;
					close_col();
				}
			}
			if ($debug){ print "No Rows to insert<p>\n";}
		}
		
	}

	//
	//
	// process updates as long as were not saving as a new name from existing data, and also if we're updating and not adding!	
	//
	//
	if (!$options['filter']['save_as_new_name_from_field'] && ($edit_type=="edit_single" || $edit_type=="edit_all" || $edit_type=="add_or_edit")){

		$form_error_messages=array();
		$field_errors_on_update=array();

		global $status_message_open;
		$tabledata = array();
		$update_sql="UPDATE " . $tablename . " SET ";	
		//var_dump($_POST);
		//var_dump($_FILES);
		$debug=0;
		foreach ($_POST as $varname => $varvalue){
			if ($debug){print "<hr>on " . $varname . " = " . $varvalue . "<p>";}
			if (!preg_match("/id_/",$varname)){continue;}
			$parts=explode("_",$varname);
			$discard=array_shift($parts);
			$rowid=array_shift($parts);
			// the following row is where a pk is hard coded. Cant work out what this does
			if (!is_numeric($rowid)){continue;}
			$actual_fieldname = implode("_",$parts);
			$actual_fieldname_data = $actual_fieldname; 
			if (preg_match("/-\d+$/",$actual_fieldname)){
				$actual_fieldname=preg_replace("/-\d+$/","",$actual_fieldname);
			}
			$tabledata[$rowid][$actual_fieldname].=$varvalue;	
			if ($actual_fieldname == $field_as_contents){ $file_content_data = $varvalue; }
			if ($actual_fieldname == $field_as_filename){ $file_to_write_external = $varvalue; }
			if ($options['filter'][$actual_fieldname]['field_config']=="password" && $varvalue){
				if ($options['filter'][$actual_fieldname]['password_hash_type']=="md5"){
					$tabledata[$rowid][$actual_fieldname] = md5($varvalue);
	}
				if ($options['filter'][$actual_fieldname]['password_hash_type']=="sha1"){
					$tabledata[$rowid][$actual_fieldname] = sha1($varvalue);
	}
			}
			if ($debug){print "set actual fieldname $actual_fieldname to " . $tabledata[$rowid][$actual_fieldname];}
	}

	$debug=0;
	// now add file uploads
	foreach ($_FILES as $varname => $varvalue){
		if (!preg_match("/id_/",$varname)){continue;}
		$parts=explode("_",$varname);
		$discard=array_shift($parts);
		$rowid=array_shift($parts);
		if (!is_numeric($rowid)){continue;}
		$actual_fieldname = implode("_",$parts);
		// finally add field to list to be updated and move file if a new upload has come through
		if ($_FILES[$varname] && $_FILES[$varname]['tmp_name']){
			$tabledata[$rowid][$actual_fieldname]=$varvalue;	
			$tabledata[$rowid][$actual_fieldname]=addslashes(self::process_file_upload($varname,$options));
		//} else if ($_FILES[$varname]){
		//	print format_error("File not uploaded: No tmp name was generated. This is a server error, please contact your host or sys admin.",0);
		}
	}	

	$debug="";
	if ($debug){var_dump($tabledata);}
	$rows_updated=0;
	foreach ($tabledata as $table_row_id => $fieldvaluearray){
			if (!$table_row_id){continue;}
			$sqlfields=array();
			foreach ($fieldvaluearray as $field_name => $field_value){
				$fieldparts=array();
				if($debug){print "on $field_name - is ok<br />";}
				if ($field_name=="id"){continue;}
				if ($field_name=="date_updated"){$field_value=date("y/m/d : H:i:s", time());}
				$EXPORT_DATA[$field_name]=$field_value;
				$field_value = $db->db_escape($field_value);
				// multiple field input to concatenate?
				if (preg_match("/-\d+$/",$field_name)){
					$fieldparts=explode("-",$field_name);
					if ($options['filter'][$fieldparts[0]]['field_type']=="multiple_text_fields"){
						$real_field_name=$fieldparts[0];
						$real_field_name_array[$fieldparts[1]].=$field_value;
						//print "val so far is "; //var_dump($real_field_name_array);
					}	
				}
				// now if not multiple field input
				if ($options['filter'][$fieldparts[0]]['field_type'] != "multiple_text_fields"){
					// need to organise date field here into the right order
					if ($table_description[$field_name]=="date"){
						if ($options['filter'][$field_name]['field_type'] != "datepicker"){
							$date_year=substr($field_value,4,4);
							$date_month=substr($field_value,2,2);
							$date_day=substr($field_value,0,2);
							$field_value=$date_year.$date_month.$date_day;
						} else {
							$date_year=substr($field_value,6,4);
							$date_month=substr($field_value,3,2);
							$date_day=substr($field_value,0,2);
							$field_value=$date_year.$date_month.$date_day;
						}
					}


					// dynamic list below but only if its a key,value lookup, otherwise it will be fine as it is
					if ($options['filter'][$field_name]['field_type']=="dynamic_list" && $field_value){
						if (preg_match("/SELECT \w+ ?, ?\w+  ?FROM /i",$options['filter'][$field_name]['select_value_list'])){// this is for id+key lookups only, if its just one list then its fine to add the text as it was
						// do a reverse sql lookup on the name
						$sql=$options['filter'][$field_name]['select_value_list'];
						$sql=trim(str_replace("SQL:","",$sql));
						$sqlbits=preg_split("/ FROM /i",$sql);
						$sqlbits[0]=preg_replace("/SELECT /i","",$sqlbits[0]);
						$id_and_name=explode(",",$sqlbits[0]);
						$id_field=trim($id_and_name[0]);
						$name_field=trim($id_and_name[1]);
						$lookup_table=$sqlbits[1];
						$lookup_table_bits=explode(" ",$lookup_table);
						if ($lookup_table_bits[0]){
							$lookup_table=array_shift($lookup_table_bits);
						}
						$sql="SELECT $id_field FROM $lookup_table WHERE $name_field = \"" . $field_value . "\"";
						//print "inputdata is $field_name and posted data is " . $field_value . " which resolves to: <br>";
						//print $sql;
						$dynamic_res=$db->query($sql);
						$rows_returned=$db->num_rows($dynamic_res);
						if ($rows_returned==1){
							while ($h=$db->fetch_array($dynamic_res)){
								$field_value=$h[$id_field];
							}
						} else if ($rows_returned >1){
							// ambiguous entry
							array_push($form_error_messages,"<p>Ambiguous entry on $field_name</p>");
							$form_error_messages_present=1;
						} else if ($rows_returned==0){
							// see if we add the record or generate an error
							if ($options['filter'][$field_name]['dynamic_list_allow_record_add']=="1"){
								$add_dynlist_sql="INSERT INTO $lookup_table ($name_field) values(\"$field_value\")";
								$add_dynlist_res=$db->query($add_dynlist_sql);
								$dynlist_insert_id=$db->last_insert_id();
								if (!is_array($extra_add_status_messages)){
									$extra_add_status_messages=array();
								}
								array_push($extra_add_status_messages,"Added new record to table: $lookup_table of &quot;$field_value&quot;");
								$field_value=$dynlist_insert_id;
							} else {
							array_push($form_error_messages,"Cannot update - not allowing add on field: $field_name");
							$form_error_messages_present=1;
							} //end if add key
						} // end if rows_return=0
					} // end if svl is in key and name lookup format
						//var_dump($_POST);
					} // end if dynamic_list

					$field_string = $field_name . " = \"" . $field_value . "\""; 
					array_push ($sqlfields, $field_string);

					// REQUIRED FIELDS FOR UPDATE!
					// THIS WONT WORK ON MULTIPLE FIELD INPUT THOUGH WILL IT as theres only ONE ARRAY!!!!!
					if ($options['filter']['required_field_list']){
						if ($edit_type=="edit_all"){
							// need to do some special checking here!
							// currently no required field list is used for edit_all whatsoever!
						} else {
						// is the field required and is a value present?
							if (stristr($options['filter']['required_field_list'],$field_name)){
								$req_post_field_name="id_".$_POST['rowid_for_edit']."_$field_name";
								if (!$_POST[$req_post_field_name]){
									array_push($field_errors_on_update,$field_name);
									$form_error_messages_present=1;
								}
							}
						}
					}
					// might need the below in a minute - filled in values to send to the repeat form?!
					//$data_posted_in_fieldname="id_".$_POST['rowid_for_edit']."_$field_name";
					//$options['filled_in_values'][$field_name]=$_POST[$data_posted_in_fieldname];
					//print "IS THIS HAPPENING?";
					//var_dump($options['filled_in_values']);
				}
				// end if not multiple field input
			}
			// we've now run through the record. Finally push the concatenated multiple field input strings into the sql update query string
			if ($options['filter'][$real_field_name]['field_type'] == "multiple_text_fields"){
				$new_field_string=$real_field_name . " = \"" . implode($options['filter'][$real_field_name]['field_delimiter'],$real_field_name_array) . "\"";
				array_push($sqlfields,$new_field_string);
				$fieldparts=array(); $real_field_name=""; $real_field_name_array=array(); // resetting these for future loops
			}


			if ($field_errors_on_update){
				array_push($form_error_messages,"The following required fields were not filled in:<br /><br />" . str_replace("_"," ",join(", ",$field_errors_on_update)) . "<br /><br />Please fill in all of these fields and send the form again. Thank you.");
			}

			if (!$form_error_messages_present){
				$update_separate_fields= implode(",",$sqlfields);
				$update_line=$update_sql . $update_separate_fields;
				$update_line .= " WHERE ";
				// 7 lines below are only there for add_or_edit when we have a rowid_for_edit of fieldname:vakye we may not be using the pk to do an update
				if (preg_match("/^\w+ ?: ?\w+$/",$_REQUEST['rowid_for_edit'])){
					@list($lookup_field,$lookup_value)=explode(":",$_REQUEST['rowid_for_edit']);
					$lookup_field=trim($lookup_field);
					$rowid_for_edit=trim($lookup_value);
				} else {
					$lookup_field="id";
				}
				$update_line .= $lookup_field;
				$update_line .= " = ";
				$update_line .= $table_row_id;
				$result=$db->query($update_line) or die(" error with $update_line : " . $db->db_error());
				//$warnings=$db->query("show warnings") or die ("not happening is it");
				//$r=$db->fetch_array($warnings);
				//var_dump($r);
				if ($debug){print "<p>".$update_line . " - Result: " . $result . "</p><br />";}
				$rows_updated++;
			} else {
				foreach ($form_error_messages as $form_error_message){
					$status_message = "<p class=\"dbf_para_alert\">$form_error_message</p>";
					if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
						if (!$status_message_open){
							open_status_message();
							print $status_message;
							close_col();
						}
					}
				}
			}
		}
	}
	if ($rows_updated){
		if ($rows_updated===1){
			$plural="";
			$rid=$_POST['rowid_for_edit'];
			$rid1_test="id_".$rid."_name";
			if ($_POST[$rid1_test]){
				$name_or_number=$db->db_escape($_POST[$rid1_test]);
					$quote="'";
			} else {
				$rid2_test="name_".$rid."_title";
				if ($_POST[$rid2_test]){
					$name_or_number=$db->db_escape($_POST[$rid2_test]);
					$quote="'";
				} else {
					$rid3_test="id_".$rid."_id";
					if ($_POST[$rid3_test]){
						$name_or_number="record id: " . $_POST[$rid3_test];
						$quote="";
					}
				}
			}
			if ($name_or_number){
				$name_or_number=$quote . strip_tags($name_or_number) . $quote;
			} else {
				$name_or_number="record";
			}
		} else {
			$plural="s";
			$name_or_number="$rows_updated record$plural";
		}
		$status_message= "Updated $name_or_number at " . date("d/m/Y H:i:s", time()) . "\n";
		if ($extra_add_status_messages){
			$status_message .= "<br />" . join("<br />",$extra_add_status_messages);
		}
		if (!$status_message_open){
			if (stristr($_SERVER['PHP_SELF'],"index.php") || stristr($_SERVER['PHP_SELF'],"administrator.php")){
				open_status_message();
				$status_message_open=1;
				print "<p class=\"dbf_para_success\">" . $status_message . "</p>";
				close_col();
			}
			$status_message_open=1;
		}
		if (stristr($_SERVER['PHP_SELF'],"mui-administrator")){
			if (!$_REQUEST['after_update_page_element'] && !$options['after_update'] && !$options['mui_cancel_autoclose'] && $_REQUEST['edit_type'] != "edit_all"){
			//print "<p><a href=\"Javascript:closeMUIWin()\">Close Window</a></p>";
			print "<script language=\"Javascript\">
					function getwindowId() {
						windowName = window.frameElement.id;
						windowName=windowName.replace(/_iframe/,\"\");
						return windowName;
					}

					function closeMUIWin(){
						winId=getwindowId();
						parent.MochaUI.closeWindow(parent.document.getElementById(winId));
					}
					setTimeout(function(){closeMUIWin()},700);
				</script>
			";
			}
		}
	}

	// note added the stristr PHP_SELF administrator.php line here as the form was coming out twice in admin on unique field errors - HOWEVER!! Just changed || to && in the first if, this may well have something to do with it!
	if (($field_errors && $unique_field_errors) && !stristr($_SERVER['PHP_SELF'],"administrator.php")){$add_to_content=form_from_table($tablename,$_POST['edit_type'],$_POST['rowid_for_edit'],$_POST['add_data'],$options);} 

	if ($form_error_messages_present){$add_to_content=form_from_table($tablename,$_POST['edit_type'],$_POST['rowid_for_edit'],$_POST['add_data'],$options); 
		if (stristr($_SERVER['PHP_SELF'],"administrator.php")){ 
			$options['filter']['after_update']="cancel";
		} else {
			$options['filter']['after_update']="repeat";
		}
	}

	$RETURN=array();

	// write data out to external file if necessary
	if (!$field_errors && !$unique_field_errors && !$form_error_messages_present){
		if ($update_corresponding_file && $corresponds_to_directory && $field_as_filename && $field_as_contents && $file_extension && $file_to_write_external && $file_content_data){
			// write out the file
			$path_and_file = $corresponds_to_directory . "/" . $file_to_write_external . ".". $file_extension;
			file_put_contents($path_and_file,$file_content_data) or print format_error("Unable to write to the external file $path_and_file. <br />The database copy of this file has been saved",0);
			open_status_message();
			$status_message_2 = "The file $file_to_write_external.$file_extension has been updated.";
			print "<p class=\"dbf_para_success\">$status_message_2</p>";	
			close_col();
		}
	}

	if ($debug){print "au is " . $options['filter']['after_update'];}
	if ($options['filter']['after_update']){

                if (stristr($options['filter']['after_update'],"|")){
                        if ($debug){print "splitting chain";}
                        $after_update_chain=explode("|",$options['filter']['after_update']);
                        $options['filter']['after_update']=array_shift($after_update_chain);
                }

                if ($options['filter']['after_update']=="mail_form"){
                        mail_form_data($tablename,$last_insert_id,$options);
                        if ($after_update_chain){
                                $options['filter']['after_update']=array_shift($after_update_chain);
                        }
                }

		if ($options['filter']['after_update']=="continue"){
			// doing nothing causes a continue;
		} elseif ($options['filter']['after_update']=="run_code" && $after_update_chain){
			// do the run code action
			if (!$options['filter']['after_update_run_code'] && $_POST['after_update_run_code']){
				$options['filter']['after_update_run_code']=$_POST['after_update_run_code'];
			}
			if ($options['filter']['after_update_run_code']){
				global $user;
				$options['filter']['after_update_run_code']=str_replace("{=current_user}",$user->value("id"),$options['filter']['after_update_run_code']);
				$return_from_code = run_php_code($options['filter']['after_update_run_code'],$last_insert_id);
				print $return_from_code;
			}

			// repeat bit
			if (stristr($_SERVER['PHP_SELF'],"site.php")){$options['filter']['add_string_to_form_post_query']="content=".$_REQUEST['content'];}
			$add_to_content=self::form_from_table($tablename,$_POST['edit_type'],$_POST['rowid_for_edit'],$_POST['add_data'],$options); 
			$options['filter']['after_update']="repeat";
			$after_update_chain="";

		} elseif ($options['filter']['after_update']=="repeat") {
			if (stristr($_SERVER['PHP_SELF'],"site.php")){$options['filter']['add_string_to_form_post_query']="content=".$_REQUEST['content'];}
			$add_to_content=self::form_from_table($tablename,$_POST['edit_type'],$_POST['rowid_for_edit'],$_POST['add_data'],$options); 
		} elseif ($options['filter']['after_update']=="list_table"){
			//$dbforms_options=load_dbforms_options($tablename);
			//$registered_filter=filter_registered_on_table($tablename,"list_table");
			//if ($registered_filter){
				//$dbforms_options['filter']=load_dbforms_filter($registered_filter);
			//}
			

			unset($_REQUEST['filter_id']);
			if ($options['filter']['after_update_list_table_filter_id']){
				$_REQUEST['filter_id']=$options['filter']['after_update_list_table_filter_id'];
			}
			$dbforms_options=self::load_dbforms_options($tablename,"list_table");
			self::list_table($tablename,$dbforms_options);
		} elseif ($options['filter']['after_update']=="run_code"){
			if (!$options['filter']['after_update_run_code'] && $_POST['after_update_run_code']){
				$options['filter']['after_update_run_code']=$_POST['after_update_run_code'];
			}
			if ($debug){print "<p>CALLING run_php_code on " . $options['filter']['after_update_run_code'] . "<p>";}

			if ($edit_type=="edit_single" && $_REQUEST['rowid_for_edit']){
				$return_from_code = run_php_code($options['filter']['after_update_run_code'],$db->db_escape($_REQUEST['rowid_for_edit']));
			} else {
				$return_from_code = run_php_code($options['filter']['after_update_run_code'],$last_insert_id);
			}
			
			if ($debug){print "<p>OK code run, dci is " . $options['filter']['after_update_display_content_id'];}
			if ($options['filter']['after_update_display_content_by_key_name']){
				// get content id from the key..
				$options['filter']['after_update_display_content_id']=$db->db_quick_match("content","id","dbf_key_name",$options['filter']['after_update_display_content_by_key_name']);
			}
			if ($options['filter']['after_update_display_content_id'] || $_POST['after_update_display_content_id']){
				if ($options['filter']['after_update_display_content_id']){$audci = $options['filter']['after_update_display_content_id'];}else{$audci=$_POST['after_update_display_content_id'];}
				$replace_vars_in_return_content=array();
				$replace_vars_in_return_content['last_insert_id']=$last_insert_id;
				$replace_vars_in_return_content['output_from_after_update_run_code']=$return_from_code;
				$replace_vars_in_return_content['status_message']=$status_message;
				global $page;
				$RETURN['return_content']=$page->content_from_id($audci,0,$replace_vars_in_return_content);
				$RETURN['title']=$page->title_from_id($options['filter']['after_update_display_content_id']);;
				/* taken care of by auto login module 
				if ($_POST['direct_to'])
					$dirto=$_POST['direct_to'];
					header("Location: $dirto");
					exit;
				
				*/
			} else if ($options['filter']['after_update_display_admin_content_by_key_name']){
				$replace_vars_in_return_content=array();
				$replace_vars_in_return_content['last_insert_id']=$last_insert_id;
				$replace_vars_in_return_content['status_message']=$status_message;
				$replace_vars_in_return_content['output_from_after_update_run_code']=$return_from_code;
				global $page;
				$page->set_global_var("last_insert_id",$last_insert_id);
				if ($options['filter']['set_global_variable']){
					@list($gvar_name,$gvar_val,$more)=explode("=",$options['filter']['set_global_variable']);
					if ($more){ $gvar_val=$gvar_val."=".$more; }
					if ($gvar_val=="{=last_insert_id}"){ $gvar_val=$last_insert_id;}
					$page->set_global_var($gvar_name,$gvar_val);
				}
				$output=$page->display_admin_content_by_key_name($options['filter']['after_update_display_admin_content_by_key_name'],$replace_vars_in_return_content);
				print $output;
				return;
				//$db>db_quick_match("","id","dbf_key_name",$options['filter']['after_update_display_content_by_key_name']);
			} else {
				if ($_POST['after_update_page_element']=="continue"){
					if ($_POST['edit_type']=="add_row"){ // convert add row to edit the record you added
						self::form_from_table($tablename,"edit_single",$last_insert_id,$_POST['add_data'],$options); 
						print "GOT TO THIS BIT 1";
					} else {
						print "GOT TO THIS BIT";
						self::form_from_table($tablename,$_POST['edit_type'],$_POST['rowid_for_edit'],$_POST['add_data'],$options); 
					}
				} else {
					unset($_REQUEST['filter_id']);
					$dbforms_options=self::load_dbforms_options($tablename,"list_table");
					self::list_table($tablename,$dbforms_options);
				}
			}
		} elseif ($options['filter']['after_update']=="display_content" && $options['filter']['after_update_display_content_id']){
			if ($debug){print "adding content to return vars and last insert id is " . $last_insert_id;}
			$replace_vars_in_return_content=array();
			$replace_vars_in_return_content['last_insert_id']=$last_insert_id;
			global $page;
			$RETURN['title']=$page->title_from_id($options['filter']['after_update_display_content_id']);;
			$RETURN['return_content']=$page->content_from_id($options['filter']['after_update_display_content_id'],0,$replace_vars_in_return_content);
		// elseif ($options['filter']['after_update']=="display_content" && $options['filter']['after_update_display_admin_content_by_key_name'] && !$_REQUEST['perpetuate_ajax_iframe_mode'])
		} elseif ($options['filter']['after_update']=="display_content" && $options['filter']['after_update_display_admin_content_by_key_name']){
				$replace_vars_in_return_content=array();
				$replace_vars_in_return_content['last_insert_id']=$last_insert_id;
				$replace_vars_in_return_content['status_message']=$status_message;
				global $page;
				$page->set_global_var("last_insert_id",$last_insert_id);
				if ($options['filter']['set_global_variable']){
					@list($gvar_name,$gvar_val,$more)=explode("=",$options['filter']['set_global_variable']);
					if ($more){ $gvar_val=$gvar_val."=".$more; }
					if ($gvar_val=="{=last_insert_id}"){ $gvar_val=$last_insert_id;}
					$page->set_global_var($gvar_name,$gvar_val);
				}
				$output=$page->display_admin_content_by_key_name($options['filter']['after_update_display_admin_content_by_key_name'],$replace_vars_in_return_content);
				print $output;
				return;
		} elseif ($options['filter']['after_update']=="function"){
			eval($options['filter']['after_update']);
		} elseif (preg_match("/call_action\(/",$options['filter']['after_update'])){
			$RETURN['script_action']=preg_replace("[\(\)]/g","",$options['filter']['after_update']);
			$RETURN['script_action']=str_replace("call_action(","",$options['filter']['after_update']);
			$RETURN['script_action']=str_replace(")","",$RETURN['script_action']); 
		} elseif ($options['filter']['after_update']=="related_keys"){
			// we need to create a record where keys can be related to the master record which has just been added
			// This should only run if it is an ADD RECORD interface and not an edit?
			if ($edit_type=="add_row"){
			$rkurl="Javascript:parent.loadPage('".$_SERVER['PHP_SELF']."?action=list_table&t=filter_keys&relation_id=4&relation_key=$last_insert_id&dbf_mui=1&jx=1&iframe=1','filter keys')";
			print "<p style=\"margin-left:15px\"><a href=\"$rkurl\">Continue to add keys</a>";
			}
		}

	} else {
		if (!$_POST['after_update_page_element']){
			// this is the default action
			unset($_REQUEST['filter_id']);
			if ($options['filter']['after_update_list_table_filter_id']){
				$_REQUEST['filter_id']=$options['filter']['after_update_list_table_filter_id'];
			}
			// if we are on both jx AND iframe we probably don't want to do this?
			global $page;
			if ($page->value("perpetuate_ajax_iframe_mode")=="1" || $page->value("mui")){
				// edit all defaults here
				if ($edit_type=="edit_all"){
					$dbforms_options=self::load_dbforms_options($tablename,"list_table");
					self::list_table($tablename,$dbforms_options);
				} else if ($edit_type=="add_row"){
					print "<p>Record id: $last_insert_id added</p>";
					//$dbforms_options=load_dbforms_options($tablename,"edit_table");
					//list_table($tablename,$dbforms_options);
				}
				exit;
			} else {
				$dbforms_options=self::load_dbforms_options($tablename,"list_table");
				self::list_table($tablename,$dbforms_options);
			}
		} else if ($_POST['after_update_page_element']=="continue"){
			//print "on a continue - this comes from save and continue editing from a record opened in EDIT mode";
			if ($edit_type=="add_row" && stristr($_SERVER['PHP_SELF'],"dministrator.php")){
				self::form_from_table($tablename,"edit_single",$last_insert_id,$_POST['add_data'],$options); 
				exit;
			}
			self::form_from_table($tablename,$_POST['edit_type'],$_POST['rowid_for_edit'],$_POST['add_data'],$options); 
		} else if ($_POST['after_update_page_element']=="repeat"){
				// add another record
				$_POST=array();
				unset($_POST);
				$options['filled_in_values']="";
				self::form_from_table($tablename,"add_row","","",$options); 
				exit;
		} else {
			print "should never happen!";
		}
	}

	$RETURN['values']=$EXPORT_DATA;
	$RETURN['status_message']=$status_message;
	$RETURN['$db->last_insert_id']=$last_insert_id;
	//$RETURN['mysql_affected_rows']=mysql_affected_rows();
	if (!$field_errors && !$unique_field_errors && !$form_error_messages_present){$RETURN['status']=1;}
	if (isset($options['filter']['status_message_success']) && !$field_errors && !$unique_field_errors && !$form_error_messages_present){$RETURN['status_message']=$options['filter']['status_message_success'];}
	if (($field_errors || $unique_field_errors || $form_error_messages_present) && $options['filter']['export']=="html"){
		$RETURN['repeat_form']=$add_to_content;
		$RETURN['repeat_form_filter_id']=$options['filter']['filter_id'];
		$RETURN['repeat_form_tablename']=$tablename;
	}

        if (($field_errors || $unique_field_errors || $form_error_messages_present) && $options['filter']['form_error_display_content_id']){
                $RETURN['alternate_content_on_form_fail']=$options['filter']['form_error_display_content_id'];
        }

	return $RETURN;
}

//////////////////////////////////////////////////////////////
// delete_row_from_table - simply delets a row from its id i//
//////////////////////////////////////////////////////////////
static function delete_row_from_table($tablename,$deleteID,$options){
	global $db;
	global $CONFIG;
	if ($CONFIG['enable_form_logging']){
	$form_identifier=$db->db_escape($_POST['dbf_sys_form_id']);
	$check_form_integrity=self::check_web_form_integrity($form_identifier,$tablename,"delete",$options['filter']['dbf_filter_id'],"");
	if (!$check_form_integrity){
		format_error("Internal form check error.",1);
	}
	
	}
	$permissions_result=self::check_dbf_permissions($tablename,"delete",$deleteID);
	if ($permissions_result['Status']==0){
		if (!$col2_open){
			open_col2();
		}
		print $permissions_result['Message'];
		return;
	}
	$sql="DELETE FROM " . $tablename . " WHERE id = " . $deleteID;
	$result=$db->query($sql) or format_error("Sql error in :<br />" . $sql . "<br /><br />" . $db->db_error());
	if ($options['back_url']){
		$locationstring = $SERVER['PHP_SELF'] . "?" . $options['back_url']; 
		header("Location: $locationstring");
		echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?" . $options['back_url'] . "\">Back To Previous Screen</a><br /><br />";
	} else {
		open_status_message();
		print "<p class=\"dbf_para_success\">Record Deleted</p>";
		if ($_REQUEST['deleteChildren']){ print "Children deleted also\n"; }
		echo "<!--<a href=\"Javascript: history.go(-1)\">&lt; Back To Previous Screen</a>//-->";
		close_col();
		open_col2();
		//$registered_filter=filter_registered_on_table($tablename,"list_table");
		//if ($registered_filter){
			//$dbforms_options['filter']=load_dbforms_filter($registered_filter);
		//}
		
		// manually apply extra options here for now...
		$dbforms_options=$options;
		//$dbforms_options['filter']['include_delete_option']=1;
		//$dbforms_options['filter']['include_edit_link']=1;
		//$dbforms_options['filter']['include_add_link']=1;
		//$dbforms_options['filter']['dbf_eda']=0;
		//$dbforms_options['filter']['dbf_rpp_sel']=1;
		//$dbforms_options['filter']['dbf_search']=1;
		//$dbforms_options['filter']['dbf_sort']=1;
		//$dbforms_options['filter']['dbf_sort_dir']=1;
		//$dbforms_options['filter']['dbf_orderby']=1;
//		var_dump($options); 

		if ($options['delete_return_to']=="edit_all"){
			if ($dbforms_options['recordset_filter_id']){
				$dbforms_options['filter']=self::load_dbforms_filter($dbforms_options['recordset_filter_id'],$dbforms_options['filter']);
			}
			self::form_from_table($tablename,"edit_all","",1,$dbforms_options);
		} else {
			self::list_table($tablename,$dbforms_options);
		}
	}
	close_col();
	exit;
}

///////////////////////////////////////////////////////////////////////////////////////
// removes a file from a record and also deletes the file from the server.
// works through ajax //
///////////////////////////////////////////////////////////////////////////////////////
static function ajax_remove_file_from_record(){
	global $db;
	if (!$_REQUEST['t'] || !$_REQUEST['f'] || !$_REQUEST['id'] ||
!$_REQUEST['filter_id']){
		print "Unable to remove file.\n"; exit;
	}
	$table=$_REQUEST['t'];
	$field=$_REQUEST['f'];
	$id=$_REQUEST['id'];
	$filter_id=$_REQUEST['filter_id'];

	$permissions_result=self::check_dbf_permissions($table,"delete",$id);
	if ($permissions_result['Status']==0){
		print $permissions_result['Message'];
		exit;
	}
	$sql="SELECT $field from $table WHERE id=$id";
	$res=$db->query($sql) or die ($db->db_error());
	$h=$db->fetch_array($res);
	$filename=$h[$field];

	$sql="UPDATE $table set $field = '' WHERE id = $id";
	$filter=self::load_dbforms_filter($filter_id);
	$dir=$filter[$field]['file_upload_directory'];
	if (!$dir){print "No directory - cant find file"; exit;}
	$path_and_file=$dir."/".$filename;
	if (file_exists($path_and_file)){unlink ("$path_and_file");}
	$res=$db->query($sql);
	print "File Deleted";
	exit;
}

///////////////////////////////////////////////////////////////////////////////////////
// load_dbforms_filter - grabs the filter keys from the filter id and returns a hash //
///////////////////////////////////////////////////////////////////////////////////////
static function load_dbforms_filter($filter_id,$existing_filter){
	global $db;
	$filter_options=array();
	if ($existing_filter){$filter_options=$existing_filter;}
	$parent_filter_id="";
	$sql1 = "SELECT parent_filter from filters where id = ". $filter_id;
	$result=$db->query($sql1);
	if ($result){
		while ($row = $db->fetch_array($result)){
			$parent_filter_id=$row['parent_filter'];
		}
	}
	if ($parent_filter_id){
		$sql="SELECT * from filter_keys WHERE filter_id = " . $parent_filter_id;
		$result=$db->query($sql);
		while ($row=$db->fetch_array($result)){
			if ($row['field']){
			$filter_options[$row['field']][$row['name']]=$row['value'];	
			} else {
			$filter_options[$row['name']]=$row['value'];
			}
		}
	}

	$sql="SELECT * from filter_keys WHERE filter_id = " . $filter_id;
	$result=$db->query($sql);
	if ($result){
		global $user;
		while ($row=$db->fetch_array($result)){
			if ($debug){print "<br>Adding key of " . $row['name'] . " to field " . $row['field'] . " with val of " . $row['value'];}
			 $row['value']=str_replace("{=current_user}",$user->value("id"),$row['value']);
			if ($row['field'] && self::load_filter_key_on_usertype($user->value("type"),$row['user_type'])){
				$filter_options[$row['field']][$row['name']]=$row['value'];
                        } else if (self::load_filter_key_on_usertype($user->value("type"),$row['user_type'])){
                                $filter_options[$row['name']]=$row['value'];
                        }
		}
	}
	$filter_options['filter_id']=$filter_id;
	$filter_options['dbf_filter_id']=$filter_id;

	return $filter_options;
}

static function load_filter_key_on_usertype($usertype,$filter_usertype){
	if (!$usertype && $filter_usertype){ return 0; }
        if (!$usertype || !$filter_usertype){ return 1;}
        if ($usertype==$filter_usertype){ return 1;}
        return 0;
}

//////////////////////////////////////////////////////////////////////////////
// returns the HTML to go between the <select> html tags via a few paramaters //
//
// Input Arguments: 
//		$comma_separated_list - a comma separated list where values are the same as option text, or include double semi-colons in each to specify id=value.
//		$selected - a VALUE (not text) that is selected that should display as selected
//		$default_if_no_selected - if nothing is selected, a VALUE (not text) that is selected as a default
//		$blank_entry_at_top - boolean that inserts 'Please Select' with no selected value at the top of the list (dev note: convert so text is sent in here)
//		$blank_entry_selected - boolean that controls whether the blank entry is selected on pageload or not
//
// Future modifications: 1) Include a third paramater on comma_separated_list where the third separation is a class. 2) Make comma_separated_list an input hash instead of a string for splitting 
//
//////////////////////////////////////////////////////////////////////////////


static function build_select_option_list($comma_separated_list,$selected,$default_if_no_selected,$blank_entry_at_top,$blank_entry_selected,$list_delimiter,$blank_entry_text=null){
//	print "selected is $selected, blank selected is $blank_entry_selected";
	if (!$list_delimiter){$list_delimiter=",";}
	if (!$blank_entry_text){ $blank_entry_text="Select:"; }
	if ($blank_entry_at_top){$return_options = "<option value=\"\">$blank_entry_text</option>";}
	if ($blank_entry_at_top && $blank_entry_selected && !$selected && !$default_if_no_selected){$return_options = "<option value=\"\" selected>$blank_entry_text</option>"; $default_if_no_selected=""; }
	$select_options=array();
	$select_options=explode($list_delimiter,$comma_separated_list);

	foreach ($select_options as $select_option){
		if (strpos($select_option,";;")){
			$pair=explode(";;",$select_option);
			$option_value=$pair[0];
			$option_text=$pair[1];	
		} else {
			$option_value = $option_text = $select_option;
		}
		$return_options .= "<option value=\"" . $option_value . "\" ";
		if ($option_value == $selected){$return_options .=  "selected";}
		if ($select_option == $default_if_no_selected && !$selected){$return_options .= " selected";}
		$return_options .= ">" . $option_text . "</option>\n";	
	}
	return $return_options;
}


static function build_multiple_select_option_list($comma_separated_list,$selected,$default_if_no_selected,$blank_entry_at_top,$blank_entry_selected,$list_delimiter,$blank_entry_text){
	$selected_array=explode(",",$selected);
	if (!$list_delimiter){$list_delimiter=",";}
	$select_options=array();
	$select_options=explode($list_delimiter,$comma_separated_list);

	foreach ($select_options as $select_option){
		if (strpos($select_option,";;")){
			$pair=explode(";;",$select_option);
			$option_value=$pair[0];
			$option_text=$pair[1];	
		} else {
			$option_value = $option_text = $select_option;
		}
		$return_options .= "<option value=\"" . $option_value . "\" ";
		if (in_array($option_value,$selected_array)){$return_options .=  "selected";}
		$return_options .= ">" . $option_text . "</option>\n";	
	}
	return $return_options;
}

static function search_linked_field($query,$search_for,$searchwhere="all",$specify_table=""){
	global $db;
	if ($searchwhere=="all"){$initial_percent="%";} else {$initial_percent="";}
	$query=str_replace("SQL:","",$query);
	$query=str_replace("SELECT ","",$query);
	$query=str_replace("select ","",$query);
	$qbits=explode("WHERE", $query);
	$query=$qbits[0];
	if (preg_match("/from/",$query)){$from="from";}
	if (preg_match("/FROM/",$query)){$from="FROM";}
	$field_and_table=explode($from,$query);
	$table_to_check_array=explode(" ",trim($field_and_table[1]));
	$table_to_check=$table_to_check_array[0];
	if ($specify_table){ $table_to_check=$specify_table; }
	$primary_key_of_table=get_primary_key($table_to_check);
	$field_and_table[0]=preg_replace("/\b$primary_key_of_table,/","",$field_and_table[0]);
	$get_id_from_table="SELECT $primary_key_of_table FROM " . $table_to_check . " WHERE ";
	$all_fields_to_search = explode(",",$field_and_table[0]);
	$key_value=$field_and_table[0];
	if ($specify_table){$key_value=array_shift($all_fields_to_search); }// this is always the key value. Possibly there is reason to search a key value?! Hm
	// the above only if table specified?! NOT right, actually I thnk needs to be run IF there is nore than one field in the list (ie an id calls up 2 fields in a corresponding table such as the administrators table
	$sql_like_clauses=array();
	$and_or_or="OR";
	if (sizeof($all_fields_to_search)>1){
		$search_words = explode(" ",$search_for);
		// same number of multiple columns as fields to search?
		if ((sizeof($search_words) == sizeof($all_fields_to_search)) && sizeof($search_words)>=2){
			if ($searchwhere=="two_as_two"){
				$and_or_or="AND";
				$wordcount=0;
				foreach ($all_fields_to_search as $field_to_search){
					array_push($sql_like_clauses,$field_to_search . " LIKE \"$initial_percent" . $search_words[$wordcount] . "%\"");
					$wordcount++;
				}
			} else {
				$and_or_or="OR";
				$wordcount=0;
				$search_results_array=array();
				foreach ($search_words as $this_search_word){
					foreach ($all_fields_to_search as $field_to_search){
						array_push($sql_like_clauses, $field_to_search . " LIKE \"$initial_percent" . $this_search_word . "%\""); 
					}
					$wordcount++;
				}
			}
		} else {
			foreach ($search_words as $search_word){
				foreach ($all_fields_to_search as $field_to_search){
					 array_push($sql_like_clauses,$field_to_search . " LIKE \"$initial_percent" . $search_word . "%\""); 
				}
			}	
		}
		if ($and_or_or=="OR"){
			$get_id_from_table .= join(" OR ",$sql_like_clauses);
		} else {
			$get_id_from_table .= join(" AND ",$sql_like_clauses);
		}
	} else {
		$get_id_from_table .= $key_value . " LIKE \"$initial_percent" . $search_for . "%\"";
	}
//	print "<p>Heres the query: $get_id_from_table</p>";

	$slf_result=$db->query($get_id_from_table) or format_error("Cannot search linked field using $get_id_from_table: " . $db->db_error(),1);
	$possible_ids=array();
	while ($slf_results=$db->fetch_array($slf_result)){
		//print "Adding " . $slf_results[$primary_key_of_table] . " to $primary_key_of_table\n<br />";
		array_push($possible_ids,$slf_results[$primary_key_of_table]);
	}
	$return_possible_ids=implode(",",$possible_ids);
	//print "our possible ids are " . $return_possible_ids;
	if ($debug){	print "returning possible ids of " . $return_possible_ids;	}
	if (preg_match("/^,+$/",$return_possible_ids)){
		//print "Not looking good on $query!";
		$return_possible_ids="";
	}
	return $return_possible_ids;
}


/*
 * Function: get_sql_list_values 
 * Returns a comma separated list from an sql query that fits exactly into the format required by build_select_option_list() above
*/
static function get_sql_list_values($query,$id_value=null,$list_delimiter=","){
	global $db;
	if (!$list_delimiter){$list_delimiter=",";}
	
	$query=str_replace("SQL:","",$query);
	$querywords=explode(" ",$query);
	$nextword=0;
	foreach ($querywords as $queryword){
		if ($nextword){$nextword=0;$retrieve_field=$queryword;}
		if ($queryword == "SELECT" or $queryword == "select"){
			$nextword=1;
		}
	}
	if (strpos($retrieve_field,",")){$id_and_field=explode(",",$retrieve_field);}
	$sql=$query;
	if ($id_value){$sql .= " WHERE id = $id_value";}
	$return_array=array();
	$result=$db->query($sql);
	while ($sqlrow=$db->fetch_array($result)){
		if (!$id_and_field){
			array_push($return_array,$sqlrow[$retrieve_field]);
		} else {
			if ($sqlrow[$id_and_field[2]]){
				if (stristr($id_and_field[1],".")){ $id_and_field[1]=preg_replace("/\w+\./","",$id_and_field[1]); }
				if (stristr($id_and_field[2],".")){ $id_and_field[2]=preg_replace("/\w+\./","",$id_and_field[2]); }
				$sqlrow[$id_and_field[1]] .= " " . $sqlrow[$id_and_field[2]];
			}
			if (stristr($id_and_field[0],".")){ $id_and_field[0]=preg_replace("/\w+\./","",$id_and_field[0]); }
			if (stristr($id_and_field[1],".")){ $id_and_field[1]=preg_replace("/\w+\./","",$id_and_field[1]); }
			array_push($return_array,$sqlrow[$id_and_field[0]].";;".$sqlrow[$id_and_field[1]]);	
		}
	}
	$return_string=implode($list_delimiter,$return_array);
	if (!$return_string){
		return "NULL;;No Select Options Found";
	}
	return $return_string;
}

//
// like the query_above (exactly in fact!!) but the $id_value arg allows a specific id filter to be placed on an existing sql query. Merge these?
//
static function sql_value_from_id($key_query, $id_value, $specify_table=""){
	//print "- on sql value from id with $id_value!";
	//if (!preg_match("/^\d+$/",$id_value)){
	//	print_debug("non numeric value spotted!<br />");
	//	return $id_value;
	//}
	//if (!is_numeric($id_value)){ return $id_value; }
	global $db;
	if (!$id_value && strlen($id_value)==0){return NULL;} // matt added this line to stop all results coming back
	if (strlen(strpos($key_query,";;"))){
	$id_value_pairs=explode(",",$key_query);
	$return_string="";
	foreach ($id_value_pairs as $id_value_pair){
		$id_and_value=explode(";;",$id_value_pair);
		if ($id_and_value[0]==$id_value){$return_string=$id_and_value[1];}
	}
	return $return_string;
	}
	$query=str_replace("SQL:","",$key_query);
	$querywords=explode(" ",$query);
	$nextword=0;
	foreach ($querywords as $queryword){
		if ($nextword){$nextword=0;$retrieve_field=$queryword;}
		if ($queryword == "SELECT" or $queryword == "select"){
			$nextword=1;
		}
	}
	if (strpos($retrieve_field,",")){$id_and_field=explode(",",$retrieve_field);}
	$sql=$query;
	if ($id_value || strlen($id_value)>=1){

		$check_no_of_wheres=explode(" ",$sql);
		foreach ($check_no_of_wheres AS $checkwhere){
			if ($checkwhere=="WHERE"){$where_count++;}
		}
		// ALERT BELOW = >=1 was 2 - not sure why but changed for amber
		if ($where_count>=1){$where_or_and="AND";}else{$where_or_and = "WHERE";}
		
		$key_column="id";
		
		// key may not always be id so..
		$all_fields=explode(",",$retrieve_field);
		if ($all_fields[0] != "id" && $all_fields[0]=="key_name"){ // MATTPLATTS JUNE 2012 - this could well be a HACK! What do we think?
			print_debug("Not an id field - it is " . $all_fields[0]);
			$key_column=$all_fields[0];
		}

		if ($specify_table){ $key_column = $specify_table . ".id";}
		$sql .= " $where_or_and $key_column = '$id_value'";
		// if any order by, remove it so that the where is in the right place
		if (preg_match("/(ORDER BY\s+\w+(\s+\w+)?\s+)WHERE/i",$sql,$matches)){
			$sql = str_replace($matches[1],"",$sql);
			$sql .= " " . $matches[1];
		} else if (preg_match("/(ORDER BY\s+\w+(\s+\w+)?\s+)AND/i",$sql,$matches)){ // the AND as opposed to where came in from the sub select functionality
			$sql = str_replace($matches[1],"",$sql);
			$sql .= " " . $matches[1];
		} else {
			// print "no middle match in $sql";
		}
	} else { print_debug("No id value here!"); }

	// may still have something like SELECT product_id,name,size FROM products WHERE master_product=1 ORDER BY name,size AND id = 16070 so chop it?
		if (preg_match("/ORDER BY .* AND/i",$sql)){
			$sql=preg_replace("/ORDER BY .* AND/i"," AND",$sql);
		}

	$return_array=array();
	if (preg_match("/SELECT/i",$sql)){ // note we may already have just a list and a query may not be required
		$result=$db->query($sql) or print format_error("ERROR 519U87 in $sql" . $db->db_error(),"0","4");
		while ($sqlrow=$db->fetch_array($result)){
			if (!$id_and_field){
				array_push($return_array,$sqlrow[$retrieve_field]);
			} else {
				$discard_id = array_shift($id_and_field);
				foreach ($id_and_field as $field_to_display){
					$display_value .= $sqlrow[$field_to_display] . " ";
				}
				array_push($return_array,rtrim($display_value));	
			}
		}
	}
	$return_string=implode(",",$return_array);
	return $return_string;
}

static function merge_filters(){
}

// load_dbforms_options is deprectaed - only included in case any front ends still have it
// HOLY CRAP - its in the after update on an edit all form at least!!
static function load_dbforms_options($optional_tablename,$optional_action){
	// clear everything to start
	$dbforms_options="";
	global $CONFIG;
	global $_REQUEST_SAFE;
	global $_POST_SAFE;
	global $_GET_SAFE;
	global $db;

	$dbforms_options=self::load_options_defaults();
	$records_per_page=$dbforms_options['filter']['rpp'];
	if ($_REQUEST_SAFE['dbf_rpp']){ $records_per_page=$_REQUEST_SAFE['dbf_rpp']; $dbforms_options['filter']['dbf_rpp']=$_REQUEST_SAFE['dbf_rpp'];}
	if (!$records_per_page){ $records_per_page = filter_key_exists("dbf_rpp",$_REQUEST['filter_id']);} // although filter is not yet loaded, it will be as we have specified the filter id. filter_key_exists without the filter_id only returns the value if it is loaded.

	// date start requests
	if ($_REQUEST['dbf_search_date_start_full']){
		$start_date_array=explode("-",$_REQUEST['dbf_search_date_start_full']);
		$dbforms_options['filter']['new_dbf_search_date_start-1']=$start_date_array[0];
		$dbforms_options['filter']['new_dbf_search_date_start-2']=$start_date_array[1];
		$dbforms_options['filter']['new_dbf_search_date_start-3']=$start_date_array[2];
		$dbforms_options['filter']['dbf_search_date_start_full']=$_REQUEST['dbf_search_date_start_full'];
		$dbforms_options['filter']['active_date_filter']=1;
	}
	if ($_REQUEST['dbf_search_date_end_full']){
		$end_date_array=explode("-",$_REQUEST['dbf_search_date_end_full']);
		$dbforms_options['filter']['new_dbf_search_date_end-1']=$end_date_array[0];
		$dbforms_options['filter']['new_dbf_search_date_end-2']=$end_date_array[1];
		$dbforms_options['filter']['new_dbf_search_date_end-3']=$end_date_array[2];
		$dbforms_options['filter']['dbf_search_date_end_full']=$_REQUEST['dbf_search_date_end_full'];
		$dbforms_options['filter']['active_date_filter']=1;
	}

	if ($_REQUEST['dbf_ido']=="1"){$dbforms_options['filter']['include_delete_option']=1;} 
	if ($_REQUEST['dbf_imd']=="1"){$dbforms_options['filter']['dbf_imd']=1;} 
	if ($records_per_page){$dbforms_options['filter']['rpp']=$records_per_page;} else {$dbforms_options['rpp']="All";}
	if ($_REQUEST['dbf_next']>=1){$dbforms_options['filter']['dbf_next']=$_REQUEST['dbf_next'];} 
	if (array_key_exists("dbf_edi",$_REQUEST)){$dbforms_options['filter']['include_edit_link']=$_REQUEST['dbf_edi'];} 
	if (array_key_exists("dbf_add",$_REQUEST)){$dbforms_options['filter']['include_add_link']=$_REQUEST['dbf_add'];} 
	if (array_key_exists("dbf_eda",$_REQUEST)){$dbforms_options['filter']['include_edit_all_link']=$_REQUEST['dbf_eda'];} 
	if (array_key_exists("dbf_udl",$_REQUEST)){$dbforms_options['filter']['include_upload_data_link']=$_REQUEST['dbf_udl'];} 
	if ($records_per_page>=1){$dbforms_options['filter']['dbf_rpp']=$records_per_page;} 
	if (array_key_exists("dbf_rpp_sel",$_REQUEST)){$dbforms_options['filter']['dbf_rpp_sel']=$_REQUEST['dbf_rpp_sel'];} 
	if ($_REQUEST['dbf_sort']>=1){$dbforms_options['filter']['dbf_sort']=$_REQUEST['dbf_sort'];} 
	if ($_REQUEST['dbf_sort_dir']>=1){$dbforms_options['filter']['dbf_sort_dir']=$_REQUEST['dbf_sort_dir'];} 
	if ($_REQUEST['dbf_filter']>=1){$dbforms_options['filter']['dbf_filter']=$_REQUEST['dbf_filter'];} 
	if ($_REQUEST['dbf_search']>=1){$dbforms_options['filter']['dbf_search']=$_REQUEST['dbf_search'];} 
	if (!$_REQUEST['always_raw_data'] && !$CONFIG['always_raw_data']){
		if ($_REQUEST_SAFE['t']){$dbf_tablename=$_REQUEST_SAFE['t'];}
		if ($_REQUEST_SAFE['tablename']){$dbf_tablename=$_REQUEST_SAFE['tablename'];}
		$form_action=$_REQUEST['action'];
		if ($optional_action){ $form_action=$optional_action;}
		if ($form_action=="process_update_table"){$form_action=$optional_action;}
		if ($form_action=="process_multiple_records"){$form_action=$optional_action;}
		$registered_filter=self::filter_registered_on_table($dbf_tablename,$form_action);
		if ($registered_filter){
			$dbforms_options['filter']=self::load_dbforms_filter($registered_filter,$dbforms_options['filter']);
		}
		if ($_REQUEST['filter_id']){$dbforms_options['filter']=load_dbforms_filter($_REQUEST['filter_id'],$dbforms_options['filter']);}
		if ($_GET['filter_id'] && !$_REQUEST['filter_id'] && preg_match("/administrator/",$_SERVER['PHP_SELF'])){$dbforms_options['filter']=load_dbforms_filter($_GET['filter_id'],$dbforms_options['filter']);}

	} // end always raw data
	
	// hmm filter is overriding the rpp if its sent via a request
	if ($_REQUEST_SAFE['dbf_rpp']){$dbforms_options['filter']['dbf_rpp']=$_REQUEST_SAFE['dbf_rpp']; $records_per_page=$_REQUEST_SAFE['dbf_rpp'];}	
	// also, the limit remains stuck at the default $records_per_page so need to set this to the filter value if there is one	
	if ($dbforms_options['filter']['dbf_rpp']){$records_per_page=$dbforms_options['filter']['dbf_rpp'];}

	// look for filter keys in post
	if ($_POST['dbf_after_update']){$dbforms_options['filter']['after_update'] = $_POST['dbf_after_update'];}
	if (($_POST['dbf_data_filter_value'] || strlen($_POST['dbf_data_filter_value'])) && !$_POST['clear_filtering_post']){
		// the mapping is done here as well
		$dbforms_options['filter']['field_equals'] = $_POST['dbf_data_filter_field'] . " " . $_POST['dbf_data_filter_operator'] . " " . $_POST['dbf_data_filter_value'];
	}
	if ($_POST['pass_keys_as_hidden_fields']){
		$hidden_field_keys=explode(",",$_POST['pass_keys_as_hidden_fields']);
		foreach ($hidden_field_keys as $hidden_field_key){
			$dbforms_options['filter'][$hidden_field_key] = $_POST[$hidden_field_key];
		}
	}
	//open_col2(); // THIS LINE SHOULD NOT BE NECESSARY AT THIS POINT MATT PLATTS

	// records per page - has to override limit of course, so this is re-included with full functionality
	if (($_REQUEST_SAFE['dbf_rpp'] && ($_REQUEST_SAFE['dbf_rpp'] != "All" && $_REQUEST_SAFE['dbi_rpp'] != "Nil")) || (filter_key_exists("dbf_rpp",$_REQUEST['filter_id'])) || ($dbforms_options['rpp'] != "All" && $dbforms_options['rpp'] != "Nil")){
		 // critical update here - just added the third main or here!
		 $dbforms_options['filter']['limit']=$records_per_page;
		 if ($_REQUEST['dbf_next']){
			$dbforms_options['filter']['limit_from'] = $_REQUEST['dbf_next']-1;
			if ($_REQUEST['dbf_direction']=="Static" && $dbforms_options['filter']['limit_from']>1){
				$new_from=$_REQUEST['dbf_next']-($_REQUEST['dbf_rpp_pre']+1);
				$dbforms_options['filter']['limit_from']=$new_from;
			}
			if ($_REQUEST['dbf_direction']=="Reset"){
				$dbforms_options['filter']['limit_from']=0;
			}
			if ($_REQUEST['dbf_direction']=="Down"){
				$new_from = $dbforms_options['filter']['limit_from']-(($dbforms_options['filter']['limit']*2));
				if ($new_from<0){$new_from=0;}
				$dbforms_options['filter']['limit_from']=$new_from;
			}
			$dbforms_options['filter']['limit']=$dbforms_options['filter']['dbf_rpp'];
			if ($debug){print "<p>ITS SET, limit from is " . $dbforms_options['filter']['limit_from'] . " and limit is " . $dbforms_options['filter']['dbf_rpp'];}
		} else {
			$dbforms_options['filter']['limit_from']="0";
		}
	} elseif ($_REQUEST['dbf_rpp'] != "All") {
	
		$dbforms_options['filter']['limit_from']="0";
		$dbforms_options['filter']['limit']="All";
	} else {
		$dbforms_options['filter']['limit_from']="0";
	}
	if ($dbforms_options['filter']['limit']=="Nil"){
		$dbforms_options['filter']['limit']="0";
	}
	// MATT ADDED THIS DECEMBER 23 2009
	if ($_REQUEST['dbf_direction']=="Reset"){
		$dbforms_options['filter']['limit_from']=0;
	}
	if ($debug){
		print "<p>At var setting, limit is " . $dbforms_options['filter']['limit'] . " and limit from is " . $dbforms_options['filter']['limit_from'] . "<p>";
	}
	if ($_REQUEST['preUrl']){$dbforms_options['back_url']=get_preUrl_string($_REQUEST['preUrl']);}

	if ($_REQUEST['t']=="configuration"){
		$dbforms_options['filter']['config_value']['concat_field']=100;
	}

	// finally, if there are any values in the select_lists that we need to apply, add these to the filter
	//if ($_REQUEST['t'] || ($_REQUEST['tablename'] && ($_POST['edit_type']=="edit_single" || $_POST['edit_type']=="edit_all")))
	if ($_REQUEST['t'] || $_REQUEST['tablename']){
		$selection_list=array();
		$tablename=$_REQUEST['t'];
		if (!$tablename){$tablename=$_REQUEST['tablename'];}
		$select_list_sql="SELECT * from select_lists where table_name = '".$tablename."'";
		$sls_res=$db->query($select_list_sql) or format_error("Error checking select lists: " . $db->db_error(),1);
		while ($h=$db->fetch_array($sls_res)){
			if (!is_array($selection_list[$h['field_name']])){
				$selection_list[$h['field_name']]=array();
			}
			array_push($selection_list[$h['field_name']],$h['item']);
		}
		foreach ($selection_list as $field_name => $select_value){ // this is the table field
			$selection_list[$field_name]=join(",",$selection_list[$field_name]);
			if (!$dbforms_options['filter'][$field_name]['select_value_list'] && !$dbforms_options['filter'][$field_name]['do_not_load_from_select_lists']){
				$dbforms_options['filter'][$field_name]['field_config']="select_list";
				$dbforms_options['filter'][$field_name]['field_type']="select";
				$dbforms_options['filter'][$field_name]['select_value_list']=$selection_list[$field_name];	
			}
		}
	}
	if ($_REQUEST['relation_key'] && $_REQUEST['relation_id']){
		if (!$tablename){$tablename=$_REQUEST['tablename'];}
		$child_table_key_field=self::get_child_key_field_from_child_table($tablename,$_REQUEST['relation_id']);
		$master_table_key_field=self::get_master_key_field_from_child_table($tablename,$_REQUEST['relation_id']);
		$dbforms_options['filter'][$child_table_key_field]['child_key_field']=1;
		$dbforms_options['filter'][$child_table_key_field]['master_key_field']=$master_table_key_field;
	}
	if ($_REQUEST['dbf_rpp']=="All"){
		$dbforms_options['filter']['limit_from']="0";
	}
	return $dbforms_options;
}


/*
 * Function: load_options_defaults
*/
static function load_options_defaults(){
	$dbforms_options=array();
	global $CONFIG;
	$default_list_values=$CONFIG['table_list_defaults'];
	if ($_REQUEST['action']=="list_query_v2" || strlen(stristr($_REQUEST['t'],"QUERY:"))){
		$default_list_values=$CONFIG['query_list_defaults'];
	}
	$default_vals_array=explode("&",$default_list_values);
	foreach ($default_vals_array as $dPair){
	$dvp=explode("=",$dPair);
		if ($dvp[0]=="dbf_ido"){$dbforms_options['filter']['include_delete_option']=$dvp[1];}
		if (array_key_exists("dbf_ido",$_REQUEST) && !$_REQUEST['dbf_ido']){$dbforms_options['filter']['include_delete_option']=0;}
		if ($dvp[0]=="dbf_edi"){$dbforms_options['filter']['include_edit_link']=$dvp[1];}
		if ($dvp[0]=="dbf_eda"){$dbforms_options['filter']['include_edit_all_link']=$dvp[1];}
		if ($dvp[0]=="dbf_add"){$dbforms_options['filter']['include_add_link']=$dvp[1];}
		if ($dvp[0]=="dbf_imd"){$dbforms_options['filter']['imd']=$dvp[1];}
		if ($dvp[0]=="dbf_sort"){$dbforms_options['filter']['dbf_sort']=$dvp[1];}
		if ($dvp[0]=="dbf_search"){$dbforms_options['filter']['dbf_search']=$dvp[1];}
		if ($dvp[0]=="dbf_rpp_sel"){$dbforms_options['filter']['dbf_rpp_sel']=$dvp[1];}
		if ($dvp[0]=="dbf_sort_dir"){$dbforms_options['filter']['dbf_sort_dir']=$dvp[1];}
		if ($dvp[0]=="dbf_filter"){$dbforms_options['filter']['dbf_filter']=$dvp[1];}
		if ($dvp[0]=="dbf_rpp"){$dbforms_options['filter']['rpp']=$dvp[1];}
	}
	return $dbforms_options;
}

/*
 * Function get_master_table_from_relationship
*/
static function get_master_table_from_relationship($tablename,$relation_id){
	global $db;
	$sql = "SELECT * from table_relations WHERE id = $relation_id AND table_2 = \"$tablename\" AND relationship = \"one to many\"";
	$res=$db->query($sql) or die($db->db_error());
	while ($h=$db->fetch_array($res)){
		$return_master_table=$h['table_1'];
	}
	return $return_master_table;
}

/*
 * Function get_child_key_field_from_child_table
*/
static function get_child_key_field_from_child_table($tablename,$relation_id){
	global $db;
	$sql = "SELECT * from table_relations WHERE id = $relation_id AND table_2 = \"$tablename\" AND relationship = \"one to many\"";
	$res=$db->query($sql) or die($db->db_error());
	while ($h=$db->fetch_array($res)){
		$return_key_field = $h['field_in_table_2'];
	}
	return $return_key_field;
}


/*
 * Function get_master_name_field_from_master_table
 * Returns: String (name of key field from master table)
*/
static function get_master_name_field_from_master_table($tablename,$relation_id){
	global $db;
	$sql = "SELECT table_1, master_table_name_field from table_relations WHERE id = $relation_id AND table_2 = \"$tablename\" AND relationship = \"one to many\"";
	$res=$db->query($sql) or die($db->db_error());
	while ($h=$db->fetch_array($res)){
		$return_name_field = $h['master_table_name_field'];
		$master_table=$h['table_1'];
	}
	if (!$return_name_field){
		// can we find one?
		$master_fields=list_fields_in_table($master_table);
		foreach ($master_fields as $mf){
			if ($mf=="name"){ $return_name_field=$mf;} else if (!$return_name_field && $mf=="title"){ $return_name_field=$mf;}
		}
	}
	return $return_name_field;
}

/*
 * Function get_master_table_name
 * Return String (name of master table)
*/
static function get_master_table_name($tablename,$relation_id){
	global $db;
	$sql = "SELECT table_1 from table_relations WHERE id = $relation_id AND table_2 = \"$tablename\" AND relationship = \"one to many\"";
	$res=$db->query($sql) or die($db->db_error());
	while ($h=$db->fetch_array($res)){
		$return_field = $h['table_1'];
	}
	return $return_field;

}

/*
 * Function get_master_key_field_from_child_table
*/
static function get_master_key_field_from_child_table($tablename,$relation_id){
	global $db;
	$sql = sprintf("SELECT * from table_relations WHERE id = '%s' AND table_2 = '%s' AND relationship = \"one to many\"",$db->db_escape($relation_id),$db->db_escape($tablename));
	$res=$db->query($sql) or format_error("Cannot get master key field: " . $db->db_error(),1);
	while ($h=$db->fetch_array($res)){
		$return_key_field = $h['field_in_table_1'];
	}
	return $return_key_field;
}

/* 
 * Function process_file_upload
 * Meta: Send it the field name and it will return the value to place in the field
*/
static function process_file_upload($upload_field_name,$options){
	global $user;
	global $db;
	$user_id = $user->value("id");
	$db_fieldname = str_replace("new_","",$upload_field_name);
	if (preg_match("/id_\d+_/",$upload_field_name)){
		$db_fieldname=preg_replace("/id_\d+_/","",$upload_field_name);
	}
	$uploaddir = getcwd() . "/" . $options['filter'][$db_fieldname]['file_upload_directory'] . "/";
	$original_filename_parts = explode(".",basename($_FILES[$upload_field_name]['name']));
	$save_as_filename=$options['filter'][$db_fieldname]['file_upload_generate_name'];
	if (!$options['filter'][$db_fieldname]['file_upload_generate_name']){
		$save_as_filename=$original_filename_parts[0];
	}
	$save_as_filename=str_replace("{=original}",$original_filename_parts[0],$save_as_filename);
	$save_as_filename=str_replace("{=time}",time(),$save_as_filename);
	$save_as_filename=str_replace("{=userid}",$user_id,$save_as_filename);
	if (!$options['filter']['file_upload_dont_remove_illegal_chars']){
		//$save_as_filename = preg_replace("/[, %+&*()\"'\[\]@#~:;<>\?]/","-",$save_as_filename);
		$save_as_filename = preg_replace('/[^0-9a-z-_.]+/i', '', $save_as_filename);
	}
	$spare_filename=$save_as_filename;
	$save_as_filename .= "." . $original_filename_parts[1];
	$new_file_including_path = $uploaddir . $save_as_filename;
	if (file_exists($new_file_including_path)){
		if (preg_match("/-(\d+)$/",$spare_filename,$matches)){
			$num=$matches[1];
			$newnum=intval(intval($num)+1);
			$save_as_filename=str_replace("-$num","-$newnum",$spare_filename);
			$save_as_filename=$save_as_filename . $original_filename_parts[1];
		} else {
			$save_as_filename=$spare_filename . "-1" . $original_filename_parts[1];
			$second_spare_filename=$spare_filename . "-1";
			$second_spare_inc_path=$uploaddir.$second_spare_filename . $original_filename_parts[1];
			if (file_exists($second_spare_inc_path)){
				if (preg_match("/-(\d+)$/",$second_spare_filename,$matches)){
					$num=$matches[1];
					$newnum=intval(intval($num)+1);
					$save_as_filename=str_replace("-$num","-$newnum",$second_spare_filename);
					$save_as_filename=$save_as_filename . $original_filename_parts[1];
				}
			}
		}
		print "<p class=\"dbf_para_warning\">As the original file already existed, your file has been renamed to $save_as_filename</p>";
		$new_file_including_path = $uploaddir . $save_as_filename;
	}
	//$new_file_name_only = $save_as_filename . "." . $original_filename_parts[1];
	if (move_uploaded_file($_FILES[$upload_field_name]['tmp_name'],$new_file_including_path)){
                // check for macros
                $dir_to = $options['filter'][$db_fieldname]['file_upload_directory'];
                $filename=$_FILES[$upload_field_name]['name'];
		$filename = $save_as_filename; 
                $macro=value_in_table_field("file_manager_macros","directory","$dir_to","=","row");
                if ($macro){
                        foreach ($macro as $macro_action){
                                $macro_id=$macro_action['id'];
                                $sql="SELECT action,variables from file_manager_macro_actions WHERE macro_id = $macro_id ORDER BY action_order";
                                $res=$db->query($sql);
                                while ($row=$db->fetch_array($res)){
                                        $macro_results=run_file_manager_macro_action($row['action'],$filename,$dir_to,$row['variables']);
					print "<p class=\"dbf_para_info\">$macro_results</p>";
                                }
                        }
                }

		return $save_as_filename;
	} else {
		print "Error uploading file from field:" . $upload_field_name . " to " . $new_file_including_path . "<p> Check permissions on the directory and the file_upload_generate_name filter key.";
		exit;
	}
}

/*
 * Function: process_file_uploads
 * Meta: Save files uploaded through the form
*/
static function process_file_uploads(){
	global $db;
	$user_id = $user->value("id");
	foreach ($_FILES as $fileuploadname => $filedata ){
		$db_fieldname =str_replace("new_","",$fileuploadname);
		$uploaddir = getcwd() . "/" . $options['filter'][$db_fieldname]['file_upload_directory'] . "/";
		$original_filename_parts = explode(".",basename($filedata['name']));
		$save_as_filename=$options['filter'][$db_fieldname]['file_upload_generate_name'];
		$save_as_filename=str_replace("original",$original_filename_parts[0],$save_as_filename);
		$save_as_filename=str_replace("time",time(),$save_as_filename);
		$save_as_filename=str_replace("userid",$user_id,$save_as_filename);
		$new_file_including_path = $uploaddir . $save_as_filename . "." . $original_filename_parts[1];
		$new_file_name_only = $save_as_filename . "." . $original_filename_parts[1];
		if (move_uploaded_file($filedata['tmp_name'],$new_file_including_path)){
			$update_sql="UPDATE " . $tablename . " SET " . $db_fieldname . " = '" . $new_file_name_only . "' WHERE id = " . $db->last_insert_id();
			$update_upload_column = $db->query($update_sql);
		} else {
			print "Error uploading file " . $filedata['name'] . " to " . $uploaddir;
			exit;
		}
	}
}

/*
 * Function: eval_request
 * DEPRECATED - use function in Codeparser instead 
*/
static function eval_request($request_string_in){
	$getmatches=preg_match_all("/\\\$_[GET|POST|REQUEST]+\[[\"\']?\w+[\"\']\]/",$request_string_in,$requestmatches);	
	$request_string_out=$request_string_in;
	foreach ($requestmatches[0] as $requestmatch){
		$requestmatch_orig = $requestmatch;
		$requestmatch = str_replace("\$_GET[","",$requestmatch);
		$requestmatch = str_replace("\$_POST[","",$requestmatch);
		$requestmatch = str_replace("\$_REQUEST[","",$requestmatch);
		$requestmatch = str_replace("'","",$requestmatch);
		$requestmatch = str_replace("\"","",$requestmatch);
		$requestmatch = str_replace("]","",$requestmatch);
		$requestvalue = $_POST[$requestmatch];
		$request_string_out = str_replace($requestmatch_orig,$requestvalue,$request_string_in);
	}	
	return $request_string_out;
}

/*
 * Function db_content
 * Strange name - is this used???
*/
static function db_content($filter_in){
	global $db;
	$get_filter_sql="SELECT * from filters WHERE id = " . $filter_in;
	$get_filter_result=$db->query($get_filter_sql);
	while ($get_filter_row=$db->fetch_array($result)){
		$filter_type=$get_filter_row['filter_type'];
	}
}

/*
 * Function: filter_registered_on_table
 * Dev note: move to filters class
 * Param: table (String) - name of table
 * Param: filter_action (String) (ENUM - edit_table | edit_all etc) 
*/
static function filter_registered_on_table($table,$filter_action=null){
	global $db;
	if ($filter_action=="edit_table" && $_REQUEST['edit_type']=="edit_all"){
		$filter_action="edit_all";
	}
	$return_registered_filter = NULL;
	$front=preg_match("/site.php/",$_SERVER['PHP_SELF']);
	
	$registered_filter_sql="SELECT * from registered_filters where tablename = '" . $table . "'";
	if ($filter_action && $filter_action != "apply_to_all"){
		$registered_filter_sql.= " AND (limit_interface_types_to = '$filter_action' OR limit_interface_types_to IS NULL OR limit_interface_types_to ='apply_to_all')";
	}

	if ($front) $registered_filter_sql .= " AND site_or_admin != \"admin only\"";
	if (!$front) $registered_filter_sql .= " AND site_or_admin != \"site only\""; 

	$registered_filter_result=$db->query($registered_filter_sql) or format_error("ERROR CODE 998779",1);
	$total_reg_rows=$db->num_rows($registered_filter_result);
	while ($registered_filter_row=$db->fetch_array($registered_filter_result)){
		if ($registered_filter_row['limit_interface_types_to'] && ($registered_filter_row['limit_interface_types_to'] != $filter_action && !$return_registered_filter && $registered_filter_row['limit_interface_types_to'] != "apply_to_all")){
		} else {
			$return_registered_filter=$registered_filter_row['filter_id'];
		}
		// finally, only pass it if it applies to the site or admin
		$front=preg_match("/site.php/",$_SERVER['PHP_SELF']);
		if ($front && $registered_filter_row['site_or_admin']=="admin only" || !$front && $registered_filter_row['site_or_admin']=="site only"){
			$return_registered_filter="";
		}
	}	

	return $return_registered_filter;
}

/*
 * Function: check_dbf_permissions
*/
static function check_dbf_permissions($table, $permission_type, $permission_rowid=null, $options=null){
	global $db;
	global $user;
	
	$debug=0;
	# 1. master account and superadmins always win..
	if ($user){
		if ($user->value("type")=="superadmin" && $permission_type != "drop"){return array("Status"=>1);}
		if ($user->value("type")=="master" && $permission_type != "drop"){return array("Status"=>1);}
	}

	// first select all permissions on the table where the type matches and load the rows into $permissions_on_table
	// then we can check if the permission itself passes other criterea
	$permissions_sql="SELECT * from permissions WHERE tablename = '" . $table . "' AND (permission_type = '" . $permission_type . "' OR permission_type='all')";
	if ($debug){print $permissions_sql;}
	$permissions_result=$db->query($permissions_sql);
	$permissions_on_table=array();
	while ($permissions_row = $db->fetch_array($permissions_result)){
		array_push ($permissions_on_table,$permissions_row);
	}

	// whitelist only so if no permissions are found, we can return an error message already, unless its the master user in which case return good.
	if (!$permissions_on_table){
		if ($user->value("type")=="master"){
			$return_data=array("Status" => 0, "Message" => format_error("No permissions found on $table for the $permission_type action"), "Code" => "9598");
		} else { 
			$fail_msg=format_error("Permissions are not set for you to edit this section.");
			if ($options['filter']['permissions_fail_alternate_message']){ $fail_msg=$options['filter']['permissions_fail_alternate_message']; }
			$return_data=array("Status" => 0, "Message" => $fail_msg, "Code" => "9598");
		}
		return $return_data;

	}	

	// loop through permissions, see if everything passes ok
	$permission_check_result=array();
	$check_returned_rows=array();
	foreach ($permissions_on_table as $table_permission){

		// first check for userid permissions in a table field, if that exists then check the record exists to be edited. If so, pass it
		if ($table_permission['value']=="current_user()"){
			if (!$user->value("id")){ /*print "no user id as not logged in";*/ continue; }
			// if its view there may not be any rows returned but this doesnt mean it doesnt pass
			// however this is going to NEED to be checked later when the results come back for matches?
			if ($permission_type=="view"){
				array_push($permission_check_result,"1"); 
				array_push($check_returned_rows,$table_permission['setting'] . ":" . $table_permission['operator']);
			}
			if ($debug){ print "its a user permission";}
			print "<!-- type h is " . $permission_type ." //-->";
			if ($permission_type != "add_or_edit" || ($permission_type=="add_or_edit" && $permission_rowid)){
				$table_permission['value'] = $user->value("id");
	//print "setting of " . $table_permission['value'] . " to match user id of " . $user->value("id");
				$check_sql="SELECT * from $table WHERE " . $table_permission['setting'] . $table_permission['operator'] . $table_permission['value'];
				if ($permission_rowid){
					$check_sql .= " AND " . $table_permission['setting'] . $table_permission['operator'] . $permission_rowid;
				} 
				if ($debug){print "<p>check is $check_sql</p>";}
				print "<!-- check sql is " . $check_sql ." //-->";
				$check_result=$db->query($check_sql);
				while ($check_sql_row=$db->fetch_array($check_result)){
					array_push($permission_check_result,$check_sql_row);
				}
			} else {
				// temporarily let it through - this is add_or_edit with no rowid (so should be a straight add, nothing to worry about)
				array_push($permission_check_result,"1");
			}
		}
		//secondly if its a permission on a sysUserType, see if this matches the current user type
		if ($table_permission['setting']=="sysUserType" && $user->value("type")==$table_permission['value']){
			array_push($permission_check_result,"1");
			$check_returned_rows=""; // the user type passes, no row checking required
		} 
		//thirdly if its a permission on a specific userid compare to the current userid
		if ($table_permission['setting']=="user_id" || $table_permission['setting']=="userid" && $user->value("id")==$table_permission['value']){
			array_push($permission_check_result,"1");	
			$check_returned_rows="";
		}
		//fourthly -  hierarchial types
		if ($table_permission['setting']=="sysUserHierarchialType"){
			$eval_permission=0;
			$permission_eval_code = "\$eval_permission = (" . $user->value("hierarchial_order") . $table_permission['operator'] . $table_permission['value'] . ")? 1:0;";	
			$eval_result=eval($permission_eval_code);
			if ($eval_permission){
				array_push($permission_check_result,"1");
				$check_returned_rows="";
			}
			else { print "doesnt eval!"; }
		}
		// fifthly, if the value is simply 1 (free for all) then pass it
		if ($table_permission['value']=="1"){
			array_push($permission_check_result,"1");
		}
	}

	// no results, fail the attempt
	if (!$permission_check_result){
		if ($options['filter']['permissions_fail_alternate_message']){
			$fail_msg=$options['filter']['permissions_fail_alternate_message']; 
		} else {
			$fail_msg=format_error("Error: Permissions did not check out correctly on $table. This action cannot be performed.");

		}
		$return_data=array("Status" => 0, "Message" => $fail_msg, "Code" => "9596");
		return $return_data;
	}

	# everything fine from here, return check_returned_rows for further checking when a recordset is generated
	# if this flag is set
	$return_data=array("Status" => 1, "Message" => "Permissions found on $table", "Code" => "1");
	if ($check_returned_rows){ $return_data['check_returned_rows']=join(";",$check_returned_rows); }
	if ($permission_check_result){ return $return_data;}
}

static function get_dependent_fields($field,$filter_hash){
	// are any other fields dependent on the value of this one? If so return a list
	$dependent_fields=array();
	foreach ($filter_hash as $filter_hash_var => $filter_hash_val){
		foreach ($filter_hash_val as $next_hash => $next_hash_val){
			//print "<p>ON " . $filter_hash_var . "<br>";
			//print $next_hash. " = " . $next_hash_val . "<br>";
			if (preg_match("/^SQL:[\w\s,]+\sWHERE\s\w+\s\=\s{\=$field\}/",$next_hash_val)){
				//print "<b>$filter_hash_var is dependent on $field</b><br>";
				array_push($dependent_fields,$filter_hash_var);
			}
		}
	}
	return $dependent_fields;
}

static function populate_search_fields($table,$filter_hash,$selected){

	global $db;

	if ($filter_hash['search_fields']){
		$search_field_list=$filter_hash['search_fields'];
	} elseif ($filter_hash['display_fields']){
		$search_field_list=$filter_hash['display_fields'];
	}
	if ($search_field_list){
		$fields_to_display_array=explode(",",$search_field_list);
	} else {
		$fields_to_display_array = array();
		$sql_for_tablefields="DESC " . $table;
		$desc_result = $db->query($sql_for_tablefields);
		while ($desc_row = $db->fetch_array($desc_result)){
			array_push($fields_to_display_array,$desc_row['Field']);
		}	
	}

        // dynamic fields to be used to replace?
        if ($_REQUEST['dbf_dynamic_fields_to_display_list']){
                $dyn_fields_to_display_list=$_REQUEST['dbf_dynamic_fields_to_display_list'];
                //print "Have a list of " . $dyn_fields_to_display_list;
        }
        if ($_REQUEST['dbf_dynamic_fields_to_display']){
                if (count($_REQUEST['dbf_dynamic_fields_to_display']>=1)){
                        $dyn_fields_to_display_list_flat=join(",",$_REQUEST['dbf_dynamic_fields_to_display']);
                        if ($dyn_fields_to_display_list_flat){
                                $dyn_fields_to_display_list=$dyn_fields_to_display_list_flat;
                        }
                }
        }
        if ($_REQUEST['dbf_dynamic_mootools_fields_to_display_list']){
                $fields_to_display_array=explode(",",$_REQUEST['dbf_dynamic_mootools_fields_to_display_list']);
        }
	if ($_REQUEST['dbf_dynamic_search_fields_list']){
		$fields_to_display_array=explode(",",$_REQUEST['dbf_dynamic_search_fields_list']);
	}
	$current_fields_as_options = "<option value=\"All Fields\">All Fields</option>";
	foreach ($fields_to_display_array as $display_field){
		$current_fields_as_options .= "<option value=\"$display_field\"";
		if ($display_field == $selected){$current_fields_as_options .= " selected";}
		$display_field_text=preg_replace("/_/"," ",$display_field);
		$display_field_text=ucfirst($display_field_text);
		$current_fields_as_options .= ">$display_field_text</option>";
	}
	return $current_fields_as_options;
}

static function populate_sort_fields($table,$filter_hash,$selected){

	global $db;

	if ($filter_hash['sort_fields']){
		$sort_field_list=$filter_hash['sort_fields'];
	} elseif ($filter_hash['display_fields']){
		$sort_field_list=$filter_hash['display_fields'];
	}
	if ($sort_field_list){
		$fields_to_display_array=explode(",",$sort_field_list);
	} else {
		$fields_to_display_array = array();
		if ($db->table_exists($table)){
			$sql_for_tablefields="DESC " . $table;
			$desc_result = $db->query($sql_for_tablefields);
			while ($desc_row = $db->fetch_array($desc_result)){
				array_push($fields_to_display_array,$desc_row['Field']);
			}	
		}
	}

	// dynamic fields to be used to replace?
	if ($_REQUEST['dbf_dynamic_fields_to_display_list']){
		$dyn_fields_to_display_list=$_REQUEST['dbf_dynamic_fields_to_display_list'];
		//print "Have a list of " . $dyn_fields_to_display_list;
	}
	if ($_REQUEST['dbf_dynamic_fields_to_display']){
		if (count($_REQUEST['dbf_dynamic_fields_to_display']>=1)){
			$dyn_fields_to_display_list_flat=join(",",$_REQUEST['dbf_dynamic_fields_to_display']);
			if ($dyn_fields_to_display_list_flat){
				$dyn_fields_to_display_list=$dyn_fields_to_display_list_flat;
			}
		}
	}
	if ($_REQUEST['dbf_dynamic_mootools_fields_to_display_list']){
		$dyn_fields_to_display_list=$_REQUEST['dbf_dynamic_mootools_fields_to_display_list'];
		//print "Have a list of " . $dyn_fields_to_display_list;
	}
	if ($dyn_fields_to_display_list){
		$fields_to_display_array=explode(",",$dyn_fields_to_display_list);
	}	

	foreach ($fields_to_display_array as $display_field){

                if (strpos($display_field,";;")){
                        $pair=explode(";;",$display_field);
                        $option_value=$pair[0];
                        $option_text=$pair[1];
                } else {
                        $option_value = $display_field;
                        $option_text = $display_field;
                }

		$current_fields_as_options .= "<option value=\"$option_value\"";
		if ($option_value == $selected){$current_fields_as_options .= " selected";}
		$display_field_text=preg_replace("/_/"," ",$option_text);
		$display_field_text=ucfirst($display_field_text);
		$current_fields_as_options .= ">$display_field_text</option>";
	}
	return $current_fields_as_options;
}

static function populate_filter_fields($table,$filter_hash,$selected){

	global $db;
	if ($filter_hash['filter_fields']){
		$filter_field_list=$filter_hash['filter_fields'];
	} elseif ($filter_hash['display_fields']){
		$filter_field_list=$filter_hash['display_fields'];
	}
	if ($filter_field_list){
		$fields_to_display_array=explode(",",$filter_field_list);
	} else {
		$fields_to_display_array = array();
		$sql_for_tablefields="DESC " . $table;
		$desc_result = $db->query($sql_for_tablefields);
		while ($desc_row = $db->fetch_array($desc_result)){
			array_push($fields_to_display_array,$desc_row['Field']);
		}	
	}

        // dynamic fields to be used to replace?
        if ($_REQUEST['dbf_dynamic_fields_to_display_list']){
                $dyn_fields_to_display_list=$_REQUEST['dbf_dynamic_fields_to_display_list'];
                //print "Have a list of " . $dyn_fields_to_display_list;
        }
        if ($_REQUEST['dbf_dynamic_fields_to_display']){
                if (count($_REQUEST['dbf_dynamic_fields_to_display']>=1)){
                        $dyn_fields_to_display_list_flat=join(",",$_REQUEST['dbf_dynamic_fields_to_display']);
                        if ($dyn_fields_to_display_list_flat){
                                $dyn_fields_to_display_list=$dyn_fields_to_display_list_flat;
                        }
                }
        }
        if ($_REQUEST['dbf_dynamic_mootools_fields_to_display_list']){
                $dyn_fields_to_display_list=$_REQUEST['dbf_dynamic_mootools_fields_to_display_list'];
                //print "Have a list of " . $dyn_fields_to_display_list;
        }
        if ($dyn_fields_to_display_list){
                $fields_to_display_array=explode(",",$dyn_fields_to_display_list);
        }

	foreach ($fields_to_display_array as $display_field){
		$current_fields_as_options .= "<option value=\"$display_field\"";
		if ($display_field == $selected){$current_fields_as_options .= " selected";}
		$display_field_text=preg_replace("/_/"," ",$display_field);
		$display_field_text=ucfirst($display_field_text);
		$current_fields_as_options .= ">$display_field_text</option>";
	}
	return $current_fields_as_options;
}

static function records_per_page_options($selected){
	global $CONFIG;
	$csl=$CONFIG['records_per_page_selections'];
	$return_string=self::csv_to_options($csl,$selected);	
	return $return_string;
}

// converts a comma separated list to html option tags
// using ;; in the list denotes option value followed by ;; followed by text
static function csv_to_options($csl,$selected=null,$classes=null){
        $select_options=array();
        $select_options=explode(",",$csl);

        foreach ($select_options as $option){
                if (strpos($option,";;")){
                        $pair=explode(";;",$option);
                        $option_value=$pair[0];
                        $option_text=$pair[1];
                } else {
                        $option_value = $option;
                        $option_text = $option;
                }

                $return_string .= "<option value=\"$option_value\"";
                if ($classes[$option_text]['class']){
                                $return_string .= " class=\"" . $classes[$option_text]['class'] . "\"";
                }
                if ($option_value == $selected){ $return_string .= " selected";}
                $return_string .= ">$option_text</option>";
        }
        return $return_string;
}

static function search_criterea_applied(){
	if ($_REQUEST_SAFE['dbf_search'] || $_REQUEST_SAFE['dbf_sort'] || $_REQUEST_SAFE['dbf_rpp_sel'] || $_REQUEST_SAFE['dbf_filter']){
		return 1;
	} else {
		return 0;
	}
}

static function date_input_field($selected_date,$rowid_for_edit,$fieldname,$options=null){
	// selected date is expected in the format yyyymmdd as per mysql format;
	$date_year=substr($selected_date,0,4);
	$date_month=substr($selected_date,5,2);
	$date_day=substr($selected_date,8,2);
	// if no further options, go for default options 
	if (!$options['year_options']){$options['year_options']="1998-2018";} else {$options['year_options']=self::build_year_options_range($options['year_options']);}
	if (!$options['month_options']){
		$options['month_options']=array("" => "","01" => "January","02" => "February","03" => "March","04" => "April","05" => "May","06" => "June","07" => "July","08" => "August","09" => "September","10" => "October","11" => "November","12" => "December");
	} else {$options['month_options']=self::build_month_options($options['year_options']);}
	if (!$options['day_options']){
		$options['day_options']=array("","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
}
	if ($options['text_options']){ // if you want extre text by each select. Eg, D,M,Y
		$day_text=$options['text_options']['day_text'];
		$month_text=$options['text_options']['month_text'];
		$year_text=$options['text_options']['year_text'];
	}
	$year_start_and_end=explode("-",$options['year_options']);
	$year_start=$year_start_and_end[0];
	$year_end=$year_start_and_end[1];
	$year_options=array();
	array_push($year_options,"");
	do {
	array_push($year_options,$year_start);
	$year_start++;
	} while ($year_start <= $year_end);
	
	// get fieldnames
	if ($rowid_for_edit){
		$day_fieldname="id_".$rowid_for_edit."_".$fieldname."-1";
		$month_fieldname="id_".$rowid_for_edit."_".$fieldname."-2";
		$year_fieldname="id_".$rowid_for_edit."_".$fieldname."-3";
	} else {
		$day_fieldname="new_".$fieldname."-1";
		$month_fieldname="new_".$fieldname."-2";
		$year_fieldname="new_".$fieldname."-3";
	}
	
	$return_date = "$day_text <select name=\"$day_fieldname\" class=\"dbf_date_input_day\">";
	foreach ($options['day_options'] as $day_option){
		$return_date .="<option value=\"$day_option\"";
		if ($date_day==$day_option){ $return_date .=" selected";}
		$return_date .=">$day_option</option>";
		}
	$return_date .="</select> ";
	$return_date .="$month_text <select name=\"$month_fieldname\" class=\"dbf_date_input_month\">";
	foreach ($options['month_options'] as $month_option => $month_text){
		$return_date .="<option value=\"$month_option\"";
		if ($date_month==$month_option){ $return_date .= " selected";}
		$return_date .= ">$month_text</option>";
		}
	$return_date .= "</select> ";
	
	$return_date .= "$year_text <select name=\"$year_fieldname\" class=\"dbf_date_input_year\">";
	foreach ($year_options as $year_option){
		$return_date .= "<option value=\"$year_option\"";
		if ($date_year==$year_option){ $return_date .= " selected";}
		$return_date .= ">$year_option</option>";
		}
	$return_date .= "</select>";
	return $return_date;
}

static function datepicker_input_field($selected_date,$rowid_for_edit,$fieldname,$options){
	if ($selected_date && substr($selected_date,0,4) != "0000"){
		$year=substr($selected_date,0,4);
		$month=substr($selected_date,5,2);
		$day=substr($selected_date,8,2);
		$selected_date=$day."/".$month."/".$year;
	} else {
		$selected_date="";
	}
	$return_date .= '<link rel="stylesheet" type="text/css" href="css/date_picker_input_type.css" />';
	$return_date .= '<script type="text/javascript" src="scripts/date_picker_input_field.js"></script>';
	$return_date .= "\n";
	$return_date .= '<script type="text/javascript">' . "\n" . 'window.addEvent(\'domready\', function() {';
	$return_date .= "\n";
	$return_date .= "myCal_".$fieldname." = new Calendar({ $fieldname: 'd/m/Y' }, { classes: ['alternate'], direction: 0, navigation: 2 });";
	$return_date .= " });\n</script>\n"; 
	$return_date .= "<input type=\"text\" name=\"$fieldname\" id=\"$fieldname\" value=\"$selected_date\" />\n";
	return $return_date;
}

static function mail_form_data($table,$id,$options){

	global $db;

        $mail_to=$options['filter']['after_update_mail_form_recipient'];
        $mail_from=trim($options['filter']['after_update_mail_form_from']);
        $template=$options['filter']['after_update_mail_form_template'];

        $pk=get_primary_key($table) or format_error("Error 67172PJ",1);
        // select row
        $sql="SELECT * from $table WHERE $pk = $id";
        $result=$db->query($sql) or format_error("ERROR: A141" . $db->db_error(),1);
        $fieldnames=list_fields_in_table($table);
        while ($h=$db->fetch_array($result)){
                foreach ($fieldnames as $fieldname){
                        $mailtext .= "$fieldname: " . $h[$fieldname] . "\n";
                }
                $rowdata=$h;
        }

        if ($template){
                $templatedata=$db->field_from_record_from_id("templates",$template,"template");
                foreach ($rowdata as $key=> $val){
                        $replacestring="{=".$key."}";
                        $templatedata=str_replace($replacestring,$val,$templatedata);
                }
        }

        $mail_from_address=$mail_from;
        if (preg_match("/{=formfield:.*}/",$mail_from)){
                //$mail_from_formfield=$mail_to;
                $mail_from=str_replace("{=formfield:","",$mail_from);
                $mail_from=trim(str_replace("}","",$mail_from));
                $mail_from_address=$db->db_escape($_POST[$mail_from]);
        }

        $headers = "Content-type: text/html\r\n";
        $headers .= "From: $mail_from_address\r\n";
        $headers .= "Reply-To: $mail_from_address\r\n";
        $subject=$options['filter']['after_update_mail_form_subject'];
        if (preg_match("/{=formfield:.*}/",$mail_to)){
                $mail_to_formfield=$mail_to;
                $mail_to=str_replace("{=formfield:","",$mail_to);
                $mail_to=trim(str_replace("}","",$mail_to));
                $mail_to=$db->db_escape($_POST[$mail_to]);
        }
        if (preg_match("/{=formfield:.*}/",$subject)){
                $subject_formfield=$subject;
                $subject_formfield=str_replace("{=formfield:","",$subject_formfield);
		$subject_formfield=trim(str_replace("}","",$subject_formfield));
		$subject=$db->db_escape($_POST[$subject_formfield]);
        }

        mail ($mail_to,$subject,$templatedata,$headers);
}

static function build_year_options_range($year_options){
	$year_range_options=str_replace("current",date("Y"),$year_options);
	$year_range_options=explode(">",$year_range_options);
	$new_year_options=array();
	foreach ($year_range_options as $year_range_option){
			preg_match("/[-|+]/",$year_range_option,$delim);
			$splityears=preg_split("/[-|+]/",$year_range_option);
			if ($delim[0]=="-"){
				$year_range_option=$splityears[0]-$splityears[1];
			} else if ($delim[0]=="+"){
				 $year_range_option=$splityears[0]+$splityears[1];
			}
	array_push($new_year_options,$year_range_option);
	}
	$return_string=$new_year_options[0] . "-" . $new_year_options[1];
	return $return_string;
}

static function build_month_options($month_options){
	
	return $month_options;

}

/*b May have been moved to recordset.. */
static function data_to_excel_spreadsheet($data,$tablename){

	require_once("library/modules/excel.php"); // load excel writing functions
	// complare sent fields with headers
	$nHeadings=array();
	foreach ($data as $row){
		foreach ($row as $column => $value){
			array_push($nHeadings,$column);
		}
		break;
	}
	
	// START XL STREAM HERE (headers)
	$iCols = count($nHeadings);
	$sXlOut = xlsBOF();   // begin Excel stream

	$sXlOut .= xlsWriteLabel(0,0,"$tablename - " . date('Y-m-d')) ; // write title into excel spreadsheet - table name and date

	for ($i = 0; $i < $iCols; $i++) {
		$sXlOut .= xlsWriteLabel(2, $i, ucFirst(str_replace("_"," ",$nHeadings[$i])));
	}
	$linecounter=3;
	foreach ($data as $row){
		$cellnumber=0;
		foreach ($row as $column => $value){
			$sXlOut .= xlsWriteLabel($linecounter,$cellnumber, $value);
			$cellnumber++;
		}
		$linecounter++;
	 }
	ob_end_clean();
	$sXlOut .= xlsEOF(); // close the stream

	/* no cache */
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //always modified
	header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0

	/* application */
	header("Content-type: application/vnd.ms-excel");
	header("Content-Disposition: inline; filename=$tablename" . "-" . $sType . date('Ymd') . ".xls");
	header("Content-Description: PHP3 Generated Data" );
	header("Content-Length: ".strlen($sXlOut));

	print $sXlOut;
	exit;
}

static function ajax_populate_dynamic_list($table,$idfield,$keyfield){
	global $db;
	if(isset($_GET['letters'])){
		$letters = $_GET['letters'];
		$letters = preg_replace("/[^a-z0-9 ]/si","",$letters);
		$permissions_result=self::check_dbf_permissions($table,"view");
		if ($permissions_result['Status']==0){ exit; } 

		$res = $db->query("select $idfield,$keyfield from $table where $keyfield like '".$letters."%' ORDER BY $keyfield") or die($db->db_error());
		#echo "1###select ID,countryName from ajax_countries where countryName like '".$letters."%'|";
		while($inf = $db->fetch_array($res)){
			echo $inf[$idfield]."###".$inf[$keyfield]."|";
		}
	}
}

static function site_search_list(){
	global $db;
	$letters=$_GET['letters'];
	$avail_str="(available =1 OR (allow_pre_orders=1 AND quantity_in_stock=0))";
	$sql="SELECT artists.artist as prefix, title as searchstr from products INNER JOIN artists ON products.artist=artists.id WHERE (products.title LIKE '%$letters%' AND $avail_str) UNION select 'artist' as prefix, artist as searchstr from artists WHERE (artist LIKE '%$letters%' AND active=1) UNION select 'label' as prefix, label_name as searchstr from labels WHERE label_name LIKE '%$letters%' ORDER BY searchstr";
	$return_array=array();
	$res=$db->query($sql); // Is there a reason that this can't be $db->query?
	while($inf = $db->fetch_array($res)){
		if (!$inf['prefix']){ $prefix=""; } else {$prefix = $inf['prefix']; }
		$retstr=$inf['searchstr']."###".$inf['searchstr']." ($prefix)|";
		array_push($return_array,$retstr);
	}
	$front_array=array();
	$back_array=array();
	foreach ($return_array as $return_item){
		if (preg_match("/^$letters/",$return_item)){
			array_push($front_array,$return_item);
		} else {
			array_push($back_array,$return_item);
		}
	}
	$return_array=array_merge($front_array,$back_array);
	$search_output=join("",$return_array);
	echo $search_output;
}

static function system_log_form_generation($table,$rowid,$formtype,$filter){
	
	global $db;
	global $user;
	$unique_form_id=uniqid();

	$sql="INSERT INTO sys_form_log (ip_address,uuid,gen_time,update_table,row_identifier,form_type,filter,user,user_session) values (";
	$sql .= "\"".$_SERVER['REMOTE_ADDR']."\",";	
	$sql .= "\"".$unique_form_id."\",";
	$sql .= "NOW(),";	
	$sql .= "\"".$table."\",";
	$sql .= "\"".$rowid."\",";
	$sql .= "\"".$formtype."\",";
	$sql .= "\"".$filter."\",";
	$sql .= "\"".$user->value('id')."\",";	
	$sql .= "\"".session_id()."\"";	
	$sql .= ")";
	$res=$db->query($sql) or format_error("Unable to log system form details",1);
	return $unique_form_id;
}

static function check_web_form_integrity($unique_id,$table,$formtype,$filter,$rowid){
	global $db;
	$sql="SELECT * from sys_form_log WHERE uuid='".$unique_id."'";
	$res=$db->query($sql);
	if ($db->num_rows($res)<1){
		return 0;
	}
	while ($h=$db->fetch_array()){
	
		if ($h['ip_address'] != $_SERVER['REMOTE_ADDR']){
			format_error("Internal form check error on remote address",1);
			$error=1;
		}
		if ($h['update_table'] != $table){
			format_error("Internal form check error on table",1);	
			$error=1;
		}
		if (!$filter && !$h['filter']){ //passes 
		} else {
			if ($h['filter'] != $filter){
				format_error("Internal form check error on filter between $filter and " . $h['filter'],1);	
				$error=1;
			}
		}
		if ($h['row_identifier'] != $rowid){
			format_error("Internal form check error on row identifier",1);	
			$error=1;
		}
		if ($h['form_type'] != $formtype){
			format_error("Internal form check error on type",1);	
			$error=1;
		}
		if ($h['user_session'] != session_id()){
			format_error("Internal form check error on user session",1);
		}

		global $user;
		if ($user->value('id')){
			if ($h['user'] != $user->value('id')){
				format_error("Internal form check error on user details",1);
			}
		}
	}
	if ($error){ return 0; exit;}
	return 1;
}

static function recordset_metadata($table,$filter){
	global $db;
	print "<h3><b>Meta Data</b></h3><p>Source Table: $table</p>";
	print "<p>Filter id: $filter<br />Filter name: " . $db->field_from_record_from_id("filters",$filter,"filter_name") . "<br />";
	print "Parent Filter ID: " . $db->field_from_record_from_id("filters",$filter,"parent_filter") . "</p>";
	print "<p><a href=\"Javascript:window.close()\">Close Window</a>";
	exit;
}

static function run_sql_list_code($list_code){
	$list_code=str_replace("CODE:","",$list_code);
	$list_code=trim($list_code);
	$r=call_user_func($list_code,$row_id);
	return $r;
}

static function run_sql_list_function($func,$selected){
	$func=str_replace("FUNCTION:","",$func);
	$r=call_user_func($func,$selected);
	return $r;
}

static function select_code_value_from_id($list_code,$row_id){
	$list_code=str_replace("CODE:","",$list_code);
	$r=call_user_func($list_code,$row_id);
	return $r;
}

static function products_as_select($row_id){
	
	global $db;
	if (!$row_id){
		$result_set=str_replace("CODE:","",$list_code);
		$sql="SELECT products.id,artists.artist,products.title from products INNER JOIN artists on products.artist=artists.id ORDER BY artists.artist,products.title";
		$result=$db->query($sql);
		$code_results=array();
		while ($code_res=$db->fetch_array($result)){
			array_push($code_results,$code_res['id'] . ";;". $code_res['artist'] . " - " . $code_res['title']);	
		}
		$return_set=join(",",$code_results);
		return $return_set;

	} else {

		$sql="SELECT products.id,artists.artist,products.title from products INNER JOIN artists on products.artist=artists.id WHERE products.id = $row_id ORDER BY artists.artist,products.title";
		$code_res=$db->query($sql);
		while ($cod_arr=$db->fetch_array($code_res)){
			$rv=$cod_arr['artist'] . " - " . $cod_arr['title'];
		}
		return $rv;	
	}
}

static function get_dynamic_list_value($svl,$fieldvalue){

	// essentially we may have text and not an id in here if the form is a repeat form that hasn't been processed. Basically, if it's not a number then return null for now. Worth nothing though, dynamic list values can ONLY be id,key lookups at present, need to check if the straight text version works at all!
	if (!preg_match("/^\d+$/",$fieldvalue)){
		return $fieldvalue;
	} else {
		$result=self::sql_value_from_id($svl,$fieldvalue);
	}
	return $result;
}

static function view_record($table,$row_id,$options){
	// basic record view function for viewing a single record
	global $db;
	global $page;
	global $col2_open;
	global $use_built_in_styles;
	if (!$col2_open && $use_built_in_styles){open_col2();print "<!-- col2 open from view_record //-->";}
	
	$output_text .= "<div class=\"leftAlign\">";
	if (preg_match("/QUERY:/",$table)){
		$query=str_replace("QUERY:","",$table);
		global $db;
		$query = "SELECT query from queries where query_name = \"$query\"";
		$queryresult=$db->query($query);
		$qh=$db->fetch_array($queryresult);
		$sql=Codeparser::parse_request_vars($qh['query']); 
	} else{
		$pk=get_primary_key($table);
		if ($options['filter']['display_fields']){ $field_list=$options['filter']['display_fields']; } else { $field_list = "*"; }
		$sql="SELECT $field_list from $table where $pk = $row_id";
	}
	$res=$db->query($sql);
	if ($db->num_rows($res)==0){
		print "No data returned.";
	}
	while ($h=$db->fetch_array($res)){
		foreach ($h as $key => $value){
			$EXPORT[$key]=$value;
		}
	}
	if ($options['filter']['display_in_template']){
		$template=$db->field_from_record_from_id("templates",$options['filter']['display_in_template'],"template");
		$output_text .= record_to_template($template,$EXPORT);
		$output_text = Codeparser::parse_request_vars($output_text);
	} else if ($options['filter']['display_in_admin_template']){
		$template=$db->db_quick_match("admin_templates","template","dbf_key_name",$options['filter']['display_in_admin_template']);
		$output_text .= $template;
	} else {
		if ($options['filter']['title_text']){ $print_title=$options['filter']['title_text']; } else { $print_title = ucfirst(str_replace("_"," ",$table)) . ": #" . $row_id . ""; }
		

	if ($options['filter']['link_to_list_table_from_add_and_edit'] || ($CONFIG['link_to_list_table_from_add_and_edit'] && !stristr($_SERVER['PHP_SELF'],"site.php")) && !$page->value("useAjax") && !$page->value("iframe")){ // values to be: after add one, always, never
	if ($_REQUEST['relation_key'] && $_REQUEST['relation_id']){ $include_relations = "&relation_key=".$_REQUEST['relation_key'] . "&relation_id=" . $_REQUEST['relation_id'];}
		$link_to_list_table = "<div id=\"list_table_link\" class=\"rightFloat\"><a href=\"Javascript:loadPage('" . $_SERVER['PHP_SELF'] . "?action=list_table&t=$table&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp=".$CONFIG['default_records_per_page']."&dbf_rpp_sel=1&dbf_filter=1&dbf_sort=1&jx=1$include_relations')\"><img src=\"".SYSIMGPATH."/application_images/button_previous_beige_29x28.png\" alt=\"Return to list\" title=\"Return to list\" border=0/></a></div>";
	} else { $link_to_list_table = ""; }

		$output_text .= "<div id=\"table_header_div\">$link_to_list_table";
		$output_text .= "<div class=\"table_title\" id=\"table_title_div\" class=\"zeroBottom\"> " . $print_title; 
		$output_text .= "</div></div><br clear=\"all\">";
		$output_text .= "<table class=\"form_table\">";
		foreach ($EXPORT as $key => $value){
			$output_text .="<tr><td align=\"right\" valign=\"top\" style=\"font-weight:bold; vertical-align:top; background-color:#f7f7f7\">".ucfirst(str_replace("_"," ",$key)).":</td><td>&nbsp;</td><td valign=\"top\" align=\"left\">".self::apply_filter_to_field_value($value,$options['filter'][$key]) . "</td></tr>";
		}
	}
	$output_text .= "</table>";
	$output_text .= "</div>";
	if ($options['filter']['export']=="html"){
		return $output_text;
	} else {
		print $output_text;
	}
	close_col();
	return;
}

static function apply_filter_to_field_value($value,$fieldfilter){
	$return_val=$value;
	if ($fieldfilter['select_value_list']){ $return_val = self::sql_value_from_id($fieldfilter['select_value_list'],$value);}
	if ($fieldfilter['field_prefix']){ $return_val = $fieldfilter['field_prefix'] . $return_val;}
	if ($fieldfilter['field_suffix']){ $return_val = $fieldfilter['field_suffix'] . $return_val;}

	return $return_val;
}

static function process_multiple_records($table,$action,$records){
	// run through multiple records selected via the multi checkboxes on a list page
	// currently the only action here is delete
	if (!$records){
		print "<p class=\"dbf_para_alert\">No records to delete.</p>";
	}
	$record_id_list=join(",",$records);
	$permissions_result=self::check_dbf_permissions($table,"delete");
	if ($permissions_result['Status']==0){ 
		if (!$col2_open){
			open_col2();
		}
		print $permissions_result['Message'];
		return;
	}

	$form_identifier=$db->db_escape($_POST['dbf_sys_form_id']);
	$check_form_integrity=check_web_form_integrity($form_identifier,$table,"delete",$options['filter']['dbf_filter_id'],"");
	if (!$check_form_integrity){
		format_error("Internal form check error - cannot update.",1);
	}

	$pk=get_primary_key($table);
	if ($action=="delete" && $records){
		global $db;
		$sql="DELETE FROM $table WHERE $pk IN ($record_id_list)";
		$res=$db->query($sql) or format_error("Cannot run sql",1);
		print "<p class=\"dbf_para_success\">Records Deleted</p>";
	}
	$dbforms_options=self::load_dbforms_options($table,"list_table");
	// FOR SOMEREASON displayfields key isnt working...
	list_table($table,$dbforms_options);
	print "<p><a href=\"Javascript:history.go(-1)\">Back</a></p>";
	return;
}

static function process_record_ids(){
	// return a list of ids which have been selected to be processed from the checkbox on a list page
	$process_ids=array();
	foreach ($_POST as $key => $value){
		$match=preg_match("/^del_key_(\d+)$/",$key,$keymatches);
		if (preg_match("/^del_key_\d+$/",$keymatches[0])){
			array_push($process_ids,$keymatches[1]);
		}
	}
	return $process_ids;
}

static function dbf_search_popup_open($t,$f,$c){
?>
<div id="dbf_search_popup_inner" class="search_filter_popup">
<p style="float:right; margin:0px; padding:0px; width:25px;"><input type="button" value="X" style="color:red; font-weight:bold; font-size:12px; background-color:#fff; width:22px; height:20px;" onClick="document.getElementById('dbf_search_popup').style.display='none';" /></p>
<p style="padding:0px; margin:0px; text-align:left; float:left; width:190px;"><b>Advanced Search &amp; Filter</b></p>
<p class="leftFloat" style="clear:both"><b>Fields to search:</b> (Use CTRL to select multiple values)</p>
<br clear="all" />
<?php
if (!$c){
	global $db;
	$sql="SELECT value from filter_keys where filter_id=$f AND name = \"search_fields\"";
	$res=$db->query($sql);
	$h=$db->fetch_array($res);
	if ($db->num_rows($res)==0){ 
		$include_all_fields=1;
	}
	if ($h['value']){
		$fields_to_display_values=explode(",",$h['value']);
		$fields_to_display_list=$h['value'];
	}
} else {
	$fields_to_display_values=explode(",",$c);
}
print "<select name=\"dbf_dynamic_search_fields[]\" multiple style=\"height:150px\">";
foreach (list_fields_in_table($t) as $tablefield){
	print "<option value=\"$tablefield\"";
	if ($include_all_fields || in_array($tablefield,$fields_to_display_values)){ print " selected"; }	
	print ">$tablefield</option>";
}
print "</select>\n";
print "<input type=\"button\" value=\"Go\" style=\"color:green; font-weight:bold; background-color:#fff; width:32px; height:20px\" onClick=\"search_data()\" />\n";
print "</div>\n";
}

static function dbf_displayfields_popup_open($t,$f,$c){
// where t=table, f=filter and c=current list
$mootools_display_fields=1; // turn on and off mootools search
if (stristr($t,"QUERY:")){
	$on_query=1;
	$query_note ="<span style=\"color:orangered; margin-left:5px; background-color:#f1f1f1\">Please note: Editable fields for queries (not basic tables) is still in beta!</span>";
}
$t=str_replace("QUERY:","",$t);
?>
<div id="dbf_search_popup_inner"> 
<p style="float:left; font-size:13px; display:block; margin-top:0px; padding-top:2px;"><b>Fields to display:</b><?php echo $query_note?><br />
<?php if ($mootools_display_fields){
	print "Drag items between lists and re-order the display list to change the display order";
} else { 
	print "Use CTRL to select multiple values";
}?></p>
<p style="float:right; margin:0px; padding:0px; width:25px;"><input type="button" value="X" style="color:#1b2c67; font-weight:bold; font-size:12px; background-color:#fff; width:22px; height:20px;" onClick="document.getElementById('dbf_search_popup').style.display='none';" /></p>
<br clear="all" />
<?php
if (!$c){
	global $db;
	require_once(LIBPATH. "/classes/core/filters.php");
	$thisfilter=new filter($f);
	$disf=$thisfilter->value_for_key("display_fields");
	$fields_to_display_values=explode(",",$disf);
	$fields_to_display_list=$disf;
	
} else {
	$fields_to_display_values=explode(",",$c);
}
if (!$on_query){
	$tablefields=list_fields_in_table($t);
} else {
	$fields_to_display_values=query_functions::list_fields_in_query_by_queryname($t);
	$tablefields=array("");
}
sort($tablefields);
if (!$mootools_display_fields){
print "<select name=\"dbf_dynamic_fields_to_display[]\" multiple>";
foreach ($tablefields as $tablefield){
	print "<option value=\"$tablefield\"";
	if ($include_all_fields || in_array($tablefield,$fields_to_display_values)){ print " selected"; }	
	print ">$tablefield</option>";
}
print "</select>";
}
//mootools implementation
?>
<style tyle="text/css">

#table_fields_sortable LI {
    cursor: move;
    padding: 1px;
}

#table_fields_sortable UL {
    float: left;
    min-height: 1px;
    margin: 2px;
    width: 140px;
}
</style>

<div id="table_fields_sortable">
<div id="tfs_list1">
<p style="margin:0px; padding:0px; font-weight:bold; background-color:#ddd">Available Fields:</p>
    <ul>
	<?php
	foreach ($tablefields as $tablefield){
		if (!in_array($tablefield,$fields_to_display_values)){
			print "<li>$tablefield</li>\n";
		}
	}
	?>
    </ul>
</div>
<div id="tfs_list2">
<p style="margin:0px; padding:0px; font-weight:bold; background-color:#ddd">Fields To Display:</p>
        <ul id="fields_to_display_ul">
	<?php
	$i=0;
	foreach ($fields_to_display_values as $displaying_field){
		print "<li class=\"tfslist2item\" id=\"tfslist_".$i."\">$displaying_field</li>\n";
		$i++;
	}
	?>
    </ul>
</div>
</div>
<?php
if ($mootools_display_fields){
?>
<p style="float:right; margin:5px; vertical-align:bottom; padding:0px; width:25px;"><input type="button" value="GO" style="color:green; font-weight:bold; font-size:12px; background-color:#fff; width:32px; height:20px;" onClick="processDisplayFieldsList(); document.getElementById('dbf_search_popup').style.display='none';" /></p>
<?php
}
print "</div>\n";
}

static function shop_order($cat_id,$dem_id){

global $db;
?>
<p class="admin_header">Shop Ordering</p>
<form name="shop_ordering" id="shop_ordering" action="<?=basename($_SERVER['SCRIPT_NAME']);?>" method="get">
<input type="hidden" name="action" value="shop_sort_order" />
<p style="margin-left:20px; padding-left:20px;">Demography: 
<select name="dem_id" id="dem_id">
<option value="0">DEFAULT</option>
<?php
$sql="SELECT * FROM SHOP_Demographies";
$rv=$db->query($sql);
while ($h=$db->fetch_array()){
	print "<option value=\"".$h['id']."\"";
	if ($h['id']==$dem_id){
		print " selected";
	}
	print ">";
	print $h['demography_name']."</option>";
}
?>
</select>
 &nbsp; Category: 
<select name="cat_id" id="cat_id">
<option value="0">DEFAULT</option>
<?php
$sql="SELECT * FROM SHOP_Categories";
$rv=$db->query($sql);
while ($h=$db->fetch_array()){
	print "<option value=\"".$h['id']."\"";
	if ($h['id']==$cat_id){
		print " selected";
	}
	print ">";
	print $h['name']."</option>";
}
?>
</select>
<input type="hidden" name="shop_order" value="">
<input type="submit" value="go">
</p>
</form>
<?php

$cat_id=$db->db_escape($cat_id);
$dem_id=$db->db_escape($dem_id);

if (!$cat_id && !$dem_id){
	$cat_id=0; $dem_id=0;
}

$shops_used=array();
$shop_used_ids=array();
$sql="SELECT DISTINCT SHOP_Shop.id, SHOP_Shop.name, SHOP_Shop.gif, SHOP_Sort_Order.category_id FROM SHOP_Shop LEFT JOIN SHOP_Sort_Order ON SHOP_Shop.id = SHOP_Sort_Order.shop_id WHERE SHOP_Sort_Order.category_id = $cat_id AND SHOP_Sort_Order.demography_id=$dem_id ORDER BY sort_order ASC";
$rv=$db->query($sql);
while ($h=$db->fetch_array()){
	array_push($shops_used,$h);
	array_push($shop_used_ids,$h['id']);
}
$shops_used_string=join(",",$shop_used_ids);

$shops_unused=array();
$sql="SELECT SHOP_Shop.id, SHOP_Shop.name, SHOP_Shop.gif FROM SHOP_Shop WHERE ";
if ($shops_used_string){
	$sql .= "SHOP_Shop.id NOT IN ($shops_used_string) AND ";
}

if ($cat_id){ // do something a bit different in this instance
		  $sql="SELECT SHOP_Shop.id, SHOP_Shop.name, SHOP_Shop.gif FROM SHOP_Shop INNER JOIN SHOP_Shop_Category ON SHOP_Shop.id = SHOP_Shop_Category.shop_id WHERE SHOP_Shop_Category.category_id = $cat_id AND ";
		  if ($shops_used_string){
			  $sql .= "SHOP_Shop.id NOT IN ($shops_used_string) AND ";
		  }
}

$sql .= "SHOP_Shop.valid_from < NOW() ORDER BY name";
$rv=$db->query($sql);
while ($h=$db->fetch_array()){
	array_push($shops_unused,$h);
}
?>

<script type="text/javascript">
	function saveShops(){
		if (!document.forms['shop_ordering'].elements['dem_id'].value || !document.forms['shop_ordering'].elements['cat_id'].value){
			//alert("You must select a demography and a category in order to save this list of shops.");
			//return;
		}
		var shops =new Array();
		shops_ul=document.getElementById("shops_to_use_ul");
		shops_ul.getChildren('li').each(function(el){
			el.getChildren('span').each(function(spanel){
				elId=spanel.id;
				elId=elId.replace("shop_used_","");
				if (elId>0){
					shops.push(elId);
				}
			});
		});
		shopsValue=shops.join(",");
		document.forms['shop_ordering'].elements['shop_order'].value=shopsValue;

	// make the ajax request
        urlStr = "ajax/shop_order_save.php";
        var myRequest = new Request({
		method: 'post',
		data: $('shop_ordering').toQueryString(),
		url: urlStr,
                 onSuccess:function(data){
					  //$jquery('#msg_updated').fadeIn();
					  alert(data);
                },
                onFailure: function(){
                        alert("An error has occurred saving the data - please try again - thank you.");
                }
        });
        myRequest.setHeader('Content-type','text/plain');
        myRequest.send();
	}
</script>

<style type="text/css">
	#shop_sort_order ul {display:inline-block; padding:0px; width:100%;}
	#shop_sort_order ul li {display:inline; margin-right:10px; margin-bottom:1px;}
	.shop_span {margin-bottom:10px; border:1px blue solid; padding:2px; height:30px; display:inline-block; width:90px;}
</style>

<div id="shop_sort_order" style="width:100%; float:left">
	<div id="sort_list" style="border-bottom:1px gray dashed;">
		<ul id="shops_to_use_ul" style="min-height:34px; min-width:800px; border:1px gray dashed; padding-top:5px;">
		<?php foreach ($shops_used AS $shop_used){
			print "<li><span id=\"shop_used_".$shop_used['id']."\" class=\"shop_span\"><img src=\"".$shop_used['gif']."\" alt=\"".$shop_used['name']."\" width=\"88\" height=\"31\"></span></li>";
		}

		if (!$shops_used){
//			print "<li><span id=\"shop_used_0\" class=\"shop_span\"><img src=\"http://www.affiliatewindow.com/logos/1736/logo.gif\"></span><li>";
		}
		?>
		</ul>
<span style="display:none;" id="msg_updated">Database Updated</span>
<p style="text-align:center">Select a demographic, drag shops to the list above, then click below to <br /><a href="Javascript:saveShops()" style="font-weight:bold; padding:4px; border-radius:3px; border:1px blue solid; margin-top:10px; position:relative; top:10px;">Save Order</a></p>
<br />
	</div>

	<div id="sort_rest">
		<ul style="min-height:50px; background-color:#f1f1f1:">
		<?php foreach ($shops_unused AS $shop){
			print "<li><span id=\"shop_used_".$shop['id']."\" class=\"shop_span\"><img src=\"".$shop['gif']."\" alt=\"".$shop['name']."\" title=\"".$shop['name']."\" width=\"88\" height=\"31\"></span></li>";
		}
		?>
		</ul>
		  <?php
				  if (count($shops_unused)==0){
					  print "<p class=\"dbf_para_info\">All shops in this demography / category combination are already ordered.</p>";
				  }
		  ?>

	</div>

</div>

<script language="Javascript" type="text/javascript">
    new Sortables('#shop_sort_order UL', {
        clone: true,
        revert: true,
        opacity: 0.7
    });
</script>
<?php
}

static function form_from_hash($data,$options){
print "onf";
}

static function update_customer_account_totals($cur_user_id,$insert_id){
	$cur_user_id=$_REQUEST['user_id'];
	global $db;
	if ($cur_user_id && $insert_id){
		// need to adjust totals
		$sql="SELECT payment_amount from payments WHERE id = $insert_id";
		$rv=$db->query($sql);
		while ($h=$db->fetch_array($rv)){
			$amount=$h['payment_amount'];
		}
		if ($amount){
			$sql="SELECT credit_limit,account_balance,credit_available FROM user WHERE id = $cur_user_id";
			$rv=$db->query($sql);
			while ($h=$db->fetch_array($rv)){
				$account_balance=$h['account_balance']-$amount;
				$credit_available=$h['credit_available']+$amount;
				$up=$db->query("UPDATE user set account_balance = $account_balance, credit_available = $credit_available WHERE id = $cur_user_id");
				print "<script language=\"Javascript\" type=\"text/javascript\">\n";
				print "top.MUI.notification('New payment registered.');\n";
				print "parent.user_edit_recalculate_credit_amounts($cur_user_id)\n";
				print "</script>\n";
			}
		}
	}
}

//////////////////////////////////////////////////////////
// search results					//
// returns rows based on search results			//
// pass it the table or query to search, the string to  //
// search for, which fields to look through and which   //
// fields to return, and you will get them as an array  //
// the return structure is a hash containing this array //
// along with no. of returned rows as $data['results']  //
// and $data['num_results]'                             //
//////////////////////////////////////////////////////////
static function search_results($search_table_or_query,$search_for,$search_fields,$return_fields){
	global $db;
	$sql = "SELECT " . $return_fields . "FROM " . $search_table_or_query . " WHERE ";
	$search_conditions=array();
	foreach ($search_fields as $search_field){
		array_push($search_conditions,  $search_field . " LIKE \"%" . $search_for . "%");
	}
	$sql .= implode(" OR ",$search_conditions);

	$search_result=$db->query($sql);
	$return_data=array();
	$return_data['num_rows']=$db->num_rows($search_result);
	$return_data['results']=$db->num_rows($search_results);
	
	while ($search_results_returned=$db->fetch_array($search_result)){
	
	}
}

static function many_to_many_subform($master_table,$many_table,$through_table,$master_id,$key_field,$value_field,$name_field,$type){
	$subform ='<script type="text/javascript">
		function setManyToManyCheckbox(shopValue,catId,catValue){
		console.log(catValue);
		if (catValue){catValue=1;}else{catValue=0;}
		//console.log(catValue);
			data={ table: "'.$through_table.'",
				key_field: "'.$key_field.'",
				key_id: catId,
				value_field: "'.$value_field.'",
				master_id: '.$master_id.',
				value: "'.$master_id.'",
				type: "'.$type.'",
				item_value: catValue
				};
			console.log(data);
			AJAX_update_many_to_many_field(data);
		}
	</script> ';

	$subform .= "<table><tr>";
	$count=0;
	global $db;
	$sql="SELECT $many_table.id,$many_table.$name_field,$through_table.$key_field FROM $many_table LEFT JOIN $through_table ON $many_table.id = $through_table.$key_field AND $through_table.$value_field=$master_id";
	$rv=$db->query($sql);
	while ($h=$db->fetch_array()){
		$line = '<td><input type="checkbox" name="shop_category" onClick="setManyToManyCheckbox('.$master_id.','.$h["id"].',this.checked)"';
		if ($h[$key_field]){
			$line .= ' checked';
		}
		$line .= '> ' . $h[$name_field] . '</td>';
		$subform .= $line;
		$count++;
		if ($count==3){$subform .= "</tr><tr>"; $count=0;}
	}
	$subform .= "</tr></table>";
   //$subform .= "<span style=\"font-size:7px\">Key field: $key_field,  value field: $value_field";
	return $subform;
	
}

}

// Why is this here outside the class? It needs to go somewhere else thats why. It's called using call_user_func in the select_code_value_from_id function, which doesnt have self attached to it. This is entered into a filter key somewhere. It needs to be in a custom code snippet or something.
function products_as_select($row_id){
	
	global $db;
	if (!$row_id){
		$result_set=str_replace("CODE:","",$list_code);
		$sql="SELECT products.id,artists.artist,products.title from products INNER JOIN artists on products.artist=artists.id ORDER BY artists.artist,products.title";
		$result=$db->query($sql);
		$code_results=array();
		while ($code_res=$db->fetch_array($result)){
			array_push($code_results,$code_res['id'] . ";;". $code_res['artist'] . " - " . $code_res['title']);	
		}
		$return_set=join(",",$code_results);
		return $return_set;

	} else {

		$sql="SELECT products.id,artists.artist,products.title from products INNER JOIN artists on products.artist=artists.id WHERE products.id = $row_id ORDER BY artists.artist,products.title";
		$code_res=$db->query($sql);
		while ($cod_arr=$db->fetch_array($code_res)){
			$rv=$cod_arr['artist'] . " - " . $cod_arr['title'];
		}
		return $rv;	
	}
}
?>
