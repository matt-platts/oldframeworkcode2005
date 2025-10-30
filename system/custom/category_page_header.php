<?php

if ($_REQUEST['master_category_id']){
	$cat_id=$_REQUEST['master_category_id'];
} else {
	$cat_id=$_REQUEST['category_id'];
}

print $cat_id;

?>
