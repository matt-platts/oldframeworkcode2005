<div style="float:left;">
<?php
$iconsql="SELECT ui_desktop_software.* FROM ui_desktop_software INNER JOIN user_desktop_icons ON user_desktop_icons.icon = ui_desktop_software.id WHERE user_desktop_icons.user=" . $user->value("id") . " ORDER BY user_desktop_icons.ordering";
$iconDivIds=array();
$iconAnchorIds=array();
$iconTitles=array();
$iconIcons=array();
$ui_icons_rv=$db->query($iconsql);
$number_of_icons=mysqli_num_rows($ui_icons_rv);
if (!$number_of_icons){
	$iconsql="SELECT ui_desktop_software.* FROM ui_desktop_software WHERE default_for_all_admins=1";
	$ui_icons_rv=$db->query($iconsql);
}
$got_icons=0;
while ($ui_icons_h=$db->fetch_array($ui_icons_rv)){
	array_push($iconIcons,str_replace(".png","",$ui_icons_h['icon']));
	array_push($iconTitles,$ui_icons_h['name']);
	array_push($iconDivIds,"dragIcon_" . str_replace(" ","_",$ui_icons_h['name']));
	array_push($iconAnchorIds,$ui_icons_h['anchor_id']);
	$got_icons++;
}

if ($got_icons){
?>

<script language="Javascrript" type="text/javascript">

var availHeight=document.body.scrollHeight;
var iconHeight=58;

var iconAnchorIds=Array("<?php echo join("\",\"",$iconAnchorIds);?>");
var iconIcons=Array("<?php echo join("\",\"",$iconIcons);?>");
var iconTitles=Array("<?php echo join("\",\"",$iconTitles);?>");
var iconDivIds=Array("<?php echo join("\",\"",$iconDivIds);?>");

j=-1;
for (i=0;i<iconDivIds.length;i++){
	document.write('<div class="iconDiv" id="' + iconDivIds[i] + '"><a id="' + iconAnchorIds[i] + '" href="#"><img src="images/icons/48x48/' + iconIcons[i] + '.png" alt="' + iconTitles[i] + '" title="' + iconTitles[i] + '" class="desktopIconDatabase" width="48" height="48" border="0" onload="fixPNG(this)"></a></div>');

if (availHeight<750 && j==5){
	document.write('</div><div style="float:left; padding-left:20px">');
	j=-1;
} else if (availHeight>750 && j==8){
	document.write('</div><div style="float:left; padding-left:20px">');
	j=-1;

}
j++;
}
</script>
</div>
</div>
<?php
}
?>
