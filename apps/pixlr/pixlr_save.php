<?php
$basepath="../../";
?><html>
<head>
<title>Save Image</title>
<link rel="stylesheet" type="text/css" href="<?=$basepath?>css/system_classes.css" />
<link rel="stylesheet" type="text/css" href="<?=$basepath?>css/admin_styles.css" />
</head>
<body>
<?php

$image_file=$_REQUEST['image'];
$image_title=$_REQUEST['title'];
$image_type=$_REQUEST['type'];
$rebuild_title=str_replace("_-_","/",$image_title);
print "<p><b>Pixlr..</b> Saving image to ".$basepath."$rebuild_title.$image_type ..</p>";
// use curl to get the image as a binary file and save it out to the filename...

	$url=$image_file;
        set_time_limit(60);
        // Initialise output variable
        $output = array();
        // Open the cURL session
        $curlSession = curl_init();
        // Set the URL
        curl_setopt ($curlSession, CURLOPT_URL, $url);
        // No headers, please
        curl_setopt ($curlSession, CURLOPT_HEADER, 0);
        // Return it direct, don't print it out
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER,1);
        // This connection will timeout in 30 seconds
        curl_setopt($curlSession, CURLOPT_TIMEOUT,30);
        //Send the request and store the result in an array
        $rawresponse = curl_exec($curlSession);
	$path_and_file=$basepath.$rebuild_title.".".$image_type;
	file_put_contents($path_and_file,$rawresponse) or die("<p class=\"dbf_para_alert\">Cannot save image. Check that the file and directory are writeable in the filesystem.</p>");

	print "<p class=\"dbf_para_success\">File has been saved as $rebuild_title.$image_type</p>";
exit;

?>
</body>
</html>
