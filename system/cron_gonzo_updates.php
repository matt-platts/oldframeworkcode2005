<?php

chdir ("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/system/custom");
require_once("../../config.php");
require_once("$libpath/errors.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");

cron_mail("Starting General Updates");

// Artists
$db=new database_connection();
$sql="UPDATE artists SET active =0 WHERE 1";
$res=$db->query($sql) or die("Point 1 error");
$sql="UPDATE artists LEFT JOIN products ON products.artist = artists.id SET artists.active=1 WHERE products.price != \"\" AND products.price IS NOT NULL AND products.available=1";
$res=$db->query($sql) or die("Oops an error happened " . mysql_error());
cron_mail("Artists have been updated to active");

// Gebres
$sql="UPDATE genres SET products_available=0 WHERE 1";
$res=$db->query($sql) or die("Point 1 error");
$sql="UPDATE genres LEFT JOIN products ON products.genre=genres.id SET genres.products_available=(genres.products_available+1) WHERE products.price != \"\" AND products.price IS NOT NULL AND products.available=1";
$res=$db->query($sql) or die("Oops an error happened");
cron_mail("Genres have been updated to active");

//Prices
$sql="UPDATE products INNER JOIN price_formats on products.price_format = price_formats.id SET products.price=price_formats.web_price, products.price_in_dollars = price_formats.usd_price";
$res=$db->query($sql) or die("Oops an error happened");
cron_mail("Prices have been updated.");

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
