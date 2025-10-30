<?php

class volume_discount{
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
	
		$calculate_volume_discount=0;
		global $db;
		global $user;
		$sql="SELECT restrict_to_user_type FROM volume_discount_options";
		$rv=$db->query($sql);
		$h1=$db->fetch_array($sql);
		if ($h1['restrict_to_user_type']){
			$user_types=",".$h1['restrict_to_user_type'].",";
			$current_user_type=",".$user->value("type") . ",";
			if (stristr($user_types,$current_user_type)){
				$calculate_volume_discount=1;
			}
			
		} else {
			$calculate_volume_discount=1;
		}
		if ($calculate_volume_discount){
			$discount_level=$db->db_quick_match("user","discount_band","id",$user->value("id")); //gives us numeric discount level
			$running_discount=0;
			if (!$discount_level){ return 0; }
			//loop through cart
			foreach ($_SESSION['cart'] as $cartitem => $cartvalues){
				$line_qty=$cartvalues['quantity'];
				// look up product and discount band
				$sql="SELECT MAX(discount_percentage) AS dp from product_discounts WHERE discount_level=$discount_level AND product = $cartitem AND quantity <=$line_qty";
				$rv=$db->query($sql);
				$h=$db->fetch_array($rv);
				$discount_percent=$h['dp'];
				if ($discount_percent){
					// get price of product
					$product_price=$db->db_quick_match("products","wholesale_price","id",$cartitem);
					$line_price=$product_price*$line_qty;
					$discounted_line_price=$line_price-(($line_price/100)*$discount_percent);
					$actual_discount=($line_price/100)*$discount_percent;
					$running_discount = $running_discount+$actual_discount;	
				}
			}
			$volume_discount=-$running_discount;
		}
		$_SESSION['volume_discount_total']=$volume_discount;
		return $volume_discount;
	}
}
?>
