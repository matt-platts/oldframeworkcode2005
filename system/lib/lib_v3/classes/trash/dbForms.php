<?php

// unused class - this became classes.page.php
class dbForms {

	function __construct(){
		$this->script_action=$_GET['action'];
		$this->useAjax=$_REQUEST['jx'];
		if ($_REQUEST['subform_mode']){$this->useAjax=1;}
		$this->exportExcel = ($_REQUEST['dbf_output_type']=="excel") ? 1 : 0;
		$this->htmlPageTitle="Welcome To The Administrator";
	}

	function value($of){
		return $this->$of;
	}

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }
}

?>
