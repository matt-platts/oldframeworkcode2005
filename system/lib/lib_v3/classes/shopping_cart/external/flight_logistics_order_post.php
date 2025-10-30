<?

class flight_logistics_order_post extends shopping_cart {

function __construct($order_details){
	global $db;
	$sql="SELECT * from flight_logistics_config WHERE active=1";
	$rv=$db->query($sql);
	$h=$db->fetch_array();
	$this->test_order=$h['test_mode']; // set to 0 to set it live
	if ($order_details['order_number']){
		$this->order_number=$order_details['order_number'];
	} else {
		$this->order_number=parent::getInternalSaleID();
	}
	$this->sku_field=$h['sku_field_in_products_table'];
	$this->company_seq=$h['company_seq'];
	$this->order_string=$this->format_order_details_for_flight();
	if ($order_details['user_id']){
		$this->user_details=$this->get_user_details_for_flight_from_user_id($order_details['user_id']);
	} else {
		$this->user_details=$this->get_user_details_for_flight();
	}
	$this->currency=$h['default_currency'];
}

function value($of){
	return $this->$of;
}

function post_order_to_flight_logistics($order_id){
	if (!$this->order_number){
		$this->order_number=$order_id;
	}
	if (!$this->order_number){
		print "Error 2UB9: No norde nunmber";
exit;
	}
	$this->do_order_post_to_flight_logistics($this->order_number,$this->user_details,$this->order_string);
}

function format_order_details_for_flight(){
	$product_lines=array();
	foreach ($_SESSION['cart'] as $item => $itemdata){
		$product_line = $this->get_flight_id_number($item);
		$product_line .= "~";
		$qty=$_SESSION['cart'][$item]['quantity'];
		$item_price=parent::get_item_price($item);
		$line_price=($item_price*$qty);
		$product_line .= $qty . "~" . $item_price . "~" . $line_price;
		array_push($product_lines,$product_line);
	}
	$product_order_string=implode("~~~",$product_lines);
	return $product_order_string;
}

function format_order_details_for_flight_by_order_number($order_id){
	$product_lines=array();
	global $db;
	$sql="SELECT product_id,quantity,price FROM order_products WHERE order_id = $order_id";
	$rv=$db->query($sql);
	while($h=$db->fetch_array($rv)){
		$product_line = $this->get_flight_id_number($h['product_id']);
		$product_line .= "~";
		$qty=$h['quantity'];
		$item_price=$h['price'];
		$line_price=($item_price*$qty);
		$product_line .= $qty . "~" . $item_price . "~" . $line_price;
		array_push($product_lines,$product_line);
	}
	$product_order_string=implode("~~~",$product_lines);
	return $product_order_string;
}

function get_user_details_for_flight(){
	global $user;
	global $db;
	$user_details=array();
	$sql="SELECT * from user WHERE id = " . $user->value("id");
	$res=$db->query($sql);
	$h=$db->fetch_array($res);
	$user_details['title']=$h['title'];
	$user_details['first_name']=$h['first_name'];
	$user_details['second_name']=$h['second_name'];
	$user_details['email']=$h['email_address'];
	$user_details['tel']=$h['telephone_no'] . " " . $h['mobile_no'];
	if ($h['same_as_billing_address']){
		$user_details['address_1']=$h['address_1'];	
		$user_details['address_2']=$h['address_2'];
		$user_details['address_3']=$h['address_3'];
		$user_details['town']=$h['county_or_state'];
		if ($h['city']){ $user_details['town'] = $h['city'] . ", " . $user_details['town']; }
		$user_details['postcode']=$h['zip_or_postal_code'];
		$user_details['country']=$h['country'];
	} else {
		$user_details['address_1']=$h['delivery_address_1'];	
		$user_details['address_2']=$h['delivery_address_2'];
		$user_details['address_3']=$h['delivery_address_3'];
		$user_details['town']=$h['delivery_county_or_state'];
		if ($h['city']){ $user_details['town'] = $h['delivery_city'] . ", " . $user_details['town']; }
		$user_details['postcode']=$h['delivery_zip_or_postal_code'];
		$user_details['country']=$h['delivery_country'];

	}
	$countrynameSQL="SELECT Name from countries WHERE ID = " . $user_details['country'];
	$cnRes=$db->query($countrynameSQL) or die("Mysql Error with country name");
	while ($hCn=$db->fetch_array($cnRes)){
		$user_details['country']=$hCn['Name'];
	}
	return $user_details;
}

function get_user_details_for_flight_from_user_id($user_id){
	if (!$user_id){
		format_error("Error: Get user details for flight from user id has been called without a user id",1);
	}
	global $user;
	global $db;
	$user_details=array();
	$sql="SELECT * from user WHERE id = " . $user_id; 
	$res=$db->query($sql);
	$h=$db->fetch_array($res);
	$user_details['title']=$h['title'];
	$user_details['first_name']=$h['first_name'];
	$user_details['second_name']=$h['second_name'];
	$user_details['email']=$h['email_address'];
	$user_details['tel']=$h['telephone_no'] . " " . $h['mobile_no'];
	if ($h['same_as_billing_address']){
		$user_details['address_1']=$h['address_1'];	
		$user_details['address_2']=$h['address_2'];
		$user_details['address_3']=$h['address_3'];
		$user_details['town']=$h['county_or_state'];
		if ($h['city']){ $user_details['town'] = $h['city'] . ", " . $user_details['town']; }
		$user_details['postcode']=$h['zip_or_postal_code'];
		$user_details['country']=$h['country'];
	} else {
		$user_details['address_1']=$h['delivery_address_1'];	
		$user_details['address_2']=$h['delivery_address_2'];
		$user_details['address_3']=$h['delivery_address_3'];
		$user_details['town']=$h['delivery_county_or_state'];
		if ($h['city']){ $user_details['town'] = $h['delivery_city'] . ", " . $user_details['town']; }
		$user_details['postcode']=$h['delivery_zip_or_postal_code'];
		$user_details['country']=$h['delivery_country'];

	}
	$countrynameSQL="SELECT Name from countries WHERE ID = " . $user_details['country'];
	$cnRes=$db->query($countrynameSQL) or die("Mysql Error with country name");
	while ($hCn=$db->fetch_array($cnRes)){
		$user_details['country']=$hCn['Name'];
	}
	return $user_details;
}

function do_order_post_to_flight_logistics($order_number,$user_details,$product_lines){

	global $db;
	$check_not_sent_sql="SELECT posted_to_flight FROM orders WHERE id = $order_number";
	$check_not_sent_rv=$db->query($sql);
	$check_not_sent_h=$db->fetch_array($check_not_sent_rv);
	if ($check_not_sent_h['posted_to_flight']=="1"){
		// DO NOT POST TO FLIGHT IF ALREADY POSTED - thought theres no reason this should happen this is extra
		// terminate immediately
		exit;
	}
	
	$local_order_number=$order_number;
        $url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rt";
	global $user;
	if ($this->test_order==1 || $user->value("id")==1){
		$url .= "Test";
	}
	$url .= "OrderUpdate&OmnisLibrary=stock&OmnisServer=5912&ivOrderNumber=".$order_number."&ivCompanySeq=".$this->value("company_seq")."&ivOrderDate=";
        $url .= date("d/m/Y"); 
        $url .= "&ivDeliveryContactTitle=" . $user_details['title'];
        $url .= "&ivDeliveryContactFirstName=" . $user_details['first_name'];
        $url .= "&ivDeliveryContactSurname=" . $user_details['second_name'];
        $url .= "&ivDeliveryAddress1=" . $user_details['address_1'];
        $url .= "&ivDeliveryAddress2=" . $user_details['address_2'];
        $url .= "&ivDeliveryAddress3=" . $user_details['address_3'];
        $url .= "&ivDeliveryTownName=";
        $url .= "&ivDeliveryRegionName=" . $user_details['town'];
        $url .= "&ivDeliveryPostcode=" . $user_details['postcode'];
        $url .= "&ivDeliveryCountryName=" . $user_details['country'];
        $url .= "&ivDeliveryPhone=" . $user_details['tel'];
        $url .= "&ivDeliveryEmail=" . $user_details['email'];
        $url .= "&ivSubTotal=" . $_SESSION['order_total'];
        $url .= "&ivOrderPP=" . $_SESSION['shipping_rate']; 
        $url .= "&ivVATFlag=NO"; // or no?
        $url .= "&ivOrderVAT=";
        $url .= "&ivOrderTotal=" . $_SESSION['grand_total'];
        $url .= "&ivOrderLinesText=";
        $url .= $product_lines; // stockno,qty,unit_price,line_price
	if ($this->value("currency")=="USD"){
		$url .= "&ivOrderCurrencySwitch=$";
	}

	$url=str_replace(" ","%20",$url);

        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $returned_data=curl_exec($ch);
        curl_close($ch);

        $order_no_ok=0;
        $status_ok=0;
        if (empty($returned_data)){
                $return="Unable to post order to Flight Logistics";
        } else {
                $fields=explode(":",$returned_data);
                $order_number=$fields[1];
                $status=$fields[2];

                if (preg_match("/^\d+$/",$order_number)){
                        $order_no_ok=1;
                }
                if (preg_match("/^Succesful/",$status)){ // note: there was a $ at the end of Successful. 
                        $status_ok=1;
                }
		// log flight logistics order details
		global $db;
		$flight_sql="INSERT INTO flight_logistics_order_post_responses (order_id,flight_order_number,flight_return_status,flight_return_string,post_time,full_posted_string) VALUES($local_order_number,\"$order_number\",\"$status\",\"$returned_data\",NOW(),\"$url\")";
		$flight_res=$db->query($flight_sql) or die("Error posting order to logistics");
                if ($status_ok && $order_no_ok){
                        $return="Order Posted Successfully";
                        $sql="UPDATE orders set posted_to_flight=1 WHERE id = $order_number";
			$res=$db->query($sql) or die ("Cannot update orders to say posted to flight");
                } else {
                        $return="Order post failed. The returned string was: $returned_data";
                }
        }
        return $return;
}

function post_existing_order_to_flight_logistics($order_number){
	$this->test_order=0;

	global $db;
	$check_not_sent_sql="SELECT * FROM orders WHERE id = $order_number";
	$check_not_sent_rv=$db->query($check_not_sent_sql);
	$h=$db->fetch_array($check_not_sent_rv);
	if ($db->num_rows($check_not_sent_rv)==0){ $return = "No order found with this number"; }
	if ($h['posted_to_flight']=="GIBBON"){
		$return['status']=0;
		$return['message']="Order has already been sent to flight logistics";
	} else {
	
		$url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rt";
		//global $user;
		//if ($this->test_order==1 || $user->value("id")==1){
			//$url .= "Test";
		//}
		$userid_from_order=$h['ordered_by'];
		$user_details=$this->get_user_details_for_flight_from_user_id($userid_from_order);
		$url .= "OrderUpdate&OmnisLibrary=stock&OmnisServer=5912&ivOrderNumber=".$order_number."&ivCompanySeq=".$this->value("company_seq")."&ivOrderDate=";
		$url .= date("d/m/Y"); 
		$url .= "&ivDeliveryContactTitle=" . $user_details['title'];
		$url .= "&ivDeliveryContactFirstName=" . $user_details['first_name'];
		$url .= "&ivDeliveryContactSurname=" . $user_details['second_name'];
		$url .= "&ivDeliveryAddress1=" . $user_details['address_1'];
		$url .= "&ivDeliveryAddress2=" . $user_details['address_2'];
		$url .= "&ivDeliveryAddress3=" . $user_details['address_3'];
		$url .= "&ivDeliveryTownName=";
		$url .= "&ivDeliveryRegionName=" . $user_details['town'];
		$url .= "&ivDeliveryPostcode=" . $user_details['postcode'];
		$url .= "&ivDeliveryCountryName=" . $user_details['country'];
		$url .= "&ivDeliveryPhone=" . $user_details['tel'];
		$url .= "&ivDeliveryEmail=" . $user_details['email'];
		$url .= "&ivSubTotal=" . $h['total_amount'];
		$url .= "&ivOrderPP=" . $h['shipping_total']; 
		$url .= "&ivVATFlag=NO"; // or no?
		$url .= "&ivOrderVAT=";
		$url .= "&ivOrderTotal=" . $h['grand_total'];
		$url .= "&ivOrderLinesText=";
		$product_lines = $this->format_order_details_for_flight_by_order_number($order_number); 
		$url .= $product_lines; // stockno,qty,unit_price,line_price
		if ($this->value("currency")=="USD"){
			$url .= "&ivOrderCurrencySwitch=$";
		}
		$url=str_replace(" ","%20",$url);

		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$returned_data=curl_exec($ch);
		curl_close($ch);

		$order_no_ok=0;
		$status_ok=0;
		if (empty($returned_data)){
			$return="Unable to post order to Flight Logistics";
		} else {
			$fields=explode(":",$returned_data);
			$order_number=$fields[1];
			$status=$fields[2];

			if (preg_match("/^\d+$/",$order_number)){
				$order_no_ok=1;
			}
			if (preg_match("/^Succesful/",$status)){ // note: there was a $ at the end of Successful. 
				$status_ok=1;
			}
			// log flight logistics order details
			global $db;
			$flight_sql="INSERT INTO flight_logistics_order_post_responses (order_id,flight_order_number,flight_return_status,flight_return_string,post_time,full_posted_string) VALUES($order_number,\"$order_number\",\"$status\",\"$returned_data\",NOW(),\"$url\")";
			$flight_res=$db->query($flight_sql) or die("Error posting order to logistics");
			if ($status_ok && $order_no_ok){
				$return['status']=1;
				$return['message']="Order Posted Successfully";
				$sql="UPDATE orders set posted_to_flight=1 WHERE id = $order_number";
				$res=$db->query($sql) or die ("Cannot update orders to say posted to flight");
			} else {
				$return['status']=0;
				$return['message']="Order post failed. The returned string was: $returned_data";
			}
		}
	}
        return $return;
}

function get_flight_id_number($product_id){
	global $db;
	$sql = "SELECT " . $this->value("sku_field") . " FROM products where id = $product_id";
	$res=$db->query($sql);
	$h=$db->fetch_array($res);
	return $h[$this->value("sku_field")];
}

}

?>
