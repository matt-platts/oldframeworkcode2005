<?php

// This file has become a dumping ground for junk!
// All needs to be put in classes properly.
// Some functions need to use config variables not hard coded site names, email addresses etc

function unable_to_unlink_file($dir,$file){
	print "Unable to delete file '$file': please check that the directory called '$dir' is writeable (chmod 777)."; $dtd=0;	
	return $dtd;
}

function create_preUrl_string($preUrl){
	if (!$preUrl){$preUrl=$_SERVER['QUERY_STRING'];}
	$preUrl=str_replace("=",":::",$preUrl);
	$preUrl=str_replace("&","$$",$preUrl);
	return $preUrl;
}

function get_preUrl_string($preUrl){
	$preUrl=str_replace(":::","=",$preUrl);
	$preUrl=str_replace("$$","&",$preUrl);
	return $preUrl;
}

function send_system_mail($mail_id,$mail_name,$variables){
// function accepts either $mail_id OR $mail_name to retrieve an email from the table from either the id or the name. The 3rd paramater is an associative array which accepts variables which are replaced in the email message itself. 

	global $db;
	if ($mail_id){$sql_where = "id = $mail_id";}
	if ($mail_name){$sql_where = "name = \"$mail_name\"";}

	$sql="SELECT * from email_configuration WHERE $sql_where";
	$res=$db->query($sql) or format_error("Unable to retrieve system mail info",1);
	while ($row=$db->fetch_array($res)){
	//var_dump($row);
		if ($row['mail_from_name']){ $from = "\"" . $row['mail_from_name'] . "\" <"; }
		$from .= $row['mail_from_address'];
		if ($row['mail_from_name']){ $from .= ">"; }
		$subject=$row['subject'];
		$to=$row['mail_to_address'];
		$additional_headers=$row['additional_headers'];
		$mail_type=$row['mail_type'];	
		$mail_template=$row['email_template'];
	}
	$headers = "From: $from\n";
	if ($mail_type=="text"){ $headers .= "Content-type:text/plain\n\r";}
	if ($mail_type=="html"){ $headers .= "Content-type:text/html\n\r";}
	if ($additional_headers){
		$additional_headers=preg_replace("/\n/","\n\r",$additional_headers);
		$headers .= $additional_headers;
	}
	
	if ($variables['dbf_message']){ $message = $variables['dbf_message']; }
	if ($mail_template) { $message=$db->field_from_record_from_id("templates",$mail_template,"template");}
	foreach ($variables as $varname => $varval){
		$var_as_var="{=".$varname."}";
		if (stristr($message,$var_as_var)){ $message=str_replace($var_as_var,$varval,$message); }
		if (strlen(stristr($subject,$var_as_var))){ $subject=str_replace($var_as_var,$varval,$subject); }
		if (strlen(stristr($to,$var_as_var))){
			$to=str_replace($var_as_var,$varval,$to);
		}
	}
	//print "Sending mail to $to, $subject, $message, $headers<p>";
	mail ($to,$subject,$message,$headers);
}

// This function creates a cross-referenced table or data selector from 2 data hashes of key/value pairs.
// $fieldtype can be: checkbox,radio,display
// Input data for options and elements should be simple arrays
function crossref_form_from_data($options,$elements,$selected_elements,$fieldtype,$keys_at_top){
	print "<table class=\"form_table\">";
	if ($keys_at_top){
		print "<tr><td></td>";
		foreach ($elements as $element){
			print "<td class=\"crossref_form_col_header\">$element</td>\n";
		}
		print "</tr>";
	}
	foreach ($options as $option){
		print "<tr><td valign='top' align='right'>" . $option . "</td>";
		foreach ($elements as $element){
			if ($fieldtype=="display"){
				print "<td>OK - $element</td>";
			} else if ($fieldtype=="checkbox"){

				print "<td align='center' class='crossref_form_row_header'><input type=\"checkbox\" value=\"$element\" name=\"$option\">";
				if (!$keys_at_top){print $element;}
				print "</td>";
			} else {
				print "<td>OK - $element</td>";
			}
		}
		print "</tr>";	
	}
	print "</table>";
}

// This is not used in the core - possibly called as an additional function and needs to be put somewhere better?
function array_from_table_field($table,$col_name,$col_id,$where){
	global $db;
	$sql = "SELECT ";
	if ($col_id){$sql .= "id,";}
	$sql .= $col_name . " ";
	$sql .= "FROM " . $table;
	if ($where){ $sql .= " WHERE " . $where;}
	$result=$db->query($sql);
	$returnarray=array();
	while ($returned_array=$db->fetch_array($result)){
		if ($returned_array['id']){
			array_push($returnarray,$returned_array['id']);
		} else {
			array_push($returnarray,$returned_array[$col_name]);
		}
	}
	return $returnarray;
}

// THIS SHOULD BE DEPRECATED NOW TOO!! THE NEW PLUGIN USES {=FORM functionality instead of a simple append file, the mail_form action to be removed from site.php as well.
function mail_form(){

	$recipient = $_POST['recipient'];
	$subject = $_POST['subject'];
	$from = $_POST['from'];
	$required = $_POST['required'];
	$headers = $from . "\r\n"; 

	//check for required fields first
	$required_fields=explode(",",$required);
	$required_errors=array();
	foreach ($required_fields as $required_field){
		if (!$_POST[$required_field]){
			array_push($required_errors,preg_replace("/dbf_\d+_/","",$required_field));
		}
	}

	if ($required_errors){
		$errormsg="<p><span class=\"title\"><b>Missing Fields:</b></span></p><p>The following fields are required and were not filled out:<p> " . implode(", ", $required_errors) . "<p>Please go <a href=\"Javascript:history.go(-1)\">Back</a> and resubmit this form. Thanks.  ";
	return $errormsg;
	}

	if (!$recipient || !$subject){
		return "Error: Recipient or subject not specified.";	
	}


	$form_values=array();
	foreach ($_POST as $key => $value){
		if (preg_match("/dbf_\d+_/",$key)){
			$field_name_parts=explode("_",$key);
			$field_name = preg_replace("/dbf_\d+_/","",$key);
			$form_values[$field_name_parts[1]]['fieldname']=$field_name;
			$form_values[$field_name_parts[1]]['value']=$value;
		}
	}

	$message="Here are results from your feedback form\n\n";
	foreach ($form_values as $form_value){
		$message .= $form_value['fieldname'] . ": " .$form_value['value'] . "\n";
	}
	$recipient="mattplatts@gmail.com";
	mail ($recipient,$subject,$message,$headers) || die("Cant send mail ");
			 
}

// runs embedded php code (embedded with {=PHP:scriptname.php} syntax? Not sure but it certainly is used when running custom code on a table update from database_functions...
function run_php_code($code_to_run,$last_insert_id){
	global $options;
	if (!preg_match("/;$",$code_to_run)){
		$code_to_run .= ";";
	}
	$code_to_run=preg_replace("/{=last_insert_id}/",$last_insert_id,$code_to_run);
	ob_start();
	$res = eval ($returned=$code_to_run); 
	$return_content=ob_get_contents();
	ob_end_clean();
	return $return_content;
}


function mui_on_link($url,$window_title="",$mui_same_window="",$previewWindow=""){
	if ($_REQUEST['dbf_mui']){
		$url .= "&dbf_mui=1&jx=1";
		$url=preg_replace("/^administrator/","mui-administrator",$url);
	} else {
	}
	return $url;
}

function get_link($url,$window_title="",$mui_same_window="",$previewWindow=""){
 // converts internal links to ajax calls if the use_ajax_in_admin configuration option is on
	global $CONFIG;
	global $page;
	if ($CONFIG['use_ajax_in_admin'] && $url != "administrator.php?action=process_log_out" && preg_match("/administrator.php/",$_SERVER['PHP_SELF']) && !$_REQUEST['dbf_mui']){
		if (stristr($url,"?")){
			$returnstr = "Javascript:loadPage('$url&jx=1')";
		} else {
			$returnstr = $url; //"Javascript:loadPage('$url?jx=1')";
		}
	} else if ($page->value("mui")){
		// generating dynamic page titles
		if (stristr($url,"?")){
			if ($mui_same_window){
				$returnstr = "$url&dbf_mui=1";
			} else {
				if (!$previewWindow){
					$returnstr = "Javascript:parent.loadPage('$url&dbf_mui=1&jx=1&iframe=1','$window_title')";
				} else {
					$returnstr = "Javascript:parent.loadPage('$url&dbf_mui=1&jx=1&iframe=1','$window_title',1)";
				}
			}
		} else {
			if ($mui_same_window){
				$returnstr = "$url&dbf_mui=1";
			} else {
				if (!$previewWindow){
					$returnstr = "Javascript:parent.loadPage('$url?dbf_mui=1&jx=1&iframe=1','$window_title')";
				} else {
					$returnstr = "Javascript:parent.loadPage('$url?dbf_mui=1&jx=1&iframe=1','$window_title',1)";
				}
			}
		}
	} else {
		$returnstr = $url;
	}
	if ($url=="#" || $url=="#?jx=1") { $returnstr=$url; }
	return $returnstr;
}

function table_search_replace_front(){

	global $col2_open;
	if (!$col2_open){open_col2();}

	print "<p class=\"admin_header\">Global Search And Replace</p>";
	print "<p>Search through a table and replace all instances of a text string in all fields with replacement text.</p>\n";
	print "<table bgcolor=\"#f1f1f1\"><tr><td align=\"right\">Search For: </td><td><input type=\"text\" name=\"search_string\"> in </td><td><select name=\"table\" >";	
	print tables_as_select_options();
	print "</select></td></tr>";
	print "<tr><td>Replace with: </td><td><input type=\"text\" name=\"replace_string\"> </td><td><input type=\"submit\" value=\"Search And Replace Now\"></td></tr></table>";
	
}

function ajax_generate_options_list($querytype,$top_value,$fieldname,$filter){
	global $db;
	if ($querytype=="tabledesc"){
//		$result=list_fields_in_table(strtolower(str_replace(" ","_",$_GET['top_value'])));
		$result=list_fields_in_table(str_replace(" ","_",$_GET['top_value']));
		$result = "Please select a table field:::".join(":::",$result);
		echo($result);
	} else if ($querytype=="dataset"){
		// first get the SVL from the filter for the fieldname
		$sql_query_to_use="SELECT value from filter_keys WHERE filter_id=$filter AND name=\"select_value_list\"";
		$sql_res=$db->query($sql_query_to_use);
		while ($h1=$db->fetch_array($sql_res)){
			$svl=trim(str_replace("SQL:","",$h1['value']));
		}
		$svl=preg_replace("/{=\w+}/",$top_value,$svl);
		$query=$svl;
		$res=$db->query($query) or die("An error has occurred:::".$db->db_error());
		$result_array=array();
		while ($h=$db->fetch_array($res)){
			array_push($result_array,$h['title']);	
		}
		$result="" . join(":::",$result_array);
		echo $result;
	}
}

function ajax_generate_text_field_value($top_value,$fieldname,$filter){
	global $db;
	// first get the SVL from the filter for the fieldname
	$sql_query_to_use="SELECT value from filter_keys WHERE filter_id=$filter AND name=\"text_field_select_value_list\" AND field = \"$fieldname\"";
	$sql_res=$db->query($sql_query_to_use);
	while ($h1=$db->fetch_array($sql_res)){
		$svl=trim(str_replace("SQL:","",$h1['value']));
	}
	$svl=preg_replace("/{=\w+}/",$top_value,$svl);
	$query=$svl;
	$query .= " WHERE id = $top_value"; // use get pk here
	$res=$db->query($query) or die("An error has occurred:::".$db->db_error());
	$result_array=array();
	while ($h=$db->fetch_array($res)){
		$result=trim($h['web_price']); // split to find second field here
	}
	$result=trim($result);
	echo $result;
}

/* 
 * Function value_in_table_field
 * Meta: Needs to be moved to database class, but there are already existing functions that do this?
 * $comparison can be =, >, < or RLIKE
 * $return can be bool or row
*/
function value_in_table_field($tablename,$tablefield,$variable,$comparison,$return_value){
	global $db;
	if (!$comparison){$comparison="=";}
	if (!$return_value){$return_value="bool";}
	$sql="SELECT * FROM $tablename WHERE $tablefield $comparison \"$variable\"";
	$res=$db->query($sql);
	if ($db->num_rows($res)>=1 && ($return_value=="bool" || !$return_value)){
		return 1;
	}

	if ($db->num_rows($res)>=1){
		$return_rows=array();
		while ($row=$db->fetch_array($res)){
			array_push($return_rows,$row);
		}
	return $return_rows;
	}
	return 0;
}

/*
 * Function: hash_into_template
*/
function hash_into_template($hash,$templateid){
        global $db;
        $template=$db->field_from_record_from_id("templates",$templateid,"template");
        foreach ($hash as $key => $value){
                $dbf_tag="{=".$key."}";
                $template=preg_replace("/$dbf_tag/",$value,$template);
        }
	$template = preg_replace("/{=.*?}/","",$template);
        return $template;
}

/*
 * Function hash_into_template_by_key
*/
function hash_into_admin_template_by_key($hash,$dbf_key_name){
        global $db;
        $template=$db->db_quick_match("admin_templates","template","dbf_key_name",$dbf_key_name);
        foreach ($hash as $key => $value){
                $dbf_tag="{=".$key."}";
                $template=preg_replace("/$dbf_tag/",$value,$template);
        }
        $template = preg_replace("/{=.*?}/","",$template);
        return $template;
}

/*
 * Function redirect_browser
 * Meta: Function to redirect browser, if headers sent does it with javascript automatically
*/
function redirect_browser($url){
   if (!headers_sent()) { header('Location: '.$url);
        } else {
        echo '<script type="text/javascript">';
        echo 'window.location.href="'.$url.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
        echo '</noscript>';
   }
}

/*
 * Funciton record_to_template
*/
function record_to_template($t,$EXPORT){
	$pattern="/{=\w+}/";
	$template_matches=preg_match_all($pattern,$t,$matches);
	foreach ($matches[0] as $each){
		$each=str_replace("{=","",$each);
		$each=str_replace("}","",$each);
		if($EXPORT[$each]){
			$to_replace="{=".$each."}";
			$t=str_replace($to_replace,$EXPORT[$each],$t);
		}
	}
	if (stristr($t,"{=SUBREPORT")){
		$pattern="/{=SUBREPORT:[\w{}=&: ;]+}/";
		$sub_matches=preg_match_all($pattern,$t,$matches);
		foreach ($matches[0] as $each){
			$each=str_replace("{=SUBREPORT:","",$each);
			$each=str_replace("}","",$each);
			$to_replace="{=SUBREPORT:".$each."}";
			$each=str_replace("&amp;","&",$each);
			$keys=explode("&",$each);
			foreach ($keys as $key){
			@list($k,$v)=explode("=",$key);
				if ($k=="t"){ $table=$v;}
				if ($k=="filter"){ $filter=$v;}
				if ($k=="type"){ $type=$v;}
				if ($k=="key"){ $key=$v;}
			}
			global $libpath;
			require_once("$libpath/classes/core/filters.php");
			$db_filter=new filter();
			$dbforms_options=$db_filter->load_options();
			$dbforms_options['filter']=$db_filter->load_filter($filter);
			$dbforms_options['filter']['subreport_lookup_key']=$key;
			$subreport=list_table($table,$dbforms_options);
			$t=str_replace($to_replace,$subreport,$t);
		}
	}
	return $t;
}

?>
