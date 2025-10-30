<?php

$debug=0;

$video_xml_template="video/flashxmlscript/flashplayer_template.xml";
$templatefile=file_get_contents($video_xml_template);

if (!$_GET){
	foreach ($argv as $arg){
		if (preg_match("/=/",$arg)){
			$bits=explode("=",$arg);
			$$bits[0]=$bits[1];
		}
	}
} else {
	$video_content_id=$_GET['video_content_id'];
	if (!$video_content_id){
		$video_content_id=$_GET['press_release_id'];
	}
}

if ($video_content_id){
// look up video file from id
	$sql="SELECT video from press_releases where id = $video_content_id";
	if ($debug){print $sql;}
	$res=mysql_query($sql) or die(mysql_error());
	while ($h = mysql_fetch_array($res,MYSQL_ASSOC)){
		$videofile=$h['video'];
	}

	$templatefile=str_replace("{=video}",$videofile,$templatefile);
	$newid="video-".uniqid();
	$newfile="video/flashxmlscript/$newid.xml";
	$videoxml="$newid.xml";
	$write_template=file_put_contents($newfile,$templatefile);
	if ($debug){print "written $newfile";}
	$return="$videoxml";

?>
<div id="video" align="center">
<script type="text/javascript">writeFlash({"id":"flashplayer","width":"440","height":"324","bgcolor":"#ffffff","align":"middle","allowscriptaccess":"sameDomain","flashvars":"config_url=video/flashxmlscript/<?php echo $videoxml; ?>&view_id=0","quality":"high","src":"video/flashplayer.swf"});</script>
</div>
<?php 
}
?>
