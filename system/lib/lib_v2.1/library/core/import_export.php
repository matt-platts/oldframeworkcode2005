<?

/* This file is quite old
 * Used to import and export databases, and also upgrate the database entries required to run the software itself in order to update it
 * Has not been touched since around 2008, occasionali use 
 */

function import_database($sqlfile,$type){
	global $user;
	if ($user->value("type") != "master"){
		print "Access denied"; exit;
	}
	//sqlfile has come in as a file upload	
	if (!$sqlfile && !$type){
		print_db_import_form($type);
		exit;
	} else {
		print "<p class=\"admin_header\">Importing SQL Data..</p>";
	}
	
	open_col2();
	if ($type=="local_file" && file_exists("$systempath/io/$sqlfile")){
		print "<p>Running SQL from local file..</p>";
		$f=IOPATH."/".$sqlfile;
		$openfile=fopen("$f","r");
		$file_contents=fread($openfile, filesize("$f"));
		fclose($openfile);
		//print "contents of $f is " . $file_contents;
		global $database_username;
		global $database_name;
		global $database_password;
		$command="mysql -u $database_username $database_name -p$database_password < ".IOPATH."/".$sqlfile;
		print "command is " . $command;
		$result = exec($command);
		print "<p>result is " . $result;
		
	}

	if ($type=="pasted_sql"){
                open_col2();
                print "<p>Installing pasted data..</p>";
                print "<p>Data will first be saved out into ".IOPATH."/temp.sql. This operation will not work if this file is not writeable (chmod 777).</p>";
                global $database_username;
                global $database_name;
                global $database_password;
                $sqlfile = preg_replace("/txtareareplacestring/","textarea",$sqlfile);
		$iopath=IOPATH;
                if (!file_exists("$iopath/temp.sql")){
                        $create_file=exec("touch $iopath/temp.sql");
                        $chmod_it=exec("chmod 777 $iopath/temp.sql");
                }
                $fh=fopen("$iopath/temp.sql","w") or format_error("Unable to open $iopath/temp.sql for writing. Please check the permissions on this file and directory.",1);
                fwrite($fh,$sqlfile);
                fclose($fh);
                $command="mysql -u $database_username $database_name -p$database_password < $iopath/temp.sql";
                print "Sending the following command to MySQL: " . $command;
                $result = exec($command);
                print "<p>SQL has been run.</p>";
        }

}

function print_db_import_form(){
	global $user;
	open_col2();
	print "<p class=\"admin_header\">Import SQL</p>";
	if ($user->value("type") != "god" && $user->value("type") != "master"){
		print "Access denied"; exit;
	}
	?>
	<p>Choose one of the methods below of importing your data</p>
	<font size=1><sup>NB: This form will actually run any sql that you throw at it.</sup></font>
	<p>
	<form name="database_upload_form" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>?action=import_database">
	<input type="hidden" name="type" value="<?php echo $type; ?>">
	<b>1. Upload SQL:</b>
	<p>
	<input type="file" name="sqlfile"> &nbsp; <input type="submit" value="Upload File">
	</form>
	<hr size=1>
	<form name="database_local_file" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>?action=import_database">
	<input type="hidden" name="type" value="<?php echo $type; ?>">
	<b>2. Select a local file from 'io' directory:</b> 
	<p>
	<select name="sqlfile"><option value="Please Select..">Please Select..</option>
	<?php
	$local_file_list=array();
	$iopath=IOPATH;	
	exec("ls $iopath/*.sql -al",$local_file_result);
	$fileparts=array();
	foreach ($local_file_result as $local_file){
		if (preg_match("/\w+\.sql/i",$local_file)){	
			$fileparts=array();
			$fileparts=preg_split("/\s+/",$local_file);
			$array_no = (sizeof($fileparts))-1;
			$fileparts[$array_no]=str_replace("$iopath/","",$fileparts[$array_no]);
			print "<option value=\"".$fileparts[$array_no]."\">".$fileparts[$array_no]."</option>";
		}
	}
	?>
	</select>
	<input type="hidden" name="type" value="local_file">
	 &nbsp; <input type="submit" value="Import Database">
	</form>
	<hr size=1>
	<b>3. Paste your SQL Below:</b>
	<form name="database_paste_sql" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>?action=import_database">
	<textarea name="sqlfile" rows=10 cols=60></textarea><p><input type="submit" value="Run Pasted SQL">
	<input type="hidden" name="type" value="pasted_sql">
	</form>
	<?
	close_col();
}

function dump_database(){

	if ($_REQUEST['type']=="system"){dump_system(); return; }

	global $db;
	global $database_username;
	global $database_password;
	$database_name=$db->dbname();

	ini_set("safe_mode",0);
	error_reporting(E_ALL);

	$dt=date("d-m-Y--h-i-s");
	$dbdump_filename = IOPATH."/";
	$dbdump_filename .= $database_name ."_dump_$dt.sql";
	$filename_local = IOPATH. "/" . $database_name . "_dump_$dt.sql";

	$exec_string="-u {=username} {=database_name} --complete-insert -p{=password} > $dbdump_filename 2>&1";
	$exec_result=$db->dump_data($exec_string);
	//$exec_result=passthru($exec_string);
	open_col2();
	print "<p class=\"admin_header\">MySQL Dump</p>";
	print "<p>Preparing to execute the following command:<br><pre>mysqldump $exec_string</pre><p>";
	print "<b>Database Dump Result:</b> $dumpresult<br />Database has been exported as " . $dbdump_filename;
	close_col();
}

function dump_system(){
	global $db;
	$database_name=$db->dbname();

	ini_set("safe_mode",0);
	error_reporting(E_ALL);

	$dt=date("d-m-Y--h-i-s");
	$dbdump_filename = $basepath . "/temp/";
	$dbdump_filename .= $database_name ."_dump.sql";
	$filename_local = IOPATH."/" . $database_name . "_system_dump_$dt.sql";

	open_col2();
	print "<p class=\"admin_header\">Dumping System to $filename_local</p><ul>";
	
	// scan through table_meta to get a list of all system tables
	$sql="SELECT * from table_meta where system=\"1\"";
	$result=$db->query("SELECT * from table_meta");
	$all_system_tables=array();
	$structure_only_tables=array();
	$sys_dump_id_tables=array();
	while ($sys_table_rows=$db->fetch_array($result)){
		array_push($all_system_tables,$sys_table_rows['table_name']);
		if ($sys_table_rows['system_dump_param']=="structure only"){array_push($structure_only_tables,$sys_table_rows['table_name']);}
		if ($sys_table_rows['system_dump_ids']){$sys_dump_id_tables[$sys_table_rows['table_name']]=array(); $sys_dump_id_tables[$sys_table_rows['table_name']]['dump_id']=$sys_table_rows['system_dump_ids'];}
	}
	
	foreach ($all_system_tables as $system_table){
		$table_data_dump_filename=$basepath . "/temp/" . $database_name . "--data--" . $system_table;	
		// if the table has a system column, dump where the value is 1, else dump whole table
		$check_table_sql="DESC $system_table";
		$check_result=$db->query($check_table_sql);
		$has_system_column=0;
		while ($check_row=$db->fetch_array($check_result)){ 
			if ($check_row['Field']=="system"){
				$has_system_column=1;
			}
		}
		$structure_only_table=0;
		if ($has_system_column){
			print "<p><li>Table: $system_table<br>Dump: Structure and System columns<br />\n";
			$exec_string="-u {=username} {=database_name} $system_table --complete-insert --where=system='1' -p{=password}";
		} else {
			// is it a structure only table?
			foreach ($structure_only_tables as $structure_only_table){
				if ($structure_only_table==$system_table){
					$dump_structure_only=1;
				}
			}
			if (!$dump_structure_only){
				if (!$sys_dump_id_tables[$system_table]){
					print "<p><li>Table: $system_table<br>Dump: Structure and all data<br />\n";
					$exec_string="-u {=username} {=database_name} $system_table -p{=password}";
				} else {
					print "<p><li>Table: $system_table<br>Dump: Structure and id " . $sys_dump_id_tables[$system_table]['dump_id'] . "<br />\n";
					$exec_string="-u {=username} {=database_name} $system_table --where=\"ID IN (" . $sys_dump_id_tables[$system_table]['dump_id'] . ")\" -p{=password}";

				}
			} else {
				if (!$sys_dump_id_tables[$system_table]){
					print "<p><li>Table: $system_table<br>Dump: Structure only<br />\n";
					$exec_string="-u {=username} {=database_name} $system_table --no-data -p{=password}";
				} else {
					print "<p><li>Table: $system_table<br>Dump: Structure only + id " . $sys_dump_tables[$system_table]['dump_id'] . "<br />\n";
					$exec_string="-u {=username} {=database_name} $system_table --where=\"ID IN (" .$sys_dump_id_tables[$system_table]['dump_id'] . ") -p{=password}";

				}
			}
		}
		print "<font size=1>$exec_string</font></li>";
		$sqldump=$db->dump_data_exec($exec_string,$sqldump); // appends response of exec into $sqldump array each time, this is the default behaviour (to append)
	}	
	print "</ul><hr>";
	print "Opening $filename_local..\n";
	$fp = fopen ($filename_local,"w") or die ("Cannot open local file $filename_local for writing!");
	foreach ($sqldump as $line){
		fwrite($fp,$line . "\n");
	}
	fclose($fp);
	print "<p>A system data dump has been saved as $filename_local.";
	close_col();
}

function dump_user_tables(){
	global $db;
	$database_name=$db->dbname();
	global $basepath;

	ini_set("safe_mode",0);
	error_reporting(E_ALL);

	$dt=date("d-m-Y--h-i-s");
	$dbdump_filename = $basepath . "/temp/";
	$dbdump_filename .= $database_name ."_user_data_dump.sql";
	$filename_local = IOPATH . "/" . $database_name . "_user_data_dump_$dt.sql";
	
	$all_user_tables=list_tables();

	open_col2();

	// scan through table_meta to get a list of all system tables
	$sql="SELECT * from table_meta";
	global $db;
	$result=$db->query("SELECT * from table_meta");
	$all_system_tables=array();
	$structure_only_tables=array();
	while ($sys_table_rows=$db->fetch_array($result)){
		array_push($all_system_tables,$sys_table_rows['table_name']);
		if ($sys_table_rows['system_dump_param']=="structure only"){array_push($structure_only_tables,$sys_table_rows['table_name']);

}
	}

	print "<h4>Dumping User Tables and user data:</h4><ul>";
	foreach ($all_user_tables as $user_table){
		$table_data=$user_table;
		$user_table=$table_data['real_name'];
		$is_system_table=$table_data['system'];

		// if the table has a system column, dump where the value is 0, else dump nothing 
		$check_table_sql="DESC $user_table";
		$check_result=$db->query($check_table_sql);
		$has_system_column=0;
		while ($check_row=$db->fetch_array($check_result)){ 
			if ($check_row['Field']=="system"){
				$has_system_column=1;
			}
		}
		$structure_only_table=0;
		$dump_structure_only=0;
		if ($has_system_column && $is_system_table){ // has system column and is systen table? dump where system =0
			print "<p><li>Table: $user_table<br>Dump: Data only where system is null or 0. No info.</li>\n";
			$exec_string="-u {=username} {=database_name} $user_table --where=\"system IS NULL OR system=0\" --no-create-info -p{=password}";
		} else {
			$exec_string="";
			// is it a structure only table?
			foreach ($structure_only_tables as $structure_only_table){
				if ($structure_only_table==$user_table){
					$dump_structure_only=1;
				}
			}
			if (!$dump_structure_only && !$is_system_table){
				print "<p><li>Table: $user_table<br>Dump: Structure and all data</li>\n";
				$exec_string="-u {=username} {=database_name} $user_table -p{=password}";
			} else if (!$is_system_table){ 
				print "<p><li>Table: $user_table<br>Dump: Structure only</li>\n";
				$exec_string="-u {=username} {=database_name} $user_table --no-data -p{=password}";
			} else if ($dump_structure_only && $is_system_table){
				print "<p><li>Table: $user_table<br>Dump: Data only</li>\n";
				$exec_string="-u {=username} {=database_name} $user_table --complete-insert --no-create-info -p{=password}";
			}
		}
		if ($exec_string){
			$exec_string_print=preg_replace("/-p\w+/","-p********",$exec_string);
			print "<font size=1>$exec_string_print</font></li><br /><br />";
			$sqldump=$db->dump_data_exec($exec_string,$sqldump); // appends response of exec into $sqldump array each time, this is the default behaviour (to append)
		}
	}	
	print "</ul><hr>";
	$fp = fopen ($filename_local,"w");
	foreach ($sqldump as $line){
	//	print $line . "\n";
		fwrite($fp,$line . "\n");
	}
	fclose($fp);

	print "<p>A user data dump has been saved as $filename_local./";
	close_col();
}

function export_software_action(){
	print "<p class=\"admin_header\">Export Software to ".$_POST['filename'].".".$_POST['filetype']."</p>";
	$full_filename = IOPATH . "/" . $_POST['filename'] . "." . $_POST['filetype'];
	print "<p>Software exported successfully as $full_filename.<br />";
	if ($_POST['include_sql']=="system"){ print "A system data dump is included as ".IOPATH."/systemdata.sql";}
	if ($_POST['include_sql']=="all"){ print "A full database dump is included as ".IOPATH."/fulldata.sql";}
	if ($_POST['include_sql']=="none"){ print "No system or user data has been included";}
}

function export_software_front(){
?>
<p class="admin_header">Export Software</p>
<p>This function allows you to export the entire software as a zip or tar file. This can then be set up on another server.</p>
<form method="post" action="administrator.php?action=export_software_action">
<input type="radio" name="include_sql" value="system" checked>Inlucde system data<br />
<input type="radio" name="include_sql" value="all">Inlucde system and user data<br />
<input type="radio" name="include_sql" value="none">Do not inlucde any data<br />
<br />
Filename: <?php echo IOPATH;?>/<input type="text" name="filename" value=""> . <select name="filetype"><option value="tar">tar</option><option value="zip">zip</option></select> 
<input type="submit" value="Export Software">
</form>
<?
}

function upgrade_software_front(){
	print "<p class=\"admin_header\">Upgrade Software</p>";
	print "<p class=\"dbf_para_alert\"><b>IMPORTANT!!!! This operation may overwrite database entries, PHP files and other system files. </b><br /><br />Please BACK UP YOUR SYSTEM before upgrading the software. This means BOTH DATA AND PHP FILES! Ensure that you know how to restore your system beforehand should you need to and be prepared to test your new installation thoroughly.</p><p>Please select the software upgrade file from the io directory:";
	print "<p><select name=\"sqlfile\"><option value=\"Please Select..\">Please Select..</option>\n";
	$local_file_list=array();
	$iopath=IOPATH;
	exec("ls $iopath/*.sql -al",$local_file_result);
	$fileparts=array();
	foreach ($local_file_result as $local_file){
		if (preg_match("/\w+\.sql/i",$local_file)){	
			$fileparts=array();
			$fileparts=preg_split("/\s+/",$local_file);
			$array_no = (sizeof($fileparts))-1;
			$fileparts[$array_no]=str_replace("$iopath/","",$fileparts[$array_no]);
			print "<option value=\"".$fileparts[$array_no]."\">".$fileparts[$array_no]."</option>";
		}
	}
	print "</select>";
	print "</p><p>Please enter the master system password:</p><p><input type=\"password\" name=\"sys_pass\" value=\"\">";
	print "</p><p><input type=\"submit\" value=\"Upgrade Software\">";

}

?>
