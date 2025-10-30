<?php

function new_interface_demo(){
// unused idea of listing keys in differnt fields for different types which would have added them to something below.

	global $db;
	$list_options=array();
	$sql="SELECT * from filter_key_options WHERE (related_to_field IS NULL or related_to_field=0) AND interface_type=\"list\"";
	$res_list=$db->query($sql);
	while ($h=$db->fetch_array($res_list)){
		array_push($list_options,$h['value']);
	}

	$edit_options=array();
	$sql="SELECT * from filter_key_options WHERE (related_to_field IS NULL or related_to_field=0) AND interface_type=\"edit_single\"";
	$res_edit=$db->query($sql);
	while ($h=$db->fetch_array($res_edit)){
		array_push($edit_options,$h['value']);
	}

	$general_options=array();
	$sql="SELECT * from filter_key_options WHERE (related_to_field IS NULL or related_to_field=0) AND interface_type=\"ALL\"";
	$res_all=$db->fetch_array($sql);
	while ($h=$db->fetch_array($res_all)){
		array_push($general_options,$h['value']);
	}

?>
<form name="interfaceform" method="post">
<p>Display Fields: </p>
<div id="list_records_select">List Records:<br /><select name="list_records_keys"><?php echo csv_to_options(join(",",$list_options)); ?></select></div>
<div id="edit_records_select">Edit Records:<br /><select name="edit_records_keys"><?php echo csv_to_options(join(",",$edit_options)); ?></select></div>
<div id="general_select">General Options:<br /><select name="general_keys"><?php echo csv_to_options(join(",",$general_options)); ?></select></div>
</form>
<?php
}

function filters_and_interfaces_start(){
// Currently used front page for listing filters and interfaces. Adds a few extra keys and prints the links to create new ones above
	open_col2();
	?>
	<table>
	<tr>
	<td valign="middle"><img src="<?php echo SYSIMGPATH;?>/icons/table_filter.png" /> <font size='3' color='#1b2c67'>Filters &amp; Interfaces </font><br /><span class="helptip">Filter your data by values, ordering, and into forms and templates using the options below</span></td></tr> </table><p><hr size='1'>
</p><p>
<ul>
<li><a href="<?php echo $_SERVER['PHP_SELF'];?>?action=create_new_interface">Click here to create a new data Filter</a></li>
<li><a href="administrator.php?action=list_table&t=registered_filters&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=All&dbf_filter=1&dbf_sort=1">Click here to register filters and view registered filters</a></li>
<li><img src="<?php echo SYSIMGPATH;?>/application_images/wizard_icon_purple_29x29.png" /><a href="<?php echo $_SERVER['PHP_SELF'];?>?action=filter_wizard">Click here for the Filter Wizard</a></li>
</ul>
<p>
	<?php
	$filter_table="filters";
	global $libpath;
	require_once("$libpath/classes/core/filters.php");
	$filter_options=new filter();
	$dbforms_options=$filter_options->load_options();	
	$dbforms_options['filter']['title_text']="Showing all filters:";
	$dbforms_options['filter']['hide_system_header']="1";
	$dbforms_options['filter']['include_edit_link']="1";
	//$dbforms_options['filter']['hide_edit_items_link']="1";
	$dbforms_options['filter']['edit_item_link']=$_SERVER['PHP_SELF']."?action=edit_filter&filter={=id}";
	$dbforms_options['filter']['add_text_button_to_row']=SYSIMGPATH."/application_images/button_edit_filter_beige_29x28.png,".SYSIMGPATH."/application_images/button_edit_filter_beige_29x28.png";
	$dbforms_options['filter']['add_button_to_row_url']=$_SERVER['PHP_SELF']."?action=edit_interface&interface_id={=id},".$_SERVER['PHP_SELF']."?action=full_interface_edit&interface_id={=id}";
	$dbforms_options['filter']['add_button_to_row_alt']="Edit version 2,Edit version 3";
	database_functions::list_table($filter_table,$dbforms_options); 
?>
<br /><br />
<?php
}

function create_new_interface($interface_type){
// This is the original single form version
	global $db;
	open_col2();
	if (!$interface_type){
?>
	<table>
	<tr>
	<td valign="middle"><img src="<?php echo SYSIMGPATH;?>/icons/table_filter.png" /> <font size='3' color='#1b2c67'>Filters &amp; Interfaces <img src="<?php echo SYSIMGPATH;?>/icons/arrow_right.png" />  Create A New Filter</font><br /><span class="helptip">Filter your data by values, ordering, and into forms and templates using the options below</span></td></tr> </table><p><hr size='1'>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>?action=create_new_interface" method="post" name="interface_start">
What kind of interface do you wish to create? <p>
<select name="interface_type">
<option value="general">General Interface / Filter for use with any/all of the below</option>
<option value="list">List Records</option>
<option value="edit_single">Edit a single record in a single table</option>
<option value="edit_multiple">Edit a set of records from a single table</option>
<option value="add">Add a single record to a single table</option>
<option value="">---------------------------------------</option>
<option value="list_query">List Records from a query or table join</option>
<option value="edit_single_query">Edit a single record from a query or table join</option>
<option value="edit_multiple_query">Edit a set of records from a query or table join</option>
<option value="add_query">Add a single record via a query or table join</option>
</select>
<p>
Table: <select name="tablename">
<?php
$table_list=list_tables_basic();
foreach ($table_list as $listed_table){
	list($realname,$printname)=explode(";;",$listed_table);
	print "<option value=\"" . $realname . "\">" . $printname . "</option>";
}
?>
</select>
<p>
<input type="button" onClick="document.forms['interface_start'].action='<?php echo $_SERVER['PHP_SELF']; ?>?action=create_new_interface_v2'; document.forms['interface_start'].submit()" value="Continue (new Dynamic Form Style)">
<input type="button" onClick="document.forms['interface_start'].action='<?php echo $_SERVER['PHP_SELF']; ?>?action=create_new_interface'; document.forms['interface_start'].submit()" value="Continue (Single Form)">


</form>
<?php
close_col();
print "</body></html>";
exit;



	} else { // ** From here the type and table have been selected, so display all the options from filter_key_options.

	// load all options from the interface type into an array.
	if ($_POST['interface_type']=="general"){
		$sql = "SELECT * FROM filter_key_options ORDER BY interface_type, ordering DESC";
	} else {
		$sql = "SELECT * FROM filter_key_options WHERE interface_type='" . $_POST['interface_type'] . "' OR interface_type='ALL' order by ordering DESC";
	}
	$result=$db->query($sql);
	while ($row = $db->fetch_array($result)){
		foreach ($row as $var => $value){
			$filtertypedata[$row['id']][$var]=$value;
		}
	}
?>
	<table>
	<tr>
	<td valign="middle"><img src="<?php echo SYSIMGPATH;?>/icons/table_filter.png" /> <font size='3' color='#1b2c67'>Filters &amp; Interfaces</font><br /><span class="helptip">Filter your data by values, ordering, and into forms and templates</span></td>
	<td><img src="<?php echo SYSIMGPATH;?>/icons/arrow_right.png" /></td>
	<td><b>Create a new '<?php echo $_POST['interface_type'];?>' filter on the <?php echo $_POST['tablename']; ?> table.</td></tr>
	</table>
	<p><hr size='1'><p>Please give your filter a name so that you can find it again easily under 'Filters':<br /><b>Filter Name:</b><input type="text" name="filter_name">
	<hr size=1>
<p><br />
<?php
	echo "<form name=\"add_interface_form\" action=\"" . $_SERVER['PHP_SELF'] . "?action=process_create_new_interface\" method=\"post\">";
	echo "<table class=\"form_table\" border=0 cellpadding=2 cellspacing=2>";
	$global_filters=0;
	$interface_subhead="";
	$interface_text =array("ALL" => "All Filters<br>The following keys apply to list, add and edit filters and as such are termed global", "list" => "List Records", "edit_single" => "Editing Single Records");
	
	// Global filters first. Use where they're not related to a field and dont have a parent id..
	foreach ($filtertypedata as $filter_var => $filter_val){
		if (!$filter_val['related_to_field'] && !$filter_val['parent_id']){
			if (!$global_filters){
				print "<tr><td colspan=\"2\"><span style=\"background-color:cfcfcf; font-weight:bold\">Global Keys:</td></tr>\n";
			}	
			if ($filter_val['interface_type'] != $interface_subhead){
				print "<tr><td colspan=\"2\"><span style=\"font-weight:bold; background-color:#cccccc\">" . $interface_text[$filter_val['interface_type']] . "</span></td></tr>\n";
				$interface_subhead=$filter_val['interface_type'];
			} else {
			}
		
			print "<tr><td align=\"right\" valign=\"top\">" . ucfirst(str_replace("_"," ",$filter_val['value'])) . "</td><td>";

			if (!$filter_val['set_values']){
				print "<input type=\"text\" size=\"45\" name=\"globalvalues_-_" . $filter_val['value'] . "\" value=\"\">";
			} else {
				print "<select name=\"globalvalues_-_" . $filter_val['value'] . "\">";	
				if (stristr($filter_val['set_values'],"SQL:")){
					if (preg_match("/SQL:DESC/i",$filter_val['set_values'])){
						$get_tablename=explode(" ",$filter_val['set_values']);
						if ($get_tablename[1]=="{=tablename}"){$get_tablename[1]=$_POST['tablename'];}
						$select_value_list=list_fields_in_table($get_tablename[1]);
						$select_value_list=implode(",",$select_value_list);
					} else {
						$select_value_list=get_sql_list_values($filter_val['set_values']);
					}
				} else {
					$select_value_list=$filter_val['set_values'];
				}
				$options=build_select_option_list($select_value_list,"",1,1,0);
				print $options;
				print "</select>";
			}
			print "<span class='helptip'>" . $filter_val['notes'] . "</span></td></tr>\n";
			$global_filters++;
		}
	}

	echo "<tr><td>&nbsp;</td><td></td></tr>";


	// desc table and print related_to_field optons for each field;
	$sql = "describe " . $_POST['tablename'];
	$result=$db->query($sql);
	while ($row=$db->fetch_array($result)){
		$table_description[$row['Field']]=$row['Type'];
	}	

	$runFunctionsOnLoad=Array();	
	print "<tr bgcolor=\"#f1f1f1\"><td colspan=\"2\" bgcolor=\"#f1f1f1\"><span style=\"background-color:cfcfcf; font-weight:bold\">Field Related Values:<br /><span class=\"helptip\">Below, every field in your table is listed. You can configure values that are specific to each field in each section. Note that defaults exist for everything so you can just alter the parts that you wish to configure.</td></tr>\n";

	// foreach field in the table...
	foreach ($table_description as $table_field_name => $table_field_type){
		echo "<tr><td style=\"font-weight:bold; background-color:f6f6f6\" valign=\"top\" align=\"right\">$table_field_name:</td><td>\n\n";
		echo "<table class=\"form_table\" style=\"background-color:#f1f1f1;\" bgcolor=\"#f1f1f1\">\n";
		// foreach filter key... 
		foreach ($filtertypedata as $filter_var => $filter_val){
			// if it's related to a field and not a global setting..
			if ($filter_val['related_to_field']){
				$print_name= ucfirst(str_replace("_"," ",$filter_val['value']));
				// if its not set values then its free text
				if (!$filter_val['set_values']){
					print "<tr><td align=\"right\" valign=\"top\">" . $print_name . ": </td>\n<td><input type=\"text\" name=\"" . $table_field_name . "_-_" . $filter_val['value'] . "\" value=\"\"><a href=\"\"><img src=\"".SYSIMGPATH."/icons/help.png\" border=0/></a></td></tr>\n\n";
				} else {
					$select_list_options=explode(",",$filter_val['set_values']);
					$selectlist="";
					foreach ($select_list_options as $select_option){
					if (stristr($select_option,";;")){
						$option_pair=explode(";;",$select_option);
						$print_option=$option_pair[1];
						$option_value=$option_pair[0];
					} else {
						$print_option = ucfirst(str_replace("_"," ",$select_option));
						$option_value=$print_option;
					}
					$selectlist .= "<option value=\"$select_option\"";
					if ($select_option == "textarea" && $table_field_type == "text"){ $selectlist .= " selected"; array_push($runFunctionsOnLoad,"showNextField(" . $filter_val['id'] . ",'" . $table_field_name  . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "')");}
					if ($select_option == "text" && preg_match("/varchar/i",$table_field_type)){ $selectlist .= " selected";}
						$selectlist .= ">$print_option</option>\n";
					}
					print "<tr><td align=\"right\" valign=\"top\">" . $print_name . ": </td><td>\n<select name=\"" . $table_field_name . "_-_" . $filter_val['value'] . "\" onChange=\"if (this.value) {showNextField(" . $filter_val['id'] . ",'" . $table_field_name . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "');}  else { clearField(" . $filter_val['id'] . ",'" . $table_field_name . "---" . $filter_val['value'] . "')}\">" . $selectlist . "</select>";
					print "<a href=\"\"><img src=\"".SYSIMGPATH."/icons/help.png\" border=0 /></a>\n";
					print "<div id=\"$table_field_name" . "---" . $filter_val['value'] . "\" style=\"display:inline\">  </div>\n";
					// now select everyting with a parent id of this one
					$select_next_sql="SELECT * from filter_key_options WHERE parent_id = " . $filter_val['id'];
					$result=$db->query($select_next_sql);
					$subResultCounter=0;
					print "<script language=\"Javascript\">\n";
					print "interfaceItem[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceDefs[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceTypeAssocs[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceValues[" . $filter_val['id'] . "] = new Array();\n";
					
					while ($subrow=$db->fetch_array($result)){
						print "interfaceItem[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['value'] . "\";\n";
						print "interfaceDefs[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['notes'] . "\";\n";
						print "interfaceTypeAssocs[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['parent_value'] . "\";\n";
						print "interfaceValues[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['set_values'] . "\";\n";
						$subResultCounter++;
					}	
					print "</script>\n";
					print "</td></tr>\n";
				}
			}
		}
		print "</table>\n</td></tr>\n\n";
	}
	echo "<tr><td></td><td><hr size='1' color='#666666'<input type=\"submit\" value=\"continue\">\n";
	echo "</table>";	
	echo "</form>";	
	}

	foreach ($runFunctionsOnLoad as $eachFunction){
		print "<script language=\"Javascript\">\n";
		print $eachFunction . ";\n";
		print "</script>";
	}
}

function process_create_new_interface(){
// process the single form version
open_col2();
foreach ($_POST as $var => $value){
	print $var . " = " . $value . "<br />";
	$explode_var = explode("_-_",$var);
	if ($explode_var[0] =="globalvalues"){
		$runsql = "INSERT INTO filter_keys VALUES(\"\",$inserted_filted_id,\"" . $explode_var[1] . "\",\"" . $null . "\",\"" . $_POST[$var] . "\",\"\")";
	} else {
		$runsql = "INSERT INTO filter_keys VALUES(\"\",$inserted_filted_id,\"" . $explode_var[1] . "\",\"" . $explode_var[0] . "\",\"" . $_POST[$var] . "\",\"\")";
	}	
	print $runsql;
}
	print "<p>A new filter has been created with an id of " . $inserted_filter_id . "<p>";
close_col();
}

function process_edit_interface(){
	open_col2();
	$editing_filter_id=$_REQUEST['master_table_filter_id'];

	foreach ($_POST as $var => $value){
		//print "<p>stristr is " . stristr($var,"master_table") . "!<br>";
		if (stristr($var,"master_table")){continue;}
		//print $var . " = " . $value . "<br />";
		$explode_var = explode("_-_",$var);

		if ($explode_var[0] =="globalvalues"){ // avoids dealing with field values in this section
			$runsql = "INSERT INTO filter_keys VALUES(\"\",$editing_filter_id,\"" . $explode_var[1] . "\",\"" . $null . "\",\"" . $_POST[$var] . "\",\"\")";
		} else {
			$runsql = "INSERT INTO filter_keys VALUES(\"\",$editing_filter_id,\"" . $explode_var[1] . "\",\"" . $explode_var[0] . "\",\"" . $_POST[$var] . "\",\"\")";
		}	

		print $runsql;
	}
		print "<p>Filter Updated.<p>";

close_col();
}

function delete_interface($interface_id){

}

function create_new_interface_v2(){
// second attempt - some good stuff in here!
	global $db;
	open_col2();
		$sql = "select * from filter_key_options order by interface_type, ordering DESC, value";
		$result=$db->query($sql);
		while ($row = $db->fetch_array($result)){
			foreach ($row as $var => $value){
				$filtertypedata[$row['id']][$var]=$value;
			}
		}

		$global_filter_values=array();
		foreach ($filtertypedata as $filter_var => $filter_val){
			if (!$filter_val['related_to_field'] && !$filter_val['parent_id']){ // ignore field related and items with parents
				array_push($global_filter_values,ucfirst(str_replace("_"," ",$filter_val['value']))); 
			} 
			$filter_val['js_value']=str_replace("-","_x_",$filter_val['value']);
			$inputfield = "var inputfield_" . $filter_val['js_value'];
			$helptext = "var helptext_" . $filter_val['js_value'];
			$filter_val['notes']=trim(addslashes($filter_val['notes']));
			$filter_val['notes']=preg_replace("/\s+/"," ",$filter_val['notes']);
			$filter_val['notes']=preg_replace("/'/","&#145;",$filter_val['notes']);
			if (!$filter_val['set_values']){
				?>
				<script type="text/javascript">
				<?php echo $inputfield; ?> = "<input type=\"text\" size=\"45\" name=\"globalvalues_-_<?php echo $filter_val['value'];?>\" value=\"\">";
				<?php echo $helptext; ?> = "<?php echo $filter_val['notes'];?>";
				</script>
				<?php
			} else {
				if (stristr($filter_val['set_values'],"SQL:")){
					if (preg_match("/SQL:DESC/i",$filter_val['set_values'])){
						$get_tablename=explode(" ",$filter_val['set_values']);
						if ($get_tablename[1]=="{=tablename}"){$get_tablename[1]=$_POST['tablename'];}
						$select_value_list=list_fields_in_table($get_tablename[1]);
						$select_value_list=implode(",",$select_value_list);
					} else {
						$select_value_list=get_sql_list_values($filter_val['set_values']);
					}
				} else {
					$select_value_list=$filter_val['set_values'];
				}
				$options=database_functions::build_select_option_list($select_value_list,"",1,1,0);
				$options=preg_replace("/\n/","",$options);
				?>
				<script type="text/javascript">
				<?php echo $inputfield; ?> = "<select name=\"globalvalues_-_<?php echo $filter_val['value'];?>\"><?php echo addslashes($options);?></select>";	
				<?php echo $helptext; ?> = "<?php echo $filter_val['notes'];?>";
				</script>
				<?php
			}
		}
		$all_global_vals=implode(",",$global_filter_values);
		$no_of_options=count($global_filter_values);
		echo "<form name=\"add_interface_form\" action=\"" . $_SERVER['PHP_SELF'] . "?action=process_create_new_interface\" method=\"post\">";
?>
<!-- TOP HTML //-->
<table>
<tr>
<td valign="middle"><img src="<?php echo SYSIMGPATH;?>/icons/table_filter.png" /> <font size='3' color='#1b2c67'>Filters &amp; Interfaces</font><br /><span class="helptip">Filter your data by values, ordering, and into forms and templates</span></td>
<td><img src="<?php echo SYSIMGPATH;?>/icons/arrow_right.png" /></td>
<td><b>Create a new filter on the <?php echo $_POST['tablename']; ?> table.</td></tr>
</table>
<p>
<hr size='1'><p style=\"background-color:#999999\">Please give your filter a name so that you can find it again easily under 'Filters':<br /><br /><b>Filter Name:</b><input type="text" name="filter_name">
<hr size=1>
<p><b>Global Attributes</b><br>You can now build up a list of global attributes for your filter. Click 'Add Global Attribute' to add new attributes.
<!-- END TOP HTML //-->
<script type="text/javascript">
function node_delete(whichPara){
	if (confirm("Are you sure you want to delete this key")){
		document.getElementById('allparas').removeChild(document.getElementById(whichPara));
	} else {}
}

function loadInputField(keyVal,targetDiv){
	myname = "inputfield_" + keyVal;
	notesname = "helptext_" + keyVal;
	targetDiv="div_" + targetDiv;
	eval("document.getElementById('"+targetDiv+"').innerHTML='"+eval(myname)+"<div class=\"help_for_filter_keys\">" + eval(notesname) + "</div>';")
} 
</script>
<div id="allparas">

<p><a style="font-weight:bold" href="#" onclick="
if( !document.createElement || !document.childNodes ) {
	window.alert('Your browser is not DOM compliant');
} else {
	// set up paragraph and select
	randomNumber = Math.floor(Math.random()*9999999999999999) 
	var theNewParagraph = document.createElement('p');
	var divName = 'div_' + randomNumber;
	var theSelect = document.createElement('select');
	newSelectName= 'global_select_' + document.getElementsByTagName('p').length;
	newSelectName += (Math.random()*9999999999999999)
	theSelect.setAttribute('name',newSelectName);
	passToFunction='loadInputField(this.value,\'' + randomNumber + '\')';
	
	theSelect.setAttribute('onChange',passToFunction);

	// set up options
	firstOption = document.createElement('option');
	firstOption.setAttribute('value','');
	firstOption.setAttribute('style','font-weight:bold;');
	firstText = document.createTextNode('Select Attribute:');
	theSelect.appendChild(firstOption);
	firstOption.appendChild(firstText);
	arrayOptions=Array();
	arrayText=Array();
	<?php
	$i=0;
	$interface_subhead="";
	$interface_text =array("ALL" => "All Filters", "list" => "List Records", "edit" => "Editing Records (General)", "edit_single" => "Editing Single Records Only", "edit_all" => "Edit Multiple Records");
	foreach ($filtertypedata as $filter_var => $filter_val){
		if (!$filter_val['related_to_field'] && !$filter_val['parent_id']){

			if ($filter_val['interface_type'] != $interface_subhead){
				$interface_subhead=$filter_val['interface_type'];
				print "arrayOptions[$i] = document.createElement('option');";
				print "arrayOptions[$i].setAttribute('value','');";
				print "arrayOptions[$i].setAttribute('class','select_list_inline_header');";
				if ($interface_text[$filter_val['interface_type']]){
				print "arrayText[$i] = document.createTextNode(' -- " . $interface_text[$filter_val['interface_type']] . " --');";
				} else {
				print "arrayText[$i] = document.createTextNode(' -- Typeof:" . $filter_val['interface_type'] . " --');";
				}
				print "theSelect.appendChild(arrayOptions[$i]); ";
				print "arrayOptions[$i].appendChild(arrayText[$i]);";
				$i++;
			}

		
			print "arrayOptions[$i] = document.createElement('option');";
			print "arrayOptions[$i].setAttribute('value','" . $filter_val['value'] . "');";
			print "arrayText[$i] = document.createTextNode('" . ucfirst(str_replace("_"," ",$filter_val['value'])) . "');";
			print "theSelect.appendChild(arrayOptions[$i]); ";
			print "arrayOptions[$i].appendChild(arrayText[$i]);";
			$i++;
		}
	}
	?>
	var theTextField = document.createElement('input');
	var theDeleteImage = document.createElement('img');
	
	//set up theNewParagraph
	newParaName= 'para_' + document.getElementsByTagName('p').length;
	newParaName += randomNumber; 
	theNewParagraph.setAttribute('id',newParaName);
	theNewParagraph.setAttribute('style','clear:both; padding-top:10px');

	theSelect.setAttribute('name','');	
	theTextField.setAttribute('type','text');
	theTextField.setAttribute('size','60');

	theDeleteImage.setAttribute('src','<?php echo SYSIMGPATH;?>/icons/page_white_delete.png');
	deleteImageName = 'delete_' + document.getElementsByTagName('p').length;
	deleteImageName += randomNumber; 
	
	theDeleteImage.setAttribute('id',deleteImageName);

	theDiv = document.createElement('div');
	theDiv.setAttribute('id',divName); 
	theDiv.setAttribute('style','display:inline'); 

	//prepare the text nodes
	var theText1 = document.createTextNode('Key: ');

	//put together the whole paragraph
	theNewParagraph.appendChild(theText1);
	theNewParagraph.appendChild(theSelect);
	theNewParagraph.appendChild(theDiv);
	theDiv.appendChild(theTextField);
	theNewParagraph.appendChild(theDeleteImage);
	
	//insert it into the document somewhere
	this.parentNode.parentNode.insertBefore(theNewParagraph,this.parentNode);

	//make the paragraph delete itself when they click on it
	document.getElementById(deleteImageName).onclick = function () { node_delete(this.parentNode.id); //this.parentNode.parentNode.removeChild(this.parentNode);
       	};
}
return false;
"><p style="clear:both; padding-top:15px;">Add Global Attribute:</a></p>
</div>

<?php
close_col();
}


///////////////////////////////////////////////////////////////////////
//                            EDIT INTERFACE
///////////////////////////////////////////////////////////////////////
function edit_interface($interface_id,$tablename){
		global $col2_open;
		if (!$col2_open){ open_col2();}

		global $db;
		// GET THE NAME AND ID WE'RE UPDATING
		$name_sql="select * from filters where id = " . $_REQUEST['interface_id'];
		$name_result=$db->query($name_sql) or format_error("Cant select from filters table",1);
		while ($row = $db->fetch_array($name_result)){
			$filterName = $row['filter_name'];
			$filterDataSource = $row['source_data'];
		}


		// AS PER ADD FUNCTION..
		$sql = "select * from filter_key_options order by interface_type, ordering DESC, value";
		$result=$db->query($sql);
		while ($row = $db->fetch_array($result)){
			foreach ($row as $var => $value){
				$filtertypedata[$row['id']][$var]=$value;
	
			}
		}

		// LOAD EXISTING DATA
		$select_interface_sql="SELECT * from filter_keys WHERE filter_id = $interface_id";
		$interface_result=$db->query($select_interface_sql) or format_error("Unable to run select interface sql",1);
		while ($row=$db->fetch_array($interface_result)){
			$existing_data[$row['name']]=$row;
			//print "data exists for " . $row['name'];
			// build html for existing values....
			//$randomnumber = rand(9999);
			//$output_text .= "<p>Key1: <select name=\"global_select_$randomnumber\" onChange=\"loadInputField(this.value,$randomnumber)\">";
			//print "e is $existing_data_list";
			//$output_text .= build_select_option_list($all_global_vals);
			//$output_text .= "</select><div id=\"div_$randomnumber\" style=\"display:inline\">";
			//$output_text .= "</div><img src=\"".SYSIMGPATH."/icons/page_delete.png\" onClick=\"function(){node_delete(this.parentNode.id);}\"/></p>";
		}

		$global_filter_values=array();
		$existing_data_values=array();
		$existing_data_key_names=array();
		foreach ($filtertypedata as $filter_var => $filter_val){
			if (!$filter_val['related_to_field'] && !$filter_val['parent_id']){ // ignore field related and items with parents
				array_push($global_filter_values,ucfirst(str_replace("_"," ",$filter_val['value']))); 
			} 
			$filter_val['js_value']=str_replace("-","_x_",$filter_val['value']);
			$inputfield = "var inputfield_" . $filter_val['js_value'];
			$existing = "var existing_" . $filter_val['js_value'];
			$helptext = "var helptext_" . $filter_val['js_value'];
			$filter_val['notes']=trim(addslashes(preg_replace("/\s+/"," ",$filter_val['notes'])));
			$filter_val['notes']=preg_replace("/'/","&#145;",$filter_val['notes']);

			// not a set bunch of values - see filter_key_options table where the selections are
			if (!$filter_val['set_values']){
				?>
				<script type="text/javascript">
				<?php echo $inputfield; ?> = "<input type=\"text\" size=\"45\" name=\"globalvalues_-_<?php echo $filter_val['value'];?>\" value=\"<?php echo $existing_data[$filter_val['value']]['value'];?>\">";
				<?php echo $helptext; ?> = "<?php echo $filter_val['notes'];?>";
				<?php if ($existing_data[$filter_val['value']]['value'] && !$filter_val['related_to_field'] && !$filter_val['parent_id']){
						array_push($existing_data_values,$existing_data[$filter_val['value']]['value']);
						array_push($existing_data_key_names,$existing_data[$filter_val['value']]['name']);
				}?>
				<?php echo $existing; ?> = "<?php echo $existing_data[$filter_val['value']]['value']; ?>";
				</script>
				<?php
			} else {
			// here it is a bunch of set values. This can be an SQL query or a csv
				if (stristr($filter_val['set_values'],"SQL:")){
					if (preg_match("/SQL:DESC/i",$filter_val['set_values'])){
						$get_tablename=explode(" ",$filter_val['set_values']);
						if ($get_tablename[1]=="{=tablename}"){$get_tablename[1]=$_POST['tablename'];}
						$select_value_list=list_fields_in_table($get_tablename[1]);
						$select_value_list=implode(",",$select_value_list);
					} else {
						$select_value_list=get_sql_list_values($filter_val['set_values']);
					}
				} else {
					$select_value_list=$filter_val['set_values'];
				}
				$options=database_functions::build_select_option_list($select_value_list,$existing_data[$filter_val['value']]['value'],"",1,0);
				$options=preg_replace("/\n/","",$options);
				?>
				<script type="text/javascript">
				<?php echo $inputfield; ?> = "<select name=\"globalvalues_-_<?php echo $filter_val['value'];?>\"><?php echo addslashes($options);?></select>";	
				<?php echo $existing; ?> = "<?php echo $existing_data[$filter_val['value']]['value']; ?>";
				<?php if ($existing_data[$filter_val['value']]['value'] && !$filter_val['related_to_field'] && !$filter_val['parent_id']){
						array_push($existing_data_values,$existing_data[$filter_val['value']]['value']);
						array_push($existing_data_key_names,$existing_data[$filter_val['value']]['name']);
						}?>
				<?php echo $helptext; ?> = "<?php echo $filter_val['notes'];?>";
				</script>
				<?php
			// End set values on the next curly bracket
			}
		}
		$all_global_vals=implode(",",$global_filter_values);
		$no_of_options=count($global_filter_values);
		$existing_data_list=implode(",",$existing_data_values);
		$existing_data_keys=implode(",",$existing_data_key_names);
		//print "existing values list is " . $existing_data_list;
		//print "<p>existing values list is " . $existing_data_keys;

		echo "<form name=\"add_interface_form\" action=\"" . $_SERVER['PHP_SELF'] . "?action=process_edit_interface\" method=\"post\">";
?>
<!-- TOP HTML //-->
<table>
<tr>
<td valign="middle"><img src="<?php echo SYSIMGPATH;?>/icons/table_filter.png" /> <font size='3' color='#1b2c67'>Filters &amp; Interfaces</font><br /><span class="helptip">Filter your data by values, ordering, and into forms and templates</span></td>
<td><img src="<?php echo SYSIMGPATH;?>/icons/arrow_right.png" /></td>
<td><b>Edit filter</td></tr>
</table>
<p>
<hr size='1'>
<div style="background-color:#e1e1e1"><p style="margin:5px; padding:10px; border-width:1px;"><span class="bordered_header_box">1</span><span style="padding-left:20px; font-weight:bold;">Please give your filter a name so that you can find it again easily under 'Filters':</span><br /><br />
<input type="hidden" name="master_table_filter_id" value="<?php echo $_REQUEST['interface_id'];?>"><br />
<table>
<tr><td width="100" align="right" valign="top"><b>Filter Name:</b> </td><td><input type="text" name="master_table_filter_name" value="<?php echo $filterName; ?>" size="37"></td></tr>
<tr><td align="right" valign="top"><b>Source Data:</b> </td><td><select name="filter_source_data"><option value=\"\">No Source Data</option>
<?php

$table_list=list_tables_basic();
foreach ($table_list as $listed_table){
	list($realname,$printname)=explode(";;",$listed_table);
	print "<option value=\"" . $realname . "\"";
	if ($realname==$filterDataSource){print " selected";}
	print ">" . $printname . "</option>";
}


?></select>
<br /><span style="font-size:11px">* A filter does not need to have source data to work as filters can be applied to different tables (data sources), however if you wish to configure specific fields of a filter a source data table is required so that those fields may be listed here.</span>
</td></tr></table>
</div>
<hr size=1>
<div style="background-color:#e2e2e2">
<p style="padding:10px; margin:5px"><span class="bordered_header_box" style="margin-right:20px">2</span> <span style="padding-left:20px; display:block;"><b>Global Attributes</b><br /><br />You can now build up a list of global attributes for your filter. Any existing attributes are listed below. <br />Click 'Add Global Attribute' at the bottom of the list to add new attributes.</span>
<!-- END TOP HTML //-->
<script type="text/javascript">
function node_delete(whichPara){
	if (confirm("Are you sure you want to delete this key")){
		document.getElementById('allparas').removeChild(document.getElementById(whichPara));
	} else {}
}

function loadInputField(keyVal,targetDiv){
	myname = "inputfield_" + keyVal;
	notesname = "helptext_" + keyVal;
	existing_value = "existing_" + keyVal;
	if(typeof(eval(existing_value)) != "undefined"){
		if (eval(existing_value)){
			//alert("Loading existing value of: " + eval(existing_value));
		}
	}

	targetDiv="div_" + targetDiv;
	eval("document.getElementById('"+targetDiv+"').innerHTML='"+eval(myname)+"<div class=\"help_for_filter_keys\">" + eval(notesname) + "</div>';")
} 
</script>
<div id="allparas">

<?php echo $output_text; ?>

<p><a id="addLink" style="font-weight:bold" href="#" onclick="
if( !document.createElement || !document.childNodes ) {
	window.alert('Your browser is not DOM compliant');
} else {
	// set up paragraph and select
	randomNumber = Math.floor(Math.random()*9999999999999999) 
	var theNewParagraph = document.createElement('p');
	var divName = 'div_' + randomNumber;
	var theSelect = document.createElement('select');
	newSelectName= 'global_select_' + document.getElementsByTagName('p').length;
	newSelectName += (Math.random()*9999999999999999)
	theSelect.setAttribute('name',newSelectName);
	passToFunction='loadInputField(this.value,\'' + randomNumber + '\')';
	
	theSelect.setAttribute('onChange',passToFunction);

	// set up options
	firstOption = document.createElement('option');
	firstOption.setAttribute('value','');
	firstOption.setAttribute('style','font-weight:bold;');
	firstText = document.createTextNode('Select Attribute:');
	theSelect.appendChild(firstOption);
	firstOption.appendChild(firstText);
	arrayOptions=Array();
	arrayText=Array();
	<?php
	$i=0;
	$interface_subhead="";
	$interface_text =array("ALL" => "All Filters", "list" => "List Records", "edit_single" => "Editing Records");
	foreach ($filtertypedata as $filter_var => $filter_val){
		if (!$filter_val['related_to_field'] && !$filter_val['parent_id']){

			if ($filter_val['interface_type'] != $interface_subhead){
				$interface_subhead=$filter_val['interface_type'];
				print "arrayOptions[$i] = document.createElement('option');";
				print "arrayOptions[$i].setAttribute('value','');";
				print "arrayOptions[$i].setAttribute('class','select_list_inline_header');";
				print "arrayText[$i] = document.createTextNode(' -- " . $interface_text[$filter_val['interface_type']] . " --');";
				print "theSelect.appendChild(arrayOptions[$i]); ";
				print "arrayOptions[$i].appendChild(arrayText[$i]);";
				$i++;
			}

		
			print "arrayOptions[$i] = document.createElement('option');";
			print "arrayOptions[$i].setAttribute('value','" . $filter_val['value'] . "');";
			print "arrayText[$i] = document.createTextNode('" . ucfirst(str_replace("_"," ",$filter_val['value'])) . "');";
			print "theSelect.appendChild(arrayOptions[$i]); ";
			print "arrayOptions[$i].appendChild(arrayText[$i]);";
			$i++;
		}
	}
	?>
	var theTextField = document.createElement('input');
	var theDeleteImage = document.createElement('img');
	
	//set up theNewParagraph
	newParaName= 'para_' + document.getElementsByTagName('p').length;
	newParaName += randomNumber; 
	theNewParagraph.setAttribute('id',newParaName);
	theNewParagraph.setAttribute('style','clear:both; padding-top:10px');

	theSelect.setAttribute('name','');	
	theTextField.setAttribute('type','text');
	theTextField.setAttribute('size','60');

	theDeleteImage.setAttribute('src','<?php echo SYSIMGPATH;?>/icons/page_white_delete.png');
	deleteImageName = 'delete_' + document.getElementsByTagName('p').length;
	deleteImageName += randomNumber; 
	
	theDeleteImage.setAttribute('id',deleteImageName);

	theDiv = document.createElement('div');
	theDiv.setAttribute('id',divName); 
	theDiv.setAttribute('style','display:inline'); 

	//prepare the text nodes
	var theText1 = document.createTextNode('Key: ');

	//put together the whole paragraph
	theNewParagraph.appendChild(theText1);
	theNewParagraph.appendChild(theSelect);
	theNewParagraph.appendChild(theDiv);
	theDiv.appendChild(theTextField);
	theNewParagraph.appendChild(theDeleteImage);
	
	//insert it into the document somewhere
	this.parentNode.parentNode.insertBefore(theNewParagraph,this.parentNode);

	//make the paragraph delete itself when they click on it
	document.getElementById(deleteImageName).onclick = function () { node_delete(this.parentNode.id); //this.parentNode.parentNode.removeChild(this.parentNode);
       	};
}
return false;
">
<p id="add_para" style="clear:both; padding-top:15px;">Add Global Attribute:</a></p>
<script type="text/javascript">
function write_entry(myFieldType,mySelectValue){

	// set up paragraph and select
	randomNumber = Math.floor(Math.random()*9999999999999999) 
	var theNewParagraph = document.createElement('p');
	var divName = 'div_' + randomNumber;
	var theSelect = document.createElement('select');
	newSelectName= 'global_select_' + document.getElementsByTagName('p').length;
	newSelectName += (Math.random()*9999999999999999)
	theSelect.setAttribute('name',newSelectName);
	theSelect.setAttribute('id',newSelectName);
	passToFunction='loadInputField(this.value,\'' + randomNumber + '\')';
	
	theSelect.setAttribute('onChange',passToFunction);

	// set up options
	firstOption = document.createElement('option');
	firstOption.setAttribute('value','');
	firstOption.setAttribute('style','font-weight:bold;');
	firstText = document.createTextNode('Select Attribute:');
	theSelect.appendChild(firstOption);
	firstOption.appendChild(firstText);
	arrayOptions=Array();
	arrayText=Array();
	setThisValue=0;
	<?php
	$i=0;
	$interface_subhead="";
	$interface_text =array("ALL" => "All Filters", "list" => "List Records", "edit_single" => "Editing Records");
	
	foreach ($filtertypedata as $filter_var => $filter_val){
		if (!$filter_val['related_to_field'] && !$filter_val['parent_id']){

			if ($filter_val['interface_type'] != $interface_subhead){
				$interface_subhead=$filter_val['interface_type'];
				print "arrayOptions[$i] = document.createElement('option');";
				print "arrayOptions[$i].setAttribute('value','');";
				print "arrayOptions[$i].setAttribute('class','select_list_inline_header');";
				print "arrayText[$i] = document.createTextNode(' -- " . $interface_text[$filter_val['interface_type']] . " --');";
				print "theSelect.appendChild(arrayOptions[$i]); ";
				print "arrayOptions[$i].appendChild(arrayText[$i]);";
				$i++;
			}

			print "arrayOptions[$i] = document.createElement('option');";
			print "arrayOptions[$i].setAttribute('value','" . $filter_val['value'] . "');";
			print "\ncurrentValue = '" . $filter_val['value'] . "';\n";	
			print "if (currentValue==myFieldType){arrayOptions[$i].setAttribute('selected','selected'); setThisValue=1;}\n";
			print "arrayText[$i] = document.createTextNode('" . ucfirst(str_replace("_"," ",$filter_val['value'])) . "');";
			print "theSelect.appendChild(arrayOptions[$i]); ";
			print "arrayOptions[$i].appendChild(arrayText[$i]);";
			$i++;
		}
	}
	?>
	var theTextField = document.createElement('input');
	var theDeleteImage = document.createElement('img');
	
	//set up theNewParagraph
	newParaName= 'para_' + document.getElementsByTagName('p').length;
	newParaName += randomNumber; 
	theNewParagraph.setAttribute('id',newParaName);
	theNewParagraph.setAttribute('style','clear:both; padding-top:10px');

	theSelect.setAttribute('name','');	
	theTextField.setAttribute('type','text');
	theTextField.setAttribute('size','60');

	theDeleteImage.setAttribute('src','<?php echo SYSIMGPATH;?>/icons/page_white_delete.png');
	deleteImageName = 'delete_' + document.getElementsByTagName('p').length;
	deleteImageName += randomNumber; 
	
	theDeleteImage.setAttribute('id',deleteImageName);

	theDiv = document.createElement('div');
	theDiv.setAttribute('id',divName); 
	theDiv.setAttribute('style','display:inline'); 

	//prepare the text nodes
	var theText1 = document.createTextNode('Key: ');

	//put together the whole paragraph
	theNewParagraph.appendChild(theText1);
	theNewParagraph.appendChild(theSelect);
	theNewParagraph.appendChild(theDiv);
	theDiv.appendChild(theTextField);
	theNewParagraph.appendChild(theDeleteImage);
	
	//insert it into the document somewhere
	document.getElementById('addLink').parentNode.insertBefore(theNewParagraph,this.parentNode);

	//make the paragraph delete itself when they click on it
	document.getElementById(deleteImageName).onclick = function () { alert(this.parentNode.id); node_delete(this.parentNode.id); //this.parentNode.parentNode.removeChild(this.parentNode);
       	};
	
	loadInputField(myFieldType,randomNumber);
}
// javascript loop in here to call write_entry
<?php
$existing_counter=0;
foreach ($existing_data_key_names as $e_key => $e_value){
	$e_input = $existing_data_values[$existing_counter];
?>

	window.onload=write_entry('<?php echo $e_value;?>','<?php echo $e_input; ?>');
<?php
$existing_counter++;
 } ?>

</script> 
</div>
</div>
<hr size=1>
<p style="margin:5px; padding:10px"><span class="bordered_header_box" style="margin-right:20px">3</span>
<span style="font-weight:bold;">Configure Fields Below<br /><span style="font-weight:normal">Here you can set up options for individual fields.</span></span></p>
<hr size=1>
<!--<table>//-->
<?php

	// load all options from the interface type into an array.
	$sql = "select * from filter_key_options order by ordering,interface_type DESC";
	$result=$db->query($sql) or format_error("Error selecting from filter key options",1);
	while ($row = $db->fetch_array($result)){
		foreach ($row as $var => $value){
			$filtertypedata[$row['id']][$var]=$value;
		}
	}

	asort($filtertypedata);
	// desc table and print related_to_field optons for each field;
	$sql = "describe " . $filterDataSource;
	$result=$db->query($sql);
	while ($row=$db->fetch_array($result)){
		$table_description[$row['Field']]=$row['Type'];
	}

	$runFunctionsOnLoad=Array();	

	$existing_field_data=array();
	// get all existing data related to fields
	print "<script type=\"text/javascript\">\n\n";
	$existing_field_sql="SELECT * from filter_keys WHERE field != \"\" and filter_id=\"" . $_REQUEST['interface_id'] . "\"";
	$existing_field_result=$db->query($existing_field_sql) or format_error("Error in existing field sql",1);
	while ($fieldrow = $db->fetch_array($existing_field_result)){
		$existing_field_data[$fieldrow['field']][$fieldrow['name']]=array();
		array_push($existing_field_data[$fieldrow['field']][$fieldrow['name']],$fieldrow['value']);

		?>
var existing___<?php echo $fieldrow['field'] . "___" . $fieldrow['name'] . " = \"" . $fieldrow['value'] . "\";";?>

		<?php
	}

	print "\n</script>\n";
	?>
	<style type="text/css">
	.table_field_setup_filter_div {display:none;}
	.table_field_setup_filter_name {display:inline; padding:15px 0px 15px 5px;}
	</style>
	<script language="Javascript" type="text/javascript">
	function showFieldOptions(whichfieldtoshow,buttonName){
		allFieldNames= new Array("<?php
		$all_table_fields=array();
		foreach ($table_description as $table_field_name => $table_field_type){
			array_push($all_table_fields,"fieldname_".$table_field_name);
		}
		echo join("\",\"",$all_table_fields);
		?>");
		allButtonNames= new Array("<?php
		$all_table_fields=array();
		foreach ($table_description as $table_field_name => $table_field_type){
			array_push($all_table_fields,"button_".$table_field_name);
		}
		echo join("\",\"",$all_table_fields);
		?>");
		for (z=0;z<allFieldNames.length;z++){
			thisElement=document.getElementById(allFieldNames[z]);
			if (allFieldNames[z]==whichfieldtoshow){
				thisElement.style.display="block";
			} else {
				thisElement.style.display="none";
			}
		}
		for (z=0;z<allButtonNames.length;z++){
			buttonElement=document.getElementById(allButtonNames[z]);
			if (allButtonNames[z]==buttonName){
				buttonElement.style.backgroundColor="cyan";
			} else {
				buttonElement.style.backgroundColor="white";
			}
		}
	}
	</script>
	<?php

	// foreach field in the source data table...
	$df_field_count=0;
	foreach ($table_description as $table_field_name => $table_field_type){
		$df_field_count++;
		echo "<div class=\"table_field_setup_filter_name\" ><a href=\"Javascript:showFieldOptions('fieldname_$table_field_name','button_$table_field_name')\">";
		echo "<span id=\"button_$table_field_name\" style=\"border-width:1px; border-style:solid; padding:2px; font-weight:bold; font-size:9px; margin-bottom:10px;\">".ucfirst(str_replace("_"," ",$table_field_name))."</span></a>";
		if ($df_field_count==10){
			$df_field_count=0;
			echo "<br /><br clear=\"all\" />";
		}
		echo "</div>\n\n";
		}


	foreach ($table_description as $table_field_name => $table_field_type){
		echo "<div class=\"table_field_setup_filter_div\" id=\"fieldname_$table_field_name\"><table><tr><td>\n\n";
		echo "<table class=\"form_table\" style=\"background-color:#f1f1f1;\" bgcolor=\"#f1f1f1\"><tr><td colspan=2></td></tr>\n";
		echo "<tr><td colspan=2><hr></td></tr>";
		echo "<tr><td align=\"right\"><b>MySQL Field Name: </b></td><td>$table_field_name</td></tr>";
		echo "<tr><td align=\"right\"><b>MySQL Column Type: </b></td><td>$table_field_type</td></tr>";
		echo "<tr><td colspan=2><hr></td></tr>";
		// foreach filter key option... 
		foreach ($filtertypedata as $filter_var => $filter_val){
			// if it's related to a field and doesnt have a parent value we can print it
			if ($filter_val['related_to_field'] && !$filter_val['parent_value']){
				$print_name= ucfirst(str_replace("_"," ",$filter_val['value']));
				// if its not set values then its free text - just print a row, input field and put the values in - easy
				if (!$filter_val['set_values']){
					// check if we have a value here from the existing filter for a text based filter key related to a field.
					if ($existing_field_data[$table_field_name][$filter_val['value']]){
						//print "Looking in array for " . $table_field_name . " -> " . $filter_val['value'] . "<br>";
						$existing_value=$existing_field_data[$table_field_name][$filter_val['value']][0];
						//var_dump($existing_field_data[$table_field_name][$filter_val['value']][0]);
						//print "GOT EXISTING VALUE FOR " . $filter_val['value'] . " OF " . $existing_field_data[$table_field_name][$filter_val['value']][0];
					} else {
						$existing_value="";
					}
					print "<tr><td align=\"right\" valign=\"top\">" . $print_name . ": </td>\n<td><input type=\"text\" name=\"" . $table_field_name . "_-_" . $filter_val['value'] . "\" value=\"$existing_value\"><a href=\"\"><img src=\"".SYSIMGPATH."/icons/help.png\" border=0/></a></td></tr>\n\n";
				} else {
					// here it is based on set values then..!
					if ($existing_field_data[$table_field_name][$filter_val['value']]){
						$existing_value=$existing_field_data[$table_field_name][$filter_val['value']][0];
					} else {
						$existing_value="";
					}
					
					$selectedyet=0;
					$select_list_options=explode(",",$filter_val['set_values']);
					$selectlist="";
					foreach ($select_list_options as $select_option){
						if (stristr($select_option,";;")){
							$option_pair=explode(";;",$select_option);
							$print_option=$option_pair[1];
							$option_value=$option_pair[0];
						} else {
							$print_option = ucfirst(str_replace("_"," ",$select_option));
							$option_value=$select_option;
						}
						$selectlist .= "<option value=\"$select_option\"";
						if ($select_option==$existing_value){ $selectlist .= " selected"; $selectedyet=1;}

						// textarea? need to show advanced options onload..
						if ($select_option == "textarea" && $table_field_type == "text"){ 
							$selectlist .= " selected"; array_push($runFunctionsOnLoad,"showNextField(" . $filter_val['id'] . ",'" . $table_field_name  . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "')");
						}

						// select list? need to show the advanced options onload..
						if ($select_option == "select" && $existing_value==$select_option){
							array_push($runFunctionsOnLoad,"showNextField(" . $filter_val['id'] . ",'" . $table_field_name . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "')");
						}

						// text? need to show advanced options onload..
						if ($select_option == "text"){ $selectlist .= " selected"; array_push($runFunctionsOnLoad,"showNextField(" . $filter_val['id'] . ",'" . $table_field_name  . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "')");
						}

						if ($select_option == "text" && preg_match("/varchar/i",$table_field_type && !$selectedyet)){ $selectlist .= " selected";}
						$selectlist .= ">$print_option</option>\n";


					}

					print "<tr style=\"background-color:#e1e1e1\"><td align=\"right\" valign=\"top\">" . $print_name . ": </td><td>\n<select name=\"" . $table_field_name . "_-_" . $filter_val['value'] . "\" onChange=\"if (this.value) {showNextField(" . $filter_val['id'] . ",'" . $table_field_name . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "');}  else { clearField(" . $filter_val['id'] . ",'" . $table_field_name . "---" . $filter_val['value'] . "')}\">" . $selectlist . "</select>";
					print "<a href=\"\"><img src=\"".SYSIMGPATH."/icons/help.png\" border=0 /></a>\n";
					print "<div id=\"$table_field_name" . "---" . $filter_val['value'] . "\" style=\"display:inline\">  </div>\n";
					// now select everyting with a parent id of this one
					$select_next_sql="SELECT * from filter_key_options WHERE parent_id = " . $filter_val['id'] . " ORDER BY ordering DESC";
					$result=$db->query($select_next_sql) or format_error ("Mysql error 81XY",1);
					$subResultCounter=0;
					print "<script language=\"Javascript\">\n";
					print "interfaceItem[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceDefs[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceTypeAssocs[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceValues[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceSelectedValue[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceElementType[" . $filter_val['id'] . "] = new Array();\n";
					
					while ($subrow=$db->fetch_array($result)){
						print "interfaceItem[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['value'] . "\";\n";
						print "interfaceDefs[".$filter_val['id']."][".$subResultCounter."] = \"" . preg_replace("\n","<br />",$subrow['notes']) . "\";\n";
						print "interfaceTypeAssocs[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['parent_value'] . "\";\n";
						print "interfaceValues[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['set_values'] . "\";\n";
						print "interfaceSelectedValue[".$filter_val['id']."][".$subResultCounter."] = \"" . $existing_value . "\";\n";
						print "interfaceElementType[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['element_type']. "\";\n";
						$subResultCounter++;
					}	

					print "</script>\n";
					print "</td></tr>\n";
				}
			}
		}
		print "</table>\n</td></tr></table></div>\n\n";
	}

?>
<!--</table>//-->
<p align="right" style="background-color:#f1f1f1; padding:3px"><input type="submit" value="Update Interface"></p>
</form>
<?php
	foreach ($runFunctionsOnLoad as $eachFunction){
		print "<script language=\"Javascript\">\n";
		print $eachFunction . ";\n";
		print "</script>";
	}
close_col();
}

function full_interface_edit($filter_id){
	global $db;
	// 1 - Set initial variables from the master record in "filters" - the name, source data and description.
	$filter_name=$db->field_from_record_from_id("filters",$filter_id,"filter_name");
	$filter_source=$db->field_from_record_from_id("filters",$filter_id,"source_data");
	$filter_description=$db->field_from_record_from_id("filters",$filter_id,"description");
	$filter_description_field="<textarea name=\"filter_description\" id=\"filter_description\">$filter_description</textarea>";
	$filter_name_field="<input type=\"text\" name=\"filter_name\" id=\"filter_name\" value=\"$filter_name\" />";
	$filter_hash=array();
	$filter_hash['filter_id']=$filter_id;
	$filter_hash['filter_name']=$filter_name;
	$filter_hash['filter_name_field']=$filter_name_field;
	$filter_hash['filter_description']=$filter_description;
	$filter_hash['filter_description_field']=$filter_description_field;

	// 2 - Get existing values in $all filter_key_values.
	//     Those related to a field are in the 'field' key as a sub-tree.
	//     Global variables will not be in a 'field' sub key and will be single items off the trunk.
	$filter_keys_sql="SELECT * from filter_keys WHERE filter_id=$filter_id";
	$filter_keys_rv=$db->query($filter_keys_sql);
	$all_filter_key_values=array();
	$multiple_keys_for_field=array();
	while ($filter_key_values_h=$db->fetch_array($filter_key_rv)){
		if (!$filter_key_values_h['field']){
			if ($all_filter_key_values[$filter_key_values_h['name']]){
				print "<p class=\"dbf_para_warning\">Multiple keys exist for " . $filter_key_values_h['name']."</p>";
				$multiple_keys_for_field[$filter_key_values_h['name']]=array();
				$multiple_field_data['user_type']=$filter_key_values_h['user_type'];
				$multiple_field_data['value']=$filter_key_values_h['value'];
				array_push($multiple_keys_for_field[$filter_key_values_h['name']],$multiple_field_data);
			} else {
				$all_filter_key_values[$filter_key_values_h['name']]=$filter_key_values_h;
			}
		} else {
			$all_filter_key_values['fields'][$filter_key_values_h['field']][$filter_key_values_h['name']]=$filter_key_values_h['value'];
		}
	}
	//var_dump($multiple_keys_for_field);
	// 3 -Get all potential options - this is all the values that *could* exist and is stored in $all_filter_key_options
	$all_filter_key_options_sql="SELECT * from filter_key_options"; 
	$all_filter_key_options_rv=$db->query($all_filter_key_options_sql);
	$all_filter_key_options=array();
	while ($all_filter_keys_h=$db->fetch_array($all_filter_key_options_rv)){
		array_push($all_filter_key_options,$all_filter_keys_h);
	}
	
	// 4 - write input field and value keys for all options which are not field based
	foreach ($all_filter_key_options as $option_set){ // these are all potential options - values may not exist!
		// 4a - write input field key (keyname:input_field);
		$input_field_key=$option_set['value'].":input_field";
		$value_key=$option_set['value'].":value";
			$filter_hash[$input_field_key].=generate_input_field_from_hash_data($option_set['value'],$all_filter_key_values[$option_set['value']],$option_set['set_values'],$option_set['element_type'],$filter_source,$option_set['multiple_set_values']);
		// multiple keys bit
		foreach ($multiple_keys_for_field[$option_set['value']] as $innerhash){
			$filter_hash[$input_field_key] .= "<br /><b>or for user type: " . $innerhash['user_type']."<br />" . "</b>";
			$filter_hash[$input_field_key] .= generate_input_field_from_hash_data($innerhash['value'],$all_filter_key_values[$innerhash['value']],$option_set['set_values'],$option_set['element_type'],$filter_source,$option_set['multiple_set_values']); 
		}
		// 4b - write value key (keyname:value) 
		$filter_hash[$value_key]=$all_filter_key_values[$option_set['value']]['value'];

		// unused below so far..
		$filter_hash[$option_set['value']]=$all_filter_key_values[$option_set['value']]['value'];
	}
	//var_dump($filter_hash['dbf_sort_by_field:input_field']);

	//5 - specific variables - in this case we want {=field_list} to give a complete list of fields with <br /> separators
	$all_fields_in_table=list_fields_in_table($filter_source);
	$table_field_list=array();
	foreach ($all_fields_in_table as $tablefield){
		$field_class="field_list_unconfigured";
		if ($all_filter_key_values['fields'][$tablefield]){ $field_class="field_list_configured"; }
		$tablefield="<a href=\"Javascript:configure_field($filter_id,'$tablefield')\" class=\"$field_class\">$tablefield</a>";	
		array_push($table_field_list,$tablefield);
	}
	$filter_hash['field_list']=join("<br />",$table_field_list);

	// 6 - we want to configure a few fields manually. Fields to display and/element_type
	//$input_field_key="display_fields:input_field";
	//$all_fields_in_table=list_fields_in_table($filter_source);
	//$all_fields_in_table=join(",",$all_fields_in_table);
	//$filter_hash[$input_field_key]=generate_input_field_from_hash_data("display_fields",$filter_hash['display_fields:value'],$all_fields_in_table,"select",$filter_source,$all_filter_key_options['display_fields']['multiple_set_values']);
	//var_dump($all_filter_key_options);

	//foreach ($all_filter_key_values['fields'] as $fieldname => $arrayvalues){
	//	$filter_hash['field_list'] .= $fieldname . "<br />";
	//};
	$form=new dynamic_form();
	$filter_hash['submit_button']=$form->draw_form_submit_button();
	$filter_hash['reset_button']=$form->draw_form_reset_button();
	// rework a few to textares
	$filter_hash['sql_filter:input_field']="<textarea name=\"global_sql_filter\" id=\"global_sql_filter\">".$filter_hash['sql_filter:value']."</textarea>";
	if ($multiple_keys_for_field['sql_filter']){
		$counter=0;
		foreach ($multiple_keys_for_field['sql_filter'] as $innerhash){
			$extra_sql_filter .= "<br /><b>Overwrite value for user type: ".$innerhash['user_type']."</b><br />";
			$extra_sql_filter .= "<textarea name=\"global_sql_filter-$counter\" id=\"global_sql_filter\">".$innerhash['value']."</textarea>";
			$counter++;
		}
	}
	$filter_hash['sql_filter:input_field']=$filter_hash['sql_filter:input_field'] . $extra_sql_filter;
        $content=hash_into_admin_template_by_key($filter_hash,"filter_keys_edit_template");
        print $content;
}

function interface_edit_configure_fields($interface_id,$field){
	global $db;
	$field=trim($db->db_escape($field));
	print "<p><b>Edit Field: ".ucfirst(str_replace("_"," ",$field))."</b></p>";
	print "<form name=\"add_interface_form\" action=\"" . $_SERVER['PHP_SELF'] . "?action=process_edit_configure_fields\" method=\"post\">";
	// GET THE NAME AND SOURCE DATA FOR THE FILTER 
	$name_sql="select * from filters where id = " . $interface_id;
	$name_result=$db->query($name_sql) or format_error("Cant select from filters table",1);
	while ($row = $db->fetch_array($name_result)){
		$filterName = $row['filter_name'];
		$filterDataSource = $row['source_data'];
	}

	// LOAD ALL OF THE POTENTIAL OPTIONS INTO $filtertypedata
	$sql = "SELECT * from filter_key_options order by interface_type, ordering DESC, value";
	$result=$db->query($sql);
	while ($row = $db->fetch_array($result)){
		foreach ($row as $var => $value){
			$filtertypedata[$row['id']][$var]=$value;

		}
	}
	asort($filtertypedata);

	// LOAD EXISTING DATA
	$select_interface_sql="SELECT * from filter_keys WHERE filter_id = $interface_id";
	$interface_result=$db->query($select_interface_sql) or format_error("Unable to run select interface sql",1);
	while ($row=$db->fetch_array($interface_result)){
		$existing_data[$row['name']]=$row;
		//print "data exists for " . $row['name'];
		// build html for existing values....
		//$randomnumber = rand(9999);
		//$output_text .= "<p>Key1: <select name=\"global_select_$randomnumber\" onChange=\"loadInputField(this.value,$randomnumber)\">";
		//print "e is $existing_data_list";
		//$output_text .= build_select_option_list($all_global_vals);
		//$output_text .= "</select><div id=\"div_$randomnumber\" style=\"display:inline\">";
		//$output_text .= "</div><img src=\"".SYSIMGPATH."/icons/page_delete.png\" onClick=\"function(){node_delete(this.parentNode.id);}\"/></p>";
	}

	$global_filter_values=array();
	$existing_data_values=array();
	$existing_data_key_names=array();
	foreach ($filterypedata as $filter_var => $filter_val){
			if (!$filter_val['related_to_field'] && !$filter_val['parent_id']){ // ignore field related and items with parents
				array_push($global_filter_values,ucfirst(str_replace("_"," ",$filter_val['value']))); 
			} 
			$filter_val['js_value']=str_replace("-","_x_",$filter_val['value']);
			$inputfield = "var inputfield_" . $filter_val['js_value'];
			$existing = "var existing_" . $filter_val['js_value'];
			$helptext = "var helptext_" . $filter_val['js_value'];
			$filter_val['notes']=trim(addslashes(preg_replace("/\s+/"," ",$filter_val['notes'])));
			$filter_val['notes']=preg_replace("/'/","&#145;",$filter_val['notes']);

			// not a set bunch of values - see filter_key_options table where the selections are
			if (!$filter_val['set_values']){
				?>
				<script type="text/javascript">
				<?php echo $inputfield; ?> = "<input type=\"text\" size=\"45\" name=\"globalvalues_-_<?php echo $filter_val['value'];?>\" value=\"<?php echo $existing_data[$filter_val['value']]['value'];?>\">";
				<?php echo $helptext; ?> = "<?php echo $filter_val['notes'];?>";
				<?php if ($existing_data[$filter_val['value']]['value'] && !$filter_val['related_to_field'] && !$filter_val['parent_id']){
						array_push($existing_data_values,$existing_data[$filter_val['value']]['value']);
						array_push($existing_data_key_names,$existing_data[$filter_val['value']]['name']);
				}?>
				<?php echo $existing; ?> = "<?php echo $existing_data[$filter_val['value']]['value']; ?>";
				</script>
				<?php
			} else {
			// here it is a bunch of set values. This can be an SQL query or a csv
				if (stristr($filter_val['set_values'],"SQL:")){
					if (preg_match("/SQL:DESC/i",$filter_val['set_values'])){
						$get_tablename=explode(" ",$filter_val['set_values']);
						if ($get_tablename[1]=="{=tablename}"){$get_tablename[1]=$_POST['tablename'];}
						$select_value_list=list_fields_in_table($get_tablename[1]);
						$select_value_list=implode(",",$select_value_list);
					} else {
						$select_value_list=get_sql_list_values($filter_val['set_values']);
					}
				} else {
					$select_value_list=$filter_val['set_values'];
				}
				$options=build_select_option_list($select_value_list,$existing_data[$filter_val['value']]['value'],"",1,0);
				$options=preg_replace("/\n/","",$options);
				?>
				<script type="text/javascript">
				<?php echo $inputfield; ?> = "<select name=\"globalvalues_-_<?php echo $filter_val['value'];?>\"><?php echo addslashes($options);?></select>";	
				<?php echo $existing; ?> = "<?php echo $existing_data[$filter_val['value']]['value']; ?>";
				<?php if ($existing_data[$filter_val['value']]['value'] && !$filter_val['related_to_field'] && !$filter_val['parent_id']){
						array_push($existing_data_values,$existing_data[$filter_val['value']]['value']);
						array_push($existing_data_key_names,$existing_data[$filter_val['value']]['name']);
						}?>
				<?php echo $helptext; ?> = "<?php echo $filter_val['notes'];?>";
				</script>
				<?php
			// End set values on the next curly bracket
			}
		}

	$sql = "DESC " . $filterDataSource;
	$result=$db->query($sql);
	while ($row=$db->fetch_array($result)){
		$table_description[$row['Field']]=$row['Type'];
	}

	$runFunctionsOnLoad=Array();	
	$existing_field_data=array();
	// get all existing data related to fields
	print "<script type=\"text/javascript\">\n\n";
	$existing_field_sql="SELECT * from filter_keys WHERE field != \"\" and filter_id=\"" . $interface_id . "\"";
	$existing_field_result=$db->query($existing_field_sql) or format_error("Error in existing field sql",1);
	while ($fieldrow = $db->fetch_array($existing_field_result)){
		$existing_field_data[$fieldrow['field']][$fieldrow['name']]=array();
		print "<p style=\"color:green\"><b>Adding existing data of " . $fieldrow['value'] . "</b></p>";
		array_push($existing_field_data[$fieldrow['field']][$fieldrow['name']],$fieldrow['value']);
		?>
var existing___<?php echo $fieldrow['field'] . "___" . $fieldrow['name'] . " = \"" . $fieldrow['value'] . "\";";?>

		<?php
	}
	?>
	function showFieldOptions(whichfieldtoshow,buttonName){
		allFieldNames= new Array("<?php
		$all_table_fields=array();
		foreach ($table_description as $table_field_name => $table_field_type){
			array_push($all_table_fields,"fieldname_".$table_field_name);
		}
		echo join("\",\"",$all_table_fields);
		?>");
		allButtonNames= new Array("<?php
		$all_table_fields=array();
		foreach ($table_description as $table_field_name => $table_field_type){
			array_push($all_table_fields,"button_".$table_field_name);
		}
		echo join("\",\"",$all_table_fields);
		?>");
		for (z=0;z<allFieldNames.length;z++){
			thisElement=document.getElementById(allFieldNames[z]);
			if (allFieldNames[z]==whichfieldtoshow){
				thisElement.style.display="block";
			} else {
				thisElement.style.display="none";
			}
		}
		for (z=0;z<allButtonNames.length;z++){
			buttonElement=document.getElementById(allButtonNames[z]);
			if (allButtonNames[z]==buttonName){
				buttonElement.style.backgroundColor="cyan";
			} else {
				buttonElement.style.backgroundColor="white";
			}
		}
	}

	<?php 
	print "\n</script>\n";
	//$sql="SELECT filter_keys.name,filter_keys.value,filter_keys.field from filter_key_options LEFT JOIN filter_keys ON filter_keys.name=filter_key_options.value WHERE filter_key_options.related_to_field=1";
	//$rv=$db->query($sql);
	$table_field_name=trim($field);
	$table_field_type=$table_description[$field];
	echo "<div class=\"table_field_setup_filter_div\" id=\"fieldname_$table_field_name\"><table><tr><td>\n\n";
	echo "<table class=\"form_table\" style=\"background-color:#f1f1f1;\" bgcolor=\"#f1f1f1\"><tr><td colspan=2></td></tr>\n";
	echo "<tr><td colspan=2><hr></td></tr>";
	echo "<tr><td align=\"right\"><b>MySQL Field Name: </b></td><td>$table_field_name</td></tr>";
	echo "<tr><td align=\"right\"><b>MySQL Column Type: </b></td><td>$table_field_type</td></tr>";
	echo "<tr><td colspan=2><hr></td></tr>";
	foreach ($filtertypedata as $filter_var => $filter_val){
			// if it's related to a field and doesnt have a parent value we can print it
			if ($filter_val['related_to_field'] && !$filter_val['parent_value']){
				$print_name= ucfirst(str_replace("_"," ",$filter_val['value']));
				// if its not set values then its free text - just print a row, input field and put the values in - easy
				if (!$filter_val['set_values']){
					// check if we have a value here from the existing filter for a text based filter key related to a field.
					if ($existing_field_data[$table_field_name][$filter_val['value']]){
						//print "Looking in array for " . $table_field_name . " -> " . $filter_val['value'] . "<br>";
						$existing_value=$existing_field_data[$table_field_name][$filter_val['value']][0];
						//var_dump($existing_field_data[$table_field_name][$filter_val['value']][0]);
						//print "GOT EXISTING VALUE FOR " . $filter_val['value'] . " OF " . $existing_field_data[$table_field_name][$filter_val['value']][0];
					} else {
						$existing_value="";
					}
					print "<tr><td align=\"right\" valign=\"top\">" . $print_name . ": </td>\n<td><input type=\"text\" name=\"" . $table_field_name . "_-_" . $filter_val['value'] . "\" value=\"$existing_value\"><a href=\"\" style=\"float:right\"><img src=\"".SYSIMGPATH."/icons/help.png\" border=0/></a></td></tr>\n\n";
				} else {
					// here it is based on set values then..!
					if ($existing_field_data[$table_field_name][$filter_val['value']]){
						$existing_value=$existing_field_data[$table_field_name][$filter_val['value']][0];
					} else {
						$existing_value="";
					}
					//print "have select values for $filter_var and " . $filter_val['value'] ." def is " . $filter_val['default_value']. "<br />";
					//print "Existing data is $existing_value<br />";
					
					$selectedyet=0;
					$select_list_options=explode(",",$filter_val['set_values']);
					$selectlist="";
					foreach ($select_list_options as $select_option){
						if (stristr($select_option,";;")){
							$option_pair=explode(";;",$select_option);
							$print_option=$option_pair[1];
							$option_value=$option_pair[0];
						} else {
							$print_option = ucfirst(str_replace("_"," ",$select_option));
							$option_value=$select_option;
						}
						$selectlist .= "<option value=\"$option_value\"";
						if ($select_option==$existing_value){ $selectlist .= " selected"; $selectedyet=1; print "Setting selected on $select_option and $existing_value";}

						// textarea? need to show advanced options onload..
						if ($select_option == "textarea" && $table_field_type == "text"){ 
							//$selectlist .= " selected"; 
							array_push($runFunctionsOnLoad,"showNextField(" . $filter_val['id'] . ",'" . $table_field_name  . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "')");
						}

						// select list? need to show the advanced options onload..
						if ($select_option == "select" && $existing_value==$select_option){
							array_push($runFunctionsOnLoad,"showNextField(" . $filter_val['id'] . ",'" . $table_field_name . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "')");
						}

						// text? need to show advanced options onload..
						if ($select_option == "text"){ 
							//$selectlist .= " selected"; 
							array_push($runFunctionsOnLoad,"showNextField(" . $filter_val['id'] . ",'" . $table_field_name  . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "')");
						}

						if ($select_option == "text" && preg_match("/varchar/i",$table_field_type && !$selectedyet)){ $selectlist .= " selected";}
						$selectlist .= ">$print_option</option>\n";


					}
					//if (!$selectedyet){ print "<p>NOthing was selected?</p>"; }

					print "<tr style=\"background-color:#e1e1e1\"><td align=\"right\" valign=\"top\" style=\"vertical-align:top\"><!--WOo2//-->" . $print_name . ": </td><td>\n<select name=\"" . $table_field_name . "_-_" . $filter_val['value'] . "\" onChange=\"if (this.value) {showNextField(" . $filter_val['id'] . ",'" . $table_field_name . "','" . $table_field_name . "---" . $filter_val['value'] . "','" . $filter_val['value'] . "');}  else { clearField(" . $filter_val['id'] . ",'" . $table_field_name . "---" . $filter_val['value'] . "')}\">" . $selectlist . "</select>";
					print "<a href=\"\"><img src=\"".SYSIMGPATH."/icons/help.png\" border=0 /></a>\n";
					print "<div id=\"$table_field_name" . "---" . $filter_val['value'] . "\" style=\"display:inline\">  </div>\n";
					// now select everyting with a parent id of this one
					$select_next_sql="SELECT * from filter_key_options WHERE parent_id = " . $filter_val['id'] . " ORDER BY ordering DESC";
					$result=$db->query($select_next_sql) or format_error ("Mysql error 81XY",1);
					$subResultCounter=0;
					print "<script language=\"Javascript\">\n";
					print "interfaceItem[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceDefs[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceTypeAssocs[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceValues[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceSelectedValue[" . $filter_val['id'] . "] = new Array();\n";
					print "interfaceElementType[" . $filter_val['id'] . "] = new Array();\n";
					
					while ($subrow=$db->fetch_array($result)){
						print "interfaceItem[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['value'] . "\";\n";
						print "interfaceDefs[".$filter_val['id']."][".$subResultCounter."] = \"" . preg_replace("\n","<br />",$subrow['notes']) . "\";\n";
						print "interfaceTypeAssocs[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['parent_value'] . "\";\n";
						print "interfaceValues[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['set_values'] . "\";\n";
						print "interfaceSelectedValue[".$filter_val['id']."][".$subResultCounter."] = \"" . $existing_value . "\";\n";
						print "interfaceElementType[".$filter_val['id']."][".$subResultCounter."] = \"" . $subrow['element_type']. "\";\n";
						$subResultCounter++;
					}	

					print "</script>\n";
					print "</td></tr>\n";
				}
			}
		}
	print "</table>\n</td></tr></table></div>\n\n";
	print "<div style=\"float:right\"><input type=\"submit\" value=\"Save Field Configuration\" /></div><br clear=\"all\" />";
	print "</form>";
	//while ($h=$db->fetch_array($rv)){
	//	print "<p>".$h['name'] . " - " . $h['value'];
	//}
	print "<hr size=\"1\" />";
	foreach ($runFunctionsOnLoad as $eachFunction){
		print "<script language=\"Javascript\">\n";
		print $eachFunction . ";\n";
		print "</script>";
		print $eachfunction;
	}

}

function generate_input_field_from_hash_data($field_name,$key_array,$svl,$el_type,$filter_source,$svl_multiple){
	global $libpath;
	if ($key_array || !$key_array){
		require_once("$libpath/classes/core/form.php");
		$key_form=new dynamic_form();
		//print "<p>Key from ";
		//var_dump($key_array['name']);
		//var_dump($key_array['value']);
		$key_form->make_field($field_name,"global_".$field_name);
		$key_form->add_to_field($field_name,"value",$key_array['value']);
		if ($el_type=="checkbox"){
			$key_form->add_to_field($field_name,"db_field_type","tinyint(1)");
			$input_field=$key_form->draw_checkbox_input_field($field_name);
		} else if ($svl){
			$svl=str_replace("{=tablename}","{=table}",$svl);	
			$svl=str_replace("{=table}",$filter_source,$svl);	
			$key_form->add_to_field_filter($field_name,"select_value_list",$svl);
			if (!$svl_multiple){
				$input_field=$key_form->draw_select_input_field($field_name);
			} else {
				$input_field=$key_form->draw_select_input_field($field_name,1);

			}
		} else {
			$input_field=$key_form->draw_default_input_field($field_name);
		}
	}
	return $input_field;
}

function process_edit_configure_fields($filter_id){
	global $db;
	$sql_delete="DELETE FROM filter_keys WHERE filter_id=$filter_id AND field != \"\"";	
	// $rv=$db->qery($sql_delete);
	foreach ($_POST as $var=>$value){
		list($fieldname,$key)=explode("_-_",$var);
		if ($value){
			print "<p>$key = $value</p>";
			$sql="INSERT INTO filter_keys (filter_id,name,field_value) VALUES ($filter_id,$key,$fieldname,$value)";
		}
	}
	print "<p class=\"dbf_para_success\">Field $fieldname Saved Successfully</p>";
}

?>
