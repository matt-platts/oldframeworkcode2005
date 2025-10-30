<? 

/* transfer.php
 * functions for importing and exporting related table data via text files
*/

function export_rel_table_front(){
	global $db;
	print "<div style=\"margin-left:15px\"><p class=\"admin_header\">Export records from related tables</p>";
	print "<p><b>Note: </b>This function can only be used on parent and child tables where a one to many relationship has been defined in the <a href=\"javascript:loadPage('/software/administrator.php?action=list_table&t=table_relations&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1&jx=1')\">Table Relations</a> section.</p>";
	print "<p><b>Info: </b>This function allows you to export parent and child records from tables which are joined by a one to many join. By entering the id of the parent record, all child records will be exported along with it into a specially formatted text file. This file can be imported back again usingthe 'Import related table data' wizard, and if any child records contain ids of parent records (either parents in the same table <i>or</i> the parent table or indeed both), these fields can be specified and the keys and data will automatically be recalculated to ensure that the records all match up.</p>";
	$tablelist="select table_1 from table_relations";
	$res=$db->query($tablelist) or die($db->db_error());
	$table_relations=array();
	while ($h=$db->fetch_array($res)){
		array_push ($table_relations,$h['table_1']);
	}
	$tablelist=join(",",$table_relations);
?>
<form name="export_related_tables_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?action=export_related_table">
<input type="hidden" name="action" value="export_related_table">
<span style="width:300px; float:left;"><b>1. </b>Select a parent table to export:</span> <select name="master_table">
<?php echo csv_to_options($tablelist); ?>
</select>
<br /><br />
<span style="width:300px; float:left;"><b>2. </b>Enter value of primary key from master table:</span> <input type="text" size="5" name="selector_var"> 
<br /><br />
<span style="width:300px; float:left;"><b>3. </b>Choose a file name to export to:</span> <?php echo $sytem_path;?>/io/<input type="text" name="datafile" value="">.rtd.txt
<br /><br />
<span style="width:300px; float:left;"></span>
<input type="submit" value="Export related data">
</form>
</div>
<?php
}

function rel_table_init($action){
	global $db;
	if ($action != "import"){
		// simulate input vars
		$master_table=$_REQUEST['master_table'];
		if (!$master_table){print "No master table selected."; exit;}
		$get_relations="SELECT * from table_relations where table_1 = \"$master_table\"";
		$res=$db->query($get_relations) or die($db->db_error());
		/* This needs to be a multiple array below.. */
		$child_tables=array();
		$child_fields=array();
		while ($h=$db->fetch_array($res)){
			$child_table=$h['table_2'];
			$master_field=$h['field_in_table_1'];
			$child_field=$h['field_in_table_2'];
			array_push($child_tables,$child_table);
			array_push($child_fields,$child_field);
		}

		//$master_table="menu";
		//$child_table="menu_items";
		//$master_field="id";
		//$child_field="menu_id";
		$selector_var=$_REQUEST['selector_var']; // the ID (primary key) from the MASTER table
		$extra_relation_field="parent_id"; // ?!
		$datafile=IOPATH."/".$_REQUEST['datafile'].".rtd.txt";
	} else {
		$datafile=IOPATH."/".$_REQUEST['datafile'];
	}


	if ($action=="export"){
		export_related_tables($master_table,$child_tables,$master_field,$child_fields,$selector_var,$extra_relation_field,$datafile);
	}

	if ($action =="import"){
		import_related_tables($datafile);
	}
}

function export_related_tables($master_table,$child_tables,$master_field,$child_fields,$selector_var,$extra_relation_field,$datafile){
	global $db;
	print "<p class=\"admin_header\">Export Related Tables</p>";
	$relationship=$master_table . "." . $master_field . " = " . $child_table . "." . $child_field;

	$master_fields=list_fields_in_table($master_table);
	$tablecounter=0;
	$output_text="";
	foreach ($child_tables as $child_table){
		$child_field=$child_fields[$tablecounter];

		$child_table_fields=list_fields_in_table($child_table);
		$master_field_list=join(",",$master_fields);
		$child_field_list=join(",",$child_table_fields);
		$mpk=get_primary_key($master_table);
		$cpk=get_primary_key($child_table);
		// grab the master query first.
		$output_text .= "$master_table\n$child_table\n$master_field\n$child_field\n";
		$sql="SELECT ";
		foreach ($master_fields as $field){
			$sql .= $master_table . "." . $field . ",";	
		}
		$sql = preg_replace("/,$/","",$sql);
		$sql .= " FROM " . $master_table;
		if ($selector_var){$sql .= " WHERE " . $master_field . " = \"" . $selector_var . "\"";}
		$res=$db->query($sql);
			$newsql = "INSERT INTO $master_table ($master_field_list) values(";
		while ($h=$db->fetch_array($res)){ // should be one line
			foreach ($h as $var => $val){
				if ($var==$mpk){
					$newsql .= "\"{=pk}\",";
				} else {
					$newsql .= "\"".$h[$var]."\",";
				}
			}
			$newsql = preg_replace("/,$/","",$newsql);
			$newsql .= ")";	
		}
		// WE HAVE NEWSQL which is the master query for the master table;
		$output_text .= $newsql . "\n";

		$sql = "SELECT ";
		foreach ($child_table_fields as $field){
				$sql .= $field . ",";	
		}
		$sql = preg_replace("/,$/","",$sql);
		$sql .= " FROM $child_table"; 
		if ($selector_var){$sql .= " WHERE $child_field = $selector_var";}

		$count=1;
		$res=$db->query($sql) or die ($db->db_error());
		while ($h=$db->fetch_array($res)){
			$newsql = "INSERT INTO $child_table ($child_field_list) values(";
			foreach ($h as $var => $val){
				if ($var==$child_field){
					$newsql .= "\"{=cpk}\",";
				} else if ($extra_relation_field && $var==$extra_relation_field){
					$newsql .= "\"{=cpk" . $h[$var] . "}\",";
				} else if ($var==$cpk){
					$newsql .= "\"{=pk" . $h[$var] . "}\",";
				} else {
					$newsql .= "\"".$h[$var] . "\",";
				}
			}	
			$newsql = preg_replace("/,$/","",$newsql);
			$newsql .= ")";	
			$output_text .= $newsql . "\n";
			$count++;
		}
	$tablecounter++;
	$output_text .= "-----x-----\n";
	}

	if ($debug){print $output_text;}
	print "<p>Writing to $datafile...</p>";
	file_put_contents($datafile,$output_text) or format_error("Unable to write to $datafile",1);
	print "<p>This record and it's children have been exported to $datafile.</p><p>You can load this into this or another instance of this software using the import related table function.";
}

function import_related_tables($datafile){

	global $db;
	$all_data=explode("-----x-----",file_get_contents($datafile)) or print "cant read file";
	foreach ($all_data as $tableset){
		$data=explode("\n",$tableset) or print "cant read table set data";
		// get initial data out of table
		$master_table=array_shift($data);
		$child_table=array_shift($data);
		$master_field=array_shift($data);
		$child_field=array_shift($data);
		if (!$master_table || !$child_table || !$master_field || !$child_field){ continue; }

		$relationship=$master_table . "." . $master_field . " = " . $child_table . "." . $child_field;
		$master_fields=list_fields_in_table($master_table);
		$child_fields=list_fields_in_table($child_table);
		$master_field_list=join(",",$master_fields);
		$child_field_list=join(",",$child_fields);
		$mpk=get_primary_key($master_table);
		$cpk=get_primary_key($child_table);
		
		$insert_to_master=array_shift($data);
		$insert_to_master=str_replace("{=pk}","",$insert_to_master);
		$insert_res=$db->query($insert_to_master) or die($db->db_error());
		$pk=$db->last_insert_id();
		if ($debug){ print "pk is " . $pk; }
		$ins_id_ar=array();
		foreach ($data as $dataline){
			if (!$dataline){continue;}
			if ($debug){ print "<p>on <b>$dataline</b><br>"; }
			// scan through data replacing pk
			$dataline=preg_replace("/{=cpk}/",$pk,$dataline); // cpk is pk in child table, so replace with new pk (last insert id of main query)
			$pkmatches=preg_match_all("/{=pk(\d+)}/",$dataline,$matches); // {=pk4} for example is the old pk 4. Store this +replace parent fields
			$original_primary_key=$matches[1][0];
			$cpkmatches=preg_match_all("/{=cpk(\d+)}/",$dataline,$matches); // {=pk4} for example is the old pk4. Sore this + replace parent fields
			$rel_pk=$matches[1][0];
			$dataline=preg_replace("/{=pk\d+}/","",$dataline);
			$dataline=preg_replace("/{=cpk0}/","0",$dataline);
			if ($debug){ print "original pk is $original_primary_key"; }
			if ($rel_pk){
				$dataline=preg_replace("/{=cpk(\d+)}/",$ins_id_ar[$rel_pk],$dataline);
			}
			// delete this line $dataline=preg_replace("/{=cpk\d+}/","EEEEE",$dataline);

			$sql=$dataline;
			$res=$db->query($sql) or die ($db->db_error());
			$new_pk=$db->last_insert_id();
			$ins_id_ar[$original_primary_key]=$new_pk;
			if ($debug){ print "<br>".$dataline . "<br>added with pk of $new_pk\n"; }
	//		$ins_id_ar[$count]=$db->last_insert_id();	
		}
	}
print "<p class=\"dbf_para_success\">New data has been added with a parent primary key of " . $pk . ".</p>";
}

function import_related_table_front(){
	$dir_list=csv_to_options(join(",",get_directory_list("system/io","rtd.txt")));
?>
<p class="admin_header">Import Related Table Data</p>
<div style="padding-left:15px">
<p>Import Related Table Data allows you to import a pair of joined tables exported by the 'Export Related Tables' function only.</p>
<form action="administrator.php?action=import_related_table" method="post">
<span style="width:300px; padding-bottom:20px">Please select the related table file you wish to import below:</span><br /><br /> <select name="datafile"><?php echo $dir_list; ?></select>
<span style="width:300px; padding-bottom:20px"></span> <input type="submit" name="submit" value="Import Related Tables">
</form>
</div>
<?php
}
?>
