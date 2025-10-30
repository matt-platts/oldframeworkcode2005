<?php

function survey_response(){
	global $db;
	$survey_id=$_POST['survey_id'];
	$vote=$_POST['response'];
	$user_ip = $_SERVER["REMOTE_ADDR"];
	$sql1="SELECT NOW() - INTERVAL 1 MINUTE as time_ago";
	$res=$db->query($sql1) or die(->db_error());
	$h=$db->fetch_array($res);
	$today=$h['time_ago'];
	$check_select="SELECT * from survey_responses where ip_address = \"$user_ip\" AND question_id=$survey_id AND time_stamp >=\"$today\"";
	$check_result=$db->query($check_select);
	if($db->num_rows($check_result)>0){
		$returntext = "<br /><p>Thank you for your vote. Unfortunately we have very recently received a response from this IP address and cannot log another response to this survey from here so soon.</p><p>Please try again in a couple of minutes.</p><hr size=1 width=300>";
		$returntext .= survey_results();	
		return $returntext;
	}
	$sql="INSERT INTO survey_responses (question_id,response,response_date,ip_address) values($survey_id,$vote,NOW(),\"$user_ip\")";
	$result=$db->query($sql) or die (->db_error());
	$returntext = "<br /><p style=\"font-weight:bold\">Thanks for voting in our survey!</p><hr size=1 width=300>";
	$returntext .= survey_results();	
	return $returntext;
}

function survey_comments_response(){
	global $db;
	$survey_id=$_REQUEST['survey_id'];
	if (!$survey_id){return "ERROR: NO SURVEY ID FOUND";}
	$comment=$_POST['comments'];
	if (!$comment){ $returntext = "No comment entered - please go back and enter a comment for this question."; return $returntext;}
	$sql="INSERT INTO survey_comments (survey_id,comments,comment_date) values($survey_id,\"$comment\",NOW())";
	$res=$db->query($sql) or die(->db_error());
	$returntext = "<br /><p>Thank you for your comments.</p>";
	return $returntext;
}

function survey_results(){
	global $db;
	$survey_id=$_REQUEST['survey_id'];
	if (!$survey_id){ return "ERROR"; }

	// what type of survey have we got?
	$sql = "SELECT * from survey_questions WHERE id = $survey_id";
	$res=$db->query($sql) or die(->db_error());
	while ($h=$db->fetch_array($res)){
		$mchoice = $h['multi_choice'];
	}
	$returntext = "<br /><p>Responses to our current survey:</p>";
	if ($mchoice){
		$totalsql="SELECT count(*) as no_of_results FROM survey_response_options INNER JOIN survey_responses on survey_response_options.id=survey_responses.response WHERE survey_response_options.question_id=$survey_id";
		$totalres=$db->query($totalsql);
		$h=$db->fetch_array($totalres);
		$returntext .= "<p><b>Total results: </b>" . $h['no_of_results'] . "</p>";
		$maxlength_in_px=290;

		$colours=array("#005500","#009900","#00CC00","#00FF00");
		$colours=array("#62BD18","#62BD18","#62BD18","#62BD18");
		$sql="SELECT survey_response_options.id, survey_response_options.response, count(survey_responses.response) as total_votes from survey_response_options INNER JOIN survey_responses on survey_response_options.id=survey_responses.response WHERE survey_response_options.question_id=$survey_id GROUP BY response order by display_order";
		$res=$db->query($sql) or die(->db_error());
		$count=0;

		while ($h=$db->fetch_array($res)){
			if ($count==0){
				$highest_value=$h['total_votes'];
				$total_results=$h['no_of_results'];	
				$multiplier=$maxlength_in_px/$highest_value;
			}
		//	$returntext .= "<tr><td>".$h['response']." &nbsp; </td><td> ".$h['total_votes']."</td></tr>";
			$returntext .= '<p><table border=0 cellspacing=0 cellpadding=0><tr><td><div class="bargraph" style="width:'.round($h['total_votes']*$multiplier).'px; margin-left:15px; background-color:'.$colours[$count].'"></div></td><td> <div class="optiontext">'.$h['response'].' ('.$h['total_votes'].')</div></td></tr></table></p>';
			$count++;
		}
		$returntext .= "</p><br clear=\"all\"><br />";


		$sql = "SELECT DATE_FORMAT(MIN(survey_responses.response_date),\"%d-%m-%Y\") AS mindate, DATE_FORMAT(MAX(survey_responses.response_date),\"%d-%m-%Y\") AS maxdate FROM survey_responses WHERE question_id=$survey_id";
		$res=$db->query($sql) or die(->db_error());
		while ($h=$db->fetch_array($res)){
			$returntext .= "<p>Data collected between " . $h['mindate'] . " and " . $h['maxdate'] . ".</p>";
		}
	} else {

		$totalsql="SELECT count(*) as no_of_results FROM survey_comments WHERE survey_comments.survey_id=$survey_id";
		$totalres=$db->query($totalsql);
		$h=$db->fetch_array($totalres);
		$returntext .= "<p><b>Total no. of responses: </b>" . $h['no_of_results'];
	}
	return $returntext;
}

?>
