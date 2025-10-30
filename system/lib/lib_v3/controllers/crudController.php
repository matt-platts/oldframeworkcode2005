<?php

/* Example usage:
   -------------
   List a table by defaults: 		/crud/table/products/action/list_table/
   List a table with a filter: 		/crud/table/products/action/list_table/filter/158/
   Filter a table by key of a relation: /crud/table/products/action/list_table/relation_id/22/relation_key/6548
   Edit a single table row:	 	/crud/table/content/action/edit_record/rowid/1/
   Edit the recordset you 
   have listed on screen: 		/crud/table/content/action/edit_table/edit_type/edit_all/filter/106/
   Update a table: 			/crud/table/content/action/process_update_table/
   Edit recordset with a relation:	/crud/table/products/action/edit_recordset/filter/128/relation_key/6548/relation_id/22
   Add a record:			/crud/table/content/action/add_record/?style_editor=1 // need to look at how the style editor is included..
*/


class crudController extends baseController{

	private $admin_only = 1;

	protected $Routing;

	private $table;
	private $query;
	private $action;
	private $rowid;

	function __construct(){

		if ($this->admin_only==1){
			global $user;
			if (!$user->value("admin_access")){
				format_error("You do not have permission to perform this action",1);
				exit;
			}
		}

		parent::__construct();

		/*
		if (array_key_exists('table',$Routing)){
			$_REQUEST['t']=$Routing['table'];
			$_GET['t']=$Routing['table'];
		}
		if (array_key_exists('action',$Routing)){
			$_GET['action']=$Routing['action'];
			$_REQUEST['action']=$Routing['action'];
		}
		if (array_key_exists('edit_type',$Routing)){
			$_GET['edit_type']=$Routing['edit_type'];
			$_REQUEST['edit_type']=$Routing['edit_type'];
		}
		if (array_key_exists('rowid',$Routing)){
			$_GET['rowid']=$Routing['rowid'];
			$_REQUEST['rowid']=$Routing['rowid'];
		}
		*/

	}

	function table(){

		global $page;

		/* 
		 * the options action is normally defined by the action paramater, however for edit_all this is stored in the edit_type paramater. 
		 * We pass this to load_options instead of the main action.
		*/
		$options_action=$this->Routing['action'];
		if ($this->Routing['action']=="edit_recordset"){
			$options_action="edit_all"; // need to change to edit_recordset everywhere
		} else if ($this->Routing['action']=="edit_record"){
			$options_action="edit_table"; // we need to change edit_table to edit_record everywhere
		} else if ($this->Routing['action']=="add_record"){
			$options_action="edit_table";
		}

		require_once(LIBPATH . "/classes/core/filters.php");
		$db_filter=new filter();
		$table_options=$db_filter->load_options($this->Routing['table'],$options_action,$this->Routing['filter']); // NB this now loads filter as well

		$table_options['filter']['export']="html";

		if ($this->Routing['relation_key'] && $this->Routing['relation_id']){
			$table_options['relation_key']=$this->Routing['relation_key'];
			$table_options['relation_id']=$this->Routing['relation_id'];
			
			// for now faking the request variable.. this doesn't appear very often in the form class so just needs to be set to a filter key - easy
			if (!$_REQUEST['relation_key']){
				$_REQUEST['relation_key'] = $this->Routing['relation_key'];
				$_REQUEST['relation_id'] = $this->Routing['relation_id'];
			}

		}



		// list a table
		if ($this->Routing['action']=="list_table"){
			$admin_content = database_functions::list_table($this->Routing['table'],$table_options);
			$page->set_value("content",$admin_content);

		// edit a single record
		} else if ($this->Routing['action']=="edit_table" && ($this->Routing['edit_type']=="edit_single" || $this->Routing['edit_type']=="edit_record") 
			|| ($this->Routing['action']=="edit_record" && $this->Routing['rowid'])
			){
			global $view;
			$view->set_value("record_edit_type","edit_single");
			$admin_content=database_functions::form_from_table($this->Routing['table'],"edit_single",$this->Routing['rowid'],'',$table_options);
			$page->set_value("content",$admin_content);

		// edit a record set
		} else if ($this->Routing['action']=="edit_recordset"){
			global $view;
			$view->set_value("record_edit_type","edit_all");
			$admin_content=database_functions::form_from_table($this->Routing['table'],"edit_all",'','1',$table_options);
			$page->set_value("content",$admin_content);

		// process a table update form
		} else if ($this->Routing['action']=="process_update_table"){
		//} else if ($this->Routing['action']=="process_update_table" && $_POST){

			$update_table_vars=database_functions::process_update_table($table_options); 
			if ($update_table_vars['return_content']) { 
				$admin_content = $update_table_vars['return_content']; 
				$page->set_value("content",$admin_content);
			} else if ($update_table_vars['status_message']){
				$page->set_value("content",$update_table_vars['status_message']);
			}

		// add a record
		} else if ($this->Routing['action']=="add_record"){
			global $view;
			$view->set_value("record_edit_type","edit_all");
			$admin_content=database_functions::form_from_table($this->Routing['table'],"add_row",'','1',$table_options);
			$page->set_value("content",$admin_content);

			
		} else {
			$page->set_value("content",format_error("Bad Input - crud controller does not know what to do with this input."));
		}

		return 1;

	}

}

?>
