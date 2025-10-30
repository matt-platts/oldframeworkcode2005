<?php

if ($_REQUEST['artist_id']){
	if (is_numeric($_REQUEST['artist_id']) && strlen($_REQUEST['artist_id'])<=6){
		$sql="SELECT artist FROM artists WHERE id = " . $_REQUEST['artist_id']; 
		$rv=$db->query($sql);
		while ($h=$db->fetch_array()){
			print $h['artist'];		
		}
	}
}

if (!$_REQUEST['artist_id'] && $_REQUEST['artist']){
	print mysql_real_escape_string($_REQUEST['artist']);
}

?>
