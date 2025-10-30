<?php

class tablesController extends baseController{

	private $table;

	function __construct(){
		parent::__construct();
		
	}

	public function sysListTables(){

		require_once(LIBPATH . "/classes/core/tables.php");
		$tables=new tables;
		ob_start(); // horrible wrapper - needs to set or return outside of output buffering
		$tables->print_list_tables('all');
		$content = ob_get_contents();
		ob_end_clean();
		$this->page->set_value("content",$content);

		return 1;

	}

	public function sysTableInfo(){

		require_once(LIBPATH . "/classes/core/tables.php");
		$tables=new tables;
		$content = $tables->sysTableInfo($this->Routing['table']);
		$this->page->set_value("content",$content);

		return 1;

	}

}

?>
