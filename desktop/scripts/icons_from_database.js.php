<?php
if ($user->value("id")){
	$iconsql="SELECT ui_desktop_software.* FROM ui_desktop_software INNER JOIN user_desktop_icons ON user_desktop_icons.icon = ui_desktop_software.id WHERE user_desktop_icons.user=" . $user->value("id") . " ORDER BY user_desktop_icons.ordering";
	$ui_icons_rv=$db->query($iconsql);
	if (!mysqli_num_rows($ui_icons_rv)){
		$iconsql="SELECT ui_desktop_software.* FROM ui_desktop_software WHERE default_for_all_admins=1";
		$ui_icons_rv=$db->query($iconsql);
	}
	$iconDivIds=array();
	$iconAnchorIds=array();
	$iconTitles=array();
	$iconIcons=array();
	while ($ui_icons_h=$db->fetch_array($ui_icons_rv)){
		$print_window_function=0;

		// create the window function which will appear in the final javascript file
		$windowFunction = "MUI.".str_replace(" ","_",$ui_icons_h['name'])."Window = function(){\n";
		$windowFunction .= "	var availWinHeight = document.body.scrollHeight;\n";
		$windowFunction .= "	var winHeight=".$ui_icons_h['height'].";\n";
		$windowFunction .= "	if (winHeight>availWinHeight){\n";
		$windowFunction .= "		winHeight=availWinHeight-100;\n";
		$windowFunction .= "	}\n";
		$windowFunction .= "	new MUI.Window({\n";
		$windowFunction .= "		id: '".str_replace(" ","_",$ui_icons_h['window_name'])."',\n";
		$windowFunction .= "		title: '".$ui_icons_h['window_name']."',\n";
		$windowFunction .= "		loadMethod: 'iframe',\n";
		$windowFunction .= "		contentURL: '".str_replace("{=id}",$user->value("id"),$ui_icons_h['contentURL'])."',\n";
		if ($ui_icons_h['width']){ $windowFunction .= "		width: '".$ui_icons_h['width']."',\n";}
		if ($ui_icons_h['height']){ $windowFunction .= "		height: winHeight,\n";}
		if ($ui_icons_h['window_x']){ $windowFunction .= "		x: '".$ui_icons_h['window_x']."',\n";}
		if ($ui_icons_h['window_y']){ $windowFunction .= "		y: '".$ui_icons_h['window_y']."',\n";}
		$windowFunction .= "	});\n";
		$windowFunction .= "}\n\n";

		// create the link check function - this is required alongside the window function itself
		$print_icon_link_check="if ($('".$ui_icons_h['anchor_id']."')) {\n";
		$print_icon_link_check .= "	$('".$ui_icons_h['anchor_id']."').addEvent('click', function(e){\n";
		$print_icon_link_check .= "	new Event(e).stop();\n";
		if (!$ui_icons_h['mui_function']){
			$print_icon_link_check .= "		MUI.".str_replace(" ","_",$ui_icons_h['name'])."Window()\n";
			$print_window_function=1;
		} else {
			$print_icon_link_check .= "		".$ui_icons_h['mui_function'].";\n";
		}
		$print_icon_link_check .= "	});\n";
		$print_icon_link_check .= "}\n\n";
		
		if ($print_window_function){
			print "\n// win func\n\n";
			print $windowFunction;
		}
		print $print_icon_link_check;
	}
}
?>
