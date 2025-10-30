<?php


// replace any closing paragraph with a double line break
// replace any opening paragraph preceded by a <td tag with nothing unless the p has attributes. if it does replace the p with a span (<td[^>] hmm 

function rsaCleanHTML($inputstring){
$html=file_get_contents("html.txt");
$clean=rsaCleanHTML($html);
print $clean;
exit;
	$inputstring = str_replace("&nbsp;"," ",$inputstring);
	$inputstring = str_replace("<br>","<br />",$inputstring);
	$inputstring = str_replace("<p>","",$inputstring);
	$inputstring = str_replace("</p>","<br /><br />",$inputstring);
	return $inputstring;
}

function export_rsa_email($contentID){
global $db;
global $content_table;
global $content_table_name_field;
$sql = "SELECT * from " . $content_table . " WHERE id = " . $contentID;
$result=$db->query($sql);
while ($row=$db->fetch_array($result)){
$content_to_format = $row['content'];
$email_title = $row['name'];
}  
open_col2();
?>
<textarea rows="40" cols="90">
<html>
<head><title><?php echo $email_title; ?></title>
</head>
<body>
<?php echo $content_to_format;?>
</body>
</html>
</textarea>
<?php
close_col();
}


?>
