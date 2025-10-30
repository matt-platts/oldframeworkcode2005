<?php

/* CLASS: dynamic_form
 * Meta: The dynamic form class is used for creating forms directly from database tables or queries.
 *       Default options for form elements are based on field types as well as global config variables
 *       A filter is used to be more specific about the actual form element, and specified table relations
 *       are used by default to populate select lists on lookup fields. 
 *
 * 	 Also fatures a number of custom form types, such as the 'select or upload' field, 
 *       and a complex image-picker spanning multiple directories etc.
*/
class dynamic_form extends baseController {

	function __construct($tablename,$formtype,$rowid_for_edit,$add_data,$options,$form_name=null){
		$this->tablename=$tablename;
		$this->options=$options;
		if (!$this->options){ $this->options=array(); }
		$this->formtype=$formtype;
		$this->rowid_for_edit=$rowid_for_edit;
		$this->add_data=$add_data;
		$this->fields=array();
		$this->form_name=$form_name;
	}

        public function value($of){
                return $this->$of;
        }

        public function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }

	public function get_field_data($field,$which){
		return $this->fields[$field][$which];
	}

	public function make_field($fieldname,$fieldname_with_prefix){
	//	print "made a field called $fieldname prefixed $fieldname_with_prefix";
		$this->fields[$fieldname]['name']=$fieldname_with_prefix;	
	}

	public function add_to_field($fieldname,$var,$value){
		$this->fields[$fieldname][$var]=$value;
	}

	public function add_to_field_filter($fieldname,$var,$value){
		$this->options['filter'][$fieldname][$var]=$value;
	}

	public function form_header(){

		if (!$this->value("header_html")){
			// start with the url - may change from filters, REQUEST[s] or add_string_to... 
			if (array_key_exists("form_action",$this->options['filter']) && !empty($this->options['filter']['form_action'])){
				$this->form_url=$this->options['filter']['form_action'];
			} else {
				$this->form_url=$_SERVER['PHP_SELF'] . "?action=process_update_table";
				global $base;
				if ($base->Routing['table'] && $base->Routing['action']){
					$this->form_url= HTTP_PATH . "/crud/table/".$this->value("tablename")."/action/process_update_table/";
				}
			}


			if (array_key_exists("form_onsubmit",$this->options['filter']) && !empty($this->options['filter']['form_onsubmit'])){
				$this->form_onsubmit=$this->options['filter']['form_onsubmit'];
			} else {
				$this->form_onsubmit="";
			}
			if (!empty($_REQUEST['s'])){
				$this->form_url.="&s=".$_REQUEST['s'];
			}
			if ($this->options['filter']['add_string_to_form_post_query']){$this->form_url .= "&" . Codeparser::parse_request_vars($this->options['filter']['add_string_to_form_post_query']);}
			if ($this->value("form_name")){ $form_name=$this->value("form_name"); } else { $form_name="update_table"; $this->set_value("form_name","update_table");}
			$this->header_html="<form name=\"$form_name\" id=\"$form_name\" method=\"post\" action=\"".$this->form_url."\" enctype=\"multipart/form-data\"";
			if ($this->value("form_onsubmit")){
				$this->header_html .= " onSubmit=\"" . $this->form_onsubmit . "\"";
			}
			$this->header_html .= ">\n";
			global $CONFIG;
			if ($CONFIG['use_ajax_in_admin'] && stristr($_SERVER['PHP_SELF'],"administrator.php")){
				$this->header_html .= "<div id=\"ajax_loading\" name=\"ajax_loading\" style=\"display:none\"></div>\n";
				$this->header_html .= "<script type=\"text/javascript\" src=\"scripts/ajax_form_post.js\"></script>\n";
			}
		
			$this->header_html .= "<input type=\"hidden\" name=\"tablename\" value=\"" . $this->tablename . "\">\n";
			$this->header_html .= "<input type=\"hidden\" name=\"edit_type\" value=\"" . $this->formtype. "\">\n";
			$this->header_html .= "<input type=\"hidden\" name=\"rowid_for_edit\" value=\"" . $this->rowid_for_edit. "\">\n";
			if (!empty($this->sys_form_id)){
				$this->header_html .= "<input type=\"hidden\" name=\"dbf_sys_form_id\" value=\"" . $this->sys_form_id . "\">\n";
			}
			$this->header_html .= "<input type=\"hidden\" name=\"add_data\" value=\"" . $this->add_data. "\">\n";

			$filter_id_added=0;
			if (!is_array($this->options['filter']['filter_id'])){
				$this->header_html .= "<input type=\"hidden\" name=\"filter_id\" value=\"" . $this->options['filter']['filter_id'] . "\">\n";
				$filter_id_added=1;
			} else {
				// PANIC
				$this->header_html .= "<input type=\"hidden\" name=\"filter_id\" value=\"" . $this->options['filter']['dbf_filter_id'] . "\">\n";
			}
			if (!empty($_REQUEST['dbf_ido'])){
				$this->header_html .= "<input type=\"hidden\" name=\"dbf_ido\" value=\"" . $_REQUEST['dbf_ido'] . "\">\n";
			}

			if (!empty($_REQUEST['filter_id']) && !$filter_id_added){
				$this->header_html .= "<input type=\"hidden\" name=\"filter_id\" value=\"" . $_REQUEST['filter_id'] . "\">\n";
			}

			if (!empty($this->options['filter']['pass_keys_as_hidden_fields'])){
				$hidden_field_keys=explode(",",$this->options['filter']['pass_keys_as_hidden_fields']);
				$this->header_html .= "<input type=\"hidden\" name=\"pass_keys_as_hidden_fields\" value=\"" . $this->options['filter']['pass_keys_as_hidden_fields'] . "\">\n";
				foreach ($hidden_field_keys as $hidden_field_key){
					$this->header_html .= "<input type=\"hidden\" name=\"$hidden_field_key\" value=\"" . $this->options['filter'][$hidden_field_key] . "\">\n";
				}
			} else if (!empty($_POST['pass_keys_as_hidden_fields'])){
				$hidden_field_keys=explode(",",$_POST['pass_keys_as_hidden_fields']);
				$this->header_html .= "<input type=\"hidden\" name=\"pass_keys_as_hidden_fields\" value=\"" . $_POST['pass_keys_as_hidden_fields'] . "\">\n";
				foreach ($hidden_field_keys as $hidden_field_key){
					$this->header_html .= "<input type=\"hidden\" name=\"$hidden_field_key\" value=\"" . $_POST[$hidden_field_key] . "\">\n";
				}

			}

			if (!empty($this->options['filter']['pass_request_vars_as_post'])){
				$hidden_field_keys=explode(",",$this->options['filter']['pass_request_vars_as_post']);
				$this->header_html .= "<input type=\"hidden\" name=\"pass_request_vars_as_post\" value=\"" . $this->options['filter']['pass_request_vars_as_post'] . "\">\n";
				foreach ($hidden_field_keys as $hidden_field_key){
					$this->header_html .= "<input type=\"hidden\" name=\"$hidden_field_key\" value=\"" . $_REQUEST[$hidden_field_key] . "\">\n";
				}
			} else if (!empty($_POST['pass_request_vars_as_post'])){
				$hidden_field_keys=explode(",",$_POST['pass_request_vars_as_post']);
				$this->header_html .= "<input type=\"hidden\" name=\"pass_request_vars_as_post\" value=\"" . $_POST['pass_request_vars_as_post'] . "\">\n";
				foreach ($hidden_field_keys as $hidden_field_key){
					$this->header_html .= "<input type=\"hidden\" name=\"$hidden_field_key\" value=\"" . $_POST[$hidden_field_key] . "\">\n";
				}
			}
		}
		if (array_key_exists("jx",$_REQUEST) && array_key_exists("iframe",$_REQUEST) && $_REQUEST['jx'] && $_REQUEST['iframe']){
			$this->header_html .= "<input type=\"hidden\" name=\"perpetuate_ajax_iframe_mode\" value=\"1\" />";
		}
		return $this->header_html;
	}

	/*
 	 * Function: form_footer
	 * Meta: For basic consistency, return the form closing tag in a function
	*/
	public function form_footer(){
		return "</form>";
	}

	/*
 	 * Function set_js_vars_for_custom_functionality
	 * Meta: 
	*/
	public function set_js_vars_for_custom_functionality($rowid){
		if (!$rowid){
			$rowid=0;
		}
		$rowid=preg_replace("/\w+: ?/","",$rowid);
		$return = "\n\n<script language=\"Javascript\" type=\"text/javascript\">";
		$return .= "\n";
		$return .= "dbf_rowid_for_edit = $rowid;";
		$return .= "\n";
		$return .= "</script>\n\n";
		return $return;
	}

	/*
	 * Function: delete_record_form
	*/
	public function delete_record_form(){
		// Hidden Form for deleting a record - print now
		$sys_form_id=$this->dbf_system_log_delete_form_generation();
		$this->delete_form = "<form name=\"deleterow\" action=\"" . $_SERVER['PHP_SELF'] . "?action=delete_row_from_table";
		if ($_REQUEST['jx']){ $this->delete_form .= "&jx=1"; }
		if ($_REQUEST['iframe']){ $this->delete_form .= "&iframe=1"; }

		$this->delete_form .= "\" method=\"post\">";

		if ($this->options['filter']['pass_request_vars_as_post']){
			$extra_delete_form_keys=explode(",",$this->options['filter']['pass_request_vars_as_post']);
			$this->delete_form .= "<input type=\"hidden\" name=\"pass_request_vars_as_post\" value=\"" . $this->options['filter']['pass_request_vars_as_post'] . "\">\n";

			foreach ($extra_delete_form_keys as $hidden_field_key){
				$this->delete_form .= "<input type=\"hidden\" name=\"$hidden_field_key\" value=\"" . $_REQUEST[$hidden_field_key] . "\">\n";
			}
		} else if ($_POST['pass_request_vars_as_post']){
			$extra_delete_form_keys=explode(",",$_POST['pass_request_vars_as_post']);
			$this->delete_form .= "<input type=\"hidden\" name=\"pass_request_vars_as_post\" value=\"" . $_POST['pass_request_vars_as_post'] . "\">\n";
			foreach ($extra_delete_form_keys as $hidden_field_key){
				$this->delete_form .= "<input type=\"hidden\" name=\"$hidden_field_key\" value=\"" . $_POST[$hidden_field_key] . "\">\n";
			}
		}

		$this->delete_form .= "<input type=\"hidden\" name=\"carrier_field\"><input type=\"hidden\" name=\"tablename\" value=\"" . $this->tablename . "\">";
		if ($this->formtype=="edit_all"){
			$this->delete_form .= "<input type=\"hidden\" name=\"dbf_delete_return_to\" value=\"edit_all\">";
			$this->delete_form .= "<input type=\"hidden\" name=\"delete_filter_id\" value=\"".$this->options['filter']['filter_id']. "\">";
			$this->delete_form .= "<input type=\"hidden\" name=\"recordset_filter_id\" value=\"".$this->options['filter']['filter_id']. "\">";
			$this->delete_form .= "<input type=\"hidden\" name=\"filter_id\" value=\"".$this->options['filter']['filter_id']. "\">";

		}
		$this->delete_form .= "<input type=\"hidden\" name=\"dbf_sys_form_id\" value=\"".$sys_form_id."\">\n";
		$this->delete_form .= "<input type=\"hidden\" name=\"deleteID\" value=\"\"></form>\n";
	
		return $this->delete_form;
	}

	/* 
	 * Function: dbf_system_log_form_generation
	 * Meta: esssentially provides a csrf token
	*/
	public function dbf_system_log_form_generation(){

		global $db;
		global $user;
		$unique_form_id=uniqid();

		$sql="INSERT INTO sys_form_log (ip_address,uuid,gen_time,update_table,row_identifier,form_type,filter,user,user_session) values (";
		$sql .= "\"".$_SERVER['REMOTE_ADDR']."\",";
		$sql .= "\"".$unique_form_id."\",";
		$sql .= "NOW(),";
		$sql .= "\"".$this->tablename."\",";
		$sql .= "\"".$this->rowid_for_edit."\",";
		$sql .= "\"".$this->formtype."\",";
		$sql .= "\"".$this->options['filter']['dbf_filter_id']."\",";
		$sql .= "\"".$user->value('id')."\",";
		$sql .= "\"".session_id()."\"";
		$sql .= ")";
		$res=$db->query($sql) or format_error("Unable to log system form details",1);
		return $unique_form_id;
	}

	/*
	 * Function dbf_system_log_delete_form_generation
	*/
	public function dbf_system_log_delete_form_generation(){
	global $db;
	global $user;
	$unique_form_id=uniqid();
	$sql="INSERT INTO sys_form_log (ip_address,uuid,gen_time,update_table,row_identifier,form_type,filter,user,user_session) values (";
	$sql .= "\"".$_SERVER['REMOTE_ADDR']."\",";
	$sql .= "\"".$unique_form_id."\",";
	$sql .= "NOW(),";
	$sql .= "\"".$this->tablename."\",";
	$sql .= "\"\",";
	$sql .= "\"delete\",";
	$sql .= "\"".$this->options['filter']['dbf_filter_id']."\",";
	$sql .= "\"".$user->value('id')."\",";
	$sql .= "\"".session_id()."\"";
	$sql .= ")";
	$res=$db->query($sql) or format_error("Unable to log system form details",1);
	return $unique_form_id;
	}

	/*
	 * Function draw_imagepicker_with_upload_field
	*/
	public function draw_imagepicker_with_upload_field($fieldname){
		$imagepicker_part=$this->draw_dbf_image_picker_field($fieldname);
		$upload_part=$this->draw_file_input_field($fieldname);
		$this->fields[$fieldname]['html_input_field']=$imagepicker_part . " or " . $upload_part;
		return $this->fields[$fieldname]['html_input_field'];

	}

	/*
	 * Function draw_select_or_upload_field
	*/
	public function draw_select_or_upload_field($fieldname){
		$select_part=$this->draw_select_input_field($fieldname);
		$upload_part=$this->draw_file_input_field($fieldname);
		$this->fields[$fieldname]['html_input_field']=$select_part . " or " . $upload_part;
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_select_input_field($fieldname,$allow_multiple_select=null){
		$this->fields[$fieldname]['html_input_field']="";
		$select_value_list="";
		$display_unselectable=0;
		if ($this->options['filter'][$fieldname]['select_list_disabled']){$display_unselectable=1;}
		if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"CODE:"))){
			$this->options['filter'][$fieldname]['select_value_list']=database_functions::run_sql_list_code($this->options['filter'][$fieldname]['select_value_list']);
		}
		if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"DIR:"))){
			$this->options['filter'][$fieldname]['select_value_list']=$this->dir_as_csv_for_select($this->options['filter'][$fieldname]['select_value_list'],$this->options['filter'][$fieldname]['file_upload_types']);
		}
		if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"DESC "))){
			$this->options['filter'][$fieldname]['select_value_list']=trim(str_replace("SQL:","",$this->options['filter'][$fieldname]['select_value_list']));
			$this->options['filter'][$fieldname]['select_value_list']=trim(str_replace("DESC","",$this->options['filter'][$fieldname]['select_value_list']));
			//run sql
			$this->options['filter'][$fieldname]['select_value_list']=join(",",list_fields_in_table($this->options['filter'][$fieldname]['select_value_list']));
		}
		if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"SQL:"))){
			if (!strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"WHERE"))){
				if ($this->options['filter'][$fieldname]['master_key_field'] && $this->options['filter'][$fieldname]['child_key_field']){
					if (preg_match("/ORDER BY/i",$this->options['filter'][$fieldname]['select_value_list'])){ // do nothing as the order by screws things up at the moment

					} else {
					// if it already has a where, use and, otherwise use where
						if (preg_match("/WHERE/i",$this->options['filter'][$fieldname]['select_value_list'])){$op = "AND";} else {$op = "WHERE";}
						$this->options['filter'][$fieldname]['select_value_list'] .= " ".$op." " . $this->options['filter'][$fieldname]['master_key_field'] . " = " . $_REQUEST['relation_key'];
						$display_unselectable=1;
					} // end the order by do nothing bit
				} else { $display_unselectable=0;}
				$this->options['filter'][$fieldname]['select_value_list']=database_functions::get_sql_list_values($this->options['filter'][$fieldname]['select_value_list'],"",$this->options['filter'][$fieldname]['select_value_list_delimiter']);
			} else {
				// split string into words, alter {=word} with $fieldvalues[$word]
				$explode_value = explode(" ",$this->options['filter'][$fieldname]['select_value_list']);
				foreach ($explode_value as $exploded){
					if (preg_match("/\{=\w+\}/",$exploded)){
						$exploded=str_replace("{=","",$exploded);
						$exploded=str_replace("}","",$exploded);
						if (preg_match("/\)/",$exploded)){$add_close_bracket=")";} else { $add_close_bracket=""; }
						$exploded=str_replace(")","",$exploded); // in case of it being in a sub select!
						// need to add current_user and current_user_type into here too!
						if ($exploded=="current_user_hierarchial_type"){
							global $user;
							$exploded=$user->value("hierarchial_order");
						} else if ($exploded=="current_user" || $exploded=="current_user()"){
							global $user;
							$exploded=$user->value("id");
						} else {
							$exploded=$this->fields[$exploded]['value'];
						}
						$exploded .= $add_close_bracket;
						}
					$select_value_list .= $exploded;
					$select_value_list .= " ";
				}
				$select_value_list = database_functions::get_sql_list_values($select_value_list);
			}
		}
		if (!$select_value_list){$select_value_list=$this->options['filter'][$fieldname]['select_value_list'];}
		if (preg_match("/^list_directory\('(.*)'\)/",$select_value_list,$dirname)){
			$select_value_list=implode(",",get_directory_list($dirname[1]));
		}
		if (preg_match("/^table_fields\('\{?=?(\w+)\}?'\)/",$select_value_list,$dirname)){
			if (stristr($select_value_list,"}")){
			$lookup_table_name=str_replace("{=","",str_replace("}","",$dirname[1]));
			if ($this->fields[$lookup_table_name]['value']){ ## we use an if as this value may not be filled in yet - eg new table relation
				$select_value_list=implode(",",list_fields_in_table($this->fields[$lookup_table_name]['value']));
			}
			} else {
				$lookup_table_name=$dirname[1];
				$select_value_list=implode(",",list_fields_in_table($lookup_table_name));
			}
		}
		if (preg_match("/^list_tables\('(\w+)'\)/",$select_value_list,$dirname)){
			$select_value_list=implode(",",list_tables_basic($dirname[1]));
		}
		$blank_entry_at_top=1; $blank_entry_selected=1;
		if ($this->options['filter'][$fieldname]['select_list_disabled']){$display_unselectable=1;}
		if ($display_unselectable){$blank_entry_at_top=0; $blank_entry_selected=0;}
		if ($this->options['filter'][$fieldname]['select_list_no_blank_at_top']){$blank_entry_at_top=0; $blank_entry_selected=0;}
		// if this is for ADDING a record, and we have a relation key, we need to pre-select or somehow enter the value on the relation key...
		if ($this->formtype=="add_row" && $_REQUEST['relation_key'] && $_REQUEST['relation_id']){$this->fields[$fieldname]['value']=$_REQUEST['relation_key'];}

		$display_unselectable=0;
		if (!$display_unselectable){
			$this->fields[$fieldname]['html_input_field'] .= "<select name=\"".$this->fields[$fieldname]['name']."\" id=\"".$this->fields[$fieldname]['name'] . "\"";
			if ($this->options['filter'][$fieldname]['select_list_disabled']){
				$this->fields[$fieldname]['html_input_field'] .= "disabled ";
			}
			if ($this->options['filter'][$fieldname]['select_may_contain_multiples'] || $allow_multiple_select){
				$this->fields[$fieldname]['html_input_field'] .= " multiple=\"multiple\"";
			}
			if ($this->options['filter'][$fieldname]['select_onchange']){
				$this->fields[$fieldname]['html_input_field'] = '<script type="text/javascript" src="scripts/ajax.js"></script>' . "\n" . $this->fields[$fieldname]['html_input_field'];
				$this->fields[$fieldname]['html_input_field'] .= " onChange=\"";
				$rowid_for_edit_to_javascript=$this->rowid_for_edit;
				if (!$rowid_for_edit_to_javascript){$rowid_for_edit_to_javascript="0";} // stops javascript error if there is no row to edit as we're adding a record
				$temporary_var= str_replace("{=id}",$rowid_for_edit_to_javascript,$this->options['filter'][$fieldname]['select_onchange']);
				$this->fields[$fieldname]['html_input_field'] .= str_replace("{=filter}",$this->options['filter']['dbf_filter_id'],$temporary_var);
				$this->fields[$fieldname]['html_input_field'] .= "\"";
	}
			$this->fields[$fieldname]['html_input_field'] .= ">";
			
			if ($this->options['filter'][$fieldname]['select_list_default_value'] && !$this->fields[$fieldname]['value']){
				$this->fields[$fieldname]['value']=$this->options['filter'][$fieldname]['select_list_default_value'];
			}
			if (!strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"FUNCTION:"))){
				if (!$allow_multiple_select){
					$this->fields[$fieldname]['html_input_field'] .= database_functions::build_select_option_list($select_value_list,$this->fields[$fieldname]['value'],0,$blank_entry_at_top,$blank_entry_selected,$this->options['filter'][$fieldname]['select_value_list_delimiter']);
				} else {
					$this->fields[$fieldname]['html_input_field'] .= database_functions::build_multiple_select_option_list($select_value_list,$this->fields[$fieldname]['value'],0,$blank_entry_at_top,$blank_entry_selected,$this->options['filter'][$fieldname]['select_value_list_delimiter']);
				}
			}

			// FUNCTION to generate full options html
			if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"FUNCTION:"))){
				$this->fields[$fieldname]['html_input_field'].=database_functions::run_sql_list_function($this->options['filter'][$fieldname]['select_value_list'],$this->fields[$fieldname]['value']);
			}

			$this->fields[$fieldname]['html_input_field'] .= "</select>\n";
			$this->fields[$fieldname]['html_input_field'] .= $this->options['filter'][$fieldname]['field_suffix'];
		} else {
			// this code will never run as we decided to use a real disabled rather than an invisible..
			// left here in case we want invisibility on select lists again though..
			$this->fields[$fieldname]['html_input_field'] .= $this->options['filter'][$fieldname]['field_prefix'] . "<input type=\"hidden\" name=\"".$this->fields[$fieldname]['name']."\" value=\"".$this->fields[$fieldname]['value']."\">".database_functions::sql_value_from_id($this->options['filter'][$fieldname]['select_value_list'],$this->fields[$fieldname]['value']) . $this->options['filter'][$fieldname]['field_suffix'];
		}

			// FUNCTION to generate full options html
			if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"FUNCTION:"))){
				$this->fields[$fieldname]['html_input_field'].=database_functions::run_sql_list_function($this->options['filter'][$fieldname]['select_value_list'],$this->fields[$fieldname]['value']);
			}
		if ($display_unselectable){$sel_output_text .= "<b>";}
		$sel_output_text .= $this->fields[$fieldname]['html_input_field'];
		if ($display_unselectable){$sel_output_text .= "</b>"; $this->fields[$fieldname]['html_input_field']=$sel_output_text;}
		// we may need to use the lookup value instead of the raw value in a form template, so look this up here
		if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],"SQL:"))){
			global $user;
			$this->options['filter'][$fieldname]['select_value_list'] = str_replace("{=current_user_hierarchial_type}",$user->value("hierarchial_order"),$this->options['filter'][$fieldname]['select_value_list']);
			$this->fields[$fieldname]['override_value']=database_functions::sql_value_from_id($this->options['filter'][$fieldname]['select_value_list'],$this->fields[$fieldname]['value']);
		} else if (strlen(strpos($this->options['filter'][$fieldname]['select_value_list'],";;"))){
			$separates=explode(",",$this->options['filter'][$fieldname]['select_value_list']);	
			foreach ($separates as $separate){
				@list($var,$val)=explode(";;",$separate);
				if ($var==$this->fields[$fieldname]['value']){ $this->fields[$fieldname]['override_value']=$val;}
			}
		} else {
			$this->fields[$fieldname]['override_value']=$this->fields[$fieldname]['value'];
		}

		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_default_input_field($fieldname){
		if (!$this->fields[$fieldname]['value'] && strlen($this->options['filter'][$fieldname]['default_prefill_value'])){ $this->fields[$fieldname]['value']=$this->options['filter'][$fieldname]['default_prefill_value'];}
		if ($this->options['filter'][$fieldname]['field_size']){$size=$this->options['filter'][$fieldname]['field_size'];}

		if (!$this->value['tablename']!="filter_keys"){ // so filter keys is now the ONLY place where request variables do this..
			$this->fields[$fieldname]['value']=Codeparser::parse_request_vars($this->fields[$fieldname]['value']);
		}

		$prefix=$this->options['filter'][$fieldname]['field_prefix'];
		if ($prefix != "&pound;"){ $prefix=htmlentities($prefix);}
		$this->fields[$fieldname]['html_input_field'] = $prefix . "<input type=\"text\"" . $this->options['filter'][$fieldname]['readonly'] . " ";
		$this->fields[$fieldname]['html_input_field'] .= "id=\"".$this->fields[$fieldname]['name']."\" ";
		$this->fields[$fieldname]['html_input_field'] .= "name=\"".$this->fields[$fieldname]['name'] . "\" ";
		if (strlen($this->fields[$fieldname]['value'])){
			$this->fields[$fieldname]['html_input_field'] .= "value=\"" . htmlentities($this->fields[$fieldname]['value']) . "\" ";
		} else {
			$this->fields[$fieldname]['html_input_field'] .= "value=\"\" ";
		}
		if ($this->options['filter'][$fieldname]['text_onchange']){
			$this->fields[$fieldname]['html_input_field'] .= "onchange=\"" . $this->options['filter'][$fieldname]['text_onchange'] . "\" ";
		}
		if (!$size){ $size=40;}
		if ($size){
			$this->fields[$fieldname]['html_input_field'] .= "size=\"$size\"";
		}

		if ($this->options['filter'][$fieldname]['field_disabled']){
			$this->fields[$fieldname]['html_input_field'] .= " disabled = \"disabled\"";
		}

		$this->fields[$fieldname]['html_input_field'] .= ">" . $this->options['filter'][$fieldname]['field_suffix'];
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_textarea_input_field($fieldname){
	global $CONFIG;
	if (!$this->options['filter'][$fieldname]['textarea_rows']){$this->options['filter'][$fieldname]['textarea_rows'] = $CONFIG['default_textarea_rows'];}
	if (!$this->options['filter'][$fieldname]['textarea_cols']){$this->options['filter'][$fieldname]['textarea_cols'] = $CONFIG['default_textarea_cols'];}
	// the 11 or so lines below checks to see if we need to load the code editor, and does so
	global $code_editor;
	if ($code_editor){ $local_code_editor=1;}
	if (!$local_code_editor && $this->options['filter'][$fieldname]['code_editor']){
		$local_code_editor=1;
	}
	if (preg_match("/id_\d+_$fieldname/",$this->fields[$fieldname]['name']) && $local_code_editor){
	if ($debug){print "<p>we are now loading editarea as $fieldname and $this->fields[$fieldname]['name'] create an appropriate match!";}
	?>
<script language="javascript" type="text/javascript">
editAreaLoader.init({
id : "<?php echo $this->fields[$fieldname]['name'];?>"
,syntax: "css"
,start_highlight: true
,font_size: 8
,word_wrap: true
});
</script>
	<?php  }
	if (!$local_code_editor){
		if (!preg_match("/$this->tablename/",$CONFIG['no_style_editor_on_tables'])){
			$editor_class="mceEditor";
			$front=preg_match("/site.php/",$_SERVER['PHP_SELF']);
			if (!$front){$toggle_editor_text="Toggle Editor (Rich Text / Raw HTML)";}
		} else {
			$toggle_editor_text="";
		}

		if (isset($this->options['filter'][$fieldname]['style_editor']) && !$this->options['filter'][$fieldname]['style_editor']){ $editor_class="no_editor_class"; $toggle_editor_text="";}

		$this->fields[$fieldname]['html_input_field_extra'] = "<table border=0><tr><td>";

		$this->fields[$fieldname]['html_input_field_extra_2'] .= "</td></tr><tr><td align=\"right\"><div id=\"toggle_".$this->fields[$fieldname]['name']."\"><a href=\"#\" style=\"font-size:9px\" onClick=\"toggleEditor('" . $this->fields[$fieldname]['name'] . "')\">$toggle_editor_text</a></div></td></tr></table>";
	}

	if ($this->options['filter'][$fieldname]['default_prefill_value']){
		global $db;
		$this->fields[$fieldname]['value']=$this->options['filter'][$fieldname]['default_prefill_value'];
		if (stristr($this->fields[$fieldname]['value'],"SQL:")){
			$this->fields[$fieldname]['value']=str_replace("SQL:","",$this->fields[$fieldname]['value']);
			$res=$db->query($this->fields[$fieldname]['value']);
			while ($h=$db->fetch_array()){
				$this->fields[$fieldname]['value']=implode(",",$h);
			}
		}
	}

	//$this->fields[$fieldname]['value']=str_replace("£","&pound;",$this->fields[$fieldname]['value']);

	$this->fields[$fieldname]['value']=htmlentities($this->fields[$fieldname]['value']);
	$this->fields[$fieldname]['html_input_field'] = "<textarea class=\"$editor_class\" id=\"".$this->fields[$fieldname]['name']."\" name=\"".$this->fields[$fieldname]['name']. "\" rows=\"" . $this->options['filter'][$fieldname]['textarea_rows'] . "\" cols=\"".$this->options['filter'][$fieldname]['textarea_cols']."\">" . $this->fields[$fieldname]['value'] . "</textarea>";

	if (!$local_code_editor){
		$this->fields[$fieldname]['html_input_field'] = $this->fields[$fieldname]['html_input_field_extra'] . $this->fields[$fieldname]['html_input_field'] . $this->fields[$fieldname]['html_input_field_extra_2'];
	}

	return $this->fields[$fieldname]['html_input_field'];
}

	public function draw_password_input_field($fieldname){
		if ($this->options['filter'][$fieldname]['field_size']){$size=$this->options['filter'][$fieldname]['field_size'];}
		$this->fields[$fieldname]['html_input_field'] = $this->options['filter'][$fieldname]['field_prefix'] ."<input autocomplete=\"off\" type=\"password\"" . $this->fields[$fieldname]['readonly'] . " name=\"".$this->fields[$fieldname]['name'] . "\" value=\"".$this->fields[$fieldname]['value'] . "\" size=\"$size\">";
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_hidden_input_field($fieldname){

		if (!$this->fields[$fieldname]['value'] && $this->options['filter'][$fieldname]['default_prefill_value']){ $this->fields[$fieldname]['value']=$this->options['filter'][$fieldname]['default_prefill_value'];}
		if ($this->options['filter'][$fieldname]['field_value_always']){ $this->fields[$fieldname]['value']=$this->options['filter'][$fieldname]['field_value_always'];}
		$this->fields[$fieldname]['value']=Codeparser::parse_request_vars($this->fields[$fieldname]['value']);
		$this->fields[$fieldname]['html_input_field'] = "<input type=\"hidden\" name=\"".$this->fields[$fieldname]['name']."\" value=\"".$this->fields[$fieldname]['value'] . "\">";
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_checkbox_input_field($fieldname){
		//print "drawring checkbox for $fieldname with name of " . $this->fields[$fieldname]['name'] . " and value " . $this->fields[$fieldname]['value'];
		$checkbox_text = "<input type=\"checkbox\" value=\"1\" name=\"checkbox_for_".$this->fields[$fieldname]['name'] . "\" id=\"checkbox_for_".$this->fields[$fieldname]['name'] . "\"";
		if ($this->fields[$fieldname]['value']){ $checkbox_text .= " checked";}
		$form_name="update_table";
		if ($this->options['filter']['form_name']){
			$form_name=$this->options['filter']['form_name'];
		}
		$checkbox_onclick_code="document.forms['".$form_name."'].elements['".$this->fields[$fieldname]['name']."'].value=this.checked ? 1:0; ";
		if ($this->options['filter'][$fieldname]['form_element_onclick']){
			$checkbox_onclick_code .= $this->options['filter'][$fieldname]['form_element_onclick'];
		}
		$checkbox_text .= " onclick=\"$checkbox_onclick_code\">\n";
		$checkbox_text .= "<input type=\"hidden\" name=\"".$this->fields[$fieldname]['name']."\" id=\"".$this->fields[$fieldname]['name']."\" value=\"".$this->fields[$fieldname]['value']."\">";
		$this->fields[$fieldname]['html_input_field']=$checkbox_text;
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_multiple_checkbox_input_field($fieldname){
		// for a select_list field config with a field type of checkboxes
		
		$this->fields[$fieldname]['html_input_field']="<input type=\"checkbox\" value=\"eee\">Here it is";
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_dynamic_list_input_field($fieldname){
		$dynlist_output_text .= $this->options['filter'][$fieldname]['field_prefix'];
		$dynlist_output_text .= '<script type="text/javascript" src="scripts/ajax.js"></script>'."\n".'<script type="text/javascript" src="scripts/ajax-dynamic-list.js"></script>'."\n".'<link rel="stylesheet" type="text/css" href="css/dynamic_list.css" />'."\n";
		$field_svl=str_replace("SQL:","",$this->options['filter'][$fieldname]['select_value_list']);
		$pass_table=query_functions::list_tables_in_query($field_svl);
		$pass_fields=query_functions::list_selected_fields_from_query($field_svl);
		$pass_fields_array=explode(",",$pass_fields);
		$pass_id_field=array_shift($pass_fields_array);
		$pass_key_field=array_shift($pass_fields_array);
		if (!$pass_key_field){$pass_key_field=$pass_id_field;}
		//print "set name to " . $fieldname_with_prefix;
		$dynlist_output_text .= "<input type=\"text\" onkeyup=\"ajax_showOptions(this,'t=$pass_table&idf=$pass_id_field&kf=$pass_key_field&jx=1&ajax_populate_dynamic_list',event)\" ";
		$dynlist_output_text .= "id=\"" . $this->fields[$fieldname]['name'] . "\" name=\"" . $this->fields[$fieldname]['name'] . "\""; 
		if ($this->options['filter'][$fieldname]['field_width']){ 
			$dynlist_output_text .= " width=\"".$this->options['filter'][$fieldname]['field_width']."\" ";
		}
		$dynlist_output_text .= "value=\"".database_functions::get_dynamic_list_value($this->options['filter'][$fieldname]['select_value_list'],$this->fields[$fieldname]['value']) ."\""; 
		$dynlist_output_text .= "><input type=\"hidden\" name=\"actual_value_for_".$this->fields[$fieldname]['name']."\" value=\"" . $this->fields[$fieldname]['value']. "\">";
		$dynlist_output_text .= $this->options['filter'][$fieldname]['field_suffix'];
		$this->fields[$fieldname]['html_input_field']=$dynlist_output_text;
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_radio_input_field($fieldname,$checked_value){
		$radio_button_options=explode(",",$this->options['filter'][$fieldname]['radio_options']);
		foreach ($radio_button_options as $radio_button_option){
			$this->fields[$fieldname]['html_input_field'] .= "<span class=\"dbf_radio_button radio_button_$fieldname\">
			<input type=\"radio\" name=\"" . $this->fields[$fieldname]['name'] . "\" value=\"".$radio_button_option."\"";
			if ($radio_button_option==$checked_value){
				$this->fields[$fieldname]['html_input_field'] .= " checked";
			}
			$this->fields[$fieldname]['html_input_field'] .= "></span><span class=\"dbf_radio_button_text radio_text_$fieldname\">" . $radio_button_option . "</span>";
		}
		return $this->fields[$fieldname]['html_input_field'];
	}

	public function draw_dbf_image_picker_field($fieldname){ // mootools 
		$script="<script type=\"text/javascript\" language=\"Javascript\">\n";
		$script .= "function updateImagePickerPreview(imageValue){\n";
		$script .= "document.getElementById('".$fieldname."_image_preview').innerHTML=\"";
		$script .= "<a href=\\\"Javascript:parent.loadPage(document.forms['update_table'].elements['".$this->fields[$fieldname]['name']."'].value,'Image Preview',1)\\\">";
		$script .= "<img src=\"+imageValue+\" style=\\\"height:24px\\\" border=\\\"0\\\"></a>\";\n";
		$script .= "}\n";
		$script .= "</script>\n";
		$EXPORT[$fieldname]['input_field'].="<table><tr><td>".$this->draw_default_input_field($fieldname);
		$EXPORT[$fieldname]['input_field'] .= " </td><td><span id=\"".$fieldname."_image_preview\" style=\"display:inline; \">";
		if($this->fields[$fieldname]['value']){
			$EXPORT[$fieldname]['input_field'].= "<a href=\"Javascript:parent.loadPage(document.forms['update_table'].elements['".$this->fields[$fieldname]['name']."'].value,'Image Preview',1)\"><img style=\"height:24px\" src=\"". $this->fields[$fieldname]['value'] . "\" border=\"0\" />";
		}
		$EXPORT[$fieldname]['input_field'] .= "</span></td><td><input type=\"button\" onClick=\"parent.MUI.notification('Loading Images....'); dbf_imagePicker_popup_open('".$this->fields[$fieldname]['name']."')\" value=\"Select Image\"></td></tr></table>";
		$EXPORT[$fieldname]['input_field'] .= "<div id=\"dbf_imagePicker\" style=\"display:none; overflow:scroll; border:1px solid #666;\"></div>";
		$EXPORT[$fieldname]['input_field'] .= $script;
		return $EXPORT[$fieldname]['input_field'];
	}

	public function draw_mui_image_picker_field($fieldname){ // only for MUI interface - this is the one we couldn't get to work due to iframe security in chrome
		$EXPORT[$fieldname]['input_field']=$this->draw_default_input_field();
		$EXPORT[$fieldname]['input_field'] .= " <input type=\"button\" onClick=\"parent.MUI.imagePickerWindow(window.name,'" . $this->fields[$fieldname]['name'] . "')\" value=\"Select Image\">";
		return $EXPORT[$fieldname]['input_field'];
	}

	public function draw_file_input_field($fieldname){
		$EXPORT[$fieldname]['input_field'] .= "<input type=\"file\" name=\"".$this->fields[$fieldname]['name'] . "\">";

		// template
		global $CONFIG;
		if ($CONFIG['default_file_upload_template']){ $this->options['filter'][$fieldname]['file_upload_template']=$CONFIG['default_file_upload_template'];}
		if ($this->options['filter'][$fieldname]['file_upload_template']){
			global $db;
			$file_upload_template=$db->field_from_record_from_id("templates",$this->options['filter'][$fieldname]['file_upload_template'],template);
			if ($this->options['filter'][$fieldname]['file_upload_preview_dir']){
				$img_src=$this->fields[$fieldname]['value'];
				$img_src_full=$file_upload_preview_dir . "/" . $this->fields[$fieldname]['value'];
			} else {
				$img_src= $this->fields[$fieldname]['value'];
				$img_src_full=$this->options['filter'][$fieldname]['file_upload_directory']. "/" . $this->fields[$fieldname]['value'];
			} 
			$file_upload_template = str_replace("{=image}",$img_src,$file_upload_template);
			$file_upload_template = str_replace("{=image_inc_path}",$img_src_full,$file_upload_template);
			$file_upload_template = str_replace("{=current_file}",$this->fields[$fieldname]['value'],$file_upload_template);
			$file_upload_template = str_replace("{=input_field}",$EXPORT[$fieldname]['input_field'],$file_upload_template);
			if (!$img_src){
				$file_upload_template=preg_replace("/<img.*>/","",$file_upload_template);
				$file_upload_template=preg_replace("/{=if file_exists}.*?{=end_if}/","",$file_upload_template);
				$file_upload_template=preg_replace("/{=if !file_exists}(.*?){=end_if}/","\${1}",$file_upload_template);
			} else {
				$file_upload_template=preg_replace("/{=if !file_exists}.*?{=end_if}/","",$file_upload_template);
				$file_upload_template=preg_replace("/{=if file_exists}(.*?){=end_if}/","\${1}",$file_upload_template);
			}
		}
		if ($this->options['filter'][$fieldname]['file_upload_display_current']){
		$EXPORT[$fieldname]['input_field'] .= "<br />";
		$EXPORT[$fieldname]['input_field'] .= "<span class='current_file_title' style='float:left'>Current file: </span> <span class='current_file_name' style='float:left'>";
		if ($this->options['filter'][$fieldname]['file_upload_current_link']){
			$file_upload_dir = $this->options['filter'][$fieldname]['file_upload_directory'];
			$file_upload_preview_dir= $this->options['filter'][$fieldname]['file_upload_preview_directory'];
			if ($file_upload_dir){$file_upload_dir .= "/";}
			if ($file_upload_preview_dir){
				$file_upload_preview_dir .= "/";
				$file_upload_source = $file_upload_preview_dir . $this->fields[$fieldname]['value'];
			} else {
				$file_upload_source = $file_upload_dir . $this->fields[$fieldname]['value'];
			}
			if (stristr($_SERVER['PHP_SELF'],"mui-administrator.php")){
				$EXPORT[$fieldname]['input_field'] .= "<a href=\"" . get_link($file_upload_source,"Image Preview","",1) . "\">";
			} else {
				$EXPORT[$fieldname]['input_field'] .= "<a href=\"$file_upload_source\" title=\"" . $this->fields[$fieldname]['value'] . "\" class=\"mb\">";
			}
		}
		$EXPORT[$fieldname]['input_field'] .= $this->fields[$fieldname]['value'];
		if ($this->options['filter'][$fieldname]['file_upload_current_link'] && !$this->options['filter'][$fieldname]['file_upload_inline_image_preview']){ $EXPORT[$fieldname]['input_field'] .= "</a>"; }
		$EXPORT[$fieldname]['input_field'] .= "</span>";
		if ($this->options['filter'][$fieldname]['file_upload_inline_image_preview'] && $this->fields[$fieldname]['value']){
			list($img_width,$img_height)=explode(",",$this->options['filter'][$fieldname]['file_upload_inline_image_preview']);
			$img_width=str_replace("x=","",$img_width);
			$img_height=str_replace("y=","",$img_height);
			$EXPORT[$fieldname]['input_field'] .= "<span class=\"current_file_inline_preview\">";
			if ($this->options['filter'][$fieldname]['file_upload_current_link']){
				 if (stristr($_SERVER['PHP_SELF'],"mui-administrator.php")){
					$EXPORT[$fieldname]['input_field'] .= "<a href=\"" . get_link($file_upload_source,"Image Preview","",1) . "\">";
				} else {
					$EXPORT[$fieldname]['input_field'] .= "<a href=\"$file_upload_source\" title=\"" . $this->fields[$fieldname]['value'] . "\" class=\"mb\">";
				}
			}
			$EXPORT[$fieldname]['input_field'] .= "<img hspace=\"5\" vspace=\"0\" src=\"$file_upload_source\" width=\"$img_width\" ";
				if ($img_height){ $EXPORT[$fieldname]['input_field'] .= "height=\"$img_height\" ";
				}
			$EXPORT[$fieldname]['input_field'] .= "border=0>";
			if ($this->options['filter'][$fieldname]['file_upload_current_link']){
				$EXPORT[$fieldname]['input_field'] .= "</a>";
			}
			$EXPORT[$fieldname]['input_field'] .= "</span>";
		}
		if (!$this->fields[$fieldname]['value']){$EXPORT[$fieldname]['input_field'] .= "NONE";}
		}
		$ajax_delete="<div id=\"file_delete_response_$fieldname\"><a href=\"Javascript:removeFile('".$this->tablename."','$fieldname','$this->rowid_for_edit','$dbf_filter_id')\">REMOVE FILE</a></div>";
		if ($this->options['filter'][$fieldname]['file_upload_ajax_delete_button'] && $this->fields[$fieldname]['value']){
			$dbf_filter_id=$this->options['filter']['dbf_filter_id'];
			$EXPORT[$fieldname]['input_field'] .= $ajax_delete;
		}
		
		if ($this->options['filter'][$fieldname]['file_upload_template']){
			if (!$img_src){$ajax_delete="";}
			$file_upload_template=str_replace("{=ajax_delete}",$ajax_delete,$file_upload_template);
			$EXPORT[$fieldname]['input_field']=$file_upload_template;
		}
		
		return $EXPORT[$fieldname]['input_field'];
	}

	public function draw_date_input_field($fieldname){
		if (!$this->fields[$fieldname]['value'] && $this->options['filter'][$fieldname]['default_prefill_value']){
			if ($this->options['filter'][$fieldname]['default_prefill_value']=="{=today}"){
				$this->options['filter'][$fieldname]['default_prefill_value']=date("Y-m-d",time());
			}
			$selected_date=$this->options['filter'][$fieldname]['default_prefill_value'];
		} else {
			$selected_date=$this->fields[$fieldname]['value']; 
		}
		$date_options=$this->options['filter'][$fieldname];
		$EXPORT[$fieldname]['input_field'] .= database_functions::date_input_field($selected_date,$this->rowid_for_edit,$fieldname,$date_options);
		return $EXPORT[$fieldname]['input_field'];
	}

	public function draw_multiple_textfield_input_field($fieldname){
		// not tested this one as its not used in any current implementatons of the software
		$multiple_field_headers = explode(",",$this->options['filter'][$fieldname]['field_headers']);
		$multiple_field_sizes = explode(",",$this->options['filter'][$fieldname]['field_sizes']);
		$multiple_field_values_array = explode($this->options['filter'][$fieldname]['field_delimiter'],$fieldvalues[$fieldname]);
		$i=0;
		do {
			if ($this->rowid_for_edit){
				$sub_field_name="id_" . $this->rowid_for_edit . "_" . $fieldname . "-" . $i;
			} else {
				$sub_field_name="new_" . $fieldname . "-" . $i;
			}
			$output_text .= " " . $multiple_field_headers[$i].": <input type=\"text\" name=\"$sub_field_name\" size=\"".$multiple_field_sizes[$i]."\" value=\"".$multiple_field_values_array[$i]."\">";
			if ($i<$options['filter'][$fieldname]['field_quantity']){ $output_text .= $this->options['filter'][$fieldname]['field_delimiter'];}
			$i++;
		} while ($i < $this->options['filter'][$fieldname]['field_quantity']);
		return $output_text;
	}

	public function draw_multiple_select_input_field($fieldname){
		// not tested this one as its not used in any current implementatons of the software
		$multiple_field_headers = explode(",",$this->options['filter'][$fieldname]['field_headers']);
		$multiple_field_select_options = explode("|",$this->options['filter'][$fieldname]['multiple_select_field_options']);
		$multiple_field_values_array = explode($this->options['filter'][$fieldname]['field_delimiter'],$fieldvalues[$fieldname]);
		$i=0;
		do {
			if ($this->rowid_for_edit){
				$sub_field_name="id_" . $this->rowid_for_edit . "_" . $fieldname . "-" . $i;
			} else {
				$sub_field_name="new_" . $fieldname . "-" . $i;
			}
			$output_text .= " " . $multiple_field_headers[$i].": <select name=\"$sub_field_name\" id=\"$sub_field_name\">";
			$output_text .= database_functions::build_select_option_list($multiple_field_select_options[$i],$multiple_field_values_array[$i],"","","",",","");
			$output_text .= "</select>";
			if ($i<$options['filter'][$fieldname]['field_quantity']){ $output_text .= $this->options['filter'][$fieldname]['field_delimiter'];}
			$i++;
		} while ($i < $this->options['filter'][$fieldname]['field_quantity']);
		return $output_text;
	}

	public function draw_display_only_input_field($fieldname){

		if (!$this->options['filter'][$fieldname]['select_value_list']){
			$EXPORT[$fieldname]['input_field']=$this->fields[$fieldname]['value'];
		} else {
			$EXPORT[$fieldname]['input_field']= database_functions::sql_value_from_id($this->options['filter'][$fieldname]['select_value_list'],$this->fields[$fieldname]['value']); 
		}
		return $EXPORT[$fieldname]['input_field'];
	}

	public function draw_form_reset_button(){
		$reset="<a href=\"Javascript:document.forms['" . $this->value("form_name") . "'].reset()\" title=\"Reset Form\"><img src=\"" . SYSIMGPATH . "/application_images/reset_form_medium.png\" alt=\"Reset Form\" title=\"Reset Form\" border=\"0\" ></a>";
		return $reset;
	}

	public function draw_form_submit_button(){

		if ($this->options['filter']['form_submit_to_javascript']){
			$submit_element_type="button";
		} else {
			$submit_element_type="submit";
		}

		if ($this->options['filter']['submit_button_text']){
			$this->submit_button = "<input type=\"$submit_element_type\" class=\"general_button\" value=\"" . $this->options['filter']['submit_button_text'];
			if ($submit_element_type=="button"){
				$this->submit_button .= " onclick=\"".$this->options['filter']['form_submit_to_javascript'] . "\"";
			}
			$this->submit_button .= " \">";
		} else {
			$img_src=SYSIMGPATH."/application_images/save_beige_43x39.png";
			if ($this->options['filter']['submit_button_image']){
				$img_src=$this->options['filter']['submit_button_image'];
			}
			if ($this->formtype == "edit_single" || $this->formtype == "edit_all"){
				if ($submit_element_type=="button"){
					$this->submit_button = "<img src=\"$img_src \" onClick=\"".$this->options['filter']['form_submit_to_javascript'] . "\" alt=\"Save Record\" title=\"Save Record\" /> ";
				} else {
					$this->submit_button .= "<img src=\"".$img_src."\" alt=\"Save the Record\" title=\"Save the Record\" onClick=\"document.forms['update_table'].submit();\" style=\"cursor:pointer;\"> ";
				}
			} else { // add or even add_or_edit
				if ($submit_element_type=="button"){
					$this->submit_button = "<img src=\"$img_src\" onClick=\"".$this->options['filter']['form_submit_to_javascript'] . "\"  /> ";
				} else {
					$this->submit_button .= "<img src=\"$img_src\" alt=\"Save Record\" title=\"Save Record\" onClick=\"document.forms['update_table'].submit();\" style=\"cursor:pointer;\"> ";
				}
			}
		}
		
		return $this->submit_button;
	}

	public function draw_save_and_continue_button(){
			$imgsrc = "" . SYSIMGPATH . "/application_images/save_edit_beige_43x39.png";
			$this->save_and_continue_button = "<input type=\"hidden\" name=\"after_update_page_element\" value=\"\"><img src=\"".$imgsrc."\" onClick=\"document.forms['update_table'].elements['after_update_page_element'].value='continue'; document.forms['update_Table'].submit();\" alt=\"Save and continue editing this record\" title=\"Save and continue editing this record\" style=\"cursor:pointer\">\n ";
		return $this->save_and_continue_button;
	}


	public function draw_save_and_add_button(){
		if ($this->options['filter']['save_and_add_button']){
			$this->save_and_add_button = "<input type=\"image\" src=\"".SYSIMGPATH."/application_images/save_add_beige_43x39.png\" onClick=\"document.forms['update_table'].elements['after_update_page_element'].value='repeat';\" alt=\"Save and add another\" title=\"Save and add another\">\n ";
		} else {
			$this->save_and_add_button = "<input type=\"image\" src=\"".SYSIMGPATH."/application_images/save_add_beige_43x39.png\" onClick=\"document.forms['update_table'].elements['after_update_page_element'].value='repeat';\" alt=\"Save and add another\" title=\"Save and add another\">\n ";
		}
		return $this->save_and_add_button;
	}

	public function add_form_data_to_template($EXPORT,$template_source){
		global $db;
		global $page;
		if (!$template_source){ $template_source="templates"; $template_filter_key="display_in_template"; }
		if ($template_source=="templates"){ $template_filter_key="display_in_template"; $where_field = "id"; }
		if ($template_source=="admin_templates"){ $template_filter_key="display_in_admin_template"; $where_field = "dbf_key_name"; }
		$sql="SELECT * from $template_source where $where_field = \"" . $this->options['filter'][$template_filter_key] . "\"";
		$result=$db->query($sql);
		while ($row=$db->fetch_array($result)){
			$template=$page->get_appended_template_files($row['template']);
		}
		if ($db->num_rows($result)==0){
			format_error("No template file returned. Has the correct template been specified in the filter key?",1);
		}
		// create main template variables
		//$template=str_replace("\n","_NEWLINE_",$template); // actually they're all on one line through tiny mce aren't they?

		$cp1=new codeparser();
		foreach ($EXPORT as $var=>$val){
			if (is_array($val) && array_key_exists("value",$val)){ // checking for array type and if key exists added nov 2015
				$data[$var]=$val['value'];
			}
		}
		$template=$cp1->parse_form_template_code($template,$data);

		$match_result=preg_match_all("/{=\w+(:\w+)?}/s",$template,$matches);
		$matches=$matches[0];
		foreach ($matches as $each_match){
			$replace_with="";
			if (stristr($each_match,"=global:")){$replace_with=$each_match;}
			$each_match_var=str_replace("{=","",$each_match);
			$each_match_var=str_replace("}","",$each_match_var);
			list ($fieldname,$field_part)=explode(":",$each_match_var);
			if (stristr($each_match_var,":fieldname") && $EXPORT[$fieldname]['formatted_fieldname']){
				$replace_with=$EXPORT[$fieldname]['formatted_fieldname'];
			} else if (stristr($each_match_var,":input_field") && $EXPORT[$fieldname]['input_field']) {
				$replace_with=$EXPORT[$fieldname]['input_field'];
			} else if (stristr($each_match_var,":value") && $EXPORT[$fieldname]['value']) {
				$replace_with=$EXPORT[$fieldname]['value'];
			} else if (stristr($each_match_var,":formatted_value") && $EXPORT[$fieldname]['formatted_value']) {
				$replace_with=$EXPORT[$fieldname]['formatted_value'];
			} else {
				if ($EXPORT[$each_match_var]){
					$replace_with=$EXPORT[$each_match_var];
				} else if ($each_match_var=="query_string"){
						$replace_with=$_SERVER['QUERY_STRING'];
					} else if ($each_match_var=="coded_query_string"){
						$replace_with=create_preUrl_string($_SERVER['QUERY_STRING']);
					} else {
						if ($each_match_var=="submit_button"){$replace_with=$EXPORT['submit_button'];}
					}
				}
					$template=str_replace($each_match,$replace_with,$template);
			}

			//if (!stristr($_SERVER['PHP_SELF'],"administrator.php")){
				$cp=new codeparser();
				$template=$cp->parse_string_for_system_functions($template);
				$template=$cp->global_vars($template);
			//}
			//$template=Codeparser::php_in_content($template);
			if (stristr($template,"{=many_to_many_subform:")){$template=Codeparser::many_to_many_subform($template);}
			return $template;
	}

	public function options_from_enum_type($fieldname,$datatype){
		$datatype=str_replace("enum('","",$datatype);
		$datatype=preg_replace("/'\)$/","",$datatype);
		$options_array=explode("','",$datatype);
		$datatype=join(",",$options_array);
		$this->options['filter'][$fieldname]['radio_options']=$datatype;
		return "enum";
	}

	private function dir_as_csv_for_select($dir_string,$filetypes){
		$dir_string=trim(str_replace("DIR:","",$dir_string));
		$filelist=join(",",get_directory_list($dir_string,$filetypes));
		return $filelist;
	}

}
