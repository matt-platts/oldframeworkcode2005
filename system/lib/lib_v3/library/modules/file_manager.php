<?php

/*
* File: file_manager.php
*/

/*
 * Function file_manager_front
 * API Action: file_manager
*/
function file_manager_front(){
	open_col2();
	print "<p class=\"admin_header\"><img src=\"".SYSIMGPATH."/icons/folder_table.png\" /> File Manager - <span style=\"font-weight:normal\">Manage the local filesystem</span></p>";
	print "<p>Explore files on the server, set up macros and front end interfaces into the filesystem.</p>";
	print "<table width=\"100%\"><tr><td width=\"50%\" valign=\"top\">";

	print "<h3 style=\"background-color:#f1f1f1\">Browse</h3>";
	print "<p><a class=\"arrow_right\" style=\"background-image:url('".SYSIMGPATH."/icons/folder_explore.png'); background-repeat:no-repeat; padding-left:20px;\" href=\"".$_SERVER['PHP_SELF']."?action=directory_browser&dir=.\">Filesystem Explorer</a><br />&nbsp; &nbsp; &nbsp; <span style=\"font-size:9px\">Explore the local filesystem and perform batch jobs on directories.</span>";

	print "</td><td width=\"50%\" valign=\"top\">";

	print "<h3 style=\"background-color:#f1f1f1\">File Manager Widget</h3>";
	print "<p>The file manager widget allows you to display directory listings in assorted views (thumbnails, lists etc) and import the views into your web site pages with an optional file uploader.</p>";
	print "<a class=\"arrow_right\" style=\"background-image:url('".SYSIMGPATH."/icons/folder_edit.png'); background-repeat:no-repeat; padding-left:20px;\" href=\"".$_SERVER['PHP_SELF']."?action=list_table&t=file_manager&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1\">Set Up / Edit File Management Directories</a><br />&nbsp; &nbsp; &nbsp;<span style=\"font-size:9px\">This is where you set up the directories you want to enable for use with the file manager widget.</span></p>";
	print "<p><a class=\"arrow_right\" style=\"background-image:url('".SYSIMGPATH."/icons/folder_table.png'); background-repeat:no-repeat; padding-left:20px;\" href=\"".$_SERVER['PHP_SELF']."?action=list_table&t=file_manager_options&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=20&dbf_filter=1&dbf_sort=1\">Set Up / Configure Interfaces to File Manager</a><br />&nbsp; &nbsp; This is where you set up the interface to be imported into a web page.</p>";
	print "<p><a class=\"arrow_right;\" style=\"background-image: url('".SYSIMGPATH."/icons/folder_picture.png'); background-repeat:no-repeat;padding-left:20px;\" href=\"".$_SERVER['PHP_SELF']."?action=file_browser\">Browse managed directories through the File Manager Widget.</a></p>";

	print "</td></tr><tr><td valign=\"top\" width=\"50%\">";

	print "<h3 style=\"background-color:#f3f3f3\">Macros</h3>";
	print "<a class=\"arrow_right\" style=\"background-image:url('".SYSIMGPATH."/icons/folder_go.png'); background-image:no-repeat; padding-left:20px;\" href=\"".$_SERVER['PHP_SELF']."?action=list_table&t=file_manager_macros\">Configure Macros</a><br /> &nbsp; &nbsp; &nbsp; <span style=\"font-size:9px\">Set up macros to run for specific directories and file types.<br /> &nbsp; &nbsp; &nbsp; Macros can be triggered off uploads through any form interface.</span></p>";

	print "</td><td>";
	print "<h3 style=\"background-color:#f1f1f1\">Tools</h3>";
	print "<p><a class=\"arrow_right;\" style=\"background-image: url('".SYSIMGPATH."/icons/folder_go.png'); background-repeat:no-repeat;padding-left:20px;\" href=\"".$_SERVER['PHP_SELF']."?action=file_browser\">Run Batch Job</a><br />&nbsp; &nbsp; &nbsp;<span style=\"font-size:9px;\">Run a script over every file in a directory</span></p>";
	print "<p><a class=\"arrow_right;\" style=\"background-image: url('".SYSIMGPATH."/icons/folder_wrench.png'); background-repeat:no-repeat;padding-left:20px;\" href=\"".$_SERVER['PHP_SELF']."?action=file_browser\">Run File Permissions Scan</a><br />&nbsp; &nbsp; &nbsp;<span style=\"font-size:9px;\">Reports on any files whose permissions are set possibly incorrectly such as writeable system files and directories</span></p>";

	print "</td></tr></table>";
}

/*
 * Function file_manager_main
*/
function file_manager_main($directory,$list_type,$display_options,$options_position,$default_no_per_page,$list_dir_options){
	global $db;
	if ($list_dir_options['list_type']){$list_type=$list_dir_options['list_type'];}
        if (!$list_type){$list_type="thumbs";}
        if ($directory && !$display_options){list_directory($directory,$list_type,$default_no_per_page,$list_dir_options);}
        if (!$directory && !$display_options){$display_options=1;}
        if ($display_options && (!$options_position || $options_position=="top" )){
                // get a list of enabled directories from the database
                $directories=array();
                if ($list_dir_options['filter_directory_list']){;}
                if (preg_match("/site.php/",$_SERVER['PHP_SELF'])){$append_to_query = " WHERE (back_end_admin_only = 0 or back_end_admin_only IS NULL)";}
                $sql="SELECT * from file_manager" . $append_to_query; $result=$db->query($sql);
                while ($rows=$db->fetch_array($result)){
                        $list_for_options .= $rows['directory'];
                        $list_for_options .= ";;";
                        $list_for_options .= $rows['title'];
                        $list_for_options .= ",";
                        if ($rows['directory']==$_REQUEST['d']){$selected_dir=$rows['directory'] . ";;" . $rows['title'];}
			if (is_empty_dir($rows['directory'])){
				$files_array[$rows['title']]['class']="empty_directory";
			} else {
				$files_array[$rows['title']]['class']="non_empty_directory";
			}
                }
                $list_for_options=preg_replace('/\,$/','',$list_for_options);
                $option_list=database_functions::csv_to_options($list_for_options,$selected_dir,$files_array);
                if (!$col2_open){open_col2();}
                print "<p><form action=\"" . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "\" method=\"post\" name=\"dbf_filebrowserform\">\n";
                print "<select name=\"d\" class=\"browse_directory_select_list\" onchange=\"document.forms['dbf_filebrowserform'].submit()\">\n<option value=\"\">Select..</option>" . $option_list . "\n</select>";
		?><!--if (!$list_dir_options['hide_browse_directory_button']){print "<input type=\"submit\" value=\"Browse Directory\" class=\"browse_directory_button\">";}//--><?php
                print "<input type=\"hidden\" name=\"display_options\" value=\"1\">";
                print "<input type=\"hidden\" name=\"options_position\" value=\"top\">";
                print "</form></p>";
                if ($db->num_rows($result)==1){list_directory($directory,$list_type,$default_no_per_page,$list_dir_options);}
                if ($directory){list_directory($directory,$list_type,$default_no_per_page,$list_dir_options);}
		if (!$directory){ 

			?>
<div id="homediv" style="padding-right: 50px;" align="right">
<span style="float: right;"><a href="site.php?s=1"><img src="images/home.gif" border="0"></a></span>
</div>
			<?php
			print "<span class=\"subtitle\">".$list_dir_options['intro_text']."<span>";}
        } elseif ($display_options && $options_position=="bottom"){
        }
}

// the original file manager directory list thingy (ugh)
function list_directory($dir,$listtype,$default_no_per_page,$list_dir_options){
	if (preg_match("/administrator.php/",$_SERVER['PHP_SELF'])){
		open_col2();
	}

?>
<!-- baha //-->
<script language="Javascript">
<!--
function deletePhoto(sFile,sDirectory,sDisplayOptions,sOptionsPosition){
	if(confirm("Are you sure you want to delete this photograph?")){
		locstring="administrator.php?action=file_browser&dt="+sFile+"&d="+sDirectory+"&display_options="+sDisplayOptions+"&options_position="+sOptionsPosition;
		location=locstring;
	}
}
//-->
</script>
<?php
	if (!$default_no_per_page){$default_no_per_page="100";}
	$image_cells_accross=3;
	if ($listtype=="thumbs" && $list_dir_options['image_cells_accross']){
		$image_cells_accross=$list_dir_options['image_cells_accross'];
	} else if ($listtype=="thumbs" && !$list_dir_options['image_cells_accross']) {
		$image_cells_accross=3;
	} else if ($listtype != "thumbs") {
		$image_cells_accross=3;
	}
        if ($_REQUEST_SAFE['dt']){ // the delete option
                $dtd=1;
		$dir = str_replace(" ","",$dir);
                $filename=$dir . "/" . $_REQUEST_SAFE['dt'];
                if (!unlink($filename)) {$dtd=0; unable_to_unlink_file($dir,$filename);}
                //echo exec('ls -al');
                if ($dtd){print "$filename has been deleted.<p>";}
        }

        if ($list_dir_options['file_uploader'] || $list_dir_options['display_uploader']){ 
		upload_file_front("$dir",$list_dir_options['fileint']);
	} 
	if (preg_match("/administrator.php/",$_SERVER['PHP_SELF'])){ print "<p><br />"; } else { print "<p>";}
	$directory_name=ucfirst(basename($dir));
	$directory_name = str_replace("_"," ",$directory_name);
	echo "<span style=\"float:right; font-weight:bold; margin-right:25px; padding-right:25px\"><a href=\"".$list_dir_options['home_button_url']."\"><!--<img src=\"".$list_dir_options['home_button_image']."\" border=0 width=50>//--></a></span>";
	if ($list_dir_options['default_title']){
		echo "<span class=\"file_browser_title\" style=\"text-align:left; width:100%; \" >".$list_dir_options['default_title']."</span>";
	} else {
		echo "<span class=\"file_browser_title\">Showing files in: $sub_dir $directory_name. Click on the trash icon to delete a photograph.</span>";
	}
        echo "<span class=\"subtitle\">" . $list_dir_options['default_text'] . "</span>";
        $count_files_in_dir = count(glob($dir . "/*"));
	if ($list_dir_options['default_items_per_page']){$default_no_per_page = $list_dir_options['default_items_per_page'];}
        if ($count_files_in_dir > $default_no_per_page){ if ($default_no_per_page){$no_of_pages = ceil($count_files_in_dir/$default_no_per_page);} else {$no_of_pages=1;}
        } else {
		$no_of_pages=1;
	}
        $current_page=$_REQUEST['current_page']; if (!$current_page){$current_page=1;}
        $next_page=$current_page+1;
        $previous_page=$current_page-1;
        $first_image = $current_page*$default_no_per_page;
        // print form
	if ($_REQUEST['s']){$sitestring="s=".$_REQUEST['s']."&";}
	if ($_REQUEST['mt']){$sitestring.="mt=".$_REQUEST['mt']."&";}
        print "<form name=\"current_image_form\" action=\"".$_SERVER['PHP_SELF'] . "?".$sitestring."action=file_browser\" method=\"post\"><input type=\"hidden\" name=\"d\" value=\"".$_REQUEST['d']."\"><input type=\"hidden\" name=\"display_options\" value=\"".$_REQUEST['display_options']."\"><input type=\"hidden\" name=\"options_position\" value=\"top\"><input type=\"hidden\" name=\"current_page\" value=\"$current_page\">";
        //print "Total files : <b>$count_files_in_dir</b>. ";
	if ($count_files_in_dir>0){print "<!--This is page <b>$current_page</b> of <b>$no_of_pages</b>.//-->";}
	if ($list_dir_options['previous_page']){$previous_text = $list_dir_options['previous_page'];} else {$previous_text = "&lt; Previous Page";}
	if ($list_dir_options['next_page']){$next_text = $list_dir_options['next_page'];} else {$next_text= "&lt; Next Page";}
        //if ($current_page > 1 && $current_page < $no_of_pages){print " | ";}
		if ($current_page > 1){print "<a href=\"javascript:document.forms['current_image_form'].elements['current_page'].value=$previous_page; document.forms['current_image_form'].submit()\"><img src=\"images/previous_page.gif\" border=0></a>";}
        //if ($current_page > 1 && $current_page < $no_of_pages){print " | ";}
        if ($current_page < $no_of_pages) {print " <a href=\"Javascript:document.forms['current_image_form'].elements['current_page'].value=$next_page; document.forms['current_image_form'].submit()\"><img src=\"images/next_page.gif\" border=0></a>";}
	if ($listtype=="thumbs" || $list_type=="table"){
		echo "<table style=\"border-collapse:collapse; margin-left:25px\" class=\"formtable,images_table\" background-color=\"#ffffff\" border=0>\n";
	}
        if ($listtype == "table"){
        echo "<tr bgcolor=\"#f1f1f1\"><td><b>File Name:</b></td><td bgcolor=\"#f1f1f1\"><b>File type:</b></td></tr>\n";
        }
	if ($listtype=="list"){echo "<p style=\"margin-top:0px; padding-top:0px;\">";}
        $count=0;
        $filecount=0;
        $start_printing_at=($current_page*$default_no_per_page)-$default_no_per_page;
        // Open a known directory, and proceed to read its contents
	chdir(INSTALL_ROOT);
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != "." && $file != ".." && !is_dir($dir/$file)){
			    $print_filename=$file;
			    if ($list_dir_options['remove_file_extension_from_name']){
				$print_filename = explode(".",$file);
				$print_filename = $print_filename[0];
			    } else {
				$print_filename=$file;
			    }
			
                            if ($filecount == $default_no_per_page*$current_page){continue(1);}
                            $filecount++;
                            if ($start_printing_at > $filecount-1){continue(1);}
                            $count++;
                            if ($listtype == "table"){
                                    $filetype=mime_content_type("$dir/$file");
                                    if (!$filetype){
                                        $bits=explode(".",$file);
                                        $filetype=$bits[(sizeof($bits)-1)];
                                    }
                                    echo "<tr><td><font size='2'><a href=\"$dir/$file\">$print_filename</a></font></td><td>" . mime_content_type("$dir/$file") . "</td></tr>\n";
                            } else if ($listtype == "list") {

				    $printfile=$file;
				    $printfile=preg_replace("/\.\w{3}/","",$printfile);
				    $printfile=str_replace("_"," ",$printfile);
				    
                                    echo "<a style=\"font-weight:bold; color:#62bd18;\" href=\"$dir/$file\" target=_blank>$print_filename</a><br />\n";

			    } else {
                                   if ($count==1){echo "<tr>";}
				   if ($list_dir_options['thumbnail_directory']){$display_image_dir=HTTP_ROOT . "/" . $list_dir_options['thumbnail_directory'];}else{$display_image_dir=HTTP_ROOT . "/" . $dir; }
				   if ($list_dir_options['thumbnail_width']){$tn_width=$list_dir_options['thumbnail_width'];} else {$tn_width=100;}
				   $path_and_file=$display_image_dir . "/" . $file;
				   if ($list_dir_options['thumbnail_directory'] && !file_exists($path_and_file)){$insert_text="<br /><br />THUMBNAIL COMING SOON<br /> - CLICK FOR HI-RES IMAGE<br /><br />";} else {$insert_text =     "<img src=\"$display_image_dir/$file\" width=\"$tn_width\" style=\"border-width:1px; border-color:#000000; border-style:solid;\">";}
				    if (!$list_dir_options['alternate_download_directory']){
					   echo "<td align=\"center\" valign=\"top\"><a href=\"$dir/$file\" target=_blank>$insert_text</a><br />$print_filename";
				    } else {
				           $alternate_download_file=$print_filename . "." . $list_dir_options['alternate_download_extension'];
					   echo "<td align=\"center\" valign=\"top\"><a href=\"".$list_dir_options['alternate_download_directory']."/$alternate_download_file\" target=_blank>$insert_text</a><br />$print_filename";

				    }
				   if ($list_dir_options['include_delete']==1){
					echo "<br /><a href=\"Javascript:deletePhoto('$file','".$_REQUEST['d']."','".$_REQUEST['display_options']."','".$_REQUEST['options_position']."')\"><b><font size='2' color='red'><img src=\"".SYSIMGPATH."/application_images/trash_icon.jpg\" border=0></font></b></a>";
				   }
				   echo "<br /></td>\n";
                                   if ($count==$image_cells_accross){echo "</tr>\n"; $count=0;}
                            }
                    }
                }
                closedir($dh);
            } else {
		print "Cannot read directory"; exit;
	    }
        } else {
		format_error("$dir is not a directory. Currently is " . getcwd() . " " . BASEPATH . " " . INSTALL_ROOT. ". program terminating.",1);
	}
        if ($count != 0){echo "</tr>";}
        echo "</table>";
        echo "</form>";
        return;
        close_col();
}

/*
 * Function is_empty_dir
*/
function is_empty_dir($dir){
    if (($files = @scandir($dir)) && count($files) <= 2) {
        return true;
    }
    return false;
}

/* 
 *Function getFilePermissions
*/
function getFilePermissions($file){
        $perms = fileperms($file);
        if (($perms & 0xC000) == 0xC000) {
           $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
           $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
           $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
           $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
           $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
           $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
           $info = 'p';
        } else {
           $info = 'u';
        }
        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                   (($perms & 0x0800) ? 's' : 'x' ) :
                   (($perms & 0x0800) ? 'S' : '-'));
        $info .= '-';
        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                   (($perms & 0x0400) ? 's' : 'x' ) :
                   (($perms & 0x0400) ? 'S' : '-'));
        $info .= '-';
        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                   (($perms & 0x0200) ? 't' : 'x' ) :
                   (($perms & 0x0200) ? 'T' : '-'));

        return $info;
}

/*
 * Function directory_browser
 * Meta: the directory browser interface
*/
function directory_browser(){
    	global $CONFIG;
	global $db;
	global $current_site;

	// request vars (change this so they are passed to the function?)
	$newdoc=$_REQUEST['newDoc']; // =1? Need to create a new document for "My Documents" section
	$showpath=$_REQUEST['showpath']; //=x? Dont show the directory path and replace with "My Documents"

	// include pixlr js in case we need to display the image edit links
	print "<script type=\"text/javascript\" src=\"scripts/pixlr.js\"></script>\n";
    ?>
	<script type="text/javascript">
	<!--
	function sendToPixlr(imgSrc,imgTitle){

	pixlr.edit({
		image: imgSrc,
		title: imgTitle,
		target:'<?=$CONFIG['http_path']?>/apps/pixlr/pixlr_save.php',
		service: 'editor',
		method: 'GET',
		exit:'<?=$CONFIG['http_path']?>/apps/pixlr/pixlr.php'
		});
	}
	//-->
	</script>
    <?php
	if (!$showpath){
		print "<p class=\"admin_header\">Filesystem Explorer</p>";
	} else {
		print "<div id=\"table_title_div\" class=\"table_title\ style=\"padding-bottom:0px; margin-bottom:0px\">Filesystem Explorer</div>";
		print "<div id=\"interface_buttons\">";
		$add_url=$_SERVER['PHP_SELF'];
		$add_url.= "?" . $_SERVER['QUERY_STRING'];
		$add_url .= "&amp;newDoc=1";
		print "<a href=\"".get_link($add_url,"",1)."\"><img src=\"".SYSIMGPATH."/application_images/button_add_beige_29x28.png\" alt=\"New Document\" title=\"New Document\" border=0></a></div>";
		if ($newdoc){
			$newdoc=$_REQUEST['dir']."/New Document.html";
			if (file_exists($newdoc)){
				print "<p class=\"dbf_para_alert\" style=\"width:80%; float:left; text-align:left\">A document entitled New Document.html already exists. Please rename this file before creating another new document.</p>";
			} else {
				if (file_put_contents($newdoc," ")){
					print "<p class=\"dbf_para_success\" style=\"width:80%; float:left; text-align:left\">Successfully created $newdoc</p>";
				} else {
					print "<p class=\"dbf_para_alert\" style=\"width:80%; float:left; text-align:left\">Cannot create new file - this directory is not writeable.</p>";
				}
			}
		}
	}

	//if (!$_GET['dir'] || $_GET['dir']=="./")
	$allowed_folders=array("files","images","documents","downloads");

	if (!$_GET['dir'] || $_GET['dir']=="./" || $_GET['dir']=="." || (!preg_match("/images/",$_GET['dir']) && !preg_match("/files/",$_GET['dir']) && !preg_match("/downloads/",$_GET['dir']) && !preg_match("/documents/",$_GET['dir']))){
		print "<p style=\"clear:both\"><b>Home Directory</b></p>";
		print "<a href=\"".$_SERVER['PHP_SELF']."?action=directory_browser&dir=./images\">./Images</a><br />";
		print "<a href=\"".$_SERVER['PHP_SELF']."?action=directory_browser&dir=./documents\">./Documents</a><br />";
		print "<a href=\"".$_SERVER['PHP_SELF']."?action=directory_browser&dir=./files\">./Files</a><br />";
		print "<p><a href=\"javascript:history.go(-1)\">Back</a></p>"; return;
	}
	
	if (!isset($_GET["dir"])) {

		$split = explode('/', $_SERVER["PHP_SELF"]);
		$dir = str_replace($split[count($split)-1], "", $_SERVER["PHP_SELF"]);
		$dir = getcwd(); // note that this one adds the whole server path, instead we use . as below
		$dir= ".";
	} else {
		$dir = $_GET["dir"];
	}

	if (substr($dir, -1,1) != '/') {
		$dir .= '/';
	}

	$opendir = "../" . $_GET['dir']; # MATT 2021 added ../ as we're now in /admin not root
	$workdir = opendir($opendir);

	if (!$workdir) {
	echo "Error 166518: Can not open directory of $dir.<p><a href=\"Javascript:history.go(-1)\">BACK</a>";
	print "(You are currently in " . getcwd() . ")";
	exit;
	}

	$dirArray = array();
	$fileArray = array();

	while (false !== ($file = readdir($workdir))) {
	$paf = "../" . $dir . $file; // paf = path and file; MATT 2021 ADDED ../ as we're not in /admin not root
	if (!@is_dir($paf)) {
		array_push($fileArray, $file);
	} else {
		array_push($dirArray, $file);
	}
	}

	if (isset($_GET["chmod"])) {
		$split = explode("/", $_GET["chdir"]);
		$file = $split[count($split)-1];
		$dir = str_replace($split[count($split)-1], "",  $_GET["chdir"]);

		chdir($dir);
		settype($_GET["chmod"], "int");        
		$fullfile=$dir.$file;
		$chmodto=$_GET['chmod'];
		print "chmod $dir"."$file, $chmodto";
		chmod($fullfile, $chmodto) or print " CANNOT CHMOD FILE - NO PERMISSIONS TO ALLOW ME TO DO THIS";
	}
	sort($dirArray);
	sort($fileArray);
?>

<script language="javascript">
    function setPerms(file) {
        x = prompt('Set file permissions for:' + "n" + file ,'0777');
        if (x != null) {
            document.location = '<?=$_SERVER["PHP_SELF"];?>?action=directory_browser&chdir=' + file + '&dir=<?=$dir;?>&chmod=' + x;
        }
    }
</script>
<style type="text/css">
    td { font-family: verdana; font-size: 12px; }
    a:link { font-family: verdana; font-size: 12px; text-decoration: none; color: #455266; }
    a:visited { font-family: verdana; font-size: 12px; text-decoration: none; color: #455266; }
    a:active { font-family: verdana; font-size: 12px; text-decoration: none; color: #455266; }
    a:hover { font-family: verdana; font-size: 12px; text-decoration: underline; color: #455266; }
</style>
<br clear="all" />
<table width="100%">
<tr style="background-color:#ddd">
    <td style="font-weight: bold; font-size: 13px; width:800px;">
        
<?php 
        $split = explode("/", substr($dir,1,strlen($dir)-2));
	if ($_REQUEST['showpath']=="x"){
		echo "<a href=\"#\" style=\"font-size:13px;\">My Documents</a><br /><span style=\"font-size:10px; font-weight:normal;\">Documents are saved by default in HTML format and can be converted to word by clicking the button.</span> ";
	} else {
		for ($i = 0; $i < count($split); $i++) {
		    if ($i !==0){ // if we want the full path from server root leave out this if and only use the line below
		    $path .= "/" . $split[$i];
		} else {
		    $path .= "." . $split[$i]; // not required if we are using the full server path, use the above only
		echo "<a href=\"" . $_SERVER["PHP_SELF"] . "?action=directory_browser\" style=\"font-size:13px;\">Home</a> ";

		}
		    echo "<a href=\"" . $_SERVER["PHP_SELF"] . "?action=directory_browser&dir=$path\" style=\"font-size:13px;\">" . $split[$i] . "</a> / ";
		}
	}	
		// display extra folder text
		$sql_path=str_replace("./","",$path);
		$sql_path .= $split[$i];
		$folder_info_sql="SELECT folder_text FROM folder_info WHERE folder = \"$sql_path\"";
		$folder_info_res=$db->query($folder_info_sql);
		$fi_h=$db->fetch_array($folder_info_res);
		if ($fi_h['folder_text']){
		print "<br><span class=\"dbf_folder_info\" style=\"margin-top:10px; padding-top:10px; display:block; font-size:9px; font-weight:normal\">" . $fi_h['folder_text'] . "</span>";
		}
	echo "</td><td nowrap=\"nowrap\">";
	echo "<div style=\"float:right; display:inline; margin-top:6px; clear:none;\" align=\"right\">" . upload_file_form(str_replace("./","",$dir));
	select_file_manager_macros($dir);
	echo "</div>";
?>
</td>
</tr>
<tr>
<td colspan=2><hr size="1"></td>
</tr>
</table>
<table>
<?php
    for ($i = 0; $i < count($dirArray); $i++) {
        $path = $dir . "" . $dirArray[$i];
        if ($dirArray[$i] == '.') {
            continue;
        }
        if ($dirArray[$i] == '..') {
            $cwd = substr($dir,0, strlen($dir)-1);
            $split = explode('/',$cwd);
            $dirPath = str_replace($split[count($split)-1], "", $cwd);
            $path = $dirPath;
        }

   ?> 
    <tr>
        <td><img src="<?php echo SYSIMGPATH;?>/icons/folder.png"></td>
        <td style="width:330px;font-weight: bold;"><?="<a href=\"" . $_SERVER["PHP_SELF"] . "?action=directory_browser&dir=" .$path . "\">" . $dirArray[$i] . "</a>";?></td>
	<td></td>
        <td><?= getFilePermissions($dir.$dirArray[$i]);?></td>
    </tr>
<?php
}
	// start looping through files
	for ($i = 0; $i < count($fileArray); $i++) {
		$last_modified = filemtime($dirArray[$i]);
		$last_modified = date("d/m/Y h:i", $last_modified);
		$graphic="page_white";
		$include_edit=1;
		if (preg_match("/.sql/",$fileArray[$i])){$graphic="page_white_database";}
		if (preg_match("/.doc/",$fileArray[$i])){$graphic="page_white_word";}
		if (preg_match("/.html/",$fileArray[$i])){$graphic="html"; $include_word_download=1;}
		if (preg_match("/.css/",$fileArray[$i])){$graphic="css";}
		if (preg_match("/.js/",$fileArray[$i])){$graphic="page_white_code";}
		if (preg_match("/.txt/",$fileArray[$i])){$graphic="page_white_text";}
		if (preg_match("/(.cgi|.pl)/",$fileArray[$i])){$graphic="page_white_code_red";}
		if (preg_match("/.php/",$fileArray[$i])){$graphic="page_white_php";}
		if (preg_match("/.png/i",$fileArray[$i])){$graphic="image"; $include_edit=1;}
		if (preg_match("/.jpg/i",$fileArray[$i])){$graphic="image"; $include_edit=1;}
		if (preg_match("/.gif/i",$fileArray[$i])){$graphic="image"; $include_edit=1;}
   ?> 
    <tr onMouseOver="this.bgColor='#cccccc';" onMouseOut="this.bgColor='#ffffff'">
	<td>
	<?php if ($graphic=="image" && count($fileArray)<300){
		$imgfile=preg_replace("/^\.\//","",$dir).$fileArray[$i];
		if (filesize($imgfile)<=500000){
			echo "<img src=\"".preg_replace("/^\.\//","",$dir).$fileArray[$i]."\" width=\"15\" height=\"15\" />";
		} else {
		 echo "<img src=\"".SYSIMGPATH."/icons/".$graphic.".png\">";
		}
	} else {
		 echo "<img src=\"".SYSIMGPATH."/icons/".$graphic.".png\">";
	}?>
        <td><a href="Javascript:void window.open('<?=$dir.$fileArray[$i];?>','previewWindow','width=500,height=450,scrollbars=1');"><?=$fileArray[$i];?></a></td>
        <td><?= $last_modified;?></td>
        <td><?= getFilePermissions($dir.$fileArray[$i]);?></td>
        <td><!--<a href="#" onclick="setPerms('<?=$dir.$fileArray[$i];?>');return false;">chmod</a> | //-->
	<?php if (!$_REQUEST['dbf_mui'] && !strlen(stristr($_SERVER['PHP_SELF'],"mui-administrator.php"))){?>

	<a title="Preview" href="Javascript:void window.open('<?=$dir.$fileArray[$i];?>','previewWindow','width=500,height=450');"><img src="<?php echo SYSIMGPATH;?>/icons/monitor.png" border="0"></a>  
	<a href="<?=$dir.$fileArray[$i];?>" class="mb" title="Preview in popup: <?=$dir.$fileArray[$i];?>"><img src="<?php echo SYSIMGPATH;?>/icons/monitor_go.png" border="0"></a>
	<?php }  else { ?>
		<a title="Preview" href="<?=get_link($dir.$fileArray[$i],"Image Preview","",1);?>"><img src="<?php echo SYSIMGPATH;?>/icons/monitor.png" border="0"></a>  
	<?php } 
	// edit link?	
	if ($include_edit){
		if ($graphic=="image"){
			$linkpath=str_replace("./","",$dir);
			$linkpath_for_title=str_replace("/","_-_",$linkpath);
			if (stristr($_SERVER['PHP_SELF'],"mui-administrator.php")){
				$pixlrLinkJs="parent.";
			} else { 
				$pixlrLinkJs="";
			}
			print " <a href=\"javascript:".$pixlrLinkJs."sendToPixlr('".$CONFIG['http_path']."/".$linkpath.$fileArray[$i]."','".$linkpath_for_title.$fileArray[$i]."')\"><img src=\"".SYSIMGPATH."/icons/pixlr16px.png\" border=\"0\" title=\"Edit with Pixlr\"></a>\n";
		} else {
			$link="administrator.php?action=fileEdit&file=" . $dir . $fileArray[$i];
			if (preg_match("/.html$/",$fileArray[$i]) && preg_match("/documents/",$dir)){
				$link .= "&mceInit=document";
			} else {
				$link .= "&mceInit=normal";
			}
			if ($showpath=="x"){ $link .= "&showpath=x"; }
			print "|  <a href=\"" . get_link($link) . "\"><img src=\"".SYSIMGPATH."/icons/page_white_edit.png\" border=\"0\" title=\"Edit\"></a>"; 
		}
	} 
	$deleteUrl="Javascript:file_delete('";
	$deleteUrl .= $_SERVER['PHP_SELF'];
	$deleteUrl .= "','".$dir.$fileArray[$i]."','".$dir."','".$showpath."')";
	?> 
	<?php if ($include_word_download){
	global $libpath;
	print " <a href=\"$libpath/library/modules/html_to_word.php?document=".$fileArray[$i]."\" title=\"Download Word Document\"><img src=\"".SYSIMGPATH."/icons/page_white_word.png\" border=\"0\" /></a>";
	}
?>
	<a href="Javascript:file_rename('<?php echo $_SERVER['PHP_SELF'];?>','<?php echo $fileArray[$i];?>','<?php echo $dir;?>','<?php echo $showpath;?>')" title="Rename" alt="Rename"><img src="<?php echo SYSIMGPATH;?>/icons/control_play.png" border="0"></a>
	<a href="<?php echo $deleteUrl;?>" title="Delete" alt="Delete"><img src="<?php echo SYSIMGPATH;?>/icons_companion/control_remove.png" border="0"></a>
	</td>
    </tr>
    <?php
    }
?>
</table>
<?php
} // end directory_browser

/*
 * Function file_edit
*/
function file_edit($file,$mceEditor){
	$filedata=file_get_contents($file);
	$filename_array=explode(".",$file);
	$filetype=array_pop($filename_array);
	$permissions=getFilePermissions($file);
	print "<p class=\"admin_header\">Edit File: $file</p>";
	if ($_POST['save_file']){
		file_put_contents($file,$_POST['fileEdit']) or format_error("This file cannot be written as you do not have the appropriate permissions",1);
		print "<p class=\"dbf_para_success\">File written successfully on " . date('l jS \of F Y h:i:s A') . "</p>";
		$filedata=$_POST['fileEdit'];
	}
	if (!is_writable($file)) {
		print "<p style=\"background-image:url(".SYSIMGPATH."/icons/exclamation.png); background-repeat:no-repeat; padding-left:20px; \" class=\"file_is_writable_text\">File is not writeable - this file may be viewed but due to the permissions set on the server, it cannot be saved.</span></p>";
	}	
?>

<?php 
if ($filetype=="html" || $mceEditor){ $editor_class="mceEditor"; } else {
?>
<script language="javascript" type="text/javascript">
editAreaLoader.init({
        id : "fileEdit"
        ,syntax: "<?=$filetype;?>"
        ,start_highlight: true
        ,font_size: 8
});
</script>
<?php
}
$save_filename=$_SERVER['PHP_SELF']."?action=fileEdit";
if ($_GET['mceInit']=="document"){
	$save_filename .= "&mceInit=document";
}
?>
<form action="<?=$save_filename?>" method="post" name="editFileForm">
<input type="hidden" name="file" value="<?=$file;?>">
<input type="hidden" name="save_file" value="1">
<textarea name="fileEdit" id="fileEdit" style="width:850px; height:450px;"><?=$filedata?></textarea>
<p>
<input type="image" src="<?php echo SYSIMGPATH;?>/application_images/save_beige_43x39.png" border="0"> 
</p>
</form>

<?php
}


/*
 * Function directory_browser_delete_file
*/
function directory_browser_delete_file($file){
	$file=str_replace("./","",$file);
	if (unlink($file)){
		print "<p class=\"dbf_para_info\">The file '$file' has been deleted.</p>";	
	} else {
		if (file_exists($file)){
			print "<p class=\"dbf_para_alert\">The file '$file' could not be deleted as you do not have permissions on the file to do this.</p>";	
		} else {
			print "<p class=\"dbf_para_alert\">The file '$file' could not be deleted as it does not exist.</p>";	
		}
	}
}

/*
 * Function select_file_manager_macros
*/
function select_file_manager_macros($directory){
	//return;
	global $db;
	$sql="SELECT id,name from file_manager_macros";
	$res=$db->query($sql);
	if ($db->num_rows($res)>=1){
		echo "<form name=\"run_batch_macro_form\" class=\"run_batch_macro_form\" action=\"administrator.php?action=run_batch_macro\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"directory\" value=\"$directory\">\n";
		echo "<b>Run Macro:</b> <select name=\"dbf_macro_id\">\n";
		echo "<option value=\"\">Please Select..</option>";
		while ($h=$db->fetch_array($res)){
			echo "<option value=\"".$h['id']."\">".$h['name']."</option>";
		}
		echo "</select><input type=\"submit\" value=\"Run\"></form>";
	}
}

/*
 * Function run_batch_macro
*/
function run_batch_macro($directory,$macro_id){
	global $db;
	print "<p class=\"admin_header\">Run batch macro job</p>";
	print "<p><b>Directory:</b> $directory</p>";
	$macro_name=$db->field_from_record_from_id("file_manager_macros",$macro_id,"name");
	print "<p><b>Macro:</b> $macro_name</p>";
	
	$files_in_dir=get_directory_list($directory);
	$directory=str_replace("./","",$directory);
	$directory=preg_replace("/\/$/","",$directory);
	foreach ($files_in_dir as $filename){
		$sql="SELECT * from file_manager_macro_actions WHERE macro_id = $macro_id ORDER BY action_order";
		$res=$db->query($sql);
		while ($row=$db->fetch_array($res)){
			//print "Calling " . $row['action'] . " on $filename in $directory with args of: " . $row['variables'] . "<br>";
			$macro_results=run_file_manager_macro_action($row['action'],$filename,$directory,$row['variables']);
			print $macro_results;
		}
	}
}

/*
 * Function get_directory_list
*/
function get_directory_list($dirname,$filetypes){
        $filetypes=explode(",",$filetypes);
        $directories=array();
        if ($h=opendir($dirname)){
                while (($file = readdir($h)) !== false){
                        if ($file != "." && $file != ".."){
                                        if ($filetypes){
                                                foreach ($filetypes as $filetype){
                                                        if (preg_match("/$filetype$/",$file)){
                                                                array_push($directories,$file);
                                                        }
                                                }
                                        } else {
                                                array_push($directories,$file);
                                        }
                        }
                }
        } else { print "Cannot open directory ($dirname)"; }
        closedir($h);
        sort($directories);
        return $directories;
}

/*
 * Function directory_browser_file_rename
 * Meta: Performs the file rename requested in the directory browser module
*/
function directory_browser_file_rename($dir,$old,$new){
	$file_to_rename=$dir . $old;
	$newfile =$dir . $new;
	if (rename($file_to_rename,$newfile)){
		print "<p class=\"dbf_para_success\">Renamed $file_to_rename to $newfile.</p>";
	} else {
		print "<p class=\"dbf_para_alert\">Could not rename $file_to_rename to $newfile. Invalid filename or permissions error.</p>";
	}
}

/* 
* Function image_selector
*/
function image_selector() {
    	global $CONFIG;
	global $db;
	global $current_site;

	// request vars (change this so they are passed to the function?)
	$newdoc=$_REQUEST['newDoc']; // =1? Need to create a new document for "My Documents" section
	$showpath=$_REQUEST['showpath']; //=x? Dont show the directory path and replace with "My Documents"
	$targetWindow=$_REQUEST['target_window'];
	$targetElement=$_REQUEST['target_element'];

	// include pixlr js in case we need to display the image edit links
	print "<script type=\"text/javascript\" src=\"scripts/pixlr.js\"></script>\n";
    ?>
<script type="text/javascript">
<!--
function sendToPixlr(imgSrc,imgTitle){

pixlr.edit({
        image: imgSrc,
        title: imgTitle,
        target:'<?=$CONFIG['http_path']?>/apps/pixlr/pixlr_save.php',
        service: 'editor',
        method: 'GET',
        exit:'<?=$CONFIG['http_path']?>/apps/pixlr/pixlr.php'
        });
}
//-->
</script>
    <?php
	if (!$showpath){
		print "<p class=\"admin_header\">Select Image</p>";
	} else {
		print "<div id=\"table_title_div\" class=\"table_title\ style=\"padding-bottom:0px; margin-bottom:0px\">Image Selector</div>";
		print "<div id=\"interface_buttons\">";
		$add_url=$_SERVER['PHP_SELF'];
		$add_url.= "?" . $_SERVER['QUERY_STRING'];
		$add_url .= "&amp;newDoc=1";
		print "<a href=\"".get_link($add_url,"",1)."\"><img src=\"".SYSIMGPATH."/application_images/button_add_beige_29x28.png\" alt=\"New Document\" title=\"New Document\" border=0></a></div>";
		if ($newdoc){
			$newdoc=$_REQUEST['dir']."/New Document.html";
			if (file_exists($newdoc)){
				print "<p class=\"dbf_para_alert\" style=\"width:80%; float:left; text-align:left\">A document entitled New Document.html already exists. Please rename this file before creating another new document.</p>";
			} else {
				if (file_put_contents($newdoc," ")){
					print "<p class=\"dbf_para_success\" style=\"width:80%; float:left; text-align:left\">Successfully created $newdoc</p>";
				} else {
					print "<p class=\"dbf_para_alert\" style=\"width:80%; float:left; text-align:left\">Cannot create new file - this directory is not writeable.</p>";
				}
			}
		}
	}
    if (!$_GET['dir']){
	print "<p>No directory specified or access to this directory is not allowed</p><p><a href=\"javascript:history.go(-1)\">Back</a></p>"; return;
	}
	
    if (!isset($_GET["dir"])) {

        $split = explode('/', $_SERVER["PHP_SELF"]);
        $dir = str_replace($split[count($split)-1], "", $_SERVER["PHP_SELF"]);
        $dir = getcwd(); // note that this one adds the whole server path, instead we use . as below
	$dir= ".";
    } else {
        $dir = $_GET["dir"];
    }

    if (substr($dir, -1,1) != '/') {
        $dir .= '/';
    }

    $workdir = opendir($dir);

    if (!$workdir) {
        echo "Can not open directory of $dir.<p><a href=\"Javascript:history.go(-1)\">BACK</a>";
        exit;
    }

    $dirArray = array();
    $fileArray = array();

    while (false !== ($file = readdir($workdir))) {
       if (!@is_dir($dir.$file)) {
            array_push($fileArray, $file);
       } else {
            array_push($dirArray, $file);
       }
   }

    sort($dirArray);
    sort($fileArray);
?>

<style type="text/css">
    td { font-family: verdana; font-size: 12px; }
    a:link { font-family: verdana; font-size: 12px; text-decoration: none; color: #455266; }
    a:visited { font-family: verdana; font-size: 12px; text-decoration: none; color: #455266; }
    a:active { font-family: verdana; font-size: 12px; text-decoration: none; color: #455266; }
    a:hover { font-family: verdana; font-size: 12px; text-decoration: underline; color: #455266; }
</style>
<br clear="all" />
<table width="100%">
<tr style="background-color:#ddd">
    <td style="font-weight: bold; font-size: 13px; width:800px;">
        
<?php 
        $split = explode("/", substr($dir,1,strlen($dir)-2));
		for ($i = 0; $i < count($split); $i++) {
		    if ($i !==0){ // if we want the full path from server root leave out this if and only use the line below
		    $path .= "/" . $split[$i];
		} else {
		    $path .= "." . $split[$i]; // not required if we are using the full server path, use the above only
		echo "<a href=\"Javascript: dbf_imagePicker_popup_open('$targetElement','action=image_selector&dir=$path')\" style=\"font-size:13px;\">Home</a> ";

		}
		    echo "<a href=\"Javascript: dbf_imagePicker_popup_open('$targetElement','action=image_selector&dir=$path')\" style=\"font-size:13px;\">" . $split[$i] . "</a> / ";
		}
		// display extra folder text
		$sql_path=str_replace("./","",$path);
		$sql_path .= $split[$i];
		$folder_info_sql="SELECT folder_text FROM folder_info WHERE folder = \"$sql_path\"";
		$folder_info_res=$db->query($folder_info_sql);
		$fi_h=$db->fetch_array($folder_info_res);
		if ($fi_h['folder_text']){
		print "<br><span class=\"dbf_folder_info\" style=\"margin-top:10px; padding-top:10px; display:block; font-size:9px; font-weight:normal\">" . $fi_h['folder_text'] . "</span>";
		}
	echo "</td><td nowrap=\"nowrap\">";
	echo "<div style=\"float:right; display:inline; margin-top:6px; clear:none;\" align=\"right\"><!-- file upload was here //--></div>";
?>
</td>
</tr>
<tr>
<td colspan=2><hr size="1"></td>
</tr>
</table>
<table>
<?php
    for ($i = 0; $i < count($dirArray); $i++) {
        $path = $dir . "" . $dirArray[$i];
        if ($dirArray[$i] == '.') {
            continue;
        }
        if ($dirArray[$i] == '..') {
            $cwd = substr($dir,0, strlen($dir)-1);
            $split = explode('/',$cwd);
            $dirPath = str_replace($split[count($split)-1], "", $cwd);
            $path = $dirPath;
        }

   ?> 
    <tr>
        <td><img src="<?php echo SYSIMGPATH;?>/icons/folder.png"></td>
        <td style="width:330px;font-weight: bold;"><a href="Javascript: dbf_imagePicker_popup_open('<?php echo $targetElement;?>','<?php echo $path;?>')"><?php echo $dirArray[$i];?></a></td>
    </tr>
<?php
}
	// start looping through files
	for ($i = 0; $i < count($fileArray); $i++) {
		$last_modified = filemtime($dirArray[$i]);
		$last_modified = date("d/m/Y h:i", $last_modified);
		$graphic="page_white";
		$include_edit=1;
		if (preg_match("/.sql/",$fileArray[$i])){$graphic="page_white_database";}
		if (preg_match("/.doc/",$fileArray[$i])){$graphic="page_white_word";}
		if (preg_match("/.html/",$fileArray[$i])){$graphic="html"; $include_word_download=1;}
		if (preg_match("/.css/",$fileArray[$i])){$graphic="css";}
		if (preg_match("/.js/",$fileArray[$i])){$graphic="page_white_code";}
		if (preg_match("/.txt/",$fileArray[$i])){$graphic="page_white_text";}
		if (preg_match("/(.cgi|.pl)/",$fileArray[$i])){$graphic="page_white_code_red";}
		if (preg_match("/.php/",$fileArray[$i])){$graphic="page_white_php";}
		if (preg_match("/.png/i",$fileArray[$i])){$graphic="image"; $include_edit=1;}
		if (preg_match("/.jpg/i",$fileArray[$i])){$graphic="image"; $include_edit=1;}
		if (preg_match("/.gif/i",$fileArray[$i])){$graphic="image"; $include_edit=1;}
   ?> 
    <tr onMouseOver="this.bgColor='#cccccc';" onMouseOut="this.bgColor='#ffffff'">
	<td>
	<?php if ($graphic=="image" && count($fileArray)<300){
		$imgfile=preg_replace("/^\.\//","",$dir).$fileArray[$i];
		if (filesize($imgfile)<=500000){
			echo "<img src=\"".preg_replace("/^\.\//","",$dir).$fileArray[$i]."\" width=\"15\" height=\"15\" />";
		} else {
		 echo "<img src=\"".SYSIMGPATH."/icons/".$graphic.".png\">";
		}
	} else {
		 echo "<img src=\"".SYSIMGPATH."/icons/".$graphic.".png\">";
	}?>
        <td><a href="Javascript:void window.open('<?=$dir.$fileArray[$i];?>','previewWindow','width=500,height=450,scrollbars=1');"><?=$fileArray[$i];?></a></td>
        <td><!--<a href="#" onclick="setPerms('<?=$dir.$fileArray[$i];?>');return false;">chmod</a> | //-->
	<?php if (!$_REQUEST['dbf_mui'] && !strlen(stristr($_SERVER['PHP_SELF'],"mui-administrator.php"))){?>

	<a title="Preview" href="Javascript:void window.open('<?=$dir.$fileArray[$i];?>','previewWindow','width=500,height=450');"><img src="<?php echo SYSIMGPATH;?>/icons/monitor.png" border="0"></a>  
	<a href="<?=$dir.$fileArray[$i];?>" class="mb" title="Preview in popup: <?=$dir.$fileArray[$i];?>"><img src="<?php echo SYSIMGPATH;?>/icons/monitor_go.png" border="0"></a>
	<?php }  else { ?>
		<a title="Preview" href="<?=get_link($dir.$fileArray[$i],"Image Preview","",1);?>"><img src="<?php echo SYSIMGPATH;?>/icons/monitor.png" border="0"></a>  
	<?php } 
	// edit link?	
	if ($include_edit){
		if ($graphic=="image"){
			$linkpath=str_replace("./","",$dir);
			$linkpath_for_title=str_replace("/","_-_",$linkpath);
			if (stristr($_SERVER['PHP_SELF'],"mui-administrator.php")){
				$pixlrLinkJs="parent.";
			} else { 
				$pixlrLinkJs="";
			}
			print " <a href=\"javascript:".$pixlrLinkJs."sendToPixlr('".$CONFIG['http_path']."/".$linkpath.$fileArray[$i]."','".$linkpath_for_title.$fileArray[$i]."')\"><img src=\"".SYSIMGPATH."/icons/image_edit.png\" border=\"0\" title=\"Edit with Pixlr\"></a>\n";
		} else {
			$link="administrator.php?action=fileEdit&file=" . $dir . $fileArray[$i];
			if (preg_match("/.html$/",$fileArray[$i]) && preg_match("/documents/",$dir)){
				$link .= "&mceInit=document";
			} else {
				$link .= "&mceInit=normal";
			}
			if ($showpath=="x"){ $link .= "&showpath=x"; }
			print "|  <a href=\"" . get_link($link) . "\"><img src=\"".SYSIMGPATH."/icons/page_white_edit.png\" border=\"0\" title=\"Edit\"></a>"; 
		}
	} 
	$deleteUrl="Javascript:file_delete('";
	$deleteUrl .= $_SERVER['PHP_SELF'];
	$deleteUrl .= "','".$dir.$fileArray[$i]."','".$dir."','".$showpath."')";
	?> 
	<?php if ($include_word_download){
	global $libpath;
	print " <a href=\"$libpath/library/modules/html_to_word.php?document=".$fileArray[$i]."\" title=\"Download Word Document\"><img src=\"".SYSIMGPATH."/icons/page_white_word.png\" border=\"0\" /></a>";
	}
?>
	<a href="Javascript:file_rename('<?php echo $_SERVER['PHP_SELF'];?>','<?php echo $fileArray[$i];?>','<?php echo $dir;?>','<?php echo $showpath;?>')" title="Rename" alt="Rename"><img src="<?php echo SYSIMGPATH;?>/icons/control_play.png" border="0"></a>
	<a href="<?php echo $deleteUrl;?>" title="Delete" alt="Delete"><img src="<?php echo SYSIMGPATH;?>/icons_companion/control_remove.png" border="0"></a> 
	<?php // THIS WAS MUI ONLY $buildLink = "'" . $dir . $fileArray[$i] . "','" . $targetWindow . "','" . $targetElement . "'"; ?>
	<!--<a href="Javascript:parent.pickImage(<?php echo $buildLink; ?>)">SELECT IMAGE</a>//-->
	<?php
	$fieldname=$targetElement . "_image_preview";
	print "<a href=\"Javascript: document.forms['update_table'].elements['".$targetElement."'].value='".$dir . $fileArray[$i] . "'; document.getElementById('dbf_imagePicker').style.display='none'; updateImagePickerPreview('".$dir.$fileArray[$i]."'); \">SELECT IMAGE</a>";
	?>
	</td>
    </tr>
    <?php
    }
?>
</table>
<?php
}
?>
