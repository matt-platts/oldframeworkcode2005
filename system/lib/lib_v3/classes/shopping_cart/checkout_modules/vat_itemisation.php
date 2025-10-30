<?php

class vat_itemisation {


	function __construct(){
		$this->add_to_total=0;
		$this->name="VAT Itemisation";
	}

	function value($of){
		return $this->$of;
	}

        function set_value($of,$to){
                $this->$of=$to;
                return 1;
        }

	function itemise_at_checkout(){

		$calculate_vat=0;
		$include_shipping=0;
		global $db;
		global $user;
		$sql="SELECT restrict_to_user_type FROM vat_itemisation_options";
		$rv=$db->query($sql);
			while ($h1=$db->fetch_array($rv)){
			if ($h1['restrict_to_user_type']){
				$user_types=",".$h1['restrict_to_user_type'].",";
				$current_user_type=",".$user->value("type") . ",";
				if (stristr($user_types,$current_user_type)){
					$calculate_vat=1;
				}
				
			} else {
				$calculate_vat=1;
			}
		}
	$calculate_vat=1;

		if ($calculate_vat){
			// look at other checkout modules too..
		        $sql="SELECT name,key_name,checkout_itemisation_text,class_file from checkout_modules WHERE active=1 ORDER BY ordering";
			$rv=$db->query($sql);
			$take_into_account=0;
			/*
			while ($h=$db->fetch_array($rv)){
				if ($h['key_name'] != "vat_itemisation"){
					$class_file=LIBPATH."/classes/".$h['class_file'];
					require_once($class_file);
					$checkout_mod_class = new $h['key_name'];
					$this_total = $checkout_mod_class->itemise_at_checkout();
					$take_into_account = $take_into_account + $this_total;
				}
			}
			*/
			//$vat_amount=($_SESSION['total_price']+$_SESSION['shipping_amount']+$take_into_account)/100*16.666;
			foreach ($_SESSION['cart'] as $cartitem => $itemdata){
				$sql="SELECT product_categories.vatable FROM product_categories INNER JOIN products ON products.category = product_categories.id WHERE products.ID = " . $itemdata['product_id'];
				$result=$db->query($sql);
				while ($hash = $db->fetch_array()){
					if ($hash['vatable']){
						$prod_sql="SELECT price, price_ex_vat, vat FROM products where ID = " . $itemdata['product_id'];
						$prod_rv=$db->query($prod_sql);
						while ($ph=$db->fetch_array()){
								//print "GOT ONE";
							$total_price += $ph['price']*$itemdata['quantity'];
							$vat_amount += sprintf("%4.2f",$ph['vat']*$itemdata['quantity']);
						};
				
							
					}
				}
			}
			if ($include_shipping){
				$vat_amount+=($_SESSION['shipping_amount']/100*20);
			}
			
			if ($_SESSION['promo_discount_vat_by']){
				$vat_amount+=-$_SESSION['promo_discount_vat_by'];
			}
			//print "Storing vat amount of $vat_amount on total price of " . $total_price;
			$_SESSION['vat_itemisation_total']=$vat_amount;
			if ($vat_amount<0){ $vat_amount=0;}
			return $vat_amount;
		} else {
			return;
		}
	}

	function calculate_total_discount(){

	}
} // end class

?>
