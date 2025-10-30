<?

$sale_id=$_REQUEST['sid'];
$prize=$_REQUEST['z'];

$current_user=user_data_from_cookie("id");
$sql="SELECT * from game_results WHERE sale_id = $sale_id AND prize_id=\"\"";
$result=mysql_query($sql);

if (mysql_num_rows($result)>1){print "ERROR - game logged multiple times.";exit;}
if (!mysql_num_rows($result)){print "ERROR: SALE NOT FOUND OR ALREADY CLAIMED. Please click back and look up your game under 'My Games'.";exit;}
$select_products_sql="SELECT * FROM products WHERE id= $prize";
$result=mysql_query($select_products_sql);
while ($row=mysql_fetch_array($result,MYSQL_ASSOC)){
	$product_name = $row['name'];
}

if ($product_name){

	$update_results_with_prize="UPDATE game_results SET prize_id = $prize WHERE sale_id = $sale_id";
	$result=mysql_query($update_results_with_prize) or die ("Error logging prize request. Please contact a systems administrator<p>$update_results_with_prize<p>" . mysql_error());

	print "Your request for a prize of a <b>$product_name</b> has been recorded. <p><strong>Have you given us the correct contact details? If not please <a href=\"http://www.greenhamaearo.co.uk/site.php?s=1&content=10\">go there now</a> and enter your office address. <p>Thank You for playing <img src=\"graphics/application_images/spintext.gif\" />. Come back soon for more chances to win prizes!<p align=\"center\" style=\"text-align:center\"><a href=\"site.php?s=1&content=41\">Continue</a>";
}

?>
