<?php


class baseController {

	protected $Routing;
	protected $view;
	protected $path;
	private $urlParts;

	public function __construct(){

		$this->Routing=Array();
		$this->path = parse_url($_SERVER['REQUEST_URI']);
		$this->urlParts=explode("/",$this->path['path']);

		// lose the admin part of the url
		if ($this->urlParts[1]=="admin"){
			$this->urlParts[1]="action";
			//array_shift($this->urlParts);
		}

		// start looping at 0 or 1 - crud controller starts at 1 as crud itself is an identifier, others at 0
		if ($this->urlParts[1]=="crud"){
			$start=1;
		} else {
			$start=0;
		}
		
		global $db; // for db_escape
		for ($i=$start;$i<count($this->urlParts);$i=$i+2){
			$this->Routing[$db->db_escape($this->urlParts[$i-1])] = $db->db_escape($this->urlParts[$i]);
		}

		global $page;
		if ($page){
			$this->page=$page;
		}
		
	}


	public function loadController($controller){
	
		if (file_exists(LIBPATH . "/controllers/" . $controller . ".php")){
			$loadController = new $controller;
		} else {
			throw new Exception("Error 404: Page not found");
		}

		return $loadController;

	}

	public function is_mui(){
		if ($this->Routing['mui'] || $_REQUEST['dbf_mui']==1){
			return 1;
		} else {
			return null;
		}
	}

	public function getPath(){
		return $this->path;
	}

	public function isAdmin(){
		if ($this->urlParts[1]=="admin"){
			return 1;
		} else {
			return null;
		}
	}

}

?>
