<?php

cron_mail("Starting FTP Updates");
shell_exec("/usr/bin/wget -O -q http://www.voiceprint.co.uk/images/news/ftp_news_images_from_gonzo.php");
shell_exec("/usr/bin/wget -O -q http://www.gonzomultimedia.com/images/news/ftp_news_images_from_gonzo.php");
shell_exec("/usr/bin/wget -O -q http://www.voiceprint.co.uk/images/radio/ftp_radio_images_from_gonzo.php");
shell_exec("/usr/bin/wget -O -q http://www.gonzomultimedia.com/images/radio/ftp_radio_images_from_gonzo.php");
shell_exec("/usr/bin/wget -O -q http://www.voiceprint.co.uk/images/product_images/ftp_images_from_gonzo.php");
shell_exec("/usr/bin/wget -O -q http://www.gonzomultimedia.com/images/product_images/ftp_images_from_gonzo.php");

cron_mail("FTP scripts completed");
exit;

function cron_mail($a){
	
	$to="mattplatts@gmail.com";
	$subject="Cron Results For General Update";
	$from="gonzo_web_bot@gonzomultimedia.co.uk";
	$message="Cron results<br /><br /><b>Message:</b><br />$a<br /><br />Message sent from dir ".getcwd();
	$headers="From:$from\r\nContent-type:text/html\r\n";
	//mail($to,$subject,$message,$headers);
}

function cron_error_mail($a){

	$err_point=$a;
        $to="mattplatts@gmail.com";
        $subject="Cron Error Message";
        $from="gonzo_web_bot@gonzomultimedia.co.uk";
        $message="Cron error: $a\n - from dir ".getcwd();
        $headers="From:$from\r\nContent-type:text/html\r\n";
        mail($to,$subject,$message,$headers);
}

?>
