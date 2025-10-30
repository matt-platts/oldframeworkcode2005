<?php

class london_steakhouse_co_shipping {

	function __construct(){

	}

	function calculate_shipping_rate(){
		if ($_SESSION['lsc_calculated_shipping_rate']){
			$rate=$_SESSION['lsc_calculated_shipping_rate'];
		} else {
			$rate=0;
		}
		//print "Rate is $rate - that's what's being returned innit!";
		return sprintf("%4.2f",$rate);
	}

	function shipping_delivery_options(){
		
		$have_meat=0;
		global $db;
		foreach ($_SESSION['cart'] as $item => $cartitem){
			$sql = "SELECT products.category FROM products WHERE id = " . $cartitem['product_id'];
			$rv=$db->query($sql);
			while ($h = $db->fetch_array()){
				if ($h['category']==1){ $have_meat=1; }
			}
		}
		if (!$have_meat){ return; }
		$weekday=date("D");
		$ret = "<p><b>Please select one of the following options for your delivery from the Butcher's shop:</b></p>";
		//if ($weekday=="Wed" || $weekday=="Thu"){
			$numdays=2;
			if (date("j", strtotime("this Saturday"))>date("j")+$numdays){
			print "on this sat";
				$sat_date=date("j F Y", strtotime("this Saturday"));
			} else {
				print "on next sat";
				$sat_date=date("j F Y", strtotime("+ 1 week Saturday"));
			}

			$delivery_days=0;
			if (date("H")>14){ // greater than 14, must be 15 or higher, ie 3PM or after
				$delivery_days++;
			} else {
				if (date("D")=="Fri"){ $delivery_days++;$delivery_days++;}
			}

			$first_delivery_numdays=$delivery_days+2;
			$second_delivery_numdays=$delivery_days+3;


			$timestr="+" . $first_delivery_numdays . " days";
			$timestr2="+" . $second_delivery_numdays . " days";
			//print "Got $timestr and $timestr2";
			if (date("D", strtotime($timestr))=="Sat"){
				$first_delivery_numdays = $first_delivery_numdays + 2;
				$timestr="+ " . $first_delivery_numdays . " days";
			};
			if (date("D", strtotime($timestr2))=="Sat"){
				$second_delivery_numdays = $second_delivery_numdays + 2;
				$timestr2="+ " . $second_delivery_numdays . " days";
			};

			// Sundays
			if (date("D", strtotime($timestr))=="Sun"){
				$first_delivery_numdays = $first_delivery_numdays + 1;
				$timestr="+ " . $first_delivery_numdays . " days";
			};
			if (date("D", strtotime($timestr2))=="Sun"){
				$second_delivery_numdays = $first_delivery_numdays + 1;
				$timestr2="+ " . $second_delivery_numdays . " days";
			};

			//print "<p>Got $timestr and $timestr2";
			$first_avail_date=date("l j F", strtotime($timestr));
			$second_avail_date=date("l j F Y", strtotime($timestr2));

			$avail_dates = $first_avail_date . ", or " . $second_avail_date;
			$ret .= "<input type=\"radio\" name=\"delivery\" value=\"5\" onClick=\"setDelivery(this.value,'Saturday')\"";
			if ($_SESSION['lsc_calculated_shipping_rate']==5){
				$ret .= " checked";
			}
			$ret .= "> I would like to pay £5 for delivery on Saturday " . $sat_date . ".<br /><input type=\"radio\" name=\"delivery\" value=\"0\" onClick=\"setDelivery(this.value,'Regular')\"";
			if ($_SESSION['lsc_calculated_shipping_rate']==0){
				$ret .= " checked";
			}
			$ret .= "> I would like my order to be delivered for free at the next available time (This will be either $avail_dates).<br />";
		//}

			$ret .= '
				<script language="Javascript" type="text/javascript">
					function setDelivery(toWhat,service){
					$.ajax({
						cache: false,
						url: "/new/site/ajax/setDeliveryOption.php?rate=" + toWhat + " &service= " + service,
						success: function(data) {
							//alert(data);
							//document.getElementById("upcoming_occasions_inner").innerHTML=occasion_data;
							location.reload();
						}
					});
					}

					function show_delivery_info(){
						$("#delivery_explained").fadeIn("slow");
						$("#explain_link").fadeOut("fast");
						$("#explain_title").fadeIn("fast");
					}
					function hide_delivery_info(){
						$("#delivery_explained").fadeOut("slow");
						//$("#explain_link").fadeIn("fast");
						$("#explain_title").fadeOut("fast");
					}
				</script>
			';

		$ret .= "<p id=\"explain_title\" style=\"display:none\"><b>Delivery Explained</b> [<a href=\"Javascript:hide_delivery_info()\" title=\"Close\" alt=\"Close\">X</a>]</p>";
		$ret .= "<p id=\"explain_link\"><a href=\"Javascript:show_delivery_info()\" >Delivery Explained - click to view</a></p><div id=\"delivery_explained\" style=\"display:none\">";
		$ret .="<p>Orders for meat products will be sent out directly from our supplier in Scotland. These will be delivered in 2-3 working days, however we will only be able to confirm the exact delivery date with you once you have placed your order. We will always get back to you the same day if you have ordered by 12PM, or the next working day if you have ordered afterwards, and let you know on which day your delivery will be.</p>"; 
		$ret .= "<p>Please note that Saturday delivery attracts a premium of £5.00 extra. If you would like guaranteed delivery on Saturday, please indicate so above. Otherwise these products will be sent out on the next available delivery slot.</p></div>";
		return $ret;
	}
}

	function calculate_saturday_date(){

		$x = date("j", strtotime("this Saturday"));
		return $x;

	}

?>
