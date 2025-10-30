<?php

// DEPRECATED FILE
// This is OLD code for writing out the javascript milonic menus, not been touched since circa 2007.
// Left in the repository as some old sites may still be using it

if ($_REQUEST['UPDATE_DYNAMIC_MENU']){
	open_status_message();
	global $database_name;
	global $db;
	$row_header = "Tables_in_" . $database_name;
	$sql="show tables";
	$result=$db->query($sql); 

	$table_list=array();
	while ($row=$db->fetch_array($result)){
		array_push($table_list,$row[$row_header]);
	}

	$initial_menu_links=array();
	$menu_links=array();
	$menus = array();
	foreach ($table_list as $tablename){
		$linktext='aI("showmenu=' . $tablename . ';text=' . $tablename . ';")';
		array_push($initial_menu_links, $linktext);
		$varname_add = $linktext . "_add";
		$varname_edit = $linktext . "_edit";
		$varname_view = $linktext . "_view";	
		$menu_links[$tablename][$varname_edit]='aI("text=Edit;url=index.php?action=edit_table&t=' . $tablename . '&edit_type=edit_all&add_data=0")';
		$menu_links[$tablename][$varname_add]='aI("text=Add Record;url=index.php?action=edit_table&t=' . $tablename . '&edit_type=add_row")';
	}

	foreach ($initial_menu_links as $menulink){
		$initial_menu_text .= $menulink . "\n";
	}

	$sub_links = "";
	foreach ($menu_links as $name => $menulink){
		$sub_links .= "with (milonic=new menuname(\"" . $name . "\")){";
		$sub_links .= "\n";
		$sub_links .= "style = submenuStyle" . "\n"; 
		foreach ($menu_links[$name] as $var => $val){
			$sub_links .= $menu_links[$name][$var] . "\n";
		}
		$sub_links .= "}" . "\n\n";
	}

	$data = file_get_contents("../javascript_menu/menu_data_template.js") or die("Cant open file");
	$data = str_replace("// Table-Menu-Initial",$initial_menu_text,$data);
	$data = str_replace("// Table-Menu-Individuals",$sub_links,$data);

	$filename = "$basepath/javascript_menu/menu_data_super_admin.js";
	// Let's make sure the file exists and is writable first.
	if (is_writable($filename)) {

	    // In our example we're opening $filename in append mode.
	    // The file pointer is at the bottom of the file hence
	    // that's where $somecontent will go when we fwrite() it.
	    if (!$handle = fopen($filename, 'w')) {
		 echo "Cannot open file ($filename)";
		 exit;
	    }

	    // Write $somecontent to our opened file.
	    if (fwrite($handle, $data) === FALSE) {
		echo "Cannot write to file ($filename)";
		exit;
	    }

	    echo "<font color='green'>Success, wrote dynamic menu javascript to file ($filename)</font>";
	    fclose($handle);

	} else {
	    echo "The file $filename is not writable";
	}

	close_col();
}
?>
