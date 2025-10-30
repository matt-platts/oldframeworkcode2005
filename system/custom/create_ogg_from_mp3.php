<?php


$show_id=$_REQUEST['id'];
$sql="SELECT mp3_file, ogg_vorbis_file FROM web_radio WHERE id = $show_id";
$res=$db->query($sql);
while ($h=$db->fetch_array($res)){
	$mp3_file=$h['mp3_file'];

}
if (!$mp3_file){
	print "<p>No mp3 file exists yet - there must be an mp3 uploaded in order to create the ogg vorbis file.</p>";
	print "<p>Note: If you have just selected an mp3 file, please save the radio show first so the file is stored.</p>";
	exit;
}

$ogg_file=str_replace(".mp3",".ogg",$mp3_file);
print "<p>Mp3 file: $mp3_file<br />Creating ogg vorbis file : $ogg_file</p>";

$path="/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/files/radio_shows";
$command="ffmpeg -y -i '$path/$mp3_file' -acodec libvorbis '$path/$ogg_file'";
print "<p style=\"font-size:9px\">Running command: $command</p>";
flush();
shell_exec("$command >/dev/null &");
$update="UPDATE web_radio SET ogg_vorbis_file = '$ogg_file' WHERE id=$show_id";
$res2=$db->query($update);
?>
