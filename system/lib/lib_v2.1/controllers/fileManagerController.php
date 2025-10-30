<?php

/*
 * CLASS: fileManagerController
 * Meta: Controller for the directory browser and file manager sections. Note many functions still to be imported into here
*/
class fileManagerController extends baseController{


	private $fm;

	function __construct(){
		parent::__construct();
		require_once(LIBPATH . "/classes/plugins/fileManager/fileManager.php");
		$this->fm = new fileManager;
	}

	public function defaultAction(){
		$this->page->set_value("content",$this->fm->fileManagerFront());
		return 1;
	}

	public function directoryBrowser(){
		$this->page->set_value("content",$this->fm->directoryBrowser());
		return 1;

	}

}

?>
