<?php

/* 
 * Dev note:  wrap must be redone to use a dbf form with form logging
*/

/*
 * CLASS: inline_cms
 * Meta: deals with the cms which appears on the front end pages to admins
*/
class inline_cms {

	/*
	 * Function wrap_content
	 * Meta: wraps the content item in a form and with a textarea which tinyMCE will convert to a rich text editor
	*/
	static function wrap_content($content_id,$content){
		global $content_table;
		global $CONFIG;
		global $user;
		if ($CONFIG['use_client_side_cms'] && $user->value("type") == "administrator" || $user->value("type") == "master" || $user->value("type") != "superadmimin"){ 
		$return_content = "<form action=\"" . $_SERVER['PHP_SELF'] . "?s=" . $_REQUEST['s'] . "&action=updateContent&content=" . $_REQUEST['content'] . "\" method=\"post\" enctype=\"multipart/form-data\"><textarea cols=".$CONFIG['inline_cms_textarea_cols']." rows=".$CONFIG['inline_cms_textarea_rows']." name=\"id_" . $_GET['content'] . "_value\">$content</textarea><input type=\"hidden\" name=\"content_id\" value=\"" . $_REQUEST['content'] ."\"><input type=\"hidden\" name=\"tablename\" value=\"" . $content_table . "\"><input type=\"hidden\" name=\"dbf_after_update\" value=\"continue\"></form>";
		} else {
			$return_content=$content;
		}
		return $return_content;
	}

	/*
	 * Function get_cms_alternatives
	 * Meta: some content items 
	 */
	static function get_cms_alternatives($content_id){

		if (!$content_id){return;}
		global $db;
		$cms_alternatives=array();
		$return=array();
		$cms_sql="SELECT * from cms where content_id = $content_id";
		$cms_result=$db->query($cms_sql);
		while ($cms_rows=$db->fetch_array($cms_result)){
			array_push($cms_alternatives,$cms_rows);	
		}
		
		if (!$cms_alternatives){return;}

		foreach ($cms_alternatives as $cms_alternative){
			if ($cms_alternative['cms_variable']=="replace_link_url"){$return['new_url']=$cms_alternative['value'];}
			if ($cms_alternative['cms_variable']=="replace_link_text"){$return['new_text']=$cms_alternative['value'];}
			if ($cms_alternative['cms_variable']=="display_extra_link"){$return['extra_url']=$cms_alternative['value'];}
			if ($cms_alternative['cms_variable']=="display_extra_text"){$return['extra_text']=$cms_alternative['value'];}
			if ($cms_alternative['cms_variable']=="no_cms"){$return['no_cms']=$cms_alternative['value'];}
		}
		return $return;
	}

	/*
	 * get_cms_page_link
	*/
	static function get_cms_page_link($query_string,$content){
		
		$cms_alternatives=get_cms_alternatives($_GET['content']);
		if (!$cms_alternatives){
			$return_link = "Administrator: <a href=\"" . $_SERVER['PHP_SELF'] . "?" . $query_string . "&pageEdit=1\">Edit This Page</a>";
			$return_link .= " | <a href=\"" . $_SERVER['PHP_SELF'] . "?s=" . $_GET['s'] . "&action=process_log_out\">Log Out</a>";
			$return_link .= "<p>".$content;
			$return_link = str_replace("&action=updateContent","",$return_link);
			if (!$_GET['content'] && !$_GET['action']){$return_link="";} // cancel edit where no content id
			return $return_link;
		} else {
			$return_url = "<a href=\"" . $_SERVER['PHP_SELF'] . "?" . $query_string . "&pageEdit=1\">Edit This Page</a>";

			if ($cms_alternatives['new_url']){ $return_url = "<a href=\"" . $cms_alternatives['new_url'] . "\">";  
				if ($cms_alternatives['new_text']){ $return_url .= $cms_alternatives['new_text'] . "</a>"; } else {$return_url .= "Edit This Page</a>";}
			}

			if ($cms_alternatives['extra_url']){ $return_url .= " | <a href=\"" . $cms_alternatives['extra_url'] . "\">"; 
				if ($cms_alternatives['extra_text']){ $return_url .= $cms_alternatives['extra_text'] . "</a>"; } else {$return_url .= "Edit Module</a>";}
			}

			if ($cms_alternatives['no_cms']){ $return_url=""; }
		}

		if (preg_match("/site.php\?[^s][^=]/",$return_url)){
			$return_url = preg_replace("/site.php\?/","site.php?s=1&",$return_url);
		}
		if (preg_match("/site.php\?[^s][^=]/",$return_url)){
			$return_url = preg_replace("/site.php\?/","site.php?s=1&",$return_url);
		}

		$return_url .= "<p>" . $content;
		$return_url = str_replace("&action=updateContent","",$return_url);
		return $return_url;
	}

}
?>
