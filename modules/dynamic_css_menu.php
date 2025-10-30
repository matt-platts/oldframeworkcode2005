<?php

/////////////////////////////////////////////////////////////////////////////////////
// build menu from table - takes data from the menu_items table, relates it by the //
// parent_id key and builds a menu usin ul and li html tags. This can by styles    //
// later on, for example to make a dynamic menu                                    //
/////////////////////////////////////////////////////////////////////////////////////
function build_menu_from_table($incoming_menu_id){
	global $user;
	global $db;
	$usertype = $user->value("type");
	if ($usertype == "administrator"){
		$sql = 'SELECT * from menu_items where menu_id='.$incoming_menu_id.' AND ((restricted_to != "superadmin" AND restricted_to != "master") OR restricted_to IS NULL) order by parent_id, ordering';
	} else if ($usertype == "superadmin") {
		$sql = 'SELECT * from menu_items where menu_id='.$incoming_menu_id.' AND ((restricted_to != "master") OR restricted_to IS NULL) order by parent_id, ordering';
	} else {
		$sql="SELECT * from menu_items where menu_id=$incoming_menu_id order by parent_id, ordering";
	}
	$all_rows=array();
	$result=$db->query($sql) or die("Error " . mysql_error());
	$count_after=0;

	while ( $row=$db->fetch_array($result)){
		$row_var="row" . $row['parent_id'];
		if ($row['url']=="administrator.php?action=sysListTables"){
			//$add_tables_to_parent=$row['id'];
			$count_after++;
		}
		array_push($all_rows,$row);
		$count_from=$row['id']+1;
		if ($count_after){$count_after++;}
	}

	if ($add_tables_to_parent){ 
		$count_from = $count_from + $count_after;
		$array_of_tables = generate_table_list($incoming_menu_id,$add_tables_to_parent, $count_from); 
		$full_array = array_merge($all_rows,$array_of_tables);
		$all_rows=$full_array;
	}
	
	$menu_html = "<div id=\"menu\">\n\n<ul class=\"menu_level1\">\n";
	$printed_ids=array(); # array of all ids that have been printed

	#
	# Start looping through everything
	#
	
	// get the last item id for level 0
	$maxsql = "select max(ordering) as highest_order from menu_items where menu_id=$incoming_menu_id AND parent_id=0";
	$maxresult=$db->query($maxsql) or die ("error " . mysql_error());
	$highest="";
	while ($maxrow = $db->fetch_array($maxresult)){
		$highest=$maxrow['highest_order'];
	}

	$maxid = "select id from menu_items where menu_id=$incoming_menu_id AND parent_id =0 AND ordering = " . $highest;
	$maxresult=$db->query($maxid) or die ("error " . mysql_error());
	$last_id="";
	while ($maxrow = $db->fetch_array($maxresult)){
		$last_id=$maxrow['id'];
	}

	foreach ($all_rows as $eachrow => $eachrow_array){
		
		$already_printed=0;
		foreach ($printed_ids as $row_printed){
			if ($row_printed == $eachrow_array['id']){$already_printed=1; continue 2;}
		}
		if ($eachrow_array['item_text'] == "sysListTables"){continue;}
		// the last menu item in the first level gets a different class in case its bunped up against something and needs it
		if ($eachrow_array['id']==$last_id){
			$menu_html .= "\n<li class=\"listitem_level1_last\">";
		} else {	
			$menu_html .= "\n<li class=\"listitem_level1\">";
		}
		$menu_html .= "<a href=\"" . get_link($eachrow_array['url']) . "\">".$eachrow_array['item_text']."</a>";
		array_push($printed_ids,$eachrow_array['id']);

		#
		# Looping through second set now
		#
		foreach ($all_rows as $eachrow2 => $eachrow_array2){
		$already_printed2=0;
		foreach ($printed_ids as $row_printed){
			if ($row_printed == $eachrow_array2['id']){$already_printed2=1; continue 2;}
		}
			if ($eachrow_array2['parent_id']==$eachrow_array['id']){
				$already_printed2=0;
				if (!$second_ul_printed){$menu_html .= "\n<ul class=\"menu_level2\">\n"; $second_ul_printed=1;}
				$menu_html .= "\n<li class=\"listitem_level2\">";
				$menu_html .= "<a href=\"" . get_link($eachrow_array2['url']) . "\"";
				if (get_link($eachrow_array2['url'])=="#" && $eachrow_array2['item_text']=="New Window"){
					$menu_html .= " id=\"create_new_window\""; 
				}
				if (get_link($eachrow_array2['url'])=="#" && $eachrow_array2['item_text']=="Window From Page"){
					$menu_html .= " id=\"window_from_page\""; 
				}
				$menu_html .= ">".$eachrow_array2['item_text']."</a>";
				array_push($printed_ids,$eachrow_array2['id']);
				
				#
				# third loop
				#
				foreach ($all_rows as $eachrow3 => $eachrow_array3){
					$already_printed3=0;
					if ($eachrow_array3['item_text']=="sysListTables"){$already_printed3=1;continue(1);}	
					foreach ($printed_ids as $row_printed){
						if ($row_printed == $eachrow_array3['id']){$already_printed3=1; continue(1);}
					}
					if ($eachrow_array3['parent_id']==$eachrow_array2['id']){
						if (!$third_ul_printed){$menu_html .= "<ul class=\"menu_level3\">\n"; $third_ul_printed=1;}
						if (!$already_printed3){
							$menu_html .= "<li class=\"listitem_level3\"><a href=\"" . get_link($eachrow_array3['url']) . "\">".$eachrow_array3['item_text']."</a>";
							array_push($printed_ids,$eachrow_array3['id']);
						}


					#
					# fourth loop
					# 
					foreach ($all_rows as $eachrow4 => $eachrow_array4){
						$already_printed4=0;
						if ($eachrow_array4['item_text']=="sysListTables"){$already_printed4=1;continue(1);}
						foreach ($printed_ids as $row_printed){
							if ($row_printed == $eachrow_array4['id']){$already_printed4=1; continue 1;}
						}
						if ($eachrow_array4['parent_id']==$eachrow_array3['id']){
							if (!$fourth_ul_printed){$menu_html .= "<ul class=\"menu_level4\">\n"; $fourth_ul_printed=1;}
							if (!$already_printed4){
								$menu_html .= "<li class=\"listitem_level4\"><a href=\"" . get_link($eachrow_array4['url']) . "\">".$eachrow_array4['item_text']."</a>";
							array_push($printed_ids,$eachrow_array4['id']);	
							}

							#
							# fifth loop
							#
							foreach ($all_rows as $eachrow5 => $eachrow_array5){
								$already_printed5=0;
								if ($eachrow_array5['item_text']=="sysListTables"){$already_printed5=1;continue(1);}
								foreach ($printed_ids as $row_printed){
									if ($row_printed == $eachrow_array5['id']){$already_printed5=1; continue 1;}
								}
								if ($eachrow_array5['parent_id']==$eachrow_array4['id']){
									if (!$fifth_ul_printed){$menu_html .= "<ul class=\"menu_level5\">"; $fifth_ul_printed=1;}
								if (!$already_printed5){
										$menu_html .= "<li class=\"listitem_level5\"><a href=\"" . get_link($eachrow_array5['url']) . "\">".$eachrow_array5['item_text']."</a>";
										array_push($printed_ids,$eachrow_array5['id']);
									}
									$menu_html .= "</li>\n"; # closes 5th level li	
								}
							}# close foreach 5
							if ($fifth_ul_printed){
								$menu_html .= "</ul>\n"; $fifth_ul_printed=0;
							}

							$menu_html .= "</li>"; # closes 4th level li
						}
					}# close foreach 4
					if ($fourth_ul_printed){
						$menu_html .= "</ul>\n"; $fourth_ul_printed=0;
					}
					$menu_html .= "</li>\n"; # closes 3rd level li
					}# close foreach 3 
				}
				if ($third_ul_printed){
					$menu_html .= "</ul>\n"; $third_ul_printed=0;
				}
			}
		} # close foreach 2
		if ($second_ul_printed){$menu_html .= "</ul>\n"; $second_ul_printed=0;}
	}
	// the last li with a parent of 0 needs a different class..
	$menu_html .= "</ul>\n";
	$menu_html .= "</div>\n";
	$menu_html=str_replace("current_user()",$user->value('id'),$menu_html);
	return $menu_html;
}

function build_desktop_menu_from_table($incoming_menu_id){
	global $user;
	global $db;
	$usertype = $user->value("type");
	if ($usertype == "administrator"){
		$sql = 'SELECT * from menu_items where menu_id='.$incoming_menu_id.' AND ((restricted_to != "superadmin" AND restricted_to != "master") OR restricted_to IS NULL) order by parent_id, ordering';
	} else if ($usertype == "superadmin") {
		$sql = 'SELECT * from menu_items where menu_id='.$incoming_menu_id.' AND ((restricted_to != "master") OR restricted_to IS NULL) order by parent_id, ordering';
	} else {
		$sql="SELECT * from menu_items where menu_id=$incoming_menu_id order by parent_id, ordering";
	}
	$all_rows=array();
	$result=$db->query($sql) or die("Error " . mysql_error());
	$count_after=0;

	while ( $row=$db->fetch_array($result)){
		$row_var="row" . $row['parent_id'];
		if ($row['url']=="administrator.php?action=sysListTables"){
			//$add_tables_to_parent=$row['id'];
			$count_after++;
		}
		array_push($all_rows,$row);
		$count_from=$row['id']+1;
		if ($count_after){$count_after++;}
	}

	if ($add_tables_to_parent){
		$count_from = $count_from + $count_after;
		$array_of_tables = generate_table_list($incoming_menu_id,$add_tables_to_parent, $count_from); 
		$full_array = array_merge($all_rows,$array_of_tables);
		$all_rows=$full_array;
	}
	
	$menu_html = "<div id=\"menu\">\n\n<ul class=\"menu_level1\">\n";
	$printed_ids=array(); # array of all ids that have been printed

	#
	# Start looping through everything
	#
	
	// get the last item id for level 0
	$maxsql = "select max(ordering) as highest_order from menu_items where menu_id=$incoming_menu_id AND parent_id=0";
	$maxresult=$db->query($maxsql) or die ("error " . mysql_error());
	$highest="";
	while ($maxrow = $db->fetch_array($maxresult)){
		$highest=$maxrow['highest_order'];
	}

	$maxid = "select id from menu_items where menu_id=$incoming_menu_id AND parent_id =0 AND ordering = " . $highest;
	$maxresult=$db->query($maxid) or die ("error " . mysql_error());
	$last_id="";
	while ($maxrow = $db->fetch_array($maxresult)){
		$last_id=$maxrow['id'];
	}

	foreach ($all_rows as $eachrow => $eachrow_array){
		
		$already_printed=0;
		foreach ($printed_ids as $row_printed){
			if ($row_printed == $eachrow_array['id']){$already_printed=1; continue 2;}
		}
		if ($eachrow_array['item_text'] == "sysListTables"){continue;}
		// the last menu item in the first level gets a different class in case its bunped up against something and needs it
		if ($eachrow_array['id']==$last_id){
			$menu_html .= "\n<li class=\"listitem_level1_last\">";
		} else {	
			$menu_html .= "\n<li class=\"listitem_level1\">";
		}
		$menu_html .= "<a class=\"dynMenuItem\" id=\"dbfMenuItem-".$eachrow_array['id']."\" href=\"" . get_desktop_link($eachrow_array['url']) . "\">".$eachrow_array['item_text']."</a>";
		array_push($printed_ids,$eachrow_array['id']);

		#
		# Looping through second set now
		#
		foreach ($all_rows as $eachrow2 => $eachrow_array2){
		$already_printed2=0;
		foreach ($printed_ids as $row_printed){
			if ($row_printed == $eachrow_array2['id']){$already_printed2=1; continue 2;}
		}
			if ($eachrow_array2['parent_id']==$eachrow_array['id']){
				$already_printed2=0;
				if (!$second_ul_printed){$menu_html .= "\n<ul class=\"menu_level2\">\n"; $second_ul_printed=1;}
				$menu_html .= "\n<li class=\"listitem_level2\">";
				$menu_html .= "<a class=\"dynMenuItem\" id=\"dbfMenuItem-".$eachrow_array2['id']."\" href=\"" . get_desktop_link($eachrow_array2['url']) . "\"";
				if (get_desktop_link($eachrow_array2['url'])=="#" && $eachrow_array2['item_text']=="New Window"){
					$menu_html .= " id=\"create_new_window\""; 
				}
				if (get_desktop_link($eachrow_array2['url'])=="#" && $eachrow_array2['item_text']=="Window From Page"){
					$menu_html .= " id=\"window_from_page\""; 
				}
				$menu_html .= ">".$eachrow_array2['item_text']."</a>";
				array_push($printed_ids,$eachrow_array2['id']);
				
				#
				# third loop
				#
				foreach ($all_rows as $eachrow3 => $eachrow_array3){
					$already_printed3=0;
					if ($eachrow_array3['item_text']=="sysListTables"){$already_printed3=1;continue(1);}	
					foreach ($printed_ids as $row_printed){
						if ($row_printed == $eachrow_array3['id']){$already_printed3=1; continue(1);}
					}
					if ($eachrow_array3['parent_id']==$eachrow_array2['id']){
						if (!$third_ul_printed){$menu_html .= "<ul class=\"menu_level3\">\n"; $third_ul_printed=1;}
						if (!$already_printed3){
							$menu_html .= "<li class=\"listitem_level3\"><a class=\"dynMenuItem\" id=\"dbfMenuItem-".$eachrow_array3['id']."\" href=\"" . get_desktop_link($eachrow_array3['url']) . "\">".$eachrow_array3['item_text']."</a>";
							array_push($printed_ids,$eachrow_array3['id']);
						}


					#
					# fourth loop
					# 
					foreach ($all_rows as $eachrow4 => $eachrow_array4){
						$already_printed4=0;
						if ($eachrow_array4['item_text']=="sysListTables"){$already_printed4=1;continue(1);}
						foreach ($printed_ids as $row_printed){
							if ($row_printed == $eachrow_array4['id']){$already_printed4=1; continue 1;}
						}
						if ($eachrow_array4['parent_id']==$eachrow_array3['id']){
							if (!$fourth_ul_printed){$menu_html .= "<ul class=\"menu_level4\">\n"; $fourth_ul_printed=1;}
							if (!$already_printed4){
								$menu_html .= "<li class=\"listitem_level4\"><a class=\"dynMenuItem\" id=\"dbfMenuItem-".$eachrow_array4['id']."\" href=\"" . get_desktop_link($eachrow_array4['url']) . "\">".$eachrow_array4['item_text']."</a>";
							array_push($printed_ids,$eachrow_array4['id']);	
							}

							#
							# fifth loop
							#
							foreach ($all_rows as $eachrow5 => $eachrow_array5){
								$already_printed5=0;
								if ($eachrow_array5['item_text']=="sysListTables"){$already_printed5=1;continue(1);}
								foreach ($printed_ids as $row_printed){
									if ($row_printed == $eachrow_array5['id']){$already_printed5=1; continue 1;}
								}
								if ($eachrow_array5['parent_id']==$eachrow_array4['id']){
									if (!$fifth_ul_printed){$menu_html .= "<ul class=\"menu_level5\">"; $fifth_ul_printed=1;}
								if (!$already_printed5){
										$menu_html .= "<li class=\"listitem_level5\"><a class=\"dynMenuItem\" id=\"dbfMenuItem-".$eachrow_array5['id']."\" href=\"" . get_desktop_link($eachrow_array5['url']) . "\">".$eachrow_array5['item_text']."</a>";
										array_push($printed_ids,$eachrow_array5['id']);
									}
									$menu_html .= "</li>\n"; # closes 5th level li	
								}
							}# close foreach 5
							if ($fifth_ul_printed){
								$menu_html .= "</ul>\n"; $fifth_ul_printed=0;
							}

							$menu_html .= "</li>"; # closes 4th level li
						}
					}# close foreach 4
					if ($fourth_ul_printed){
						$menu_html .= "</ul>\n"; $fourth_ul_printed=0;
					}
					$menu_html .= "</li>\n"; # closes 3rd level li
					}# close foreach 3 
				}
				if ($third_ul_printed){
					$menu_html .= "</ul>\n"; $third_ul_printed=0;
				}
			}
		} # close foreach 2
		if ($second_ul_printed){$menu_html .= "</ul>\n"; $second_ul_printed=0;}
	}
	// the last li with a parent of 0 needs a different class..
	$menu_html .= "</ul>\n";
	$menu_html .= "</div>\n";
	$menu_html=str_replace("current_user()",$user->value('id'),$menu_html);
	return $menu_html;
}
///////////////////////////////////////////////////////////////////////////////////////
// msort isnt acually used anyhere, but i was going to use it for the function above //
// i left it here as im sure it will be useful for something some time..             //
///////////////////////////////////////////////////////////////////////////////////////

function msort($array, $id="id") {
        $temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id]) && $array[$lowest_id][$id]) {
                    if ($item[$id]<$array[$lowest_id][$id]) {
                        $lowest_id = $index;
                    }
                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
        return $temp_array;
    }

function generate_table_list($id_for_menu,$tables_parent_id,$count_from){
	global $database_name;
	global $CONFIG;
	global $db;
	$row_header = "Tables_in_" . $database_name;
	$sql="show tables";
	$result=$db->query($sql); 
	$count_from = $count_from+2;
	$table_list=array();
	$tables_array=array();
	while ($row=$db->fetch_array($result)){
		if ($CONFIG['readable_table_names']){
			$table_name_readable=$row[$row_header];
			$table_name_readable=str_replace("_"," ",$table_name_readable);
			$table_name_readable=ucfirst($table_name_readable);
		} else {
			$table_name_readable=$row[$row_header];
		}
		array_push($table_list,$row[$row_header]);
		$tables_array[$count_from]['id']=$count_from;
		$tables_array[$count_from]['menu_id']=$id_for_menu;
		$tables_array[$count_from]['item_text']=$table_name_readable;
		$tables_array[$count_from]['url']="administrator.php?action=list_table&t=".$row[$row_header]."&dbf_edi=1&dbf_ido=1&dbf_add=1&dbf_eda=1&dbf_search=1&dbf_rpp_sel=1&dbf_rpp=".$CONFIG['default_records_per_page']."&dbf_filter=1&dbf_sort=1&dbf_sort_dir=1";
		$tables_array[$count_from]['parent_id']=$tables_parent_id;
		$tables_array[$count_from]['ordering']=0;
		$tables_array[$count_from]['real_name']=$row[$row_header];
		
		$count_from++;
		$tables_array[$count_from]['id']=$count_from;
		$tables_array[$count_from]['menu_id']=$id_for_menu;
		$tables_array[$count_from]['item_text']="Add Row";
		$tables_array[$count_from]['url']="administrator.php?action=edit_table&edit_type=add_row&t=".$row[$row_header];
		$tables_array[$count_from]['parent_id']=$count_from-1;
		$tables_array[$count_from]['ordering']=0;
		$tables_array[$count_from]['real_name']=$row[$row_header];

		$count_from++;
		$tables_array[$count_from]['id']=$count_from;
		$tables_array[$count_from]['menu_id']=$id_for_menu;
		$tables_array[$count_from]['item_text']="Edit All";
		$tables_array[$count_from]['url']="administrator.php?action=edit_table&t=".$row[$row_header]."&edit_type=edit_all&add_data=1";
		$tables_array[$count_from]['parent_id']=$count_from-2;
		$tables_array[$count_from]['ordering']=0;
		$tables_array[$count_from]['real_name']=$row[$row_header];

		$count_from++;
	}

return $tables_array;

}

function cache_dynamic_menu($menu_id){
	$build_menu=build_menu_from_table($menu_id);
	$fh=fopen ("cache/menu/menu_$menu_id.html","w") or die ("Unable to write to menu file in cache. Have you checked permissions on the cache directory?");
	fwrite($fh,$build_menu);
	fclose($fh);
	return "Menu $menu_id cached at " . time();
}

function get_desktop_link($url){
	$url="../mui-".trim($url);
	if (stristr($url,"?")){
		$url .= "&dbf_mui=1";
	} else {
		$url .= "?dbf_mui=1";
	}
	return $url;
}
?>
