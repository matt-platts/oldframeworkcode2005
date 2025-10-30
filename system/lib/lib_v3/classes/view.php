<?php

/* Class: view
   The admin view is complicated as it pulls different default javascripts and css in depending on what and how something is being viewed.
   Code pulled in dynamically may be the tinyMCe editor, the code editor, specific system stylesheet for iframes, etc. along with page headers, footers  and menus
*/
  

class view extends page {
	
	private $view_template;
	private $record_edit_type;

	function __construct(){
		$this->view_template=null;
		$this->record_edit_type=null;
	}
	
	/*
	 * Function: value
	 * Meta; generic property getter
	*/
	function value($of){
		return $this->$of;
	}

	/*
	 * Function: set_value
	 * Meta: generic property setter
	*/
	function set_value($of,$to){
		$this->$of=$to;
		return 1;
	}

	/*
	 * Function: printPage
	 * Meta: Called externally, this loads, parses and prints the view
	*/
	public function printPage() {
		$this->view_template=$this->load_view();
		$this->printCompletePage();
		return 1;
	}

	/* 
	 * Function: load_view
	 * Loads and returns the contents of the view file
	*/
	private function load_view(){
		if (!parent::value("view")){ print format_error("No view specified"); exit;}
		$view_inc_path = str_replace("admin/","",HTTP_PATH . "/views/" . parent::value("view"));

		# Matt2020 - Version without http to get round allow_url_fopen php setting - just 2 lines
		$view_inc_path=BASEPATH . "/views/" . parent::value("view");
		$view_inc_path = str_replace("admin/../","",$view_inc_path);

		$view = file_get_contents($view_inc_path);
		return $view;
	}

	/*
	 * Function: printCompletePage
	 * Meta: parses the view file and passes it to the final_print() function 
	*/
	private function printCompletePage(){
		global $page;
		global $CONFIG;
		global $db;

		$core_template = Array();

		if ($page->value("mui") && !$page->value("exportExcel")){
			$add_to_header="<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/mui_styles.css\">\n</head>\n";
		}

		if ($_GET['mceInit']){ $mce_editor_type=$_GET['mceInit']; } else { $mce_editor_type="normal"; }


		// 1. If it's NOT an ajax request or an excel spreadsheet, load the page header, header html and javascripts to kick of the text and code editors if required.
		if (!$page->value("useAjax") && !$page->value("exportExcel")){
			// this line loads the style information and title
			$core_template[] = str_replace("<?php echo \$admin_title; ?>",$CONFIG['admin_title'],$page->load_header(1)); // THIS IS THE ADMIN VIEW!!!!
			$this->output($core_template,"page_header");
			$core_template = Array();
			// do NOT load tinyMCE if there's no editing (edit_type var),
			// or if we're on edit all or add multiple as we dont like the idea of waiting for minutes for the page to render
			// get table_options for style_editor and code_editor
			$style_editor=0;
			if ($_REQUEST['edit_type'] == "edit_single" || $_REQUEST['edit_type'] == "add_row" || $this->value("record_edit_type")=="edit_single"){$style_editor=1;}
			$t_options_sql="SELECT * from table_options where table_option IN (\"no_editor\",\"code_editor\")";
			$t_options_res=$db->query($t_options_sql);
			while ($t_o = $db->fetch_array($t_options_res)){
				if ($t_o['table_name']== $_REQUEST['tablename'] || $t_o['table_name'] == $_REQUEST['t']){ $style_editor=0;}
				if ($t_o['table_option']=="code_editor" && ($t_o['table_name']== $_REQUEST['tablename'] || $t_o['table_name'] == $_REQUEST['t'])){ $code_editor=1;}		
			}
			if (array_key_exists("style_editor",$_REQUEST)){$style_editor=$_REQUEST['style_editor'];}
			if ($style_editor){
				$core_template[] = $page->style_editor_code($mce_editor_type,$dbforms_options['filter']['tinymce_images_dir']);
				$this->output($core_template,"style_editor_code");
				$core_template = Array();
			}
			if ($code_editor || $page->value("script_action")=="fileEdit"){
				$core_template[] =  $page->code_editor_code();
				$this->output($core_template,"code_editor_code");
				$core_template = Array();
			}
			if ($code_editor && $_REQUEST['edit_type']=="add_row"){
				$page->code_editor_new_record_init($_REQUEST['t']);
			}
		if ($CONFIG['use_preview_widget']==1 && $user->value("id")){
			$core_template[] = '<script type="text/javascript" src="scripts/mootools_ajax_admin.js"></script>';
				$this->output($core_template,"mootools_preview_widget_init");
				$core_template = Array();
		}
		$core_template[] = $page->multibox_code_head();
		$this->output($core_template,"multibox_code_head");
		$core_template = Array();
		$core_template[] = $page->multibox_code_body();
		$this->output($core_template,"multibox_code_body");
		$core_template = Array();
		// preview widget
		if ($CONFIG['use_preview_widget']==1 && $user->value("id")){
			$core_template[] = $page->preview_div_code();
			$this->output($core_template,"preview_div_code");
			$core_template = Array();
		}

		$core_template[] = $CONFIG['admin_header_html'];
		$this->output($core_template,"admin_header_html");
		$core_template = Array();

		} 

		// 2. The code editor. If its not an ajax request and not an excel spreadsheet, see if we need to load the code editor..
		if ($page->value("useAjax") && !$page->value("exportExcel")){
			$t_options_sql="SELECT * from table_options where table_option IN (\"no_editor\",\"code_editor\")";
			$t_options_res=$db->query($t_options_sql);
			while ($t_o = $db->fetch_array($t_options_res)){
				if ($t_o['table_name']== $_REQUEST['tablename'] || $t_o['table_name'] == $_REQUEST['t']){ $style_editor=0;}
				if ($t_o['table_option']=="code_editor" && ($t_o['table_name']== $_REQUEST['tablename'] || $t_o['table_name'] == $_REQUEST['t'])){ $code_editor=1;}		
			}
			if ($code_editor || $_REQUEST['action']=="fileEdit"){
				$core_template[] = $page->code_editor_code();
				$this->output($core_template,"code_editor_code");
				$core_template = Array();
			}
			if ($code_editor && $_REQUEST['edit_type']=="add_row"){
				$core_template[] = $page->code_editor_new_record_init($_REQUEST['t']);
				$this->output($core_template,"code_editor_new_record_init");
				$core_template = Array();
			}
		}


		// 3. If we're in iframe mode and not exporting excel, load the iframe stylesheet
		if ($page->value("iframeMode")==1 && !$page->value("exportExcel")){

			$core_template[] = str_replace("<?php echo \$admin_title; ?>",$CONFIG['admin_title'],$page->load_header(1));
			$core_template[] = "\n";
			$core_template[] = "<link rel=\"stylesheet\" type=\"text/css\" href=\"".HTTP_PATH."/css/iframe_stylesheet_admin.css\">\n";

			$this->output($core_template,"iframe_mode");
			$core_template = Array();
		}

		// 4. If we're using ajax and it's not an inline popup, we add the style editor code, code editor code and re-attach mootools to the page..
		if ($page->value("useAjax") && !$page->value("inlinePopup")){
				$core_template[] = $page->style_editor_code($mce_editor_type,$dbforms_options['filter']['tinymce_images_dir']);
				$core_template[] = $page->code_editor_code();
				$core_template[] = $page->ajax_include_system_scripts(); // think need to reattach the mootools ondomready stuff!
			$this->output($core_template,"ajax_not_popup");
			$core_template = Array();
		}

		// 5. If it's the mochaUi interface (desktop) and not excel, add $add_to_header(see above)
		if ($page->value("mui")==1 && !$page->value("exportExcel")){
			$core_template[] = $add_to_header;
			$this->output($core_template,"add_to_header");
			$core_template = Array();

		// 6. If its not the mui interface and not excel, pull in the text that says who is logged in (nb: this should NOT be in menu_admin!). 
		} else if (!$page->value("mui") && !$page->value("exportExcel")){
			$menuData = require_once(LIBPATH . "/library/core/menu_admin.php");// Side menu (DHTML)
			$core_template[] = $menuData['content'];
			$core_template[] = menu_admin_top();
			
			$this->output($core_template,"dhtml_menu");
			$core_template = Array();
			if ($menuData['exit']){
				final_print();
				exit;
			}
		}

		
		// 7. If its not an ajax request and not excel, pull in the top menu (may be user or user type specific)
		$menuid=1;
		if ($CONFIG['admin_menu_user'] && $user->value("type")=="user"){$menuid=$CONFIG['admin_menu_user'];}
		if ($CONFIG['admin_menu_administrator'] && $user->value("type")=="administrator"){$menuid=$CONFIG['admin_menu_administrator'];}
		$time_start_menu = time();
		global $user;
		if ((!$page->value("useAjax") && !$page->value("exportExcel")) && ($action || $user->value('id'))){
	
			$core_template[] = build_menu_from_table($menuid); 
			$this->output($core_template,"system_menu");
			$core_template = Array();
		} // load a menu from the menu table. Need a selector for this somehow

		// 8. The actual page content (as defined by the url or POST, etc) comes in here
		$content=$page->value("content");
		if (is_array($page->value("content"))){
			$core_template[]=$content['Message']; // an error message - eg. permissions error
			$this->output($core_template,"content");
		} else {
			#$core_template[]=$page->value("content");
			$core_template[]="<!--{[=CONTENT-HERE]}//-->"; # Going to replace this later as we need to preserve dbf tags
			$this->output($core_template,"content");
		}

		$core_template = Array();

		// Ignore this - needs to be moved (checked)
		if (!$page->value("exportExcel")){
			//close_col(); // closes main div (col2)
		}

		// 9. The footer if it's ajax, the span called unique_1 if it's not
		if (!$page->value("useAjax") && !$page->value("exportExcel")){
			$core_template[] = "<p id=\"footer_para\">&copy; Paragon Digital, " . date("Y") . "</p>";
			$core_template[] = "</div><!-- just closed the container div, needs to go into footer really //-->";
		} else {
			$core_template[] = "<span id=\"unique_1\"></span>";
		}
		$this->output($core_template,"footer");
		$core_template = Array();

		// 10. If it's not excel then the actual html footer goes here
		if (!$page->value("exportExcel")){
			$core_template[] = $page->load_footer(2);
			$this->output($core_template,"page_footer");
		}

		// 11. Finally, print it all out.
		$this->final_print();
	}

	/*
	 * Function: output
	 * Param $content (array)
	 * Param $section (string)
	 * Meta: Joins the array of lines of content and replaces each section of the view with that content
	 * Returns null
	*/
	function output($content,$section){
		$section="{=".$section."}";
		$joined_content=join("\n",$content);;
		$this->set_value("view_template",str_replace($section,$joined_content,$this->value("view_template")));
	}

	/*
	 * Function: final_print
	 * Meta: Does the final print of the view, removing any empty (unused) view tags
	*/
	function final_print(){
		$admin_template = $this->value("view_template");
		$admin_template = preg_replace("/\{=.*?\}/","",$admin_template); # MATT 2021 Don't do this as it might be a template for editing with dbf tags inputted by the user such as a content page!
		$admin_template = preg_replace("/\{=/","",$admin_template);
		// re-insert content here to preserve dbf tags
		global $page;
		$admin_template = str_replace("<!--{[=CONTENT-HERE]}//-->",$page->value('content'),$admin_template); # So we added this instead, a different type of tag

		if (strpos($_SERVER['PHP_SELF'],"/admin/") === false) { // must return an actual zero for the position of /admin/ and not false. Here we're only acting if it is admin, so using false.
			//print "ITS ADMIN";
			$admin_template = preg_replace("/\{=.*?\}/","",$admin_template); # MATT 2021 Don't do this as it might be a template for editing with dbf tags inputted by the user such as a content page!
			$admin_template = preg_replace("/\{=/","",$admin_template);
		}


		$admin_template=str_replace("routing.php","administrator.php",$admin_template);
		print $admin_template;
	}

}

?>
