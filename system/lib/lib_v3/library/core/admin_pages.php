<?php

/* 
 * File: admin_layout.php
 * Meta: functions to do with the admin settings and admin home page -  extremely old from the original version.
*/

/*
 * Function deisplay_admin_homepage
 * Meta: extremely old and needs templating properly
*/
function display_admin_homepage(){
        global $page;
        global $user;
	if (!$user->value("id")) {
		print "</div>";
		?>  
		<div id="admin_login_div"><p style="text-align:left; float:left;"><? if ($_GET['error']){print "<span style=\"color:#990000\">Login details not recognised</span>";}?></p><p style="clear:both">
		<h4 width="400" style="width:400px; text-align:left;">Please log in:</h4>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>?action=process_login" method="post" name="login_form" onsubmit="check_login()">
		<table border="0" cellpadding="0" cellspacing="0">
		<tr><td align='right' class='form_table'>Email:</td><td><input type="text" class="login_sidebar_textbox" name="email_address" value="" size="15" /></td></tr>
		<tr><td>&nbsp;</td><td></td></tr>
		<tr><td align='right' class='form_table'>Password:</td><td><input type="password" class="login_sidebar_textbox" name="password" value="" size="15" /></td></tr> 
		<tr><td>&nbsp;</td><td></td></tr>
		<tr><td></td><td><input type="submit" value="Log In" class="login_sidebar_button" /></td></tr></table>
		</form>
		<script language="Javascript" type="text/javascript">
		<!--
		window.onLoad=document.forms[0].elements[0].focus();
		//-->
		</script>
		</div>
		<?php   
	} else {
                open_col2();
                global $admin_page_title;
                global $admin_page_text;
                if ($admin_page_text){$admin_page_title=$admin_page_text;}
                global $CONFIG;
                global $db;
                global $user;

		// first choice - is there an admin_home_page based on the user id
                $homepage_sql="SELECT admin_home_page from user_desktops WHERE user = " . $user->value("id");
                $rv=$db->query($homepage_sql);
                $homepage_h=$db->fetch_array($rv);
                if ($homepage_h['admin_home_page'] && $homepage_h['admin_home_page']>0){
                        $content = $page->content_from_id($homepage_h['admin_home_page']);
                        print $content;
		// second choice - there may be one for mui if it's a mui page..
                } else if ($CONFIG['admin_home_page_mui'] && $page->value("mui")){
			$content = $page->display_admin_content_by_key_name($CONFIG['admin_home_page_mui']);
                        print $content;
		// third choice - if no config value for admin_home_page
                } else if (!$CONFIG['admin_home_page']){
                        print "<h4 class=\"intro_text_header\"><b>W</b>elcome to the $admin_page_title site administrator.</h4>";
                        print "<p><b>P</b>lease select an option from the menu to begin.</p>\n";
                        if ($user->value("type")=="superadmin"){print_graphic_menu();}
                        if ($user->value("type")=="administrator"){print_front_page_from_menu();}
		// fourth - look for a value on user type
                } else {
                        $usertype=$user->value("type");
			// config values may exist for user, administrator and superadmin
			$config_key_value="admin_home_page_" . $user->value("type");
			if ($CONFIG[$config_key_value]){
				if (is_numeric($CONFIG[$config_key_value])){
					$content = $page->content_from_id($CONFIG[$config_key_value]);
				} else {
					$content=$page->display_admin_content_by_key_name($CONFIG[$config_key_value]);
				}

			} else {
				if (!is_numeric($CONFIG['admin_home_page'])){
					if ($CONFIG['admin_home_page']=="list_tables"){
						print_list_tables("application");
					} else {
						$content=$page->display_admin_content_by_key_name($CONFIG['admin_home_page']);
					}
				} else {
					$content = $page->content_from_id($CONFIG['admin_home_page']);
				}
                        }
    
                        print $content;
                }   
        }   
}


//open first column in admin - this is normally used for a menu
function open_col1(){
        print "<div id=\"col1\">";
        $col1_open=1;
}

function open_col2(){ //open second column - this is normally used for the main
        global $col2_open;
        if (!$col2_open){
                print "<div id=\"col2\">";
                $col2_open=1;
        }   
}

function open_status_message(){ // open status_message
        print "<div id=\"status_message\">";
        $status_message_open=1;
}

function open_credits(){ // open status_message
        print "<div id=\"credits\">";
        $credits_open=1;
}

function open_col3(){ //open third column - not used at present, would prob be a right hand or bottom menu
        print "<div id=\"col3\">";
        $col3_open=1;
}

function close_col(){ // Close a page column
	if (!stristr($_SERVER['SCRIPT_FILENAME'],"ministrator.php")){
		print "</div><!-- closed from close_col() function //-->";
	}
}

/* 
 * Function meta_info
 * Meta: Content for the 'about' popup in MochaUI
*/
function meta_info(){
        ?>  
        <p class="admin_header">About / Credits</p>
        <p> 
        MySQL Data Manager 0.9.5<br />
        &copy <a href="mailto:mattplatts@gmail.com">Matt Platts</a> 1999-2010.
        <p>The following open source software and graphics have been used in this build, and the creators are duly credited and linked to:</p>
        <ul>
        <li><a href="http://tinymce.moxiecode.com" target=_blank>Tiny MCE by MoxieCode</a> - the rich text editor found on text area fields has been modified, the core code comes from here.
        <li><a href="http://www.famfamfam.com" target=_blank>Silk Icons by Mark James</a> - a nice simple free set of small icons.
        <li><a href="http://www.cdolivet.com/index.php?page=editArea" target=_blank>EditArea by Christophe Dolivet</a> - the code editor found on textarea fields giving line numbers and syntax highlighting.
        <li><a href="http://www.j-cons.com" target=_blank>Imanager / Ibrowser by Jaeger Consulting</a> - File uploads through tinymce
        <li><a href="http://www.mootools.net" target=_blank>Mootools Javascript Framework</a> - the great javascript framework, version 1.2 is installed ready to go. Some of the AJAX functionality is based on mootools.
        <li><a href="http://phatfusion.net/multibox/" target=_blank>Multibox</a> by <a href="http://www.samuelbirch.com">Samuel Birch</a> - mootools based code for image galleries and much much more..
        </ul>   
        <p>Further plugins may be based on further open or closed source software and are not listed here.</p>
<hr size=1>
        <p>MySQL Data Manager is built from a PHP conversion of the Perl DbForms module,and was built by Matt Platts. It runs on linux only (with basic functionality possible in Windows).</p>
        <?php 
}

/* 
 Function show_documentation
*/
function show_documentation(){
open_col2();
$doc=file_get_contents("../docs/userguide.htm");
print $doc;
close_col();
}

/* 
 * Function set_mui_background
 * Allow select and store of the background for the MochaUI interface
*/
function set_mui_background(){
        global $db;
        global $user;

        $background=$_POST['dbf_mui_bg'];
        if ($_POST['dbf_mui_bg_url']){
                $background=$_POST['dbf_mui_bg_url'];
                $url_bg=$background;
        }   

        if ($background){
                $update_sql="UPDATE user_desktops SET background_image=\"" . $db->db_escape($background) . "\" WHERE id = " . $user->value("id");
                $rv=$db->query($update_sql);
                $cur_bg=$db->db_escape($background);
        } else {
                $current_bg_sql="SELECT background_image FROM user_desktops WHERE user = " . $user->value("id");
                $rv=$db->query($current_bg_sql);
                $bg_h=$db->fetch_array($rv);
                if ($bg_h['background_image']){
                        $cur_bg=$bg_h['background_image'];
                }   
        }   

        if (preg_match("/http:\/\//",$cur_bg)){$url_bg=$cur_bg; $cur_bg="0";}
        $backgrounds=get_directory_list("desktop/images/backgrounds");
        $bg_options=implode(",",$backgrounds);
        $options=database_functions::build_select_option_list($bg_options,$cur_bg,1,1,0);
?>
<div style="float:left; text-align:left; padding-left:5px;">
<form action="administrator.php?action=set_mui_background&amp;jx=1&amp;iframe=1&amp;dbf_mui=1" method="post">
<select name="dbf_mui_bg" onChange="parent.MochaUI.Background.init(this.value)">
<?=$options;?>
</select><br />
or enter a URL from the web: <input type="text" name="dbf_mui_bg_url" style="width:260px;" value="<?=$url_bg?>">
<input type="submit" value="Store Background" />
</form>
<?php 
if ($background){
?>
<script type="text/javascript">
parent.MochaUI.Background.init('<?=$background?>');
</script>
<?php
print "<p class=\"dbf_para_success\" style=\"width:220px;\">Background stored</p>";
}
?>
</div>
<?php
}

/*
 * Function set_mui_display_options
 * Meta: Display options for the mochaUI interface
*/
function set_mui_display_options(){
	global $db;
	global $user;
	if ($_POST){
		print "<p class=\"dbf_para_success\">Display settings updated</p>";
		?>
		<script type="text/javascript">
			top.MUI.options.useEffects=false;
			top.MUI.options.advancedEffects=true;
		</script>
		<?php
	}
?>
<div style="float:left; text-align:left; padding-left:5px; padding-right:5px;">
<form action="mui-administrator.php?action=set_mui_display_options" method="post">
<input type="checkbox" name="basic_fx">Fade windows in/out<br />
<input type="checkbox" name="adv_fx">Advanced Effects<br />
<input type="submit" value="update">
</form>
</div>
<?php
}

/* 
 * Function set_mui_theme
 * Meta: sets the MochaUI theme
*/
function set_mui_theme(){
        global $db;
        global $user;

        $theme=$_POST['dbf_mui_theme'];

        if ($theme){
                $update_sql="UPDATE user_desktops SET theme=\"" . $db->db_escape($theme) . "\" WHERE id = " . $user->value("id");
                $rv=$db->query($update_sql);
                $cur_theme=$db->db_escape($background);
        } else {
                $current_theme_sql="SELECT theme FROM user_desktops WHERE user = " . $user->value("id");
                $rv=$db->query($current_theme_sql);
                $theme_h=$db->fetch_array($rv);
                if ($theme_h['theme']){
                        $cur_theme=$theme_h['theme'];
                }   
        }   

        $options_array="default,charcoal";
        $options=build_select_option_list($options_array);
?>
<form action="mui-administrator.php?action=set_mui_theme&amp;jx=1&amp;iframe=1&amp;dbf_mui=1" method="post">
<select name="dbf_mui_theme">
<?=$options;?>
</select><br />
<input type="submit" value="Set Theme" />
</form>
<?php 
if ($theme){
?>
<script type="text/javascript">
parent.MochaUI.Themes.init('<?=$theme?>');
</script>
<?php
}
?>
<?php
}

function admin_header($title){
        $ret ="<p class=\"admin_header\">$title</p>";
        return $ret;
}
?>
