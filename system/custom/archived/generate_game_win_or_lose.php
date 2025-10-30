<?

//////////////////////////////////////////////////////////////////////////////////
// win_or_lose									//
//										//
// generates a win or lose based on a number of options which are		//
// pulled from the database. These options generate a chance of winning		//
// and a ramdom number is generated to ascertain a win or lose			//
//////////////////////////////////////////////////////////////////////////////////
function win_or_lose($sale_id){

	$win_or_lose=0;
	// First check we havent exceeded todays daily total;
	$amount_remaining=check_daily_limit();
	// Calculate a win or lose from the chances.... 
	$sale_data = record_from_id("sales",$sale_id);
	$sale_value=$sale_data['value_of_sale'];
	$sale_type=$sale_data['sales_type'];

	$chance_for_sale_type=get_chance_for_sale_type($sale_type);
	$chance_for_sale_value=get_chance_for_sale_value($sale_value);
	$sale_chance_formula=get_sale_chance_formula();
	$initial_result=calculate_sales_vs_types($chance_for_sale_value,$chance_for_sale_type,$sale_chance_formula);

	// We may want an initial uplift to make sure that some early games are won without doubt...
	if ($debug){print "<p>initial is " . $initial_result;}
	$uplifted_result = alter_result_for_initial_uplift($initial_result);	
	if ($debug){print " - up is " . $uplifted_result;}

	// Now we know what chance there is of winning the game, so we generate a win or lose from this
	if ($amount_remaining>=10){
		$win_or_lose = generate_win_or_lose_from_chance($uplifted_result);
	} else {
		$win_or_lose=0;
	}

	return $win_or_lose;

}

//////////////////////////////////////////////////////////////////////////////////
// generate_win_or_lose_from_chance - very simply works out if we have a	//
// win or lose from the percentage chance of winning and returns a		//
// 1 for a win or 0 for a lose. This is done by simply generating a random	//
// number between 1 and 100, and if the number is lower than or equal to	//
// the chance of winning, it's a win!						//
//////////////////////////////////////////////////////////////////////////////////
function generate_win_or_lose_from_chance($percentage){

	$win=0;
	$random = mt_rand(0,99)+1;
	if ($random <= $percentage){$win=1;}
	if ($debug){print("random number is: $random");}
	return $win;

}

function get_chance_for_sale_type($sale_type){

	$sale_sql="SELECT * from sales_types WHERE id =\"$sale_type\"";
	$sale_result=mysql_query($sale_sql);
	while ($sale_type_row=mysql_fetch_array($sale_result,MYSQL_ASSOC)){
		$chance=$sale_type_row['win_chance'];	
	}
	return $chance;
}

function get_chance_for_sale_value($sale_value){
	$sql="SELECT MAX(win_chance) as winning_chance from sale_value_win_chances WHERE sale_value <= $sale_value";
	$sale_result=mysql_query($sql);
	$sale_chance_value=0;
	while ($row=mysql_fetch_array($sale_result,MYSQL_ASSOC)){
		$sale_chance_value=$row['winning_chance'];
	}
	
	return $sale_chance_value;
}

function get_sale_chance_formula(){
	
	$sql = "SELECT * from formulas WHERE formula_group=1 AND active=1";
	$result=mysql_query($sql);
	$qty = mysql_num_rows($result);
	if ($qty !=1){print "ERROR:";}
	$row=mysql_fetch_array($result,MYSQL_ASSOC);
	return $row['id'];	
	
}

//////////////////////////////////////////////////////////////////////////////////////////
// calculate_sales_vs_types - depending on the formula being used, this will be treated //
// in a different way. This works out what formula to use accross the sale type vs sale	//
// value and returns the actual percentage to use from this.				//
//////////////////////////////////////////////////////////////////////////////////////////
function calculate_sales_vs_types($sale_percentage,$type_percentage,$formula_id){
	
	if ($formula_id==1){ return $sale_percentage; }
	if ($formula_id==2){ return $type_percentage; }
	if ($formula_id==3){ return ($sale_percentage+$type_percentage)/2; }

}

//////////////////////////////////////////////////////////////////////////////////////////
// alter_result_for_initial_uplift - looks to see if we're fixing the results of the	//
// early games to guarantee a few wins and returns a winning percentage if so		//
//////////////////////////////////////////////////////////////////////////////////////////
function alter_result_for_initial_uplift($current_chance){
	
	// first get number of games played by user
	if (!user_data_from_cookie('ID')){log_error("No User Data Found In Cookie");}

	$no_of_games_sql="SELECT * from game_results WHERE user_id = " . user_data_from_cookie('id');		
	$no_of_wins_sql="SELECT * from game_results WHERE user_id = " . user_data_from_cookie('id') . " AND game_result=1";		
	$no_of_games_result=mysql_query($no_of_games_sql);
	$no_of_wins_result=mysql_query($no_of_wins_sql);

	$no_of_games_so_far=mysql_num_rows($no_of_games_result);
	$no_of_wins_so_far=mysql_num_rows($no_of_wins_result);

	if ($debug){print "GSF = $no_of_games_so_far and WSF = $no_of_wins_so_far";}

	if ($no_of_games_so_far>4){return $current_chance;} // Because the initial uplift can only go over 4 games
	$sql= "SELECT * from formulas WHERE formula_group=2 AND active=1";
	$result=mysql_query($sql);
	while ($row = mysql_fetch_array($result,MYSQL_ASSOC)){

		if ($row['formula_text']=="First sale per user always wins" && $no_of_games_so_far==0){ return 100;}	
		if ($row['formula_text']=="First two sales per user always win" && $no_of_games_so_far==1){ return 100;}
		if ($row['formula_text']=="One of the first two sales per user always wins" && $no_of_games_so_far==1 && $no_of_wins_so_far==0){
			return 100;
		}
		if ($row['formula_text']=="Two out of the first four sales per user always win" && $no_of_games_so_far<4 && $no_of_wins_so_far<2){
			if ($no_of_games_so_far==3){ return 100;}
			if ($no_of_wins_so_far==0 && $no_of_games_so_far==2){ return 100;}
		}
		
	}
	if ($no_of_games_so_far==3 && $no_of_wins_so_far==3){return $current_chance;}
	if ($no_of_games_so_far==4 && $no_of_wins_so_far==4){return $current_chance;}
	if ($no_of_games_so_far==4 && $no_of_wins_so_far==3){return $current_chance;}
	if ($no_of_games_so_far==2 && $no_of_wins_so_far==2){return $current_chance;}
	//print "ERROR _ THIS PART OF THE CODE SHOULD NEVER BE RUN";
	//print "games so far = $no_of_games_so_far and wins = $no_of_wins_so_far";
	return $current_chance;
}

//////////////////////////////////////////////////////////////////////////////////////////
// check_daily_limit - checks to see if a daily limit is in use and present, then	// 
// returns the amount of money left in the daily limit. 			        //
//////////////////////////////////////////////////////////////////////////////////////////
function check_daily_limit(){

	// are we using the daily limit?
	$sql="SELECT * from formulas WHERE formula_text = \"Use the daily total table to limit daily winnings\" AND value=1";
	$use_daily_limit_result=mysql_query($sql);
	if (mysql_num_rows($use_daily_limit_result)==0){return 100000000;}
	
	$row=mysql_fetch_array($use_daily_limit_result,MYSQL_ASSOC);
	
	$sql="SELECT * from daily_limit";
	$result=mysql_query($sql);
	$row=mysql_fetch_array($result,MYSQL_ASSOC);
	$daily_limit=$row['daily_limit'];	
	if (!$daily_limit){log_error("No Daily Limit returned from daily_limit table and use is set to YES");}
	$sales_today=get_todays_sale_total();
	if ($sales_today < $daily_limit){return ($daily_limit - $sales_today);} else {return 0;}
	
	}

function get_todays_sale_total(){
	$sql="SELECT SUM(points_awarded) AS todays_total FROM game_results where confirmed=1 AND date_format(date_played,'%Y-%m-%d')=CURDATE();";
	$result=mysql_query($sql);
	if (mysql_num_rows($result)){
	$row=mysql_fetch_array($result,MYSQL_ASSOC);
	
	if ($debug){print "total for today is " . $row['todays_total'];}
	return $row['todays_total'];
	} else {
	return;
	}
}

function log_error($msg,$terminate){
	if ($terminate){ print "This program has been terminated as an error has occurred. The error message is :<p>" . $msg;} else { }
	$userid=user_data_from_cookie('id');
	global $current_sale_id;
	$insert_query="INSERT into error_log values(\"\",\"$msg\",NOW(),\"$userid\",\"".$current_sale_id."\")";
	$insert_result=mysql_query($insert_query) or log_error(mysql_error(),1);
	if ($terminate){exit;}
}

// load_movie - returns the filename (swf) of the movie we need from the id and the win or lose paramater
function load_movie($movie_id,$which){
	$sql="SELECT * from games where id = $movie_id";
	$result=mysql_query($sql);
	$row=mysql_fetch_array($result);
	if ($which==1){$moviefile=$row['winning_file'];} else {$moviefile=$row['losing_file'];}
	$new_filename=generate_random_filename($moviefile);
	// just print the movie here really...
	return $new_filename;
}

////////////////////////////////////////////////////////////////////////////////// 
// generate_random_filename - as well as generating a random name, this function//
// the required movie from the masters folder to the temp directory and gives	// 
// it that file name								//
//////////////////////////////////////////////////////////////////////////////////
function generate_random_filename($moviefile){

	$filename="";
	while ($i<=23){
		$filename .= mt_rand(0,9);	
		$i++;
	}

	$filename="MOV".sha1($filename);
	$filename .= ".swf";
	//$basepath="../";	
	$orig_file_name=$basepath . "games/masters/$moviefile";
	$copy_file_name=$basepath . "games/temp/$filename";
	copy ($orig_file_name,$copy_file_name) or log_error("Error copying file from $orig_file_name to $copy_file_name",1);
	return $filename;

}

function embed_movie($movie){
$movie_no_ext=str_replace(".swf","",$movie);
?>

      <script type="text/javascript">
AC_FL_RunContent( 'codebase','http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0','width','400','height','300','src','games/temp/<?php echo $movie_no_ext;?>','quality','high','pluginspage','http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash','movie','games/temp/<?php echo $movie_no_ext;?>' ); //end AC code
        </script>
      <noscript>
      <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,28,0" width="700" height="300">
        <param name="movie" value="games/temp/<?php echo $movie; ?>" />
        <param name="quality" value="high" />
        <embed src="<?php echo $movie; ?>" quality="high" pluginspage="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="400" height="300"></embed>
      </object>
      </noscript>

<?

}
//////////////////////////////////////////////////////////////////////////////////
// update_game_result_table_with_result - as it says, updates game_results 	//
// with the game result, along with user, sale id, time and points scored	//
//////////////////////////////////////////////////////////////////////////////////
function update_game_result_table_with_result($game_result){
	global $current_sale_id;
	$user_id=user_data_from_cookie('id');
	$sql = "INSERT INTO game_results values(\"\",\"$user_id\",\"$current_sale_id\",\"$game_result\",NOW(),\"0\")"; 
	$insert_result=mysql_query($insert_game_sql) or log_error("A database error has occurred: " . mysql_error());
}

function calculate_prize_value($sale_id){
	// how much has been sold?
	$prize_amount=0;
	$sql="SELECT value_of_sale from sales where id = " . $sale_id;
	$result=mysql_query($sql);
	$row=mysql_fetch_array($result,MYSQL_ASSOC);
	$sale_value=$row['value_of_sale'];
	$sql="SELECT MIN(prize_amount) AS prize_amount FROM sale_amounts_to_prizes WHERE sale_value > $sale_value";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result,MYSQL_ASSOC)){
		$prize_amount=$row['prize_amount'];
	}
	if (!$prize_amount){
		$sql="SELECT MAX(prize_amount) AS prize_amount from sale_amounts_to_prizes";
		$result=mysql_query($sql);
		while ($row=mysql_fetch_array($result,MYSQL_ASSOC)){
			$prize_amount=$row['prize_amount'];
		}
	}
	if (!$prize_amount){log_error("A database error has occurred: " . mysql_error(),1);}

	// finally check to see if the prize is within the daily limit and act accordingly
	$amount_remaining=check_daily_limit();
	if ($debug){print "rem is " . $amount_remaining;}

	if ($prize_amount>$amount_remaining){
		$sql="SELECT value from formulas where id=14";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result,MYSQL_ASSOC);
		if ($row['value']=="Be cut down to match the remaining daily limit"){
			$prize_amount=$amount_remaining;
		} else if ($row['value']=="Be awarded anyway (break through daily limit)"){
			// do nothing
		} else if ($row['value']=="Do not allow winning game"){
			$prize_amount=0;
		} else {
			// default - do not allow winning game
			$prize_amount=0;
		}
				
	}

	return $prize_amount;
}

function calculate_points_from_formula($saleid){

	$points=0;
	$sql="SELECT value_of_sale,sales_type from sales where id = $saleid";
	print "<!-- SQL IS $sql //-->";
	$res=mysql_query($sql) or print "<!-- oops: ".mysql_error()."//-->";
	while ($row=mysql_fetch_array($res,MYSQL_ASSOC)){;
		print "<!--";
		var_dump($row);
		print "was row for sale id of $saleid";
		print "//-->";
		$multiplier=1;
		if ($sales_type==1){$multiplier=1;}else{$multiplier=3;}
		$points=$row['value_of_sale']*$multiplier;
	}
	return $points;
}

//////////////////////////////////////////////////
//						//
// 		MAIN SCRIPT			//
//						//
//////////////////////////////////////////////////
$caller_vars=debug_backtrace();
//var_dump($caller_vars[0]['file']);
if (!$caller_vars){
require_once ("../config.php");
require_once ("$basepath/library/errors.php");
require_once ("$basepath/library/require.php");
}
if (!user_data_from_cookie("id")){log_error("No User Found",1);}
$load_game=1;
$current_sale_id=$replace_vars['last_insert_id'];
// add value to database but first check it doess not already exist
$existing_game_result="";
$game_id="";
$select_game="SELECT * from game_results WHERE confirmed=1 AND sale_id = " . $current_sale_id;
$game_result=mysql_query($select_game);
if (mysql_num_rows($game_result)>0){// game already played
	print "This game is already played - you cannot play a second game for this sale"; 
	$load_game=0;
} else {
	// check the initial game request hasn't been logged
	$select_game_2="SELECT * from game_results WHERE sale_id = " . $current_sale_id;
	$game_result=mysql_query($select_game_2);
	// The following scenario should be impossible and never happen...
	if (mysql_num_rows($game_result)>1){print "An error has occurred - this game has been logged too many times. Please get in touch with the systems administrator."; $load_game=0;}
	// If we have an unconfirmed result we can load a game
	if (mysql_num_rows($game_result)==1){
		$row=mysql_fetch_array($game_result,MYSQL_ASSOC);
		if ($row['confirmed']==1){ // this scenario should be impossible and never happen
			print "This game is already played - you cannot play a second game for this sale"; 
			$load_game=0;
		} else if (isset($row['game_result'])){ // if we have an unconfirmed game result we should use it. Theres no point in generating a new one.
			$existing_game_result_value=$row['game_result'];
			$existing_game_result="Yes";
			$game_id=$row['id'];
			if ($debug){print "generating game id of " . $game_id . " from old game";}
		} else {// This also should never happen so we're going to print an error message
			print "An error has occurred."; $load_game=0; 
		}
	}
}	


function check_for_min_total(){
	$sql="SELECT value from formulas where id=16";
	$result=mysql_query($sql);
	$row=mysql_fetch_array($result,MYSQL_ASSOC);
	if ($row['value']==1){
		
		$sql2="SELECT monthly_min_target from user where id = " . user_data_from_cookie('id');
		$result2=mysql_query($sql2);
		$row=mysql_fetch_array($result2,MYSQL_ASSOC);
		$min=$row['monthly_min_target'];
		if (!$min) {return 0;} else {return $min;}
	}	

}

function check_min_reached($min){

	$get_current_month=date("m");
	$get_current_year=date("Y");
	$sql = "SELECT SUM(value_of_sale) AS month_sales_so_far from sales WHERE added_by = " . user_data_from_cookie('id') . " AND date_entered LIKE(\"".$get_current_year. "-".$get_current_month . "%\") AND approved IN (0,1)";
	$res=mysql_query($sql);
	$row=mysql_fetch_array($res,MYSQL_ASSOC);
	if ($row['month_sales_so_far']){
		if ($row['month_sales_so_far']>$min){return 1;} else {return 0;}
	} else {
		return 0;
	}
	// should have returned by now.,
}

$minimum=check_for_min_total();
$minimum_applied_and_reached="NO";
if ($minimum){// ie has a value other than 0
	$min_reached=check_min_reached($minimum);
	if (!$min_reached){
		// no load game here, so print a thankyou message
		$load_game=0;
?>
<span class="title">SALE ENTERED!</span> 
<p>
Thank you for entering your sale. You haven't reached your monthly minimum total in order to play the game and start winning prizes yet though. Check the monitor to see how much more you need to do!
<p><a href="site.php?s=1">Home Page</a>
</p>
<?
	} else {
		$minimum_applied_and_reached="YES";
	} 
}

if ($load_game){


?>
<span class="title">SPIN TO WIN!</span> 
<p>
Thank you for entering your sale. Here's your chance to win some great prizes. Any three fruit the same wins you a prize immediately, so get spinning!
</p>
<div align="center" style="margin: 0px; padding: 0px">
<?


	$i=1;
	while ($i<=1){
		$result=win_or_lose($current_sale_id); // needs to pass the sales id on to work out if we have a win or lose scenario
		$movie_id=1;
		if (isset($existing_game_result_value)){$result=$existing_game_result_value;}
		$movie=load_movie($movie_id,$result);
		// at this point, the correct movie is loaded into $movie and needs to be displayed to the browser	
		embed_movie($movie);
		//print "<br>\n".$i . " - " . $result;
		if ($result==1){$all++;}
		$i++;
	}
	if (isset($existing_game_result_value)){
	} else {
		// NOW we can log it
		$prize_value=calculate_prize_value($current_sale_id); // prize value is dependend on the sale value
		if (!$result){$prize_value=0;}
		$points_from_formula=calculate_points_from_formula($current_sale_id);
		print "<!-- POINTS TO INSERT IS $points_from_formula //-->";
		if (!$points_from_formula){$points_from_formula=0;}
		mysql_query("LOCK TABLES game_results WRITE");
		$insert_game_sql="INSERT INTO game_results VALUES(\"\",".user_data_from_cookie('id').",$current_sale_id,$result,NOW(),$prize_value,\"0\",\"\",0,$points_from_formula)";
		$insert_result=mysql_query($insert_game_sql) or die("ERROR LOGGING GAME: " . mysql_error());
		$result_max = mysql_query("SELECT MAX(id) AS LAST_ID FROM game_results");
		$result_max = mysql_fetch_array($result_max);
		mysql_query("UNLOCK TABLES");
		$game_id=$result_max[LAST_ID];
		if ($debug){print "generating game id of " . $game_id . " from new game";}
	}
	//print "\n\nAll IS $all"
	?>
	<div id="log">
		<p><p>
	</div>
	<script type="text/javascript">
	var flaVx="<?php echo $game_id;?>";
	var flaVy="<?php echo $result;?>";

	function post_completed(){
		sendResult(<?php echo $game_id;?>,<?php echo $result;?>);
	}
	</script>
	<!--<a href="javascript:post_completed()">ee</a>//-->
	<?

print "</div>";
}
?>



