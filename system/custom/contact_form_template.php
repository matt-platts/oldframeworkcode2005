	<?
/*
	$get="select * from councillors";
	$res=mysql_query($get);
	$councillor_select_options.="<option value=\"\">Please Select</option>";
	$councillor_select_options.="<option value=\"clerk@chalfontstpeter-pc.gov.uk\"";
	if ($_GET['cid']=="clerk"){ $councillor_select_options .= "selected"; }
	$councillor_select_options .= ">The Parish Council Clerk</option>";
	while ($h=mysql_fetch_array($res,MYSQL_ASSOC)){
		$councillor_select_options.="<option value=\"". $h['email'] ."\"";
		if ($h['id']==$_GET['cid']){
			$councillor_select_options .= " selected";
		}
		$councillor_select_options .= ">Cllr. ".$h['first_name'] . " " . $h['second_name'] . "</option>";
	}
	//$councillor_select_options.="<option value=\"mattplatts@gmail.com\">Matt Platts (web site issues)</option>";
*/
?>
<input type="hidden" name="new_mail_to" value="mattplatts@gmail.com" />
<table border="0" cellspacing="10" cellpadding="0" width="430">
		<tbody>

			<tr>
				<td valign="top"><font face="arial" size="-2" color="black">

				<strong>Contact Name:<br />
				<input name="new_your_name" size="30" type="text" />
				<br />

				Your Email Address:<br />
				(*Required)<br />

				<input name="new_your_email_address" size="30" type="text" />

				<br />
				Telephone Number:<br />

				(*Required)<br />

				<input name="new_your_telephone_number" size="30" type="text" />
				</td>
				<td width="180" align="left" valign="top">

				<font face="arial" size="-2" color="black">

				<b>How should we respond?</b><br />

				<select name="new_how_should_we_respond">

				<option value="-">

				Please select
				</option>
				<option value="Telephone">
				By telephone
				</option>

				<option value="Email">
				By email
				</option></select>

				</strong></font></td>
			</tr>

			<tr>
				<td colspan="2" valign="top">
				<font face="arial" size="-2" color="black">

				<strong>Send your enquiry below - be sure to let us know what you want and when:</strong></font><br />
				<textarea name="new_your_message" rows="8" cols="52"></textarea>

				</td>

			</tr>

			<tr>
				<td colspan="2" valign="top">
				<input type="submit" value="Send Enquiry" />

				<br />
				<font face="arial" size="-2" color="black">

				<strong>Please complete all fields for an efficient response.<br />
				We will endeavour to respond as soon as possible.</strong>
				<input type="hidden" name="new_contact_date" value="NOW()">
				</font></td>
			</tr>
		</tbody>

	</table>


