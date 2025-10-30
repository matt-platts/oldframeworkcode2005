<?php

/* Functions for adding and removing plugins */

function plugins_front(){
	global $db;
	open_col2();
	database_functions::list_table("plugins");	
	
	print "<br clear=\"all\"><h4>Viewing Available Plugins</h4>";
	print "<span style=\"font-size:12px\">The following plugins have been found in the plugins directory:</span><p>";
	print "<table><tr style=\"font-weight:bold; color:#ffffff; background-color:#666666\"><td>Plugin Name</td><td>Install</td>";
	print "</tr>";
	$dir_array=get_directory_list("plugins");	
	foreach ($dir_array as $plugin){
		print "<tr><td>".ucfirst(str_replace("_"," ",$plugin)) . "</td>";
		$sql="SELECT * from plugins WHERE plugin_directory= '".$plugin."'";
		$res=$db->query($sql) or format_error("Cant run sql",1,0,$db->db_error());
		if ($db->num_rows($res)>=1){
			print "<td><span style=\"color:green\">Installed</span> [<a style=\"font-size:9px\" href=\"".$_SERVER['PHP_SELF']."?action=remove_plugin&plugin=$plugin\">Remove</a>]</td>";
		} else {
			print "<td><a style=\"background-color:#ffffff;color:#0000ff\" href=\"".$_SERVER['PHP_SELF']."?action=install_plugin&plugin=$plugin\">Install Now</a></td>";
		}
		print "</tr>";
	}
	print "</table>";
	close_col();

}

function install_plugin($plugin){

	global $db;

	print "<p class=\"admin_header\">Installing plugin: $plugin</p><br />";
	$dir="plugins/$plugin";
	$info_file=$dir."/install_info.txt";
	if (file_exists($info_file)){
		$install_info=file_get_contents($info_file);
		if (preg_match("/{=include_options}/",$install_info)){
			$options_list=get_directory_list("$dir/options");
			if (sizeof($options_list)>=1){
				$options_text ="<ul>\n";
				foreach ($options_list as $option){
					if (is_file($dir/$option)){
						$options_text .="<li>$option</li>";
					} else {
						$sub_options_list=get_directory_list("$dir/options/$option");
						$options_text .= "<li><b>" . ucfirst(str_replace("_"," ",$option)) . "</b><ul style=\"margin-bottom:15px;\">";
						foreach ($sub_options_list as $sub_option){
							$options_text .= "<li style=\"list-style-type:none;\"><input type=\"checkbox\" name=\"$sub_option\" value=\"1\" /> $sub_option</li>";
						}
						$options_text .= "</ul></li>";
					}
				}
				$options_text .= "</ul>";
			}
			$install_info=preg_replace("/{=include_options}/",$options_text,$install_info);
		}
		print "<div style=\"padding:10px; background-color4:#f1f1f1; border-width:2px; border-style:dashed; border-color:#1b2c67\">" . str_replace("\n","",$install_info) . "<br />";

		if (!$_REQUEST['install_confirmed']){
			print "<div style=\"float:right; font-weight:bold; background-color:#ffffff; font-size:14px; padding-left:15px; padding-right:15px; border:2px #1b2c67 dashed; display:inline; text-align:right; padding-top:5px; padding-bottom:5px;\"><a style=\"color:#cc0000\" href=\"administrator.php?action=install_plugin&plugin=$plugin&install_confirmed=confirmed\">Click here to continue installing $plugin</a></div></div>";
		exit;
		} else {
			print "</div>";
		}
	}
	
	$plugin_files=get_directory_list($dir);
	print "<p>";
	foreach ($plugin_files as $file){
		$filename="$dir/$file";
		if (is_dir($filename)){continue;}
		if (preg_match("/\.sql$/",$file)){
			print "<b>Run $file</b><br>";
			$filecontents=file_get_contents($filename);		
			$contentsarray=split(";",$filecontents);
			foreach ($contentsarray as $sqlcommand){
				print "<p>Running $sqlcommand";
				if ($sqlcommand && !preg_match("/^\s+$/",$sqlcommand)){
					$m_result=$db->query($sqlcommand) or die("Error on command'$sqlcommand': ". $db->db_error());
				}
			}
		} else if (preg_match("/\.php$/",$file)){
			print "<b>requiring $filename</b><br>";
			require_once($filename);
		} else if (preg_match("/\.txt$/",$file)){
			print "<b>ignoring $filename</b><br>";	
		} else if (preg_match("/^\.+.*$/",file)){
			print "<b>ignoring temporary file $filename.<br />";
		} else {
			plugin_install_error_terminate("Don't know what to do with $file - install does not recognise thie file type.");
		}
	}

	print "<p style=\"color:green\">The $plugin plugin has been successfully installed.</span>";
}

function plugin_install_error_terminate($ermsg){
	print "<span style=\"color:#cc0000\">";
	print $ermsg;
	print "<br />Install terminating</span>";
	exit;
}

function uninstall_plugin($plugin){
	global $db;
	print "<h4>Remove Plugin: $plugin</h4>\n";
	$dir="plugins/$plugin/uninstall";
	$uninstall_files=get_directory_list($dir);	
	foreach ($uninstall_files as $file){
		$filename="$dir/$file";
		if (preg_match("/\.sql$/",$file)){
			print "<b>Run $file</b><br>";
			$filecontents=file_get_contents($filename);		
			$contentsarray=split(";",$filecontents);
			foreach ($contentsarray as $sqlcommand){
				print "<p>Running $sqlcommand";
				if ($sqlcommand && !preg_match("/^\s+$/",$sqlcommand)){
					$m_result=$db->query($sqlcommand) or die("Error on command'$sqlcommand': ". $db->db_error());
				}
			}
		} else if (preg_match("/\.php$/",$file)){
			print "<b>requiring $filename</b><br>";
			require_once($filename);
		} else if (preg_match("/\.txt$/",$file)){
			print "<b>ignoring $filename</b><br>";	
		} else {
			plugin_install_error_terminate("Don't know what to do with $file - install does not recognise thie file type.");
		}
	}
}

?>
