<?php

/* CLASS: recordset_template
 * Templating functions for recordsets
*/
class recordset_template extends recordset {

function __construct(){
}

public function rs_to_template($rs,$template,$EXPORT){
	
	global $db;
	// create main template variables
	//$template=str_replace("\n","_NEWLINE_",$template); // actually they're all on one line through tiny mce aren't they?
	$match_result=preg_match_all("/{=\w+(:\d+)?}/s",$template,$matches);
	$matches=$matches[0];
	//var_dump($matches);
	//$debug=1;
	foreach ($matches as $each_match){
		if ($debug){print "on match of $each_match";}
		$replace_with="";
		$each_match_var=str_replace("{=","",$each_match);
		$each_match_var=str_replace("}","",$each_match_var);
		if (strlen($EXPORT[$each_match_var])){
			$replace_with=$EXPORT[$each_match_var];
			if ($debug){print " - Excellent = got a match for $each_match_var<br />";} 
		} else if ($each_match_var=="query_string"){
			$replace_with=$_SERVER['QUERY_STRING'];
		} else if ($each_match_var=="coded_query_string"){
			$replace_with=create_preUrl_string($_SERVER['QUERY_STRING']);
		} else {
			if ($debug){print "Cant replace: no export variable found for $each_match_var<br>";}
		}

		if (preg_match("/:\d+/",$each_match,$submatches)){
		
			$submatches[0]=str_replace(":","",$submatches[0]);
			$sub_template_sql="SELECT template FROM templates WHERE id = " . $submatches[0];
			$result2=$db->query($sub_template_sql);
			$all_rows=$EXPORT['rows'];
			while ($subrow=$db->fetch_array($result2)){
				$innertemplate=$subrow['template'];
				//var_dump($all_rows);
			}
			$innertemplate=Codeparser::parse_request_vars($innertemplate);
			// here we run a foreach id of $all_rows, and s&r the name fields from $innertemplate
			$row_iterator=1;
			$replace_with="";
			foreach ($all_rows as $all_rows_id => $all_rows_data){
				$eachinnertemplate=$innertemplate; // start with a fresh template for each as we modify them..

				// WE can do the each variables first of all and alter the initial template dependent on the $row_iterator var.
				$eachinnertemplate=$this->parse_each_variables_in_template($eachinnertemplate,$row_iterator);

				// use display fields filter here or not? 
				// either way do search and replace and print the template, not the array and we should be done with this bit

// look for if blocks first
$s=$eachinnertemplate;

if (stristr($s,"{=if")){
	$match_pattern="/{=if ((?:!?\w+,?)+ ?=? ?\w+)}(.*?){=end[ _]?if}/is";
	$or_block_match_result = preg_match_all($match_pattern,$s,$or_results);
	$no_of_or_templates=sizeof($or_results[0])-1;
	for ($or_template_no=0; $or_template_no<=$no_of_or_templates; $or_template_no++){
	$or_template=$or_results[0][$or_template_no];
	$or_inner_template=$or_results[2][$or_template_no];

	$or_field_results=$or_results[1][$or_template_no];
	$or_fields=explode(",",$or_field_results);
	$expression_evaluates=false;
	foreach ($or_fields as $or_field){
		if ($expression_evaluates){continue;}
		@list($if_var,$if_val)=explode(" = ",$or_field);// does the or field have a value? if so store it in $if_val and rewrite $or_field to be the var only
		if ($if_val){$or_field=$if_var;}
		if (preg_match("/^!\w+$/",$or_field)){$positive_check=0; $or_field=str_replace("!","",$or_field);} else {$positive_check=1;}
			if ($positive_check && array_key_exists($or_field,$all_rows_data)) {
				if ($all_rows_data[$or_field] && strlen($all_rows_data[$or_field])>=1) {
					if (($if_val && $all_rows_data[$or_field]==$if_val) || !$if_val){
						global $CONFIG;
						if ($CONFIG['default_boolean_no_icon']){
							if (stristr($all_rows_data[$or_field],$CONFIG['default_boolean_no_icon'])){

							} else {
								$expression_evaluates=true;
							}
						} else {
							$expression_evaluates=true;
						}
					}
				 }
			} else if (!$positive_check){
				if (!array_key_exists($or_field,$all_rows_data) || (array_key_exists($or_field,$all_rows_data) && ($all_rows_data[$or_field]=="" || $all_rows_data[$or_field]=="0"))){ $expression_evaluates=true; }
			}
	}
	if ($expression_evaluates){
		$s = str_replace($or_template,$or_inner_template,$s);
	} else {
		$s = str_replace($or_template,"",$s);
	}
	}

	$eachinnertemplate=$s;
	$s=$eachinnertemplate;
	// AND or normal I think
	$r_match_pattern="/{=if ([!?\w\++]+\w+)}(.*?){=end[ _]?if}/";
	$r = preg_match_all($r_match_pattern,$s,$and_results);
	$no_of_and_templates=sizeof($and_results[0])-1;
	for ($and_template_no=0; $and_template_no<=$no_of_and_templates; $and_template_no++){
	$and_template=$and_results[0][$and_template_no];
	$and_inner_template=$and_results[2][$and_template_no];
	$and_field_results=$and_results[1][$and_template_no];
	$and_fields=explode("+",$and_field_results);
	$expression_evaluates=false;
	foreach ($and_fields as $and_field){
		if (preg_match("/^!\w+$/",$and_field)){$positive_check=0; $and_field=str_replace("!","",$and_field);} else {$positive_check=1;}
		if ($positive_check && array_key_exists($and_field,$all_rows_data) && strlen($all_rows_data[$and_field])){
			$expression_evaluates=true;
		} else if (!$positive_check && (!array_key_exists($and_field,$all_rows_data) || !$all_rows_data[$and_field])){
			$expression_evaluates=true;
		} else {$expression_evaluates=false; break;}

	}

	if ($expression_evaluates){
		$s=str_replace($and_template,$and_inner_template,$s);
	} else {
		$s=str_replace($and_template,"",$s);
	}
	}
} // end the if test
$eachinnertemplate=$s;
				$tags_match_pattern="/{=\w+.*?}/s";
				$inner_match_result = preg_match_all($tags_match_pattern,$innertemplate,$inner_matches);
				$inner_matches=$inner_matches[0];
				foreach ($inner_matches as $each_inner_match){
					$each_inner_match_var = str_replace("{=","",$each_inner_match);
					$each_inner_match_var = str_replace("}","",$each_inner_match_var);
					$each_inner_pattern="/(.*) ?= ?(\w+)/";
					if (preg_match_all($each_inner_pattern,$each_inner_match_var,$eimv)){
						$full_text_to_replace="{=".$eimv[0][0]."}";
						$full_template_to_replace=preg_match("/$full_text_to_replace(.*)?{=end ?if}/",$innertemplate,$if_template_matches);
						$full_template_to_replace=$if_template_matches[0];
						$full_template_replace_with=$if_template_matches[1];
						$if_field=rtrim(str_replace("if ","",$eimv[1][0]));
						$if_value=$eimv[2][0];
						$each_inner_match_var=$if_field;
						if ($all_rows_data[$each_inner_match_var]!=$if_value){$full_template_replace_with="";}
					} else { $if_field=""; $if_value="";}
					if ($all_rows_data[$each_inner_match_var]){
						$inner_replace_with=$all_rows_data[$each_inner_match_var];
						if ($rs->options['filter'][$each_inner_match_var]['select_value_list']){
							if ($debug){ print "<p>Got an svl on $each_inner_match_var and it is " . $rs->options['filter'][$each_inner_match_var]['select_value_list'] . "! The allrows id is $all_rows_id too. Data for this id is " . $all_rows_data[$each_inner_match_var] . "<br />";}
							$inner_replace_sql=$rs->options['filter'][$each_inner_match_var]['select_value_list'];
							$inner_replace_sql .= " WHERE id = " . $all_rows_id;
							$inner_replace_sql = str_replace("SQL:","",$inner_replace_sql);
							$inner_result=$db->query($inner_replace_sql);
							while ($inner_replace_row = $db->fetch_array($inner_result)){


	// CODE ALERT!!!!
	// The line which calls sql_value_from_id was changed.
	// First incarnation sent $all_rows_id instead of $all_rows_data[$each_inner_match_var]
	// NEED TO LOOK AT WHY THE ORIGINAL WAS DONE, WHAT SOFTWARE IT WAS FOR (EMAIL TEMPLATE?) AND EXPAND FILTER KEYS IF NECESSARY
	//$inner_replace_with=sql_value_from_id($rs->options['filter'][$each_inner_match_var]['select_value_list'],$all_rows_id);
	// SECONDLY.....
	// A recent script (freakemporium.com was causing a sql error from the called sql_value_from_id function as the $all_rows_data[$each_inner_match_var] was already replaced with the correct text! A new bit of code has been added (08/02/2011) to check that it is a number and only calling the code if it is to get round this
	// Question then: Should this ALWAYS be a number? What if we are looking up on a name??! What if the primary key is non numeric? Actually ust need to remove the svl from the options in whatever query called this which is obviously incorrect.... 

								if (preg_match("/^\d+$/",$all_rows_data[$each_inner_match_var])){
									$inner_replace_with=sql_value_from_id($rs->options['filter'][$each_inner_match_var]['select_value_list'],$all_rows_data[$each_inner_match_var]);
								} else { $inner_replace_with=$all_rows_data[$each_inner_match_var]; }
								//$inner_replace_row['county'];		
							}	
						}
					} else {
						// sql values to function

						if (stristr($each_inner_match_var,"PassSQLValueToFunction:")){
							$string_to_function=""; $embed_external_output="";
							$fields_to_function=preg_replace("/PassSQLValueToFunction: ?(\w+.php) /","",$each_inner_match_var);
							$functionname=preg_replace("/PassSQLValueToFunction: ?/","",$each_inner_match_var);
							$functionname = preg_replace("/ .*/","",$functionname);
							$functionname = "system/custom/" . $functionname;
							$fields_to_function_array=explode(" ",$fields_to_function);
							foreach ($fields_to_function_array as $functionfield){
								if ($all_rows[$all_rows_id][$functionfield]){
									$add_to_string= $all_rows[$all_rows_id][$functionfield];
								} else {
									if ($functionfield=="user_type_current"){
										global $user;
										$add_to_string=$user->value("type");
									} else if ($functionfield=="current_user"){
										$add_to_string=$user->value("id");
									} else {
										$add_to_string="";
									}
								}
								$string_to_function .= "\"".$add_to_string."\"" . " ";
							}
							global $user;
							$string_to_function=rtrim($string_to_function);
							$string_to_function = str_replace("current_user",$user->value('id'),$string_to_function);
							$functionname = BASEPATH . "/" . $functionname;
							$sysresult = exec("php $functionname $string_to_function",$embed_external_output);
							$inner_replace_with=join("\n",$embed_external_output);
							// end sql values to function
						} else if (stristr($each_inner_match_var,"PassSQLValueToImportedfunction:")){
							$string_to_function=""; $embed_external_output=""; $functionResult="";
							$fields_to_function=preg_replace("/PassSQLValueToImportedfunction: ?(\w+?.php) /i","",$each_inner_match_var);
							$functionname=preg_replace("/PassSQLValueToImportedfunction: ?/","",$each_inner_match_var);
							$functionname = preg_replace("/ .*/","",$functionname);
							$functionname = "system/custom/" . $functionname;
							$fields_to_function_array=explode(" ",$fields_to_function);
							$user_function=array_shift($fields_to_function_array);
							foreach ($fields_to_function_array as $functionfield){
								if ($all_rows[$all_rows_id][$functionfield]){
									$string_to_function .= "\"" . $all_rows[$all_rows_id][$functionfield] . "\",";
								} else {
									if ($functionfield=="user_type_current"){
										$string_to_function .= "\"" . $user->value("type") . "\",";
									} elseif ($functionfield=="current_user"){
										$string_to_function .= "\"" . $user->value("id") . "\",";
									} else {
										$string_to_function .= "\"$functionfield\","; 
									}
								}
							}
							$string_to_function=rtrim($string_to_function);
							$string_to_function=preg_replace("/,$/","",$string_to_function);
							$functionname=BASEPATH . "/" . $functionname;
							$sysresult2 = include_once($functionname);
							$evalStr="\$functionResult = " . $user_function . "($string_to_function);";
							$res=eval($evalStr);
							$inner_replace_with=$functionResult;
						} else if (stristr($each_inner_match_var,"PassSQLValueToExistingfunction")){
							$string_to_function=""; $embed_external_output=""; $functionResult="";
							$fields_to_function=preg_replace("/PassSQLValueToExistingfunction: ?/i","",$each_inner_match_var);
							$fields_to_function_array=explode(" ",$fields_to_function);
							$user_function=array_shift($fields_to_function_array);
							foreach ($fields_to_function_array as $functionfield){
								if ($all_rows[$all_rows_id][$functionfield]){
									$string_to_function .= "\"" . $all_rows[$all_rows_id][$functionfield] . "\",";
								} else {
									if ($functionfield=="user_type_current"){
										$string_to_function .= "\"" . $user->value("type") . "\",";
									} elseif ($functionfield=="current_user"){
										$string_to_function .= "\"" . $user->value("id") . "\",";
									} else {
										$string_to_function .= "\"$functionfield\","; 
									}
								}
							}
							$string_to_function=rtrim($string_to_function);
							$string_to_function=preg_replace("/,$/","",$string_to_function);
							$evalStr="\$functionResult = " . $user_function . "($string_to_function);";
							$res=eval($evalStr);
							$inner_replace_with=$functionResult;
						} else {
							$inner_replace_with="";
							if ($each_inner_match_var=="query_string"){$inner_replace_with=$_SERVER['QUERY_STRING'];}
							if ($each_inner_match_var=="coded_query_string"){$inner_replace_with=create_preUrl_string($_SERVER['QUERY_STRING']);}	
							// there is space here to pull out the each's if we want to...	
							//if (preg_match("/each:\d+/",$each_inner_match_var)){
							//	print "EACH: $each_inner_match_var<br />";
							//}
							}
						}
					if ($if_field && $if_value){
						$eachinnertemplate=str_replace("$full_template_to_replace",$full_template_replace_with,$eachinnertemplate);
						$if_field=""; $if_value="";
					} else {
						$eachinnertemplate=str_replace($each_inner_match,$inner_replace_with,$eachinnertemplate);
					}
				}
				
			$replace_with .= $eachinnertemplate;
			$row_iterator++;
			}
			$template=str_replace($each_match,$replace_with,$template);
		} else {
			// this section is for bits that are not template ids
			if ($debug){print " - Current replace is $each_match with $replace_with<p>";}
			$template=str_replace($each_match,$replace_with,$template);		
		}
	}
	global $page;
	$template=$page->load_template_widgets($template,$EXPORT);
	return $template;
}

private function parse_each_variables_in_template($eachinnertemplate,$row_iterator){

	// This is the complex form of the each code which ONLY accepts the modulo!
	$each_iterators = (preg_match_all("/\{=each:\d+:\d+\}.*?{=end_?each}/is",$eachinnertemplate,$each_iterator_matches));
	$each_iterator_match_array=$each_iterator_matches[0];
	foreach ($each_iterator_match_array as $each_iterator_match){
		$each_iterator_match = preg_replace("/{=each:/","",$each_iterator_match);
		$get_first_number_from_string=preg_match_all("/^\d+/",$each_iterator_match,$iteration_count_value);
		$iteration_count_val=$iteration_count_value[0][0];
		@list($discardme,$modulo_part)=explode(":",$each_iterator_match);
		$get_second_number_from_string=preg_match_all("/^\d+/",$modulo_part,$modulo_count_value);
		$modulo_count_val=$modulo_count_value[0][0];
		// got the number from the string, now delete it
		$each_iterator_match = str_replace("$iteration_count_val:$modulo_count_val}","",$each_iterator_match);
		$each_iterator_match = preg_replace("/{=end_?each}/i","",$each_iterator_match);
		$each_iterator_html = $each_iterator_match;
		$each_iterator_match = str_replace($each_iterator_html,"",$each_iterator_match);
		if ($row_iterator % $iteration_count_val != $modulo_count_val) {
			$eachinnertemplate=preg_replace("~{=each:$iteration_count_val:$modulo_count_val}$each_iterator_html{=end_?each}~eis","",$eachinnertemplate);
		}
	}


	// Now the short form of the each which does NOT deal with modulos
	// Do we have an iterator variable in there? Pull it out if so..
	$each_iterators = (preg_match_all("/\{=each:\d+\}.*?{=end_?each}/eis",$eachinnertemplate,$each_iterator_matches));
	foreach ($each_iterator_matches[0]  as $each_iterator_match){
		$each_iterator_match = preg_replace("/{=each:/","",$each_iterator_match);
		$get_number_from_string=preg_match_all("/^\d+/",$each_iterator_match,$iteration_count_value);
		$iteration_count_val=$iteration_count_value[0][0];
		// got the number from the string, now delete it
		$each_iterator_match = str_replace("$iteration_count_val}","",$each_iterator_match);
		$each_iterator_match = preg_replace("/{=end_?each}/i","",$each_iterator_match);
		$each_iterator_html = $each_iterator_match;
		$each_iterator_match = str_replace($each_iterator_html,"",$each_iterator_match);
		if ($row_iterator % $iteration_count_val){
			$eachinnertemplate=preg_replace("~{=each:$iteration_count_val}$each_iterator_html{=end_each}~eis","",$eachinnertemplate);
		}
	}

	return $eachinnertemplate;
}

// end class recordset_template
}

?>
