<?php

include_once("../init_database.php");
$tablelist=list_tables();
$filtersql="SELECT id,filter_name FROM filters ORDER BY filter_name";
$filterlist=array();
$filterres=$db->query($filtersql);
while ($h=$db->fetch_array($filterres)){
	$filterlist[$h['id']]['name']=$h['filter_name'];
	$filterlist[$h['id']]['id']=$h['id'];
}
?>

<ul id="tree1" class="tree">
	<li class="folder f-open first"><span>Tables</span>
		<ul>
<?php
foreach ($tablelist as $table){
	$link="Javascript: MUI.updateContent({ element: $('panel2'), url: '../mui-administrator.php?action=list_table&t=".$table['real_name']."', title: '".$table['name']."', padding: { top: 8, right: 8, bottom: 8, left: 8 } });";
			print '<li id="table_'.$table['real_name'].'" class="doc"><span><a href="'.$link.'">'.$table['name'].'</a></span></li>';
}
?>
		</ul>
	</li>

	<li class="folder f-open"><span>Filters</span>
		<ul>
		<?php
		foreach ($filterlist as $filter){
		$link="Javascript: MUI.updateContent({ element: $('panel2'), url: '../mui-administrator.php?action=full_interface_edit&interface_id=".$filter['id']."', title: '".$filter['name']."', padding: { top: 8, right: 8, bottom: 8, left: 8 } });";
			print '<li id="filter_'.$filter['id'].'" class="doc"><span><a href="'.$link.'">'. $filter['name'].'</a></span></li>';
		}
		?>
		</ul>
	</li>
	<li class="folder f-open"><span>Windows</span>
		<ul>
			<li id="ajaxpageLink" class="doc"><span><a>Ajax/XHR Demo</a></span></li>
			<li id="jsonLink" class="doc"><span><a>Json Demo</a></span></li>
			<li id="youtubeLink" class="doc"><span><a>Iframe: YouTube</a></span></li>
			<li id="accordiantestLink" class="doc"><span><a>Accordian</a></span></li>
			<li id="parametricsLink" class="doc"><span><a>Window Parametrics</a></span></li>
			<li id="splitWindowLink" class="doc"><span><a>Split Window</a></span></li>						
		</ul>
	</li>
	<li class="folder f-open"><span>Widgets</span>
		<ul>
			<li id="calendarLink" class="doc"><span><a>Calendar (Plugin)</a></span></li>
			<li id="clockLink" class="doc"><span><a>Clock</a></span></li>						
		</ul>
	</li>	
	<li class="folder f-open"><span>Tools</span>
		<ul>
			<li id="fxmorpherLink" class="doc"><span><a>Path Animation (Plugin)</a></span></li>				
		</ul>
	</li>					
</ul>
