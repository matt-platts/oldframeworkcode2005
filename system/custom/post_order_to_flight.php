<?php
$userdata="";
$post_result=post_order_to_flight(251928,$userdata,$product_lines);
print $post_result;
exit;

if ($_GET['action']=="flight_stock_level"){
        print "There are <b>" . get_stock_level($_GET['stockno']) . "</b> items in stock at flight logistics."; exit;
}

function post_order_to_flight($order_number,$user_details,$product_lines){

	$url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rtOrderUpdate&OmnisLibrary=stock&OmnisServer=5912&ivOrderNumber=".$order_number."&ivCompanySeq=74&ivOrderDate=";
	$url .= "05/02/2010";
	$url .= "&ivDeliveryContactTitle=Mr";
	$url .= "&ivDeliveryContactFirstName=FirstName";
	$url .= "&ivDeliveryContactSurname=Surname";
	$url .= "&ivDeliveryAddress1=Address1";
	$url .= "&ivDeliveryAddress2=Address2";
	$url .= "&ivDeliveryAddress3=Address3";
	$url .= "&ivDeliveryTownName=TownName";
	$url .= "&ivDeliveryRegionName=RegionName";
	$url .= "&ivDeliveryPostcode=Postcode";
	$url .= "&ivDeliveryCountryName=CountryName";
	$url .= "&ivDeliveryPhone=PhoneNumber";
	$url .= "&ivDeliveryEmail=EmailAddress";
	$url .= "&ivSubTotal=10.00";
	$url .= "&ivOrderPP=5.00";
	$url .= "&ivVATFlag=YES";
	$url .= "&ivOrderVAT=2.63";
	$url .= "&ivOrderTotal=17.63";
	$url .= "&ivOrderLinesText=";
	$url .= "10GODSCD~1~9.00~9.00~~~RBR0006~2~11.00~22.00"; // stockno,qty,unit_price,line_price

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
		
		print "got number $order_number and status $status\n";
		if (preg_match("/^\d+$/",$order_number)){
			$order_no_ok=1;
		}	
		if (preg_match("/^Succesful$/",$status)){
			$status_ok=1;
		}	
		if ($status_ok && $order_no_ok){
			$return="Order Posted Successfully";
			$sql="UPDATE orders set posted_to_flight=1 WHERE id = $order_number";
		} else {
			$return="Order post failed. The returned string was: $returned_data";
		}
	}
	return $return;
}


?>
