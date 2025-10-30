<?php

function filter_key_exists($filter_key,$filter){
	global $db;
	// if no filter specified, look in the current filter only
	if ($filter){
		global $dbforms_options;
		if ($dbforms_options['filter']){ // a filter is loaded
			if ($dbforms_options['filter'][$filter_key]){
				return $dbforms_options['filter'][$filter_key];	
			} else { // not loaded, check database
				$sql = "SELECT * from filter_keys where filter_id = $filter and name = \"$filter_key\"";
				$res = $db->query($sql);
				$h=$db->fetch_array($res);
				if ($h['value']){return $h['value'];} else { return 0; }
			}
		}
	} else if ($filter){
		if ($dbforms_options['filter'][$filter_key]){
			return $dbforms_options['filter'][$filter_key];
		}
	} else {
		return 0;
	}
}


function filter_key_will_load($filter_key,$filter){
	// if no filter specified, look in the current filter only
	if ($filter){
		global $dbforms_options;
		if ($dbforms_options['filter']){ // a filter is loaded
			if ($dbforms_options['filter'][$filter_key]){
				return $dbforms_options['filter'][$filter_key];	
			} else { // not loaded, check database
				$sql = "SELECT * from filter_keys where filter_id = $filter and name = \"$filter_key\"";
				global $db;
				$res = $db->query($sql);
				$h=$db->fetch_array($res);
				if ($h['value']){return $h['value'];} else { return 0; }
			}
		}
	} else if ($filter){
		if ($dbforms_options['filter'][$filter_key]){
			return $dbforms_options['filter'][$filter_key];
		}
	} else {
		return 0;
	}
}

function filter_wizard(){

	print "ok";
}

?>
