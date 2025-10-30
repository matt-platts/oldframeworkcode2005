<?php
require_once('plugins/qcalendar/app/controller.php');

if (isset($_POST) && $_POST != NULL) {
	extract($_POST);
}
else {
	// init month, year and category
	$month = date('m');
	$year = date('Y');
	$cat_id = 0;
}
?>
<form name='form1' action='' method='post'>
<table>
	<tr>
		<td><strong>Month:</strong></td>
		<td>
		<select name='month'>
		<?php
		$monthArray = array('1'=>'January', '2'=>'February', '3'=>'March', '4'=>'April', '5'=>'May', '6'=>'June', '7'=>'July', '8'=>'August', '9'=>'September', '10'=>'October', '11'=>'November', '12'=>'December');
		
		foreach($monthArray as $k=>$v) {
			echo "<option value='$k'";
			if ($month == $k) {
				echo ' selected';
			}
			echo ">$v</option>";
		}
		?>
		</select>
		</td>
		<td><strong>Year:</strong></td>
		<td>
		<select name='year'>
		<?php
		$yearArray = array('2008'=>'2008 - Rat', '2009'=>'2009 - Cow', '2010'=>'2010 - Tiger', '2011'=>'2011 - Rabit', '2012'=>'2012 - Dragon');
		
		foreach($yearArray as $k=>$v) {
			echo "<option value='$k'";
			if ($month == $k) {
				echo ' selected';
			}
			echo ">$v</option>";
		}
		?>
		</select>
		</td>
		<td><strong>Category:</strong></td> 
		<td>
		<select name='cat_id'>
		<option value='0'>Select All</option>
		<?php
		$sql = "SELECT id, short_desc FROM ".QCALENDAR_CAT_TABLE." where active='1'";
		$rs = mysql_query($sql);
		while ($rw = mysql_fetch_assoc($rs)) {
			echo "<option value='{$rw['id']}'";
			if ($cat_id==$rw['id']) {
				echo ' selected';
			}
			echo ">{$rw['short_desc']}</option>";
		}
		?>
		</select>
		</td>
		<td><input type="submit" value="submit" /></td>
	</tr>
</table>
</form>
<br/>
<div style="float:left;">
<?php
$cssCalendar='float:left; width:430px';
$cssLongDesc='width:200px;overflow:auto;z-index:10;position:absolute;border:1px solid #0066FF; background-color:#FFFFFF; visibility:hidden;';
// configure calendar theme
initQCalendar('twocolumn','qCalendarTwoColumn', $cssCalendar, 'myContentTwoColumn', $cssLongDesc, 0, $month, $year, $cat_id, 0);
?>
</div>

<div style="float:right; ">
<?php
$mon = $month;
$y = $year;
$monthDisplay = 5;
for ($i=$month; $i < $month+$monthDisplay; $i++) {
	// configure calendar theme
	echo "<div style='margin-right:10px;margin-bottom:10px;border:1px solid blue;'>";
	$cssCalendar= '';
	$cssLongDesc='';
	$mon = $i;
	if ($i>12) {
		$y = $year+1;
		$mon = $i-12;
	}
	initQCalendar('tiny', 'qCalendarTiny'.$i, $cssCalendar, 'myContentTiny'.$i, $cssLongDesc, 0, $mon, $y, $cat_id, 0);
	echo "</div><div style='clear:both'></div>";
}
?>
</div>
