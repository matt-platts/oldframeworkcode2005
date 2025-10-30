<?php

/* CLASS: page
 * Meta: All pages are based on an instance of the pace class.
 *
*/
class page {

	private $content_table;
	private $templates_table;
	private $admin_templates_table;
	
	private $script_action; // every request has an action as part of the routing
	private $useAjax; // boolean - page is requested by ajax and does not require headers and footers
	private $pureAjax; // boolean - page is a pure ajax request and should return data only - no templating
	private $iframeMode; // boolean
	private $mui; // boolean - admin is running in the MocahUI interface - automatically defaults to $useAjax=1, $iframeMode=1, $perpetuate_ajax_iframe_mode=1;
	private $perpetuate_ajax_iframe_mode; // boolean - keep all future requests as ajax
	private $inlinePopup; // boolean - this is using a popup window so is essentially an ajax request - used to differentiate in other places
	private $exportExcel; // boolean - recordset data is being exported as excel format and not html to the browser
	private $view; // the file name for the view in which to show this request 
	

	function __construct(){

		$path = parse_url($_SERVER['REQUEST_URI']);
		$urlParts=explode("/",$path['path']);

		$this->content_table = "content";
		$this->templates_table = "templates";
		$this->admin_templates_table = "admin_templates";

		$this->script_action=$_GET['action'];
		$this->useAjax=$_REQUEST['jx'];
		$this->inlinePopup=$_REQUEST['ipopup'];
		$this->iframeMode=$_REQUEST['iframe'];
		$this->pureAjax=$_REQUEST['pureAjax'];
		if ($_REQUEST['subform_mode']){$this->useAjax=1; $this->iframeMode=1; $this->subform_mode=1;}
		// below only relevent if perpetuating the ajax call - set by mootools
		if ($_SERVER['HTTP_X_REQUESTED_WITH']){ $this->useAjax=1; }

		$this->exportExcel = ($_REQUEST['dbf_output_type']=="excel") ? 1 : 0;
		$this->htmlPageTitle="Welcome To The Administrator";
		if ($_POST['perpetuate_ajax_iframe_mode']=="1"){
			$this->useAjax=1;
			$this->iframeMode=1;
			$this->perpetuate_ajax_iframe_mode=1;
		}
		if ($_REQUEST['dbf_mui'] || stristr($_SERVER['PHP_SELF'],"ui-administrator.php") || in_array("mui",$urlParts)){ // mocha ui interface
			$this->useAjax=1;
			$this->iframeMode=1;
			$this->mui=1;
			$this->perpetuate_ajax_iframe_mode=1;
		}
		$this->content=null;
		$this->view=null;
	}

	function value($of){
		return $this->$of;
	}

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }
	
	function set_global_var($of,$to){
		if (!$this->global_vars){ $global_vars=array(); }
		$to=Codeparser::parse_request_vars($to);	
		$this->global_vars[$of]=$to;
	}

	function get_global_var($of){
		return $this->global_vars[$of];
	}

	// One problem in the function below with replacing hrefs, if the value is a # it replaces hashes everywhere which may interfere with other code
	// need to check the match is surrounded by the same characters on both sides most likely?
	function load_page_template($template_id){
		global $db;
		$template_content = $db->field_from_record_from_id($this->value("templates_table"),$template_id,"template","type='Master'");
		$template_content= Codeparser::code_tags($template_content);
		$template_content = $this->load_template_widgets($template_content);
		global $current_site;
		
		/*
		DEPRECATED - left in as comment 'just in case any old site requires it'
		preg_match_all("/href\s?=\s?\"(.*?)\"/",$template_content,$matches);
		if ($current_site['http_path']){
			$http_path=trim($current_site['http_path']);
			foreach ($matches[1] as $each_match){
				if (!$each_match){continue;} // blank anchor tags do exist!
				$each_match=str_replace("/","\/",$each_match); //LSCMOD
				if (!preg_match("/^http:\/\//",$each_match) && !preg_match("/^Javascript/",$each_match) && !preg_match("/^mailto/",$each_match) && !preg_match("/^\//",$each_match)){
				//	print "replacing <pre>$each_match</pre> with <pre>$http_path/$each_match</pre>";
					$template_content=preg_replace("/\"$each_match/","\"$http_path/$each_match",$template_content);
					$template_content=preg_replace("/'$each_match/","'$http_path/$each_match",$template_content);
				} else {
					//print "not replacing <pre>$each_match</pre>";
				}
			}
		}*/ 
	
		$template_content=str_replace("{=search_query}",$_GET['dbf_search_for'],$template_content);
		return $template_content;
	}

	/* 
	 * Function: load_template_widgets
	 * Meta: template widgets are areas in a template which may load further dynamic content as well as the main page
	 * Param: content (string);
	 * Param: exported_vars (mixed) - associative array of vars and values
	 * Returns: content as sent in with the widgets
	 */
	function load_template_widgets($sent_content,$exported_vars){
		$original_content=$sent_content;
		$sent_content=$this->load_template_widgets_from_img_syntax($sent_content);
		$widgets=preg_match_all("/{=widget:(.*)?}/",$sent_content,$matches);
		$widget_counter=0;
		foreach ($matches[0] as $each_match){
			$cancel_widget=0;
			$widget_pairs=$matches[1][$widget_counter];
			$widget_pairs=str_replace("&amp;","&",$widget_pairs);
			$widget_pairs=explode("&",$widget_pairs);
			foreach ($widget_pairs as $widget_pair){
				$widget_bits=explode("=",$widget_pair);
				if ($widget_bits[0]=="table" || $widget_bits[0]=="t"){
					$table=$widget_bits[1];
				} else if ($widget_bits[0]=="field") {
					$field=$widget_bits[1];
				} else {
					$id=$widget_bits[1];
					$id_field=$widget_bits[0];
				}
				//if ($widget_bits[0] != "table" && $widget_bits[0] != "id" && $widget_bits[0] != "field"){ print "Error on bit ". $widget_bits[0]; exit;}
			}
			
			if ($table=="content"){
				$widget=$this->content_from_id($id);	
			} else {
				global $db;
				$get_widget_sql="SELECT $field FROM $table WHERE $id_field = $id";
				if ($db->field_exists_in_table($table,"requires_login")){
					$get_widget_sql="SELECT $field,requires_login FROM $table WHERE $id_field = $id";
					global $user;
				}
				$get_widget_rv=$db->query($get_widget_sql);
				$get_widget_h=$db->fetch_array($get_widget_rv);
				$widget_content=$get_widget_h[$field];
				$widget_login_status=$get_widget_h['requires_login'];
				if ($widget_login_status && !$user->value("id")){
					$cancel_widget=1;
					//print "<!--set cancel widget to 1 on $id //-->";
				}
				if (preg_match("/\[=variable:\w+\]/",$widget_content)){
					$vars=preg_match_all("/\[=variable:\w+\]/",$widget_content,$varmatches);
					foreach ($varmatches[0] as $each_var_match){
						$original_var_match=$each_var_match;
						$each_var_match=str_replace("[=variable:","",$each_var_match);
						$each_var_match=str_replace("]","",$each_var_match);
						if ($exported_vars[$each_var_match]){
							$widget_content=str_replace($original_var_match,$exported_vars[$each_var_match],$widget_content);
						}
					}

				}
				if ($cancel_widget){
					$widget="";
				} else {
					$widget=Codeparser::code_tags($widget_content);
				}
				//$widget=Codeparser::code_tags($db->field_from_record_from_id($table,$id,$field));	
			}
			$sent_content=str_replace($each_match,$widget,$sent_content);
			$widget_counter++;
		}
		return $sent_content;
	}

	/* 
	 * Function: load_template_widgets_from_img_syntax
	 * Meta: template widgets are areas in a template which may load further dynamic content as well as the main page.
	 *       -> This function is the same as above but runs on embedded images in the template rather than pure code
	 * Param: content (string);
	 * Param: exported_vars (mixed) - associative array of vars and values
	 * Returns: content as sent in with the widgets
	 */
	function load_template_widgets_from_img_syntax($sent_content){
		$widgets=preg_match_all("/<img src=\"\/widgets\/.*\/\d+\/\d+\/\d+\/\d+\/template_embed.c\" border=\"0\" width=\"\d+\" height=\"\d+\" rel=\"t:(.*);f:(.*);(\w+):(.*)\" \/>/",$sent_content,$matches); 
		$widget_counter=0;
		foreach ($matches[0] as $each_match){
			$cancel_widget=0;
			$table=$matches[1][$widget_counter];
			$field=$matches[2][$widget_counter];
			$id_field=$matches[3][$widget_counter];
			$id=$matches[4][$widget_counter];	
			
			if ($table=="content"){
				$widget=$this->content_from_id($id);	
			} else {
				global $db;
				$get_widget_sql="SELECT $field FROM $table WHERE $id_field = $id";
				if ($db->field_exists_in_table($table,"requires_login")){
					$get_widget_sql="SELECT $field,requires_login FROM $table WHERE $id_field = $id";
					global $user;
				}
				$get_widget_rv=$db->query($get_widget_sql);
				$get_widget_h=$db->fetch_array($get_widget_rv);
				$widget_content=$get_widget_h[$field];
				$widget_login_status=$get_widget_h['requires_login'];
				if ($widget_login_status && !$user->value("id")){
					$cancel_widget=1;
					print "<!--set cancel widget to 1 on $id //-->";
				}
				if (preg_match("/\[=variable:\w+\]/",$widget_content)){
					$vars=preg_match_all("/\[=variable:\w+\]/",$widget_content,$varmatches);
					foreach ($varmatches[0] as $each_var_match){
						$original_var_match=$each_var_match;
						$each_var_match=str_replace("[=variable:","",$each_var_match);
						$each_var_match=str_replace("]","",$each_var_match);
						if ($exported_vars[$each_var_match]){
							$widget_content=str_replace($original_var_match,$exported_vars[$each_var_match],$widget_content);
						}
					}

				}
				if ($cancel_widget){
					$widget="";
				} else {
					$widget=Codeparser::code_tags($widget_content);
				}
				//$widget=Codeparser::code_tags($db->field_from_record_from_id($table,$id,$field));	
			}
			$sent_content=str_replace($each_match,$widget,$sent_content);
			$widget_counter++;
		}
		return $sent_content;
	}

	/*
	 * Function: get_appended_template_files
	 * Meta: For if a page of content should have the contents of a file (from the filesystem) appended to it 
	*/
	function get_appended_template_files($template_content){ // this function is called from database_functions for a form embedded in a template
		// BUT WHERE? doesnt seem to be called from any current version
		$appended_files=preg_match_all("/{=APPEND_FILE:(.*)?}/",$template_content,$matches);
		foreach ($matches[0] as $each_match){
			$append_file_match=$each_match;
			$append_file=$matches[1][0];
		}
		if ($append_file){
			$append_file = "system/custom/append_files/" . $append_file;
			if (!file_exists($append_file)){print "Appended file does not exist"; exit;}
			$appendage = file_get_contents($append_file);
			$return_content .= preg_replace("/$append_file_match/",$appendage,$template_content);
		}
		if ($return_content){ return $return_content;} else {return $template_content;}
	}

	/* 
	 * Function: display_admin_content_by_key_name
	 * Meta: Returns a page of content for the admin, based on the key_name field in the database
	*/
	function display_admin_content_by_key_name($key_name,$replace_vars){
		global $db;
		// IF KEY NAME IS DIFFERENT, DO SOMETH(ING FOR ADMIN HOME PAGE!
		global $user;
		if ($key_name=="admin_home_mui"){
			$homepage_sql="SELECT admin_home_page_key from user_desktops WHERE user = " . $user->value("id");
			$homepage_rv=$db->query($homepage_sql);
			$homepage_h=$db->fetch_array($homepage_rv);
			if ($homepage_h['admin_home_page_key']){
				$key_name=$homepage_h['admin_home_page_key'];
			}
		}
		if ($key_name=="admin_home_page_mui"){ $key_name="admin_home_mui";}
		$sql="SELECT content FROM admin_pages WHERE dbf_key_name = \"$key_name\"";
		$rv=$db->query($sql);
		$h=$db->fetch_array($rv);
		$admin_content=$h['content'];
		if ($replace_vars){
			if ($replace_vars['output_from_after_update_run_code']){
				$admin_content=str_replace("{=output_from_after_update_run_code}",$replace_vars['output_from_after_update_run_code'],$admin_content);
				$admin_content=str_replace("{=status_message}",$replace_vars['status_message'],$admin_content);
				$admin_content=str_replace("{=last_insert_id}",$replace_vars['last_insert_id'],$admin_content);
			}
		}
		$admin_content=Codeparser::global_vars($admin_content);
		$admin_content=Codeparser::code_tags($admin_content);
		return $admin_content; 
	}

	function admin_content_from_id($content_id){

	}

	/*
	 * Function: content_from_title
	 * Meta: loads in an item of content based on the title instead of an id. Used for friendly url mapping to pages rather than passing in ids.
	*/
	function content_from_title($title){
		global $db;
		$title=basename($title);
		$original_title=$title;
		$title=str_replace("---"," # ",$title);
		$title=str_replace("_-_"," # ",$title);
		$title=str_replace("_"," ",$title);
		$title=str_replace("-"," ",$title);
		$title=str_replace("#","-",$title);
		$default_template="";
		$sql="SELECT id,default_template FROM " . $this->value("content_table") . " WHERE title = \"$title\" AND allow_access_by_title=1";
		$rv=$db->query($sql);
		$h=$db->fetch_array();
		if ($h['id']){
			$content=$this->content_from_id($h['id']);
			$default_template=$h['default_template'];
		} else { 
			if (stristr($original_title,"-")){
				$title_bits=explode("-",$original_title);
				$title_bits_2=explode("_",$title_bits[0]);
				$secondary_sql="SELECT id,default_template FROM " . $this->value("content_table") . " WHERE title LIKE \"".$title_bits_2[0]."%\" AND allow_access_by_title=1";
				$secondary_rv=$db->query($secondary_sql);
				while ($h=$db->fetch_array()){
					$got=1;
					$content=$this->content_from_id($h['id']);
					$default_template=$h['default_template'];
				}
			}
			if (!$got){
				$content .= $this->content_from_id(272);
			}
		}
		if ($default_template){ $this->set_value("override_template_from_content",$default_template);  }
		return $content;
	}

	/* 
	 * Function: content_from_id
	 * Meta: Loads an item of content from the database based on it's id
	*/
	function content_from_id($content_id,$appended_files,$replace_vars){
		if ($debug){
			print "<p><hr size=1></p>";
			print "<p>Just reached content_from_id</p>";
			$trace=debug_backtrace();
			echo 'called by '.$trace[0]['file'].' line ' . $trace[0]['line'];
			var_dump($trace[0]['args']);
			print "<p><hr size=1></p>";
		}

		if (!preg_match("/^\d+$/",$content_id)){return format_error("ERROR: INVALID CONTENT ID of $content_id at (1)",1);}
		global $db;
		if ($_GET['pageEdit']){$appended_files=0;}
		if (!$content_id){$content_id=1;}
		# check login status first
		$sql="SELECT value,javascript,append_file,requires_login FROM {$this->content_table} WHERE id = " . $content_id;

		$result=$db->query($sql);
		while ($row=$db->fetch_array($result)){
			if ($row['requires_login']){
				# do we have a content login page?
				global $current_site;
				global $user;
				if ($current_site['content_page_for_login'] && !$user->value("id")){
					if (!$_SESSION){session_start();}
					$_SESSION['direct_login_to']=$content_id;
					$_SESSION['extra_login_message']="You must be logged in to view this page";
					$content_id=$current_site['content_page_for_login'];
				}
			}
		}
		$result=$db->query($sql);
		while ($row=$db->fetch_array($result)){
			//$requires_login=$row['requires_login'];
			$return_content= $row['value'] . "\n" . $row['javascript'];
			$append_file = $row['append_file'];
		}
		if ($db->num_rows($result)==0){
			return format_error("No Content Found ($content_id)");
		}
		/*
		global $user;
		if ($requires_login && !$user->value("id")){
			return format_error("You must be logged in to view this content");
		}
		*/

		$return_content=str_replace("{=extra_login_message}",$_SESSION['extra_login_message'],$return_content);
		$return_content=str_replace("{=direct_login_to}",$_SESSION['direct_login_to'],$return_content);
		if ($direct_login_to==$content_id){
			 unset($_SESSION['direct_login_to']);
			 unset($_SESSION['extra_login_message']);
		 }
			 
		// now this is used for things such as forms which contain textareas and therefore cannot be displayed in the admin screens. Also useful to provide uneditable content
		if ($append_file && $appended_files){ 
			$append_file = "system/custom/append_files/" . $append_file;
			$appendage = file_get_contents($append_file);	
			$return_content .= $appendage;	

		}
		if (strpos($return_content,"{=include:")){
			$split_content=explode("{=include:",$return_content);
			$second_split=explode("}",$split_content[1]);
			$append_file=$second_split[0];
			$append_file_inc_path="system/custom/append_files/" . $append_file;
			$appendage=file_get_contents($append_file_inc_path);
			$string_to_replace="{=include:".$append_file."}";
			$return_content=str_replace($string_to_replace,$appendage,$return_content);
		}
		
		// check for custom PHP scripts
		// this works by calling ob_start, running the script, loading the ob into a variable and replacing the code with the variable
		if (strpos($return_content,"{=PHP:")){
			$split_content=explode("{=PHP:",$return_content);
			$second_split=explode("}",$split_content[1]);
			$php_script=$second_split[0];
			$php_script="system/custom/".trim($php_script);
			$original_php="{=PHP:".$second_split[0]."}";
			if (file_exists($php_script)){
				ob_start();
				require_once($php_script);
				$embed_external_output=ob_get_contents();
				ob_end_clean();
			} else {
				$embed_external_output="<p class=\"dbf_para_alert\">Content cannot be displayed from php_script - file not found.</p>";
			}
			$return_content=str_replace($original_php,$embed_external_output,$return_content);
		}

		if (strpos($return_content,"{=EXEC:")){
			$split_content=explode("{=EXEC:",$return_content);
			$second_split=explode("}",$split_content[1]);
			$php_script=$second_split[0];
			$php_script="system/custom/".$php_script;
			$original_php="{=EXEC:".$second_split[0]."}";
			//ob_start();
			$sysresult = exec("php $php_script",$embed_external_output);
			$embed_external_output = join("",$embed_external_output);
			//$embed_external_output=ob_get_contents();
			//ob_end_clean();
			$return_content=str_replace($original_php,$embed_external_output,$return_content);
		}

		if (strpos($return_content,"{=recordset")){
			// means we load a templated widget thingy!
			$split_content=explode("{=recordset:",$return_content);
			$second_split=explode("}",$split_content[1]);
			$recordset_data=$second_split[0];
			$recordset_pairs=explode("&",$recordset_data);
		}

		if ($replace_vars){
			if ($replace_vars['output_from_after_update_run_code']){
				$return_content=str_replace("{=output_from_after_update_run_code}",$replace_vars['output_from_after_update_run_code'],$return_content);
			}
		}

		global $current_site;
		preg_match_all("/href\s?=\s?\"(.*?)\"/",$return_content,$matches);
		$debug=0;
		if ($debug){ var_dump($matches[1]);}
		if ($current_site['http_path']){
			$http_path=trim($current_site['http_path']);
			foreach ($matches[1] as $each_match){
				if (!$each_match){continue;}
				if ($debug){ print "<p>on $each_match<br />";}
				if (!preg_match("/^http:/",$each_match) && !preg_match("/^javascript:/i",$each_match) && !preg_match("/^{=/",$each_match) && !preg_match("/^mailto:/",$each_match) && !preg_match("/^#/",$each_match)){
					$each_match_actual=str_replace("/","\/",$each_match);
					$each_match_actual_double_quotes="\"" . $each_match_actual;
					$each_match_actual_single_quotes="'" . $each_match_actual;
					$return_content=str_replace($each_match_actual_double_quotes,"\"$http_path/$each_match",$return_content);
					$return_content=str_replace($each_match_actual_single_quotes,"'$http_path/$each_match",$return_content);
					if ($debug){print "changing $each_match_actual by adding $http_path ";}
				} else {
					if ($debug){ print "NOT changing $each_match by adding $http_path ";}
				}
			}
		}
		
		$return_content=Codeparser::global_vars($return_content);
		$return_content=Codeparser::code_tags($return_content);
		//print "heres pre eval <textarea>$return_content</textarea>";
		if ($this->value("breadcrumb_navigation")){
			$replace_string=$this->value("breadcrumb_navigation");
			$replace_string=preg_replace("/<p.*?>/","",$replace_string);
			$replace_string=str_replace("/<\/p>/","",$replace_string);
			$return_content=str_replace("{=breadcrumb_navigation}",$replace_string,$return_content);
		}
		return $return_content;
	}


	/*
	 * Function: dynamic_seo_lookup
	 * Meta: Titles, keywords and descriptions may be based on dynamic content from a particular record, rather than the page itself.
	 *       This function takes in a custom tag explaining what to look up and where, and which section (title, description etc) to apply it to. 
	 * Param: $seo_in (string) - the tag containing the lookup variables which are broken out in order to do the lookup.
	 * Param: $seo_section (string) - description, keywords, title
	 * Returns: The value for the description, keywords or title
	*/
	function dynamic_seo_lookup($seo_in,$seo_section){
		$final_seo_out=$seo_in;
		$m=preg_match_all("/{=lookup:[\w+:_,-]+}?/",$seo_in,$matches);
		foreach ($matches[0] as $eachmatch){
			$each_original_match=$eachmatch;
			$eachmatch=preg_replace("/.*{=lookup:/","",$eachmatch);
			$eachmatch=preg_replace("/}.*?/","",$eachmatch);
			@list($table,$field,$pk_val)=explode(":",$eachmatch);
			$fields=explode(",",$field);
			$pk_val=preg_replace("/ .*/","",$pk_val);
			if (!$_GET[$pk_val]){ return $seo_in;}
			if ($_GET[$pk_val]){
				// Get the registered filter so we can do a lookup in case a field is linked in a registered filter (only works in a registered filter)
				$registered_filter_check=database_functions::filter_registered_on_table($table,"list_table");
				if ($registered_filter_check){
					$registered_filter=database_functions::load_dbforms_filter($registered_filter_check);
				}
				$pk=get_primary_key($table);
				$sqlquery="SELECT $field FROM $table WHERE $pk = ".$db->db_escape($_GET[$pk_val])."";
				global $db;
				$res=$db->query($sqlquery) or die(->db_error());
				$seo_out="";
				while ($h=$db->fetch_array($res)){
					foreach ($fields as $field){
						if ($registered_filter[$field]['select_value_list']){
							$h[$field]=database_functions::sql_value_from_id($registered_filter[$field]['select_value_list'],$h[$field]);
						}
						$seo_out .= strip_tags($h[$field]) . " - ";
					}
				}
				if ($seo_section=="keywords"){
					$seo_out=str_replace(",","",$seo_out);
					$seo_out=str_replace(".","",$seo_out);
					$seo_words=explode(" ",$seo_out);
					foreach ($seo_words as $seo_word){
						if (strlen($seo_word)>=3){
							$new_seo_out .= $seo_word . " ";
						}	
					}
				$seo_out=trim($new_seo_out);
				}
				$seo_out=substr($seo_out,0,160);
				$seo_out=preg_replace("/{=lookup:.*}/",$seo_out,$each_original_match);
			} else {
				$seo_out="";
			}
			$seo_out=preg_replace("/ - $/","",$seo_out);
			if ($seo_section=="keywords"){
				$seo_out=str_replace(" - "," ",$seo_out);
			}
			$final_seo_out = str_replace($each_original_match,$seo_out,$final_seo_out);
		}
		$final_seo_out=str_replace("&quot;","'",$final_seo_out);
		$final_seo_out=str_replace("\"","'",$final_seo_out);
		return str_replace("  "," ",$final_seo_out);
	}

	/*
	 * Function: meta_description_from_id
	 * Meta: get the meta description of the content from the content id
	 * Param: $content_id (int) - id of a content item
	 * Returns: string
	*/
	function meta_description_from_id($content_id){
		global $db;
		if (!$content_id){return;}
		$return_desc="";
		$sql="SELECT description from content where id = " . $content_id;
		$result=$db->query($sql);
		while ($row=$db->fetch_array()){
			$return_desc= $row['description'];
		}
		if (stristr($return_desc,"=lookup:")){
			$return_desc=$this->dynamic_seo_lookup($return_desc,"description");
		}
		if (!$return_desc){
			global $current_site;
			$return_desc=$current_site['default_meta_description'];
		}
		$return_desc=Codeparser::parse_request_vars($return_desc);
		return $return_desc;
	}

	/*
	 * Function: keywords_from_id
	 * Meta: get the meta description of the content from the content id
	 * Param: $content_id (int) - id of a content item
	 * Returns: string
	*/
	function keywords_from_id($content_id){ // returns the meta keywords from a content id
		global $db;
		if (!$content_id){return;}
		$sql="SELECT keywords from content where id = " . $content_id;
		$result=$db->query($sql);
		while ($row=$db->fetch_array()){
			$return_keywords = $row['keywords'];
		}
		if (stristr($return_keywords,"=lookup:")){
			$return_keywords=$this->dynamic_seo_lookup($return_keywords,"keywords");
		}
		if (!$return_keywords){
			global $current_site;
			$return_keywords=$current_site['default_meta_keywords'];
		}
		$return_keywords=Codeparser::parse_request_vars($return_keywords);	
		return $return_keywords;
	}

	/* 
	 * Function: title_from_id
	 * Param: content_id - id of an item of content from the content table
	 * Returns: string - the title of the content item which should be displayed - this relates to the on-page title and not the browser title tag.
	*/
	function title_from_id($content_id){
		if (!preg_match("/^\d+$/",$content_id)){return format_error("INVALID CONTENT ID at (2)",1);}
		global $db;
		if (!$content_id){$content_id=1;}
		$sql="SELECT title FROM {$this->content_table} WHERE id = " . $content_id;
		$result=$db->query($sql);
		while ($row=$db->fetch_array()){
			$return_title= Codeparser::parse_request_vars($row['title']);
		}
		return $return_title;
	}

	/* 
	 * Function: browser_title_from_id
	 * Param: content_id - id of an item of content from the content table
	 * Returns: string - the title of the content item that should be displayed in the <title> html tags
	*/
	function browser_title_from_id($content_id){
		if (!preg_match("/^\d+$/",$content_id)){return format_error("INVALID CONTENT ID at (3)",1);}
		global $db;
		if (!$content_id){$content_id=1;}
		$sql="SELECT browser_title from content where id = " . $content_id;
		$result=$db->query($sql);
		while ($row=$db->fetch_array()){
			$sent_content= $row['browser_title'];
		}
		if (stristr($sent_content,"=lookup:")){
			$sent_content=$this->dynamic_seo_lookup($sent_content);
		}
		$sent_content=Codeparser::parse_request_vars($sent_content);	
		return $sent_content;
	}

	/*
	 * Function: load_header
	 * Meta: Returns a page header from it's id
	 * Param: $header_id (int)
	 * Returns: string - the header html
	*/
	function load_header($header_id){
		global $db;
		$header_id=(int)$header_id;
		$header_content=$db->field_from_record_from_id("headers_and_footers",$header_id,"item_content");
		$style_editor=1;
		if (array_key_exists("style_editor",$_REQUEST)) {$style_editor=$_REQUEST['style_editor'];}
		if ($_GET['pageEdit'] || $_GET['action']=="edit_table" && $style_editor){ 
			global $CONFIG;
			// add tinymce init calls to the header..
			$edit_header .= '<script language="javascript" type="text/javascript" src="'.HTTP_PATH.'/tinymce3/jscripts/tiny_mce/tiny_mce_gzip.js"></script>';
			$edit_header .= "\n";
			$edit_header .= "<script type=\"text/javascript\">\n";
			$edit_header .= "tinyMCE_GZ.init({";
			$edit_header .= "\n";
			$edit_header .= "plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,ibrowser,imanager',\n";
			$edit_header .= "themes : 'simple,advanced',\n";
			$edit_header .= "languages : 'en',\n";
			$edit_header .= "disk_cache : true,\n";
			$edit_header .= "debug : false\n";
			$edit_header .= "});\n";
			$edit_header .= "</script>\n";
			$edit_header .= "";

			$edit_header .= '<script language="javascript" type="text/javascript">';
			$edit_header .= "\n";
			$edit_header .= "tinyMCE.init({ " . $CONFIG['tinymce_init_call_inline_cms'] . " });";
			$edit_header .= "\n";
			$edit_header .= "</script></head>";
			$header = preg_replace("~</head>~",$edit_header,$header_content);
		}

		global $current_site;
		if ($current_site['http_path']){
			// replace urls with the http path first
			$http_path=trim($current_site['http_path']);
			//$header_content=preg_replace("/href\s?=\s?\"/i","href=\"$http_path/",$header_content);
			//$header_content=preg_replace("/src\s?=\s?\"/i","src=\"$http_path/",$header_content);
		}
		$header_content=str_replace("{=HTTP_PATH}",HTTP_PATH,$header_content);
		return $header_content;
	}

	/* 
	 * Function: load_footer
	 * Meta: load a footer for the page based on it's own id
	 * Param: $footer_id (int)
	 * Returns: string - footer html
	*/
	function load_footer($footer_id){
		global $db;
		return $db->field_from_record_from_id("headers_and_footers",$footer_id,"item_content");
	}

	/*
	 * Function: style_editor_code
	 * Param: $editor_type
	 * Param: $tinymce_images_dir (string) - where to look for the images to load automatically into the tinymce images let
	*/
	function style_editor_code($editor_type,$tinymce_images_dir){

	if ($this->value("exportExcel")){ return; }
	$style_code = '<script language="javascript" type="text/javascript" src="'.HTTP_ROOT.'/tinymce_3_3_8/jscripts/tiny_mce/tiny_mce_gzip.js"></script>';
	$style_code .= <<<EOT
	<script type="text/javascript">
	tinyMCE_GZ.init({
	plugins : 'style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras',
	themes : 'simple,advanced',
	languages : 'en',
	disk_cache : true,
	debug : false
	});
	</script>
	<script language="javascript" type="text/javascript">
	tinyMCE.init({
	file_browser_callback : "ajaxfilemanager",
	EOT;
		global $CONFIG;
		$tinymce_init_call=$CONFIG['tinymce_init_call'];
		if ($editor_type=="document"){
			$tinymce_init_call=$CONFIG['tinymce_init_call_documents'];
		}
		if ($tinymce_images_dir){
			$tinymce_init_call=$CONFIG['tinymce_init_call'];
			$init_images_query=str_replace("/",":::::",$dbforms_options['filter']['tinymce_images_dir']);
			$tinymce_init_call=preg_replace("/tinymce_image_list.php/","tinymce_image_list.php?dirlist=$init_images_query","$tinymce_init_call");
		}
		$style_code .= $tinymce_init_call;
		$style_code .= "\n";
		$style_code .= "});";
		$style_code .= "\n\n";
	// ALERT! the ajaxfilemanagerurl in 2 places below needs to be relative to the local root!
	$style_code .= <<<EOT
			function ajaxfilemanager(field_name, url, type, win) {
				var ajaxfilemanagerurl = "/tinymce_3_3_8/jscripts/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php";
				var view = 'detail';
				switch (type) {
					case "image":
					view = 'thumbnail';
						break;
					case "media":
						break;
					case "flash":
						break;
					case "file":
						break;
					default:
						return false;
				}
		    tinyMCE.activeEditor.windowManager.open({
			url: "/tinymce_3_3_8/jscripts/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php?view=" + view,
			width: 782,
			height: 440,
			inline : "yes",
			close_previous : "no"
		    },{
			window : win,
			input : field_name
		    });

	/*            return false;
				var fileBrowserWindow = new Array();
				fileBrowserWindow["file"] = ajaxfilemanagerurl;
				fileBrowserWindow["title"] = "Ajax File Manager";
				fileBrowserWindow["width"] = "782";
				fileBrowserWindow["height"] = "440";
				fileBrowserWindow["close_previous"] = "no";
				tinyMCE.openWindow(fileBrowserWindow, {
				  window : win,
				  input : field_name,
				  resizable : "yes",
				  inline : "yes",
				  editor_id : tinyMCE.getWindowArg("editor_id")
				});

				return false;*/
			}
	EOT;

		$style_code .= "\n</script>\n";
		return $style_code;
	}

	function code_editor_code(){

		$editor_code = '<script language="javascript" type="text/javascript" src="'.HTTP_ROOT.'/tinymce_3_3_8/jscripts/editarea/editarea_0_8_1_1/edit_area/edit_area_compressor.php"></script>';
		return $editor_code;

	}

	function ajax_include_system_scripts(){
		$mootools_code = '<script language="javascript" type="text/javascript" src="'.HTTP_ROOT.'/scripts/mootools-1-2.js"></script>';
		$mootools_code .= "\n";
		$mootools_code = '<script language="javascript" type="text/javascript" src="'.HTTP_ROOT.'/scripts/mootools_ajax-1-2.js"></script>';
		$mootools_code .= "\n";
		return $mootools_code;	
	}

	function code_editor_new_record_init($table){
	?>
	<script language="javascript" type="text/javascript">
	editAreaLoader.init({
	<?php
		if ($table=="style_sheets"){
	?>
		id : "new_style_sheet"		// textarea id
		,syntax: "css"			// syntax to be uses for highgliting
		,start_highlight: true		// to display with highlight mode on start-up
		,font_size: 8
		,word_wrap: true

	<?php
		} elseif ($table=="headers_and_footers") {
		?>
		id : "new_item_content"		// textarea id
		,syntax: "html"			// syntax to be uses for highgliting
	<?php
		}
	?>
		,start_highlight: true		// to display with highlight mode on start-up
	});
	</script>

	<?php
	}

	function multibox_code_head(){
	$mbox = <<<EOT
	<!-- Start multibox stuff //-->
	<script type="text/javascript" src="/scripts/multibox/Lighter.js"></script>        
	<script type="text/javascript" src="/scripts/multibox/Fuel.css.js"></script>
	<script type="text/javascript" src="/scripts/multibox/Fuel.html.js"></script>
	<script type="text/javascript" src="/scripts/multibox/Fuel.js.js"></script>
	<script type="text/javascript">
			window.addEvent('domready', function(){
				$$('code').light({
					altLines: 'hover',
					path: 'lighter/',
					mode: 'ol',
					fuel: 'js',
					indent: 4
				});
			});
	</script>
	<script type="text/javascript" src="/scripts/multibox/multibox.js"></script>
	<script type="text/javascript" src="/scripts/multibox/multibox_Assets.js"></script>
	<script type="text/javascript" src="/scripts/multibox/multibox_overlay.js"></script>
	<!-- End multibox Stuff  in administrator.php //-->
	EOT;
	return $mbox;
	}

	function multibox_code_body(){
	$mbox = <<<EOT
	<link rel="stylesheet" type="text/css" href="/css/multibox.css" />
	<!--[if IE 6]>
	<link rel="stylesheet" href="css/multibox-ie6.css" type="text/css" media="screen" />
	<![endif]-->
	<script type="text/javascript"><!--

			window.addEvent('domready', function(){
				var box = new multiBox('mb', {
					overlay: new overlay()
				});

				var advanced = new multiBox('advanced', {
					overlay: new overlay(),
					descClassName: 'advancedDesc'
				});
			});
	//--></script>
	EOT;
	return $mbox;
	}

	function preview_div_code(){
	global $CONFIG;
	$quick_edit_tables=array();
	if ($CONFIG['quick_edit_tables']){
		$tablelist=explode(",",$CONFIG['quick_edit_tables']);
		foreach ($tablelist as $eachtable){
			@list($table,$field,$where)=explode(":",$eachtable);
			array_push($quick_edit_tables,"$table|$field");
		}
	}
	if ($_SESSION['quickeditmenutop'] && $_SESSION['quickeditmenuleft']){
		$qe_menu_top=$_SESSION['quickeditmenutop'];
		$qe_menu_left=$_SESSION['quickeditmenuleft'];
	} else {
		$qe_menu_top="0px";
		$qe_menu_left="0px";
	}
	$pd=<<<EOT
	<script type="text/javascript" src="scripts/mootools_ajax_admin.js"></script>
	<div id="previewDivision" name="previewDivision" style="float:right; border-width:1px; border-color:transparent; border-style:solid; background-color:#1b2c67; position:absolute; 
	EOT;
	$pd = preg_replace("/\n$/","",$pd);
	$pd .= "top: " . $qe_menu_top . "; left: " . $qe_menu_left . ";\">\n";
	$pd .= <<<EOT
	<form name="previewForm" action="#">
	<span style="background-color:#1b2c67; margin-top:0px; padding-top:1px; margin-bottom:0px; padding-bottom:3px; color:#fff;"> &nbsp; Quick Edit: </span>
	<select id="preview_selector" name="preview_selector" style="font-size:10px; width:150px; height:18px; border-width:0px; border-style:none; border:none; background-color:#1b2c67; color:#fff;">
	<option value="">Select:</option>
	EOT;
	foreach ($quick_edit_tables as $table){
		@list($tab,$field)=explode("|",$table);
		$pd .= "<option value=\"$table\" style=\"background-color:#fff; color:#1b2c67;\">".ucfirst($tab)."</option>\n";

	}
	$pd .= <<<EOT
	</select>
	</form>
	<div name="section_preview" id="section_preview" style="display:none"></div> 
	</div>
	EOT;
	return $pd;
	}

	function log_page(){
		global $CONFIG;
		if ($CONFIG['enable_front_end_page_logging']){
			$this->log_page=1;
			global $user;
			$uid=$user->value("id");
			$page_here= $_SERVER['REQUEST_URI'];
			if ($uid){
				global $db;
				$sql="INSERT INTO page_logs (user_id,url,note,view_time) VALUES ($uid,\"$page_here\",\"Front End Page Logs\",NOW())";
				$ret=$db->query($sql);
			}
			return 1;
		}

	}

	// END PAGE CLASS
	}

?>
