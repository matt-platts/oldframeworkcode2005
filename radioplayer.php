<html>
<head>
<title>Gonzo Web Radio</title>
<?php
require_once("config.php");
require_once("$libpath/classes/database.php");
$db=new database_connection();
$sql="SELECT * from web_radio where id = " . $_GET['id'];
$res=$db->query($sql);
$h=$db->fetch_array($res);
$progname=$h['name'];
$play=$_GET['play'];
$image=$h['image'];
$mp3file=trim($play);
$mp3file="files/radio_shows/$mp3file";
if (!file_exists($mp3file)){
        print "Sorry - I can't find this radio show on this server."; exit;
}
?>
<link rel="stylesheet" href="css/gonzo.css" type="text/css">
<style type="text/css">
<!--
/*body {width:320px; margin:0px auto; background-color:#fff; min-width:320px; max-width:320px; background-image:url(images/rss2.png); background-repeat:no-repeat; background-position:-27 40;}
*/
body {width:320px; margin:0px auto; background-color:#fff; min-width:320px; max-width:320px; }
.td {color:#fff;}
//-->
</style>
</head>
<body>
<div align="left">
<h3 style="margin-bottom:0px;"><table width="300" border="0" cellpadding="0" cellspacing="0"><tr><td><img src="images/radiosmall.png" width="20"></td><td style="color:#fff; background-color:transparent; font-size:15px">Gonzo web radio</td><td align="right"><a style="color:#fff; font-size:9px" href="Javascript:window.close()">Close Player</a></td></tr></table></h3>
<center>
<br />
<table border="0" cellpadding="0" cellspacing="0"><tr>
<tr><td colspan="1">
<p style="background-color:transparent; margin-bottom:5px; margin-top:5px; font-size:12px;"><marquee><b>Click below to play: </b> <?php echo $progname; ?></marquee></p>
</td></tr>
<tr>
<td align="center"><table style="background-image:url(images/speaker-md.png); background-position:-20 -140;"><tr>
<?php
if ($image){
?>
<td rowspan="2">
<img src="images/radio/<?php echo $image; ?>" height="40" hspace="6"> 
</td>

<?
}

?>
<td>
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" type="application/x-shockwave-flash" data="images/mb_Components/player_mp3_maxi.swf" width="200" height="20">
<param name="movie" value="images/mb_Components/player_mp3_maxi.swf" />
<param name="wmode" value="transparent" />
<param name="FlashVars" value="mp3=files/radio_shows/<?php echo $play; ?>&amp;autoload=1&amp;showvolume=1&amp;volume=160&amp;autoplay=1&amp;showloading=always&showstop=1&sliderwidth=20" />

<embed src="images/mb_Components/player_mp3_maxi.swf" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="200" height="20" FlashVars="mp3=files/radio_shows/<?php echo $play; ?>&amp;autoload=1&amp;showvolume=1&amp;volume=160&amp;showvolume=1&amp;autoplay=1&amp;showloading=always&showstop=1&sliderwidth=20"></embed>

</object>
</td>
</tr>
<tr><td>
<p style="font-size:9px; color:#fff">Click the play button above to stream.</p>
</td></tr></table>
<br />
</center>

</div>
<?php
?>
</body>
</html>

