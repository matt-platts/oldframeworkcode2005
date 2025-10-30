<?php

/* 
 * File: web_site_manager.php
 * Meta: Functions related to the web_sites and web_site_data tables, also xml sitemaps and .htaccess
*/

/*
 * Function load_web_site_vars
 * Param $site_id (int) - id of record in web_sites that we are loading for all configuration values
 */
function load_web_site_vars($site_id){
	global $db;
	$return_all_cols=array();
	$site_sql="SELECT * from web_site_data WHERE web_site_id=$site_id";
	$site_result=$db->query($site_sql);
	while ($site_row = $db->fetch_array($site_result)){
		$param_array[$site_row['param']]=$site_row['value'];
		$return_all_cols[$site_row['param']]=$site_row['value'];
	}
	return $return_all_cols;
}

/*
 * Function web_manager_front
*/
function web_manager_front(){

	global $db;
	open_col2();
	print "<ul> <li><a href=\"" . $_SERVER['PHP_SELF'] . "?action=new_web_site\">Create New Web Site</a></li>";
	print "<li>Edit web sites: <ul>";
	$list_sites_sql="SELECT * from web_sites";
	$list_sites_result=$db->query($list_sites_sql);
	while ($list_sites_rows=$db->fetch_array($list_sites_result)){
		print "<li><a href=\"" . $_SERVER['PHP_SELF'] . "?action=edit_web_site&site=" . $list_sites_rows['id'] . "\">" . $list_sites_rows['name'] . "</a></li>";
	}
	print "</ul>";
	print "</ul>";
	?>
	<?php

}

/*
 * Function new_web_site
 * Meta: how old?????!!
*/
function new_web_site(){
	open_col2();
	print "<b>Create New Web Site</b><p>";
	?>
	<form method="post" action="administrator.php?action=create_new_web_site">
	<table class="formtable">
	<tr><td>Please enter the ID for your master template:i<br><font size=1>This is the default page that loads as the main page</font></td><td><input type="text" size=4 name="master_template_id"></td></tr>
	<tr><td>Does this page have a DBForms menu?<br><font size=1>If so please enter the menu ID:</font></td><td><input type="text" size=4 name="master_menu_id"></td></tr>
	<tr><td></td><td><input type="submit" value="Create Web Site"></td></tr>
	</table>
	</form>
	<?php
	close_col();
}

/*
 * Funciton write_category_page_rewrites
*/
function write_category_page_rewrites(){

	global $db;
	print "<p class=\"admin_header\">Activate HTML page names for categories</p>";
	print "<p>This function sets the web page names from the categories section live, turning the html_page_name field from the categories table into a dynamic url. This url will also take into account the allocated template and web site section.</p>";
	$top_level_page="253";
	$mid_level_page="253";
	$bottom_level_page="88";
	$rewrite_data=array();

	$cats=build_category_data(1);
	$new_cats=array();
	foreach ($cats as $cat){
		$cid=$cat['id'];
		foreach ($cats as $cat2){
			if ($cat2['parent']== $cid){
				$cat['has_children']=1;
			}
		}
		if (!$cat['has_children']){
			$cat['has_children']=0;
		}
		array_push($new_cats,$cat);
	}

	$newcats2=array();
	foreach ($new_cats as $newcat){
		$master_category_id=0;
		$newcat2=get_master_category($newcat,$new_cats);
		array_push($newcats2,$newcat2);
	}

	// print rewrites
	foreach ($newcats2 as $rule){
		if ($rule['html_page_name']){
		$write="rewriteRule ^" . $rule['html_page_name'] . " site.php?action=cart_categories_browse&category_id=" . $rule['id'] . "&content=";
		if ($rule['has_children']){
			$write_level=$top_level_page;
		} else{
			$write_level=$bottom_level_page;

		}
		if (!$rule['has_children'] && !$rule['master_category_id']){
			$write_level = $top_level_page;
		}
		$write .= $write_level;
		if ($rule['master_category_id']){
			$write.="&master_category_id=".$rule['master_category_id'];
		}
		//print $write;
		array_push ($rewrite_data,$write);
		}
	}
		


		$rewrite_text = join("\n",$rewrite_data);

		$htaccess_in = fopen(".htaccess","r") or die ("Cannot read htaccess file. Maybe it does not exist? Please create this manually first of all.");
		$data=fread($htaccess_in,filesize(".htaccess"));
		fclose($htaccess_in);

		$data=preg_split("/\n/",$data);
		$write_back=array();
		$stop_writeback=0;
		foreach ($data as $dataline){
			$dataline = preg_replace("/\n/","",$dataline);
			if (preg_match("/# write product categories/",$dataline)){
				$stop_writeback=1;
				array_push($write_back, $dataline);
			}
			if (preg_match("/# end write product categories/",$dataline)){
				$stop_writeback=0;
				array_push($write_back, $rewrite_text);
			}

			if (!$stop_writeback){
				array_push($write_back, $dataline);
			}
		}

		$full_rewrite_text = join("\n",$write_back);

		$htaccess_out = fopen(".htaccess","w") or $nowrite_to_htaccess=1;
		fwrite($htaccess_out,$full_rewrite_text);
		fclose($htaccess_out);
		print "<pre>";
		print $full_rewrite_text;
		print "</pre>";

		if ($nowrite_to_htaccess){
			print "<b>Cannot write to .htaccess file (no permissions set to enable me to do this).<br />The following data should be manually pasted into a .htaccess file and placed in the root directory of the CMS installation:</b><p>";
			print "RewriteEngine on<br />";
			foreach ($rewrite_data as $rewriterule){
				print $rewriterule . "<br />";
			}
		} else {

			print "All page names for general content are now live on the web site.";
		}

}

/*
 * Function generate_htaccess_html_page_rewites
*/
function generate_htaccess_html_page_rewrites(){

	global $db;
	print "<div style=\"padding-left:15px\">";
	print "<p class=\"admin_header\">Activate HTML Page Names</p>";
	print "<p>This function sets the web page names from the content section live, turning the html_page_name field from the categories table into a dynamic url. This url will also take into account the allocated template and web site section.</p>";
	print "<p>Please use this function with caution, as mistakes in the html page name can cause dead links or otherwise break your web site.</p>";
	
	$sql = "SELECT count(*) as sitecount from web_sites";
	$res=$db->query($sql);
	while ($h=$db->fetch_array($res)){
		$sitecount=$h['sitecount'];
	}

	if ($sitecount >=1 && !$_POST['rw_cont']){
			print "<form name=\"rwform\" method=\"post\" action=\"".$_SERVER['PHP_SELF']."?action=generate_htaccess_html_page_rewrites\">";
			print "<input type=\"hidden\" name=\"rw_cont\" value=\"1\" />";
			print "<input type=\"submit\" value=\"Click here to proceed.\"></form>";
			return;
	}
	
        $sql = "SELECT id,html_page_name,default_template,assign_to_web_site from content WHERE (html_page_name IS NOT NULL AND (do_not_rewrite_name_to_server =0 OR do_not_rewrite_name_to_server IS NULL)) ORDER BY id";
        $result = $db->query($sql) or die($db->db_error());
        $rewrite_data=array();
        while ($h=$db->fetch_array($result)){
                $urlmatch = "content=".$h['id'];
                $html_page_name=$h['html_page_name'];
		$website=$h['assign_to_web_site'];
		if ($website){ $website="s=".$website."&";} else { $website=""; }
                if ($h['default_template']){$template="&mt=".$h['default_template'];}else{$template="";}
		if (strlen($h['html_page_name'])>3){
			$newline = "RewriteRule ^$html_page_name site.php?$website$urlmatch$template";
			array_push($rewrite_data,$newline);
		}
        }

	if ($db->num_rows($result)==0){
		print "No URLS in content to rewrite."; exit;
	}
        $rewrite_text = join("\n",$rewrite_data);

        $htaccess_in = fopen(".htaccess","r") or die ("Cannot read htaccess file. Maybe it does not exist? Please create this manually first of all.");
        $data=fread($htaccess_in,filesize(".htaccess"));
        fclose($htaccess_in);

        $data=preg_split("/\n/",$data);
        $write_back=array();
        $stop_writeback=0;
        foreach ($data as $dataline){
                $dataline = preg_replace("/\n/","",$dataline);
                if (preg_match("/# start mod_rewrite for content/",$dataline)){
                        $stop_writeback=1;
                        array_push($write_back, $dataline);
                        array_push($write_back, "RewriteEngine on");
                }
                if (preg_match("/# end mod_rewrite for content/",$dataline)){
                        $stop_writeback=0;
                        array_push($write_back, $rewrite_text);
                }

                if (!$stop_writeback){
                        array_push($write_back, $dataline);
                }
        }

        $full_rewrite_text = join("\n",$write_back);

        $htaccess_out = fopen(".htacces","w") or $nowrite_to_htaccess=1;
        fwrite($htaccess_out,$full_rewrite_text);
        fclose($htaccess_out);

        if ($nowrite_to_htaccess){
                print "<p class=\"dbf_para_alert\">Cannot write to apache configuration files from the web portal (no permissions set to enable me to do this).</b><p>";
		global $user;
		if ($user->value("type")=="master"){
			print "RewriteEngine on<br />";
			foreach ($rewrite_data as $rewriterule){
				print $rewriterule . "<br />";
			}
		}
        } else {

                print "<p class=\"dbf_para_success\">All page names for general content are now live on the web site.</a>";
		print "<textarea rows=\"20\" cols=\"120\">".$full_rewrite_text."</textarea>";
        }
	print "</div>";
}

/*
 * Funciton generate_xml_sitemap
*/
function generate_xml_sitemap(){
        print "<h1 class=\"admin_header\">XML Site Map</h1>";
        print "<p>This site map can be submitted to google as a google sitemap or submitted to other search engines.<br />It is in standard sitemap protocol format.</p>";
	print "<b>Note:</b> Any html page names entered into the content table have been used in this site map, so please ensure that htaccess rewrite is set up.</p><br />";
        $sql = "SELECT * from content";
	global $db;
        $result = $db->query($sql);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        $xml .= "\n";
        $current_date = date("Y-m-j");

        while ($h=$db->fetch_array($result)){
                $location="";
                if ($h['html_page_name']){$location .= $h['html_page_name'];
                } else {
                        $location="site.php?s=1&content=".$h['id'];
                        if ($h['default_template']){
                                $location .= "&mt=".$h['default_template'];
                        }
                }

                $xml .= "<url>\n";
                $xml .= "<loc>" . $location . "</loc>\n";
                $xml .= "<lastmod>" . $current_date . "</lastmod>\n";
                $xml .= "<changefreq>monthly</changefreq>\n";
                $xml .= "<priority>0.5</priority>\n";
                $xml .= "</url>\n";
        }
        $xml .= "</urlset>";
        print "<textarea rows=\"15\" cols=\"90\">";
        print $xml;
        print "</textarea>";
}

// used for category rewrites
function build_category_data($incoming_menu_id){
        global $user;
        global $db;
        $usertype = $user->value("type");
        $user_hierarchial_order=$user->value("hierarchial_order");
        $sql = "SELECT product_categories.* FROM product_categories WHERE active=1 ORDER BY parent,category_name";
        $all_rows=array();
        $result=$db->query($sql) or die("Error " . $db->db_error());

        while ( $row=$db->fetch_array($result)){
                array_push($all_rows,$row);
        }
        return $all_rows;
}

// used for category rewriters
function get_master_category($cat,$cats){
        if ($dbug){print "On get master for " . $cat['id'] . "<br />";}
        $this_paarent_id=0;
        global $master_category_id;
        if(!$cat['parent']){
                $cat['master_category_id']=0;
                if ($master_category_id){
                        $cat['master_category_id']=$master_category_id;
                }
                if ($dbug){print " - returning master id of $master_category_id <br>";}
                return $cat; // no master
        }
        if($cat['parent']){
                $this_parent_id=$cat['parent'];
                $master_category_id=$cat['parent'];
                if ($dbug){print "- Set master id to $master_category_id";}
                foreach ($cats as $pcat){
                        if ($pcat['id']==$this_parent_id){
                                $xcat=get_master_category($pcat,$cats);
                        }
                }
                $cat['master_category_id']=$xcat['master_category_id'];
                return $cat;
        }
}

?>
