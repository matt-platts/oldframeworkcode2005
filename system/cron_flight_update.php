<?php

//cron_mail(0);
//if (!file_exists("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/system/custom/flight_logistics_bulk_stock_check.php")) {cron_error_mail("file does not exist");}
//include_once("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/system/custom/flight_logistics_bulk_stock_check.php") or cron_error_mail("unable to include once");
$url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rtStockLevels&OmnisLibrary=Stock&OmnisServer=5912&ivCompanySeq=74";
$flight_external_data_field="catalogue_number";
// line below ujpdated for ROOT crontab - added /var/www/vhosts/gonzomultimedia.co.uk/ where previously it only said httpdocs/system/custom
chdir ("/var/www/vhosts/gonzomultimedia.co.uk/httpdocs/system/custom");
require_once("../../config.php");
require_once("$libpath/errors.php");
require_once("$libpath/classes/database.php");
require_once("$libpath/classes/user.php");
require_once("$libpath/classes/shopping_cart.php");
if (!$db && !$mycart){
	$db=new database_connection();
	$mycart=new shopping_cart();
}
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$returned_data=curl_exec($ch);
curl_close($ch);
//cron_mail("after CURL");
if (empty($returned_data)){
	cron_mail("Unable to connect to flight logistics");
} else {

	$data_pairs=explode("~~~",$returned_data);
	$count=0;
		$messagetext = "";
	foreach ($data_pairs as $data_pair){
		print $data_pair;
		$message="";
		@list($catno,$qty)=explode("~",$data_pair);
		if (!$catno || !$qty){ print "\n"; continue; }
		//print $catno . "-".$qty."\n";
		$sql="\nSELECT ID,price,hidden,stock_quantity,quantity_in_stock from products where $flight_external_data_field = \"$catno\"";
		$res=$db->query($sql);
		$h=$db->fetch_array($res);
		$product_id=$h['ID'];
		$price=$h['price'];
		$previous_quantity=$h['quantity_in_stock'];
		$hidden=$h['hidden'];
		if (!$product_id && $qty > 0 || $product_id==""){
			$message =  "$catno : Flight Logistics returned data for the product code $catno, but no such product exists in the local database.<br />\n";
			$messagetext .= $message;
			print "\n";
			continue;
		}
		$num_qty=$qty;
		$available=1;
		$available_text="Available";
		if (!preg_match("/^\d+$/",$num_qty)){ $num_qty=0; $available=0; $available_text = "Not Available"; $message="$catno set to unavailable as no numberic preg match<br />\n";}
		if ($qty==0){$available=0;}
		if ($hidden==1){$available=0; $message .= "THE FOLLOWING IS HIDDEN:";}
		if ($price==0 || $price==""){
			$available=0;
			$available_text="<span style=\"color:#cc0000\">Not Available (No Local Price Set)</span>";
		}
		if ($available){
			$preorders_extra=", allow_pre_orders=0";
		} else {
			$preorders_extra="";
		}

		print " - $previous_quantity : $num_qty\n";
		if ($previous_quantity==0 && $num_qty>0){
			print "its gone up";
			check_mailing_list($product_id);
		}

		$sql="UPDATE products SET stock_quantity = '$qty', quantity_in_stock = $num_qty, available = $available, in_stock = $available $preorders_extra WHERE ID = $product_id";
		$res=$db->query($sql);
		$message .= "$catno : Set quantity to $qty (Numeric:$num_qty, Local Id: $product_id) and available to $available_text<br />\n$sql<br />\n";
		$messagetext .= $message;
		$count++;
	}
cron_mail("Products imported: $count:<br />$messagetext");
}
//cron_mail(1);
exit;

function cron_mail($a){
	
	$to="mattplatts@gmail.com";
	$subject="Gonzo Product Update Cron Results";
	$from="gonzo_web_bot@gonzomultimedia.co.uk";
	$message="Cron results<br /><br /><b>Message:</b><br />$a<br /><br />Message sent from dir ".getcwd();
	$headers="From:$from\r\nContent-type:text/html\r\n";
	mail($to,$subject,$message,$headers);
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

function check_mailing_list($product){
	global $db;
	$sql="SELECT shopping_cart_mailinglist_data.user_id AS userID, 
artists.artist, products.title, products.id, products.image, product_formats.format, products.price, products.full_description, 
user.first_name, user.second_name, user.email_address  
FROM products 
INNER JOIN artists on products.artist=artists.id 
INNER JOIN product_formats ON products.format=product_formats.id 
INNER JOIN shopping_cart_mailinglist_data ON artists.id = field_as_category_value 
INNER JOIN user ON shopping_cart_mailinglist_data.user_id = user.id 
WHERE products.id=$product GROUP BY shopping_cart_mailinglist_data.user_id;";

	//print "\n" . $sql . "\n";
	$rv=$db->query($sql);
	while ($h=$db->fetch_array()){

		// already purchased?
		$already_purchased=0;
		$sql="SELECT orders.id FROM orders INNER JOIN order_products ON orders.id = order_products.order_id WHERE order_products.product_id=$product AND orders.ordered_by = " . $h['userID'] . " AND orders.complete=1";
		$rv2=$db->query($sql);
		while ($hash=$db->fetch_array()){
//var_dump($hash);
			$already_purchased=1;
		}
		if ($already_purchased){ 
			print "ALREADY HAD THIS ONE BOUGHT on id " . $hash['id']; 
			continue; 
		}

		$h['productname']=str_replace(" ","-",$h['artist']) . "_" . str_replace(" ","-",$h['title']);
		//var_dump($h);
		//print "its there!";
		$artist=$h['artist'];
		$title=$h['title'];
		$email=$h['email_address'];
		$templatesql="SELECT * FROM templates WHERE id=2034";
		$rv=$db->query($templatesql);
		while ($t=$db->fetch_array()){
			$template=$t['template'];

			$res=preg_match_all("/{=\w+}/",$template,$matches);
			foreach ($matches[0] as $thismatch){
				//print "GOT A MATCH AND ITS " . $thismatch . "\n";
				$original_match=$thismatch;
				$replace=preg_replace("/{=/","",$thismatch);
				$replace=preg_replace("/}/","",$replace);
				$replace=$h[$replace];
				$template=str_replace($original_match,$replace,$template);
				//print $string . "\n";
			}

			$headers="From:website@gonzomultimedia.co.uk\r\nContent-type:text/html\n\n";
			mail($email,"$artist mailing list at Gonzo Multimedia - $title is now in stock",$template,$headers);
			//print "SENT MAIL to $email for $artist mailing list\n";
		}
	}
}
?>
