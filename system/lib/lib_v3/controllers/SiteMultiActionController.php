<?php

/* 
 * CLASS: SiteMultiActionController
 * Meta: Legacy code - not yet put into classes 
 *       The old script has been converted to one massive controller to ensure it still works, as conversion to OO takes place.
 * 	 When complete this file should not exist
*/
class SiteMultiActionController extends baseController {


	function __construct(){
		parent::__construct();
	}

	function defaultAction(){
		
		// init vars
		$status_message = null;
		if (array_key_exists("pageEdit",$_GET)) { $pageEdit = $_GET['pageEdit'];} else{ $pageEdit = null;}
		$update_results = null;	

		global $CONFIG;
		if ($CONFIG['enable_front_end_page_logging']){ $log_page=$this->page->log_page(); }
		if ($CONFIG['site_requires_login']==1 && !$user->value("id")){
			header("Location: login.php");
		}

		if (!$_GET['action']){
			$_GET['action']="";
		}

		// set global vars
		global $user;
		if ($user){
			$this->page->set_global_var("sysUserName",$user->value("full_name"));
		}

		 // undo magic quotes 
		if (get_magic_quotes_gpc()) {
			$in = array(&$_GET, &$_POST, &$_COOKIE); 
			while (list($k,$v) = each($in)) {
				foreach ($v as $key => $val) {
					if (!is_array($val)) { 
					  $in[$k][$key] = stripslashes($val); 
					  continue; 
					} 
					$in[] =& $in[$k][$key]; 
				} 
			} 
			unset($in); 
		}

		// #1 Refresh login cookie if it exists
		if ($_COOKIE['login']){
			$user->refresh_login_cookie();
		}

		#2 Load default site if no site is specified
		global $_REQUEST;
		global $db;
		if (!$_REQUEST['s']){
			$site_sql = "SELECT * from web_sites where default_site=1"; 
			$res=$db->query($site_sql) or die($db->db_error()); 
				while ($sitedata=$db->fetch_array($res)){
					$_REQUEST['s']=$sitedata['id'];
					$_REQUEST['s']=$sitedata['id'];
				}
			if (!$_REQUEST['s']){
				format_error("No default web site found and no web site specified",1,'','',''); exit;
			}
		}

		// #3 FUNCTIONS THAT REQUIRE HEADERS SENT BEFORE OUTPUT GO HERE
		// #3.1 If we have a login request (cookie to be set set, possible redirect header), process it
		if ($_GET['action']=='process_login'){
			$userlogin=$_POST['email_address']; 
			$pass=$_POST['password'];
			$direct_login_to=$_SERVER['PHP_SELF'] . "?";
			if ($_POST['site_id_login']){
				$direct_login_to .= "s=".$_POST['site_id_login']."&amp;";
			} else {
				$direct_login_to .= "s=1&amp";
			}
			if ($_POST['content_id_login']){
				$direct_login_to .= "content=".$_POST['content_id_login']."&amp;"; 
			} 
			if ($_POST['login_page_name']){$direct_login_to=$_POST['login_page_name'];}
			if ($_POST['direct_to']){ $direct_login_to = "/".$_POST['direct_to']; }
			$login_result=$user->process_login($userlogin,$pass,$direct_login_to); 
		}

		// log out requires cookie to expire immediately 
		if ($_GET['action']=="process_log_out" || $_GET['action']=="log_out"){ $return_to = $_SERVER['PHP_SELF'] . "?s=" . $_GET['s']; $user->process_log_out("$return_to"); }
		// change password - (Does not actually have to be here I don't think, should move to lower down) 
		if ($_GET['action']=="change_password"){$title="My Account"; $content = $user->change_password($_POST['password1'],$_POST['password2']);}

		// #4 GET SITE ID
		$current_site=load_web_site_vars($_REQUEST['s']);


		// #5 LOAD HEAD SECTION OF HTML DOCUMENT 
		$jx = $_REQUEST['jx'];
		if (!$jx){
		if ($current_site['default_header']){
			$page_header= $this->page->load_header($current_site['default_header']);
		} else {
			$page_header= $this->page->load_header($CONFIG['default_site_header']); // STILL TO BE ADDED - DEFAULTS TABLE?
		}
		if (!$page_header){ 
			format_error("No HTML header found in web site data or global configuratin options.",0); 
			$page_header=""; }
		}

		// #6 - LOAD DYNAMIC MENU
		if ($_REQUEST['dbf_mu']){$menu_id=$_REQUEST['dbf_mu'];}
		if ($current_site['dynamic_menu_default']){$menu_id=$current_site['dynamic_menu_default'];}
		if (!$current_site['lock_default_menu'] && $_REQUEST['dbf_mu']){ $menu_id=$_REQUEST['dbf_mu']; }
		if ($menu_id){$menu_data=build_menu_from_table($menu_id);} else { $menu_data=""; }


		if ($_GET['content']){
			$meta_keywords = $this->page->keywords_from_id($db->db_escape($_GET['content']));
			$meta_description = $this->page->meta_description_from_id($db->db_escape($_GET['content']));
		}

		// #8 - LOAD MAIN CONTENT DEPENDING ON THE action VARIABLE
		// If no action, or you've just logged out, run the home page
		if ((!$_GET['action'] || $_GET['action'] == "process_log_out" || $_GET['action'] == "log_out") && !$_GET['content']){
			$content = $this->page->content_from_id($current_site['default_content']);
			$title=$this->page->title_from_id($current_site['default_content']);
			$browser_title=$this->page->browser_title_from_id($current_site['default_content']);
			$display_content_now=1;
		} else if (!$_GET['action']){
			 $content = $this->page->content_from_id($_GET['content']); 
			 $title=$this->page->title_from_id($_GET['content']); 
			 $browser_title=$this->page->browser_title_from_id($db->db_escape($_GET['content'])); 
			 $display_content_now=1;
		}
		if ($_GET['action']=="content_by_title"){
			$content=$this->page->content_from_title($db->escape($_GET['title']));
		}

		// #9 If we're updating content via the local cms, or updating data via an embedded form, do this then load the content back in 'view' mode (not edit)
		if ($_GET['action']=="updateContent" || $_GET['action']=="process_update_table"){
			require_once(LIBPATH . "/classes/core/filters.php");
			$db_filter=new filter();
			$dbforms_options=$db_filter->load_options(); // NB this now loads filter as well
			$update_results=database_functions::process_update_table($dbforms_options);
			$status_message=$update_results['status_message'];
			$form_update_status=$update_results['status'];
			$form_updated_content_id=$update_results['updated_content_id'];
			// all we need here then is something to check if the status is good and to display the status of the form in the content page instead of the form

			if ($update_results['repeat_form']){
				$content_repeat_form=$update_results['repeat_form']; 
				$content_repeat_form_filter_id=$update_results['repeat_form_filter_id'];
				$content_repeat_form_table=$update_results['repeat_form_tablename'];
				if ($_GET['content']){
					$title=$this->page->title_from_id($_GET['content']);
					$browser_title=$this->page->browser_title_from_id($_GET['content']);
				} else {
					// this will actually screw up the repeat form as theres no content to load for the form! ERK!
				}
			} else if ($update_results['script_action']){
				$script_action=$update_results['script_action'];
				header("Location: site.php?action=$script_action");
				exit;
			} else {
				if ($_GET['action']=="updateContent"){$content=$this->page->content_from_id($_GET['content']); $title=$this->page->title_from_id($_GET['content']);} 
			}
			if ($update_results['return_content']){
				$content=$update_results['return_content'];
				$title=$update_results['title'];
			} else {
				// YES THE MIS_SPELLING BELOW IS DELIBERATE! Problem is theres not values in the repeated form so we have to DO IT AGAIN!!!!
				if (!$crontent_repeat_form){ // ERK!!! The line below gives us the form WITH filled in values, so its obviously not repeated properly when first exported by database_functions.php! Why on earth is this?!
					if ($_GET['content']){$content=$this->page->content_from_id($_GET['content']); $title=$this->page->title_from_id($_GET['content']);}
				}
			}

			if ($update_results['alternate_content_on_form_fail']){
				$content=$this->page->content_from_id($update_results['alternate_content_on_form_fail']);
				$title=$this->page->title_from_id($update_results['alternate_content_on_form_fail']);
				$browser_title=$this->page->browser_title_from_id($update_results['alternate_content_on_form_fail']);

			}
			$display_content_now=1;
		}

		// #10 if we're deleting a row then just delete it (nothing else yet - can look at this at some point make it better)
		if ($_GET['action']=="delete_row_from_table"){
			require_once(LIBPATH . "/classes/core/filters.php");
			$db_filter=new filter();
			$dbforms_options=$db_filter->load_options(); // NB this now loads filter as well
			$dbforms_options['back_url']=get_preUrl_string($_REQUEST['preUrl']); $tablename=$_REQUEST['t']; if (!$_REQUEST['t'] && $_REQUEST['tablename']){$tablename=$_REQUEST['tablename'];} $deleteID = $_REQUEST['rowid']; if (!$_REQUEST['rowid']){$deleteID=$_REQUEST['deleteID'];} delete_row_from_table($tablename,$deleteID,$dbforms_options);	
		}

		// #11 if we've got a form to mail (along with a content ID this should be) then hit the mail_form module. If we get a response print it, otherwise load the content
		if ($_GET['action']=="mail_form"){
			$response = mail_form();
			if ($response){$content=$response;} else { $content=$this->page->content_from_id($_GET['content']);}
			$title=$this->page->title_from_id($_GET['content']);
			$browser_title=$this->page->browser_title_from_id($_GET['content']);
			$display_content_now=1;
		}

		// #12 - Load a Master Template 
		if ($current_site['master_template'] && !$_REQUEST['mt']){$template_to_load=$current_site['master_template'];}
		if ($current_site['master_template_when_logged_in'] && $user->value('id')){
			$template_to_load=$current_site['master_template_when_logged_in'];
		}
		if ($_REQUEST['mt']){$template_to_load=$_REQUEST['mt'];}
		if ($this->page->value("override_template_from_content")){ $template_to_load=$this->page->value("override_template_from_content"); }
		$master_template=$this->page->load_page_template($template_to_load);

		// if admin type user, and we're not already editing, add the CMS toolbar to the content if its specified in CONFIG
		if (($user->value("type")=="superadmin" || $user->value("type")=="administrator" || $user->value("type")=="master") && !$pageEdit && $CONFIG['use_client_side_cms']){
			$content=inline_cms::get_cms_page_link($_SERVER['QUERY_STRING'],$content);
		}

		// these 3 lines need to me moved to a module
		if ($_GET['action']=="survey_response"){ $title="Survey response recorded"; $content = survey_response();}
		if ($_GET['action']=="survey_results"){ $title="Survey results"; $content = survey_results();  }
		if ($_GET['action']=="survey_comments_response"){ $title="Thanks for your comments "; $content = survey_comments_response();  }

		// Its time to print the page header. Search and replace on title, keywords and description and print it out
		// get content specific keywords
		if (!$browser_title){$browser_title=$title;}
		$page_header=str_replace("{=title}",$browser_title,$page_header);
		$page_header=str_replace("{=keywords}",$meta_keywords,$page_header);
		$page_header=str_replace("{=description}",$meta_description,$page_header);
		if (array_key_exists("HTTPS",$_SERVER) && $_SERVER['HTTPS']=="on"){
			$page_header=str_replace("http://","https://",$page_header);
			$page_header=str_replace("https://www.w3.org","http://www.w3.org",$page_header); // leave doctype and xmlns intact
		}
		print $page_header;

		$style_editor=0;
		if (array_key_exists("style_editor",$_REQUEST)){$style_editor=$_REQUEST['style_editor'];}
		if (array_key_exists("ajax_populate_dynamic_list",$_GET) && !$_GET['ajax_populate_dynamic_list']){
			if ($style_editor || !$style_editor){

			if ($current_site['http_path']){
				if ($_SERVER['HTTPS']=="on"){
					$current_site['http_path']=str_replace("http://","https://",$current_site['http_path']);
				}
			$mce_string ="<script language=\"javascript\" type=\"text/javascript\" src=\"".$current_site['http_path']."/tinymce_3_3_8/jscripts/tiny_mce/tiny_mce_gzip.js\"></script>";
			} else {
			$mce_string ="<script language=\"javascript\" type=\"text/javascript\" src=\"tinymce_3_3_8/jscripts/tiny_mce/tiny_mce_gzip.js\"></script>";
			}

			// The below is only required if we want tinymce to be present within the site scripts...
			$rte_on_site=$CONFIG['use_tinymce_on_front_end'];
			if($rte_on_site){
			$mce_string .="<script type=\"text/javascript\">\n";
			$mce_string .= "tinyMCE_GZ.init({\n";
			$mce_string .= "plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',\n";
			$mce_string .= "\nthemes : 'simple,advanced',\nlanguages : 'en',\ndisk_cache : true,\ndebug : false\n});\n";
			$mce_string .= "</script>\n<script language=\"javascript\" type=\"text/javascript\">tinyMCE.init({\n" . $CONFIG['tinymce_init_call_front_end'] . "\n});\n</script>";
			print $mce_string;
			}
			}
		}

		// # 14 - Are we displaying the content now? Then do so..
		if($display_content_now){
			if (!$update_results['return_content']){
				if ($pageEdit && $user->value("id") && $CONFIG['use_client_side_cms']){
					$content=inline_cms::wrap_content($_GET['content'],$content);
				}
			}
			$template=str_replace("{=title}",$title,$master_template);
			if (stristr($content,"{=status_message")) {
				$content=str_replace("{=status_message}",$status_message,$content);
				$template=str_replace("{=status_message}","",$template);
			} else {
				$template=str_replace("{=status_message}",$status_message,$template);
			}
			$template=str_replace("{=content}",$content,$template);
			$template=preg_replace("/{=menu:(\d+)}/e","build_menu_from_table(\${1})",$template); # MATT2021 - the e operator does not work in PHP 7- was /{=menu:(\d+)}/e - apparrently should use preg_replace_callback 
			$template=str_replace("{=menu}",$menu_data,$template);

			if (stristr($_SERVER['HTTP_USER_AGENT'],"MSIE 6")){
				$template=preg_replace("/images\/(\w+).png/i","images/ie6_gifs/\\1.gif",$template);
			}

			// https check - replace http with https everywhere
			if (array_key_exists("HTTPS",$_SERVER) && $_SERVER['HTTPS']=="on"){
				$template=preg_replace("/http:\/\//","https://",$template);
				$content=preg_replace("/http:\/\//","https://",$content);
				$template=preg_replace("/https:\/\/i3.ytimg/","http://i3.ytimg",$template);
				$content=preg_replace("/https:\/\/i3.ytimg/","http://i3.ytimg",$content);
			}

			if (!$jx){ print $template; } else {print $content; }
			check_for_google_analytics($_REQUEST['s']);
			print $this->page->load_footer($current_site['default_footer']);
			//var_dump($this->('content'));
			return;
			exit; // This is the only early exit point
		}

		// basic content will have been printed at this point. Advanced content is up next

		// If we have a login result, print it (it's an error at this time as it hasnt redirected);
		if ($login_result){$title="Log in was unsuccessful"; $content="<font color='#cc0000'>".$login_result."</font></b>";}

		// User Logic - all this to change to returning content instead of printing direct as it should fit into the template
		// mail password screen if forgotten password
		if ($_GET['action']=="mail_password_front"){ $title = "Mail My Password"; $content = $user->mail_password_front(); }
		if ($_GET['action']=="mail_password"){ $title = "Password Mailed"; $content = $user->mail_password(); }
		if ($_GET['action']=="reset_password"){ $title = "Reset Password"; $content = $user->reset_password_generate($_POST['email_address']); }
		if ($_GET['action']=="reset_password_confirm"){ $title = "Reset Password"; $content = $user->reset_password_confirm($_GET['uuid']); }
		if ($_GET['action']=="reset_password_complete"){ $title = "Reset Password"; $content = $user->reset_password_complete(); }
		if ($_GET['action']=="show_cookies"){ $title = "Showing all cookies for the current session"; $content = get_cookie_data(); }
		if ($_GET['action']=="reset_cookies"){ $title = "Reset Cookies"; $content = reset_cookies(); }
		if ($_GET['action']=="mail_new_password_front"){ $title = "Mail My Password"; $content = $user->mail_new_password_front(); }
		if ($_GET['action']=="mail_new_password"){ $title = "Password Mailed"; $content = $user->mail_new_password(); }
		// edit user details
		if ($_GET['action']=="edit_user_details"){ edit_user_details(); } // still to be written??!
		if ($_GET['action']=="dynamic_site_search"){ site_search_list(); exit;}
		if ($_GET['action']=="site_search_list"){ site_search_list(); exit;}

		# check action against URL aliases for DBF options
		$sql="SELECT * from url_aliases";
		$sql_result=$db->query($sql);
		while ($rows=$db->fetch_array($sql_result)){
			if ($rows['query_string_variable']==$_REQUEST['action']){
				$params=$rows['virtual_query_string'];
			}
		}
		if ($params){
			$url_aliased=1;
			$param_pairs=explode("&",$params);
			foreach ($param_pairs as $param_pair){
				$param_vars=explode("=",$param_pair);
				$$param_vars[0]=$param_vars[1];
			}
			$aliased_table=$t;
		}

		# dbf stuff in here for now
		$action=$_REQUEST['action'];
		if ($action=="list_table" && $url_aliased){
			if ($filter_id){
				require_once(LIBPATH . "/classes/core/filters.php");
				$db_filter=new filter();
				$dbforms_options=$db_filter->load_options($filter_id); // NB this now loads filter as well
			}
			if ($dbforms_options['filter']['title_text']){$title=$dbforms_options['filter']['title_text'];}
			$content=database_functions::list_table($aliased_table,$dbforms_options);	
		} elseif ($action=="list_table" && !$url_aliased && $_REQUEST['filter_id']){
			require_once(LIBPATH . "/classes/core/filters.php");
			$db_filter=new filter();
			//$dbforms_options=load_dbforms_options();
			$dbforms_options=$db_filter->load_options(); // NB this now loads filter as well
			if ($dbforms_options['filter']['title_text']){$title=$dbforms_options['filter']['title_text'];}
			if ($dbforms_options['filter']['title_on_page']){$title=Codeparser::parse_request_vars($dbforms_options['filter']['title_on_page']);}
			if ($dbforms_options['filter']['master_template']){$master_template=$this->page->load_page_template($dbforms_options['filter']['master_template']); } 
			$tablename=$_REQUEST['t'];
			if (!$dbforms_options['filter']['display_in_template'] && !$dbforms_options['filter']['export'] && !$dbforms_options['filter']['display_raw']){
				$content = "Nothing to display (2)";
				$title = "Access Denied";
			} else {
				$content = database_functions::list_table($tablename,$dbforms_options);	
			}
		} elseif ($action=="list_table" && !$_REQUEST['filter_id']){
			$title = "Access Denied";
			$content = "Nothing to display";
		}

		if ($action=="edit_table"){
			if ($filter_id){
				require_once(LIBPATH . "/classes/core/filters.php");
				$edit_filter=new filter();
				$dbforms_options=$edit_filter->load_options($filter_id); // NB this now loads filter as well
			}
			if ($dbforms_options['filter']['title_text']){$title=$dbforms_options['filter']['title_text'];}
			if (!$dbforms_options['filter']['display_in_template'] && !$dbforms_options['filter']['export'] && !$dbforms_options['filter']['display_raw']){
				$dbforms_options['filter']['export']="html";
			}
			$content=database_functions::form_from_table($_REQUEST['t'],$_REQUEST['edit_type'],$_REQUEST['rowid'],$_REQUEST['add_data'],$dbforms_options);
		}

		// file manager and uploads
		if ($_GET['action']=="file_browser"){
			ob_start();
			$directory = ($_REQUEST['d'])? $_REQUEST['d'] : "";
			// get list type
			if ($directory){
				$list_type_sql="SELECT list_type,default_no_per_page from file_manager where directory = \"" . $directory . "\"";
				$list_type_result = $db->query($list_type_sql) or die ("ERROR in file browser:" . $db->db_error());
				while ($h=$db->fetch_array($list_type_result)){
					$list_type = $h['list_type'];
					$default_no_per_page= $h['default_no_per_page'];
				}
			}
			if (!$list_type){$list_type='list';}
			if (!$default_no_per_page){$default_no_per_page='12';}
			$list_dir_options['display_uploader']=0;
			$list_dir_options['thumbnail_directory']=$directory . "_thumbs";
			$list_dir_options['thumbnail_width']=200;
			$list_dir_options['next_page']="<img src='images/next_page.gif' border=0>";
			$list_dir_options['previous_page']="<img src='images/previous_page.gif' border=0>";
			if ($_REQUEST['fileint']){
				$sql = "SELECT * from file_manager_options where interface = '" . $_REQUEST['fileint'] . "'";
				$res=$db->query($sql);
				while ($h=$db->fetch_array($res)){
					$list_dir_options[$h['file_manager_option']] = $h['value'];
					if ($h['file_manager_option']=="list_type"){$list_type=$h['value'];}
				}
			}
			file_manager_main($directory,$list_type,$_REQUEST['display_options'],$_REQUEST['options_position'],$default_no_per_page,$list_dir_options);
		$content .= ob_get_contents();
		ob_end_clean();
		$content .= "</div>";
		}

		// assuming we havent already displayed the home page and have used some of the more advanced options, do it now
		if (!$display_content_now){
			print "<!-- not displaying in a sec //-->";
			if ($pageEdit){
				$content=wrap_content($_GET['content'],$content);
			}
			if (!$title){
				$title = $dbforms_options['filter']['main_page_title'];
			}
			$template=str_replace("{=title}",$title,$master_template);
			$template=str_replace("{=content}",$content,$template);
			$template=str_replace("{=content2}",$content2,$template);
			$template=str_replace("{=content3}",$content3,$template);
			$template=str_replace("{=content4}",$content4,$template);
			$template=str_replace("{=content5}",$content5,$template);
			$template=str_replace("{=content6}",$content6,$template);
			$template=str_replace("{=status_message}",$status_message,$template);
			$template=preg_replace("/{=menu:(\d+)}/ex","build_menu_from_table(\${1})",$template);
			$template=str_replace("{=menu}",$menu_data,$template);

			// https check
			if ($_SERVER['HTTPS']=="on"){
				$template=preg_replace("/http:/","https:",$template);
				$content=preg_replace("/http:/","https:",$content);
			}
			if (!$jx){ print $template; } else {print $content; }
		}
		//$time_end=microtime_float();
		//print "<p>Loaded in " . (float)($time_end-$time_start)/100000 . " seconds</p>";

		check_for_google_analytics($_REQUEST['s']);
		print $this->page->load_footer($current_site['default_footer']);
	}
}

	function check_for_google_analytics($site_id){
		if (!$site_id){ $site_id=1; } // Record 1 is always the default:w
		global $db;
		$sql = "SELECT * from google_analytics_code WHERE site_id = $site_id";
		$res=$db->query($sql);
		$GA_Code="";
		while ($h=$db->fetch_array($res)){
			$GA_Code=$h['code'];
		}
		if ($GA_Code){print $GA_Code;}

	}
?>
