<?php

// load the contents of the configuration table into the CONFIG array
$load_defaults=$db->query("SELECT config_name,config_value FROM configuration"); 
while ($result_defaults=$db->fetch_array($load_defaults)){
		$CONFIG[$result_defaults['config_name']]=$result_defaults['config_value'];
	}

// PRE-ESCAPE ALL REQUEST VARS
foreach ($_REQUEST as $r_var=>$r_val){
        $r_var=$db->db_escape($r_var);
        $_REQUEST_SAFE[$r_var]=$db->db_escape($r_val);
}

foreach ($_POST as $r_var=>$r_val){
        $r_var=$db->db_escape($r_var);
        $_POST_SAFE[$r_var]=$db->db_escape($r_val);
}

foreach ($_GET as $r_var=>$r_val){
        $r_var=$db->db_escape($r_var);
        $_GET_SAFE[$r_var]=$db->db_escape($r_val);
}



function master_content_header($tablename,$interface_type){
	global $CONFIG;
	if ($CONFIG['show_icons_and_headers']){
		global $db;
		$sql="SELECT * FROM master_interface where master_table = '$tablename'";
		$result=$db->query($sql);
		global $master_content_header;
		$master_content_header=array();
		while ($row = $db->fetch_array($result)){
			$master_content_header=$row;
			break;
		}
		
		if ($master_content_header){return true;} else {return false;}
	} else {
		return false;
	}

}

function display_master_content_header($table,$interface_type){
	global $master_content_header;
	$mcs=$master_content_header;
	$return .= "<script language=\"Javascript\" type=\"text/javascript\">\nfunction displayHelptext(hide){\nif (hide==1){\ndocument.getElementById('master_interface_helptext_inner').style.display=\"block\";\n} else {\ndocument.getElementById('master_interface_helptext_inner').style.display=\"none\";\n}\n}\n</script>\n";
	$return .= "<div id=\"master_interface_header\">";
	if ($mcs['helptext']){ 
		$return .= "<div id=\"master_interface_helptext\">";
		$return .= "<div id=\"master_interface_help_icon\" class=\"rightFloat\"><a href=\"Javascript:displayHelptext(1)\"><img border=\"0\" src=\"".SYSIMGPATH."/icons/help.png\"></a></div><div id=\"master_interface_helptext_inner\"> " . $mcs['helptext'] . "<a href=\"Javascript:displayHelptext(0)\" style=\"text-decoration:underline\" >Close Help</a></div></div>";
	}
	$return .= "<div id=\"master_interface_inner_main\">";
	$return .= "<img src=\"".SYSIMGPATH."/icons/" . $mcs['icon'] . "\" /> <font size='3' color='#1b2c67'>" . $mcs['name'] . "</font><br /><span class=\"helptip\">" . $mcs['helptip'] . "</span><p></p>";
	$return .= "</div></div><div class=\"cleardiv\"></div>";
	return $return; 
}
?>
