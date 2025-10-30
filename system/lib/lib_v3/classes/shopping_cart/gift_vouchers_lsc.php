<?php

class gift_vouchers_complex{

	function __construct(){
		$this->add_to_total=1;

	}

	function value($of){
		return $this->$of;
	}

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }

	function itemise_at_checkout(){
	
		global $db;
		if ($_SESSION['gift_vouchers_complex_code']){
			$sql="SELECT voucher_actions.function_name from voucher_codes INNER JOIN voucher_actions ON voucher_codes.voucher_action = voucher_actions.id WHERE voucher_number = \"" . $db->db_escape($_SESSION['gift_vouchers_complex_code']) . "\" AND voucher_codes.valid_from <= NOW() AND voucher_codes.valid_to >= NOW()";
			$result=$db->query($sql);
			while ($h = $db->fetch_array($result)){
				$function_name=$h['function_name'];
				$voucher_result=$this->{$function_name}();
			}
		} else {
			$voucher_result="0.00";
		}
		return $voucher_result;
	}

	function calculate_total_discount(){

	}

	function process_enter_promotional_code($promo_code){

		global $db;
		$retval=0;
		$sql="SELECT * from voucher_codes WHERE voucher_number = \"" . $db->db_escape($promo_code) . "\" AND valid_from <= NOW() AND valid_to >= NOW()";
		$result=$db->query($sql);
		while ($h = $db->fetch_array($result)){
			$retval=1;
			$_SESSION['gift_vouchers_complex_code']=$promo_code;	
		}

		// individual voucher codes now if no global code is found
		global $user;
		if (!$retval && $user->value("id")){
			$sql="SELECT * from voucher_codes_per_user WHERE user = " . $user->value("id") . " AND voucher_used=0 AND voucher_number = \"" . $db->db_escape($promo_code) . "\" AND valid_from <= NOW() AND valid_to >= NOW()";
			$result=$db->query($sql);
			while ($h = $db->fetch_array($result)){
				$retval=1;
				$_SESSION['gift_vouchers_complex_code']=$promo_code;	
			}
			
		}
		return $retval;
	}

	#################### SPECIFIC VOUCHER FUNCTIONS #####################
	function free_shipping_on_order(){

		global $shipping_rate;
		global $mycart;
		$_SESSION['voucher_text']="Free Shipping";
		$shipping_discount_total=sprintf("%4.2f","-".$mycart->value("shipping_total"));
		$_SESSION['gift_vouchers_complex_total']=$shipping_discount_total;
		return "-" . $mycart->value("shipping_total");
	}

	function free_shipping_on_product(){

	return "0.00";
	}

	function discount_order_by_percentage(){
		global $db;
		$sql="SELECT voucher_codes.discount_percentage FROM voucher_codes WHERE voucher_number = \"" . $db->db_escape($_SESSION['gift_vouchers_complex_code']) . "\" AND voucher_codes.valid_from <= NOW() AND voucher_codes.valid_to >= NOW()";
		$result=$db->query($sql);
		while ($h = $db->fetch_array($result)){
			$disc_percentage=$h['discount_percentage'];
		}
		$discount_amount="-" . ($_SESSION['total_price']/100)*$disc_percentage;
		$_SESSION['voucher_text']=$disc_percentage . "% Off";
		$_SESSION['gift_vouchers_complex_total']=$discount_amount;
		return $discount_amount; 
	}

	function add_product_to_order(){

	return "0.00";
	}

	function discount_order_by_set_amount(){
		global $db;
		$sql="SELECT voucher_codes.discount_amount FROM voucher_codes WHERE voucher_number = \"" . $db->db_escape($_SESSION['gift_vouchers_complex_code']) . "\" AND voucher_codes.valid_from <= NOW() AND voucher_codes.valid_to >= NOW()";
		$result=$db->query($sql);
		while ($h = $db->fetch_array($result)){
			$discount_amount=$h['discount_amount'];
		}
		$_SESSION['voucher_text']="&pound;" . $discount_amount. " Off";
		$_SESSION['gift_vouchers_complex_total']=$discount_amount;
		$discount_amount="-" . $discount_amount;
		return $discount_amount; 
	}

	function generate_vouchers_on_product_combinations(){
		global $db;
		global $user;
		if (!$user){ return; } // function currently only applies to when people are logged in
		$sql="SELECT * from product_combinations_for_vouchers WHERE active=1";
		$rv=$db->query($sql);
		while ($h=$db->fetch_array($rv)){
			$product_1=$h['product_1'];
			$product_2=$h['product_2'];
			$product_3=$h['product_3'];
			$product_4=$h['product_4'];
		}	
		// now look at the current order and see if the products match up to what has been ordered
		// if so we need to generate a new random voucher code and send an email
		$unique_voucher_number=uniqid();		
		$insert_sql="INSERT INTO voucher_codes_per_user(voucher_number,user) values(\"$unique_voucher_number\",\"".$user->value("id")."\")";
		$create_voucher_result=$db->query($insert_sql);
	}

	// if purchased vouchers need a unique voucher code..
	function generate_gift_voucher_numbers(){
		$order_id=$_SESSION['order_id'];
		$list_ids=array();
		foreach ($_SESSION['cart'] as $item=>$itemdata){
			array_push($list_ids,$itemdata['product_id']);
		}
		global $db;
		global $user;
		$sql = "SELECT products.ID as id, products.name, products.category, products.feature_page_top_left_image AS image FROM products INNER JOIN product_categories ON products.category = product_categories.id WHERE products.ID IN (".join(",",$list_ids).")";
		//print "<p>Running $sql</p>";
		$res=$db->query($sql);
		//print "got " . $db->num_rows($res) . " rows.";
		$voucher_links=array();
		while ($h=$db->fetch_array($res)){
		//var_dump($h);
			print "<p> - on category " . $h['category'] . "</p>";
			if ($h['category']==3 || $h['category']==4){
				//print "category is 3 or 4 - actually it is " . $h['category'] . "!<br />";
				foreach ($_SESSION['cart'] as $cartitem => $cartitemdata){
					if ($cartitemdata['product_id']==$h['id']){

						$qty=$_SESSION['cart'][$cartitem]['quantity'];
						$price=$_SESSION['cart'][$cartitem]['price'];
						$product=$_SESSION['cart'][$cartitem]['product_id'];
						$product_name=$h['name'];
						$product_image=$h['image'];
						//print "GOT PRODUCT OF $product";

						for ($i=0;$i<$qty;$i++){
							$unique_voucher_number=strtoupper(uniqid());
							$bits=explode(" ",str_replace("0.","",microtime()));
							$vn=$bits[1] . $bits[0];
							$vn = chunk_split($vn, 4, ' ');
							$vn=trim(substr_replace($vn,"",-3));

							$insert_sql='INSERT INTO restaurant_voucher_codes (voucher_number,serial_number,product,discount_amount,purchased_by,date_purchased,expiry_date,order_number,type,used) VALUES("'.$vn.'","'.$unique_voucher_number.'",'.$product.','.$price.','.$user->value("id").',NOW(),DATE_ADD(NOW(), INTERVAL 1 YEAR),"'.$order_id.'","Purchased",0)';
							//print "VOUCHER NUMBERS GENERATED<br />" . $insert_sql;
							$insert_rv=$db->query($insert_sql);
							$voucherlink = HTTP_PATH . "/vouchers/".str_replace(" ","",$vn)."-" . $unique_voucher_number.".pdf";
							$voucher_link_data['link']=$voucherlink;
							$voucher_link_data['vn']=$vn;
							$voucher_link_data['price']=$price;
							$voucher_link_data['expiry']=date("d M Y",strtotime('+1 year'));
							$voucher_link_data['product_name']=$product_name;
							$voucher_link_data['image']=$product_image;
							array_push($voucher_links,$voucher_link_data);
						}
					}
				}
			}
		}

		//print_r($voucher_links);
		if (count($voucher_links)>0){
			$mailcontent="Dear " . $user->value("full_name") . "<br /><br />\n\n";
			$mailcontent .= "The enclosed mail contains your gift voucher codes, which you will need to present when you book and when you come to one of our restaurants. <br />Click on the links to download a PDF with a printable image of your voucher.<br /><br />\n\n";
			$mailcontent .= "<table>";
			foreach ($voucher_links as $voucherlink){
				$mailcontent .= "<tr><td><img src=\"http://www.londonsteakhousecompany.com/images/feature_pages/top_left_images/" . $voucherlink['image']."\" width=\"150\" height=\"150\" /></td><td>";
				$mailcontent .= "<b>".$voucherlink['product_name'] . " - &pound;".$voucherlink['price']." </b><br /><b>Serial No.</b> ".$voucherlink['vn']."<br /><b>Expires </b>: ".$voucherlink['expiry'] . "<br /><b>Link:</b> <a href=\"".$voucherlink['link']."\">".$voucherlink['link']."</a><br /><br />\n";
				$mailcontent .= "</td></tr>";
			}
			$mailcontent .= "</table>";
			$mailcontent .= "These gift vouchers are valid for one full year." . "<br /><br />\n\n";
			$mailcontent .= "<b>BOOKING INSTRUCTIONS</b><br /><br />Please call our restaurants on the correct number below to book a table. You will need to give the voucher number when you book, and also at the restaurant on the night. You can either print or show this email, or download the PDF above for a printable voucher which you can then give as a gift!<br /><br />\n\n";
			$mailcontent .= "<br /><br />";
			$headers="From:no-reply@londonsteakhousecompany.com\r\nContent-type:text/html\r\n\r\n"; 
			mail($user->value("email_address"),"Your London Steakhouse Company Gift Vouchers are enclosed.",$mailcontent,$headers);
		} else {
			// no vouchers here
		}
	}

	function generate_vouchers_on_order_price(){
		global $db;
		global $user;
		$order_id=$_SESSION['order_id'];
		if (!$user){ return;}
		$sql = "SELECT order_products.product_id,order_products.quantity,order_products.price,products.category FROM order_products INNER JOIN products ON order_products.product_id = products.id WHERE order_id = $order_id AND products.category = \"1\"";
		$rv=$db->query($sql);
		//print "DB - got an rv of $rv";
		//print $sql;
		$order_total=0;
		while ($h=$db->fetch_array()){
			//var_dump($h);
			$order_total+=$h['price']*$h['quantity'];
		}
		$number_of_vouchers=$order_total/50;
		$number_of_vouchers=floor($number_of_vouchers-($number_of_vouchers%1));

		$get_discount_amount = floor($order_total/50)*50;
		$total_discount=$number_of_vouchers*5;
		$msg = "Discount amount from order total of $order_total is $get_discount_amount. No of v is $number_of_vouchers. This many at 5 each is $total_discount";
		$year_from_today=date("d M Y",strtotime('+1 years'));
		$unique_voucher_number=strtoupper(uniqid());
  		$bits=explode(" ",str_replace("0.","",microtime()));
  		$vn=$bits[1] . $bits[0];
 		$vn = chunk_split($vn, 4, ' ');
 		$vn=trim(substr_replace($vn,"",-3));
		// log this voucher then
		if ($total_discount && $total_discount > 0){
			$insert_sql='INSERT INTO restaurant_voucher_codes (voucher_number,serial_number,discount_amount,purchased_by,date_purchased,expiry_date,order_number,type,used) VALUES("'.$vn.'","'.$unique_voucher_number.'",'.$total_discount.','.$user->value("id").',NOW(),DATE_ADD(NOW(), INTERVAL 3 MONTH),"'.$order_id.'","Complimentary",0)';
			$insert_rv=$db->query($insert_sql);
			$msg .= "\n\n<hr size='1'>\n\n$insert_sql\n\n";
			//print $msg;
			$voucherlink = "/vouchers/".str_replace(" ","",$vn) . "-" . $unique_voucher_number.".pdf";

			$mailcontent="Dear " . $user->value("full_name") . "<br /><br />\n\n";
			$mailcontent .= "Thank you for your recent purhcase from the London Steakhouse Company. We are delighted to be able to send you a complimentary gift voucher for &pound;$total_discount, which can be used at any of our three London Restaurants.<br /><br />\n\n";
			$mailcontent .= "Your voucher code is: $vn<br /><br />\n\n";
			$ex_date=date("d M Y",strtotime('+3 months'));
			$mailcontent .= "This voucher is valid for three months, until $ex_date." . "<br /><br />\n\n";
			$mailcontent .= "BOOKING INSTRUCTIONS<br /><br />You simply need to give the voucher number when you book, and also bring it to the restaurant on the night.<br />If you would like to download a printable voucher, please go to http://www.londonsteakhousecompany.co.uk$voucherlink." . "<br /><br />\n\n";
			$mailcontent .= "WITH COMPLIMENTS FROM THE LONDON STEAKHOUSE COMPANY.<br /><br />";
			$headers="From:no-reply@londonsteakhouseompany.com\r\nContent-type:text/html\r\n\r\n"; 
			mail($user->value("email_address"),"Your free restaurant vouchers from the London Steakhouse Company",$mailcontent,$headers);
		}
	}


	function store($order_no,$module_amount){
		// log the amount of the voucher
		if (!$module_amount){
			$module_amount=$this->itemise_at_checkout();
		}
		$order_no = "1";
		$sql="INSERT INTO order_total_extras (order_id,module,amount) values(\"\",\"gift_vouchers_complex\",$module_amount)";
		global $db;
		$rv=$db->query($sql);
		return 1;
	}

	function record_payment(){
		return;
	}
// END SHOPPING CART CLASS
}


?>
