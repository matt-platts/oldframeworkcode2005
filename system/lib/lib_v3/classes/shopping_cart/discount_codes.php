<?php

class discount_codes{

	function __construct(){
		$this->add_to_total=1;
		$this->add_to_stored_grand_total=1;
		$this->always_display_if_post_text=0;
		$this->itemise_as_part_payment=0;
		$this->name="discount_codes";
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
		if ($_SESSION['promo_code']){

			$function_name = "";

			$first_sql="SELECT * from voucher_codes WHERE voucher_number = \"". $db->db_escape($_SESSION['promo_code']) . "\" AND voucher_codes.valid_from <= NOW() AND voucher_codes.valid_to >= NOW()";
			$result=$db->query($first_sql);
			while ($h = $db->fetch_array($result)){
				if ($h['discount_percentage'] && (!$h['discount_amount'] || $h['discount_amount']<=0)){
					$function_name="discount_order_by_percentage";
				} else if ($h['discount_amount'] && (!$h['discount_percentage'] || $h['discount_percentage'] <=0)){
					$function_name="discount_order_by_set_amount";
				} else {

				}
			}

			if ($function_name){
					$promo_code_result=$this->{$function_name}();
					$this->itemise_as_part_payment=0;
			} else {


				$sql="SELECT voucher_actions.function_name from voucher_codes INNER JOIN voucher_actions ON voucher_codes.voucher_action = voucher_actions.id WHERE voucher_number = \"" . $db->db_escape($_SESSION['promo_code']) . "\" AND voucher_codes.valid_from <= NOW() AND voucher_codes.valid_to >= NOW()";
				//$sql="SELECT voucher_actions.function_name from restaurant_voucher_codes INNER JOIN voucher_actions ON restaurant_voucher_codes.voucher_action = voucher_actions.id WHERE voucher_number = \"" . $db->db_escape($_SESSION['promo_code']) . "\" AND restaurant_voucher_codes.expiry_date >= NOW() AND (restaurant_voucher_codes.voided != 1 OR restaurant_voucher_codes.voided IS NULL) AND (restaurant_voucher_codes.completed_by IS NULL or restaurant_voucher_codes.completed_by=\"\")";
				//$result=$db->query($sql);
				//while ($h = $db->fetch_array($result)){
				//	$function_name=$h['function_name'];
					// override for lsc
					$function_name="discount_order_by_set_amount";
					$promo_code_result=$this->{$function_name}();
					$this->itemise_as_part_payment=0;
				//}
			}
		} else {
			$promo_code_result="0.00";
		}
		return $promo_code_result;
	}

	function itemise_after_total(){
	
		global $db;
		if ($_SESSION['promo_code']){
			$sql="SELECT voucher_actions.function_name from voucher_codes INNER JOIN voucher_actions ON voucher_codes.voucher_action = voucher_actions.id WHERE voucher_number = \"" . $db->db_escape($_SESSION['promo_code']) . "\" AND voucher_codes.valid_from <= NOW() AND voucher_codes.valid_to >= NOW()";
			//$sql="SELECT voucher_actions.function_name from restaurant_voucher_codes INNER JOIN voucher_actions ON restaurant_voucher_codes.voucher_action = voucher_actions.id WHERE voucher_number = \"" . $db->db_escape($_SESSION['promo_code']) . "\" AND restaurant_voucher_codes.expiry_date >= NOW() AND (restaurant_voucher_codes.voided != 1 OR restaurant_voucher_codes.voided IS NULL) AND (restaurant_voucher_codes.completed_by IS NULL or restaurant_voucher_codes.completed_by=\"\")";

			$result=$db->query($sql);
			while ($h = $db->fetch_array($result)){
				$function_name=$h['function_name'];
				$promo_code_result=$this->{$function_name}();
			}
			if ($promo_code_result==0){
				// the error message below is not YET passed anywhere and is purely to illustrate the condition in the line above.
				$errmsg="Please note that whilst you have entered a valid promo code, this code does not discount your order total.";
			}
		} else {
			$promo_code_result="0.00";
		}
		return $promo_code_result;
	}

	function calculate_total_discount(){

	}

	function process_enter_promotional_code($promo_code){

		global $db;
		$retval=0;
		$promo_code=str_replace(" ","",trim($promo_code));
		$promo_code = chunk_split($promo_code, 4, ' ');
		if (strlen($promo_code)>32){ print format_error("Promotional code is too long, or incorrectly formatted. Please use your back button to try again.",0); exit;}
		$sql="SELECT * from restaurant_voucher_codes WHERE voucher_number = \"" . $db->db_escape($promo_code) . "\" AND type=\"Purchased\" AND expiry_date >= NOW() AND (used=\"\" OR used IS NULL OR used = 0) AND (voided != 1 OR voided IS NULL) AND product > 0";

		$result=$db->query($sql);
		while ($h = $db->fetch_array($result)){

			if (!$multiple_use_error){
				$retval=1;
				$_SESSION['promo_code']=$promo_code;	
			}

			/*$_SESSION['gift_vouchers_complex_type']="payment"; // THE 2 TYPES WILL BE PAYMENT AND DISCOUNT. Vouchers added as payment do not discount the order but are seen as a method of payment instead.
			if ($_SESSION['gift_vouchers_complex_type']=="payment"){
				$_SESSION['gift_vouchers_complex']['itemise_as_part_payment']=1;
			} else {
				$_SESSION['gift_vouchers_complex']['itemise_as_part_payment']=1;
			}
			*/
		}

		if (!$retval){
			$promo_code=str_replace(" ","",trim($promo_code));
			$sql = "SELECT * FROM voucher_codes WHERE voucher_number = \"".$db->db_escape($promo_code) . "\" AND valid_from <= NOW() AND valid_to >= NOW() AND active=1";
			$result=$db->query($sql);
			while ($h = $db->fetch_array($result)){

				// check single codes used
				if ($h['single_use_per_user']){
					global $user;
					$usesql="SELECT * FROM promo_codes_used WHERE user = " . $user->value("id") . " AND promo_code = " . $_SESSION['promo_code']; 
					$userv=$db->query($usesql);
					if ($db->num_rows($userv)){
						$multiple_use_error=1;
					}
				}

				if (!$multiple_use_error){
					$retval=1;
					$_SESSION['promo_code']=$promo_code;
				}
			}
			
		}

		// individual voucher codes now if no global code is found
		global $user;
		if (!$retval && $user->value("id")){
			$sql="SELECT * from voucher_codes_per_user WHERE user = " . $user->value("id") . " AND voucher_used=0 AND voucher_number = \"" . $db->db_escape($promo_code) . "\" AND valid_from <= NOW() AND valid_to >= NOW()";
			$result=$db->query($sql);
			while ($h = $db->fetch_array($result)){
				$retval=1;
				$_SESSION['promo_code']=$promo_code;	
			}
			
		}
		return $retval;
	}

	#################### SPECIFIC VOUCHER FUNCTIONS #####################
	function free_shipping_on_order(){

		global $shipping_rate;
		global $mycart;
		$_SESSION['promo_text']="Free Shipping";
		$shipping_discount_total=sprintf("%4.2f","-".$mycart->value("shipping_total"));
		$_SESSION['discount_codes_total']=$shipping_discount_total;
		return "-" . $mycart->value("shipping_total");
	}

	function free_shipping_on_product(){
		return "0.00";
	}

	function discount_order_by_percentage(){
		global $db;
		$sql="SELECT discount_percentage, apply_to_single_product_only, apply_to_products_in_category FROM voucher_codes WHERE voucher_number = \"" . $db->db_escape($_SESSION['promo_code']) . "\" AND active=1 AND voucher_codes.valid_from <= NOW() AND voucher_codes.valid_to >= NOW()";
		$result=$db->query($sql);
		while ($h = $db->fetch_array($result)){
			$disc_percentage=$h['discount_percentage'];
			$voucher_category=$h['apply_to_products_in_category'];
			$voucher_product=$h['apply_to_single_product_only'];
		}


		$total_price=0;
		foreach ($_SESSION['cart'] as $cartlineid=>$itemdata){
			$apply_to_product=0;
			$prod_sql="SELECT products.name, price, price_ex_vat, vat, products.category, category_name FROM products INNER JOIN product_categories ON products.category=product_categories.id where products.ID = " . $itemdata['product_id'];
			$prod_rv=$db->query($prod_sql);
			while ($ph=$db->fetch_array()){
				if ($voucher_product){
						if ($itemdata['product_id']==$voucher_product){
							$apply_to_product=1;
							$apply_to_product_name=$ph['name'];
						}			
				} else if ($voucher_category){
						if ($ph['category']==$voucher_category){
							$apply_to_product=1;
							$apply_to_product_category_name=$ph['category_name'];
						}			

				} else {
					$apply_to_product=1;
				}

				
				if ($apply_to_product){
				       $total_price += $ph['price']*$itemdata['quantity'];
				       $vat_amount += sprintf("%4.2f",$ph['vat']*$itemdata['quantity']);
				}
		       }
		}

		
		$remove_vat=(($vat_amount/100)*10);
		$discount_amount=($total_price/100)*$disc_percentage;
	
		if ($discount_amount>0){
			$_SESSION['promo_text']=$disc_percentage . "% Off ";
			if ($voucher_product){ $_SESSION['promo_text'] .= $apply_to_product_name;}
			if ($voucher_category){ $_SESSION['promo_text'] .= $apply_to_product_category_name;}
			$_SESSION['discount_codes_total']=$discount_amount;
			$_SESSION['promo_discount_vat_by']=$remove_vat;
		} else {
			// code entered but nothing to discount!
			unset($_SESSION['discount_codes_total']);
			unset($_SESSION['promo_discount_vat_by']);
			unset($_SESSION['promo_text']);
			$disount_amount=0;
		}

		$discount_amount="-".$discount_amount;
		return $discount_amount; 
	}

	function add_product_to_order(){
		return "0.00";
	}

	function discount_order_by_set_amount(){
		global $db;
		$sql="SELECT restaurant_voucher_codes.discount_amount FROM restaurant_voucher_codes WHERE voucher_number = \"" . $db->db_escape($_SESSION['promo_code']) . "\" AND (restaurant_voucher_codes.voided != 1 OR restaurant_voucher_codes.voided IS NULL) AND restaurant_voucher_codes.expiry_date >= NOW() AND (restaurant_voucher_codes.used IS NULL or restaurant_voucher_codes.used=\"\" OR restaurant_voucher_codes.used=0)";
		$result=$db->query($sql);
		while ($h = $db->fetch_array($result)){
			$discount_amount=$h['discount_amount'];
		}
		$_SESSION['promo_text']="&pound;" . $discount_amount. "";
		$_SESSION['discount_codes_total']=$discount_amount;
		$discount_amount="-" . $discount_amount;
		
		// Have we gone into a negative amount?
		if ($_SESSION['grand_total']<0){
		}
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
		$create_promo_code_result=$db->query($insert_sql);
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
			//print "<p> - on category " . $h['category'] . "</p>";
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
			$mailcontent .= "Thank you very much for purchasing London Steakhouse Company Gift Vouchers which are attached.  <br /><br />To redeem the voucher please call the restaurant that you would like to dine in. You will need your unique serial number to book, which is printed on your voucher.<br /><br />Further terms and conditions are printed on the voucher.<br /><br />We look forward to welcoming you to the London Steakhouse Company soon.\n\n";
			$mailcontent .= "<table>";
			foreach ($voucher_links as $voucherlink){
				$mailcontent .= "<tr><td><img src=\"http://www.londonsteakhousecompany.com/images/feature_pages/top_left_images/" . $voucherlink['image']."\" width=\"70\" height=\"70\" style=\"width:70px; height:70px\" /></td><td>";
				$mailcontent .= "<b>".$voucherlink['product_name'] . " - &pound;".$voucherlink['price']." </b><br /><b>Serial No.</b> ".$voucherlink['vn']."<br /><b>Expires </b>: ".$voucherlink['expiry'] . "<br /><b>PDF:</b> <a href=\"".$voucherlink['link']."\">".$voucherlink['link']."</a><br /><br />\n";
				$mailcontent .= "</td></tr>";
			}
			$mailcontent .= "</table>";
			$mailcontent .= "<br /><br />\n\n";
			$mailcontent .= "Thank you for shopping with the London Steakhouse Company!<br /><br />";
			$mailcontent .= $db->db_quick_match("widgets","widget","dbf_key_name","general_email_footer");
			$headers="From: \"The London Steakhouse Company\" <no-reply@londonsteakhouseompany.com>\r\nBcc: orderconfirmations@londonsteakhousecompany.com\r\nContent-type:text/html\r\n\r\n"; 
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
			$mailcontent .= "Thank you for your recent purhcase from the London Steakhouse Company. We are delighted to be able to send you a complimentary discount voucher for &pound;$total_discount, which can be used at any of our three London Restaurants.<br /><br />\n\n";
			$mailcontent .= "Your discount voucher code is: $vn<br /><br />\n\n";
			$ex_date=date("d M Y",strtotime('+3 months'));
			$mailcontent .= "This discout voucher is valid for three months, until $ex_date." . "<br /><br />\n\n";
			//$mailcontent .= "If you would like to download a printable voucher, please go to http://www.londonsteakhousecompany.co.uk$voucherlink." . "<br /><br />\n\n";
			$mailcontent .= "Reservations must be made by telephoning the restaurant in which you would like to dine (MPW Steak & Alehouse on 020 7247 5050, Kings Road Steakhouse & Grill on 020 7351 9997, Sydney Street Grill on 020 7352 3435).   You will need the unique serial number printed on your voucher to make a booking and this can be used only once. All bookings are subject to availability.<br /><br /> 
No change is given should the final bill be less than the cash value of the voucher.<br /><br /> 
You may use the Gift Voucher in conjunction with any London Steakhouse Company menu as advertised on our website including the a la carte.<br /><br />
Normal reservation booking terms & conditions apply.<br /><br />";
			$mailcontent .= "WITH COMPLIMENTS FROM THE LONDON STEAKHOUSE COMPANY.<br /><br />";
			$mailcontent .= $db->db_quick_match("widgets","widget","dbf_key_name","general_email_footer");
			$headers="From: \"The London Steakhouse Company\" <no-reply@londonsteakhouseompany.com>\r\nContent-type:text/html\r\n\r\n"; 
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
	
	function set_vouchers_as_used(){
		
		if ($_SESSION['promo_code']){
			global $db;
			global $user;
			$sql = "INSERT INTO promo_codes_used (order_no,user,promo_code) VALUES(".$_SESSION['order_id'].",".$user->value("id") . ",\"".$_SESSION['promo_code'] . "\")";
			$rv=$db->query($sql);
			return 1;
		}

	}

	function record_payment(){
		return;
	}

        function post_text(){
                if (!$_SESSION['promo_code']){
                        //$return="&nbsp;<a href=\"site.php?content=258&mt=31\" rel=\"#inthebag_overlay\"><span style=\"font-size:10px\">Cancel Vouchers</span></a>";
			$return="";
                } else {
                        $return="&nbsp;<a href=\"site.php?action=cancel_promo_code\"><span style=\"font-size:11px\">Remove Code</span></a>";
                }
         //       $return .= " | <a href=\"site.php?action=explain_vat_relief\"<spa style=\"font-size:10px\">Help</span></a>";
                return $return;
        }

	function clear_down_promo_codes(){
		unset($_SESSION['promo_code']);
		//unset($_SESSION['gift_vouchers_complex_type']);
		//unset($_SESSION['discount_codes_total']);
		//unset($_SESSION['gift_vouchers_complex']);
		unset($_SESSION['promo_text']);
		//unset($_SESSION['payment_method']);
		//ounset($_SESSION['total_for_further_payment']);
		//unset($_SESSION['voucher_only_payment']);
		unset($_SESSION['promo_discount_vat_by']);
		unset($_SESSION['discount_codes_total']);
	}

// END SHOPPING CART CLASS
}


?>
