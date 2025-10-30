<script language="JavaScript" type="text/javascript">

var offices_matrix=new Array();

<?php


//$user_id=$argv[1];
//require_once("library/database.php");

// get user dsTA

//$sql = "SELECT telephone_no, mobile_no, email_address, first_name,second_name,job_title from user where id = $user_id";
//$res=mysql_query($sql);
//$h=mysql_fetch_array($res,MYSQL_ASSOC);
//$telno=$h['telephone_no'];
//$mob=$h['mobile_no'];
//$email=$h['email_address'];
//$name=$h['first_name'] . " " . $h['second_name'];
//$job_title=$h['job_title'];

// get office data
$sql = "SELECT * FROM offices ORDER BY offices.office_name";
$res=mysql_query($sql) or die (mysql_error());
while ($h=mysql_fetch_array($res)){
	$option = "<option value=" . $h['id']. ">".$h['office_name']."</option>\n";
	$options .= $option;
	
	print "offices_matrix[".$h['id']."]=\"";
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
	print "\";\n";
	// create javascript arrays of all office data
	
}
?>
function fillOfficeFields_Registration(){
	selIndex=document.forms['register'].elements['selectoffice'].selectedIndex;
	if (selIndex>0){
		selectedOffice=document.forms['register'].elements['selectoffice'].options[selIndex].value;
		selectedText=document.forms['register'].elements['selectoffice'].options[selIndex].text;
		if (selectedText=="OTHER"){
			document.forms['register'].elements['addr1'].value="";
			document.forms['register'].elements['town'].value="";
			document.forms['register'].elements['postcode'].value="";
			document.forms['register'].elements['country'].value="";
			document.forms['register'].elements['telephone'].value="";
			document.forms['register'].elements['fax'].value="";
			alert("Please fill in the details for the new office below.");
		} else {
			officeData=offices_matrix[selectedOffice];
			officeDataArray=officeData.split("|");
			//document.forms['register'].elements['Business_Name'].value=officeDataArray[2];
			//document.forms['register'].elements['Division'].value=officeDataArray[3];
			//document.forms['register'].elements['Building_Name'].value=officeDataArray[4];
			document.forms['register'].elements['addr1'].value=officeDataArray[5];
			document.forms['register'].elements['town'].value=officeDataArray[6];
			document.forms['register'].elements['postcode'].value=officeDataArray[7];
			document.forms['register'].elements['country'].value=officeDataArray[8];
			document.forms['register'].elements['telephone'].value=officeDataArray[9];
			document.forms['register'].elements['fax'].value=officeDataArray[10];
			//document.forms['register'].elements['website'].value=officeDataArray[11];
		}
	} else {
		document.forms['register'].elements['addr1'].value="";
		document.forms['register'].elements['town'].value="";
		document.forms['register'].elements['postcode'].value="";
		document.forms['register'].elements['country'].value="";
		document.forms['register'].elements['telephone'].value="";
		document.forms['register'].elements['fax'].value="";
		//document.forms['register'].elements['website'].value="";

	}

}
<?php
print "\n</script>";
$options .= "<option value=\"OTHER\">OTHER</option>";
print '<select name="selectoffice" onChange="fillOfficeFields_Registration()"><option>Select Office:</option> ' . $options . '</select>';

?>
