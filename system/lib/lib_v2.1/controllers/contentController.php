<?php

/* Examples:
http://shop.gonzomultimedia.co.uk/content/admin/system_tools_menu/ - loads an admin page
http://shop.gonzomultimedia.co.uk/content/1 - loads content id 1 from the content db table
*/


class contentController extends baseController {

	private $contentId = null;
	private $contentKeyName = null;

	function __construct(){

		parent::__construct();

		$path = parse_url($_SERVER['REQUEST_URI']);
		$urlParts=explode("/",$path['path']);
	
		// Set either a content id or a key name (content can be looked up by either)
		if (is_numeric($urlParts[2]) && !$urlParts[3]){
			$this->contentId=$urlParts[2];
		} else if (is_numeric($urlParts[3]) && !$urlParts[4]){
			$this->contentId=$urlParts[3];
		} else if ($urlParts[3]){
			$this->contentKeyName=$urlParts[3];
		} 

	}

	/* 
	 * Function: defaultAction
	 * url contains only 1 content id
	*/
	function defaultAction(){

		$content=$this->page->content_from_id($this->contentId);
		$this->page->set_value("content",$content);
		return 1;
	} 

	/* 
	 * Function: admin
	 * Meta: called when url contains admin/[key name]
	*/
	function admin(){

		if (null != $this->contentId){
			$content=$this->page->admin_content_from_id($this->contentId); 
		print $content;
		} else {
			$content=$this->page->display_admin_content_by_key_name($this->contentKeyName);
		}
		$this->page->set_value("content",$content);
		return 1;
	}

}

?>
