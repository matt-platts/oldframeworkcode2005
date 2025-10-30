<?php 
// set up basic connection 
$ftp_server = "mediafiles.gonzomultimedia.co.uk"; 
$conn_id = ftp_connect($ftp_server); 

// login with username and password 
$ftp_user_name = "gonzomedia1"; 
$ftp_user_pass = "media555"; 
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
ftp_pasv($conn_id, true); 


// check connection 
if ((!$conn_id) || !$login_result) { 
        echo "<p><b>The FTP connection has failed.</b></p><p>Attempted to connect to $ftp_server for user $ftp_user_name</p>$login_result\n"; 
        exit; 
    } else { 
        echo "<p class=\"dbf_para_success\">Connected to $ftp_server, for user $ftp_user_name</p>\n"; 
    } 

ftp_chdir($conn_id, "httpdocs/radio_shows");
$buff = ftp_nlist($conn_id, '.'); 

if (count($buff)==0){
	print "<p class=\"dbf_para_alert\">There are no shows in the FTP directory to copy.</p>";
	$no_shows=1;
}
foreach ($buff as $remote_file){
	
	print "\n<p>Copying file: $remote_file\n\n</p>";

	$local_file="/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/files/radio_shows/$remote_file";
	$handle=fopen("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/files/radio_shows/$remote_file",'w+') or die("Cannot open local file - ensure file names contain only A-Z, 0-9, Underscore and Hypen - no spaces or brackets or other punctuation is allowed");
	
	if (ftp_fget($conn_id, $handle, $remote_file, FTP_BINARY, 0)) {
	 echo "<p class=\"dbf_para_success\">Successfully written to $local_file</p>\n";
	} else {
	 echo "<p class=\"dbf_para_alert\">There was a problem while downloading $remote_file to $local_file</p>\n";
	}
}

if (!$no_shows){
	print "<p>All files from the FTP directory should now be copied to the local radio shows directory.</p>";
}

ftp_close($conn_id);  
?> 
