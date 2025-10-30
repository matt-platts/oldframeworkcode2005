<?php

/* 
 * CLASS: MultiActionController
 * Meta: Legacy code - not yet put into classes 
 *       The old script has been converted to one massive controller to ensure it still works, as conversion to OO takes place.
 * 	 When complete this file should not exist
*/
class MultiActionController extends baseController {


	function __construct(){
		parent::__construct();
	}

	function defaultAction(){

	$path=$_SERVER['REQUEST_URI'];
	$path = preg_replace("/\/admin\//","",$path); // /admin/ directory should not be part of the path
	$path=explode("?",$path);
	$path = trim(join(",",parse_url($path[0])),"/");
	
	if ($path=="administrator.php" || $path=="mui-administrator.php" || $path=="admin_administrator.php"){
	
		if ($_GET['action']){
			$path=$_GET["action"];
		}

	}

	$this->page->set_value("script_action",$path);

	if ($this->page->value("script_action")=="process_log_out"){
		global $user;
		$user->process_log_out("/admin/");
	}

	// Table actions - load filter now - may need to update the tinymce init call so do here now
	$table_actions = Array("edit_table","process_update_table","delete_row_from_table","add_site_content_front","list_table","list_query","list_query_v2","view_record");
	if (in_array($this->page->value("script_action"),$table_actions)){
		require_once(LIBPATH . "/classes/core/filters.php");
		$db_filter=new filter();
		$dbforms_options=$db_filter->load_options(); // NB this now loads filter as well
	}

	
	// mail password screen if forgotten password
	if ($this->page->value("script_action")=="mail_password_front"){ global $user; print $user->mail_password_front();}
	if ($this->page->value("script_action")=="mail_password"){ global $user; $user->mail_password();}
	if ($this->page->value("script_action")=="edit_site_text"){ display_content_menu();}
	if ($this->page->value("script_action")=="create_new_password_for_user"){
		global $user;
		$user->create_new_password_for_user($_REQUEST['user_id'],$_POST['new_password'],$_POST['new_password_confirmed'],$_POST['create_random'],$_REQUEST['auto_mail_password_to_user']);
	}
	if ($this->page->value("script_action")=="admin_change_my_password"){
		global $user;
		$user->admin_change_my_password($_REQUEST['user_id'],$_POST['new_password'],$_POST['new_password_confirmed'],$_POST['create_random'],$_REQUEST['auto_mail_password_to_user']);
	}

	// edit record(s)
	if ($this->page->value("script_action")=="edit_table"){
		$tablename=$_REQUEST['t']; 
		$edit_type=$_REQUEST['edit_type']; 
		$row_id=$_REQUEST['rowid']; 
		$add_data=$_REQUEST['add_data']; 
		$admin_content=database_functions::form_from_table($tablename,$edit_type,$row_id,$add_data,$dbforms_options);
	}
	if ($admin_content['Status']==0 && strlen($admin_content['Status'])>=1){ 
		if (!$col2_open){open_col2();} 
		print "<p>".$admin_content['Message'] . "</p>";
	}

	// process update
	if ($this->page->value("script_action")=="process_update_table"){ 
		$update_table_vars=database_functions::process_update_table($dbforms_options); 
		if ($update_table_vars['return_content']) { 
			print $update_table_vars['return_content']; 
		}
	}

	// delete from table
	if ($this->page->value("script_action")=="delete_row_from_table"){
		$dbforms_options['back_url']=get_preUrl_string($_REQUEST['preUrl']); 
		$tablename=$_REQUEST['t']; 
		$dbforms_options['delete_filter_id']=$_REQUEST['delete_filter_id'];
		$dbforms_options['recordset_filter_id']=$_REQUEST['recordset_filter_id'];
		$dbforms_options['delete_return_to']=$_REQUEST['dbf_delete_return_to'];
		if (!$_REQUEST['t'] && $_REQUEST['tablename']){$tablename=$_REQUEST['tablename'];} 
		$deleteID = $_REQUEST['rowid']; 
		if (!$_REQUEST['rowid']){$deleteID=$_REQUEST['deleteID'];} 
		database_functions::delete_row_from_table($tablename,$deleteID,$dbforms_options); 
	}

	// list records
	if ($this->page->value("script_action")=="list_table"){ database_functions::list_table($_GET['t'],$dbforms_options);}
	if ($this->page->value("script_action")=="list_query"){ $tablename=$_GET['t']; list_query($tablename,$dbforms_options);}
	if ($this->page->value("script_action")=="list_query_v2"){ $queryname="QUERY:".$_GET['q']; database_functions::list_table($queryname,$dbforms_options);}
	if ($this->page->value("script_action")=="view_record"){
		$tablename=$_GET['t']; 
		$row_id=$_GET['row_id']; 
		database_functions::view_record($tablename,$row_id,$dbforms_options); 
	}

	//Interfaces
	if ($this->page->value("script_action")=="filters_and_interfaces"){ require_once(LIBPATH . "/library/core/interfaces.php"); filters_and_interfaces_start();}
	if ($this->page->value("script_action")=="create_new_interface"){create_new_interface($_POST['interface_type'],$_POST['tablename']);}
	if ($this->page->value("script_action")=="create_new_interface_v2"){create_new_interface_v2($_POST['interface_type'],$_POST['tablename']);}
	if ($this->page->value("script_action")=="edit_interface"){edit_interface($_REQUEST['interface_id'],$_POST['tablename']);}
	if ($this->page->value("script_action")=="process_create_new_interface"){process_create_new_interface();}
	if ($this->page->value("script_action")=="process_edit_interface"){process_edit_interface();}
	if ($this->page->value("script_action")=="full_interface_edit"){full_interface_edit($_REQUEST['interface_id']);}
	if ($this->page->value("script_action")=="interface_edit_configure_fields"){interface_edit_configure_fields($_REQUEST['filter_id'],$_REQUEST['field']);}
	if ($this->page->value("script_action")=="process_edit_configure_fields"){process_edit_configure_fields();}

	// Tools
	if ($this->page->value("script_action")=="list_directory"){if (check_permissions("list_directories")){list_directory($_GET['d']);}else{format_error("Permission denied for this action",0);}}
	if ($this->page->value("script_action")=="table_search_replace"){table_search_replace_front();}

	if ($this->page->value("script_action")=="generate_htaccess_html_page_rewrites"){ generate_htaccess_html_page_rewrites();}
	if ($this->page->value("script_action")=="write_category_page_rewrites"){ write_category_page_rewrites();}
	if ($this->page->value("script_action")=="generate_xml_sitemap"){ generate_xml_sitemap();}

	// File Manager / File Uploads
	if ($this->page->value("script_action")=="file_manager"){file_manager_front();}
	if ($this->page->value("script_action")=="upload_file_front"){upload_file_front();}
	if ($this->page->value("script_action")=="process_upload_file"){
		$file_options=get_file_options();
		process_upload_file($file_options); 
		if ($_POST['file_browse_location']=="directory_browser"){
			$script_action="directory_browser"; $_REQUEST['dir']="./".$_REQUEST['dir']; $_GET['dir']="./".$_GET['dir']; 
		} else { 
			$script_action="file_browser";
		}
	}

	// File Browser - note NOT the directory browser
	if ($this->page->value("script_action")=="file_browser"){
		global $db;
		$directory = ($_REQUEST['d'])? $_REQUEST['d'] : "";
		// get list type
		if ($directory){
			$list_type_sql="SELECT list_type,default_no_per_page from file_manager where directory = \"" . $directory . "\"";
			$list_type_result = $db->query($list_type_sql) or die ("ERROR:" . $db->db_error());
			while ($h=$db->fetch_array($list_type_result)){
				$list_type = $h['list_type'];
				$default_no_per_page= $h['default_no_per_page'];
			}
		}
		if (!$list_type){$list_type='list';}
		if (!$default_no_per_page){$default_no_per_page='12';}
		$list_dir_options['file_uploader']=1;
		$list_dir_options['include_delete']=1;
		/// if an interface is applied as default then load it
		$int_sql="SELECT default_interface from file_manager WHERE directory = '$directory'";
		$int_res=$db->query($int_sql);
		$h=$db->fetch_array($int_res);
		if ($h['default_interface']){ $default_interface=$h['default_interface'];}
		if ($_REQUEST['fileint']){ $default_interface=$_REQUEST['fileint'];}
		if ($default_interface){
			$sql = "SELECT * from file_manager_options where interface = '" . $default_interface . "'";
			//print "run $sql";
			$res=$db->query($sql);
			while ($h=$db->fetch_array($res)){
				$list_dir_options[$h['file_manager_option']] = $h['value'];
			}
			$list_dir_options['fileint']=$default_interface;
		}
		
		file_manager_main($directory,$list_type,$_REQUEST['display_options'],$_REQUEST['options_position'],$default_no_per_page,$list_dir_options);
	}

	// batch macro on directory
	if ($this->page->value("script_action")=="run_batch_macro"){
		run_batch_macro($_POST_SAFE['directory'],$_POST_SAFE['dbf_macro_id']);
	} 

	// Database Tables
	if ($this->page->value("script_action")=="sysListTables"){open_col2(); $tabletypes="all"; require_once(LIBPATH . "/classes/tables.php"); $tables=new tables; $tables->print_list_tables($tabletypes); close_col();}
	if ($this->page->value("script_action")=="sysNewTable"){open_col2(); new_table_front(); }
	if ($this->page->value("script_action")=="add_new_db_table"){open_col2(); add_new_db_table(); }
	if ($this->page->value("script_action")=="view_table_schema"){open_col2(); view_table_schema($_GET['table']); }
	if ($this->page->value("script_action")=="delete_table"){open_col2(); delete_table($_GET['table']); }
	if ($this->page->value("script_action")=="add_field_to_table"){open_col2(); add_field_to_table($_GET['table']); }
	if ($this->page->value("script_action")=="process_add_field_to_table"){open_col2(); process_add_field_to_table($_GET['table']); }

	// Ajax
	if ($this->page->value("script_action")=="ajax_remove_file_from_record"){ajax_remove_file_from_record();exit;}
	if ($this->page->value("script_action")=="ajax_populate_dynamic_list"){ajax_populate_dynamic_list($_GET['t'],$_GET['idf'],$_GET['kf'],$_GET['letters']);exit;}
	if ($this->page->value("script_action")=="ajax_generate_options_list"){ajax_generate_options_list($_GET['querytype'],$_GET['top_value'],$_GET['fieldname'],$_GET['filter']);exit;}
	if ($this->page->value("script_action")=="ajax_generate_text_field_value"){ajax_generate_text_field_value($_GET['top_value'],$_GET['fieldname'],$_GET['filter']);exit;}

	//Help text
	if ($this->page->value("script_action")=="helptext"){helptext($_REQUEST['helpid']);}

	if ($this->page->value("script_action")=="sysListSystemOptions"){ require_once(LIBPATH . "/library/core/menu_admin.php"); print_graphic_menu($_GET['pagetype']); }
	// Web Site
	if ($this->page->value("script_action")=="web_site_manager"){web_manager_front();}
	if ($this->page->value("script_action")=="new_web_site"){new_web_site();}

	// Queries
	if ($this->page->value("script_action")=="query_builder"){query_functions::query_builder();}

	// docs
	if ($this->page->value("script_action")=="documentation"){show_documentation();}
	if ($this->page->value("script_action")=="list_all_functions"){list_all_functions(LIBPATH);}

	// login result //////////////////////////////////////// NOT SURE IF THIS SHOULD BE HERE
	if ($login_result){print $login_result;}

	if ($this->page->value("script_action")=="import_database"){import_database($_POST['sqlfile'],$_POST['type']);}
	if ($this->page->value("script_action")=="cache_dynamic_menu"){cache_dynamic_menu($_REQUEST['id']);}
	if ($this->page->value("script_action")=="display_content"){$content=$this->page->content_from_id($_GET['content']); print $content;}
	if ($this->page->value("script_action")=="display_admin_content"){$content=$this->page->admin_content_from_id($_GET['content']); print $content;}
	if ($this->page->value("script_action")=="display_admin_content_by_key_name"){$content=$this->page->display_admin_content_by_key_name($_GET['dbf_key_name']); print $content;}
	if ($this->page->value("script_action")=="url_generator_front"){url_generator_front();}
	if ($this->page->value("script_action")=="generate_url"){generate_url();}

	// Directory Browser
	if ($this->page->value("script_action")=="directory_browser_delete_file"){ directory_browser_delete_file($_GET['dt']); directory_browser();}
	if ($this->page->value("script_action")=="directory_browser_file_rename"){ directory_browser_file_rename($_GET['dir'],$_GET['filename'],$_GET['newname']); directory_browser();}
	if ($this->page->value("script_action")=="directory_browser" || $script_action == "directory_browser"){directory_browser();}
	if ($this->page->value("script_action")=="image_selector" || $script_action == "image_selector"){image_selector();}
	if ($this->page->value("script_action")=="fileEdit"){file_edit($_REQUEST['file']);}
	if ($this->page->value("script_action")=="set_mui_background"){set_mui_background();}
	if ($this->page->value("script_action")=="set_mui_display_options"){set_mui_display_options();}
	if ($this->page->value("script_action")=="set_mui_theme"){set_mui_theme();}

	// Plugins
	if ($this->page->value("script_action")=="plugins_front"){plugins_front();}
	if ($this->page->value("script_action")=="install_plugin"){install_plugin($_GET['plugin']);}
	if ($this->page->value("script_action")=="remove_plugin"){uninstall_plugin($_GET['plugin']);}
	if ($this->page->value("script_action")=="export_table_front"){export_table_front($_GET['t']);}

	// Tables
	if ($this->page->value("script_action")=="dump_table"){dump_table($_REQUEST_SAFE['t']);}
	if ($this->page->value("script_action")=="dump_database" && $_GET['type']=="user"){ dump_user_tables();} elseif ( $this->page->value("script_action")=="dump_database"){dump_database();}
	if ($this->page->value("script_action")=="process_multiple_records"){process_multiple_records($_POST_SAFE['t'],$_POST_SAFE['multi_records_action'],process_record_ids());}
	if ($this->page->value("script_action")=="load_table_from_dir"){load_table_from_dir($_REQUEST['table']);}
	if ($this->page->value("script_action")=="dump_table_to_dir"){dump_table_to_dir($_REQUEST['table']);}
	if ($this->page->value("script_action")=="load_table_data_from_file"){load_table_data_from_file($_REQUEST['table']);}
	if ($this->page->value("script_action")=="export_table_to_file"){export_table_to_file($_REQUEST['table']);}
	if ($this->page->value("script_action")=="copy_row"){copy_row($_REQUEST['table'],$_REQUEST['rowid']);}
	if ($this->page->value("script_action")=="meta_info"){meta_info();}
	if ($this->page->value("script_action")=="transfer_linked_table"){export_rel_table_front();}
	if ($this->page->value("script_action")=="import_related_table_front"){import_related_table_front();}
	if ($this->page->value("script_action")=="import_related_table"){rel_table_init("import");}
	if ($this->page->value("script_action")=="export_related_table"){rel_table_init("export");}
	if ($this->page->value("script_action")=="new_interface_demo"){new_interface_demo();}
	if ($this->page->value("script_action")=="export_software"){export_software_front();}
	if ($this->page->value("script_action")=="upgrade_software"){upgrade_software_front();}
	if ($this->page->value("script_action")=="export_software_action"){export_software_action();}
	if ($this->page->value("script_action")=="recordset_metadata"){database_functions::recordset_metadata($_GET['t'],$_GET['f']);}
	if ($this->page->value("script_action")=="dbf_search_popup_open"){database_functions::dbf_search_popup_open($_GET['t'],$_GET['f'],$_GET['c']);}
	if ($this->page->value("script_action")=="dbf_displayfields_popup_open"){database_functions::dbf_displayfields_popup_open($_GET['t'],$_GET['f'],$_GET['c']);}
	if ($this->page->value("script_action")=="list_table_options_mui"){list_table_options_mui($_REQUEST_SAFE['table']);}
	if ($this->page->value("script_action")=="generate_form_template"){generate_form_template($_REQUEST_SAFE['table']);}
	if ($this->page->value("script_action")=="filter_wizard"){ require_once(LIBPATH . "/classes/core/filters.php"); $wiz=new filter(); $wiz->filter_wizard_front();}
	if ($this->page->value("script_action")=="create_recordset"){ require_once(LIBPATH . "/classes/core/filters.php"); $wiz=new filter(); $wiz->create_recordset();}
	if ($this->page->value("script_action")=="create_form"){ require_once(LIBPATH . "/classes/core/filters.php"); $wiz=new filter(); $wiz->create_form();}
	if ($this->page->value("script_action")=="import_wizard"){ import_wizard($_REQUEST['table']); }
	if ($this->page->value("script_action")=="visualise_relations"){ visualise_relations(); }
	if ($this->page->value("script_action")=="visualise_application_relations"){ visualise_application_relations(); }
	if ($this->page->value("script_action")=="save_relations"){ save_relations(); }

	if ($this->page->value("script_action")=="table_manager_option"){
		$table=$_REQUEST['selected_table'];
		$table_action=$_REQUEST['table_action'];
		if ($table_action=="duplicate"){ duplicate_table($table); }
		if ($table_action=="relationships"){ show_relationships_on($table); }
		if ($table_action=="repair"){ repair_table($table); }
		if ($table_action=="permissions"){ show_permissions_on($table); }
		if ($table_action=="optimise"){ optimise_table($table); }
		if ($table_action=="queries"){ show_queries_on($table); }
		if ($table_action=="meta_data"){ $metainfo=show_metadata_for($table); print $metainfo['header']; print $metainfo['body']; print $metainfo['footer'];}
		if ($table_action=="import_data"){ load_table_data_from_file($table); }
		if ($table_action=="export_data"){ export_table_to_file($table); }
		if ($table_action=="drop"){ delete_table($table); }
	}

	if ($this->page->value("script_action")=="shop_sort_order"){
		database_functions::shop_order($_REQUEST['cat_id'],$_REQUEST['dem_id']);
	}

	if ($this->page->value("script_action")=="sysTableInfo" && $_GET['t']){
		require_once(LIBPATH . "/classes/tables.php");
		$tables=new tables;
		$content = $tables->sysTableInfo($_GET['t']);
		print $content;
	}
	if ($this->page->value("script_action")=="hash_into_template_demo"){
		$nodata=array();
		$content=hash_into_template($nodata,87);
		print $content;
	}
	// None of the above? Custom logic..
	$custom_logic_admin_file = BASEPATH . "/system/custom/custom_logic_admin.php";
	$custom_logic_admin_file = str_replace("/admin/..","",$custom_logic_admin_file);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	if (file_exists($custom_logic_admin_file)){
		include($custom_logic_admin_file) or die("Cannit include custom logic admin file");
	}
	return 1;
	}
}
?>
