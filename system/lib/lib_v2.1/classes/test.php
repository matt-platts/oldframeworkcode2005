<?

$input="
1. Here is a template with an if {=if this} YES	! {=end_if} 
2. and negating: {=if !thip} NEGATIVE PASSES {=end_if}  
3. Positive AND existence : {=if this+that} GOT BOTH {=end_if} 
4. Neg plusses: {=if !thig+!thae} GOT NONE {=end_if} 
5. Check a single value: {=if something = A SOMETHING, here.!} Yes! {=end_if}  
6. Now an or: {=if thuppet|that} yes one of them{=end_if}
7. Plusses: {=if this+that} Yes {=end_if}
8. Positive + neg with val: {=if thes|!that = VAL OF THUT} Yes! {=end_if}
";

print $input;

$global_variables['this']="VAL OF THIS";
$global_variables['that']="VAL OF THAT";
$global_variables['something']="A SOMETHING, here.!";

if (stristr($input,"{=if")){
	$returned=parse_template_code($input);
}
print "\n\n";
print $returned;
exit;

function parse_template_code($input){
	global $global_variables;
	if (stristr($input,"{=if")){

		// First we do straight up existence matches (boolean), also value matches on these, and the ability to join with an OR operator (pipe (|) symbol)
		// The following regex matches single boolean var existences or those joined with a pipe for an or operator, and takes into acount basic = functionality:
		// Examples...
	 	// {=if this}    {=if this|that}   {=if that = VALUE OF THAT} {=if that = VALUE OF THAT|this}
		// Note that all {=if..} must termintate with {=end_if}
		$r = preg_match_all("/{=if ((?:!?[\w! =]+\|?)+ ?=? ?[^\+]*?)}(.+?){=end[ _]?if}/ims",$input,$or_results); // remove the ? after the , to stop capturing single if statements
		$no_of_or_templates=sizeof($or_results[0])-1;
		for ($or_template_no=0; $or_template_no<=$no_of_or_templates; $or_template_no++){
			$or_template=$or_results[0][$or_template_no];
			print "\ntemplate for ir is $or_template\n";
			$or_inner_template=$or_results[2][$or_template_no];

			$or_field_results=$or_results[1][$or_template_no];
			$or_fields=explode("|",$or_field_results);
			$expression_evaluates=false;
			foreach ($or_fields as $or_field){
				if ($expression_evaluates){continue;}
				$original_or_field=$or_field;

				@list($if_var,$if_val)=explode(" = ",$or_field);// does the or field have a value? if so store it in $if_val and rewrite $or_field to be the var only
				if ($if_val){$or_field=$if_var; }

				if (preg_match("/^![\w ]+$/",$or_field)){$positive_check=0; $or_field=str_replace("!","",$or_field);} else {$positive_check=1;}

				// start +ve check
				if ($positive_check && array_key_exists($or_field,$global_variables)) {
					if ($global_variables[$or_field] && strlen($global_variables[$or_field])>=1) {
						if (($if_val && $global_variables[$or_field]==$if_val) || !$if_val){
							$expression_evaluates=true;
						} else {
						// false 
						}
					 }
				} else if (!$positive_check){
					if (!array_key_exists($or_field,$global_variables)  // array key does not exist, so true
					// below says it does exist but it's blank or 0
					|| (array_key_exists($or_field,$global_variables) && (($global_variables[$or_field]=="") || $global_variables[$or_field]=="0"))){
						$expression_evaluates=true;
					} else { 
						// here there is a value, we now have to check that it doesn't match
						if ($global_variables[$or_field] != $if_val){
							$expression_evaluates=true;
						}
					}
				}
			}
			if ($expression_evaluates){
				$input = str_replace($or_template,$or_inner_template,$input);
			} else {
				$input = str_replace($or_template,"",$input);
			}
		}

		// AND matches
		// This section takes into account the + operator for and. Syntax as above but use + instead of | to get a match if both are equal
print "dong AND on $input";
		$r = preg_match_all("/{=if ([!?\w\++]+\w+)}(.*?){=end[ _]?if}/ims",$input,$and_results);
	var_dump($and_results);
		$no_of_and_templates=sizeof($and_results[0])-1;
		for ($and_template_no=0; $and_template_no<=$no_of_and_templates; $and_template_no++){
			$and_template=$and_results[0][$and_template_no];
			$and_inner_template=$and_results[2][$and_template_no];
			$and_field_results=$and_results[1][$and_template_no];
			$and_fields=explode("+",$and_field_results);
			$expression_evaluates=false;
			foreach ($and_fields as $and_field){
				if (preg_match("/^!\w+$/",$and_field)){$positive_check=0; $and_field=str_replace("!","",$and_field);} else {$positive_check=1;}
				if ($positive_check && array_key_exists($and_field,$global_variables) && strlen($global_variables[$and_field])){
					$expression_evaluates=true;
				} else if (!$positive_check && (!array_key_exists($and_field,$global_variables) || !$global_variables[$and_field])){
					$expression_evaluates=true;
				} else {$expression_evaluates=false; break;}

			}

			if ($expression_evaluates){
				$input=str_replace($and_template,$and_inner_template,$input);
			} else {
				$input=str_replace($and_template,"",$input);
			}
		}
	}
	return $input;
}

?>
