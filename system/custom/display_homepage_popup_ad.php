<?php

$sql="SELECT * from homepage_ads where active = 1 AND banner_image != \"\" AND banner_image IS NOT NULL ORDER BY RAND() LIMIT 1";
$res=$db->query($sql);
while ($h=$db->fetch_array($res)){

	/*
	if ($h['auto_generate_popup_content']){
		$output='<div style="float: right; width: 323px; margin-right: 80px; line-height: 13px; text-align: left; margin-top: 4px; padding-top: 5px; height: 50px; padding-left: 5px;"><a href="http://www.gonzomultimedia.co.uk/site.php?mt=94&product_id='.$h['promoted_product'].'&content=188" class="mb" style="text-decoration: none; color: #fff;" rel="[group1],width:700,height:500"><img src="http://www.gonzomultimedia.co.uk/images/frontpage/products/'.$h['banner_image'].'" border="0" width="323" height="55" /></a></div>';
	} else { // Stll need the new code in here
		$output='<div style="float: right; width: 323px; margin-right: 80px; line-height: 13px; text-align: left; margin-top: 4px; padding-top: 5px; height: 50px; padding-left: 5px;"><a href="http://www.gonzomultimedia.co.uk/site.php?mt=94&product_id='.$h['promoted_product'].'&content=188" class="mb" style="text-decoration: none; color: #fff;" rel="[group1],width:700,height:500"><img src="http://www.gonzomultimedia.co.uk/images/frontpage/products/'.$h['banner_image'].'" border="0" width="323" height="55" /></a></div>';
	}
	*/

	if ($h['auto_generate_popup_content']){
		$output='<div style="float: right; width: 323px; margin-right: 80px; line-height: 13px; text-align: left; margin-top: 4px; padding-top: 5px; height: 50px; padding-left: 5px;"><a href="http://www.gonzomultimedia.co.uk/site.php?product_id='.$h['promoted_product'].'&content=192" class="mb" style="text-decoration: none; color: #fff;" rel="[group1],width:700,height:500"><img src="http://www.gonzomultimedia.co.uk/images/frontpage/products/'.$h['banner_image'].'" border="0" width="323" height="55" /></a></div>';
	} else { // Stll need the new code in here
		$output='<div style="float: right; width: 323px; margin-right: 80px; line-height: 13px; text-align: left; margin-top: 4px; padding-top: 5px; height: 50px; padding-left: 5px;"><a href="http://www.gonzomultimedia.co.uk/site.php?product_id='.$h['promoted_product'].'&content=192" class="mb" style="text-decoration: none; color: #fff;" rel="[group1],width:700,height:500"><img src="http://www.gonzomultimedia.co.uk/images/frontpage/products/'.$h['banner_image'].'" border="0" width="323" height="55" /></a></div>';
	}

}

print $output;

// second multibox for autopup
	$output='<a href="/site.php?content=195" class="mb" rel="[group2],width:700,height:500" style="color:transparent">.</a>';
	print $output;

?>
