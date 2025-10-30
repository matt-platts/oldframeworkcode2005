<?php

/*
 * Need to put all functions from system/custom/shopping_cart into here!
*/


class cartController extends baseController {

	private $content;
	private $template;

	function __construct(){

		parent::__construct();

		$path = parse_url($_SERVER['REQUEST_URI']);
		$urlParts=explode("/",$path['path']);

	}

	/* 
	 * Function: defaultAction
	 * url contains only 1 content id
	*/
	public function defaultAction(){

		$content="No action specified";
		$this->page->set_value("content",$content);
		return 1;
	} 

	/* 
	 * Function: view 
	 * Meta: View shopping cart - is set to editale here too 
	*/
	public function view() {
		global $mycart;
		$content = $mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons();
		$this->page->set_value("content",$content);
		return 1;
	}
	
	public function add($data){
		global $mycart;

		// need to get the pairs out.. find an improved way on this
		$product_id=$data['product_id'];
		$quantity = $data['quantity'];
		if (!$quantity || !is_numeric($quantity)){ $quantity=1; }

		$content = $mycart->add_to_cart($product_id,$quantity);
		$title="Shopping cart"; // where do we set this?
		$this->page->set_value("title",$title);
		$this->page->set_value("content",$mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons());
		return 1;
	}

	public function preorderadd($data){
		global $mycart;

		// need to get the pairs out.. find an improved way on this
		$product_id=$data['product_id'];
		$quantity = $data['quantity'];
		if (!$quantity || !is_numeric($quantity)){ $quantity=1; }

		$content = $mycart->add_to_preorder_cart($product_id,$quantity);
		$title="Shopping cart"; // where do we set this?
		$this->page->set_value("title",$title);
		$this->page->set_value("content",$mycart->order_header() . $mycart->view_cart_general( array("allow_update" => "1") ) . $mycart->order_and_browse_buttons());
		return 1;
	}

	/* Not yet working need to send content in */
	public function browse() {

		global $mycart;
		$title = $mycart->get_category_breadcrumb($_GET['category_id']);
		$browser_title = $this->page->browser_title_from_id($_GET['content']); 
		if (!$browser_title){$browser_title="Shopping Cart";}
		$this->page->set_value("content",$this->page->content_from_id($_GET['content'])); 
		return 1;
	}
	
}

?>
