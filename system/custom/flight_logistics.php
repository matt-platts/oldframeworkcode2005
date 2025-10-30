<?php

//$stock_level_result=get_stock_level("");
//
//print "Returned value is " . $stock_level_result;
//exit;

if ($_GET['action']=="flight_stock_level"){
	print "<p>There are <b>" . get_stock_level($_GET['stockno']) . "</b> items in stock at flight logistics.</p>";
	print "<p>Generated from the following query: ";
	$url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rtStockFind&OmnisLibrary=stock&OmnisServer=5912&ivCompanySeq=74&ivStockCode=".$_GET['stockno'] . "!";
	print "$url</p>";
	exit;

}

if ($_GET['action']=="post_existing_order_to_flight_logistics" && $_GET['order_id']){
	global $user;
	if (!$user){ print "No user!"; exit; }
	global $mycart;
	$order_id=mysql_real_escape_string($_GET['order_id']);

	if ($_GET['confirm_post']){
		print "post order number $order_id";
		
		require_once(LIBPATH."/classes/shopping_cart.php");
		$mycart = new shopping_cart();
		require_once(LIBPATH."/classes/flight_logistics_order_post.php");
		$flight_module=new flight_logistics_order_post(); 
		$result=$flight_module->post_existing_order_to_flight_logistics($order_id);
		var_dump($result);
		
	} else {
		global $db;
		$sql="SELECT posted_to_flight FROM orders WHERe id = " . $_GET['order_id'];
		$rv=$db->query($sql);
		$h=$db->fetch_array($rv);
		if ($h['posted_to_flight']){
			$msg="Please Note: This order has already been posted to flight logistics.";
		} else {
			$msg="<b>Order $order_id: </b>This order has not yet been posted to flight logistics.";
		}
		print "<p>$msg</p>";
		?>
		<p>Do you really want to post this order to Flight Logistics now?</p>
		<p><a href="<?php echo $_SERVER['PHP_SELF'];?>?action=post_existing_order_to_flight_logistics&order_id=<?php echo $_GET['order_id'];?>&confirm_post=1">Post Now</a></p>
		<?php
	}
}


function get_stock_level($stockno){

	$url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rtStockFind&OmnisLibrary=stock&OmnisServer=5912&ivCompanySeq=74&ivStockCode=".$stockno;

	$ch=curl_init();
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$returned_data=curl_exec($ch);
	curl_close($ch);

	if (empty($returned_data)){
		$return="Unable to get stock level";
	} else {
		if (preg_match("/Status=\d+~\w+/",$returned_data)){
			$stock_level=str_replace("Status=","",$returned_data);
			$stock_level=preg_replace("/~\w+/","",$stock_level);
			return $stock_level;
		} else {
			$returned_data=str_replace("Status=","",$returned_data);
			return $returned_data;
		}
	}
}

?>
