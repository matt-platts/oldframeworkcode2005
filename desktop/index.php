<?php

if (empty($_SESSION)){session_start();}

require_once ("../config.php");
require_once ("$libpath/library/core/errors.php");
require_once ("$libpath/library/core/require.php");

/* Log in and out */
$script_action=$_GET['action'];

/* Log in */
if ($script_action=='process_login'){
	$login_result=$user->process_login($_POST['email_address'],$_POST['password'],$_SERVER['PHP_SELF']); 
} else if ($user->value('id') && $_COOKIE['login']){
	$user->refresh_login_cookie();

}

/* Log out */
if ($script_action=="process_log_out"){
        if (!$_GET['dir_to']) {
                $dir_to = $_SERVER['PHP_SELF'];
        } else {
                $dir_to = $_GET['dir_to'];
        }
        $user->process_log_out($dir_to);
	header("Location: index.php");
}

/* Set default theme for the ui */
$ui_theme="default";

/* If have a logged in user, get the default settings for the desktop */
if ($user->value("id") && $_COOKIE['login']){

	// get desktop defaults
	$sql="SELECT * from user_desktops WHERE user = " . $user->value("id");
	$rv=$db->query($sql);
	$desktop_h=$db->fetch_array($rv);
	if ($desktop_h){
		if ($desktop_h['theme']){ $ui_theme=$desktop_h['theme']; } // actually not used in this page, its in the init js
		if ($desktop_h['background_image']){ $ui_wallpaper=$desktop_h['background_image']; }
		if (preg_match("/^http:/",$ui_wallpaper)){
			$background_image_css="background-image:url($ui_wallpaper);";
		} else {
			$background_image_css="background-image:url(images/backgrounds/$ui_wallpaper);";
		}
	} else {
		$background_image_css="";
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<title>Gonzo Multimedia -  Desktop</title>
	<meta name="description" content="A web applications user interface library built on the Mootools javascript framework" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<link rel="stylesheet" type="text/css" href="themes/default/css/Content.css" />
	<link rel="stylesheet" type="text/css" href="themes/default/css/Core.css" />
	<link rel="stylesheet" type="text/css" href="themes/default/css/Layout.css" />
	<link rel="stylesheet" type="text/css" href="themes/default/css/Dock.css" />
	<link rel="stylesheet" type="text/css" href="themes/default/css/Tabs.css" />
	<link rel="stylesheet" type="text/css" href="themes/default/css/Window.css" />
	<!--[if IE]>
		<script type="text/javascript" src="scripts/excanvas_r43.js"></script>
	<![endif]-->
    	<script type="text/javascript" src="scripts/mootools-1.2.4-core-yc.js"></script>

    	<script type="text/javascript" src="scripts/Mootools.Fx.CSS.Transform.js"></script>
    	<script type="text/javascript" src="scripts/WindowPicker.js"></script>
	
    	<script type="text/javascript" src="scripts/mootools-1.2.4-more-yc.js"></script>
    	<script type="text/javascript" src="scripts/mocha.js"></script>    
	<script type="text/javascript">
	<?php if ($user->value("full_name") && $_SESSION['user_id'] && $_COOKIE['login']){
		print "logged_in=1;\n";
		$logged_in=1;
	} else {
		print "logged_in=0;\n";
		$logged_in=0;
	}
	?>
	</script>
	
	<script type="text/javascript" src="scripts/virtual-desktop-init.js.php"></script>
	<script type="text/javascript" src="scripts/windowFunctions.js.php"></script>

	<style>

	/* This CSS should be placed in a style sheet. It is here in order to not conflict with the other demos, but also note that PHP writes to it. */

	#pageWrapper {
		background: #777;
		background-position:center center;
		background-size:100%;
		background-repeat:no-repeat;
		<?php echo $background_image_css; ?>
	}

	.desktopIcon, .desktopIconDatabase, .desktopIconImages {
		margin: 15px 0 0 15px;
		cursor: pointer;
	}

	</style>
    <link rel="stylesheet" type="text/css" href="rc_menu/css/CM-menu.css"/>
    <script type="text/javascript" src="rc_menu/js/CM-menu.js"></script>
    <script type="text/javascript" src="rc_menu/js/rc_menu_setup.js"></script>
</head>
<body>

<div id="desktop">

	<div id="desktopHeader">
		<div id="desktopTitlebarWrapper">
			<div id="desktopTitlebar">
				<h1 class="applicationTitle">Mocha UI Virtual Desktop</h1>
				<h2 class="tagline">Paragon Digital Web Applications <span class="taglineEm">Virtual Desktop</span></h2>
				<div id="topNav">
					<ul class="menu-right">
						<li>Welcome 
<?php if ($logged_in){ ?>
	<a href="#" onclick="MUI.notification('Hello!');return false;"><?php echo $user->value("full_name"); ?></a>.
<?php } else { ?>
- please log in<?php } ?>
</li>
						<?php if ($logged_in){?><li><a href="index.php?action=process_log_out">Sign Out</a></li><?php } ?>
					</ul>
				</div>
			</div>
		</div>
	
		<div id="desktopNavbar">
			<?php if ($logged_in){
				$menuid=1; // specify menu id
				print build_desktop_menu_from_table($menuid); // dynamically build the menu - dependent on user id and type.
			?>
			<div class="another_menu">  
			<ul>
				<!-- THESE ARE THE ORIGINAL WIDGETS THAT CAME WITH THE MOCHAUI DEMO
					<li><a class="returnFalse" href="">| &nbsp; &nbsp; Widgets</a>	
					<ul>
						<li><a class="returnFalse arrow-right" href="">Demos</a>
						<ul>
						<li><a id="ajaxpageLinkCheck" href="pages/lipsum.html">Ajax/XHR Demo</a></li>
						<li><a id="jsonLink" href="data/json-windows-data.js">Json Demo</a></li>
						<li><a id="youtubeLinkCheck" href="pages/youtube2.html">Desktop Settings</a></li>
						<li><a id="accordiantestLinkCheck" href="pages/accordian-demo.html">Accordian</a></li>
						<li><a id="windoweventsLinkCheck" href="pages/events.html">Window Events</a></li>
						<li><a id="containertestLinkCheck" href="pages/lipsum.html">Container Test</a></li>
						<li><a id="iframetestLinkCheck" href="pages/iframetests.html">Iframe Tests</a></li>
						<li><a id="noCanvasLinkCheck" href="pages/lipsum.html">No Canvas Body</a></li>
						</ul>
					</li>
							<li><a id="clockLinkCheck" href="plugins/coolclock/">Widget: Clock</a></li>
						<li><a id="browserLinkCheck" href="plugins/webBrowser/">Web Browser</a></li>
						<li class="divider"><a class="returnFalse arrow-right" href="">Starters</a>
							<ul>
								<li><a target="_blank" href="index.php">New Browser Tab</a></li>
							</ul>
						</li>
					</ul>
				</li>//-->

				<li><a class="returnFalse" href="">| &nbsp; &nbsp; View</a>
					<ul>
						<li><a id="cascadeLink" href="">Cascade Windows</a></li>
						<li><a id="tileLink" href="">Tile Windows</a></li>
						<li class="divider"><a id="minimizeLink" href="">Minimize All Windows</a></li>
						<li><a id="closeLink" href="">Close All Windows</a></li>
						<li class="divider"><a id="parametricsLinkCheck" href="plugins/parametrics/">Window Parametrics</a></li>
					</ul>
				</li>
				<li>
					<a class="returnFalse" href="">Workspace</a>
					<ul>
						<li><a id="saveWorkspaceLink" href="">Save Workspace</a></li>
						<li><a id="loadWorkspaceLink" href="">Load Workspace</a></li>
					</ul>
				</li>

				<!-- FURTHER DEMO STUFF THAT CAME WITH THE ORIGINAL MOCHAUI
				<li><a class="returnFalse" href="">Help</a>
					<ul>
						<li><a id="featuresLinkCheck" href="pages/features.html">Features</a></li>
						<li class="divider"><a class="returnFalse arrow-right" href="">Documentation</a>
					<ul>
						<a id="documentationLink" href="Javascript:loadExternalPage('http://www.paragon-digital.net/paragon_dev/documentation/system_documentation/indexpage.html')">System Documentation</a> 
					</ul>
					</li>
				<li class="divider"><a id="aboutLink" href="pages/about.html">About</a></li>
				//-->

				</ul>
				</li>
			</ul>
			</div>
		<?php } ?>
			 <div class="toolbox divider">
                                <div id="spinnerWrapper"><div id="spinner"></div></div> 
                        </div>

                        <div class="toolbox divider"> 
                                <select id="themeControl" name="themeControl" size="1" onchange="MochaUI.Themes.init(this.options[this.selectedIndex].value)">
                                        <option id="chooseTheme" value="" selected>Choose Theme:</option>
                                        <option value="default">Default</option>
                                        <option value="charcoal">Charcoal</option>
                                </select> 
                        </div> 
		</div><!-- desktopNavbar end -->

	</div><!-- desktopHeader end -->

	<div id="dockWrapper">
		<div id="dock">
			<div id="dockPlacement"></div>
			<div id="dockAutoHide"></div>
			<div id="dockSort"><div id="dockClear" class="clear"></div></div>
		</div>
	</div>

	<div id="pageWrapper" class="mainPageContent" width="100%" >
		<div id="page" >
			<style type="text/css">
				.iconDiv {float:left; position:relative; clear:both;}
			</style>
			<?php if ($logged_in){ include_once("icons.php"); } ?>
		</div>
	</div>

	<div id="desktopFooterWrapper">
		<div id="desktopFooter">
			&copy; 2015 <a target="_blank" href="http://www.paragon-digital.net/">Paragon Digital</a> 
		</div>
	</div>

</div><!-- desktop end -->

</body>
</html>
<?php
exit;
?>
