<?php

/* Runs when called */

// just basic security stuff. Check that system get variables are in the expected format, and add to these lists on a per site basis too.

$query_string_max_length=150; // The maximum length of a query string sent to the front end.
$query_variable_max_length=80; // the maximum length of any query sent via GET
$positive_integers=array("councillor","press_release_id","product_id","category");

// system variables
$sys_positive_integers=array("id","s","mt","mu","content","row_id","dbf_filter","filter_id","dbf_cur_recordset_start"); // only positive integers accepted
$booleans=array("add_row","dbf_sort","dbf_edi","dbf_search","dbf_sort_dir","dbf_eda","dbf_rpp_sel","dbf_filter","dbf_sort_sel"); // 0 or 1 only
$single_word_inputs=array("action","t","dbf_data_filter_field","dbf_sort_by_field","dbf_az_filter_field","dbf_rpp_pre"); // words can contain letters 0-9 _ : . only
$lc_letters_numbers=array("dbf_sys_form_id"); // lower case letters and numbers only (no spaces or ANYTHING else
$lt9=array("dbf_data_filter_operator","dbf_direction"); // less than 9 characters only

//////////////////////////////////////////////////////////////////////////////////////////////////////
$positive_integers=array_merge($positive_integers,$sys_positive_integers);

// 1. Set a max length on query string 
if (strlen($_SERVER['QUERY_STRING'])>$query_string_max_length){
        format_error("Error G1T9",1);
}

// 2. Query variable max length and checking for strip tags
foreach ($_GET as $qvar=>$qval){
        if (strlen($qval)>$query_variable_max_length && $qvar != "__utmz" && $qvar != "__utma"){
		format_error("Error GPT9 on " . htmlentities($qvar) . " and " . htmlentities($qval),1);
        }
	if (strip_tags($_REQUEST[$qvar]) != $_REQUEST[$qvar]){
		format_error("Error JHL5",1);
	}
}

// Positive Integers 
foreach ($positive_integers as $posint){
	if ($_REQUEST[$posint]){
		if (!preg_match("/^\d+$/",$_REQUEST[$posint])){
			format_error("Non integer value found for key system variable '$posint'",1);
		}
	}
}

// boolean values (1 or 0)
foreach ($booleans as $bool){
	if ($_REQUEST[$bool]){
		if (!preg_match("/^[01]$/",$_REQUEST[$bool])){
			format_error("Non boolean value found for key system variable '$bool'",1);
		}
	}
}

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

// lower case letters and numbers 
foreach ($lc_letters_numbers as $each_one){
	if ($_REQUEST[$each_one]){
		if (!preg_match("/^[a-z0-9]+$/i",$_REQUEST[$each_one])){
			$each_one=htmlentities($each_one);
			format_error("The key system variable '$each_one' contains a badly formatted string.",1);
		}
	}
}

// less than 9 charachters only
foreach ($lt9 as $lt9_each){
	if ($_REQUEST[$lt9_each]){
		if (strlen($_REQUEST[$lt9_each])>9){
			format_error("The key system variable $lt9_each contains a badly formatted string",1);
		}
	}
}

// anybody not logged in has no reason to pass through < > or -- 
foreach ($_REQUEST as $reqvar => $reqval){
	if (strpos($reqval,"<") || strpos($reqval,">") || strpos($reqval,"--")){
		global $user;
		if (!$user->value("id") && $reqvar != "dbf_sort_by_field"){
			$reqvar=htmlentities($reqvar);
			print "Illegal characters found in a posted variable $reqvar. This program is terminating.";
			exit;
		}
	}
}
?>
