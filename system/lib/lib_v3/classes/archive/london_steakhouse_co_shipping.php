<?php

require_once(LIBPATH . "/interfaces/shipping_method.interface.php");

class london_steakhouse_co_shipping extends shopping_cart implements shipping_method {

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
		global $user;
		foreach ($_SESSION['cart'] as $item => $cartitem){
			$sql = "SELECT products.category FROM products WHERE id = " . $cartitem['product_id'];
			$rv=$db->query($sql);
			while ($h = $db->fetch_array()){
				if ($h['category']==1){ $have_meat=1; }
			}
		}
		if (!$have_meat){ return; }
		if (!$user->value("id")){ return ; }

		$result=$this->next_delivery_slot($_SESSION['delivery_postcode']);
		$sat_date=$result['saturday'];
		$first_avail_date = $result['first_day'];
		$second_avail_date = $result['second_day'];
		$first_day_date_notation = $result['first_day_date_notation'];
		$saturday_day_date_notation= $result['saturday_date_notation'];
		if (!$_SESSION['cart_vars']['extra_order_fields']['delivery_date']){
			$_SESSION['cart_vars']['extra_order_fields']['delivery_date']=trim($first_day_date_notation);
		}

		/*$weekday=date("D");
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
			*/
			$avail_dates = trim($first_avail_date) . ", or " . trim($second_avail_date);
			$ret .= "<form name=\"delivery_options\" method=\"get\">\n";
			$ret .= "<input type=\"hidden\" name=\"first_avail_date\" value=\"".str_replace("/","-",$first_day_date_notation) . "\" />\n";

			if ($sat_date){

				$ret .= "<input type=\"radio\" name=\"delivery\" value=\"0\" onClick=\"setDelivery(this.value,'Regular','".str_replace("/","-",$first_day_date_notation) . "')\"";
				if ($_SESSION['lsc_calculated_shipping_rate']==0 || !$_SESSION['lsc_calculated_shipping_rate']){
					$ret .= " checked";
				}
				$ret .= "> I would like my order to be delivered for free at the next available time (This is will be either $avail_dates).<br />";

				$ret .= "<input type=\"radio\" name=\"delivery\" value=\"5\" onClick=\"setDelivery(this.value,'Saturday','".str_replace("/","-",$saturday_day_date_notation) . "')\"";
				if ($_SESSION['lsc_calculated_shipping_rate']==5){
					$ret .= " checked";
				}
				$ret .= "> I would like to pay £5 for delivery on Saturday " . $sat_date . ".<br />";



		$ret .= "<p id=\"explain_title\" style=\"display:none\"><b>Delivery Explained</b> [<a href=\"Javascript:hide_delivery_info()\" title=\"Close\" alt=\"Close\">X</a>]</p>";
		$ret .= "<p id=\"explain_link\"><a href=\"Javascript:show_delivery_info()\" >Delivery Explained - click to view</a></p><div id=\"delivery_explained\" style=\"display:none\">";
		$ret .="<p>Orders for meat products will be sent out directly from our supplier in Scotland. These will be delivered in 2-3 working days, however we will only be able to confirm the exact delivery date with you once you have placed your order. We will always get back to you the same day if you have ordered by 2PM, or the next working day if you have ordered afterwards, and let you know on which day your delivery will be.</p>"; 
		$ret .= "<p>Please note that Saturday delivery attracts a premium of £5.00 extra. If you would like guaranteed delivery on Saturday, please indicate so above. Otherwise these products will be sent out on the next available delivery slot.</p></div>";

		} else {

			$ret="<input type=\"hidden\" name=\"delivery\" value=\"0\"><p>Your meat order will be delivered for free at the next available time. This will be either $avail_dates.<br />";
			$ret .= "<p id=\"explain_title\" style=\"display:none\"><b>Delivery Explained</b> [<a href=\"Javascript:hide_delivery_info()\" title=\"Close\" alt=\"Close\">X</a>]</p>";
			$ret .= "<p id=\"explain_link\"><a href=\"Javascript:show_delivery_info()\" >Delivery Explained - click to view</a></p><div id=\"delivery_explained\" style=\"display:none\">";
			$ret .="<p>Orders for meat products will be sent out directly from our supplier in Scotland. These will be delivered in 2-3 working days, however we will only be able to confirm the exact delivery date with you once you have placed your order. We will always get back to you the same day if you have ordered by 2PM, or the next working day if you have ordered afterwards, and let you know on which day your delivery will be.</p></div>"; 
		}


			$ret .= '
				<script language="Javascript" type="text/javascript">
					function setDelivery(toWhat,service,firstDateAvail){
					$.ajax({
						cache: false,
						url: "/ajax/setDeliveryOption.php?rate=" + toWhat + "&service= " + service + "&requested_delivery_date=" + firstDateAvail,
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
						$("#explain_title").fadeOut("fast");
						$("#explain_link").fadeIn("fast");
					}
				</script>
			';

		$ret .= "\n</form>\n";
		return $ret;
	}

function postcode_checker($postcode,$test_for,$day=""){
	if (!$postcode){ return; }
	/* Values for $test_for are:
	- increased_lead_time (returns no. of days)  eg. $result=postcode_checker($postcode,"increased_lead_time");
	- delivery_unavailable_days (returns an array of days delivery is NOT available) eg. $result=postcode_checker($postcode,"non_delivery_days");
	- check_day (returns a boolean value on whether the day IS available  - 0 for cannot delivery on this day, 1 if it can). Input should be the day spelled out fully as the third field and is case insensitive. eg. $result=postcode_checker($postcode,"check_day","Friday").
	*/
	global $db;
	$resultdata=array();

	$end_part=preg_match("/\d\w\w$/",$postcode,$matches);
	$initial_part=trim(str_replace($matches[0],"",$postcode));
	$letter_stem=preg_replace("/\d+$/","",$initial_part);

	//print "Initial: $initial_part and stem $letter_stem\n";

	// first SQL QUERY - checks for the letter stems ONLY. If the stem only is found we use this rule straight away, no need to test for the other version. 
	$match=$letter_stem;
	$match .= "(,|$)";

	$where=array();
	if ($test_for=="increased_lead_time"){
		$where[] = "AND exclusion_type like \"Extra Lead Time%\"";
	} else if ($test_for=="delivery_unavailable_days" || $test_for == "check_day"){
		$where[] = "AND exclusion_type like \"%day Delivery\"";
	}

	$sql="SELECT * FROM postcode_exclusions WHERE exclusion_list RLIKE \"$match\" ";
	$sql .= implode(" AND ",$where);
	$result=$db->query($sql);
	$rows_found_for_stem= $db->num_rows($result);
	$resultdata[]= "Found $rows_found_for_stem rows for the stem\n";

	while ($h=$db->fetch_array()){
		$results[]=$h['exclusion_type'];
	}


	$sql="SELECT * FROM postcode_exclusions WHERE exclusion_list RLIKE \"$initial_part\" ";
	$sql .= implode(" AND ",$where);
	$result=$db->query($sql);
	$rows_found_for_full= $db->num_rows($result);
	$resultdata[]= "Found $rows_found_for_stem rows for the initial section\n";

	while ($h=$db->fetch_array()){
		$results[]=$h['exclusion_type'];
	}

	$result=array();
	$result['data']=$resultdata;
	$result['results']=$results;
	$result['status']=0;
	return $result;
}

function next_delivery_slot($postcode){
	$today=date("l");
	$now=date("H");
	//print "Day $today and hour $now";
	$postcode_dependent=$this->postcode_checker($postcode,"all");

	$delivery_days=2; // always takes 2 days as minium. Eg. Mon before 3 = delivery Wed. Mon after 3 turns into the next day, so if the hour >= 15, we add a day.	
	if (date("H")>=15){ $delivery_days++;}


	// Use the lead time	
	if (in_array("Extra Lead Time 1 Day",$postcode_dependent['results'])){
		$delivery_days++;
	}
	if (in_array("Extra Lead Time 2 Days",$postcode_dependent['results'])){
		$delivery_days++;
		$delivery_days++ ; // yes twice
	}

	// build a matrix of possible days, starting with the frist day we've got
	$matrix[0]['day']="Monday";
	$matrix[0]['possible']=0;	
	$matrix[1]['day']="Tuesday";
	if (in_array("No Tuesday Delivery",$postcode_dependent['results'])){
		$matrix[1]['possible']=0;
	} else {
		$matrix[1]['possible']=1;
	}

	$matrix[2]['day']="Wednesday";
	if (in_array("No Wednesday Delivery",$postcode_dependent['results'])){
		$matrix[2]['possible']=0;
	} else {
		$matrix[2]['possible']=1;
	}

	$matrix[3]['day']="Thursday";
	if (in_array("No Thursday Delivery",$postcode_dependent['results'])){
		$matrix[3]['possible']=0;
	} else {
		$matrix[3]['possible']=1;
	}

	$matrix[4]['day']="Friday";
	if (in_array("No Friday Delivery",$postcode_dependent['results'])){
		$matrix[4]['possible']=0;
	} else {
		$matrix[4]['possible']=1;
	}

	$matrix[5]['day']="Saturday";
	if (in_array("No Saturday Delivery",$postcode_dependent['results'])){
		$matrix[5]['possible']=0;
	} else {
		$matrix[5]['possible']=1;
	}

	$matrix[6]['day']="Sunday";
	$matrix[6]['possible']=0;	
	// end build the matrix


	// FIRST RUN - discount saturdays entirely anyway
	$matrix[5]['possible']=0;

	$timevar = "+ " . $delivery_days . " Days";
	$marker=date("N",strtotime($timevar))-1;
	//print "Delivery days is $delivery_days, marker is at $marker\n\n\n\n";
	$delivery_days_reset=$delivery_days;

	/* LOOP THE MATRIX */
		$possible=0; // untested
		$count=0;
		while ($possible==0){
		
		if ($matrix[$marker]['possible']){
			$possible=1;
			//print "It is possible here at marker position $marker!\n";
			break;
		} else {
			//print "It is NOT possible here at marker position $marker!\n";
		}

		$marker++;
		if ($marker==7){ $marker=0;  } // reset the marker at the end
		if ($count>14){  exit; }

		$count++;
		}
	/* END LOOP THE MATRIX */

	//print "\n\n--- Our count was $count\n";
	$delivery_days+=$count;

	//print "Total delivery days required is $delivery_days\n";
	$timevar = "+ " . $delivery_days . " Days";
	//echo chr(27) . "[1;31m";
	//print "Next delivery day is " . date("l jS M Y", strtotime($timevar)) . "\n\n";
	//echo chr(27) . "[1;33m";
	//print "-----------------------------\n";
	//echo chr(27) . "[0;37m";
	$delivery_slots['first_day'] = date("l jS M Y", strtotime($timevar));
	$delivery_slots['first_day_date_notation']=date("d/m/Y",strtotime($timevar));
	
	/* LOOP THE MATRIX */
		$previous_count=$count;
		$count++;
		$marker++;
		$possible=0; // untested
		while ($possible==0){
		//print "- starting second loop with count at $count and marker at $marker\n";
		
		if ($matrix[$marker]['possible']){
			$possible=1;
			//print "It is possible here at marker position $marker (" . $matrix[$marker]['day'] . ")!\n";
			break;
		} else {
			//print "It is NOT possible here at marker position $marker (" . $matrix[$marker]['day'] . ")!\n";
		}

		$marker++;
		if ($marker==7){ $marker=0; } // reset the marker at the end
		if ($count>21){ 
				exit; }

		$count++;
		}
	/* END LOOP THE MATRIX */

//	print "\n\n--- Our count was $count\n";
	$delivery_days+=($count-$previous_count);

	//print "Total delivery days required is $delivery_days\n";
	$timevar = "+ " . $delivery_days . " Days";
	//echo chr(27) . "[1;31m";
	//print "Second delivery day is " . date("l jS M Y", strtotime($timevar)) . "\n";
	//echo chr(27) . "[0;37m";

	$delivery_slots['second_day'] = date("l jS M Y", strtotime($timevar));
	


	// now we only run the saturday test if we need to..

	if (!in_array("No Saturday Delivery",$postcode_dependent['results'])){

		$numdays=$delivery_days_reset;
		if (date("j", strtotime("this Saturday"))>date("j")+$numdays){
		//print "on this sat";
			$sat_date=date("j F Y", strtotime("this Saturday"));
			$sat_date_notation = date("d/m/Y",strtotime("this Saturday"));
		} else {
			//print "on next sat";
			$sat_date=date("j F Y", strtotime("+ 1 week Saturday"));
			$sat_date_notation = date("d/m/Y",strtotime("+ 1 week Saturday"));
		}

		//print "Saturday date is $sat_date";
		$delivery_slots['saturday']=$sat_date;
		$delivery_slots['saturday_date_notation']=$sat_date_notation;
	} else {
		//print chr(27) . "[1;35m\n\nDelivery on Saturday is not possible\n" . chr(27) . "[0;37m";
	}

	
	return($delivery_slots);
}


}

	function calculate_saturday_date(){

		$x = date("j", strtotime("this Saturday"));
		return $x;

	}

?>
