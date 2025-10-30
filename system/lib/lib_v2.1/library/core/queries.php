<?php

/*
 * CLASS query_functions_
 * Meta: Helper functions used in the query builder. Currently all static functions - this section to be looked at
*/

class query_functions {

/* 
 * Function query_builder
 * Front page for building a new query
 */
static function query_builder(){
	open_col2();
	?>
	
	<p class="admin_header">Query Builder</b></p>
	<p>Allows you to build and save a query into the system that can be executed as a dbForm interface</p>
	<p>The auto-builder Functionality is currently basic but does allow you to join two tables by a key.
	<br/>To specify a more complex query, enter the sql directly into the SQL box below. 
	<form name="query_builder">
	<input type="hidden" name="table1_parse">
	<input type="hidden" name="table2_parse">
	<table>
	<tr>
	<td><b>Table 1</b>: <select name="table1" onChange="query_builder_select_options('table1_fields','table1_parse','table1');"><?php print tables_as_select_options();?></select></td>
	<td><b>Table 2</b>: <select name="table2" onChange="query_builder_select_options('table2_fields','table2_parse','table2');"><?php print tables_as_select_options();?></select></td>
	</tr>
	<tr>
	<td><b>&nbsp; Field</b>: <select name="table1_fields"><div id='table1_options_list'><option value="">-</option></div></select>
	</td>
	<td><b> = Field</b>: <select name="table2_fields"></select>
	</td>
	</tr>

	</table>
	<hr size=1 color="#000000">
	<b>SQL:</b><br />
	<textarea name="query_sql" rows=6 cols=35></textarea>
	<hr size=1 color=~"#000000"<p>Query Name: <input type="text" name="query_name" value="">
	<p>Query Description (optional):<br>
	<textarea rows="6" cols="35" name="query_description"></textarea>
	<p>
	<input type="submit" value="Save Query">
	</form>
	<?php
	close_col();
}

/* 
 * Function queryfields_as_csv_taking_care_of_brackets
 * Meta: what a long name!
 */
static function queryfields_as_csv_taking_care_of_brackets($fieldlist){
	$all_fields=explode(",",$fieldlist);
	$fieldlist_return_array=array();
	foreach ($all_fields as $each_field){
		if ($add_to_next_field){
			$each_field=$add_to_next_field.",".$each_field;
			$add_to_next_field="";
			}
		if (preg_match("/[(]/",$each_field) && !preg_match("/[)]/",$each_field) && !$add_to_next_field){
			$add_to_next_field=$each_field;
			continue;
		}

		if (preg_match("/ AS /i",$each_field)){
			$eachfieldASarray=preg_split("/ AS /i",$each_field);
			$fieldvalue=$eachfieldASarray[0];
			$fieldname=$eachfieldASarray[1];
		} else {
			$fieldvalue=$each_field;
			$fieldname=$each_field;
		}
		
		array_push($fieldlist_return_array,$fieldname);
	}
	$fieldlist_return=implode(",",$fieldlist_return_array);
	return $fieldlist_return;
}

/* Function: queryfields_as_select_options
*/
static function queryfields_as_select_options($query,$optionsfilter,$selected_field){
	if ($optionsfilter['display_fields']){
		$all_fields=$optionsfilter['display_fields'];
	} else {
		// better split out everything before FROM then - note not yet tested
		$explode1 = preg_split("/ FROM /i",$query);
		$all_fields = preg_replace("/^SELECT /i","",$explode1[0]);
	}

	// DEV NOTE MATT: WE WILL PROBABLY NEED THE BELOW WHEN WE GET TO TESTING THIS QITH QUERIES
	//if ($optionsfilter['dbf_search_fields'])
	//	$all_fields = $optionsfilter['dbf_search_fields'];
	//
	$all_fields=explode(",",$all_fields);
	$inc=0;
	foreach ($all_fields as $each_field){
		if ($add_to_next_field){
			$each_field=$add_to_next_field.",".$each_field;
			$add_to_next_field="";
			}
		//if (preg_match("/[\w+ %-']$/",$each_field) && !$add_to_next_field)
		if (preg_match("/[(]/",$each_field) && !preg_match("/[)]/",$each_field) && !$add_to_next_field){
			$add_to_next_field=$each_field;
			continue;
		}

		if (preg_match("/ AS /i",$each_field)){
			$eachfieldASarray=preg_split("/ AS /i",$each_field);
			$fieldvalue=$eachfieldASarray[0];
			$fieldname=$eachfieldASarray[1];
			if (preg_match("/IF ?\(/i",$fieldvalue)){
				$fieldvalue_to_split=preg_replace("/IF ?\(/i","",$fieldvalue);
				$splitted=explode(" ",trim($fieldvalue_to_split));
				$fieldvalue=$splitted[0];
				$fieldextra=$splitted[1];
				$splitted2=explode("\"",trim($fieldvalue_to_split));
				$testfor=$splitted2[1];
				$positive_result=$splitted2[3];
				$neg_result=$splitted2[5];
				$fieldvalue .= "#test-if-value#$testfor#$positive_result#$neg_result";
	
			}
			if (preg_match("/(SUM\(|MIN\(|MAX\()/i",$fieldvalue)){ $fieldvalue="HAVING:".$fieldname;} 
			// another option here would to be have just the bit after the . to allow filtering on the pre-aggregated value using WHERE!	
		} else {
			$fieldvalue=$each_field;
			$fieldname=$each_field;
		}
		if (preg_match("/count\(\*\)/",$fieldvalue)){
			continue;
		}
		$fields_as_options .= "<option value=\"" . $fieldvalue. "\"";
		if ($fieldvalue == $selected_field){ $fields_as_options .= " selected";}
		$each_field_text=ucfirst($fieldname);
		$each_field_text=preg_replace("/_/"," ",$each_field_text);
		$fields_as_options .= ">" . $each_field_text . "</option>";
		$inc++;
	}
	return $fields_as_options;
}

static function list_tables_in_query($actual_query){
	$querytables=preg_split("/ FROM /i",$actual_query);
	$querytables=$querytables[1];
	$querytables= preg_split("/ ORDER BY /i",$querytables);
	$querytables=$querytables[0];
	$querytables= preg_split("/ WHERE /i",$querytables);
	$querytables=$querytables[0];
	$tablelinks=array("INNER JOIN","LEFT JOIN","RIGHT JOIN");
	$querytables=preg_replace("/ ON [a-zA-Z0-9_.]+\s?=\s?[a-zA-Z0-9_.]+/i","",$querytables);
	$querytables=preg_replace("/ INNER JOIN|LEFT JOIN|RIGHT JOIN /",",",$querytables);
	$querytables=str_replace(" ","",$querytables);

	return $querytables;
}

static function list_selected_fields_from_query($actual_query){

	// pull out sub complex parts (to be added to...) 
	$pattern="/(IF\(SUM\(\(SELECT .* AS \w+)/i";
	$match=preg_match_all($pattern,$actual_query,$matches);
	foreach ($matches[0] as $each){
		print "<!-- got a complex match //-->";
		$orig=$each;
		$bits=explode("AS",$each);
		$each="IF_SUM_SELECT:".trim($bits[1]) . " AS " . trim($bits[1]);;
		print "<!-- now o replace $orig with $each //-->\n\n";
		$actual_query=str_replace($orig,$each,$actual_query);
	}

	// pull out sub queries
	$pattern="/\( ?SELECT \w+ AS \w+ FROM .*\)/";
	$match=preg_match_all($pattern,$actual_query,$matches);
	foreach ($matches[0] as $each){
		$orig=$each;
		$each=str_replace("(","",$each);
		$each=str_replace("SELECT","",$each);
		$bits=explode("FROM",$each);
		$each="SUBQUERY:".trim($bits[0]);
		$actual_query=str_replace($orig,$each,$actual_query);
	}
	
	$queryfields=preg_split("/ FROM /i",$actual_query);
	$queryfields=$queryfields[0];
	$queryfields = preg_replace("/SELECT /i","",$queryfields);
	$queryfields = trim($queryfields);

	//if ($queryfields = "*"){ print "its all fields"; }
	return $queryfields;
}

static function list_fields_in_query_by_queryname($query_name){
	global $db;
	$sql="SELECT query FROM queries WHERE query_name=\"$query_name\"";
	$rv=$db->query($sql);
	$h=$db->fetch_array($rv);
	$query=$h['query'];
	$queryfields=list_selected_fields_from_query($query);
	$query_fields_array=explode(",",$queryfields);
	$rebuild_array=array();
	foreach($query_fields_array as $query_field){
		if (stristr($query_field,"SUBQUERY:")){
			$query_field=str_replace("SUBQUERY:","",$query_field);
		}
	
		if (preg_match("/ AS /i",$query_field)){
			$field_as_name_array=preg_split("/ AS /i",$query_field);
			if ($field_as_name_array[1]){
				$query_field=$field_as_name_array[1];
			}
		}
		
		array_push($rebuild_array,$query_field);
	}
	$queryfields=join(",",$rebuild_array);
	return $rebuild_array;
}

}
?>
