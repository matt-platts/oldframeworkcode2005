<?php

$single_word_inputs = array("action","display_admin_content_by_key_name"); // can contain letters, 0-9 _ : . only

// words without spaces but with underscores
foreach ($single_word_inputs as $single_word){
	if ($_REQUEST[$single_word]){
		if (!preg_match("/^[\w+\d+_:\.]+$/i",trim($_REQUEST[$single_word])) && !preg_match("/count/i",trim($_REQUEST[$single_word]))){ // : for HAVING: etc.
			if ($single_word!="dbf_sort_by_field"){
				// sort by can now contain the > to signify desc - see below where it has been added to the regex
				format_error("The key system variable '$single_word' contains a badly formatted string of '" . htmlentities($_REQUEST[$single_word]) . "'",1);
			} else {
				if (!preg_match("/^[\w+\d+_:\.>]+$/i",trim($_REQUEST[$single_word])) && !preg_match("/count/i",trim($_REQUEST[$single_word]))){ // : for HAVING: etc.
				format_error("The key system variable '$single_word' contains a badly formatted string of '" . htmlentities($_REQUEST[$single_word]) . "'",1);
				}
			}
		}
	}
}
?>
