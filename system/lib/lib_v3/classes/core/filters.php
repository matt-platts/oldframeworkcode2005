<?php

/* CLASS: filter
 * Meta: Filters are used to filter data both coming in to and out of a database, 
 *       along with specifying display information in forms, records and record sets.
 *       Filters work hierarchially - a base filter on a table may have child filters
 *       which inherit the parent properties.
*/
class filter{

	public function __construct($filter_id=null){
		if ($filter_id){
			$this->filter_options = $this->load_filter($filter_id);
		}
	}


        public function value($of){
                return $this->$of;
        }

        public function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }

	public function all_filter_keys(){
		return $this->filter_options;
	}

	public function value_for_key($filter_key){
		return $this->filter_options[$filter_key];
	}

	/* 
	 * Function: load_filter 
	 * Meta: Load the keys from a filter into $filter_options
	 *       options specific to a field will be in $filter_options[$fieldname]['option_name']
	 *       options not specific to a field will be in $filter_options['option_name']
	*/
	public function load_filter($filter_id,$existing_filter=array()){
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
			$filter_options=$this->load_filter($parent_filter_id,$filter_options);
		}

		$sql="SELECT * from filter_keys WHERE filter_id = " . $filter_id;
		$result=$db->query($sql);
		if ($result){
			global $user;
			while ($row=$db->fetch_array($result)){
				$row['value']=str_replace("{=current_user}",$user->value("id"),$row['value']);
				if ($row['field'] && database_functions::load_filter_key_on_usertype($user->value("type"),$row['user_type'])){
					$filter_options[$row['field']][$row['name']]=$row['value'];
				} else if (database_functions::load_filter_key_on_usertype($user->value("type"),$row['user_type'])){
					$filter_options[$row['name']]=$this->check_immediate_request_vars($row['value']);
				}
			}
		}
		$filter_options['filter_id']=$filter_id;
		$filter_options['dbf_filter_id']=$filter_id;
		$this->filter_options=$filter_options;
		return $filter_options;
	}

	public function check_immediate_request_vars($val){
		$req_matches=preg_match_all("/{=REQ:.*?}/",$val,$matches);
		if ($matches[0]){
			foreach ($matches[0] as $orig_match){
				$match_key=$orig_match;
				$match_key=str_replace("{=REQ:","",$match_key);
				$match_key=str_replace("}","",$match_key);
				if ($_REQUEST[$match_key]){
					$match_key=$_REQUEST[$match_key];
				}
				$val=str_replace($orig_match,$match_key,$val);
			}
		}
		return $val;
	}

	/* 
	 * Function : load_options_defaults
	 * Meta: Load the default options which are specified in the configuration as follows:
	 * table_list_defaults - default options for applying to table listings
	 * query_list_defaults - default options for applying to queries - if not specified the table_list_defaults will be used
	*/
	public function load_options_defaults($optional_tablename){
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
			if ($dvp[0]=="dbf_rpp"){$dbforms_options['filter']['rpp']=$dvp[1];} // is this used!
			if ($dvp[0]=="dbf_rpp"){$dbforms_options['filter']['dbf_rpp']=$dvp[1];}
		}

		// can we get any select lists populated via table relations?
		if ($optional_tablename){
			
			global $db;
			$sql="SELECT * FROM table_relations WHERE table_2 = \"".$optional_tablename . "\"";	
			$rv=$db->query($sql);
			while ($h=$db->fetch_array($rv)){
				$usefield="";
				if (!$dbforms_options['filter'][$h['field_in_table_2']]['select_value_list']){
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

				if ($svl && !$dbforms_options['filter'][$h['field_in_table_2']]['select_value_list']){
					$dbforms_options['filter'][$h['field_in_table_2']]['select_value_list']="SQL:".$svl;
					$dbforms_options['filter'][$h['field_in_table_2']]['field_config']="select_list";
					$dbforms_options['filter'][$h['field_in_table_2']]['field_type']="select";
				}
			}
		}
		return $dbforms_options;
	}

	/* Function: load_options
	 * Meta: Loads the default options followed by $_REQUEST options followed by filter options
	 *       table name and action are optional as request variables are all that is required to make this work
	*/
	public function load_options($optional_tablename=null,$optional_action=null,$optional_filter=null){
		// lets start afresh... 
		$dbforms_options="";
		global $CONFIG;
		global $_REQUEST_SAFE;
		global $_POST_SAFE;
		global $_GET_SAFE;
		global $db;

		// 1 - load the defaults
		$dbforms_options=$this->load_options_defaults($optional_tablename);

		// 2 - load the filter next and we can overwrite it with local options if required
		if (!$_REQUEST['always_raw_data'] && !$CONFIG['always_raw_data']){
				if ($optional_tablename){ $dbf_tablename=$optional_tablename;}
				if ($_REQUEST_SAFE['t']){$dbf_tablename=$_REQUEST_SAFE['t'];}
				if ($_REQUEST_SAFE['tablename']){$dbf_tablename=$_REQUEST_SAFE['tablename'];}
				if ($optional_action){
					$form_action=$optional_action;
				} else {
					$form_action=$_REQUEST_SAFE['action'];
				}
				if ($form_action=="process_update_table"){$form_action=$optional_action;}
				$registered_filter=database_functions::filter_registered_on_table($dbf_tablename,$form_action);
				if ($registered_filter){
					$dbforms_options['filter']=$this->load_filter($registered_filter,$dbforms_options['filter']);
				}
				if ($optional_filter){ $dbforms_options['filter']=$this->load_filter($optional_filter,$dbforms_options['filter']);}
				if ($_REQUEST['filter_id']){$dbforms_options['filter']=$this->load_filter($_REQUEST['filter_id'],$dbforms_options['filter']);}
				if ($_GET['filter_id'] && !$_REQUEST['filter_id'] && preg_match("/administrator/",$_SERVER['PHP_SELF'])){$dbforms_options['filter']=$this->load_filter($_GET['filter_id'],$dbforms_options['filter']);}

		} // end always raw data

		// 3 - records per page
		if (!$dbforms_options['filter']['dbf_rpp']){ $dbforms_options['rpp']="All"; }
		if ($_REQUEST_SAFE['dbf_rpp']){ $dbforms_options['filter']['dbf_rpp']=$_REQUEST_SAFE['dbf_rpp']; }

		// 4 - Check for date requests sent through the query string
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

		// 5 - General options for edit buttons, add buttons, delete links, file upload button, multiple delete 
		if ($_REQUEST['dbf_ido']=="1"){$dbforms_options['filter']['include_delete_option']=1;} // delete by each record 
		if ($_REQUEST['dbf_imd']=="1"){$dbforms_options['filter']['dbf_imd']=1;} // multiple delete records functionality
		if (array_key_exists("dbf_edi",$_REQUEST)){$dbforms_options['filter']['include_edit_link']=$_REQUEST['dbf_edi'];} // edit record 
		if (array_key_exists("dbf_add",$_REQUEST)){$dbforms_options['filter']['include_add_link']=$_REQUEST['dbf_add'];} // add record
		if (array_key_exists("dbf_eda",$_REQUEST)){$dbforms_options['filter']['include_edit_all_link']=$_REQUEST['dbf_eda'];} // edit all
		if (array_key_exists("dbf_udl",$_REQUEST)){$dbforms_options['filter']['include_upload_data_link']=$_REQUEST['dbf_udl'];} // upload data

		// 6 - Are we including the on page select options for filtering, records per page etc? Specify these here
		if (array_key_exists("dbf_rpp_sel",$_REQUEST)){$dbforms_options['filter']['dbf_rpp_sel']=$_REQUEST['dbf_rpp_sel'];} // records per page selector 
		if ($_REQUEST['dbf_sort']>=1){$dbforms_options['filter']['dbf_sort']=$_REQUEST['dbf_sort'];} // sort by selector
		if ($_REQUEST['dbf_sort_dir']>=1){$dbforms_options['filter']['dbf_sort_dir']=$_REQUEST['dbf_sort_dir'];} // sort direction selector
		if ($_REQUEST['dbf_filter']>=1){$dbforms_options['filter']['dbf_filter']=$_REQUEST['dbf_filter'];} // filter options selector
		if ($_REQUEST['dbf_search']>=1){$dbforms_options['filter']['dbf_search']=$_REQUEST['dbf_search'];} //search text field

		// the line below was previously wrapped in the if not raw data block below, should have been an error, checking required.
		if ($_REQUEST['dbf_next']>=1){$dbforms_options['filter']['dbf_next']=$_REQUEST['dbf_next'];} 

		// 7 - POST variables
		// 7.1 look for filter keys in post - after update MUST be set from POST
		if ($_POST['dbf_after_update']){$dbforms_options['filter']['after_update'] = $_POST['dbf_after_update'];}
		if (($_POST['dbf_data_filter_value'] || strlen($_POST['dbf_data_filter_value'])) && !$_POST['clear_filtering_post']){
			// here dbf_data_filter_value is mapped onto field_equals.
			$dbforms_options['filter']['field_equals'] = $_POST['dbf_data_filter_field'] . " " . $_POST['dbf_data_filter_operator'] . " " . $_POST['dbf_data_filter_value'];
		}
		// 7.2 - passing keys as hidden fields? Load these in here
		if ($_POST['pass_keys_as_hidden_fields']){
			$hidden_field_keys=explode(",",$_POST['pass_keys_as_hidden_fields']);
			foreach ($hidden_field_keys as $hidden_field_key){
				$dbforms_options['filter'][$hidden_field_key] = $_POST[$hidden_field_key];
			}
		}

		// 8 - We are back at records per page - has to override limit of course, so this is re-included with full functionality
		if ($dbforms_options['filter']['dbf_rpp'] && $dbforms_options['filter']['dbf_rpp'] != "All"){ // this was the line that said dilfer
			 $dbforms_options['filter']['limit']=$dbforms_options['filter']['dbf_rpp'];
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
		} else {
			$dbforms_options['filter']['limit_from']="0";
		}
		// MATT ADDED THIS DECEMBER 23 2009
		if ($_REQUEST['dbf_direction']=="Reset"){
			$dbforms_options['filter']['limit_from']=0;
		}

		// 9 - Pre url string. Can I remember what preUrls are about 3 years later? Hm
		if ($_REQUEST['preUrl']){$dbforms_options['back_url']=get_preUrl_string($_REQUEST['preUrl']);}

		// 10 - seem to have a hard coded value for concatenating the field in the configuration table to 100! This has to go!
		if ($_REQUEST['t']=="configuration"){ 
			$dbforms_options['filter']['config_value']['concat_field']=100;
		}

		// 11 - If there are any values in the select_lists that we need to apply, add these to the filter as if they were done as part of it
		if ($_REQUEST['t'] || $_REQUEST['tablename'] || $optional_tablename){
			$selection_list=array();
			$tablename=$_REQUEST['t'];
			if (!$tablename){$tablename=$_REQUEST['tablename'];}
			if (!$tablename){$tablename=$optional_tablename;}
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
			global $db;
			global $CONFIG;
			if ($CONFIG['use_tinyint(1)_as_bool'] && $db->table_exists($tablename)){
				$table_sql="DESC $tablename";
				$table_rv=$db->query($table_sql);
				while ($table_h=$db->fetch_array($table_rv)){
					if ($table_h['Type']=="tinyint(1)"){
						if ($CONFIG['default_boolean_yes_icon'] && $CONFIG['default_boolean_no_icon']){
							if (!$dbforms_options['filter'][$table_h['Field']]['select_value_list']){
								$bool_svl_str="0;;<img src=\"".$CONFIG['default_boolean_no_icon']."\" border=\"0\"/>,1;;<img src=\"".$CONFIG['default_boolean_yes_icon']."\" border=\"0\" />";
								$dbforms_options['filter'][$table_h['Field']]['select_value_list']=$bool_svl_str;
							}
						}
					}
				}
			}
		}

		// 12 - Relation keys and relation ids for listing child records
		if ($_REQUEST['relation_key'] && $_REQUEST['relation_id']){
			if (!$tablename){$tablename=$_REQUEST['tablename'];}
			$child_table_key_field=database_functions::get_child_key_field_from_child_table($tablename,$_REQUEST['relation_id']);
			$master_table_key_field=database_functions::get_master_key_field_from_child_table($tablename,$_REQUEST['relation_id']);
			$dbforms_options['filter'][$child_table_key_field]['child_key_field']=1;
			$dbforms_options['filter'][$child_table_key_field]['master_key_field']=$master_table_key_field;
		}

		// 13 - We cant seem to stop messing with this can we!
		if ($_REQUEST['dbf_rpp']=="All"){
			$dbforms_options['filter']['limit_from']="0";
		}
		$this->filter_options=$dbforms_options;
		return $dbforms_options;
	}

	public function filter_wizard_front(){
		if (!$col2_open){ open_col2(); }
		print "<p class=\"admin_header\">Filter Wizard</p>";
		print "<p>Use the filter wizard to create a data filter or front end interface into your data.</p>";
		print "<p>I want to:";
		print "<ul>";
		print "<li><a href=\"".$_SERVER['PHP_SELF']."?action=create_recordset\">Create a custom recordset or template in which to view data</a></li>";
		print "<li><a href=\"".$_SERVER['PHP_SELF']."?create_form\">Create a form to add or edit data</a></li><li><a href=\"\">Create a general filter</a></li></ul>";
		print "</p>";
	}

	public function create_recordset(){ // now we have the basic add filter and the aurc of add related keys
		if (!$col2_open){ open_col2(); }
		print "<p class=\"admin_header\">Filter Wizard: Create Recordset</p>";
		print "<p>This wizard will allow you to create a type of recordset whereby records can be listed. Click below to begin:</p>";
		print "<p><a href=\"".$_SERVER['PHP_SELF']."?action=edit_table&edit_type=add_row&t=filters&filter_id=221\">Start Wizard</a>";
	}

	public function create_form(){
		if (!$col2_open){ open_col2(); }
		print "<p class=\"admin_header\">Filter Wizard: Create Form</p>";

	}
// end class fliters
}

?>
