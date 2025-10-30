<?php

/* File: tables.php
 * Meta: helper functions for dealing with database tables
*/

function list_fields_in_table($tablename){
	global $db;
	$return_fields=array();
	$sql = "desc " . $tablename;
	$result=$db->query($sql);
	while ($row = $db->fetch_array($result)){
		array_push ($return_fields, $row['Field']);
	}
	sort($return_fields);
	return $return_fields;
}

function is_field_in_table($tablename,$field){
	global $db;
	$found=0;
	$fields_list=list_fields_in_table($tablename);
	foreach ($fields_list as $table_field){
		if ($table_field==$field){
			$found=1;
		}	
	}
	return $found;
}


function new_table_front(){
	global $database_name;
	$mysql_col_types="bigint,binary,blob,char,date,datetime,decimal,enum,float,int,mediumblob,mediumint,mediumtext,numeric,real,set,smallint,text,time,timestamp,tinyblob,tinyint,tinytext,varbinary,varchar,year";
	$mysql_options=database_functions::build_select_option_list($mysql_col_types,"int",0,0,0);
	?>
	<p class="admin_header"><img src="<?php echo SYSIMGPATH;?>/icons/table_gear.png"> <font size='3' color='1b2c67'>Add New Table to database: <?php echo $database_name;?> </p><br>
	<form action="<? echo $_SERVER['PHP_SELF']; ?>?action=add_new_db_table" onsubmit="return checkTableName()" name="addTableForm" method="post">
	<b>Table Name: </b><input type="text" name="new_table_name" value=""><br /><span style="font-size:12px">Your table should have a unique name and only contain a-z, A-Z, 0-9 and underscore characters.<br />Any spaces will be converted to underscores automatically.</span>
	<br />
	<br />
	<style type="text/css">
	#create_table_help ol li {font-size:12px;}
	</style>
	<p>For help on MySQL column types please <a onClick="document.getElementById('create_table_help').style.display='block';">Click here</a></p>
	<div style="font-size:11px; background-color:#ddd; padding:5px; display:none" id="create_table_help" name="create_table_help">
	<p style="font-size:12px"><b>MySQL Column Types: Quickstart guide:</b><br /><br />If you are new to creating MySQL tables, you can follow the following *very basic* instructions:</p>
	<ol style="font-size:12px;">
	<li style="font-size:12px;">Give each table column a unique and logical name which is descriptive of what it contains. Do not use spaces, but use underscores in place. Underscores will be replaced with spaces automatically when column names are displayed, and any spaces that you do enter in the name will be replaced with underscores automatically as the column is created.</li>
	<li>There are many MySQL column types can be used. Follow the following very basic rules if you are not familiar with them:
		<ul>
			<li>For single line text field input fields, select 'Varchar', which literally means 'variable number of characters'. You <u>must</u> then enter the maximum number of characters that the field can hold in the 'Options/Extra' field for that column type. MySQL is limited to 255 characters up to version 5.0.3, and to 65535 characters in later versions.</li>
			<li>For larger amounts of text select the 'Text' field type. This will default to the large 'textarea' form input type which may be styled using the Rich Text Editor (though you can override this in a data filter later).</li>
			<li>For numeric types select 'int' if you require integers only, or float if you require floating point numbers. For prices use decimal (x,2) where x is the maximum number of digits the column can hold - eg. decimal(5,2) contains 2 digits after the . and up to 3 before. All numeric types will default to the regular text input fields but shorter, normally showing just a few characters (NB: Form field widths are setable and changeable using filters) .</li>
			<li>For dates select 'date', for times select 'time' and for date and time use 'datetime'. Note that the 'date' type defaults to the dbForms date form input type (comprising of three select boxes). You will need to set up validation rules in your filter for other input types to ensure that the user input matches the required format for the database.</li>
			<li>For checkbox entry (yes/no / on/off / 0/1 use the column type 'tinyint' and enter the number 1 in the Options/Extra box. Tinyint(1) can only store 0 or 1 and defaults to a single checkbox, entering the value '1' if it is checked and '0' if not.</li>
			<li>For entry requiring a select drop down box you can use varchar again if you are specifying a comma separated list of a few values, or if you are storing the data to populate the list the recommended value is int - where the id of the record you are relating to only is stored and the value is looked up from another column in that table. Note that this lookup is done via a <i>data filter</i> which is applied to a form separately. If you know you are storing numeric values in this field you should use one of the int types, if you are using text values varchar is most likely suitable.</li>
		</ul></li>
	<li>For default column values, enter these into the 'Default' column. This default applies only to where no other input is added to the field of data. Note that the default balue is  NOT passed back to dbForms for display in forms, so for the default to work simply do not include the column in the form and the default value will be added to the field when other fields in the row are filled in.</li>
	<li>Remember that this is a quick start guide for the uninitiated. For a full explanation of column types and MySQL databases try searching 'MySQL Column Types'. Many advanced options are supported here.</li>
	</ol>
	</div>
	<br />
	<table name="addTable" id="addTable">
	<tr bgcolor='#ccc' style="font-weight:bold"><td>Field Name</td><td>Field Type</td><td>Options/Extra</td><td>Unsigned <font size='1'>(integers<br />only)</font></td><td>Default</td><td>Accept Null</td><td>PRI</td><td>Auto Inc</td></tr>
	<tr>
	<td><input type="text" name="field_1_name" value="id"></td><td><select name="field_1_type" value=""><?php echo $mysql_options;?></select></td><td><input type="text" name="field_1_options" value=""></td><td><input type="checkbox" name="field_1_unsigned"></td><td><input type="text" name="field_1_default" size=4></td><td><select name="field_1_null"><option value="Null Accepted">Null Accepted</option><option value="Not Null" selected>Not Null</option></select></td><td><input type="radio" name="primary_key" value="1" checked></td><td><input type="checkbox" name="field_1_autoinc" checked></td><td><div id="note1" style="font-size:9px">This autoincrementing id column has been created for you to start</div></td></tr>
	</table>
	<p>
	<a href="Javascript:addRowToTable()">-&gt; New Row</a><p>
	<script language="Javascript" type="text/javascript">
	function checkTableName(){
		if (!document.forms['addTableForm'].elements['new_table_name'].value){
			alert("Please enter a name for this table.");
			return false;
		} else {
			return true;
		}
	}
	</script>
	<input type="submit" value="Save New Table">
	</form>

	<script language="Javascript">

	var noteArray=new Array();
	noteArray['date'] = "Date field - holds a date in the format yyyy-mm-dd.";
	noteArray['datetime'] = "Datetime field - holds a date and time in the format yyyy-mm-dd hh:mm:ss.";
	noteArray['time'] = "Time field - holds a time in the format hh:mm:ss.";
	noteArray['timestamp'] = "Enter CURRENT_TIMESTAMP in the default column so that this column defaults to the current date/time. Enter ON UPDATE CURRENT_TIMESTAMP in the options column so that the field automatically populates with the current time if the record is updated but this column is not.";
	noteArray['text'] = "Text field - no options / extra data permitted";
	noteArray['varchar'] = "Variable number of characters. Please enter the max no. of characters (1-255) for mysql versions lower than 5.0.3, 1-65535 for mysql version 5.0.3 and above)";
	noteArray['varbinary'] = "Please enter the max no. of characters (1-255)";
	noteArray['char'] = "Please enter the exact no. of characters. Note this field can only contain strings containing this exact number of characters.";
	noteArray['binary'] = "Please enter the no. of characters";
	noteArray['enum'] = "Please enter the options comma separated and in quotes";
	noteArray['tinyint'] = "Please enter the maximum value (up to 128 or 255 if unsigned) or leave blank for the maximum.";
	noteArray['int'] = "Please enter the maximum value (up to 2147483647 or 4294967295 if unsigned) or leave blank for the maximum.";
	noteArray['mediumint'] = "Please enter the maximum value (up to 8388607 or 16777215 if unsigned) or leave blank for the maximum.";
	noteArray['smallint'] = "Please enter the maximum value (up to 32767 or 65535 if unsigned) or leave blank for the maximum.";
	noteArray['bigint'] = "Please enter the maximum value (up to 9223372036854775807 or 18446744073709551615 if unsigned) or leave blank for the maximum.";
	function getNote(whichRow,whichNote){
		divName = "note" + whichRow;
		eval("document.getElementById('" + divName + "').innerHTML='" + noteArray[whichNote] + "'");
	}

	var fieldCounter=2; // set to the value of the NEXT row to insert
	function addRowToTable(){
	var newRow = document.createElement('tr');
	var newCell = document.createElement('td');
	var newCell2 = document.createElement('td');
	var newCell3 = document.createElement('td');
	var newCell4 = document.createElement('td');
	var newCell5 = document.createElement('td');
	var newCell6 = document.createElement('td');
	var newCell7 = document.createElement('td');
	var newCell8 = document.createElement('td');
	var newCell9 = document.createElement('td');
	
	// field name input
	var fieldNameValue="field_"+fieldCounter+"_name";
	var fieldTypeValue="field_"+fieldCounter+"_type";
	var fieldOptionsValue="field_"+fieldCounter+"_options";
	var fieldUnsignedValue="field_"+fieldCounter+"_options";
	var fieldNullValue="field_"+fieldCounter+"_null";
	var fieldAutoincValue="field_"+fieldCounter+"_autoinc";
	var fieldDefaultValue="field_"+fieldCounter+"_default";

	var newFieldName = document.createElement('input');
	newFieldName.setAttribute("name",fieldNameValue);
	newFieldName.setAttribute("value","");

	//types drop down;
	var newFieldType = document.createElement('select');
	newFieldType.setAttribute("name",fieldTypeValue);
	changeAttribute="getNote(" + fieldCounter + ",this.value)";
	newFieldType.setAttribute("onChange",changeAttribute);

	// options for field types
	<?php
	$col_types_array=explode(",",$mysql_col_types);
	$count=0;
	foreach ($col_types_array as $col_value){
		print "var newOption$count = document.createElement('option');\n";
		print "newOption$count.setAttribute(\"value\",\"$col_value\");\n";
		print "optionText = document.createTextNode(\"$col_value\");";
		print "newOption$count.appendChild(optionText);\n";
		print "newFieldType.appendChild(newOption$count)\n\n";
		$count++;
	}
	?>

	var newExtra = document.createElement('input');
	newExtra.setAttribute('name',fieldOptionsValue);

	var newDefault= document.createElement('input');
	newDefault.setAttribute('name',fieldDefaultValue);
	newDefault.setAttribute('size','4');

	var newNull = document.createElement('select');
	newNull.setAttribute('name',fieldNullValue);
	newNullOption = document.createElement('option');
	newNullOption.setAttribute('value','Null Accepted');
	nullOption1Text = document.createTextNode('Null Accepted');
	newNullOption.appendChild(nullOption1Text);
	newNull.appendChild(newNullOption);

	newNullOption = document.createElement('option');
	newNullOption.setAttribute('value','Not Null');
	nullOption1Text = document.createTextNode('Not Null');
	newNullOption.appendChild(nullOption1Text);
	newNull.appendChild(newNullOption);

	var newUnsigned= document.createElement('input');
	newUnsigned.setAttribute('type','checkbox');
	newUnsigned.setAttribute('name',fieldAutoincValue);
	newUnsigned.setAttribute('value','on');

	var newPri = document.createElement('input');
	newPri.setAttribute('type','radio');
	newPri.setAttribute('name','primary_key');
	newPri.setAttribute('value',fieldCounter);

	var newAutoInc = document.createElement('input');
	newAutoInc.setAttribute('type','checkbox');
	newAutoInc.setAttribute('name',fieldAutoincValue);
	newAutoInc.setAttribute('value','on');

	var newNote = document.createElement('div');
	newNote.setAttribute('style','font-size:9px');
	var noteName="note" + fieldCounter;
	newNote.setAttribute('id',noteName);
	
	newCell.appendChild(newFieldName);
	newCell2.appendChild(newFieldType);
	newCell3.appendChild(newExtra);
	newCell4.appendChild(newUnsigned);
	newCell5.appendChild(newDefault);
	newCell6.appendChild(newNull);
	newCell7.appendChild(newPri);
	newCell8.appendChild(newAutoInc);
	newCell9.appendChild(newNote);

	newRow.appendChild(newCell);
	newRow.appendChild(newCell2);
	newRow.appendChild(newCell3);
	newRow.appendChild(newCell4);
	newRow.appendChild(newCell5);
	newRow.appendChild(newCell6);
	newRow.appendChild(newCell7);
	newRow.appendChild(newCell8);
	newRow.appendChild(newCell9);
	
	document.getElementById("addTable").appendChild(newRow);
	fieldCounter++; // next row will have this id
	}
	
	</script>

<?php
}

function add_new_db_table(){

	global $db;
	if ($debug){var_dump($_POST);}

	$new_table_name=trim($_POST['new_table_name']);
	$new_table_name=preg_replace("/\s+/","_",$new_table_name);
	$sql = "CREATE TABLE " . $new_table_name . " (";
	$stop=0;
	$i=1;

	do { 
		$name="field_".$i."_name";
		$type="field_".$i."_type";
		$options="field_".$i."_options";
		$default="field_".$i."_default";
		$null="field_".$i."_null";
		$autoinc="field_".$i."_autoinc";
		$sql .= $_POST[$name] . " " . $_POST[$type];
		if ($_POST[$type]=="varchar" || $_POST[$type]=="enum" || ($_POST[$options] && ($_POST[$type]=="tinyint" || $_POST[$type]=="int" || $_POST['type']=="bigint" || $_POST['type']=="mediumint" || $_POST['type']=="smallint"))){
			$sql .= "(".$_POST[$options].")";
		} else if ($_POST[$options]){
			$sql .= " " . $_POST[$options];
		}
		if ($_POST[$default]){
			if ($_POST[$type]=="timestamp"){$default_value=$_POST[$default];}else{$default_value="\"".$_POST[$default]."\"";}
			$sql .= " DEFAULT " . $default_value;
		}
		if ($_POST[$autoinc]=="on"){ $sql .= " AUTO_INCREMENT";}
		if ($_POST[$null]=="Not Null"){ $sql .= " NOT NULL";}
		if ($_POST['primary_key']==$i){ $sql .= " PRIMARY KEY";}
		$sql .= ", ";
		$i++;
		$test_name="field_".$i."_name";
		if (!$_POST[$test_name]){$stop=1;}
	} while (!$stop);

	$sql = preg_replace("/, $/","",$sql);
	$sql = preg_replace("/--/","",$sql);
	$sql = str_replace(";","",$sql);
	$sql .= ")";
	print "<p class=\"admin_header\">Create MySQL Table</p><b>Preparing to run the following sql:</b><br />" . $sql . "<p><b>Result:</b>";
	$create_table_result=$db->query($sql) or format_error("<font color='red'><b>Error:</b> ".$db->db_error()."</font>",1);
	print "<span style=\"color:green\">Table $new_table_name has been created successfully.</span><p><b>Go to:</b><br />";
	print "<p><a href=\"administrator.php?action=list_table&t=$new_table_name&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1\">View Table (to start adding data) </a></p>";
	print "<p><a href=\"administrator.php?action=view_table_schema&table=$new_table_name\">View table schema</a></p>";
	print "<p><a href=\"administrator.php?action=add_field_to_table&table=$new_table_name\">Add another field to this table</a></p>";
}

function view_table_schema($tablename){
	
	global $db;
	if (!stristr($_SERVER['SCRIPT_FILENAME'],"mui-administrator")){
		print "<p class=\"admin_header\">Table Schema For: $tablename &nbsp; &nbsp; &nbsp; <span style=\"float:right; margin-right:40px;\"><a href=\"administrator.php?action=sysListTables\">-&gt;Show All Tables</a></span></p>";
	}

	print "<table class=\"bordered_table\"><thead>";
	print "<tr><td>Field Name</td><td>Data Type</td><td>Null</td><td>Key</td><td>Default</td><td>Extra</td></tr></thead><tbody>";
	$sql="desc $tablename";
	$res=$db->query($sql);
	while ($h=$db->fetch_array($res)){
		print "<tr>";
		print "<td>".$h['Field']."</td>";
		print "<td>".$h['Type']."</td>";
		print "<td>".$h['Null']."</td>";
		print "<td>".$h['Key']."</td>";
		print "<td>".$h['Default']."</td>";
		print "<td>".$h['Extra']."</td>";
		print "</tr>";
	}
	print "</tbody></table>";
	print "<p style=\"margin-left:25px\"><b>Actions:</b><br /><ul style=\"list-style-type:square\">";
	print "<li><a href=\"administrator.php?action=delete_table&table=$tablename\">Delete Table From System (removes all data and deletes table entirely - NON-REVERSIBLE - REQUIRES SPECIAL PERMISSIONS)</a></li>";
	print "<li><a href=\"administrator.php?action=add_field_to_table&table=$tablename\">Add Field To Table</a></li>";
	print "</ul>";
}

function delete_table($tablename){
	// As a protection mechanism, only superadmin and god can drop tables, and as an extra precaution, the table must first be added to permissions as a drop permission,  and then removed once the table has been deleted. This helps to avoid any accidents
	global $db;
	global $user;
	print "<p class=\"admin_header\">Delete table $tablename</p>";
	if ($user->value("Type") != "superadmin" && $user->value("Type") != "god" && $user->value("Type") != "master"){
		print format_error("You do not have permissions to do this.");
		exit;
	}

	$permissions=check_dbf_permissions($tablename,"drop");
	if ($permissions['Status']==1){
		$sql = "drop table $tablename";
		$result=$db->query($sql) or die($db->db_error());
		print "Table $tablename has been removed from the database.";
	} else {
		print format_error("You dont have permission to do that.\n\nTables can only be dropped after a drop permission has explicitly been declared,\nand even then only by user types of superadmin, master and god.\n\nIf you wish to delete this table through this interface\nyou must add a drop permission before removing the table.\nYou can delete the drop permission afterwards if you wish.");
		exit;
	}
}

function add_field_to_table($tablename){
	global $database_name;
	global $db;
	$mysql_col_types="bigint,binary,blob,char,date,datetime,decimal,enum,float,int,mediumblob,mediumint,mediumtext,numeric,real,set,smallint,text,time,timestamp,tinyblob,tinyint,tinytext,varbinary,varchar,year";
	$mysql_options=database_functions::build_select_option_list($mysql_col_types,"int",0,0,0);
	?>
	<p class="admin_header"><img src="<?php echo SYSIMGPATH;?>/icons/table_gear.png"> Add New Field to Table: <?php echo $tablename;?> </></p>
	<form action="<? echo $_SERVER['PHP_SELF']; ?>?action=process_add_field_to_table" method="post">
	<input type="hidden" name="tablename" value="<?php echo $tablename;?>">
	<table class="bordered_table">
	<thead>
	<tr><td>Field Name</td><td>Field Type</td><td>Options/Extra</td><td>Unsigned <font size='1'>(integers only)</font></td><td>Default<span style="font-size:8px">(include &quot;'s for string data types)</span></td><td>Accept Null</td><td>PRI</td><td>Auto Inc</td></tr>
	</thead><tbody>
	<tr>
	<td><input type="text" name="field_1_name" value=""></td><td><select name="field_1_type" value=""><?php echo $mysql_options;?></select></td><td><input type="text" name="field_1_options" value=""></td><td><input type="checkbox" name="field_1_unsigned" value=""></td><td><input type="text" name="field_1_default" size=4></td><td><select name="field_1_null"><option value="Null Accepted">Null Accepted</option><option value="Not Null" selected>Not Null</option></select></td><td><input type="checkbox" name="primary_key" value="1"></td><td><input type="checkbox" name="field_1_autoinc"></td><td><div id="note1" style="font-size:8px"></div></td></tr>
	</tbody></table>
	<br />
	Add after this field: <select name="after_field">
	<option value="DBF_FIRST">FIRST</option>
	<?php print tablefields_as_select_options($tablename);?>
	</select>
	<br /><br />
	<input type="submit" value="Add Row To Table">
	</form>
<?php
}

function process_add_field_to_table(){

	global $db;
	$tablename=$_REQUEST['tablename'];
	$addfield_name=$_REQUEST['field_1_name'];
	$addfield_type=$_REQUEST['field_1_type'];
	$addfield_options=$_REQUEST['field_1_options'];
	$addfield_unsigned=$_REQUEST['field_1_unsigned'];
	$addfield_null=$_REQUEST['field_1_null'];
	$addfield_pri=$_REQUEST['primary_key'];
	$addfield_inc=$_REQUEST['field_1_autoinc'];
	$addfield_default=$_REQUEST['field_1_default'];

	$sql = "ALTER TABLE " . $tablename . " ADD " . $addfield_name . " " . $addfield_type;
	if (($addfield_type=="varchar" || $addfield_type=="enum" || $addfield_type=="int" || $addfield_type=="tinyint" || $addfield_type=="mediumint" || $addfield_type=="decimal") && $addfield_options && $addfield_options){
		$sql .= "(".$addfield_options.")";
	} else if ($addfield_options){
		$sql .= " " . $addfield_options;
	}
	if ($addfield_default){
		$sql .= " DEFAULT " . $addfield_default;
	}

	if ($_POST['after_field']=="DBF_FIRST"){
		$sql .= " FIRST";
	} else {
		$sql .= " AFTER " . $_POST['after_field'];
	}

	//print "Running the following sql: $sql";
	$result=$db->query($sql) or format_error("Cannot add field: <br />Query: $sql<br />" . $db->db_error(),1);
	print "The following field has been added to the $tablename table: $addfield_name<p>";
	print "<p><a href=\"administrator.php?action=list_table&t=$tablename&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1\">View Table Data</a></p>";
	print "<p><a href=\"administrator.php?action=view_table_schema&table=$tablename\">View Table Schema</a></p>";
	print "<p><a href=\"administrator.php?action=add_field_to_table&table=$tablename\">Add Another Field</a></p>";
}

function dump_table($table){

	print "<p class=\"admin_header\">Dumping $table...</p>";
	global $database_username;
	global $database_name;
	global $database_password;
	$dbdump_filename = IOPATH;
	$dbdump_filename .= "/" . $database_name ."_" . $table . "_dump.sql";
	$filename_local = IOPATH. "/" . $database_name . "_" . $table . "_dump.sql";

	$extras = "";
	if ($_POST['export_type']=="data_only"){ $extras .= "--no-create-info ";} 
	if ($_POST['export_type']=="structure_only"){ $extras .= "--no-data ";} 
	if ($_POST['specify_columns']=="specify_columns"){ $extras .= "--complete-insert ";} 
	if ($_POST['datatypes']=="system"){ $extras .= "--where=system=1 ";}
	if ($_POST['datatypes']=="user"){ $extras .= "--where=\"system IS NULL OR system=0\" ";}
	$extras=preg_replace("/ $/","",$extras);

	$exec_string="mysqldump -u $database_username $database_name $table $extras -p$database_password > $dbdump_filename 2>&1";
	$exec_result=passthru($exec_string);
	$exec_string_print=preg_replace("/\-p\w+ /","-p*PASSWORD * ",$exec_string);
	print "<p>Preparing to execute the following command:<br><pre>$exec_string_print</pre><p>";
	//$dumpresult = shell_exec("perl dump_database.cgi");
	print"<p class=\"dbf_para_success\">Database has been exported as " . $dbdump_filename . "</p>";
	print "<p><form name=\"raw_disaply_form\"><textarea rows=10 cols=60 name=\"raw_display\">";
	$sql=file_get_contents($filename_local);
	$sql=preg_replace("/textarea/","txtareareplacestring",$sql);
	print $sql;
	print "</textarea></form>";
}

function export_table_front($table){

	print "<div class=\"table_title\">Export Table: $table</div><br clear=\"all\">";
	print "<form name=\"export_table_form\" action =\"".$_SERVER['PHP_SELF']."?action=dump_table\" method=\"post\">";
	print "<input type=\"hidden\" name=\"t\" value=\"$table\">";
	print "<table>";	
	print "<tr><td align=\"right\"><b>Options: </b></td></tr>";
	print "<tr><td align=\"right\">Data To Export: </td><td> <select name=\"datatypes\"><option value=\"all\">All Data</option><option value=\"system\">System Data Only</option><option value=\"user\">User Data Only</option><option value=\"none\">No Data</option></select></td></tr>";
	print "<tr><td align=\"right\">Structure / Data: </td><td> <select name=\"export_type\"><option value=\"all\">Structure And Data</option><option value=\"data_only\">Data Only</option><option value=\"structure_only\">Structure Only</option></select></td></tr>";
	print "<tr><td align=\"right\">Specify Columns in insert statement: </td><td><select name=\"specify_columns\"><option value=\"specify_columns\">Yes</option><option value=\"no_column_listing\">No</option></select></td></tr>";
	print "<tr><td align=\"right\">Export Format: </td><td> <select name=\"dataformat\"><option value=\"dump\">Mysql Data Dump</option><option value=\"csv\">CSV</option><option value=\"formatted_list\">Formatted List</option></select></td></tr>";
	print "<tr><td align=\"right\" valign=\"top\">Where clause: </td><td> <textarea name=\"where_clause\" cols=\"80\" rows=\"3\"></textarea></td></tr>";
	print "<tr><td align=\"right\"></td><td><input type=\"submit\" value=\"Export Table\"></td></tr></table></form>";
}

function dump_table_to_dir($tablename){
	global $db;
	$table_options="SELECT * from table_options where table_name = \"$tablename\"";
	$table_options_result=$db->query($table_options);
	while ($tab_options = $db->fetch_array($table_options_result)){
		if ($tab_options['table_option']=="field_as_filename"){$tablefield_for_name=$tab_options['option_value'];}
		if ($tab_options['table_option']=="field_as_contents"){$tablefield_for_content=$tab_options['option_value'];}
		if ($tab_options['table_option']=="file_extension"){$ext=$tab_options['option_value'];}
		if ($tab_options['table_option']=="corresponds_to_directory"){$dir=$tab_options['option_value'];}
	}

	$sql = "SELECT * from $tablename";
	$result=$db->query($sql);

	while ($results = $db->fetch_array($result)){
		$full_filename=$dir . "/" . $results[$tablefield_for_name] . "." . $ext;
		print "Writing $full_filename...<br />";
		$put_it=file_put_contents($full_filename,$results[$tablefield_for_content]) or die ("Error: " . E_ERROR);
	}
	print "<p>$table <b>$tablename</b> has been written out to the $dir directory successfully.";
}

function load_table_from_dir($tablename){
	global $db;
	if (!$tablename){format_error("No Table specified to load",1); exit;}
	$table_options="SELECT * from table_options where table_name = \"$tablename\"";
	$table_options_result=$db->query($table_options);
	while ($tab_options = $db->fetch_array($table_options_result)){
		if ($tab_options['table_option']=="field_as_filename"){$tablefield_for_name=$tab_options['option_value'];}
		if ($tab_options['table_option']=="field_as_contents"){$tablefield_for_content=$tab_options['option_value'];}
		if ($tab_options['table_option']=="file_extension"){$ext=$tab_options['option_value'];}
		if ($tab_options['table_option']=="corresponds_to_directory"){$dir=$tab_options['option_value'];}
	}

	// get directory listing
	$load_data=array();
	$files_to_load=get_directory_list($dir);
	foreach ($files_to_load as $loadfile){
		if ($loadfile=="."){continue;}
		if ($loadfile==".."){continue;}
		$full_path=$dir . "/" . $loadfile;
		if (is_dir($full_path)){continue;}
		if (!stristr($loadfile,$ext)){continue;}
		$contents=file_get_contents($full_path);
		//$contents = str_replace("\"","\\\"",$contents);
		$contents=$db->db_escape($contents);
		$name_only=preg_replace("/.$ext/","",$loadfile);
		$load_data[$name_only]=$contents;
		$select_contents="SELECT * from $tablename where $tablefield_for_name = \"" . $name_only . "\"";
		//print $select_contents . "<p>"; 
		$content_result=$db->query($select_contents) or print "ERROR in $select_contents" . $db->db_error();
		//print "NUM IS " . $db->num_rows($content_result);
		if ($db->num_rows($content_result)>0){
			$update_sql = "UPDATE $tablename SET $tablefield_for_content = \"$contents\" WHERE $tablefield_for_name = \"$name_only\"";	
			print "&bull; Updating table '$tablename' with the live file for $name_only.<br />";
		} else {
			$update_sql = "INSERT INTO $tablename ($tablefield_for_name,$tablefield_for_content) VALUES(\"".$name_only."\",\"".$contents."\")";
			print "&bull; Adding the live file $name_only to table '$tablename'<br />";
		}
		$update_the_table=$db->query($update_sql) or format_error("ERROR in $update_sql: " . $db->db_error(),1);
	}

	print "<p>Finished loading table.</p><p><a href=\"".get_link("administrator.php?action=list_table&t=$tablename")."\">List Table</a></p>";
	// delete table contents first;
	//$delete_sql="DELETE FROM $tablename WHERE 1";
	//print $delete_sql; exit;
	//$delete_result=$db->query($delete_sql);

	// write out the new files...
}

function load_table_data_from_file($table){
	global $db;
	if (!$table){print "No table specified. Function cannot run."; return;}
	print "<p class=\"admin_header\">Load data into '" . ucfirst(str_replace("_"," ",$table)) . "'</p>";
	print "<p>Populate a table with data from a csv file.</p>";

	if (!$_POST['loadfile']){
		// get all the initial options
		import_data_first_page($table);
	} else if (!$_POST['load_now']) {
		import_data_second_page($table);
	} else if ($_POST['load_now'] && $_POST['table'] && $_POST['delimiter'] && $_POST['loadfile']){
		print "loading..";
		$loadfile=$_POST['loadfile'];
		$filecontents=file_get_contents($loadfile) or print "Cannot get contents of $loadfile";
		$filelines=split("\n",$filecontents);
		foreach ($filelines as $line){
			$sql = "INSERT INTO $table values(";
			if (!$_POST['file_contains_pk']){
				$sql .= "\"\",";
			}
			$filefields=explode($_POST['delimiter'],$line);
			foreach ($filefields as $field){
				$sql .= "\"$field\",";
			}
			$sql = preg_replace("/,$/","",$sql);
			$sql .= ")";
			$result=$db->query($sql);
		}
		print "File has been imported successfully.";
	}
}

function copy_row($table,$rowid){
	global $db;
	print "<p class=\"admin_header\">Copy Table Row $rowid to new record in table: $table</p>";
	$fields_array=list_fields_in_table($table);
	$pk = get_primary_key($table);
	$sql_array=array();
	foreach ($fields_array as $table_field){
		if ($table_field == $pk){
			array_push($sql_array,"NULL");
		} else {
			array_push($sql_array,$table_field);
		}
	}
	$field_list=implode(",",$sql_array);
	$sql = "INSERT INTO $table SELECT $field_list FROM $table WHERE $pk = $rowid";
	$res = $db->query($sql) or die("Error copying row: The following SQL generated an error message:<p>SQL:$sql. The System error message is as follows: " . $db->errmsg()); 
	print "<p>Record Copied Successfully.</p><p>A new record has been created from this record with an id of " . $db->last_insert_id()."</p>";
	print "<p><a href=\"".$_SERVER['PHP_SELF']."?action=list_table&t=$table&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1\">Show table contents</a></p>";
	print "<p><a href=\"".$_SERVER['PHP_SELF']."?action=edit_table&t=$table&rowid=".$db->last_insert_id()."&dbf_edi=1&dbf_ido=1\">Edit new record</a></p>";
}

/* FUNCTION HAS ALREADY MOVED - UPDATE LINKS AND DELETE */
function get_primary_key($table){
	global $db;
	if (!$table){ format_error("No table has been sent to gpk function. Program terminating"); exit;}
	$pk_sql="DESC " . $table;
	$result=$db->query($pk_sql);
	if ($db->num_rows($result)==0){die (format_error("The following table does not exist: $table")); }
	while ($desc_rows=$db->fetch_array($result)){
		if ($desc_rows['Key']=="PRI"){
			$pk=$desc_rows['Field'];
		}
	}
	if (!$pk){die(format_error("No primary key on table $table. DBForms requires all tables to have a primary key as a unique identifier."));}
	return $pk;
}

function export_table_to_file($table){
	global $db;
	print "<div class=\"table_title\">Export Table: $table to csv file</div><br clear=\"all\">";
	print "<p style=\"font-weight:bold\">Please note: This function exports raw data only and will not apply filters.</p>";
	if (!$_POST['filename'] && !$_POST['delimiter'] && !$_POST['table']){
		print "The table $table will be exported to a csv format file.";
		print "<p>Please select a delimiter. This should be a character that is not present in the table data itself: ";
		print "<form action=\"".$_SERVER['PHP_SELF']."?action=export_table_to_file\" method=\"post\">";
		print "<input type=\"hidden\" name=\"table\" value=\"$table\">";
		print "<select name=\"delimiter\"<option value=\"tab\">tab</option><option value=\",\">,</option><option value=\";\">;</option><option value=\"|\">|</option><option value=\"/\">/</option><option value=\"\\\">\</option></select>";
		print "<p>The file will be dumped into the io/ directory. Please specify a filename: <input type=\"text\" name=\"filename\">";
		print "<p><input type=\"submit\" value=\"Export $table to csv\">";
		print "</form>";
	} else {
		if ($_POST['filename']){ $_POST['filename']=$db->db_escape($_POST['filename']);} else {
			print "<p>No filename given.</p><p><a href=\"Javascript:history.go(-1)\">&lt; back to previous page</a></p>"; exit;
		}
		print "<p>Exporting table to " . $_POST['filename'] . "</p>";
		$sql = "select * from $table";
		$res = $db->query($sql) or format_error($db->db_error(),1);
		$filedata=array();
		while ($h=$db->fetch_array($res)){
			$linedata=array();
			foreach ($h as $key=>$value){
				$val_with_quotes=addslashes($value); 
				$val_with_qoutes = preg_replace("/\n/", " ", $val_with_quotes); 
				$val_with_quotes="\"" . $val_with_quotes . "\"";
				array_push($linedata,$val_with_quotes);
			}
			$full_line=join($_POST['delimiter'],$linedata);
			array_push($filedata,$full_line);
		}
		$full_file_data=join("\n",$filedata);
		$filename= IOPATH . "/" . $_POST['filename'];
		file_put_contents($filename,$full_file_data) or format_error("Unable to export table to io directory. Perhaps you do not have permissions to write to this directory?",1);
		print "<p class=\"dbf_para_success\">Table exported successfully.</p>";
	}
}

function tables_as_select_options(){
	require_once(LIBPATH . "/classes/core/tables.php");
	$tables = new tables;
	$all_tables=$tables->list_tables();
	$table_as_options = "<option value=\"Please Select..\">Please Select..</option>";
	foreach ($all_tables as $each_table){
			$table_as_options .= "<option value=\"" . $each_table['real_name']. "\">" . $each_table['name']. "</option>";
	}
	return $table_as_options;
}

function tablefields_as_select_options($table,$selected_field){
	$all_fields=list_fields_in_table($table);
	
	foreach ($all_fields as $each_field){
		$fields_as_options .= "<option value=\"" . $each_field . "\"";
		if (strtolower(str_replace(" ","_",$each_field)) == strtolower(str_replace(" ","_",$selected_field))){ $fields_as_options .= " selected";}
		$each_field_text=ucfirst($each_field);
		$each_field_text=preg_replace("/_/"," ",$each_field_text);
		$fields_as_options .= ">" . $each_field_text . "</option>";
	}
	return $fields_as_options;
}

function duplicate_table($table){
	print "<p class=\"admin_header\">Duplicate Table: $table</p>";
	if (!$_POST['new_table_name']){
		print "<form name=\"duplicate_table_front\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=table_manager_option\">";
		print "Enter name of new table: <input type=\"text\" name=\"new_table_name\" value=\"\" /><br />";
		print "Include current data? <input type=\"checkbox\" name=\"include_current\" value=\"include_current\" />";
		print "<input type=\"hidden\" name=\"selected_table\" value=\"$table\" />";
		print "<input type=\"hidden\" name=\"table_action\" value=\"".$_POST['table_action']."\" />";
		print "<input type=\"submit\" value=\"Duplicate Table\">";

	} else {
		global $db;
		$selected_table=$_POST['selected_table'];
		$new_table_name=$_POST['new_table_name'];
		$sql="CREATE TABLE $new_table_name LIKE $selected_table";
		$res=$db->query($sql) or format_error("Unable to run SQL",1,0,$db->db_error());
		print "New table created successfully from $selected_table.";
		if ($_POST['include_current']){
			$sql="INSERT INTO $new_table_name SELECT * from $selected_table";
			$res=$db->query($sql) or format_error("Unable to load data into new table",1,0,$db->db_error());
			print "<br />Data has been loaded into $new_table_name";
		
		} else {
			print "<br />No data hase been loaded in.";
		}
	}
	print "<p>Go To: <a href=\"".$_SERVER['PHP_SELF']."?action=sysListTables\">Table Manager</a></p>";
}

function check_create_table_permission($usertype){
	$sql="SELECT * from permissions WHERE tablename=\"action:create_table\" AND setting=\"sysUserType\" AND value=\"$usertype\"";
	$res=$db->query($sql);
	if ($db->num_rows($res)>=1){ return 1;} else {return 0;}
}

function optimise_table($table){
	global $db;
	print "<p class=\"admin_header\">Optimise Table</p>";
	$sql="OPTIMIZE TABLE $table";
	$res=$db->query($sql) or format_error("Could not optimise table",1,0,$db->db_error());
	print "<p class=\"dbf_para_success\" style=\"width:500px\">Table $table has been successfully optimised.</p>";
	print table_action_footer($table);
}

function show_relationships_on($table){
	$options['dbf_search_for']=$table;
	$options['filter']['include_edit_link']=1;
	$options['filter']['include_delete_option']=1;
	$options['filter']['dbf_search_fields']="table_1,table_2";
	$options['filter']['sql_filter']="(table_1 = \"$table\" OR table_2 = \"$table\")";
	$options['filter']['title_text']="Relationships on table: $table";
	$results=database_functions::list_table("table_relations",$options);
	print table_action_footer($table);
}

function show_queries_on($table){
	global $libpath;
	require_once("$libpath/classes/core/filters.php");
	$query_filter=new filter();
	$options=$query_filter->load_options("","",33);	
	$options['dbf_search_for']=$table;
	$options['filter']['include_edit_link']=1;
	$options['filter']['include_delete_option']=1;
	$options['filter']['dbf_search_fields']="query";
	$options['filter']['title_text']="Queries on table: $table";
	$results=database_functions::list_table("queries",$options);
	print table_action_footer($table);
}

function show_permissions_on($table){
	$options['filter']['include_edit_link']=1;
	$options['filter']['include_delete_option']=1;
	$options['filter']['sql_filter']="tablename = '$table'";
	$options['filter']['title_text']="Permissions on table: $table";
	$results=database_functions::list_table("permissions",$options);
	print table_action_footer($table);
}
	
function repair_table($table){
	global $db;
	print "<p class=\"admin_header\">Repair Table: $table</p>";
	$sql="REPAIR TABLE $table";
	$res=$db->query($sql) or format_error("Repair operation Failed.",1,0,$db->db_error());
	print "Table $table has been repaired successfully.";
	print table_action_footer($table);
}

function show_metadata_for($table){
	global $db;
	$ret=array();
	$ret['header'] = admin_header("Meta data for table: $table");
	$ret['body'] = "<table class=\"bordered_table\"><tbody>";
	$sql="show table status where name like \"$table\"";
	$res=$db->query($sql) or format_error("Can't display meta data on table $table",1,0,$db->db_error());
	while ($h=$db->fetch_array($res)){;
		foreach ($h as $hkey=>$hval){
			$ret['body'] .= "<tr><td style=\"text-align:right; font-weight:bold;\"><b>$hkey:</b></td><td>" . $h[$hkey] . "</td></tr>";
		}
	}
	$ret['body'] .= "</tbody></table>";
	$ret['footer'] .= table_action_footer($table);
	return $ret;
}

function match_field_names ($list_text,$field){
	return $list_text;
}

function import_data_first_page($table){
	global $db;
	print "<form action=\"".$_SERVER['PHP_SELF']."?action=load_table_data_from_file\" method=\"post\" enctype=\"multipart/form-data\">";
	print "<input type=\"hidden\" name=\"table\" value=\"$table\">";
	print "<fieldset style=\"background-color:#f9f9f9\"><legend style=\"font-weight:bold\">Data Source</legend>";
	print "<p><b>Load data from local file: </b>(data file should be placed in the <b>io</b> directory. Please select the file below:)<br />";
	print "<select name=\"loadfile\"><option value=\"\" style=\"background-color:#f1f1f1\">Select file:</option>";
	$io_dir=BASEPATH . "/" . IOPATH;
	$files = get_directory_list($io_dir);	
	foreach ($files as $file){
	print "<option value=\"$file\">$file</option>";
	}
	print "</select>";
	print "<p><b>Or upload a csv file:</b></p>";
	print "<input type=\"file\" style=\"background-color:#fff\"name=\"upload_add_data_file\">";
	print "</fieldset><fieldset style=\"background-color:#f9f9f9\"><legend style=\"font-weight:bold\">Delimiters</legend>";
	print "<p><b>Field Delimiter: (Split data into separate fields at this character):</b><br /> ";
	print "<select name=\"delimiter\"><option value=\",\">,</option><option value=\"|\">|</option><option value=\";\">;</option><option value=\"space\">space</option><option value=\"tab\">tab</option></select>";
	print "<p><b>Text Delimiter: (used to denote the text in the field)</b><br />";
	print "<select name=\"delimiter_for_text\"><option value=\"Double Quotes\">Double Quotes</option><option value=\"Single Quotes\">Single Quotes</option><option value=\"none\">None</option></select>";
	print "</fieldset>";
	print "<fieldset style=\"background-color:#f9f9f9\"><legend style=\"font-weight:bold\">Convert flat file CSV to relational database format</legend>";
	$filterSQL="SELECT * from filters ORDER BY filter_name";
	$filterRes=$db->query($filterSQL);
	while ($f=$db->fetch_array($filterRes)){
		$optionsText .= "<option value=\"".$f['id']."\">".$f['filter_name']."</option>";
	}
	print "Apply the following filter to the import: <select name=\"apply_filter\"><option value=\"\">--- No Filter ---</option>" . $optionsText . "</select>";
	print "<p>The only filter keys which have any effect on the import are select value list keys where the value is looked up from another table. In this case the value present in the spreadsheet is looked up and the key included rather than the actual data. In the event that the key is not present in the lookup table, this value will be added automatically. This provides a way of converting a flat file csv into a relational database format.</p>";
	print "</fieldset><fieldset><legend style=\"font-weight:bold\">Options</legend>";
	print "<p><b><input type=\"checkbox\" name=\"contains_fieldnames\"> - Csv contains field names</b> (Checking this box will treat the first line as field names, leave unchecked if csv contains just data)</p>"; 
	print "<p><b><input type=\"checkbox\" name=\"assign_names_manually\"> - Manually assign columns to fields in the next step</b> (Leaving blank will automatically expect the same number of fields in the csv as in the table and for them to be in the same order - NB if a primary key is not present in the data it will be added)</p>"; 
	print "<p><b><input type=\"checkbox\" name=\"existing\"> - Overwrite existing data</b> (leaving unchecked will append data to existing data. For updating on key fields please see next page.)</p>";
	print "</fieldset>";
	print "<p><input type=\"submit\" value=\"Continue\"></p></form>";
}

function import_data_second_page($table){
	$loadfile= IOPATH . "/" . $_POST['loadfile'];
	print "<h4>Loading $table from $loadfile</h4>";
	$fieldlist=list_fields_in_table($table);
	$no_of_fields=count($fieldlist);
	$pk=get_primary_key($table);
	print "<ul>";
	$filecontents=file_get_contents($loadfile);
	$filelines=explode("\n",$filecontents);
	$filefields=explode($_POST['delimiter'],$filelines[0]);
	$no_of_fields2=count($filefields);
	print "<li>Text file contains <b>$no_of_fields2</b> fields</li>";
	print "<li>Table '$table' contains <b>$no_of_fields</b> fields. Primary key is '$pk'.</li>";
	print "<li>CSV field delimiter is '" . $_POST['delimiter'] . "'</li>";
	print "<li>CSV text delimiter is '" . $_POST['delimiter_for_text'] . "'</li>";
	print "<li>Assign Columns to Fields: '";
	if ($_POST['assign_names_manually']){ print "Manual"; } else { print "Automatic"; }	
	print "</li></ul>";
	if (($no_of_fields2 < $no_of_fields-1 || $no_of_fields2 > $no_of_fields) && !$_POST['assign_names_manually']){
		print format_error("Error: there must be the same number of fields in the text file as the table to import data.",1,1); 
		return;
	}
	if ($_POST['assign_names_manually'] && !$_POST['load_named_cols']){
		import_data_second_page_assign_names($table,$filecontents);
		return;
	} else if ($_POST['load_named_cols']){
		import_data_load_from_named_cols($table,$filecontents);
		return;
	}
	if ($no_of_fields2 == $no_of_fields-1 && $pk){
		print "<p><span style=\"color:orange; margin:10px; padding:15px;\">Found one field less in the text file than the table. Primary key will be automaticaly generated and data will be loaded in starting at the next available field.</span>";
		$file_contains_pk=0;
	} else {$file_contains_pk=1;}

	print "<p><form action=\"".$_SERVER['PHP_SELF']."?action=load_table_data_from_file\" method=\"post\">";
	print "<input type=\"hidden\" name=\"table\" value=\"$table\">";
	print "<input type=\"hidden\" name=\"loadfile\" value=\"$loadfile\">";
	print "<input type=\"hidden\" name=\"delimiter\" value=\"".$_POST['delimiter']."\">";
	print "<input type=\"hidden\" name=\"load_now\" value=\"1\">";
	print "<input type=\"hidden\" name=\"file_contains_pk\" value=\"$file_contains_pk\">";
	print "<input type=\"submit\" value=\"Load Data From File\"></form>";	
}

function import_data_second_page_assign_names($table,$filecontents){
	$text_delimiter=$_POST['delimiter_for_text'];
	if ($text_delimiter=="Double Quotes"){$text_delimiter="\"";}
	if ($text_delimiter=="Single Quotes"){$text_delimiter="'";}
	print "<form name=\"columns_to_fields\" target=\"_blank\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=load_table_data_from_file\"><table><thead style=\"font-weight:bold; background-color:#f8f8f8; text-align:center;\"><tr><td>CSV Column</td><td>Database Field</td></tr></thead>";
	// include all current POST vars
	print "<input type=\"hidden\" name=\"load_named_cols\" value=\"load_named_cols\">\n";
	foreach ($_POST as $postvar=>$postval){
		print "<input type=\"hidden\" name=\"$postvar\" value=\"$postval\">\n";
	}
	$csv_names_array=array();
	
	$filelines=split("\n",$filecontents);
	foreach ($filelines as $line){
		$filefields=explode($_POST['delimiter'],$line);
		foreach ($filefields as $field){
			if ($_POST['delimiter_for_text']){
				$field=str_replace($text_delimiter,"",$field);
			}
		if ($field){ array_push($csv_names_array,$field); 
			print "<tr><td style=\"text-align:right\">" . ucfirst($field) . ": </td><td><select name=\"$field" . "-tablefield\"><option value=\"\">-- Do Not Use Column --</option>";
				print tablefields_as_select_options($table,$field) . "</select></td></tr>";
			}
			}
		
		break;
		}
	print "</table>\n";
	
	print "<h4>Adding and updating data options:</h4>";
	print "<input type=\"radio\" name=\"import_action\" value=\"append_data_to_existing\" checked> Append to existing data<br />";
	print "<input type=\"radio\" name=\"import_action\" value=\"overwrite_existing\"> Overwrite existing data<br />";
	print "<input type=\"radio\" name=\"import_action\" value=\"match_on_key\"> Update data where the following fields match: CSV Column: <select name=\"data_match_csv_col\"><option value=\"\"></option>";
	foreach ($csv_names_array as $csv_name){
		print "<option value=\"$csv_name\">$csv_name</option>";
	}
	print "</select>";
	print "Database table column: <select name=\"data_match_table_col\"><option value=\"\"></option>";
	print tablefields_as_select_options($table,$field);
	print "</select>";

	print "<dd><p>If you are updating on a field match, please specify one of the following actions:</p>";
	print "<input type=\"radio\" name=\"match_action\" value=\"update_only\" checked> Update data only where a field matches (ignore any extra rows in spreadsheet)<br />";
	print "<input type=\"radio\" name=\"match_action\" value=\"update_and_add\"> Update data where a field matches and add new data from the spreadsheet (If you have specified a filter for linked fields, this will automatically update the linked tables accordingly).<br />";
	print "<p></p></dd>";

	print "<input type=\"submit\" value=\"Load Data\"></form>";
	return;
}

function compare_config_section($a, $b) {
	$ret_val= strnatcmp($a['config_section'], $b['config_section']);
	if (!$ret_val){ $ret_val = strnatcmp($a['real_name'], $b['real_name']); }
	return $ret_val;
}

function import_data_load_from_named_cols($table,$filecontents){
	global $db;

	// Import action says whether to overwrite, append or match
	// match_action says whether or not to only update or to add new rows if they are present in the spreadsheet.
	if ($_POST['import_action']=="match_on_key"){
		if ($_POST['data_match_table_col']){
			$match_table_col=$_POST['data_match_table_col'];
		}
		if ($_POST['data_match_csv_col']){
			$match_csv_col=$_POST['data_match_csv_col'];
		}
		if (!$match_table_col || !$match_csv_col){
			format_error("You must specify a table column and a csv column in order to perform a Match On Key action",1);
		}
		$do_update_query=1;
	}

	print "<ul><li>The following columns will be loaded into the following fields:<br /><br />";
	print "<table><thead style=\"font-weight:bold; background-color:#f8f8f8;\"><tr><td>CSV Column</td><td>Database Field</td></tr></thead>";
	$use_csv_fields=array();
	$use_table_fields=array();
	foreach ($_POST as $postvar=>$postval){
		if (stristr($postvar,"-tablefield") && $postval){
			$actual_csv_field=str_replace("-tablefield","",$postvar);
			array_push($use_table_fields,$postval);
			array_push($use_csv_fields,trim($actual_csv_field));
			print "<tr><td><b>$actual_csv_field:</b> </td><td style=\"color:blue\">".ucfirst($postval)."\n</td></tr>";
		}
	}
	print "</table></li></ul>";
	$filter_to_apply=$_POST['apply_filter'];
	if ($filter_to_apply){
		global $libpath;
		require_once("$libpath/classes/core/filters.php");
		$apply_filter=new filter();
		//$filter_options=load dbforms filter($filter_to_apply); - untested!
		$filter_options=$apply_filter->load_filter($filter_to_apply);
		foreach ($filter_options as $filter_key => $filter_val){
			foreach ($use_table_fields as $table_field){
				if ($table_field==$filter_key){
					if ($debug){ print "<p><b>found key for $table_field</b><br />";}
					foreach ($filter_val as $subkey=>$subval){
						if ($subval="select_list"){
							$sourcedata=$filter_options[$table_field]['select_value_list'];
							if ($debug){ print "source is $sourcedata";}
						}
					}
				}
			}
		}
		if ($debug){ print "<p>Dumping filter options: ";
		var_dump($filter_options);
		print "</p>";
		}
	}
	// do the do...
	$debug=0;
	$filelines=split("\n",$filecontents);
	$linecounter=0;
	$text_delimiter=$_POST['delimiter_for_text'];
	if ($text_delimiter=="Double Quotes"){$text_delimiter="\"";}
	if ($text_delimiter=="Single Quotes"){$text_delimiter="'";}

	$total_lines_added=0;
	$total_lines_updated=0;

	foreach ($filelines as $line){
		if (!$line){ continue; }
		//if (!preg_match("/\r/",$line)){ continue; }// there must be a carriage return 
		if ($linecounter==0){
			// get field names
			$linearray=array();
			$csv_fields_as_list=",".join(",",$use_csv_fields).",";
			$csv_fields_as_list.=","; // this is so the search matches on the last field
			$sql = "INSERT INTO $table (".join(",",$use_table_fields). ") values (";
			$filefields=explode($_POST['delimiter'],$line);
			$filefields=str_replace($text_delimiter,"",$filefields);
			$fieldcount=0;
			foreach ($filefields as $filefield){
				if (!$filefield){ continue; }
				if (preg_match("/^\s+$/",$filefield)){ continue; }
				if ($debug){print "<p>Is $filefield in the field list: $csv_fields_as_list<br />";}
				$filefield=str_replace(" ","_",trim($filefield));
				if (stristr($csv_fields_as_list,",".$filefield.",")){
					if ($debug){ print "yes it is -"; }
					$linearray[$fieldcount]="use";	
					if ($debug){print "got a 'use' for $filefield";}
					$fvar=$filefield."-tablefield";
					$tfvar=$_POST[$fvar];
					if ($debug){print "<br />tablefield for this csv field is is $tfvar"; if (!$tfvar){ print "NO TABLEFIELD FOUND!!!"; exit; }}
					if ($filter_options[$tfvar]['select_value_list']){
						if ($debug){print "<br /><b>SVL FOUND on $tfvar</b>!";}
						$linearray[$fieldcount]="-uselinked:$tfvar";
					}
				} else {
					$linearray[$fieldcount]="";
					if ($debug){
					print "no its not - no instance of $filefield in $csv_fields_as_list";
					}
				}
			$fieldcount++;
			}
		$linecounter++;
		//var_dump($linearray);
		continue;
		}
		$sql = "INSERT INTO $table (".join(",",$use_table_fields). ") values(";
		$update_sql = "UPDATE $table SET ";
		if ($debug){ print "<br />Done first bit of sql - update sql is now $update_sql<br />";}
		$filefields=explode($_POST['delimiter'],$line);
		$filefields=str_replace($text_delimiter,"",$filefields);
		$fieldcounter=0;
		foreach ($filefields as $field){
			if ($debug){ print "<span style=\"color:blue\">On field $field - counter $fieldcounter;</span> - ";}
			if ($linearray[$fieldcounter]=="use"){
				if ($debug){ print " we are using this field - \n"; }
				$field=str_replace($text_delimiter,"",$field);
				if (preg_match("/^\d\d\/\d\d\/\d\d\d\d$/",$field)){
					$split_date=explode("/",$field);
					$field=$split_date[2].$split_date[1].$split_date[0];
				}
				$sql .= "\"$field\",";
				if ($debug){ print "<span style=\"color:orange\">Just added $field to $sql</span>"; }
				if ($use_table_fields[$fieldcounter]==$match_table_col){
					$update_on_match_value=$field;
					if ($debug) { print "NOT Adding field " . $use_table_fields[$fieldcounter] . "' to update as it is $match_table_col - the key field<br />";}
				} else {
					if ($debug){ print "Adding field '" . $use_table_fields[$fieldcounter] . "' to update as it is not $match_table_col<br />";}
					$update_sql .= $use_table_fields[$fieldcounter] . " = \"" . trim($field) . "\"," ;
				}
			} elseif (stristr($linearray[$fieldcounter],"uselinked")){
				if ($debug){ print " - we are using a LINK on this field - \n"; }
				$field=str_replace($text_delimiter,"",$field);
				$fieldname=str_replace("-uselinked:","",$linearray[$fieldcounter]);
				$sql_lookup=str_replace("SQL:","",$filter_options[$fieldname]['select_value_list']);
				$sql_lookup_parts=preg_split("/ WHERE /i",$sql_lookup);
				$sql_lookup=$sql_lookup_parts[0];
				if ($debug){print "lookup is " . $sql_lookup; }
				$sql_lookup=preg_replace("/select /i","",$sql_lookup);
				$sql_lookup=trim($sql_lookup);
				$fields_and_values=preg_split("/ from /i",$sql_lookup);
				$linked_table=trim($fields_and_values[1]);
				$linked_table=preg_replace("/ .*/","",$linked_table); // remove a space and anything after to just get the table name
				list($keyfield,$sourcefield)=explode(",",$fields_and_values[0]);
				// get key value of linked field
				
				$subSQL="SELECT $keyfield from $linked_table WHERE $sourcefield=\"$field\"";
				if ($debug){print "<br /> - GOT subSQL: $subSQL !";}
				$subRES=$db->query($subSQL) or die("Error with subsql of $subSQL: " . $db->db_error());
				$subSqlCount=$db->num_rows($subRES);
				if ($subSqlCount>0){
				$h=$db->fetch_array($subRES);
					if ($debug){print "Value of subSQL is " . $h[$keyfield];}
					$update_value=$h[$keyfield];
				} else {
					$field=trim($field); // last added bit
					$insert_new_value="INSERT INTO $linked_table ($sourcefield) VALUES (\"$field\")";
					$update_row=$db->query($insert_new_value) or format_error("Error in Insert New Value: $insert_new_value: " . $db->db_error(),1);
					$update_value=$db->last_insert_id();
					if ($debug){print "new value has been added of $update_value";}
				}
				$sql .= "\"$update_value\",";
				$update_sql .= $use_table_fields[$fieldcounter] . " = " . $update_value . ", ";
				if ($debug){print "<p>UPDATE VALUE IS $update_value!<br />";}
			}
		$fieldcounter++;
		}
		$sql = preg_replace("/\\r/","",$sql);
		$sql = preg_replace("/,\s?$/","",$sql);
		$sql .= ")";
		$update_sql=preg_replace("/\n/","",$update_sql);
		$update_sql .= " WHERE $match_table_col = \"" . $update_on_match_value . "\"";
		$update_sql=str_replace(", WHERE"," WHERE",$update_sql); // CAREFUL! This may alter text! Why is the above one one up 2 lines working?!
		$update_sql=preg_replace("/,\s+WHERE/"," WHERE",$update_sql); // CAREFUL! This may alter text! Why is the above one one up 2 lines working?!
		// if we are doing an update + add if not present, need to check that the value is there first..
		$update_this_time=1;
		if ($do_update_query && $_POST['match_action']=="update_and_add"){
			$check_sql="SELECT $match_table_col FROM $table WHERE $match_table_col = \"$update_on_match_value\"";
			$run_check=$db->query($check_sql);
			if ($db->num_rows($run_check)==0){ $update_this_time=0; }// causes the add action to happen instead of update
		}

		print "update is $update_sql<br />";
		print "inset is $sql<br /><br />";
		//print "update sql is " . $update_sql;
		if ($do_update_query && $update_this_time){
			$result=$db->query($update_sql) or print format_error("Cannot run query of: " . $sql . ".<br>Error message: " . $db->db_error(),0);
			$total_lines_updated++;
		} else {
			$result=$db->query($sql) or print format_error("Cannot run query of: " . $sql . ".<br>Error message: " . $db->db_error(),0);
			$total_lines_added++;
		}
		$linecounter++;
	}
	$return_hash['added']=$total_lines_added;
	$return_hash['updated']=$total_lines_updated;
	return $return_hash;
}

function table_action_footer($table){

	if (!stristr($_SERVER['SCRIPT_FILENAME'],"mui-administrator")){
	$ret = "<p style=\"text-align:left\">Go To: <ul style=\"text-align:left; display:block; list-style-type:square\">\n";
	$ret .= "<li><a href=\"".$_SERVER['PHP_SELF']."?action=sysListTables\">Table Manager</a></li>";
	$ret .= "<li><a href=\"".$_SERVER['PHP_SELF']."?action=list_table&t=$table\">View Table Data</a></li>";
	$ret .= "</ul></p>";
	} else {
		$ret="";
	}
	return $ret;
}

function list_table_options_mui($table){
?>
<ul style="list-style-type: square;">
<li><a href="mui-administrator.php?action=table_manager_option&selected_table=<?=$table?>&table_action=optimise">Optimise Table</a></li>
<li><a href="mui-administrator.php?action=table_manager_option&selected_table=<?=$table?>&table_action=repair">Repair Table</li>
<li><a href="mui-administrator.php?action=table_manager_option&selected_table=<?=$table?>&table_action=duplicate">Duplicate Table</li>
<li><a href="mui-administrator.php?action=table_manager_option&selected_table=<?=$table?>&table_action=drop">Drop Table</a></li>
<br /><b>Generate Templates</b>
<li><a href="mui-administrator.php?action=generate_form_template&table=<?=$table?>">Generate Form Template</a></li>
<li><a href="mui-administrator.php?action=generate_recordset_template&table=<?=$table?>">Generate Recordset Template</a></li>
</ul>
<?php
}

function import_wizard($table){
	open_col2();
	print "<p class=\"admin_header\">Import Data Wizard"; if ($table){ print ": $table"; } print  "</p>";
	print "<p>This wizard will allow you to import data from a csv file, and can be used to either append, replace or update data.</p>";
	if ($_FILES['csv_file'] || $_REQUEST['csv_file_uploaded']){ 
		import_wizard_2($table);
	} else {
		print "<script language=\"Javascript\">
		window.addEvent('domready', function() {

		    // apply cursor
		    var myVerticalSlide = new Fx.Slide('csv_info').hide('vertical');
		    var myVerticalSlide2 = new Fx.Slide('toggle_csv_info');

			$('toggle_csv_info').addEvent('mouseover', function(event){ $('toggle_csv_info').setStyle('cursor','pointer'); });
			$('close_csv_info').addEvent('mouseover', function(event){ $('close_csv_info').setStyle('cursor','pointer'); });

		    // click event
		     $('toggle_csv_info').addEvent('click', function(event){
			event.stop();
			myVerticalSlide.toggle();
			myVerticalSlide2.toggle();
		    });
		     $('close_csv_info').addEvent('click', function(event){
			event.stop();
			myVerticalSlide.toggle();
			myVerticalSlide2.toggle();
		    });
			// these 2 lines show it at start as we have hidden it
			document.getElementById('csv_info').style.visibility='visible';
			document.getElementById('csv_info').style.display='block';
		});
		</script><a id=\"toggle_csv_info\">Click here for instructions on creating a CSV file in the correct format.</a>";
		print "<div class=\"dbf_para_info\" id=\"csv_info\" style=\"visibility:hidden; display:none;\">";
		print "<p><b>Creating a CSV file</b></p>";
		print "<p>A csv file is essentially a spreadsheet in plain text format, where columns are separated by a character such as a comma or semi-colon. This type of file must be created from your spreadsheet in order to import data. A CSV can only contain one page of data, therefore if your data is on multiple pages in your main spreadsheet you should export each page separately.</p>";
		print "<ol><li>First be sure that your spreadsheet has column headers on the first line. Then, to create a csv file from excel, select 'Save As' from the file menu, and select .csv (this may be listed as Text CSV)</li>";
		print "<li>Give your file a name and you will be prompted to select some options as follows: </li>";
		print "<li>Delimiter / Field Delimiter - this is the character that is used to denote what separates the textfields so that each field can be identified.<br />Recommended option: a comma or semi-colon. If in doubt please use a semi-colon.<br /></li>";
		print "<li>Text Delimiter - This is used at the start and end of each field between the field delimiters, and should always be used if the field delimiter you have chosen may also be used <i>within the data itself</i> - as commas often are.<br />Recommended option: Double Quotes (&quot;) - if in doubt please use this.</li>";
		print "<li style=\"color:#cc0000\">Double check - your csv should have columns headers on the first line so the columns can be easily related to database columns.</li>";
		print "</ol>
		<p style=\"float:right\"><a id=\"close_csv_info\">Close Info Panel</a></p><br clear=\"all\"></div><br clear=\"all\"><br />";
		print "<span style=\"border:1px #000 solid; padding:10px; font-size:16px\">1</span> <b> Select and upload your csv file</b><br clear=\"all\" /><br />";
		print "<form method=\"POST\" enctype=\"multipart/form-data\" action=\"".$_SERVER['PHP_SELF']."?action=import_wizard\">";
		print "<input type=\"file\" name=\"csv_file\">";
		
		print "<input type=\"hidden\" name=\"table\" value=\"$table\" />";
		print "<input type=\"submit\" value=\"Click here to upload your chosen file\" />";
		print "</form>";
	}
}

function import_wizard_2($table){

	if (!$_REQUEST['csv_file_uploaded']){
		$target_path=IOPATH . "/" . basename($_FILES['csv_file']['name']);
		print "<p><b>Uploading file:</b> $target_path </p>";
		if (!move_uploaded_file($_FILES['csv_file']['tmp_name'], $target_path)){
			print "<p class=\"dbf_para_alert\">No uploaded file was found - please try again.</p>";
			return;
		}
	}

	if (!$_REQUEST['delimiter']){
		print "<p class=\"dbf_para_success\">File Uploaded Successfully</p><br />";
		print "<span style=\"border:1px #000 solid; padding:10px; font-size:16px\">2</span> <b>Specify field and text delimiters:</b><br clear=\"all\" /><br />";
		print "<form method=\"POST\" action=\"".$_SERVER['PHP_SELF']."?action=import_wizard\">";
		print "<input type=\"hidden\" name=\"csv_file_uploaded\" value=\"$target_path\">";
		print "<input type=\"hidden\" name=\"table\" value=\"$table\" />";
		print "<p><b>Field Delimiter: (Split data into separate fields at this character):</b><br /> ";
		print "<select name=\"delimiter\"><option value=\",\">, (comma)</option><option value=\"|\">| (pipe)</option><option value=\";\">; (semi colon)</option><option value=\"space\">space</option><option value=\"tab\">tab</option></select>";
		print "<p><b>Text Delimiter: (used to denote the text in the field)</b><br />";
		print "<select name=\"delimiter_for_text\"><option value=\"Double Quotes\">Double Quotes</option><option value=\"Single Quotes\">Single Quotes</option><option value=\"none\">None</option></select>";
		print "<input type=\"submit\" value=\"Specify Delimiters \" /></form>";
		return;
	}


	if ($_POST['load_named_cols']){
		$result=import_wizard_load_table_data_from_file($table);
		return;
	}
	print "<p class=\"dbf_para_success\">Delimiters Set</p><br />";
	// START SPECIFY FIELDS
	print "<span style=\"border:1px #000 solid; padding:10px; font-size:16px\">3</span> <b>Specify fields:</b><br clear=\"all\" /><br />";
	print "<p>The fields in your csv now need to be matched to the fields in your database. <br />If the spreadsheet field has the same name as the database column this has been already selected for you below:</p>";
	
	// get contents of the file
	if ($filecontents=file_get_contents($_REQUEST['csv_file_uploaded'])){

	} else {
		print "File did not import successfully."; exit;
	}
	
	match_csv_columns($table,$filecontents);

}

function match_csv_columns($table,$filecontents){
        $text_delimiter=$_POST['delimiter_for_text'];
        if ($text_delimiter=="Double Quotes"){$text_delimiter="\"";}
        if ($text_delimiter=="Single Quotes"){$text_delimiter="'";}
        print "<form name=\"columns_to_fields\" target=\"_blank\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=import_wizard\"><table style=\"background-color:#dddddd\"><thead style=\"font-weight:bold;\"><tr><td align=\"left\" style=\"background-color:#666; color:#fff\">Column in CSV</td><td align=\"left\" style=\"background-color:#666; color:#fff\">Database field to populate from field in CSV</td></tr></thead>";
        // include all current POST vars
        print "<input type=\"hidden\" name=\"load_named_cols\" value=\"load_named_cols\">\n";
        foreach ($_POST as $postvar=>$postval){
                print "<input type=\"hidden\" name=\"$postvar\" value=\"$postval\">\n";
        }
        $csv_names_array=array();

        $filelines=split("\n",$filecontents);
        foreach ($filelines as $line){
                $filefields=explode($_POST['delimiter'],$line);
                foreach ($filefields as $field){
			$field=trim($field);
                        if ($_POST['delimiter_for_text']){
                                $field=str_replace($text_delimiter,"",$field);
                        }
                if ($field){ array_push($csv_names_array,$field);
                        print "<tr><td style=\"text-align:right\">" . ucfirst($field) . " </td><td><select name=\"$field" . "-tablefield\"><option value=\"\">-- Do Not Import Column --</option>";
				$select_options=tablefields_as_select_options($table,$field);
                                print $select_options . "</select></td></tr>";
                        }
                        }

                break;
                }
        print "</table>\n";
	$reg_filter=filter_registered_on_table($table,"import_data");
	if ($reg_filter){
		print "<input type=\"hidden\" name=\"apply_filter\" value=\"$reg_filter\" />";
		require_once(LIBPATH."/classes/core/filters.php");
		$import_filter=new filter($reg_filter);
		$match_field=$import_filter->filter_options['import_data_field_match'];
	}

        print "<h4>Adding and updating data options:</h4>";
	print "<script type=\"text/javascript\">
	function display_match_data(radiovalue){
		if (radiovalue==\"match_on_key\"){
			document.getElementById(\"update_on_field_match_div\").style.display=\"block\";
		} else {
			document.getElementById(\"update_on_field_match_div\").style.display=\"none\";
		}
	}
	</script>";
        print "<input type=\"radio\" name=\"import_action\" value=\"append_data_to_existing\" onClick=\"display_match_data(name,this.value)\" checked> Append uploaded data to any existing data (this will retain all of the current records in this table)<br />";
        print "<input type=\"radio\" name=\"import_action\" value=\"overwrite_existing\" onClick=\"display_match_data(this.value)\"> Overwrite existing data (this will delete all current records in the table before loading in the data from your spreadsheet)<br />";
        print "<input type=\"radio\" name=\"import_action\" value=\"match_on_key\" onClick=\"display_match_data(this.value)\"> Update the database where the data in this column in your CSV: <select name=\"data_match_csv_col\"><option value=\"\"></option>";
        foreach ($csv_names_array as $csv_name){
                print "<option value=\"$csv_name\"";
		if (strtolower($csv_name)==strtolower($match_field)){
			print " selected";
		}
		print ">$csv_name</option>";
        }
        print "</select> matches the database table column:  <select name=\"data_match_table_col\"><option value=\"\"></option>";
        print tablefields_as_select_options($table,$match_field);
        print "</select>";

        print "<dd><div id=\"update_on_field_match_div\" style=\"display:none\"><p style=\"font-weight:bold\">Please specify one of the following actions:</p>";
        print "<input type=\"radio\" name=\"match_action\" value=\"update_only\" checked> Update data where this field matches only (ignore any extra records in spreadsheet)<br />";
        print "<input type=\"radio\" name=\"match_action\" value=\"update_and_add\"> Update data where this field matches (and if no corresponding entry in the database is is found add the data from the spreadsheet as a new record.)<br />";
        print "<p></p></div></dd>";
	print "<br />";
        print "<input type=\"submit\" value=\"That's it! Click here to import your data\"></form><p></p><br />";
        return;
}

function import_wizard_load_table_data_from_file($table){
	global $db;
	if (!$table){print "No table specified. Function cannot run."; return;}

	if ($_POST['load_named_cols'] && $_POST['table'] && $_POST['delimiter'] && $_POST['csv_file_uploaded'] && $_POST['import_action']){
		print "loading..";
		$loadfile=$_POST['csv_file_uploaded'];
		$filecontents=file_get_contents($loadfile) or print "Cannot get contents of $loadfile";
		$results=import_data_load_from_named_cols($table,$filecontents);
		if ($results){ print "<p class=\"dbf_para_success\">File has been imported successfully.</p>";} else { print "<p class=\"dbf_para_alert\">Error importing file.</p>"; }
		if ($results){
			print "Lines of data added: " . $results['added'] . "<br />";
			print "Lines of data updated: " . $results['updated'] . "<br />";
		}
	} else {
		print "Paramaters are missing: <br />table:$table<br />csv_file_uploaded: " . $_REQUEST['csv_file_uploaded']. "<br />";
		print "Delimiter: " . $_REQUEST['delimiter'] . "<br />";
	}
}

function visualise_relations(){
	require_once(LIBPATH . "/classes/tables.php");
	$tables=new tables;

	// create the xml
	$datatypesfile=BASEPATH."/apps/wwwsqldesigner/wwwsqldesigner-2.5/db/mysql/datatypes.xml";
	@ $datatypes = file($datatypesfile);
	$xml=$datatypes[0];
	$xml .= "<sql>\n";
	for ($i=1;$i<count($datatypes);$i++) {
		$line=$datatypes[$i];
		//$line=htmlspecialchars($line);
		$line=str_replace(" & "," \&amp; ",$line);
		$xml .= $line;
	}

	global $db;
	$sql="SELECT * from table_relations order by id ASC";
	$rv=$db->query($sql);
	$tables_written=array();
	$tablex=0;
	$tabley=0;
	while ($h=$db->fetch_array($rv)){

		// First do table 1
		$t1_relation_only=0;
		$master_table=$h['table_1'];
		$child_table=$h['table_2'];
		if (in_array($master_table,$tables_written)){ 
		//	$xml=write_relation_only($master_table,$child_table,$h['field_in_table_1'],$h['field_in_table_2'],$xml);
			$t1_relation_only=1; 
		}
		if (!$t1_relation_only){
		$descsql="DESC $master_table";
		$descrv=$db->query($descsql);
		$table1_fields=array();
		while ($desc_rows=$db->fetch_array($descrv)){
			$table1_fields[$desc_rows['Field']]=$desc_rows;
			if ($desc_rows['Key']=="PRI"){
				$table1_pk=$desc_rows['Field'];
			}
		}
		//var_dump($table1_fields);
		if (!$table1_fields){ continue; }
		// convert to xml
		$xml .= "<table name=\"$master_table\" x=\"$tablex\" y=\"$tabley\" >\n";
		foreach ($table1_fields as $fieldname=>$fieldvals){
			if (!$fieldvals['Default']){ $fieldvals['Default']="NULL"; }
			$fieldvals['Type']=strtoupper($fieldvals['Type']);
			if (strlen(stristr($fieldvals['Type'],"INT"))){$fieldvals['Type']="INTEGER"; }
			$xml .= "<row name=\"$fieldname\"";
			if ($fieldvals['Null']=="YES"){ $xml .= " null=\"1\""; } else { $xml .= " null=\"0\""; }
			if ($fieldvals['Extra']=="auto_increment"){ $xml .= " autoincrement=\"1\""; } else { $xml .= " autoincrement=\"0\""; }
			$xml .= ">\n";
			$xml .= "	<datatype>" . $fieldvals['Type'];
			$xml .= "</datatype>\n";
			$xml .= "	<default>" . $fieldvals['Default'];
			$xml .= "</default>\n";
			// is there a relation key? 
			$rel_sql="SELECT * FROM table_relations WHERE table_2 = \"$master_table\"  AND field_in_table_2=\"$fieldname\"";
			//print "On field $fieldname, looking for this as field_in_table_2.. ";
			$rel_rv=$db->query($rel_sql);
			while ($rel_h=$db->fetch_array($rel_rv)){
				//print "Building relation for " . $rel_h['table_1'] . " on " . $rel_h['field_in_table_1'] . "!<br />";
				$xml .= "	<relation table=\"".$rel_h['table_1']."\" row=\"".$rel_h['field_in_table_1']."\" />\n";	
			}
			$xml .= "</row>\n";
		}
		if ($table1_pk){
			$xml .= "<key type=\"PRIMARY\" name=\"\">\n	<part>$table1_pk</part>\n</key>\n";
		}
		$xml .= "</table>\n\n";
		array_push($tables_written,$master_table);

		// adjust x and y values
		$tablex=$tablex+220;
		if ($tablex==1100){ $tabley=$tabley+200; $tablex=0;}

		} // end t1 relation only


		// now do table 2
		$t2_relation_only=0;
		if (in_array($child_table,$tables_written)){
			$t2_relation_only=1; 
		// 	are we going to need the line below.. ever?
		//	$xml=write_relation_only($master_table,$child_table,$h['field_in_table_1'],$h['field_in_table_2'],$xml);
			continue; 
		}
                $descsql="DESC $child_table";
                $descrv=$db->query($descsql);
                $table2_fields=array();
                while ($desc_rows=$db->fetch_array($descrv)){
                        $table2_fields[$desc_rows['Field']]=$desc_rows;
                        if ($desc_rows['Key']=="PRI"){
                                $table2_pk=$desc_rows['Field'];
                        }
                }
                //var_dump($table2_fields);
                if (!$table2_fields){ continue; }
                // convert to xml
                $xml .= "<table name=\"$child_table\" x=\"$tablex\" y=\"$tabley\">\n";
                foreach ($table2_fields as $fieldname=>$fieldvals){
                        if (!$fieldvals['Default']){ $fieldvals['Default']="NULL"; }
                        $fieldvals['Type']=strtoupper($fieldvals['Type']);
                        if (strlen(stristr($fieldvals['Type'],"INT"))){$fieldvals['Type']="INTEGER"; }
                        $xml .= "<row name=\"$fieldname\"";
                        if ($fieldvals['Null']=="YES"){ $xml .= " null=\"1\""; } else { $xml .= " null=\"0\""; }
                        if ($fieldvals['Extra']=="auto_increment"){ $xml .= " autoincrement=\"1\""; } else { $xml .= " autoincrement=\"0\""; }
                        $xml .= ">\n";
                        $xml .= "       <datatype>" . $fieldvals['Type'];
                        $xml .= "</datatype>\n";
                        $xml .= "       <default>" . $fieldvals['Default'];
                        $xml .= "</default>\n";
                        // is there a relation key?
                        $rel_sql="SELECT * FROM table_relations WHERE table_2 = \"$child_table\"  AND field_in_table_2=\"$fieldname\"";
                        //print "On field $fieldname, looking for this as field_in_table_2.. ";
                        $rel_rv=$db->query($rel_sql);
                        while ($rel_h=$db->fetch_array($rel_rv)){
				//print "Building relation for " . $rel_h['table_1'] . " on " . $rel_h['field_in_table_1'] . "<br />";
                                $xml .= "       <relation table=\"".$rel_h['table_1']."\" row=\"".$rel_h['field_in_table_1']."\" />\n";
                        }
                        $xml .= "</row>\n";
                }
                if ($table2_pk){
                        $xml .= "<key type=\"PRIMARY\" name=\"\">\n     <part>$table2_pk</part>\n</key>\n";
                }
                $xml .= "</table>\n\n";
                array_push($tables_written,$child_table);

		// adjust x and y values
		$tablex=$tablex+220;
		if ($tablex==1100){ $tabley=$tabley+200; $tablex=0; }

	}

	// now get ALL other tables in the system and add them to the end
	$all_tables=$tables->list_tables_basic();
	foreach ($all_tables as $table){
		list ($actual_name,$readable) = explode(";;",$table);
		$table=$actual_name;
		if (in_array($table,$tables_written)){ continue; }

		$descsql="DESC $table";
		$descrv=$db->query($descsql);
		$table_fields=array();
		while ($desc_rows=$db->fetch_array($descrv)){
			$table_fields[$desc_rows['Field']]=$desc_rows;
			if ($desc_rows['Key']=="PRI"){
				$table_pk=$desc_rows['Field'];
			}
		}
		$extra_xml .= "<table name=\"$table\" x=\"$tablex\" y=\"$tabley\" >\n";
		foreach ($table_fields as $fieldname=>$fieldvals){
			if (!$fieldvals['Default']){ $fieldvals['Default']="NULL"; }
			$fieldvals['Type']=strtoupper($fieldvals['Type']);
			if (strlen(stristr($fieldvals['Type'],"INT"))){$fieldvals['Type']="INTEGER"; }
			$extra_xml .= "<row name=\"$fieldname\"";
			if ($fieldvals['Null']=="YES"){ $extra_xml .= " null=\"1\""; } else { $extra_xml .= " null=\"0\""; }
			if ($fieldvals['Extra']=="auto_increment"){ $extra_xml .= " autoincrement=\"1\""; } else { $extra_xml .= " autoincrement=\"0\""; }
			$extra_xml .= ">\n";
			$extra_xml .= "	<datatype>" . $fieldvals['Type'];
			$extra_xml .= "</datatype>\n";
			$extra_xml .= "	<default>" . $fieldvals['Default'];
			$extra_xml .= "</default>\n";
			// is there a relation key? 
			$rel_sql="SELECT * FROM table_relations WHERE table_2 = \"$table\"  AND field_in_table_2=\"$fieldname\"";
			//print "On field $fieldname, looking for this as field_in_table_2.. ";
			$rel_rv=$db->query($rel_sql);
			while ($rel_h=$db->fetch_array($rel_rv)){
				//print "Building relation for " . $rel_h['table_1'] . " on " . $rel_h['field_in_table_1'] . "!<br />";
				$extra_xml .= "	<relation table=\"".$rel_h['table_1']."\" row=\"".$rel_h['field_in_table_1']."\" />\n";	
			}
			$extra_xml .= "</row>\n";
		}

		if ($table_pk){
			$extra_xml .= "<key type=\"PRIMARY\" name=\"\">\n	<part>$table_pk</part>\n</key>\n";
		}
		$extra_xml .= "</table>\n\n";

		// adjust x and y values
		$tablex=$tablex+220;
		if ($tablex==1100){ $tabley=$tabley+200; $tablex=0;}
	}
	$xml .= $extra_xml;
	$xml .= "</sql>";
	header("Content-type: text/xml");
	print $xml;
	exit;
}

function visualise_application_relations(){

	require_once(LIBPATH . "/classes/tables.php");
	$tables=new tables;

	// create the xml
	$datatypesfile=BASEPATH."/apps/wwwsqldesigner/wwwsqldesigner-2.5/db/mysql/datatypes.xml";
	@ $datatypes = file($datatypesfile);
	$xml=$datatypes[0];
	$xml .= "<sql>\n";
	for ($i=1;$i<count($datatypes);$i++) {
		$line=$datatypes[$i];
		//$line=htmlspecialchars($line);
		$line=str_replace(" & "," \&amp; ",$line);
		$xml .= $line;
	}

	global $db;
	$sql="SELECT * from table_relations order by id ASC";
	$rv=$db->query($sql);
	$tables_written=array();
	$tablex=0;
	$tabley=0;
	while ($h=$db->fetch_array($rv)){

		// First do table 1
		$t1_relation_only=0;
		$master_table=$h['table_1'];
		$child_table=$h['table_2'];
		if (get_table_type($master_table!="application" || get_table_type($child_table)!="application")){
			continue;
		}
		if (in_array($master_table,$tables_written)){ 
		//	$xml=write_relation_only($master_table,$child_table,$h['field_in_table_1'],$h['field_in_table_2'],$xml);
			$t1_relation_only=1; 
		}
		if (!$t1_relation_only){
		$descsql="DESC $master_table";
		$descrv=$db->query($descsql);
		$table1_fields=array();
		while ($desc_rows=$db->fetch_array($descrv)){
			$table1_fields[$desc_rows['Field']]=$desc_rows;
			if ($desc_rows['Key']=="PRI"){
				$table1_pk=$desc_rows['Field'];
			}
		}
		//var_dump($table1_fields);
		if (!$table1_fields){ continue; }
		// convert to xml
		$xml .= "<table name=\"$master_table\" x=\"$tablex\" y=\"$tabley\" >\n";
		foreach ($table1_fields as $fieldname=>$fieldvals){
			if (!$fieldvals['Default']){ $fieldvals['Default']="NULL"; }
			$fieldvals['Type']=strtoupper($fieldvals['Type']);
			if (strlen(stristr($fieldvals['Type'],"INT"))){$fieldvals['Type']="INTEGER"; }
			$xml .= "<row name=\"$fieldname\"";
			if ($fieldvals['Null']=="YES"){ $xml .= " null=\"1\""; } else { $xml .= " null=\"0\""; }
			if ($fieldvals['Extra']=="auto_increment"){ $xml .= " autoincrement=\"1\""; } else { $xml .= " autoincrement=\"0\""; }
			$xml .= ">\n";
			$xml .= "	<datatype>" . $fieldvals['Type'];
			$xml .= "</datatype>\n";
			$xml .= "	<default>" . $fieldvals['Default'];
			$xml .= "</default>\n";
			// is there a relation key? 
			$rel_sql="SELECT * FROM table_relations WHERE table_2 = \"$master_table\"  AND field_in_table_2=\"$fieldname\"";
			//print "On field $fieldname, looking for this as field_in_table_2.. ";
			$rel_rv=$db->query($rel_sql);
			while ($rel_h=$db->fetch_array($rel_rv)){
				//print "Building relation for " . $rel_h['table_1'] . " on " . $rel_h['field_in_table_1'] . "!<br />";
				$xml .= "	<relation table=\"".$rel_h['table_1']."\" row=\"".$rel_h['field_in_table_1']."\" />\n";	
			}
			$xml .= "</row>\n";
		}
		if ($table1_pk){
			$xml .= "<key type=\"PRIMARY\" name=\"\">\n	<part>$table1_pk</part>\n</key>\n";
		}
		$xml .= "</table>\n\n";
		array_push($tables_written,$master_table);

		// adjust x and y values
		$tablex=$tablex+220;
		if ($tablex==1100){ $tabley=$tabley+200; $tablex=0;}

		} // end t1 relation only


		// now do table 2
		$t2_relation_only=0;
		if (in_array($child_table,$tables_written)){
			$t2_relation_only=1; 
		// 	are we going to need the line below.. ever?
		//	$xml=write_relation_only($master_table,$child_table,$h['field_in_table_1'],$h['field_in_table_2'],$xml);
			continue; 
		}
                $descsql="DESC $child_table";
                $descrv=$db->query($descsql);
                $table2_fields=array();
                while ($desc_rows=$db->fetch_array($descrv)){
                        $table2_fields[$desc_rows['Field']]=$desc_rows;
                        if ($desc_rows['Key']=="PRI"){
                                $table2_pk=$desc_rows['Field'];
                        }
                }
                //var_dump($table2_fields);
                if (!$table2_fields){ continue; }
                // convert to xml
                $xml .= "<table name=\"$child_table\" x=\"$tablex\" y=\"$tabley\">\n";
                foreach ($table2_fields as $fieldname=>$fieldvals){
                        if (!$fieldvals['Default']){ $fieldvals['Default']="NULL"; }
                        $fieldvals['Type']=strtoupper($fieldvals['Type']);
                        if (strlen(stristr($fieldvals['Type'],"INT"))){$fieldvals['Type']="INTEGER"; }
                        $xml .= "<row name=\"$fieldname\"";
                        if ($fieldvals['Null']=="YES"){ $xml .= " null=\"1\""; } else { $xml .= " null=\"0\""; }
                        if ($fieldvals['Extra']=="auto_increment"){ $xml .= " autoincrement=\"1\""; } else { $xml .= " autoincrement=\"0\""; }
                        $xml .= ">\n";
                        $xml .= "       <datatype>" . $fieldvals['Type'];
                        $xml .= "</datatype>\n";
                        $xml .= "       <default>" . $fieldvals['Default'];
                        $xml .= "</default>\n";
                        // is there a relation key?
                        $rel_sql="SELECT * FROM table_relations WHERE table_2 = \"$child_table\"  AND field_in_table_2=\"$fieldname\"";
                        //print "On field $fieldname, looking for this as field_in_table_2.. ";
                        $rel_rv=$db->query($rel_sql);
                        while ($rel_h=$db->fetch_array($rel_rv)){
				//print "Building relation for " . $rel_h['table_1'] . " on " . $rel_h['field_in_table_1'] . "<br />";
                                $xml .= "       <relation table=\"".$rel_h['table_1']."\" row=\"".$rel_h['field_in_table_1']."\" />\n";
                        }
                        $xml .= "</row>\n";
                }
                if ($table2_pk){
                        $xml .= "<key type=\"PRIMARY\" name=\"\">\n     <part>$table2_pk</part>\n</key>\n";
                }
                $xml .= "</table>\n\n";
                array_push($tables_written,$child_table);

		// adjust x and y values
		$tablex=$tablex+220;
		if ($tablex==1100){ $tabley=$tabley+200; $tablex=0; }

	}

	// now get ALL other tables in the system and add them to the end
	$all_tables=$tables->list_tables_basic("application");
	foreach ($all_tables as $table){
		list ($actual_name,$readable) = explode(";;",$table);
		$table=$actual_name;
		if (!strstr($table,"SHOP")){ continue;}
		if (in_array($table,$tables_written)){ continue; }

		$descsql="DESC $table";
		$descrv=$db->query($descsql);
		$table_fields=array();
		while ($desc_rows=$db->fetch_array($descrv)){
			$table_fields[$desc_rows['Field']]=$desc_rows;
			if ($desc_rows['Key']=="PRI"){
				$table_pk=$desc_rows['Field'];
			}
		}
		$extra_xml .= "<table name=\"$table\" x=\"$tablex\" y=\"$tabley\" >\n";
		foreach ($table_fields as $fieldname=>$fieldvals){
			if (!$fieldvals['Default']){ $fieldvals['Default']="NULL"; }
			$fieldvals['Type']=strtoupper($fieldvals['Type']);
			if (strlen(stristr($fieldvals['Type'],"INT"))){$fieldvals['Type']="INTEGER"; }
			$extra_xml .= "<row name=\"$fieldname\"";
			if ($fieldvals['Null']=="YES"){ $extra_xml .= " null=\"1\""; } else { $extra_xml .= " null=\"0\""; }
			if ($fieldvals['Extra']=="auto_increment"){ $extra_xml .= " autoincrement=\"1\""; } else { $extra_xml .= " autoincrement=\"0\""; }
			$extra_xml .= ">\n";
			$extra_xml .= "	<datatype>" . $fieldvals['Type'];
			$extra_xml .= "</datatype>\n";
			$extra_xml .= "	<default>" . $fieldvals['Default'];
			$extra_xml .= "</default>\n";
			// is there a relation key? 
			$rel_sql="SELECT * FROM table_relations WHERE table_2 = \"$table\"  AND field_in_table_2=\"$fieldname\"";
			//print "On field $fieldname, looking for this as field_in_table_2.. ";
			$rel_rv=$db->query($rel_sql);
			while ($rel_h=$db->fetch_array($rel_rv)){
				//print "Building relation for " . $rel_h['table_1'] . " on " . $rel_h['field_in_table_1'] . "!<br />";
				$extra_xml .= "	<relation table=\"".$rel_h['table_1']."\" row=\"".$rel_h['field_in_table_1']."\" />\n";	
			}
			$extra_xml .= "</row>\n";
		}

		if ($table_pk){
			$extra_xml .= "<key type=\"PRIMARY\" name=\"\">\n	<part>$table_pk</part>\n</key>\n";
		}
		$extra_xml .= "</table>\n\n";

		// adjust x and y values
		$tablex=$tablex+220;
		if ($tablex==1100){ $tabley=$tabley+200; $tablex=0;}
	}
	$xml .= $extra_xml;
	$xml .= "</sql>";
	header("Content-type: text/xml");
	print $xml;
	exit;
}

function write_relation_only($t1,$t2,$f1,$f2,$xml){
	// find t1 in current xml
	$xmllines=explode("\n",$xml);
	$found_table=0;
	$found_row=0;
	$found_default=0;
	foreach ($xmllines as $line){
		$line .= "\n";
		if ($found_default){ $rebuilt_xml .= $line; continue; }
		if (!$found_table && !stristr($line,"table name=\"$t1\" ")){ $rebuilt_xml .= $line; continue; } else { $found_table=1; }
		if ($found_table){
			if (!$found_row && !stristr($line,"row name=\"$f1\"" )){ $rebuilt_xml .= $line; continue; } else {$found_row=1; }
			if ($found_row){
				if (!$found_default && !stristr($line,"</default>")){ $rebuilt_xml .= $line; continue; } else { $found_default=1; }
				if ($found_default){
					$line .= "	<relation table=\"$t2\" row=\"$f2\" />\n";
					$rebuilt_xml .= $line;
				}
			}
		}
	}
	return $rebuilt_xml;
}

function save_relations(){

	global $db;
	$text=$_POST['relations'];
	$relations=explode("\n",$_POST['relations']);
	foreach ($relations as $relation){
		if (!$relation){ continue; }
		$pairs=explode(";",$relation);
		foreach ($pairs as $pair){
			list($var,$val)=explode(":",$pair);
			if ($var=="Table-1"){ $table1=$val;}
			if ($var=="Table-2"){ $table2=$val;}
			if ($var=="Table-1-Field"){ $table1_field=$val; }
			if ($var=="Table-2-Field"){ $table2_field=$val; }
		}
		$text .= "T1 $table1 with field $table1_field to T2 $table2 and field $table2_field\n";
		$check_sql="SELECT id FROM table_relations where table_1 = \"$table1\" AND field_in_table_1 = \"$table1_field\" AND table_2 = \"$table2\" AND field_in_table_2 = \"$table2_field\"";
		$check_rv=$db->query($check_sql);
		if ($db->num_rows($check_rv)==0){
			// add this relation
			$add_sql="INSERT INTO table_relations(table_1,field_in_table_1,table_2,field_in_table_2) VALUES(\"$table1\",\"$table1_field\",\"$table2\",\"$table2_field\")";
			$add_rv=$db->query($add_sql);
			$text .= $add_sql . "\n";
			$relation_id=$db->last_insert_id();
		} else {
			$r_h=$db->fetch_array($check_rv);
			$relation_id=$h['id'];
			// do nothing, it's already there
			//$add_sql="INSERT INTO table_relations(table_1,table_1_field,table_2,table_2_field) VALUES(\"$table1\",\"$table1_field\",\"$table2\",\"$table2_field\")";
			//$text .= $add_sql . "\n";
		}
		array_push($keep_relation_ids,$relation_id);
		// loop through and if id does not exist in keep_relation_ids array, delete the relation
	}	

	//file_put_contents("/var/www/vhosts/paragon-digital.net/httpdocs/demo_sites/mbp_site/dt/htdocs/mbp_site/psoft/system/io/relationsave.txt",$text);
	//file_put_contents(IOPATH . "/relationsave.txt",$text);
	print "relations saved successfully.";
	exit;
}

/* ALREADY MOVED CHECK LINKS */
function get_table_type($tablename){

	$test_sql="SELECT system FROM table_meta WHERE table_name = \"$tablename\" AND system=1";
	global $db;
	$res=$db->query($test_sql);
	$h=$db->fetch_array($res);
	if (!$db->num_rows($res)){
		$return_type = "application";
	} else {
		$return_type="system";
	}

		return $return_type;

}

/* format_table_name - ALREADY MOVED UPDATE LINKS */
function format_table_name($real_name){
	$print_tablename=ucfirst(str_replace("_"," ",$real_name));
	global $CONFIG;
	if ($CONFIG['table_prefixes']){
			$print_tablename_arr=array();
			$all_table_prefixes=explode(",",$CONFIG['table_prefixes']);
			foreach ($all_table_prefixes as $table_prefix){
				if (preg_match("/^$table_prefix/",$real_name)){
					$format_tablename=explode("_",$real_name);
					array_shift($format_tablename);
				}
			}
	foreach ($format_tablename as $word){
		array_push($print_tablename_arr, ucfirst($word));
	}
	$print_tablename=join(" ",$print_tablename_arr);
	}
	if (!$print_tablename){ $print_tablename=$real_name;}
	return $print_tablename;
}
?>
