<?php

/* CLASS: recordset
 * A recordset is a list of records from either a table or a query.
 * Filters can be applied to this data both to transform it and it's display properties.
*/
class recordset {

	protected $recordset_source;
	protected $options;

        function __construct($recordset_source,$options){
		$this->recordset_source=$recordset_source;
		$this->original_recordset_source=$recordset_source;
		$this->options=$options;
		if ($this->options['relation_key']){
			$this->relation_key=$this->options['relation_key'];
		} else {
			$this->relation_key=$_REQUEST['relation_key'];
		}
		if ($this->options['relation_id']){
			$this->relation_id=$this->options['relation_id'];
		} else {
			$this->relation_id=$_REQUEST['relation_id'];
		}
		if (preg_match("/^QUERY:/",$recordset_source)){
			$this->on_query=1;
			$this->recordset_source=str_replace("QUERY:","",$this->recordset_source);
			global $db;
			$query = "SELECT query from queries where query_name = \"".$this->value("recordset_source")."\"";
			$queryresult=$db->query($query) or format_error("Error: 98889 - Cant run sql" . $db->db_error(),1);
			while ($qr=$db->fetch_array($queryresult)){
				if ($options['filter']['subreport_lookup_key']){
					$qr['query']=str_replace("{=subreport_lookup_key}",$options['filter']['subreport_lookup_key'],$qr['query']);
				}
				$qr['query']=Codeparser::parse_request_vars($qr['query']); // replaces $_GET values in query
				$qr['query']=Codeparser::parse_php_in_query($qr['query']); // extra php functionality
				$this->set_value("actual_query",$qr['query']);
			}
			$this->set_value("fields_to_display_from_query",query_functions::list_selected_fields_from_query($this->value("actual_query")));
		}
		if ($this->value("recordset_source")){ // there will not always be a source, the rs-to-template function does not require it for a start
			$this->db_field_types=$this->load_db_field_types($this->value("recordset_source"));
		}
		// the following list is used for filter variable recall and merge only
		$this->all_dbf_filter_keys=array("dbf_search_for","dbf_search_fields","dbf_sort_by_field","dbf_direction","dbf_sort_direction","dbf_sort_dir","dbf_rpp","dbf_dynamic_fields_to_display","dbf_dynamic_mootools_fields_to_display_list","dbf_dynamic_search_fields","dbf_dynamic_search_fields_list","dbf_data_filter_operator","dbf_data_filter_field","dbf_data_filter_value","dbf_cur_recordset_start","dbf_rpp_pre","dbf_az_filter_field","dbf_az_filter_value","new_dbf_search_date_start-1","new_dbf_search_date_start-2","new_dbf_search_date_start-3","new_dbf_search_date_end-1","new_dbf_search_date_end-2","new_dbf_search_date_end-3","dbf_date_filter_field","filter_field_between_dates","dbf_next","dbf_pre_search_for","active_date_filter","active_az_filter","field_equals","dbf_drop_down_filter-1","dbf_drop_down_filter-2","dbf_drop_down_filter-3","dbf_drop_down_filter-4","dbf_drop_down_filter-5");
        }

        function value($of){
                return $this->$of;
        }

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }

	function get_options(){ // use filter_key_value below instead
		return $this->options;
	}

	function filter_key_value($key){
		return $this->options['filter'][$key];
	}

	public function internal_filter_key_value($key){
		return $this->options['filter'][$key];
	}

public function store_dynamic_display_fields(){
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
	$this->set_value("dynamic_display_fields",$dyn_fields_to_display_list);

}

public function store_dynamic_search_fields(){
	if ($_REQUEST['dbf_dynamic_search_fields_list']){
		$dyn_search_fields_list=$_REQUEST['dbf_dynamic_search_fields'];
	}
        if ($_REQUEST['dbf_dynamic_search_fields']){
                if (count($_REQUEST['dbf_dynamic_search_fields']>=1)){
                        $dyn_search_fields_list_flat=join(",",$_REQUEST['dbf_dynamic_search_fields']);
                        if ($dyn_search_fields_list_flat){
                                $dyn_search_fields_list=$dyn_search_fields_list_flat;
                        }
                }
        }
	$this->set_value("dynamic_display_fields",$dyn_search_fields_list);
}

public function memorize_filter(){
	if (!stristr($_SERVER['PHP_SELF'],"administrator.php")){ return; }
	foreach ($_REQUEST as $request_var => $request_val){
		if (preg_match("/dbf_/",$request_var)){
			$_SESSION['recordset_filters'][$this->value("recordset_source")][$request_var]=$request_val;
			if ($request_var=="dbf_sort_direction"){
//					print "<p>set sort dir at 1 to " . $request_val . "</p>";
			}
		}
	}

	foreach ($this->all_dbf_filter_keys as $dbf_filter_key){
		if ($_REQUEST[$dbf_filter_key]){
			$_SESSION['recordset_filters'][$this->value("recordset_source")][$dbf_filter_key]=$_REQUEST[$dbf_filter_key];
			if ($dbf_filter_key=="dbf_sort_direction"){
//					print "<p>set sort dir at 2 to " . $_REQUEST['dbf_sort_direction'] . "</p>";
			}
		}
	}

	if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
		//print "From memorize (dumping session)<br />";
		//var_dump($_SESSION['recordset_filters'][$this->value("recordset_source")]);
		//print "End memorize<br>";
	}
}

public function recall_filter(){
	if ($_REQUEST['clear_filter_memory']==1){
		// if a filter key of clear_filter_memory exists, abort
		$_SESSION['recordset_filters'][$this->value("recordset_source")]=array();
		$this->filter_values[$this->value("recordset_source")]=array();
	} else if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
		if ($_SESSION['recordset_filters'][$this->value("recordset_source")]){
			foreach ($_SESSION['recordset_filters'][$this->value("recordset_source")] as $session_request_var => $session_request_val){
				$this->filter_values[$session_request_var] = $session_request_val;
			}
			// normalise dates
		}
		//print "<p>From Recall:</p>";
		//var_dump($this->filter_values);
	} else {
		$this->filter_values[$this->value("recordset_source")]=array();
	}
}

public function merge_recall_with_request_vars(){

	foreach ($this->all_dbf_filter_keys as $dbf_filter_key){
		if ($_REQUEST[$dbf_filter_key] || strlen($_REQUEST[$dbf_filter_key])>=1){
			$this->filter_values[$dbf_filter_key]=$_REQUEST[$dbf_filter_key];
		}
	}

	if ($this->filter_values['active_az_filter']=="CLEAR_ME"){
		$this->filter_values['active_az_filter']="";
	}
	if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
		if ($this->filter_values['dbf_next']){
			if ($this->options['filter']['limit'] != "All"){
				$this->options['filter']['limit_from']=$this->filter_values['dbf_next']-1;
			}
			if ($this->filter_values['dbf_direction']=="Down"){
				$this->options['filter']['limit_from']=$this->options['filter']['limit_from']-(($this->options['filter']['limit']*2));	
			} else if ($this->filter_values['dbf_direction']=="Reset"){
				$this->options['filter']['limit_from']=0;
			} else if ($this->filter_values['dbf_direction']=="Static" && $this->options['filter']['limit_from']>1){
				$this->options['filter']['limit_from']=$this->filter_values['dbf_next']-($this->filter_values['dbf_rpp_pre']+1);	
			}
			if ($this->options['filter']['limit_from']<0){ $this->options['filter']['limit_from']=0;}
		}
		//print "<p>From Merge:</p>";
		//var_dump($this->filter_values);
	$this->map_recall_and_request_onto_initial_filter();
	}
	
}

private function map_recall_and_request_onto_initial_filter(){

	foreach ($this->filter_values as $fvkey=>$fvvalue){
		$this->options['filter'][$fvkey]=$fvvalue;
	}
	if ($this->options['filter']['order_by'] && !$this->filter_values['dbf_sort_by_field']){
		$this->filter_values['dbf_sort_by_field']=preg_replace("/ Desc/i","",$this->options['filter']['order_by']);
	}
	if (stristr($this->options['filter']['order_by'],"DESC") && !$this->filter_values['dbf_sort_direction']){
		$this->filter_values['dbf_sort_direction']="Desc";
	}
}

public function filter_form_header(){
	$set_dyn_field_display = $this->store_dynamic_display_fields(); 
	$set_dyn_search_fields = $this->store_dynamic_search_fields(); 
	if (preg_match("/site.php/",$_SERVER['PHP_SELF'])){$append_to_form_post_url="&s=".$_REQUEST['s']."&filter_id=".$this->options['filter']['dbf_filter_id'];}
	if ($this->options['filter']['add_string_to_form_post_query']){
		$append_to_form_post_url .= "&" . $this->options['filter']['add_string_to_form_post_query'];
	}	
	if (!$this->value("on_query")){
		$this->form_post_url=$_SERVER['PHP_SELF']."?action=list_table&t=$".$this->value("recordset_source").$append_to_form_post_url;
	} else {
		$this->form_post_url=$_SERVER['PHP_SELF']."?action=list_query_v2&filter_id=".$this->options['filter']['dbf_filter_id']."&q=".$this->value("original_recordset_source").$append_to_form_post_url;
	}
	if ($options['filter']['form_action']){
		$this->form_post_url=$this->options['filter']['form_action'];
	}
	$this->filter_form_header = "<!-- filter form header //-->\n<form name=\"list_records_filter\" id=\"list_records_filter\" action=\"$form_post_url\" method=\"post\">\n";

	$this->set_value("form_post_url",$_SERVER['PHP_SELF']."?action=list_table&t=".$this->value("original_recordset_source") . $append_to_form_post_url);
	if ($this->options['filter']['form_action']){
		$this->set_value("form_post_url",$this->options['filter']['form_action']);
	}
	$this->filter_form_header = "<form name=\"list_records_filter\" id=\"list_records_filter\" action=\"".$this->value("form_post_url")."\" method=\"post\">\n";
	$this->filter_form_header .= "<input type=\"hidden\" name=\"dbf_form_post_url\" id=\"dbf_form_post_url\" value=\"".$this->value("form_post_url")."\">\n";	
	$this->filter_form_header .= "<input type=\"hidden\" name=\"dbf_output_type\" value=\"\">\n"; // Questionable - this line was previously commented out and uncommented in cspnew to no adverse effects. It has thus been uncommented here as cspnew required this.

	// any hidden keys to pass?
	if ($this->options['filter']['pass_request_vars_as_post']){
		$all_req_vars=explode(",",$this->options['filter']['pass_request_vars_as_post']);
		foreach ($all_req_vars as $each_req_var){
			$this->filter_form_header .= "<input type=\"hidden\" name=\"$each_req_var\" value=\"".$_REQUEST[$each_req_var]."\">\n";	
		}
	}

	$this->filter_form_header .= "\n<!-- END FILTER FORM HEADER //-->\n\n";
	return $this->filter_form_header;
}

public function delete_record_form($sitevar){
	if ($sitevar){$delete_form_additions = "&s=$sitevar";}
	$sys_form_id=$this->dbf_system_log_delete_form_generation($delete_form_additions);
        // print form to delete records
	if ($_REQUEST['s']){$current_site=$_REQUEST['s']; $delete_form_additions = "&s=$current_site";}
	$this->delete_form = "<!-- delete from recordset object //-->\n";
        $this->delete_form .= "<form name=\"deleterow\" id=\"deleterow\" action=\"" . $_SERVER['PHP_SELF'] . "?action=delete_row_from_table$delete_form_additions\" method=\"post\"><input type=\"hidden\" name=\"carrier_field\"><input type=\"hidden\" name=\"tablename\" value=\"" . $this->value("recordset_source") . "\">";
        $this->delete_form .= "<input type=\"hidden\" name=\"deleteID\" value=\"\">\n";
        $this->delete_form .= "<input type=\"hidden\" name=\"deleteChildren\" value=\"\">\n";
        $this->delete_form .= "<input type=\"hidden\" name=\"filter_id\" value=\"".$this->options['filter']['dbf_filter_id']."\">\n";
        $this->delete_form .= "<input type=\"hidden\" name=\"relation_key\" value=\"".$this->relation_key."\">\n";
        $this->delete_form .= "<input type=\"hidden\" name=\"relation_id\" value=\"".$this->relation_id."\">\n";
        $this->delete_form .= "<input type=\"hidden\" name=\"dbf_sys_form_id\" value=\"".$sys_form_id."\">\n";
        $this->delete_form .= "</form>\n";
        return $this->delete_form;
}

public function dbf_system_log_delete_form_generation(){
	global $db;
	global $user;
	$unique_form_id=uniqid();
	$sql="INSERT INTO sys_form_log (ip_address,uuid,gen_time,update_table,row_identifier,form_type,filter,user,user_session) values (";
	$sql .= "\"".$_SERVER['REMOTE_ADDR']."\",";
	$sql .= "\"".$unique_form_id."\",";
	$sql .= "NOW(),";
	$sql .= "\"".$this->recordset_source."\",";
	$sql .= "\"\",";
	$sql .= "\"delete\",";
	$sql .= "\"".$this->options['filter']['dbf_filter_id']."\",";
	$sql .= "\"".$user->value('id')."\",";
	$sql .= "\"".session_id()."\"";
	$sql .= ")";
	$res=$db->query($sql) or format_error("Unable to log system form details",1);
	return $unique_form_id;
}

public function print_recordset_header(){
	// print table header
	$formatted_tablename=format_table_name($this->value("recordset_source"));
	if ($this->relation_id){
		$master_table_for_navigation=database_functions::get_master_table_from_relationship($this->value("recordset_source"),$this->relation_id);
		$master_table_for_navigation_print=format_table_name($master_table_for_navigation);
		$formatted_tablename="<a href=\"".$_SERVER['PHP_SELF']."?s=1&action=list_table&t=$master_table_for_navigation&dbf_add=1&dbf_edi=1&dbf_ido=1&dbf_search=1&dbf_sort=1&dbf_sort_dir=1&dbf_orderby=1&dbf_rpp_sel=1&dbf_rpp=All\" style=\"text-decoration:underline\">$master_table_for_navigation_print</a> : " . $formatted_tablename;
	}

	if ($this->options['filter']['title_text'] || $this->options['filter']['title_text_list_records']){ 
		if ($this->options['filter']['title_text_list_records']){
			$this->options['filter']['title_text'] = $this->options['filter']['title_text_list_records'];
		}
		$EXPORT['title_text'] = $this->options['filter']['title_text']; 
		$print_title=$this->options['filter']['title_text']; 
	} else { 
		$print_title = "You are in: " . $formatted_tablename;
	}

        if ($this->options['filter']['sub_title_text']){
                $print_sub_header .= "<div id=\"sub_title_text\">".$this->options['filter']['sub_title_text']."</div>";
                $EXPORT['sub_title_text']= "<div id=\"sub_title_text\">".$this->options['filter']['sub_title_text']."</div>";
        }
	//if (!$options['filter']['append_section_name_to_breadcrumb']){

		if ($this->relation_id && $this->relation_key){
			$print_sub_header_filtered = "<p style=\"color:#666666; font-weight:normal; font-size:10px\">Showing records where ";
			$child_table_key_field=database_functions::get_child_key_field_from_child_table($this->value("recordset_source"),$this->relation_id);
			$master_table_key_field=database_functions::get_master_key_field_from_child_table($this->value("recordset_source"),$this->relation_id);
			$print_sub_header_filtered .= ucfirst(preg_replace("/_id$/","",$child_table_key_field));
			$print_sub_header_filtered = str_replace("_"," ",$print_sub_header_filtered);
			$master_table_for_print_filter=database_functions::get_master_table_from_relationship($this->value("recordset_source"),$this->relation_id);
			$master_table_name_field=database_functions::get_master_name_field_from_master_table($this->value("recordset_source"),$this->relation_id);
			$master_table_name=database_functions::get_master_table_name($this->value("recordset_source"),$this->relation_id);
			
			$mtsql="SELECT $master_table_key_field FROM $master_table_for_print_filter WHERE $master_table_key_field = ".$this->relation_key;
			global $db;
			$mtres=$db->query($mtsql) or die($db->errmsg());
			$mth=$db->fetch_array($mtres);
			$mth_result=$mth[$master_table_key_field];
			if ($this->options['filter'][$child_table_key_field]['select_value_list']){
				$mth_result = database_functions::sql_value_from_id($this->options['filter'][$child_table_key_field]['select_value_list'],$mth_result);
			} else if ($DELETED_OPTION_HERE_AS_NOW_IN_FILTERS_php__master_table_name_field){ // still required as it's a name lookup?
				// if we have a name field, we can make a title
				$this_filter_sql= "SQL:SELECT id,$master_table_name_field FROM $master_table_name";
				if (!$_REQUEST['always_raw_data'] && !$this->options['filter'][$child_table_key_field]['select_value_list']){
					// and if not raw can include it in the filter to populate the column
					$this->options['filter'][$child_table_key_field]['select_value_list']=$this_filter_sql;
				}
				$mth_result = database_functions::sql_value_from_id($this->options['filter'][$child_table_key_field]['select_value_list'],$mth_result);
			} else {
				 print "<p class=\"dbf_para_info\"><b>System information:</b> there is no select value list on the field '$child_table_key_field'.<br />This means that you are in a child table but have not yet configured the lookup on the child field (foreign key) that links it to a field in the master table which is why you are seeing the id and not the name in this column.<br /> You can do this by going into Select Lists and entering this table name, the field '$child_table_key_field' field and a value of 'SQL:SELECT id,[parent field name] from [parent table]. Just replace the values in the square brackets with the actual values (and remove the brackets also!)</p>";
			} 
			$print_sub_header_filtered .= " = $mth_result</p>";
			if (strlen($mth_result)>40){$mth_result=rtrim(substr($mth_result,0,40)) . "...";}
			$this->options['filter']['section_name']=$mth_result;
		}

		$extra_options_sql="SELECT table_option,option_value from table_options where table_name = \"".$this->value("recordset_source")."\"";
		global $db;
		$extra_options_result=$db->query($extra_options_sql);
		while ($tab_options = $db->fetch_array($extra_options_result)){

			if ($tab_options['table_option'] == "update_type" && $tab_options['option_value']=="immediate"){ $extra_info = "This file will be written out to the local file each time it is saved.";}

			if ($tab_options['table_option']=="update_type" && $tab_options['option_value']=="update_database_only"){ $extra_info = "This file will not be written out to the local file when it is saved. You will need to run the 'write table contents to directory' operation to write out all files.";}

			if ($tab_options['table_option']=="corresponds_to_directory"){
				$corresponding_directory_text = "This dataset corresponds to files in the following directory: " . $tab_options['option_value'] . ". "; 
			}
		}
		if ($corresponds_to_directory){
			if ($extra_info){$extra_info = "<br />".$extra_info;}
			$print_sub_header_filtered .= "<p class=\"dbf_para_info\">".$corresponding_directory_text . $extra_info."</p>"; 
		}
	//}
	
	if (master_content_header($this->value("recordset_source"),"list") && !$this->options['filter']['hide_system_header']){$output_text .= display_master_content_header($this->value("recordset_source"),"list");}
	$output_text .= "<div id=\"table_header_div\">";
	$output_text .= "<div class=\"table_title\" id=\"table_title_div\" style=\"padding-bottom:0px; margin-bottom:0px;\"> " . $print_title; 
	if ($this->options['filter']['section_name']){
		$output_text .= "<span style=\"font-weight:normal;\">: " . $this->options['filter']['section_name'] . "</span>";
	}
	$output_text .= "</div>\n\n<!-- END PRINT RECORDSET HEADER //-->\n\n";
	$r_hash['EXPORT']=$EXPORT;
	$r_hash['output_text'] = $output_text;
	return $r_hash;
}

public function print_header_interface_buttons(){

	$output_text .= "<div id=\"interface_buttons\">";

	// Header Button Options
	if ($this->options['include_add_link'] || $this->options['filter']['include_add_link']){
		$add_row_url= ($this->options['filter']['add_row_link'])? $this->options['filter']['add_row_link'] : $_SERVER['PHP_SELF'] . "?action=edit_table&edit_type=add_row&t=".$this->value("recordset_source");
		if ($this->relation_id && $this->relation_key){ $add_row_url .= "&relation_key=".$this->relation_key."&relation_id=".$this->relation_id;}
		$subWindowTitle=str_replace("_"," ",$this->value("recordset_source")) . " - add";
		$output_text .= "<a href=\"" . get_link($add_row_url,$subWindowTitle) . "\"><img src=\"". SYSIMGPATH . "/application_images/button_add_beige_29x28.png\" alt=\"Add New\" title=\"Add New\" border=0></a>";
	}
	if ($this->options['include_edit_all_link'] || $this->options['filter']['include_edit_all_link']){

		// filter for edit_recordset - old pre-api versions commented out
		if ($_REQUEST['filter_id']){
			//$filter_text="&filter_id=".$_REQUEST['filter_id'];
			$filter_text = "filter/".$_REQUEST['filter_id'] . "/";
		} else if (database_functions::filter_registered_on_table($this->value("recordset_source"),"list_table")){ // invoke the list table filter as a default
			//$filter_text .= "&filter_id=".database_functions::filter_registered_on_table($this->value("recordset_source"),"list_table");
			$filter_text .= "filter/".database_functions::filter_registered_on_table($this->value("recordset_source"),"list_table") . "/";
		} else {
			$filter_text .= "&filter_id=";
		}

		// any relation key for the edit recordset link?
		if ($this->relation_id && $this->relation_key){
			 $relation_key_text = "relation_key/".$this->relation_key."/relation_id/".$this->relation_id."/";
		}

		$edit_all_js_url = HTTP_PATH . "/crud/table/" . $this->value("recordset_source") . "/action/edit_recordset/add_data/1/" . $filter_text . $relation_key_text; 
		if (stristr($_SERVER['PHP_SELF'],"ui-admin")){ $edit_function="edit_current_recordset_mui"; } else { $edit_function = "edit_current_recordset";}
		$edit_function="edit_current_recordset";
		$output_text .= "<a href=\"Javascript:$edit_function('$edit_all_js_url')\"><img src=\"". SYSIMGPATH . "/application_images/button_edit_beige_29x28.png\" alt=\"Edit This Recordset\" title=\"Edit This Recordset\" border=0></a>";
		$output_text .= "<input type=\"hidden\" name=\"filter_query\" value=\"\">";
	}
	if ($this->options['include_upload_data_link'] || $this->options['filter']['include_upload_data_link']){
		$output_text .= "<a href=\"Javascript:parent.loadPage('" . $_SERVER['PHP_SELF'] . "?action=import_wizard&table=".$this->value("recordset_source") . "','Import Data into ".$this->value("recordset_source")."',0,870,450)\"><img src=\"" . SYSIMGPATH . "/application_images/button_upload_beige_29x28.png\" border=0 name=\"Import Data\" title=\"Import Data\" alt=\"Import Data\"></a>";
	}

	if ($this->options['include_excel_download_link'] || $this->options['filter']['include_excel_download_link'] || 1==1){
		$output_text .= "<a href=\"Javascript:items_as_excel()\"><img src=\"".SYSIMGPATH."/application_images/button_excel_beige_29x28.png\" border=\"0\" alt=\"Export as Excel\" title=\"Export Excel\"></a>";
	}
	global $user;
	if ($user->value("type")=="master" || $user->value("type")=="superadmin"){
		$output_text .= "<a href=\"Javascript:show_sql()\"><img src=\"".SYSIMGPATH."/application_images/button_showsql_beige_29x28.png\" border=\"0\" alt=\"Show SQL for recordset\"></a>";
		$output_text .= "<a href=\"Javascript:void window.open('administrator.php?action=recordset_metadata&t=".$this->value("recordset_source")."&f=".$this->options['filter']['dbf_filter_id']."&jx=1','metadata','width=600,height=400')\"><img src=\"".SYSIMGPATH."/application_images/button_meta_beige_29x28.png\" border=\"0\" alt=\"Show metadata for recordset\"></a>";
	}

	// clear memory button if a filter is remembered
	if (count($_SESSION['recordset_filters'][$this->value("recordset_source")])>1){
		$output_text .= "<a href=\"" . $_SERVER['PHP_SELF']. "?action=list_table&t=".$this->value("recordset_source")."&clear_filter_memory=1\" style=\"font-size:10px; vertical-align:middle;\"><img src=\"".SYSIMGPATH."/application_images/button_clear_filter_memory_beige_29x28.png\" border=\"0\" title=\"Clear Filter\" alt=\"Clear Filter\"></a>";
	}

	// see if there are any extra table options
	global $db;
	$extra_options_sql="SELECT * from table_options where table_name = \"".$this->value("recordset_source")."\"";
	$extra_options_result=$db->query($extra_options_sql);
	while ($tab_options = $db->fetch_array($extra_options_result)){
		if ($tab_options['table_option']=="corresponds_to_directory"){
			$output_text .= " <a href=\"".$_SERVER['PHP_SELF']."?action=load_table_from_dir&table=".$this->value("recordset_source")."\"><img src=\"".SYSIMGPATH."/application_images/button_tablerefresh_beige_29x28.png\" title=\"Load table from directory\" alt=\"Load Table From Directory\" border=0></a>";
			$output_text .= " <a href=\"".$_SERVER['PHP_SELF']."?action=dump_table_to_dir&table=".$this->value("recordset_source")."\"><img src=\"".SYSIMGPATH."/application_images/button_tableout_beige_29x28.png\" title=\"Write table contents to directory\" alt=\"Dump Table To Directory\" border=0></a> ";
		}
	}

	$output_text .= " </div>";

	if ($print_sub_header){
		$output_text.= "<div class=\"sub_header\">$print_sub_header</div>";
	}
	$output_text .= "<div id=\"sub_header_filtered\">$print_sub_header_filtered</div>";

	$output_text.="<!--<hr size='1' color='#1b2c67' width=\"100%\" align=\"left\">//-->";
	$output_text .= "\n\n<!-- END PRINT INTERFACE BUTTONS //-->\n\n";
	return $output_text;
}

public function print_recordset_filtering(){

	//
	//
	// THIS IS WHERE WE STAT PRINTING THE PAGE FILTERS HEADER DIV
	//
	//

	// Load page filters (filter,search,records per page etc
	//$output_text .= " &nbsp; <a id=\"toggle_filters_button\" class=\"list_table_toggler\" href=\"#\"><img src=\"".SYSIMGPATH."/icons/page_white_magnify.png\" alt=\"Search And Filter\" title=\"Search And Filter\" border=0></a><hr size='1' color='#1b2c67' width=\"100%\" align=\"left\">";
	// now add all the query and form variables to hidden fields unless we have a filter for them
	$REQUEST_VARS=$_REQUEST;
	$REQUEST_VARS['dbf_search_for']=$this->filter_values['dbf_search_for'];
	if ($REQUEST_VARS['dbf_search_for'] || strlen($REQUEST_VARS['dbf_search_for'])){
		if ($REQUEST_VARS['dbf_pre_search_for'] != $REQUEST_VARS['dbf_search_for']){
			$this->options['filter']['limit_from']=0;	
		}
		$REQUEST_VARS['dbf_pre_search_for']=$REQUEST_VARS['dbf_search_for'];
	}

	foreach ($REQUEST_VARS as $request_var => $request_val){
		if (preg_match("/dbf_/",$request_var) && $request_var != "dbf_next" && $request_var != "dbf_current_recordset_start" && $request_var != "dbf_search_for" && $request_var != "dbf_search_fields" && $request_var != "dbf_rpp_pre" && $request_var != "dbf_output_type" && $request_var != "dbf_az_filter_field" && $request_var != "dbf_az_filter_value" && $request_var != "dbf_dynamic_mootools_fields_to_display_list" && $request_var != "dbf_data_filter_value"){
			//print "outputting request var as hidden $request_var<br>";
			$output_text .= "<input type=\"hidden\" name=\"$request_var\" value=\"" . $request_val . "\">\n";	
			$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"$request_var\" value=\"" . $request_val . "\">\n";	
		}
	}
	if (!$_REQUEST['dbf_direction']){
		$output_text .= "<input type=\"hidden\" name=\"dbf_direction\" value=\"Up\">\n";
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_direction\" value=\"Up\">\n";
	}

	// relation keys
	if ($this->relation_key){
	$output_text .= "<input type=\"hidden\" name=\"relation_key\" value=\"".$this->relation_key."\" />";
	}
	if ($this->relation_id){
	$output_text .= "<input type=\"hidden\" name=\"relation_id\" value=\"".$this->relation_id."\" />";
	}

	// include the filter id
	$output_text .= "<input type=\"hidden\" name=\"filter_id\" value=\"".$this->options['filter']['dbf_filter_id']."\">\n";
	$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"filter_id\" value=\"".$this->options['filter']['dbf_filter_id']."\">\n";

	// Display records per page select if necessary
	$cur_recordset_start=$this->filter_values['dbf_cur_recordset_start'];
	//if ($_SESSION['recordset_filters'][$this->value("recordset_source")]['dbf_cur_recordset_start']){
	//	$cur_recordset_start=$_SESSION['recordset_filters'][$this->value("recordset_source")]['dbf_cur_recordset_start'];
	//}
	if (!$cur_recordset_start){$cur_recordset_start=1;}
	if ($this->options['filter']['limit']){$cur_recordset_start += $this->options['filter']['limit'];}

	if ($this->value("on_query")){
		$this->options['dbf_search']=1;
		$this->options['dbf_sort']=1;
		$this->options['dbf_sort_dir']=1;
		$this->options['dbf_filter']=1;
		if ($this->options['filter']['dbf_rpp']){$this->options['dbf_rpp']=$this->options['filter']['dbf_rpp'];} else { $this->options['dbf_rpp']=$CONFIG['default_records_per_page'];}
		$this->options['dbf_rpp_sel']=1;
	}

	if ($this->options['dbf_sort'] || $this->options['dbf_filter'] || $this->options['dbf_rpp_sel'] || $this->options['dbf_search'] || $this->options['filter']['dbf_search'] || $this->options['filter']['dbf_filter'] || $this->options['filter']['dbf_sort']){

		$filter_display_css="none";
		if (trim($this->filter_values['dbf_search_for']) || trim($this->filter_values['dbf_data_filter_value'])){
			$filter_display_css="block";
		}

		if ($filter_display_css=="none"){
		$output_text .= "<p style=\"margin-left:15px\" id=\"filters_toggle_para\">
				<a id=\"v_toggle\" onMouseOver=\"document.getElementById('filters_toggle_para').style.display='none'; jQuery('#page_filters').fadeIn();\">Search / Filter</a>
				</p>\n";
		}
		$output_text .= "<div id=\"page_filters\" style=\"display:".$filter_display_css."\"><div id=\"filters_div\"><p>\n";
		$output_text .= '<div id="filters_left"></div><div id="filters_main">';
	}
	// Display search box if necessary 
	if ($this->options['dbf_search'] || $this->options['filter']['dbf_search']){
	if (!$this->value("on_query")){
		$search_fields_list=database_functions::populate_search_fields($this->value("recordset_source"),$this->options['filter'],$this->filter_values['dbf_search_fields']);
	} else { 
		if ($this->options['filter']['search_fields']){
			$search_fields_list = database_functions::populate_search_fields($this->actual_query,$this->options['filter'],$this->options['filter']['search_fields']);

		} else {
			$search_fields_list = query_functions::queryfields_as_select_options($this->actual_query,$this->options['filter'],$_REQUEST['dbf_search_fields']);
		}
	}
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
		// note for mootools one we check filter_values AND THEN request. Need to check filter_values for the TWO above as well.
		if ($this->filter_values['dbf_dynamic_mootools_fields_to_display_list']){
			$dyn_fields_to_display_list=$this->filter_values['dbf_dynamic_mootools_fields_to_display_list'];
		}
		if ($_REQUEST['dbf_dynamic_mootools_fields_to_display_list']){
			$dyn_fields_to_display_list=$_REQUEST['dbf_dynamic_mootools_fields_to_display_list'];
			//print "Have a list of " . $dyn_fields_to_display_list;
		}
		if ($_REQUEST['dbf_dynamic_search_fields_list']){
			$dyn_search_fields_list=$_REQUEST['dbf_dynamic_search_fields_list'];
		}
		if ($_REQUEST['dbf_dynamic_search_fields']){
			if (count($_REQUEST['dbf_dynamic_search_fields']>=1)){
				$dyn_search_fields_list_flat=join(",",$_REQUEST['dbf_dynamic_search_fields']);
				if ($dyn_search_fields_list_flat){
					$dyn_search_fields_list=$dyn_search_fields_list_flat;
				}
			}
		}
			//print "dynlist is $dyn_search_fields_list list is $search_fields_list";
		if($dyn_search_fields_list){
			$this->options['filter']['search_fields']=$dyn_search_fields_list;
			$search_fields_list=database_functions::populate_search_fields($this->value("recordset_source"),$this->options['filter'],$dyn_search_fields_list);
		}

		$output_text .= '<div id="search">
<div class="filter_image_div"><img src="'.SYSIMGPATH.'/application_images/search_filter_icon.png" width="37" height="37" alt=""></div>
<div class="collection">
<div class="filter_item_text">Find:</div><div class="filter_item_text">In:</div>
</div>
<div class="collection">
<div class="item_value_top"><input type="text" name="dbf_search_for" id="dbf_search_for" onMouseOver="this.focus()"value="'.$this->filter_values['dbf_search_for'] . '">';
if (trim($this->filter_values['dbf_search_for'])){
	$output_text .= '<a style="margin-left:5px" href="Javascript:document.forms[\'list_records_filter\'].elements[\'dbf_search_for\'].value=\'\'; document.getElementById(\'clear_search_x\').style.display=\'none\'; document.forms[\'list_records_filter\'].elements[\'dbf_direction\'].value=\'Reset\'; document.forms[\'list_records_filter\'].submit();" id="clear_search_x">X</a>';
}

$output_text .= '</div><div class="item_value_bottom"><select name="dbf_search_fields" id="dbf_search_fields" onMouseOver="return; this.focus(); event = document.createEvent(\'MouseEvents\'); event.initMouseEvent(\'mousedown\', true, true, window); dispatchEvent(event);">'.$search_fields_list.'</select><br /><a href="Javascript:dbf_search_popup_open(\''.$this->value('recordset_source').'\',\''. $this->options['filter']['filter_id'] . '\',\'' . $dyn_search_fields_list. '\')">-&gt;select fields</a></div>
</div>
</div>
<input type="hidden" name="dbf_dynamic_mootools_fields_to_display_list" id="dbf_dynamic_mootools_fields_to_display_list" value="'.$dyn_fields_to_display_list.'">
<input type="hidden" name="dbf_dynamic_fields_to_display_list" id="dbf_dynamic_fields_to_display_list" value="'.$dyn_fields_to_display_list.'">
<input type="hidden" name="dbf_dynamic_search_fields_list" id="dbf_dynamic_search_fields_list" value="'.$dyn_search_fields_list.'">
<div id="dbf_search_popup" class="search_filter_popup"></div><!-- closes search //-->';
$output_text .= "\n";

		$EXPORT['search_input_field'] .= "<input type=\"text\" name=\"dbf_search_for\" size=\"10\" class=\"page_filter_text_input\" value=\"" . $this->filter_values['dbf_search_for'] . "\" onfocus=\"this.value=''\" onMouseOver=\"this.focus()\">\n";
		if ($this->options['filter']['dbf_search_fields_sel']){
			$EXPORT['search_input_field'] .= "<select name=\"dbf_search_fields\" class=\"page_filter_select\">".database_functions::populate_search_fields($this->value("recordset_source"),$this->options['filter'],$_REQUEST['dbf_search_fields'])."</select>";
		} else {
			$EXPORT['search_input_field'] .= "<input type=\"hidden\" name=\"dbf_search_fields\" value=\"".$this->options['filter']['dbf_search_fields']."\" onfocus=\"this.value='';\">";
		}

		$EXPORT['search_button'] = "<input type=\"button\" name=\"\" onClick=\"search_data()\" value=\"Search\" class=\"search_button\">\n";
		
		$output_text .= "<div class=\"search_filter_divider\"><div class=\"search_filter_divider_inner\"></div></div>";
	}

	// Display filter if necessary
	if ($this->options['dbf_filter'] || $this->options['filter']['dbf_filter']){
	if (!$this->value("on_query")){
		$filter_fields_list=database_functions::populate_filter_fields($this->value("recordset_source"),$this->options['filter'],$this->filter_values['dbf_data_filter_field']);
	} else {
		if ($this->options['filter']['filter_fields']){
			$filter_fields_list=database_functions::populate_filter_fields($this->value("recordset_source"),$this->options['filter'],$this->filter_values['dbf_data_filter_field']);
		} else {
			$selected_filter_field=str_replace("","",$this->filter_values['dbf_data_filter_field']);
			$filter_fields_list = query_functions::queryfields_as_select_options($this->actual_query,$this->options['filter'],$selected_filter_field);
		}
	}
	$current_operator=$this->filter_values['dbf_data_filter_operator'];	
	if ($current_operator == ">"){$current_operator = "&gt;";}
	if ($current_operator == "<"){$current_operator = "&lt;";}
	$output_text .= '<div id="filter">
<div class="filter_image_div"><img src="'.SYSIMGPATH.'/application_images/filter_filter_icon.png" width="37" height="37" alt=""></div>
<div class="collection">
<div class="filter_item_text">Filter By:</div><div class="filter_item_text"><select name="dbf_data_filter_operator" class="small" style="width:35px" onMouseOver="return; this.focus(); event = document.createEvent(\'MouseEvents\'); event.initMouseEvent(\'mousedown\', true, true, window); dispatchEvent(event); ">'. database_functions::csv_to_options("=,&lt;,&gt;,IN,!=,BETWEEN",$current_operator) . '</select></div>
</div>
<div class="collection">
<div class="item_value_top"><select name="dbf_data_filter_field">' . $filter_fields_list . '</select></div><div class="item_value_bottom"><input type="text" name="dbf_data_filter_value" id="dbf_data_filter_value" value="'. $this->filter_values['dbf_data_filter_value'] .'" onMouseOver="this.focus()">';

if (trim($this->filter_values['dbf_data_filter_value'])){
	$output_text .= '<a style="margin-left:5px" href="Javascript:document.forms[\'list_records_filter\'].elements[\'dbf_data_filter_value\'].value=\' \'; document.getElementById(\'clear_search_x2\').style.display=\'none\'; document.forms[\'list_records_filter\'].elements[\'dbf_direction\'].value=\'Reset\'; document.forms[\'list_records_filter\'].submit();" id="clear_search_x2">X</a>';
}

$output_text .= '<br />';
if ($this->options['filter']['filter_field_between_dates']){
$output_text .= '<a href="Javascript:popUpDate()">Filter Between Dates</a>';
}
$output_text .= '</div>
			
</div>
</div><!-- closes filter //-->';

		//$output_text .= "<div id=\"Filter_select\" class=\"page_filter_div\">Filter By:<select name=\"dbf_data_filter_field\" class=\"page_filter_select\">" . tablefields_as_select_options($tablename,$_REQUEST['dbf_data_filter_field']) . "</select> <div style=\"clear:both; display:inline\"></div><select name=\"dbf_data_filter_operator\" class=\"page_filter_select\">" . csv_to_options("=,&lt;,&gt;",$_REQUEST['dbf_data_filter_operator']) . "</select> <input type=\"text\" size=\"10\" name=\"dbf_data_filter_value\" class=\"page_filter_text_input\" value=\"" . $_REQUEST['dbf_data_filter_value'] . "\"></div>\n";
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_data_filter_field\" value=\"".$_REQUEST['dbf_data_filter_field']."\">\n";
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_data_filter_operator\" value=\"".$_REQUEST['dbf_data_filter_operator']."\">\n";
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_data_filter_value\" value=\"".$_REQUEST['dbf_data_filter_value']."\">\n";
		$output_text .= "<div class=\"search_filter_divider\"><div class=\"search_filter_divider_inner\"></div></div>";
	}

	// Display sort if necessary
	if ($this->options['dbf_sort'] || $this->options['filter']['dbf_sort']){
	if (!$this->value("on_query")){
		if (!$this->filter_values['dbf_sort_by_field'] && $this->options['filter']['order_by']){
			$this->filter_values['dbf_sort_by_field']=$this->options['filter']['order_by'];
		}
		$sort_fields_list=database_functions::populate_sort_fields($this->value("recordset_source"),$this->options['filter'],$this->filter_values['dbf_sort_by_field']);
	} else { 
		if ($this->options['filter']['sort_fields']){
			$sort_fields_list=populate_filter_fields($this->value("recordset_source"),$this->options['filter'],$_REQUEST['dbf_data_filter_field']);
		} else {
			$sort_fields_list = query_functions::queryfields_as_select_options($this->actual_query,$this->options['filter'],$_REQUEST['dbf_search_fields']);
		}
	}
		$output_text .= '<div id="sort">
<div class="filter_image_div"><img src="'.SYSIMGPATH.'/application_images/sort_filter_icon.png" width="37" height="37" alt=""></div>
<div class="collection">
<div class="filter_item_text">Sort By:</div>
<div class="filter_item_text"><!-- nothing here //--></div>
</div>
<div class="collection">
<div class="item_value_top"><select name="dbf_sort_by_field">' . $sort_fields_list . '</select></div><div class="item_value_bottom">';

	if ($this->options['dbf_sort_dir'] || $this->options['filter']['dbf_sort_dir']){$output_text .= '<select name="dbf_sort_direction">' . database_functions::csv_to_options("Asc;;Ascending,Desc;;Descending",$this->filter_values['dbf_sort_direction']) . '</select>';}
 	$output_text .= '</div>
</div>
</div><!-- closes filter //-->';

		//$output_text .= "<div id=\"Sort_select\" class=\"page_filter_div\"><span class=\"filter_header\">Sort by:</span> <select name=\"dbf_sort_by_field\" class=\"page_filter_select\">" . tablefields_as_select_options($tablename,$_REQUEST['dbf_sort_by_field']) . "</select>";
		$EXPORT['sort_selector'] = "<select name=\"dbf_sort_by_field\" class=\"page_filter_select\"";
		if ($this->options['filter']['sort_select_triggers_submit']){
                        $EXPORT['sort_selector'] .= " onChange=\"search_data()\" ";
                }
		$EXPORT['sort_selector'] .= ">" . database_functions::populate_sort_fields($this->value("recordset_source"),$this->options['filter'],$this->filter_values['dbf_sort_by_field']) . "</select>";

		/* $EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_sort_by_field\" value=\"".$_REQUEST['dbf_sort_by_field']."\">\n";
	*/
		if ($this->options['dbf_sort_dir'] || $this->options['filter']['dbf_sort_dir']){
		//	$output_text .= " <br clear=\"all\"/><select name=\"dbf_sort_direction\" class=\"page_filter_select\">" . csv_to_options("Asc;;Ascending,Desc;;Descending",$_REQUEST['dbf_sort_direction']) . "</select>\n";
			$EXPORT['sort_selector'] .= " <select name=\"dbf_sort_direction\" class=\"page_filter_select\">" . database_functions::csv_to_options("Asc,Desc",$this->filter_values['dbf_sort_direction']) . "</select>\n";
		}
		//$output_text .= "</div>\n";
		$output_text .= "<div class=\"search_filter_divider\"><div class=\"search_filter_divider_inner\"></div></div>";
	}

	// Display records per page select if necessary
	if ($this->options['dbf_rpp_sel'] || $this->options['filter']['dbf_rpp_sel']){
		$rpp_currently_selected=$this->options['filter']['dbf_rpp'];
		if ($this->filter_values['dbf_rpp']){$rpp_currently_selected=$this->filter_values['dbf_rpp'];}
		if ($this->value("on_query")){
			$print_query_to_display_fields="QUERY:";
		} else {
			$print_query_to_display_fields="";
		}
		$output_text .= '<div id="display">
<div class="filter_image_div"><img src="'.SYSIMGPATH.'/application_images/records_per_page_filter_icon.png" width="37" height="37" alt=""></div>
<div class="collection">
<div class="filter_item_text">Display:</div><div class="filter_item_text"><!-- nothing here //--></div>
</div>
<div class="collection">
<div class="item_value_top"><select name="dbf_rpp">' . database_functions::records_per_page_options($rpp_currently_selected) . '</select></div><div class="item_value_bottom">rows per page<br /><a href="Javascript:dbf_displayfields_popup_open(\''.$print_query_to_display_fields.$this->value('recordset_source').'\',\''. $this->options['filter']['filter_id'] . '\',\'' . $dyn_fields_to_display_list . '\')">set display fields</a></div>
</div>
<input type="hidden" name="dbf_cur_recordset_start" value="' . $cur_recordset_start . '"><input type="hidden" name="dbf_rpp_pre" value="' . $rpp_currently_selected . '">
</div><!-- closes filter //-->';

		//$output_text .= "<div id=\"Records_per_page_select\" class=\"page_filter_div\">Display <select name=\"dbf_rpp\" class=\"page_filter_select_nofloat\">" . records_per_page_options($rpp_currently_selected) . "</select> Records Per Page<input type=\"hidden\" name=\"dbf_cur_recordset_start\" value=\"" . $cur_recordset_start . "\"></div><input type=\"hidden\" name=\"dbf_rpp_pre\" value=\"" . $rpp_currently_selected . "\">\n";
		$EXPORT['records_per_page_select'] = "<select name=\"dbf_rpp\" ";
		if ($this->options['filter']['rpp_select_triggers_submit']){
			$EXPORT['records_per_page_select'] .= "onChange=\"search_data()\" ";
		}
		$EXPORT['records_per_page_select'] .= "class=\"page_filter_select\">" . database_functions::records_per_page_options($rpp_currently_selected) . "</select><input type=\"hidden\" name=\"dbf_cur_recordset_start\" value=\"" . $cur_recordset_start . "\"><input type=\"hidden\" name=\"dbf_rpp_pre\" value=\"" . $rpp_currently_selected . "\">\n";
		$EXPORT['records_per_page_full'] = "Display <select name=\"dbf_rpp\" class=\"page_filter_select\">" . database_functions::records_per_page_options($rpp_currently_selected) . "</select> Records Per Page<input type=\"hidden\" name=\"dbf_cur_recordset_start\" value=\"" . $cur_recordset_start . "\"><input type=\"hidden\" name=\"dbf_rpp_pre\" value=\"" . $rpp_currently_selected . "\">\n";


		// export hidden fields for rpp if not used
	/*
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_rpp\" value=\"".$_REQUEST['dbf_rpp']."\">\n";
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_rpp_pre\" value=\"".$_REQUEST['dbf_rpp_pre']."\">\n";
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_rpp_sel\" value=\"".$_REQUEST['dbf_rpp_sel']."\">\n";
		$EXPORT['form_header'] .= "<input type=\"hidden\" name=\"dbf_cur_recordset_start\" value=\"".$cur_recordset_start."\">\n";
*/
	}

	// if we have ANY of the filter widgets on the page, we need a go button..
	if ($this->options['dbf_sort'] || $this->options['dbf_filter'] || $this->options['dbf_rpp_sel'] || $this->options['dbf_search'] || $this->options['filter']['dbf_search'] || $this->options['filter']['dbf_rpp_sel'] || $this->options['filter']['dbf_filter'] || $this->options['filter']['dbf_sort']){

		$output_text .= '<div id="buttons">
<div class="filter_image_div"><input type="submit" value="Apply" onClick="Javascript:display_items()" style="width:40px; height:20px; background-color:#ffffff; color:#1b2c67; font-family:Tahoma, Trebuchet MS; font-size:10px;">';
		if (($this->filter_values['dbf_search_for'] || strlen($this->filter_values['dbf_search_for'])) || ($this->filter_values['dbf_data_filter_value'] || strlen($this->filter_values['dbf_data_filter_value']))){
			$output_text .= '<br /><input type="button" value="Reset" onClick="Javascript:clear_all_filtering()" style="width:40px; height:20px; font-size:10px; background-color:#ffffff; color:#1b2c67;"><input type="hidden" name="clear_filtering_post" value="">';
			global $current_site;
			$http_path=$current_site['http_path'];
			$EXPORT['clear_search_button'] = "<a href=\"Javascript:clear_search_filtering()\"><!--<img src=\"$http_path/images/search_button.gif\" border=\"0\" />//--><span style=\"font-size:9px; color:#fff; position:relative; top:-5px;\">Clear Search</span></a><input type=\"hidden\" name=\"clear_filtering_post\" value=\"\">";
		}
		$output_text .= '</div></div>';

		$output_text .= '</div><div id="filters_right"></div>';
		$output_text .= "</div>\n";
	}

	if ($this->filter_values['active_date_filter'] && $this->options['filter']['filter_field_between_dates'] && $this->options['filter']['dbf_date_filter_field']){
		if ($this->filter_values['dbf_search_date_start_full'] && $this->filter_values['dbf_search_date_end_full']){
			$start_dbf_search_date = $this->filter_values['dbf_search_date_start_full'];
			$end_dbf_search_date = $this->filter_values['dbf_search_date_end_full'];
		} else {
			$start_dbf_search_date = $this->filter_values['new_dbf_search_date_start-3'] . "-" . $this->filter_values['new_dbf_search_date_start-2'] . "-" . $this->filter_values['new_dbf_search_date_start-1'];
			$end_dbf_search_date = $this->filter_values['new_dbf_search_date_end-3'] . "-" . $this->filter_values['new_dbf_search_date_end-2'] . "-" . $this->filter_values['new_dbf_search_date_end-1'];
		}
		$dbf_date_active=1;
		$dbf_date_display="block";

	} else {
		$start_dbf_search_date = date("Y-m-d",time());
		$end_dbf_search_date = date("Y-m-d",time());
		$dbf_date_active=0;
		$dbf_date_display="none";
	}

	$this->set_value("start_dbf_search_date",$start_dbf_search_date);
	$this->set_value("end_dbf_search_date",$end_dbf_search_date);

	// a-z search popup
	if ($this->filter_values['active_az_filter']){
		$dbf_az_display="block";
		$dbf_az_active=$this->filter_values['active_az_filter'];
		$dbf_az_filter_value=$this->filter_values['dbf_az_filter_value'];
	} else {
		$dbf_az_display="none";
	}
	$output_text .= "<div align=\"center\" id=\"azSearchPopup\" style=\"display:$dbf_az_display;\"><b>Search A-Z: ";
	$EXPORT['az_icons'] .= "<div align=\"center\" id=\"azSearchPopup\" style=\"display:$dbf_az_display;\"><b>Search A-Z: ";
	$let=65;
	do {
		$output_text .= "<a href=\"Javascript:document.forms['list_records_filter'].elements['dbf_az_filter_value'].value='".chr($let)."';document.forms['list_records_filter'].elements['dbf_direction'].value='Reset'; document.forms['list_records_filter'].submit()\">";
		if ($dbf_az_filter_value==chr($let)){ $output_text .= "<span class=\"active_letter_filter\">".chr($let)."</span>";} else { $output_text .= chr($let); }
		$output_text .= "</a>\n ";
		$EXPORT['az_icons'] .= "<a href=\"Javascript:document.forms['list_records_filter'].elements['dbf_az_filter_value'].value='".chr($let)."';document.forms['list_records_filter'].elements['dbf_direction'].value='Reset'; document.forms['list_records_filter'].submit()\">";
		if ($dbf_az_filter_value==chr($let)){$EXPORT['az_icons'].="<span class=\"active_letter_filter\">".chr($let)."</span>";} else {$EXPORT['az_icons'] .= chr($let);}
		$EXPORT['az_icons'] .= "</a> ";
		$let++;
	} while ($let<91);
	$EXPORT['az_icons'] .= "</b><input type=\"hidden\" name=\"active_az_filter\" value=\"$dbf_az_active\"><input type=\"hidden\" name=\"dbf_az_filter_field\" value=\"".$this->options['filter']['filter_field_az']."\"><input type=\"hidden\" name=\"dbf_az_filter_value\" value=\"".$dbf_az_filter_value."\">";
	if ($dbf_az_filter_value){ $EXPORT['az_icons'] .= "<a href=\"Javascript:document.forms['list_records_filter'].elements['active_az_filter'].value='CLEAR_ME'; document.forms['list_records_filter'].elements['dbf_direction'].value='Reset'; document.forms['list_records_filter'].submit()\">Remove A-Z Filtering</a>";}
	$EXPORT['az_icons'] .= "</div>";
	$output_text .= "</b><input type=\"hidden\" name=\"active_az_filter\" value=\"$dbf_az_active\"><input type=\"hidden\" name=\"dbf_az_filter_field\" value=\"".$this->options['filter']['filter_field_az']."\"><input type=\"hidden\" name=\"dbf_az_filter_value\" value=\"".$dbf_az_filter_value."\">";
	if ($dbf_az_filter_value){$output_text .= "<a href=\"Javascript:document.forms['list_records_filter'].elements['active_az_filter'].value='CLEAR_ME'; document.forms['list_records_filter'].elements['dbf_direction'].value='Reset'; document.forms['list_records_filter'].submit()\">Remove A-Z Filtering</a>";}
	$output_text .= "</div>";

	// between dates filter popup
	$field_filter_name=$this->options['filter']['filter_field_between_dates'];
	if ($this->options['filter'][$field_filter_name]['fieldname_text']){
		$field_filter_name=$this->options['filter'][$field_filter_name]['fieldname_text'];
	}
	$field_filter_name=ucfirst(str_replace("_"," ",$field_filter_name));
	$output_text .= "<div align=\"center\" id=\"dateSearchPopup\" style=\"display:$dbf_date_display;\"><b>Filter '$field_filter_name' between dates:</b> " . database_functions::date_input_field($this->value("start_dbf_search_date"),"","dbf_search_date_start") . " - " . database_functions::date_input_field($this->value("end_dbf_search_date"),"","dbf_search_date_end","") . "<input type=\"hidden\" name=\"active_date_filter\" value=\"$dbf_date_active\"><input type=\"hidden\" name=\"dbf_date_filter_field\" value=\"";
	$output_text .= $this->options['filter']['filter_field_between_dates'] . "\"><input type=\"button\" value=\"Go\" onClick=\"display_items()\" style=\"width:40px; height:20px; background-color:#ffffff; color:#1b2c67\"> <input type=\"button\" onClick=\"document.forms['list_records_filter'].elements['active_date_filter'].value=0; document.getElementById('dateSearchPopup').style.display='none'; document.forms['list_records_filter'].submit()\" value=\"X\" style=\"color:#cc0000; background-color:#ffffff; height:20px; width:20px;\"></div>";

	// drop_down_filter
	if ($this->options['filter']['dbf_drop_down_filter_field']){
		$all_drop_down_filter_fields=explode(",",$this->options['filter']['dbf_drop_down_filter_field']);
		$all_drop_down_filter_lists=explode(";;;",$this->options['filter']['dbf_drop_down_filter_list']);
		$output_text .= "<div style=\"clear:both\" id=\"dbf_drop_down_filtering\">";
		$count_dd_filters=1;
		foreach ($all_drop_down_filter_fields as $dbf_drop_down_filter_field){
			$drop_down_options="";
			$on_sql=0;
			if (stristr($all_drop_down_filter_lists[$count_dd_filters-1],"QL")){
				$on_sql=1;
			}
			$drop_down_sql=str_replace("SQL:","",$all_drop_down_filter_lists[$count_dd_filters-1]);
			$export_key_name="drop_down_filter-" . $count_dd_filters;
			$element_id_name="dbf_" . $export_key_name;
			if ($on_sql){
				$drop_down_options=database_functions::csv_to_options(database_functions::get_sql_list_values($drop_down_sql),$this->filter_values[$element_id_name]);
			} else {
				$individual_options=explode(",",$drop_down_sql);
				foreach ($individual_options AS $option){
					if (stristr($option,";;")){
						list($option,$value)=explode(";;",$option);
					} else {
						$value=$option;
					}
					$drop_down_options .= "<option value=\"$option\"";
					if ($this->filter_values[$element_id_name]==$option){
						$drop_down_options .= " selected";
					}
					$drop_down_options .= ">$option</option>\n";
				}
			}
			$EXPORT['drop_down_filter'] = "<select id=\"$element_id_name\" name=\"$element_id_name\" onChange=\"search_data()\" class=\"page_filter_select\"><option value=\"\">All</option>$drop_down_options</select>";
			$output_text .= "<div class=\"dbf_drop_down_filter_each\">Filter By " . ucfirst(str_replace("_"," ",$all_drop_down_filter_fields[$count_dd_filters-1])).": ";
			$output_text .= $EXPORT['drop_down_filter'];
			$ouptut_text .= "</div>";
			$count_dd_filters++;
		}
		$output_text .= "</div>";
	}

	//$output_text .= "<p style=\"margin-top:0px; padding-top:0px; clear:both\"><hr size='1'></p>\n";
	$output_text .= "</div>\n";
	$output_text .= "</div>\n";
	$output_text .= "<script type=\"text/javascript\" src=\"/scripts/mootools_functions.js\"></script>\n";
	$output_text .= "<div id=\"div_below_page_filters\"></div>";

	$return_values['filtering_html']=$output_text;
	$return_values['form_header']=$EXPORT['form_header'];
	$return_values['search_button']=$EXPORT['search_button'];
	$return_values['search_input_field']=$EXPORT['search_input_field'];
	$return_values['sort_selector']=$EXPORT['sort_selector'];
	$return_values['records_per_page_select']=$EXPORT['records_per_page_select'];
	$return_values['records_per_page_full']=$EXPORT['records_per_page_full'];
	$return_values['az_icons']=$EXPORT['az_icons'];
	$return_values['clear_search_button']=$EXPORT['clear_search_button'];
	$return_values['drop_down_filter']=$EXPORT['drop_down_filter'];
	return ($return_values);
}

public function set_fields_to_display(){
	if (!$this->options['filter']['display_fields'] && !$this->value("on_query")){
	// display all from desc instead of list and load into same vars
		$all_table_fields = array();
		$sql_for_tablefields="desc " . $this->value("recordset_source");
		global $db;
		$desc_result = $db->query($sql_for_tablefields) or print format_error("Cant run field list: $sql_for_tablefields",0);
		while ($desc_row = $db->fetch_array($desc_result)){
			array_push($all_table_fields,$desc_row['Field']);
			if ($this->options['filter']['filter_field_between_dates']==$desc_row['Field']){$date_filter_type=$desc_row['Type'];}
		}	
		$this->options['filter']['display_fields']=implode(",",$all_table_fields);
	} else if ($this->value("on_query") && !$this->options['filter']['display_fields']){
		// display all from desc instead of list and load into same vars
		$this->options['filter']['display_fields']=$this->value("fields_to_display_from_query");
	} else if ($this->value("on_query") && $this->options['filter']['display_fields']){
		global $user;
		if ($user->value("id")==1){
			//print "On a query but got a fields to display list.";
		}
		$this->options['filter']['display_fields']=$this->value("fields_to_display_from_query");
	}

	// finally the new dynamic selector
	if ($this->filter_values['dbf_dynamic_fields_to_display_list']){
		$this->options['filter']['display_fields']=$this->filter_values['dbf_dynamic_fields_to_display_list'];
	}
	if ($this->filter_values['dbf_dynamic_fields_to_display']){
		if (count($this->filter_values['dbf_dynamic_fields_to_display'])>=1){
			$dynamic_ftd=join(",",$this->filter_values['dbf_dynamic_fields_to_display']);
			if ($dynamic_ftd){
				$this->options['filter']['display_fields']=$dynamic_ftd;
			}
		}
	}
	if ($this->filter_values['dbf_dynamic_mootools_fields_to_display_list']){
		$this->options['filter']['display_fields']=$this->filter_values['dbf_dynamic_mootools_fields_to_display_list'];
	}
	return $this->options['filter']['display_fields'];
}

public function recordset_sql(){

	// fields to select in recordset sql into $select_sql	
	$pk=$this->value("pk");
	if (!preg_match("/$pk,/",$this->options['filter']['display_fields'])){
		$select_sql = $this->value("pk")."," . $this->options['filter']['display_fields'];
	} else {
		$select_sql = $this->options['filter']['display_fields'];
	}

	// get our initial sql statement
	if ($this->value("on_query")){
		$queryparts=preg_split("/ FROM /i",$this->value("actual_query"));
		$query_after_from=$queryparts[1];
		if ($queryparts[2]){
			$query_after_from .= " FROM " . $queryparts[2];
		}
		if ($queryparts[3]){
			$query_after_from .= " FROM " . $queryparts[3];
		}
		if ($queryparts[4]){
			$query_after_from .= " FROM " . $queryparts[4];
		}
		$sql="SELECT " . $select_sql . " FROM " . $query_after_from; 
		// below - we have sub selects, so need to deal with this properly at some point, revert to actualy query
		if ($queryparts[2]){
			$sql=$this->value("actual_query");
		}
	} else {
		$sql="SELECT " . $select_sql . " FROM " . $this->value("recordset_source");
	}

	// initialise arrays for where_clauses and having_clauses and order_by, we need these shortly	
	$where_clauses = array();
	$having_clauses = array();
	$orderby="";

	if ($this->options['filter']['id_from']){ array_push($where_clauses, "$pk > " . $this->options['filter']['id_from']); }
	if ($this->options['filter']['id_to']){ array_push($where_clauses, "$pk < " . $this->options['filter']['id_to']); }
	// field equals is the filter from the filter bar!
	if ($this->options['filter']['dbf_data_filter_value'] && !$this->options['filter']['field_equals']){
		$this->options['filter']['field_equals'] = $this->options['filter']['dbf_data_filter_field'] . " " . $this->options['filter']['dbf_data_filter_operator'] . " " . $this->options['filter']['dbf_data_filter_value'];	
	}
	
        if ($this->options['filter']['field_equals']){
                $temp = preg_match("/( IN | = | > | < | != | BETWEEN )/",$this->options['filter']['field_equals'],$fe_matches);
                $fe_operator = $fe_matches[0][0];
		if (!$fe_operator || $fe_operator == " "){ $fe_operator = $fe_matches[0]; }
                $field_equals = preg_split("/( IN | = | > | < | != | BETWEEN )/",$this->options['filter']['field_equals']);
		$field_equals[1]=trim($field_equals[1]);
                if (!$field_equals[1] && !strlen($field_equals[1])){
			if ($fe_operator == " IN "){
				$field_equals_string = str_replace(" IN "," IN (",$field_equals[0]);
				$field_equals_string .= ")";
			} else {
				$field_equals_string = $field_equals[0];
			}
                } else {
			//apply yes,no to 1,0 etc - code below is NOT CURRENTLY USED
			if ($this->options[$field_equals[0]]['select_value_list']){
				$field_equals_options=explode(",",$this->options[$field_equals[0]]['select_value_list']);
				foreach ($field_equals_options as $field_equals_option){
					@list($fe_k,$fe_v)=explode(";;",$field_equals_option);
					if ($fe_k==1){ $positive_option=$fe_v;}
					if ($fe_k==0){ $negative_options=$fe_v;}
					if ($fe_k != "1" && $fe_k != "0"){
						$cancel_fe_options=1;
					}
				}
				if($cancel_fe_options){
					$positive_option=""; $negative_option="";
				}
			}

			if ($this->options['filter'][$field_equals[0]]['select_value_list']){

			}
			if ($fe_operator=="="){
				if (preg_match("/^yes$/i",$field_equals[1])){
					$field_equals[1]=1;
				} else if (preg_match("/^no$/i",$field_equals[1])){
					$field_equals[1]=0;
				}
			}
			if ($fe_operator == " IN "){
				$field_equals_string = $field_equals[0] . " $fe_operator (" . Codeparser::parse_request_vars($field_equals[1]) . ")";
			} else if ($fe_operator == " BETWEEN "){
				$between_bits=explode(" AND ",$field_equals[1]);
				if (!is_numeric($between_bits[0]) && !is_numeric($between_bits[1])){
					$field_equals[1]="'".$between_bits[0]."' AND '" . $between_bits[1] . "'";
				}
				$field_equals_string = $field_equals[0] . " $fe_operator " . Codeparser::parse_request_vars($field_equals[1]) . "";
			} else {
				$field_equals_string = $field_equals[0] . " $fe_operator \"" . Codeparser::parse_request_vars($field_equals[1]) . "\"";
			}
                }
                if (!preg_match("/HAVING:/",$field_equals_string)){
                        array_push($where_clauses,$field_equals_string);
                } else {
                        array_push($having_clauses,$field_equals_string);
                }
        }
 
	if ($this->options['filter']['where_clause']){
		array_push($where_clauses,$this->options['filter']['where_clause']);
	} 
	
	// MATT the filter_field_between_dates is the all important one below that stops the crash. Need to look more closely at the rest if still problems..
        if ($this->filter_values['active_date_filter'] && $this->options['filter']['filter_field_between_dates'] && $this->options['filter']['dbf_date_filter_field']){
		$start_dbf_search_date=$this->value("start_dbf_search_date");
		$end_dbf_search_date=$this->value("end_dbf_search_date");
                if ($date_filter_type=="timestamp" || $date_filter_type != "timestamp"){
                        $start_dbf_search_date .= " 00:00:00";
                        $end_dbf_search_date .= " 23:59:59";
                }
                array_push($where_clauses,"(" . $this->options['filter']['filter_field_between_dates'] . " >= \"$start_dbf_search_date\" AND " . $this->options['filter']['filter_field_between_dates'] . " <= \"$end_dbf_search_date\")");
	}

        if ($this->filter_values['active_az_filter']){
                $dbf_az_filter_value=$this->filter_values['dbf_az_filter_value'];
                // if its not a linked field we can just add the where clause, however if it is linked we need to do something else...
                if (preg_match("/SQL:/",$this->options['filter'][$this->options['filter']['search_a-z']]['select_value_list'])){
                        $possible_az_values=database_functions::search_linked_field($this->options['filter'][$this->options['filter']['search_a-z']]['select_value_list'],$dbf_az_filter_value,"startOfString");
			if ($possible_az_values){
				array_push($where_clauses,"(" . $this->options['filter']['search_a-z'] . " IN ($possible_az_values))");
			} else {
				array_push($where_clauses,"(1 = 0)"); // To stop an SQL error
			}

                } else {
                        array_push($where_clauses,"(" . $this->options['filter']['search_a-z'] . " LIKE \"" . $dbf_az_filter_value. "%\")");
                }
	}

	if ($this->options['filter']['sql_filter']){
		$bits=explode(" = ",$this->options['filter']['sql_filter']);
		$filter_fieldname=$bits[0];
		$filter_value=$bits[1];
		$match_result=preg_match_all("/{=.*}/",$filter_value,$matches);
		$matches=$matches[0];
		foreach ($matches as $each_match){
			$each_match_var=str_replace("{=","",$each_match);
			$each_match_var=str_replace("}","",$each_match_var);
			if (strlen(strpos($each_match_var,"SQL:"))){ // if it is an sql query
				$sql_filter_query = preg_replace("/SQL: ?/i","",$each_match_var);
				$queryfields=explode("=",$sql_filter_query);
				global $user;
				if ($queryfields[1]==" user_data_from_cookie('id')"){$queryfields[1]=$user->value("id");}	
				if ($queryfields[1]==" current_user()"){$queryfields[1]=$user->value("id");}	
				$sql_filter_query=implode("=",$queryfields);
				$filter_result=$db->query($sql_filter_query) or die ("<b>Problem with $sql_filter_query:</b><p>" . $db->errmsg());
				while ($filter_row = $db->fetch_array($filter_result)){
					array_push($where_clauses, $bits[0] . "='" . $filter_row[0] . "'");
				}
			}
		}// close for each $match
		if (!$matches){
			$this->options['filter']['sql_filter']=str_replace("SQL:","",$this->options['filter']['sql_filter']);
			$this->options['filter']['sql_filter']=database_functions::eval_request($this->options['filter']['sql_filter']);
			array_push($where_clauses, "(" . $this->options['filter']['sql_filter'] . ")");
		}
	}
	

	// anything to search for?
	if (($this->filter_values["dbf_search_for"] || strlen($this->filter_values["dbf_search_for"])) || $this->options['dbf_search_for']){
		if (stristr($_SERVER['PHP_SELF'],"administrator.php")){
			//print "on the where now with " . $this->filter_values["dbf_search_for"] . "<br />";
		}
		if ($this->options['filter']['preg_replace_from_search_input']){
			$replace_reg="/".$this->options['filter']['preg_replace_from_search_input']."/";
			$category_through_search=$this->filter_values["dbf_search_for"];
			$new_dbf_search_for=$this->filter_values["dbf_search_for"];
			$new_dbf_search_for=rtrim(preg_replace($replace_reg,"",$new_dbf_search_for));
			$this->filter_values["dbf_search_for"]=$new_dbf_search_for;
			$this->options['dbf_search_for']=$this->filter_values["dbf_search_for"];
			if ($this->options['filter']['preg_replace_from_search_is_category']){
				$category_through_search=preg_match("/\((.*)\)$/",$category_through_search,$category_matched);
				$category_matched=trim($category_matched[1]);
				$search_through_fields=$this->filter_values['dbf_search_fields'];
				if (!$search_through_fields){$search_through_fields=$this->options['filter']['dbf_search_fields'];}
				$search_through_fields=",".$search_through_fields.",";
				$check_category_match=",".$category_matched.",";
				if (stristr($search_through_fields,$check_category_match)){
					$this->filter_values['dbf_search_fields']=$category_matched;
					$this->options['filter']['dbf_search_fields']=$category_matched;
					$this->options['filter']['search_fields']=$category_matched;
				} else {
					if ($this->options['filter']['preg_replace_from_search_default_field'] && $category_matched){
						$df=$this->options['filter']['preg_replace_from_search_default_field'];
						$this->filter_values['dbf_search_fields']=$df;
						$this->options['filter']['dbf_search_fields']=$df;
						$this->options['filter']['search_fields']=$df;
					} else {
						// possibly an error causing condition here ?
					}
				}
			}
		}
		if (!$this->options['dbf_search_for']){$this->options['dbf_search_for']=$this->filter_values["dbf_search_for"];}

		// fields to search?
		$search_for_fields=array();
		if ($this->filter_values['dbf_search_fields']){
			$fields_to_search=$this->filter_values['dbf_search_fields'];
		} else if ($this->options['filter']['dbf_search_fields']){
			$fields_to_search=$this->options['filter']['dbf_search_fields'];
		}
		//if (!$fields_to_search){ $fields_to_search="All Fields"; } // Added to override lack of session value
		if ($fields_to_search=="All Fields"){
			if ($this->options['filter']['search_fields']){
				$fields_to_search=$this->options['filter']['search_fields'];
			} else {
				$fields_to_search=$this->options['filter']['display_fields'];
			}
		}
		if ($fields_to_search){
		 	$each_search_fields=explode(",",$fields_to_search);
			$impossible_search=0;
			foreach ($each_search_fields as $each_search_field){
				if (stristr($each_search_field,"#test-if-value#")){
					
					@list($each_search_field,$discard,$testfor,$pos_result,$neg_result)=explode("#",$each_search_field);
				} else {
					$testfor=null; $pos_result=null; $neg_result=null;
				}
				// is this field from a select list?
				if (preg_match("/SQL: ?SELECT \w+ ?, ?\w+/i",$this->options['filter'][$each_search_field]['select_value_list'])){
					//print "searching linked field here on $each_search_field - " . $this->options['filter'][$each_search_field]['select_value_list'];
					if ($this->options['filter'][$each_search_field]['select_key_table_for_record_lists']){
						$specify_search_link_table=$this->options['filter'][$each_search_field]['select_key_table_for_record_lists'];
					} else {
						$specify_search_link_table="";
					}
					$possible_values=database_functions::search_linked_field($this->options['filter'][$each_search_field]['select_value_list'],$this->options['dbf_search_for'],"all",$specify_search_link_table);
					$prepend_fieldnames="";
					if (!$this->value("on_query")){ $prepend_fieldnames = $this->value("recordset_source") . ".";}
					if ($possible_values){
						if (!preg_match("/^,+$/",$possible_values)){
							$search_for_field_string = $prepend_fieldnames . $each_search_field . " IN (" . $possible_values . ")";
						}
					} else { //print format_error("Warning: no select list values found for the following field: $each_search_field",0,2);
						//$search_for_field_string = $prepend_fieldnames . $each_search_field . " IN (\"XXXXXXNO-POSSIBLE-VALUES-FOUNDXXXXXX\")";

						// ok, in the line below there are no possible values so the search needs to fail, however we can still look up on the 
						// actual key that has been entered. This should be optional really as looking up text on int fields goes nuts!
						// however it is useful for searching 1 and 0 against a field set to yes/no

						//$search_for_field_string = $prepend_fieldnames . $each_search_field . " IN (\"".$this->options['dbf_search_for']."\")";
					}
				} else {
					//print "no linked field on $each_search_field - " . $this->options['filter'][$each_search_field]['select_value_list'] . "<br>";
					global $user;
					// non sql select value lists
					if ($this->options['filter'][$each_search_field]['select_value_list']){
						$poss_values_array=array();
						$possible_values=$this->options['filter'][$each_search_field]['select_value_list'];
						if (stristr($possible_values,";;")){
							$all_value_pairs=explode(",",$possible_values);
							foreach ($all_value_pairs as $all_value_pair){
								@list($keyvalue,$keytext)=explode(";;",$all_value_pair);
								if ($keytext==$this->options['dbf_search_for']){
									array_push($poss_values_array,$keyvalue);
								}
							}
							$possible_values=join(",",$poss_values_array);
							$linked_field_non_sql=1;
						} 
						if ($possible_values){
							$search_for_field_string = $prepend_fieldnames . $each_search_field . " IN (" . $possible_values . ")";
						} else{
							// no possible values, so se just search for the original string (which should fail and return no results....
							$search_for_field_string = $prepend_fieldnames . $each_search_field . " IN (\"" . $this->options['dbf_search_for']. "\")";
							if ($this->db_field_types[$each_search_field]['SimpleType']=="boolean" && $this->options['dbf_search_for'] != "0" && $this->options['dbf_search_for'] != "1"){
								$search_for_field_string = $prepend_fieldnames . $each_search_field . " = \"" . $this->options['dbf_search_for']. "\"";
							}
						}
					}
					if (!$this->value("on_query")){
						if (!$linked_field_non_sql){
							if ($this->options['filter']['search_method']=="any_word" || $this->options['filter']['search_method']=="all_words"){
								$search_for_field_string=$this->build_search_criterea_for_multiple_words($each_search_field);
							} else {
								$search_for_field_string = $this->value("recordset_source") . "." . $each_search_field . " LIKE \"%" . $this->options['dbf_search_for'] . "%\"";
							}
						}
					} else {
						// The following 5 lines are NOT tested but should work exactly as the on_query section above - 17/6/11
						if ($this->options['filter']['search_method']=="any_word" || $this->options['filter']['search_method']=="all_words"){
							$search_for_field_string=$this->build_search_criterea_for_multiple_words($each_search_field);
						} else {
							if ($neg_result && $testfor && $pos_result){
								if (strtolower($this->options['dbf_search_for']) != strtolower($testfor)){
									$search_for_field_string = $each_search_field . " != \"" . $pos_result. "\"";
								} else {
								$search_for_field_string = $each_search_field . " LIKE \"%" . $this->options['dbf_search_for'] . "%\"";
								}
							} else {
								$search_for_field_string = $each_search_field . " LIKE \"%" . $this->options['dbf_search_for'] . "%\"";
							}
						}
					}
				}
				if ($search_for_field_string){
					$search_or_not=$this->check_field_type_for_search($each_search_field,$this->options['dbf_search_for'],$possible_values);

					if ($search_or_not){
						$search_for_field_string="(" . $search_for_field_string . ")"; // copied in late from clairebatchelor
						array_push($search_for_fields,$search_for_field_string);
						//print_debug("Adding $search_for_field_string");
					} else {
						$impossible_search++;
					}
				} else {
	}
				$search_for_field_string="";
			}
		} else {
			// run the above code over a table description here..
		}
		if (count($each_search_fields)==$impossible_search){
			//print_debug("This is an impossible search");
			$search_for_fields=array("1=0");
		}
			//print_debug("Got some!");
			//print_debug($sql_search_paramaters);
			$sql_search_paramaters = "(" . implode(" OR ",$search_for_fields) . ")";
			if (stristr($sql_search_paramaters,"HAVING:")){
				array_push($having_clauses,str_replace("","",$sql_search_paramaters));
			} else {
				array_push($where_clauses,$sql_search_paramaters);
			}
	}
	
	// extra drop down filter
	$dd_filter_fields=explode(",",$this->options['filter']['dbf_drop_down_filter_field']);
	if ($this->filter_values['dbf_drop_down_filter-1'] && $this->options['filter']['dbf_drop_down_filter_field']){
		array_push($where_clauses, $dd_filter_fields[0] . " = \"" . $this->filter_values['dbf_drop_down_filter-1'] . "\"");
	}
	if ($this->filter_values['dbf_drop_down_filter-2'] && $this->options['filter']['dbf_drop_down_filter_field']){
		array_push($where_clauses, $dd_filter_fields[1] . " = \"" . $this->filter_values['dbf_drop_down_filter-2'] . "\"");
	}
	if ($this->filter_values['dbf_drop_down_filter-3'] && $this->options['filter']['dbf_drop_down_filter_field']){
		array_push($where_clauses, $dd_filter_fields[2] . " = \"" . $this->filter_values['dbf_drop_down_filter-3'] . "\"");	
	}
	if ($this->filter_values['dbf_drop_down_filter-4'] && $this->options['filter']['dbf_drop_down_filter_field']){
		array_push($where_clauses, $dd_filter_fields[3] . " = \"" . $this->filter_values['dbf_drop_down_filter-4'] . "\"");
	}
	if ($this->filter_values['dbf_drop_down_filter-5'] && $this->options['filter']['dbf_drop_down_filter_field']){
		array_push($where_clauses, $dd_filter_fields[4] . " = \"" . $this->filter_values['dbf_drop_down_filter-5'] . "\"");	
	}

	// relation keys and ids
	if ($this->relation_id && $this->relation_key){
		global $db;
		$get_relation_from_id_sql=$db->record_from_id("table_relations",$this->relation_id);
		$relation_query = $get_relation_from_id_sql['field_in_table_2'] . "= " . $this->relation_key;
		array_push ($where_clauses, $relation_query); 
	}

	// flatten where_clauses
	$all_sql_paramaters = implode(" AND ", $where_clauses);
	if ($all_sql_paramaters){
		if ($this->value("on_query") && preg_match("/ WHERE /",$this->value("actual_query"))){
			$where_sql .= " AND (" . $all_sql_paramaters . ") ";
		} else {
			$where_sql .= " WHERE " . $all_sql_paramaters . " ";
		}
	}
	if ($where_sql){
		if (stristr($sql,"GROUP BY")){
			$sqlbits=explode(" GROUP BY ",$sql);
			$sql = $sqlbits[0] . $where_sql . " GROUP BY " . $sqlbits[1];
		} else {
			$sql .= $where_sql;
		}
	}
	
	// ordering - first form based, then filter based, then by pk (may be better to use first record than pk)
	// in case pk is not displayed, its just logical to use the first field as a default yes?
	if ($this->filter_values['dbf_sort_by_field']){
		// is orderby on a svl field though?
		if ($this->options['filter'][$this->filter_values['dbf_sort_by_field']]['select_value_list']){
			$order_svl_field=$this->filter_values['dbf_sort_by_field'];
		}
		$orderby = " ORDER BY " . str_replace("HAVING:","",$this->filter_values['dbf_sort_by_field']);
		if ($this->filter_values['dbf_sort_direction']){
//			print "set at 1 to " . $this->filter_values['dbf_sort_direction'];
			$orderby .= " " . $this->filter_values['dbf_sort_direction'];
		}
	} else {
		if ($this->options['filter']['order_by']){
			$orderby = " ORDER BY " . $this->options['filter']['order_by'] . " ";
			$obf=$this->options['filter']['order_by'];
			if ($this->options['filter'][$obf]['select_value_list']){
				$order_svl_field=$obf;
			}
		} else {
			if (!$this->value("on_query")){ $orderby = " ORDER BY $pk "; }
		}

		if ($this->options['filter']['order_by_direction']){ 
			$orderby .= $this->options['filter']['order_by_direction'] . " "; 
		} else if ($this->filter_values['dbf_sort_direction']){
			$orderby .= $this->filter_values['dbf_sort_direction'];
		}
	}
	# parse out any filter keys where the direction is given in the sort_by key using the > operator
	$orderby = preg_replace("/>desc ASC/i"," DESC",$orderby);
	$orderby = preg_replace("/>desc DESC/i"," DESC",$orderby);
	$orderby = preg_replace("/>desc/i"," DESC",$orderby);
	$sql .= $orderby;

	// if ordering on an svl field and not already on a query, but are on a single table listing
	// then we need an inner join to the svl source..
	// requies modification of the sql, here we go:
	if ($order_svl_field && !$this->value("on_query")){
		// get the list
		$svl=$this->options['filter'][$order_svl_field]['select_value_list'];
		if (preg_match("/^SQL:/",$svl)){
			$svl=trim(str_replace("SQL:","",$svl));
			$svl=trim(str_replace("SELECT","",$svl));
			list($svl_fields,$fromtable)=preg_split("/ FROM/i",$svl);
			$each_svl_field=explode(",",$svl_fields);
			$final_svl_field=array_shift($each_svl_field);
			$final_svl_field_2=array_pop($each_svl_field);
			if (stristr ($fromtable, " WHERE ")){
				$fromtable_list=explode(" ",$fromtable);
				$fromtable=$fromtable_list[0];
			}
			$fromtable=trim($fromtable);
			if (!$fromtable){ $do_not_amend=1; }
			if (!$this->value("on_query") && !$do_not_amend){
				$join_sub_sql = "LEFT JOIN $fromtable ON " . $this->value("recordset_source") . ".$order_svl_field = $fromtable.$final_svl_field" . " ";
				$replacewith="FROM " . $this->value("recordset_source") . " " . $join_sub_sql;
				$sql = preg_replace("/FROM \w+/","$replacewith",$sql);
				
				$new_field_list="";
				list($sql_field_list,$remainder)=explode(" FROM ",$sql);
				$sql_field_list=trim(str_replace("SELECT ","",$sql_field_list));
				$each_svl_fields_2=explode(",",$sql_field_list);
				foreach ($each_svl_fields_2 as $each_svl_field){
					$each_svl_field=$this->value("recordset_source") . "." . $each_svl_field;
					$new_field_list .= $each_svl_field . ",";
				}
				$new_field_list=preg_replace("/,$/","",$new_field_list);
				$sql = str_replace($sql_field_list,$new_field_list,$sql);
				// order svl field here needs to change to..
				$sql=preg_replace("/ORDER BY \w+/","ORDER BY $fromtable.$final_svl_field_2",$sql);
			}
		}
	}

	// may need to move group by elements to after the where!
	$gb_before_where = preg_match_all("/(GROUP BY \w+.*) WHERE /",$sql,$gb_matches);
	if ($gb_matches){
		$gb_to_move=$gb_matches[1][0];
		$sql=str_replace($gb_to_move,"",$sql);
		// 6.5 - are we HAVING anything?
		if ($having_clauses){
			$having_code=implode(" AND ", $having_clauses);
			$having_code=str_replace("HAVING:","",$having_code);
			$having_code=str_replace("HAVING","",$having_code);
			$gb_to_move .= " HAVING " . str_replace("HAVING:"," ",$having_code) . " ";
		}
		$orderby_matches=preg_match_all("/(ORDER BY \w+.*)/",$sql,$ob_matches);
		if ($ob_matches){
			$ob_to_move=$ob_matches[1][0];
			$sql=str_replace($ob_to_move,"",$sql);
		}
		$sql.= " " . $gb_to_move;
		if ($ob_to_move){
			$sql .= " " . $ob_to_move;
		}

	}

	// final code parsee
	global $user;
	$sql = str_replace("{=current_user_hierarchial_type}",$user->value("hierarchial_order"),$sql);
	$sql = str_replace("SQL:","",$sql);

	return $sql;
}

public function add_limits_to_sql($sql,$max_results){
	// just adds the limit functionality to the query - by now we've used it to get total rows returned
	// so do it now
	if ($max_results < $this->options['filter']['limit_from']){
		$this->options['filter']['limit_from']=0;	
	}
	if (!$this->options['filter']['limit']){ $this->options['filter']['limit_from']=0;} // mattplatts march 2012
	if ($this->filter_values['dbf_rpp'] && $this->options['filter']['limit']){ $this->options['filter']['limit']=$this->filter_values['dbf_rpp'];} // MATT SEOT 2011
	if ($this->options['filter']['limit'] && $this->options['filter']['limit'] != "All"){
		if ($this->options['filter']['limit_from']){
			$sql .= " LIMIT " . $this->options['filter']['limit_from'] . "," . $this->options['filter']['limit'] . " "; 
		} else {
			$sql .= " LIMIT " . $this->options['filter']['limit'] . " "; 
		}
	}
	return $sql;
}

public function check_permissions_on_returned_records($permissions_result,$result){

	global $user;
	global $db;
	$view_perm_fail=0;
	if ($permissions_result['check_returned_rows']){
		// now need to look at the rows which are returned to see if any dont match permissions settings
		// even 1 will cause a failure as the SQL should be constructed to filter these out initially
		$permissions_tests=explode(";",$permissions_result['check_returned_rows']);
		foreach ($permissions_tests as $perm_test){
			list($setting,$operator)=explode(":",$perm_test);
			while ($h1=$db->fetch_array($result)){
				if ($h1[$setting]){
					if ($h1[$setting] = $user->value("id")){ $view_perm_fail=1; }
				} else { $view_perm_fail=1;}
			}
		}		
	}

	if ($view_perm_fail){
		$rv=0;
	} else {
		$rv=1;
	}
	return $rv;
}

public function paging_and_sub_display($result,$total_results_for_non_limited_query){

	global $db;
	$total_results_for_current_query=$db->num_rows($result);
	$this->set_value("db_num_rows",$db->num_rows($result));
	if (!$this->options['filter']['limit_from']){$start_number=1;} else {$start_number=$this->options['filter']['limit_from'];}
	$output_text .= "<input name=\"results_for_current_query\" type=\"hidden\" value=\"$total_results_for_current_query\">";
	$EXPORT['append_to_form_header'] .= "<input type=\"hidden\" name=\"results_for_current_query\" value=\"$total_results_for_current_query\">\n"; // WATCH THIS

	$total_so_far=$this->options['filter']['limit'] + $this->options['filter']['limit_from'];
	$next_record_to_print_in_next_query = ($total_so_far + ($dbf_rpp+1)); // WATCH - not set anywhere!
	$output_text .= "<input type=\"hidden\" name=\"dbf_next\" value=\"$next_record_to_print_in_next_query\">";
	$EXPORT['append_to_form_header'] .= "<input type=\"hidden\" name=\"dbf_next\" value=\"$next_record_to_print_in_next_query\">\n"; // WATCH THIS
	$this->set_value("display_start",$start_number);
	$this->set_value("display_end",$this->options['filter']['dbf_rpp']+$start_number);
	if ($this->value("display_start") !=1){
		$this->set_value("display_start",$this->value("display_start")+1);
	} else { 
		$this->set_value("display_end",$this->value("display_end")-1);
	}
	if ($this->options['filter']['limit_from']+$this->options['filter']['dbf_rpp']<$total_results_for_current_query){
		$this->set_value("display_end",$total_results_for_current_query);
	}
	if ($this->options['filter']['limit_from']+$this->options['filter']['dbf_rpp']>$total_results_for_non_limited_query){ 
		$this->set_value("display_end",$total_results_for_non_limited_query);
	}
	if ($total_results_for_non_limited_query==0){$this->set_value("display_end",0);}
	if ($this->value("display_end") > $total_results_for_non_limited_query){ $this->set_value("display_end",$total_results_for_non_limited_query); }
	if ($this->value("display_start") > $this->value("display_end")){
		//$this->set_value("display_start",1);
	}
	if ($this->value("display_end")==0){
			if ($this->options['filter']['no_results_message']){
				$output_text .= $this->options['filter']['no_results_message'];
				$EXPORT['no_results'] = "<p class=\"no_results_message_class\">".$this->options['filter']['no_results_message']."</p>";
			} else {
				//$output_text .= "<table border=0 cellpadding=0 cellspacing=0 width=\"100%\" style=\"background-color:green\"><tr><td valign=\"middle\"><img src=\"".SYSIMGPATH."/icons/exclamation.png\" /></td><td valign=\"middle\">&nbsp; <font size='1'>No Data Found that matched your search criterea.</font></td></tr></table>";
				$output_text .= "<p class=\"no_results_message_class\">No results found that matched your search criterea.</p>";
				$EXPORT['no_results'] = "<p class=\"no_results_message_class\">No results found that matched your search criterea.</p>";
			}
		$EXPORT['first_record_number']=0;
		$EXPORT['last_record_number']=0;
		$EXPORT['total_records']=0;
		$EXPORT['page_number']=0;
		$EXPORT['total_pages']=0;
	} else {
		$display_start_to_display_end = $this->value("display_start") . " - " . $this->value("display_end");
		$display_plural="s";
		$EXPORT['first_record_number']=$this->value("display_start"); 
		$EXPORT['last_record_number']=$this->value("display_end"); 
		$EXPORT['total_records']=$total_results_for_non_limited_query;
		$this->set_value("records_per_page",$this->filter_values['dbf_rpp']);
		if (!$this->value("records_per_page")){$this->set_value("records_per_page",$this->options['filter']['dbf_rpp']);}
		$EXPORT['page_number']=ceil($this->value("display_end")/$this->value("records_per_page")); 
		$EXPORT['total_pages']=ceil($total_results_for_non_limited_query/$this->value("records_per_page"));

		// for writing page links
		$max_number_of_page_links=10;
		$first_page_link="1";
		$on_link=1;
		do {
			$on_link++;
		} while ($on_link<=$EXPORT['total_pages']); 

		if ($this->value("display_start")==$this->value("display_end")){$display_start_to_display_end=$this->value("display_end"); $display_plural="";}

		$output_text .= "<div class=\"displaying_records_text\">Displaying $display_start_to_display_end of $total_results_for_non_limited_query </div>\n"; // WATCH total_results... etc prob not set

		if ($this->options['filter']['limit_from']>=1 || ($this->options['filter']['limit_from']+$this->options['filter']['dbf_rpp']<$total_results_for_non_limited_query && $this->options['filter']['dbf_rpp'] != "All" && $this->options['filter']['dbf_rpp'])){
			$output_text .= "<div id=\"next_and_previous\">";
		}
		$previous_page_link="Javascript:previous_page()";
		$next_page_link="Javascript:next_page()";
		if ($this->options['filter']["recordset_filters_via_get"] && $this->options['filter']['recordset_filters_via_get_base_url']){
			$previous_page_link=HTTP_PATH."/".$this->options['filter']['recordset_filters_via_get_base_url']."/page/".($this->options['filter']['dbf_next']-$this->options['filter']['dbf_rpp'])."/".$this->options['filter']['dbf_rpp']."/";
			$next_page_link=HTTP_PATH."/".$this->options['filter']['recordset_filters_via_get_base_url']."/page/".($this->options['filter']['dbf_next']+$this->options['filter']['dbf_rpp'])."/".$this->options['filter']['dbf_rpp']."/";
			if ($_REQUEST['mt']){
				$previous_page_link .= "mt/".$db->db_escape($_REQUEST['mt'])."/";
				$next_page_link .= "mt/".$db->db_escape($_REQUEST['mt'])."/";
			}
		} else if ($this->options['filter']['recordset_filters_via_get'] && $_REQUEST['action']=="cart_categories_browse"){
			global $mycart;
			global $db;
			if($mycart->value("base_category_list_page") && $mycart->value("inner_category_list_page")){
				$next_record=$this->options['filter']['limit_from']+$this->options['filter']['limit']+1;
				$previous_record=$this->options['filter']['limit_from']-($this->options['filter']['limit'])+1;
				$catname=str_replace(" ","_",str_replace("&","and",$db->field_from_record_from_id("product_categories",$_REQUEST['category_id'],"category_name")));
				if ($_REQUEST['master_category_id']){
					$master_catname=$db->field_from_record_from_id("product_categories",$_REQUEST['master_category_id'],"category_name");
				}
				if ($previous_record<0){$previous_record=0;}
				if ($_REQUEST['master_category_id']){
					$base_url="/categories/".$_REQUEST['master_category_id']."/$master_catname/".$_REQUEST['category_id']."/$catname/content/". $mycart->value("inner_category_list_page") . "/page/";
				} else {
					$base_url="/categories/".$_REQUEST['category_id']."/$catname/content/".$mycart->value("base_category_list_page")."/page/";
				}
				$next_page_link=$base_url.$next_record."/".$this->options['filter']['dbf_rpp'];
				$previous_page_link=$base_url.$previous_record."/".$this->options['filter']['dbf_rpp'];
			}
		}

		if ($this->options['filter']['limit_from']>=1){
			$output_text .= "<a href=\"$previous_page_link\">&lt; Previous</a>";
			$EXPORT['previous_page'] = "<a href=\"$previous_page_link\">&lt; Previous</a>";
			$EXPORT['previous_page_icon'] = "<p style=\"border:0px; margin:0px; display:inline; background-image:url('".SYSIMGPATH."/icons/resultset_previous.png'); background-position:top left; background-repeat:no-repeat; padding-left:20px;\" class=\"dbf_previous_page\"><a href=\"$previous_page_link\" style=\"text-decoration:none\">Previous Page</a></p>";
		}

		if ($this->options['filter']['limit_from']+$this->options['filter']['dbf_rpp']<$total_results_for_non_limited_query && $this->options['filter']['dbf_rpp'] != "All" && $this->options['filter']['dbf_rpp']){

			if ($this->options['filter']['limit_from']>=1){$output_text .= " | "; $EXPORT['next_previous_divider']=" | ";}

			$output_text .= "<a href=\"$next_page_link\">Next &gt;</a><p>";
			$EXPORT['next_page'] = "<a href=\"$next_page_link\">Next &gt;</a><p>";
			$EXPORT['next_page_icon'] = "<p style=\"border:0px; margin:0px; display:inline; background-image:url('".SYSIMGPATH."/icons/resultset_next.png'); background-position:top right; background-repeat:no-repeat; padding-right:20px;\" class=\"dbf_previous_page\"><a href=\"$next_page_link\" style=\"text-decoration:none\">Next Page</a></p>";
	}
		if ($this->options['filter']['limit_from']>=1){ $output_text .= "</div>";} // close next_and_previous
	}
	$r_hash['EXPORT']=$EXPORT;
	$r_hash['output_text']=$output_text;
	return $r_hash;
}

public function table_header_row(){
	$output_text = "<tr class=\"admin_table_heading\">";
	$fields_to_display_array=explode(",",$this->options['filter']['display_fields']);
	if ($this->value("on_query")){$fields_to_display_array=explode(",",query_functions::queryfields_as_csv_taking_care_of_brackets($this->options['filter']['display_fields']));}
	if ($this->value("display_end")==0){$fields_to_display_array=array();}
	foreach ($fields_to_display_array as $display_field){
		if ($debug){print "<p>we are on $display_field with text of " . $this->options['filter'][$display_field]['fieldname_text'] . " ";}
		if ($this->options['filter'][$display_field]['fieldname_text']){$field_text=$this->options['filter'][$display_field]['fieldname_text'];} else {$field_text=ucfirst(str_replace("_"," ",$display_field));}
		if ($this->options['filter']['filter_field_between_dates'] && $display_field==$this->options['filter']['filter_field_between_dates']){
			$field_text = "<a href=\"Javascript:popUpDate()\">$field_text</a>";
		}
		if ($this->options['filter']['search_a-z'] && $display_field==$this->options['filter']['search_a-z']){
		if ($this->options['filter']['search_a-z_at_start']){
			$field_text .= "<script language=\"Javascript\" type=\"text/javascript\">\npopUpAZ()\n</script>\n";
		}
			$field_text = "<a href=\"Javascript:popUpAZ()\">$field_text</a>";
		}
		$display_field = str_replace("_id","",$display_field);
		if ($field_text != ""){
			if ($debug){ print "at this point field text is " . $field_text . "</p>";}
			if ($this->options['filter'][$display_field]['field_width']){ $fieldwidth = "style=\"width:" . $this->options['filter'][$display_field]['field_width'] . "px\"";} else {$fieldwidth="";}
			if ($this->options['filter'][$display_field]['supress_html_in_display']){ $tablehead_extra = "<span class=\"tablehead_extra\"> (HTML Supressed)</span>";} else { $tablehead_extra=""; }
			$output_text .= "<td $fieldwidth>" . $field_text . $tablehead_extra ."</td>";
		}
	}

	// include multiple delete (imd)
	if ($this->options['filter']['dbf_imd']){
		$head_action_colspan=3;
		$output_text .= "<td colspan=\"$head_action_colspan\"><a href=\"Javascript:document.forms['multi_records_form'].submit()\">Delete selected</a></td>";
	} else {
		$head_action_colspan=1;
		$output_text .= "<td colspan=\"$head_action_colspan\" style=\"background-color:#ffffff; min-width:150px !important;\"></td>";
	}
	$output_text .= "</tr>\n";

	return $output_text;
}

public function column_totals($total_cols){

	global $db;
	if ($this->value("db_num_rows")>0){
	$output_text = "<tr>";
	$display_fields_array=explode(",",$this->options['filter']['display_fields']);
	foreach ($display_fields_array as $display_field){
		$total_printed=0;
		foreach ($total_cols as $total_var => $total_val){
			if (stristr($display_field," AS ")){
				@list($sql_field,$display_field)=explode(" AS ",$display_field);	
			}
			if ($display_field == $total_var){
				$output_text .= "<td style=\"font-weight:bold\">" . $this->options['filter'][$total_var]['field_prefix'] . $total_val . "</td>";
				$total_printed=1;
			}
		}
		if (!$total_printed){
			$output_text .= "<td></td>";
		}
	}
	$output_text .= "</tr>";
	} else {
			$output_text="";
	}
	return $output_text;

}

public function column_totals_report_at_base($total_cols){
	if ($this->value("db_num_rows")>0){
		$output = "<br clear=\"all\"><p><b>Totals (this page only):</b></p><hr size='1' />";
		foreach ($total_cols as $total_var => $total_val){
			$total_val=sprintf("%4.2f",$total_val);
			$output .= "<b>" .  ucfirst(str_replace("_"," ",$total_var)) . ": </b>" . $this->options['filter'][$total_var]['field_prefix'] . $total_val . "<br />";
		}
	} else {
		$output="";
	}
	return $output;
}

public function data_to_excel_spreadsheet($data,$tablename,$options){
	require_once(LIBPATH . "/library/modules/excel.php"); // load excel writing functions
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

	// write title into excel spreadsheet - table name and date
	if ($options['filter']['spreadsheet_title']){
		$spreadsheet_title=$options['filter']['spreadsheet_title'];
		$spreadsheet_title=str_replace("{=date}",date('Y-m-d'),$spreadsheet_title);
		$sXlOut .= xlsWriteLabel(0,0,"$spreadsheet_title"); 
	} else {
		$sXlOut .= xlsWriteLabel(0,0,"$tablename - " . date('Y-m-d')) ; 
	}

	if ($options['filter']['spreadsheet_insert_text']){
		$spreadsheet_insert_text=$options['filter']['spreadsheet_insert_text'];

		$date_start=$_REQUEST['new_dbf_search_date_start-1'] . "/";
		$date_start .= $_REQUEST['new_dbf_search_date_start-2'] . "/";
		$date_start .= $_REQUEST['new_dbf_search_date_start-3'];
		$date_end=$_REQUEST['new_dbf_search_date_end-1'] . "/";
		$date_end .= $_REQUEST['new_dbf_search_date_end-2'] . "/";
		$date_end .= $_REQUEST['new_dbf_search_date_end-3'];

		$spreadsheet_insert_text=str_replace("{=start_date}",$date_start,$spreadsheet_insert_text);
		$spreadsheet_insert_text=str_replace("{=end_date}",$date_end,$spreadsheet_insert_text);
		$sXlOut .= xlsWriteLabel(2,0,$spreadsheet_insert_text);
	}
	for ($i = 0; $i < $iCols; $i++) {
		$sXlOut .= xlsWriteLabel(4, $i, ucFirst(str_replace("_"," ",$nHeadings[$i])));
	}
	$linecounter=5;
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

public function moodgets_data_store($data,$source,$total_results,$options){
	$js="{total:$total_results,data:[ ";
	$rows = array();
	foreach ($data as $row){
		$cols=array();
		foreach ($row as $col => $value){
			$value=$db->db_escape($value);
			//$value=strip_tags($value);
			//$value=substr($value,0,50);
			array_push($cols,$col . ": '" . $value . "'");
		}
		array_push($rows,"{" . join(", ",$cols) . "}");
	}
	$js .= join(",",$rows);
	$js .= "]}";
	print $js;
}

public function data_to_moodgets_grid($data,$source,$options){
	$headings=array();
	foreach ($data as $row){
		foreach ($row as $column => $value){
			array_push($headings,$column);
		}
		break;
	}
	
	// build datastore js - no longer used as we are going dynamically...
	$js="var moodgets_dataStore = [\n";
	$rows = array();
	foreach ($data as $row){
		$cols=array();
		foreach ($row as $col => $value){
			$value=str_replace("'","\\'",$value);
			array_push($cols,$col . ": '" . $value . "'\n");
		}
		array_push($rows,"{" . join(", ",$cols) . "}");
	}
	$js .= join(",",$rows);
	$js .= "]";
	$js .= "\n";

	// build column model...
	// 1. load the source description to get field types
	global $db;
	$sql = "DESC " . $source;
	$result=$db->query($sql);
	while ($row=$db->fetch_array($result)){
		$table_description[$row['Field']]=$row['Type'];
		if ($row['Key']=="PRI"){$pk=$row['Field'];}
	}

	$js2="var moodgets_columnModel = [\n";
	$header_data=array();

	global $CONFIG;
	foreach ($headings as $heading){
		$dataType="string";
		$width=100;
		$isEditable="true";
		$defaultValue="";
		$editableFieldType="";
		$svl_options="";
		$heading_readable=ucfirst(str_replace("_"," ",$heading));

		if ($heading==$pk){
			$isEditable="false";
			$dataType="number";
		}

		// field_value_always gives a default value
		if ($options['filter'][$heading]['field_value_always']){
			$defaultValue=$options['filter'][$heading]['field_value_always'];
		}

		// select value list
		if ($options['filter'][$heading]['select_value_list']){
			if (!strlen(stristr($options['filter'][$heading]['select_value_list'],"SQL:"))){
				$svl_options=explode(",",$options['filter'][$heading]['select_value_list']);
				$editableFieldType="select";
				$svl_options="['" . join("', '",$svl_options) . "']";
			}
		} else {
				$editableFieldType="";
				$svl_options="";
		}

		// tinyint(1) for checkbox
		if ($table_description[$heading]=="tinyint(1)" && $CONFIG['use_tinyint(1)_as_bool']){
			$editableFieldType="checkbox";
			$checkbox_options="['0', '1']";
		}
		
		// date field type gives date input
		if ($table_description[$heading]=="date"){
			$editableFieldType="date";
		}

		$headerdataString = "{header: \"$heading_readable\", dataIndex: \"$heading\", dataType: \"$dataType\", width:$width, isEditable: $isEditable";
		if ($defaultValue){
			$headerdataString .= ", defaultValue: \"$defaultValue\"";
		}
		
		if ($svl_options && $editableFieldType){
			$headerdataString .= ", editableFieldType: \"$editableFieldType\", options: $svl_options";
		}

		if ($checkbox_options && $editableFieldType){
			$headerdataString .= ", editableFieldType: \"$editableFieldType\", options: $checkbox_options";
		}

		if ($editableFieldType=="date"){
			$headerdataString .= ", editableFieldType: \"$editableFieldType\"";
			$headerdataString .= ", validator: \"validate-date dateFormat:%d/%m/%Y\"";
		}

		$headerdataString .= "}";
		array_push($header_data,$headerdataString);
	}
	$js2 .= join(",",$header_data);
	$js2 .= "]\n\n";
	
	$js_template="window.addEvent('domready', function() {
    initDemo();
});

function initDemo() {		
	var g_grid = new Grid($('grid'), {
		dataStore: new RemoteDataStore({requestOptions: {url: 'mui-administrator.php?action=moodgets_data_store&pureAjax=1&mgt=".$_REQUEST['t']."'}}),
		columnModel: new ColumnModel(moodgets_columnModel), 
		gridPlugins: [
			new EditableGridPlugin({requestOptions:{url: 'mui-administrator.php?action=moodgets_save&t=$source&pureAjax=1'}}),
			new SortableGridPlugin(),
			new ResizableGridPlugin(),
			new SelectableGridPlugin({multipleSelection: false}),
			new ColumnOrderingGridPlugin(),
			new SummaryGridPlugin(),
			new FormattedGridPlugin()
		]
	});
};
";
print "<script type=\"text/javascript\" src=\"scripts/mootools-1.2.4-core-yc.js\"></script>\n";
print "<script type=\"text/javascript\" src=\"scripts/mootools-1.2.4.4-more.js\"></script>\n";
print "<script type=\"text/javascript\" src=\"scripts/moodgets_0.08/js/language/language_en.js\"></script>\n";
print "<script src=\"scripts/moodgets_0.08/js/build/moodgets.compiled.js\" type=\"text/javascript\"></script>\n";
print "<link rel=\"stylesheet\" type=\"text/css\" href=\"css/moodgets/Widgets.css\" />\n";
print "<script type=\"text/javascript\">\n";
print $js;
print $js2;
print $js_template;
print "</script>";
print "<div id=\"grid\" style=\"height:340px; width:950px\"></div>\n";
}

public function print_rows($result,$fields_to_display,$total_cols){

	global $db;
	$debug=0;
	$row1_colour="#dddddd"; $row2_colour="#e3e3e3"; $row_colour=$row1_colour; $row_over_colour="#98b1ca";
	$row1_class="tr_1_col"; $row2_class="tr_2_col"; $row_class=$row1_class;

	// get one to many relations now to save getting it every time we loop through a row...
	global $libpath;
	require_once(LIBPATH . "/classes/core/tables.php");
	$tables=new tables;
	$one_to_many_relations = $tables->one_to_many_relationship($this->value("recordset_source"));

	$pk=$this->value("pk");
	if ($this->value("on_query") && preg_match("/\w+ AS \w+/i",$pk)){$pkwords=explode(" ",$pk); $pk=array_pop($pkwords);}
	while ($row=$db->fetch_array($result)){
		if ($debug){ print "<p><font color='red'>On a row, pk is $pk</font></p>";}
		$output_text .= "<tr class=\"$row_class\" onMouseOver=\"this.className='tr_over'\" onMouseOut=\"this.className='$row_class'\" id=\"tr_id_$pk\" name=\"tr_id_$pk\">";
		foreach ($row as $row_variable => $row_value){
			if ($debug){ print "On $row_variable and $row_value<br>";}
			$total_cols_var=$row_variable;
			if (preg_match("/\( ?SELECT \w+ AS /",$total_cols_var)){
				$total_cols_var=str_replace("(","",$total_cols_var);
				$total_cols_var=str_replace("SELECT","",$total_cols_var);
				$xbits=explode("FROM",$total_cols_var);
				$xbits[0]=preg_replace("/ \w+ AS /","",$xbits[0]);
				$total_cols_var=trim($xbits[0]);
			}	
			if (array_key_exists($total_cols_var,$total_cols)){
				$total_cols[$total_cols_var] += $row_value;
			}
			$EXPORT['rows'][$row[$pk]][$row_variable]=$row_value;
			//if (strpos($fields_to_display,$row_variable)>0)
			$check_fields_to_display=",".$fields_to_display.",";
			$check_row_variable=",".$row_variable.",";
			if (preg_match("/\( ?SELECT \w+ AS /",$check_row_variable)){
				$check_row_variable=str_replace("(","",$check_row_variable);
				$check_row_variable=str_replace("SELECT","",$check_row_variable);
				$xbits=explode("FROM",$check_row_variable);
				$xbits[0]=preg_replace("/ \w+ AS /","",$xbits[0]);
				$check_row_variable=trim($xbits[0]) . ",";
			}	
			if (strpos($check_fields_to_display,$check_row_variable)>0){
				$output_text .= "<td>";
				$link_to="";
				if ($this->options['filter']['display_field_names']){ $output_text .= $row_variable . ": "; }
				if ($this->options['filter']['hyperlink_field']=="all" || $this->options['filter']['hyperlink_field']==$row_variable || $this->options['filter'][$row_variable]['hyperlink_field']){ // uses keyed by field OR not ... ! 
					$link_to="";
					if ($this->options['filter'][$row_variable]['hyperlink_url']){$link_to=$this->options['filter'][$row_variable]['hyperlink_url'];}
					if (!$link_to && $this->options['filter']['hyperlink_url']){$link_to=$this->options['filter']['hyperlink_url'];}
					$link_to = str_replace("{=id}",$row[$pk],$this->options['filter'][$row_variable]['hyperlink_url']);
					$link_to = str_replace("{=tablename}",$this->value("recordset_source"),$link_to);
					$link_to = str_replace("{=current_field_value}",$row_value,$link_to); 
					$link_to = str_replace("{=field_value}",$row_value,$link_to); 
					if ($this->options['filter'][$row_variable]['hyperlink_target']){
						$link_target_text = " target='" . $this->options['filter'][$row_variable]['hyperlink_target'] . "'";
					}
				} 

				if ($this->options['filter'][$row_variable]['supress_html_in_display']){
					$row_value = strip_tags($row_value);
				}

				if ($this->options['filter'][$row_variable]['concat_field'] && strlen($row_value)>$this->options['filter'][$row_variable]['concat_field']){
					$row_value = substr($row_value,0,$this->options['filter'][$row_variable]['concat_field']) . "...";
				}

				if ($this->options['filter'][$row_variable]['select_value_list']) {
					if (preg_match("/SQL:SELECT \w+ ?,/",$this->options['filter'][$row_variable]['select_value_list']) && strlen(strpos($this->options['filter'][$row_variable]['select_value_list'],"WHERE")) < 1){
					//  NOTE THIS LINE (above) is one instance where id is hard coded as a primary key - really we need to get hte primary key of hte table the values are from here, or do we need this at all? Look at - 9 5 2009
						if (!$link_to){
global $user;
if ($user->value("id")==1){
        //print "<p style=\"color:red\">key stuff for rv '$row_variable' is" . $this->options['filter'][$row_variable]['select_key_table_for_record_lists'] . "</p>";
}

							$EXPORT['rows'][$row[$pk]][$row_variable]=database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value,$this->options['filter'][$row_variable]['select_key_table_for_record_lists']);
							$output_text .= $EXPORT['rows'][$row[$pk]][$row_variable];
						} else {
							// NEED TO INCLUDE LINK TO HERE, but will it interfere with excel? We need to checkt he $page->excel thiny
							$EXPORT['rows'][$row[$pk]][$row_variable]=database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value,$this->options['filter'][$row_variable]['select_key_table_for_record_lists']);
							$output_text .= "<a href=\"" . $link_to . "\">" . $EXPORT['rows'][$row[$pk]][$row_variable] . "</a>\n";
						}
					} else {
						if (strlen(strpos($this->options['filter'][$row_variable]['select_value_list'],";;"))){
						$EXPORT['rows'][$row[$pk]][$row_variable]=database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value);
						$output_text .= $EXPORT['rows'][$row[$pk]][$row_variable];
						} else if (preg_match("/SQL: ?SELECT \w+ ?,/",$this->options['filter'][$row_variable]['select_value_list'])) { // again id WAS(!) hard coded here
							$row_value_bits=explode(" ",$this->options['filter'][$row_variable]['select_value_list']);
							$rebuild_svl="";
							foreach ($row_value_bits as $rv_bit){
								if (preg_match("/{=\w+}/i",$rv_bit)){
									//print "<p>YES deal with $rv_bit</p>";
									$rv_bit = str_replace("{=","",$rv_bit);
									$replace_bracket=0;
									if (preg_match("/\)$/",$rv_bit)){
										$replace_bracket=")";
										$rv_bit = str_replace(")","",$rv_bit);
									}
									$rv_bit = str_replace("}","",$rv_bit);
 									$rv_bit = $row[$rv_bit] . $replace_bracket;
								}	
							$rebuild_svl .= $rv_bit . " ";
							}
							$rebuild_svl = preg_replace("/ $/","",$rebuild_svl);
							$rebuild_svl = str_replace(") WHERE ",") AND ",$rebuild_svl);
							if ($rebuild_svl){ $this->options['filter'][$row_variable]['select_value_list']=$rebuild_svl;}
  							if ($debug){
								print "<p>Now calling database_functions::sql_value_from_id on " . $row_value . " and " . $this->options['filter'][$row_variable]['select_value_list']; }
 							$output_text .= database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value);
    						} else if (strlen(strpos($this->options['filter'][$row_variable]['select_value_list'],"CODE:"))) {
							$output_text .= database_functions::select_code_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value);
						}else {
							if ($link_to){
							$output_text .= "<a href=\"" . $link_to . "\">" . $row_value . "</a>\n";
							} else {
								$output_text .= $row_value;
								$EXPORT['rows'][$row[$pk]][$row_variable]=$row_value;
							}
						}
					}
					// The below amend was made for excel spreadsheets but its assumed this will always be required anyway...
					// NB: Shoult we include the link_to stuff? not sure if its going to an external template... but I think we prob should at some point or theres no point in having the link_to specified in this particular filter which will always go to a template eh...
				} else if ($this->options['filter'][$row_variable]['date_format']) {
					$datebits=explode("-",$row_value);
					$thistime=mktime(0,0,0,$datebits[1],$datebits[2],$datebits[0]);
					$this_date=date($this->options['filter'][$row_variable]['date_format'],$thistime);
					$output_text .= $this_date; 
					$EXPORT['rows'][$row[$pk]][$row_variable]=$this_date;
					//print "row is $row_value, date is $this_date"; 
				} else {
					if ($link_to){
						$output_text .= "<a href=\"" . $link_to . "\" $link_target_text>" . $row_value . "</a>\n";
						$EXPORT['rows'][$row[$pk]][$row_variable]= "<a href=\"" . $link_to . "\" $link_target_text>" . $row_value . "</a>";
					} else {
						$output_text .= $this->options['filter'][$row_variable]['field_prefix'] . $row_value . "\n";
						$EXPORT['rows'][$row[$pk]][$row_variable]=$row_value;
					}
				}
				$output_text .= "</td>";
			} else { if ($debug){ print "$row_variable isnt in fields to display?! of $fields_to_display"; }}
		}

		if ($this->options['filter']['dbf_imd']){
			// include multiple delete (imd)
			$output_text .= "<td><input type=\"checkbox\" name=\"del_key_".$row[$pk]."\"></td>";
		}


		$output_text .= "<td nowrap=\"nowrap\" style=\"background-color:#fff;\">";
		$output_text .= "<table><tr>";
		$output_text .= "<td>";


		if ($this->options['filter']['include_edit_link'] || $this->options['include_edit_link']){
			$edit_url = ($this->options['filter']['edit_row_link'])? str_replace("{=id}",$row[$pk],$this->options['filter']['edit_row_link']) : HTTP_PATH . "/crud/table/" . $this->value("recordset_source") . "/action/edit_record/rowid/" . $row[$pk] . "/";
//$_SERVER['PHP_SELF'] . "?action=edit_table&t=".$this->value("recordset_source")."&edit_type=edit_single&rowid=" . $row[$pk];
			$edit_url = str_replace("{=table}",$this->value("recordset_source"),$edit_url); // not tested! 
			if ($this->options['filter']['edit_record_filter']){
				$edit_url .= "&filter_id=".$this->options['filter']['edit_record_filter'];
			}
		if ($this->relation_id && $this->relation_key){ $add_relation_link = "&relation_key=".$this->relation_key."&relation_id=".$this->relation_id;}
		if ($this->options['filter']['edit_item_link']){ // overwrites the above
			$edit_url = $this->options['filter']['edit_item_link']; 
			$edit_url = str_replace("{=table}",$this->value("recordset_source"),$edit_url); // not tested! 
			$edit_url = str_replace("{=id}",$row[$pk],$edit_url); // not tested! 
		}
		$full_edit_url=$edit_url . $add_relation_link;

		global $page;
		if ($this->options['filter']['popup_window_dimensions']){
			if ($page->value("mui")){
				list($popupWidth,$popupHeight)=explode("x",$this->options['filter']['popup_window_dimensions']);
				$full_edit_url .= "&dbf_mui_ws=".trim($popupWidth)."x".trim($popupHeight);
			}
		}

		// now to print the edit buttons - if mocha remember we do it differently
		if ($row['name']){ $windowTitleField=$row['name']; } else if ($row['title']){ $windowTitleField = $row['title']; } else {$windowTitleField=" (record " . $row[$pk] . ")"; }
		$windowTitleField=str_replace("$","",$windowTitleField);
		$windowTitleField=str_replace("'","",$windowTitleField);
		
		global $page;
		if (!$page->value("mui")){
			global $CONFIG;
			if ($CONFIG['mootools_popup_size']){
				list($popupWidth,$popupHeight)=explode("x",$CONFIG['mootools_popup_size']);
			} else if (!$popupWidth && !$popupHeight) {
				$popupWidth=1040;
				$popupHeight=550;
			}
			//$output_text .= "<a href=\"" . $edit_url . "&dbf_edi=1$add_relation_link&jx=1&iframe=1\" class=\"mb\" rel=\"width:$popupWidth,height:$popupHeight\"><img src=\"".SYSIMGPATH."/application_images/edit_in_popup.png\" alt=\"Edit\" title=\"Edit in pop-up\" border=0></a>";
			if ($add_relation_link){
				if (stristr($edit_url,"?")){
					$edit_url .= "&" . $add_relation_link;
				} else {
					$edit_url .= "?" . $add_relation_link;
				}
			}
			$output_text .= "<a href=\"" . $edit_url . "\"><img src=\"".SYSIMGPATH."/application_images/button_edit_beige_29x28.png\" alt=\"Edit\" title=\"Edit\" border=0></a>";
		} else {
			$subWindowTitle="" . ucfirst(str_replace("_"," ",$this->value("recordset_source"))) . ": Edit: " . $windowTitleField;

			$output_text .= "<a href=\"" . get_link($full_edit_url,$subWindowTitle) . "\"><img src=\"".SYSIMGPATH."/application_images/button_edit_beige_29x28.png\" alt=\"Edit\" title=\"Edit\" border=0></a>";

		}
		}
		if ($this->options['filter']['include_delete_option'] || $this->options['include_delete_option']){
			if ($one_to_many_relations){$has_child_records=1;}
			$delete_url = ($this->options['filter']['delete_row_link'])? str_replace("{=id}",$row[$pk],$this->options['filter']['delete_row_link']) : "Javascript:deleterow(" . $row[$pk] . ",'$has_child_records')";
			$delete_url = str_replace("{=table}",$this->value("recordset_source"),$delete_url);
			$delete_url = str_replace("{=coded_query_string}",create_preUrl_string($_SERVER['QUERY_STRING']),$delete_url);
			$output_text .= "<a href=\"" . $delete_url . "\"><img src=\"".SYSIMGPATH."/application_images/button_trash_beige_29x28.png\" title=\"Delete\" border=0 ></a>";
		}

		//relations
		$relations_output="";
		if ($one_to_many_relations && !$this->options['filter']['hide_edit_items_link']){
			$total_rels=count($one_to_many_relations);
			if ($total_rels>22 && $this->value("recordset_source") != "filters"){ $use_select_rels=1; $relations_output.= "</td><td valign=\"middle\"><select name=\"relations_for_".$row[$pk] . "\" onChange=\"document.location=this.value\"><option value=\"\">Related Data:</option>";}
			foreach ($one_to_many_relations as $child_table){
				if ($child_table['hide_from_system_lists']){continue;}
				$default_rpp=$CONFIG['default_records_per_page'];
				$relation_url = ($this->options['filter']['relations_link_url']) ? $this->options['filter']['relations_link_url'] : HTTP_PATH . "/crud/table/" . $child_table['table_name'] . "/action/list_table/relation_id/".$child_table['relation_id']."/relation_key/".$row[$pk]; 
	//     $_SERVER['PHP_SELF'] . "?action=list_table&t=" . $child_table['table_name'] . "&relation_id=" . $child_table['relation_id'] . "&relation_key=" . $row[$pk];
				
				$full_rel_display="<img src=\"".SYSIMGPATH."/icons/button_options_beige_29x28.png\" border=0>";
				if ($child_table['system_graphic']){$rel_sys_graphic=$child_table['system_graphic'];} else {$rel_sys_graphic="button_options_beige_29x28.png";}
				if (strlen(stristr($rel_sys_graphic,"TEXT:")) || (!stristr($rel_sys_graphic,".png") && !stristr($rel_sys_graphic,".jpg"))){
					$full_rel_display="<span class=\"relation_text_link\">".trim(str_replace("TEXT:","",$rel_sys_graphic))."</span>";
				} else {
					$full_rel_display="<img src=\"".SYSIMGPATH."/icons/$rel_sys_graphic\" border=0>";
				}
				$child_table_name=ucfirst(str_replace("_"," ", $child_table['table_name']));
				if (!$use_select_rels){
					$relations_output.= "<td style=\"background-color:#fff\"><a href=\"" . get_link($relation_url,str_replace("_"," ",$child_table_name)) . "\" alt=\"" . str_replace("_"," ",$child_table_name) . "\" title=\"".str_replace("_"," ",$child_table_name) . "\">$full_rel_display</a></td>";
				} else {
					$relations_output.= "<option value=\"".get_link($relation_url,str_replace("_"," ",$child_table_name)) . "\">".format_table_name($child_table_name) . "</option>";
				}
			}
			if ($total_rels > 2){
				$relations_output.= "</select></td>";
			}
		}
		global $user;
		if ($total_rels>0 && $user->value("type")=="master"){ $output_text .= $relations_output;}
		// end relations

		if ($this->options['filter']['add_button_to_row'] && $this->options['filter']['add_button_to_row_url']){
			$this->options['filter']['add_button_to_row_url']=str_replace("{=table}",$this->value("recordset_source"),$this->options['filter']['add_button_to_row_url']);
			if ($this->options['filter']['add_button_to_row_alt']){$alt_text = $this->options['filter']['add_button_to_row_alt'];}
			$output_text .= "<td style=\"background-color:#fff\">";
			$output_text .= "<a href=\"" . preg_replace("/\{\=(.*?)\}/e",'$row["${1}"]',get_link($this->options['filter']['add_button_to_row_url'],"$alt_text")) . "\" title=\"$alt_text\" alt=\"$alt_text\"><img src=\"" . $this->options['filter']['add_button_to_row'] . "\" border=0 /></a>";
			$output_text .= "</td>";
		}
		if ($this->options['filter']['add_text_button_to_row'] && $this->options['filter']['add_button_to_row_url']){
			$this->options['filter']['add_button_to_row_url']=str_replace("{=table}",$this->value("recordset_source"),$this->options['filter']['add_button_to_row_url']);
			$add_buttons_array=explode(",",$this->options['filter']['add_text_button_to_row']);
			if (stristr($this->options['filter']['add_button_to_row_url'],"Javascript")){
				$add_urls_array=explode(",,",$this->options['filter']['add_button_to_row_url']);
			} else {
				$add_urls_array=explode(",",$this->options['filter']['add_button_to_row_url']);
			}
			if ($this->options['filter']['add_button_to_row_target']){
				$add_buttons_targets=explode(",",$this->options['filter']['add_button_to_row_target']);
			}
			if ($this->options['filter']['add_text_button_to_row_class']){
				$add_buttons_classes=explode(",",$this->options['filter']['add_text_button_to_row_class']);
			}
			if ($this->options['filter']['add_text_button_to_row_rel']){
				$add_buttons_rels=explode("|",$this->options['filter']['add_text_button_to_row_rel']);
			}
		
			$i=0;
			$button_options=$row;
			$button_options['dbf_search_date_start_full']=$this->value("start_dbf_search_date");
			$button_options['dbf_search_date_end_full']=$this->value("end_dbf_search_date");
			foreach ($add_buttons_array as $add_button){
				$output_text .= "<td style=\"background-color:#fff\">";
				$text_span="<span class=\"extra_button_text_link\">";
				$text_span_close="</span>";
				if (preg_match("/.png$/",$add_button)){
					$output_text .= "<a href=\"" . get_link(preg_replace("/\{\=(.*?)\}/e",'$button_options["${1}"]',$add_urls_array[$i]),'Dynamic Window') . "\" class=\"".$add_buttons_classes[$i]."\"";
					$add_button="<img src=\"$add_button\" border=\"0\" />";
					$text_span="";
					$text_span_close="";
				} else { 
					if ($add_buttons_targets[$i]){
						$output_text .= "<a href=\"" . preg_replace("/\{\=(.*?)\}/e",'$button_options["${1}"]',$add_urls_array[$i]) . "\" class=\"".$add_buttons_classes[$i]."\"";
					} else {
						$output_text .= "<a href=\"" . get_link(preg_replace("/\{\=(.*?)\}/e",'$button_options["${1}"]',$add_urls_array[$i]),$add_button) . "\" class=\"".$add_buttons_classes[$i]."\"";
					}
					//$output_text .= "<a href=\"" . get_link(preg_replace("/\{\=(.*?)\}/e",'$button_options["${1}"]',$add_urls_array[$i]),$add_button) . "\" class=\"".$add_buttons_classes[$i]."\"";
				}

				if ($add_buttons_targets){
					$output_text .= " target=\"".$add_buttons_targets[$i]."\"";
				}
				if ($add_buttons_rels){
					$output_text .= " rel=\"".$add_buttons_rels[$i]."\"";
				}
				$output_text .= ">$text_span" . $add_button . "$text_span_close</a></td>";
			$i++;
			}
		}

		$output_text .= "</td></tr></table></td></tr>\n";
		if ($row_colour==$row1_colour){$row_colour=$row2_colour;}else{$row_colour=$row1_colour;}
		if ($row_class==$row1_class){$row_class=$row2_class;}else{$row_class=$row1_class;}
	}
	$return_vars['output_text']=$output_text;
	$return_vars['EXPORT']=$EXPORT;
	$return_vars['total_cols']=$total_cols;
	//print_debug("<hr>");
	//print_debug($EXPORT);
	return $return_vars;
}

public function print_rows_from_custom_search($results,$fields_to_display,$total_cols){

	$debug=0;
	$row1_colour="#dddddd"; $row2_colour="#e3e3e3"; $row_colour=$row1_colour; $row_over_colour="#98b1ca";
	$row1_class="tr_1_col"; $row2_class="tr_2_col"; $row_class=$row1_class;

	// get one to many relations now to save getting it every time we loop through a row...
	global $libpath;
	require_once("$libpath/classes/core/tables.php");
	$tables=new tables;
	$one_to_many_relations = $tables->one_to_many_relationship($this->value("recordset_source"));
	$debug=0;
	$pk=$this->value("pk");
	if ($this->value("on_query") && preg_match("/\w+ AS \w+/i",$pk)){$pkwords=explode(" ",$pk); $pk=array_pop($pkwords);}
	foreach ($results as $row){
		if ($debug){ print "<p><font color='red'>On a row, pk is $pk</font></p>";}
		$output_text .= "<tr class=\"$row_class\" onMouseOver=\"this.className='tr_over'\" onMouseOut=\"this.className='$row_class'\" id=\"tr_id_$pk\" name=\"tr_id_$pk\">";
		foreach ($row as $row_variable => $row_value){
			if ($debug){ print "On $row_variable and $row_value<br>";}
			$total_cols_var=$row_variable;
			if (preg_match("/\( ?SELECT \w+ AS /",$total_cols_var)){
				$total_cols_var=str_replace("(","",$total_cols_var);
				$total_cols_var=str_replace("SELECT","",$total_cols_var);
				$xbits=explode("FROM",$total_cols_var);
				$xbits[0]=preg_replace("/ \w+ AS /","",$xbits[0]);
				$total_cols_var=trim($xbits[0]);
			}	
			if (array_key_exists($total_cols_var,$total_cols)){
				$total_cols[$total_cols_var] += $row_value;
			}
			$EXPORT['rows'][$row[$pk]][$row_variable]=$row_value;
			//print "<hr size=2>";
			//var_dump($EXPORT['rows']);
			
			//if (strpos($fields_to_display,$row_variable)>0)
			$check_fields_to_display=",".$fields_to_display.",";
			$check_row_variable=",".$row_variable.",";
			if (preg_match("/\( ?SELECT \w+ AS /",$check_row_variable)){
				$check_row_variable=str_replace("(","",$check_row_variable);
				$check_row_variable=str_replace("SELECT","",$check_row_variable);
				$xbits=explode("FROM",$check_row_variable);
				$xbits[0]=preg_replace("/ \w+ AS /","",$xbits[0]);
				$check_row_variable=trim($xbits[0]) . ",";
			}	
			if (strpos($check_fields_to_display,$check_row_variable)>0){
				$output_text .= "<td>";
				$link_to="";
				if ($this->options['filter']['display_field_names']){ $output_text .= $row_variable . ": "; }
				if ($this->options['filter']['hyperlink_field']=="all" || $this->options['filter']['hyperlink_field']==$row_variable || $this->options['filter'][$row_variable]['hyperlink_field']){ // uses keyed by field OR not ... ! 
					$link_to="";
					if ($this->options['filter'][$row_variable]['hyperlink_url']){$link_to=$this->options['filter'][$row_variable]['hyperlink_url'];}
					if (!$link_to && $this->options['filter']['hyperlink_url']){$link_to=$this->options['filter']['hyperlink_url'];}
					$link_to = str_replace("{=id}",$row[$pk],$this->options['filter'][$row_variable]['hyperlink_url']);
					$link_to = str_replace("{=tablename}",$this->value("recordset_source"),$link_to);
					$link_to = str_replace("{=current_field_value}",$row_value,$link_to); 
					$link_to = str_replace("{=field_value}",$row_value,$link_to); 
					if ($this->options['filter'][$row_variable]['hyperlink_target']){
						$link_target_text = " target='" . $this->options['filter'][$row_variable]['hyperlink_target'] . "'";
					}
				} 

				if ($this->options['filter'][$row_variable]['supress_html_in_display']){
					$row_value = strip_tags($row_value);
				}

				if ($this->options['filter'][$row_variable]['concat_field'] && strlen($row_value)>$this->options['filter'][$row_variable]['concat_field']){
					$row_value = substr($row_value,0,$this->options['filter'][$row_variable]['concat_field']) . "...";
				}

				if ($this->options['filter'][$row_variable]['select_value_list']) {
					if (preg_match("/SQL:SELECT \w+ ?,/",$this->options['filter'][$row_variable]['select_value_list']) && strlen(strpos($this->options['filter'][$row_variable]['select_value_list'],"WHERE")) < 1){
					//  NOTE THIS LINE (above) is one instance where id is hard coded as a primary key - really we need to get hte primary key of hte table the values are from here, or do we need this at all? Look at - 9 5 2009
						if (!$link_to){
global $user;
if ($user->value("id")==1){
        //print "<p style=\"color:red\">key stuff for rv '$row_variable' is" . $this->options['filter'][$row_variable]['select_key_table_for_record_lists'] . "</p>";
}

							$EXPORT['rows'][$row[$pk]][$row_variable]=database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value,$this->options['filter'][$row_variable]['select_key_table_for_record_lists']);
							$output_text .= $EXPORT['rows'][$row[$pk]][$row_variable];
						} else {
							// NEED TO INCLUDE LINK TO HERE, but will it interfere with excel? We need to checkt he $page->excel thiny
							$EXPORT['rows'][$row[$pk]][$row_variable]=database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value,$this->options['filter'][$row_variable]['select_key_table_for_record_lists']);
							$output_text .= "<a href=\"" . $link_to . "\">" . $EXPORT['rows'][$row[$pk]][$row_variable] . "</a>\n";
						}
					} else {
						if (strlen(strpos($this->options['filter'][$row_variable]['select_value_list'],";;"))){
						$EXPORT['rows'][$row[$pk]][$row_variable]=database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value);
						$output_text .= $EXPORT['rows'][$row[$pk]][$row_variable];
						} else if (preg_match("/SQL: ?SELECT \w+ ?,/",$this->options['filter'][$row_variable]['select_value_list'])) { // again id WAS(!) hard coded here
							$row_value_bits=explode(" ",$this->options['filter'][$row_variable]['select_value_list']);
							$rebuild_svl="";
							foreach ($row_value_bits as $rv_bit){
								if (preg_match("/{=\w+}/i",$rv_bit)){
									//print "<p>YES deal with $rv_bit</p>";
									$rv_bit = str_replace("{=","",$rv_bit);
									$replace_bracket=0;
									if (preg_match("/\)$/",$rv_bit)){
										$replace_bracket=")";
										$rv_bit = str_replace(")","",$rv_bit);
									}
									$rv_bit = str_replace("}","",$rv_bit);
 									$rv_bit = $row[$rv_bit] . $replace_bracket;
								}	
							$rebuild_svl .= $rv_bit . " ";
							}
							$rebuild_svl = preg_replace("/ $/","",$rebuild_svl);
							$rebuild_svl = str_replace(") WHERE ",") AND ",$rebuild_svl);
							if ($rebuild_svl){ $this->options['filter'][$row_variable]['select_value_list']=$rebuild_svl;}
  							if ($debug){
								print "<p>Now calling database_functions::sql_value_from_id on " . $row_value . " and " . $this->options['filter'][$row_variable]['select_value_list']; }
 							$output_text .= database_functions::sql_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value);
    						} else if (strlen(strpos($this->options['filter'][$row_variable]['select_value_list'],"CODE:"))) {
							$output_text .= database_functions::select_code_value_from_id($this->options['filter'][$row_variable]['select_value_list'],$row_value);
						}else {
							if ($link_to){
							$output_text .= "<a href=\"" . $link_to . "\">" . $row_value . "</a>\n";
							} else {
								$output_text .= $row_value;
								$EXPORT['rows'][$row[$pk]][$row_variable]=$row_value;
							}
						}
					}
					// The below amend was made for excel spreadsheets but its assumed this will always be required anyway...
					// NB: Shoult we include the link_to stuff? not sure if its going to an external template... but I think we prob should at some point or theres no point in having the link_to specified in this particular filter which will always go to a template eh...
				} else if ($this->options['filter'][$row_variable]['date_format']) {
					$datebits=explode("-",$row_value);
					$thistime=mktime(0,0,0,$datebits[1],$datebits[2],$datebits[0]);
					$this_date=date($this->options['filter'][$row_variable]['date_format'],$thistime);
					$output_text .= $this_date; 
					$EXPORT['rows'][$row[$pk]][$row_variable]=$this_date;
					//print "row is $row_value, date is $this_date"; 
				} else {
					if ($link_to){
						$output_text .= "<a href=\"" . $link_to . "\" $link_target_text>" . $row_value . "</a>\n";
						$EXPORT['rows'][$row[$pk]][$row_variable]= "<a href=\"" . $link_to . "\" $link_target_text>" . $row_value . "</a>";
					} else {
						$output_text .= $this->options['filter'][$row_variable]['field_prefix'] . $row_value . "\n";
						$EXPORT['rows'][$row[$pk]][$row_variable]=$row_value;
					}
				}
				$output_text .= "</td>";
			} else { if ($debug){ print "$row_variable isnt in fields to display?! of $fields_to_display"; }}
		}

		if ($this->options['filter']['dbf_imd']){
			// include multiple delete (imd)
			$output_text .= "<td><input type=\"checkbox\" name=\"del_key_".$row[$pk]."\"></td>";
		}

		if ($this->options['filter']['include_edit_link'] || $this->options['include_edit_link']){
			$edit_url = ($this->options['filter']['edit_row_link'])? str_replace("{=id}",$row[$pk],$this->options['filter']['edit_row_link']) :  $_SERVER['PHP_SELF'] . "?action=edit_table&t=".$this->value("recordset_source")."&edit_type=edit_single&rowid=" . $row[$pk] . "&dbf_edi=1";
			$edit_url = str_replace("{=table}",$this->value("recordset_source"),$edit_url); // not tested! 
			if ($this->options['filter']['edit_record_filter']){
				$edit_url .= "&filter_id=".$this->options['filter']['edit_record_filter'];
			}
		if ($this->relation_id && $this->relation_key){ $add_relation_link = "&relation_key=".$this->relation_key."&relation_id=".$this->relation_id;}
		if ($this->options['filter']['edit_item_link']){ // overwrites the above
			$edit_url = $this->options['filter']['edit_item_link']; 
			$edit_url = str_replace("{=table}",$this->value("recordset_source"),$edit_url); // not tested! 
			$edit_url = str_replace("{=id}",$row[$pk],$edit_url); // not tested! 
		}
		$full_edit_url=$edit_url . "&dbf_edi=1$add_relation_link";

		// now to print the edit buttons - if mocha remember we do it differently
		if ($row['name']){ $windowTitleField=$row['name']; } else if ($row['title']){ $windowTitleField = $row['title']; } else {$windowTitleField=" (record " . $row[$pk] . ")"; }
		$windowTitleField=str_replace("$","",$windowTitleField);
		$windowTitleField=str_replace("'","",$windowTitleField);
		
		$subWindowTitle="" . ucfirst(str_replace("_"," ",$this->value("recordset_source"))) . ": Edit: " . $windowTitleField;
		$output_text .= "<td><a href=\"" . get_link($full_edit_url,$subWindowTitle) . "\"><img src=\"".SYSIMGPATH."/application_images/button_edit_beige_29x28.png\" alt=\"Edit\" title=\"Edit\" border=0></a></td>";
		global $page;
		if (!$page->value("mui")){
			$output_text .= "<td><a href=\"" . $edit_url . "&dbf_edi=1$add_relation_link&jx=1&iframe=1\" class=\"mb\" rel=\"width:1040,height:550\"><img src=\"".SYSIMGPATH."/application_images/edit_in_popup.png\" alt=\"Edit\" title=\"Edit in pop-up\" border=0></a></td>";
		}
		}
		if ($this->options['filter']['include_delete_option'] || $this->options['include_delete_option']){
			if ($one_to_many_relations){$has_child_records=1;}
			$delete_url = ($this->options['filter']['delete_row_link'])? str_replace("{=id}",$row[$pk],$this->options['filter']['delete_row_link']) : "Javascript:deleterow(" . $row[$pk] . ",'$has_child_records')";
			$delete_url = str_replace("{=table}",$this->value("recordset_source"),$delete_url);
			$delete_url = str_replace("{=coded_query_string}",create_preUrl_string($_SERVER['QUERY_STRING']),$delete_url);
			$output_text .= "<td><a href=\"" . $delete_url . "\"><img src=\"".SYSIMGPATH."/application_images/button_trash_beige_29x28.png\" title=\"Delete\" border=0 ></a></td>";
		}
		if ($one_to_many_relations && !$this->options['filter']['hide_edit_items_link']){
			foreach ($one_to_many_relations as $child_table){
				if ($child_table['hide_from_system_lists']){continue;}
				$default_rpp=$CONFIG['default_records_per_page'];
				$relation_url = ($this->options['filter']['relations_link_url']) ? $this->options['filter']['relations_link_url'] : $_SERVER['PHP_SELF'] . "?action=list_table&t=" . $child_table['table_name'] . "&relation_id=" . $child_table['relation_id'] . "&relation_key=" . $row[$pk];
				
				$full_rel_display="<img src=\"".SYSIMGPATH."/icons/button_options_beige_29x28.png\" border=0>";
				if ($child_table['system_graphic']){$rel_sys_graphic=$child_table['system_graphic'];} else {$rel_sys_graphic="button_options_beige_29x28.png";}
				if (strlen(stristr($rel_sys_graphic,"TEXT:"))){
					$full_rel_display="<span class=\"relation_text_link\">".str_replace("TEXT:","",$rel_sys_graphic)."</span>";
				} else {
					$full_rel_display="<img src=\"".SYSIMGPATH."/icons/$rel_sys_graphic\" border=0>";
				}
				$child_table_name=ucfirst(str_replace("_"," ", $child_table['table_name']));
				$output_text .= "<td><a href=\"" . get_link($relation_url,str_replace("_"," ",$child_table_name)) . "\" alt=\"" . str_replace("_"," ",$child_table_name) . "\" title=\"".str_replace("_"," ",$child_table_name) . "\">$full_rel_display</a></td>";
			}
		}

		if ($this->options['filter']['add_button_to_row'] && $this->options['filter']['add_button_to_row_url']){
			$this->options['filter']['add_button_to_row_url']=str_replace("{=table}",$this->value("recordset_source"),$this->options['filter']['add_button_to_row_url']);
			if ($this->options['filter']['add_button_to_row_alt']){$alt_text = $this->options['filter']['add_button_to_row_alt'];}
			$output_text .= "<td>";
			$output_text .= "<a href=\"" . preg_replace("/\{\=(.*?)\}/e",'$row["${1}"]',get_link($this->options['filter']['add_button_to_row_url'],"$alt_text")) . "\" title=\"$alt_text\" alt=\"$alt_text\"><img src=\"" . $this->options['filter']['add_button_to_row'] . "\" border=0 /></a>";
			$output_text .= "</td>";
		}
		if ($this->options['filter']['add_text_button_to_row'] && $this->options['filter']['add_button_to_row_url']){
			$this->options['filter']['add_button_to_row_url']=str_replace("{=table}",$this->value("recordset_source"),$this->options['filter']['add_button_to_row_url']);
			$add_buttons_array=explode(",",$this->options['filter']['add_text_button_to_row']);
			$add_urls_array=explode(",",$this->options['filter']['add_button_to_row_url']);
			if ($this->options['filter']['add_button_to_row_target']){
				$add_buttons_targets=explode(",",$this->options['filter']['add_button_to_row_target']);
			}
			if ($this->options['filter']['add_text_button_to_row_class']){
				$add_buttons_classes=explode(",",$this->options['filter']['add_text_button_to_row_class']);
			}
			if ($this->options['filter']['add_text_button_to_row_rel']){
				$add_buttons_rels=explode("|",$this->options['filter']['add_text_button_to_row_rel']);
			}
		
			$i=0;
			$button_options=$row;
			$button_options['dbf_search_date_start_full']=$this->value("start_dbf_search_date");
			$button_options['dbf_search_date_end_full']=$this->value("end_dbf_search_date");
			foreach ($add_buttons_array as $add_button){
				$output_text .= "<td>";
				$text_span="<span class=\"extra_button_text_link\">";
				$text_span_close="</span>";
				if (preg_match("/.png$/",$add_button)){
					$output_text .= "<a href=\"" . get_link(preg_replace("/\{\=(.*?)\}/e",'$button_options["${1}"]',$add_urls_array[$i]),'Dynamic Window') . "\" class=\"".$add_buttons_classes[$i]."\"";
					$add_button="<img src=\"$add_button\" border=\"0\" />";
					$text_span="";
					$text_span_close="";
				} else { 
					$output_text .= "<a href=\"" . get_link(preg_replace("/\{\=(.*?)\}/e",'$button_options["${1}"]',$add_urls_array[$i]),$add_button) . "\" class=\"".$add_buttons_classes[$i]."\"";
				}

				if ($add_buttons_targets){
					$output_text .= " target=\"".$add_buttons_targets[$i]."\"";
				}
				if ($add_buttons_rels){
					$output_text .= " rel=\"".$add_buttons_rels[$i]."\"";
				}
				$output_text .= ">$text_span" . $add_button . "$text_span_close</a></td>";
			$i++;
			}
		}

		$output_text .= "</tr>\n";
		if ($row_colour==$row1_colour){$row_colour=$row2_colour;}else{$row_colour=$row1_colour;}
		if ($row_class==$row1_class){$row_class=$row2_class;}else{$row_class=$row1_class;}
	}
	$return_vars['output_text']=$output_text;
	$return_vars['EXPORT']=$EXPORT;
	$return_vars['total_cols']=$total_cols;
	//print_debug("<hr>");
	//print_debug($EXPORT);
	return $return_vars;
}

public function build_search_criterea_for_multiple_words($each_search_field){
	$words_array=explode(" ", $this->options['dbf_search_for']);
	$search_words_array=array();
	foreach ($words_array as $each_search_word){
		array_push($search_words_array,$this->value("recordset_source") . "." . $each_search_field . " LIKE \"%" . $each_search_word . "%\"");
	}
	if ($this->options['filter']['search_method']=="any_word"){
		$join_word=" OR ";
	} else {
		$join_word=" AND ";
	}
	$search_for_field_string=join($join_word,$search_words_array);
	return $search_for_field_string;
}

public function load_db_field_types($rec_source){

	global $db;
	$db_struct=array();
	if (!$this->value("on_query")){
	if (!stristr($rec_source,"QUERY:")){
		$sql="DESC $rec_source";
		$rv=$db->query($sql);
		while ($h=$db->fetch_array($rv)){
			$db_struct[$h['Field']]['Type']=$h['Type'];
			if (stristr($h['Type'],"tinyint(1)")){
				$db_struct[$h['Field']]['SimpleType']="boolean";
			} else if (stristr($h['Type'],"int") || stristr($h['Type'],"decimal")){
				$db_struct[$h['Field']]['SimpleType']="numeric";
			} else if (stristr($h['Type'],"date") || stristr($h['type'],"time")){
				$db_struct[$h['Field']]['SimpleType']="datetime";
			} else {
				$db_struct[$h['Field']]['SimpleType']="textbased";
			}
		}
	}
	}
	return $db_struct;
}

private function check_field_type_for_search($fieldname,$search_for,$possible_values){
	$return=1;
	if ($this->db_field_types[$fieldname]['SimpleType']=="boolean"){
		if ($possible_values){
		} else if ($search_for != "0" && $search_for != "1"){
			$return=0;
		} 
	} else if ($this->db_field_types[$fieldname]['SimpleType']=="numeric"){
		if ($possible_values){
			$return=1;
		} else {
			if (!is_numeric($search_for)){
				$return=0;
			}
		}
	}
	//print_debug("returning $return for $fieldname");
	return $return;
}

function load_select_values_from_table_relations(){

	global $db;
	$sql="SELECT * FROM table_relations WHERE table_2 = \"".$this->value("recordset_source") . "\"";	
	$rv=$db->query($sql);
	while ($h=$db->fetch_array()){
		$usefield="";	
		if (!$this->options['filter'][$h['field_in_table_2']]['select_value_list']){
			if ($h['master_table_name_field']){
				$svl="SELECT id,".$h['master_table_name_field']." FROM " . $h['table_1'];
			} else {
				$all_fields_in_table=list_fields_in_table($h['table_1']);
				foreach ($all_fields_in_table as $tfield){
					if ($tfield=="name"){ $usefield="name";} else if ($tfield=="title" && !$usefield){ $usefield="title";}
				}
				if ($usefield){
					$svl="SELECT id,".$usefield." FROM " . $h['table_1'];
				}
			}
		}
		if ($svl && !$this->options['filter'][$h['field_in_table_2']]['select_value_list']){
			$this->options['filter'][$h['field_in_table_2']]['select_value_list']="SQL:".$svl;	
		}
	}
}

// end class
}
?>
