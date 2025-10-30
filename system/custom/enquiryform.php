<?php

?>
<script language="Javascript" type="text/javascript">

function check_enquiry_form(){
	if (!document.forms['create_enquiry'].elements['enquiry'].value || !document.forms['create_enquiry'].elements['title'].value){
		alert("Please give your enquiry both a title and a message before posting - thank you.");
	} else {
		document.forms['create_enquiry'].submit();
	}
}
</script>
<form action="site.php?s=1&action=create_enquiry" method="post" name="create_enquiry">

<table width="500" border="0" cellpadding="0" cellspacing="0" style="padding-left:15px">
<tr>
<td style="padding-right:L6px; font-weight:bold">Title: </td><td><input type=\"text\" name="title" style="width:420px"></td></tr> 
<tr valign="top">
<td class="text" style="padding-right: 6px; font-weight:bold">Enquiry: </td>
<td>
<textarea name="enquiry" rows="6" cols="50"></textarea>
</td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
</tr>
<tr>
<td>&nbsp;</td>
<td class="text"> 
<!--<input type="hidden" name="ticketID" id="ticketID" value="0">-->
<span class="jc_button_140" style="margin-top:30px">
<a href="Javascript:check_enquiry_form();">Post Enquiry</a>
</td>
</tr>
</table>    

</form>
