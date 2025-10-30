<script language="JavaScript" type="text/javascript">

var offices_matrix=new Array();

<?php


$user_id=$argv[1];
require_once("library/database.php");

// get user dsTA

$sql = "SELECT telephone_no, mobile_no, email_address, first_name,second_name,job_title from user where id = $user_id";
$res=mysql_query($sql);
$h=mysql_fetch_array($res,MYSQL_ASSOC);
$telno=$h['telephone_no'];
$mob=$h['mobile_no'];
$email=$h['email_address'];
$name=$h['first_name'] . " " . $h['second_name'];
$job_title=$h['job_title'];

// get office data
$sql = "SELECT * FROM user_office_lookup,offices WHERE (user_office_lookup.user_id='$user_id') AND (offices.id=user_office_lookup.office_id) ORDER BY offices.office_name";
$res=mysql_query($sql) or die (mysql_error());
while ($h=mysql_fetch_array($res)){
	$option = "<option value=" . $h['office_id']. ">".$h['office_name']."</option>\n";
	$options .= $option;
	
	print "offices_matrix[".$h['office_id']."]=\"";
	print $h['office_name'] . "|";
	print $h['office_id'] . "|";
	print $h['business_name'] . "|";
	print $h['division'] . "|";
	print $h['building_name'] . "|";
	print $h['addr1'] . "|";
	print $h['town'] . "|";
	print $h['postcode'] . "|";
	print $h['country'] . "|";
	print $h['telephone'] . "|";
	print $h['fax'] . "|";
	print $h['website'] . "|";
	print $telno . "|";
	print $mob . "|";
	print $email . "|";
	print $name . "|";
	print $job_title . "|";
	print "\"\n";
	// create javascript arrays of all office data
	
}

print "</script>";

print '<select name="selectoffice"><option>Select Office:</option> ' . $options . '</select>';

?>
