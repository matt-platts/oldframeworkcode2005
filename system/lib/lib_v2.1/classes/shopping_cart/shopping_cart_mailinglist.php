<?

class shopping_cart_mailinglist {

function __construct(){

}

function add_to_my_list($field,$item,$userid){

	if (!$userid || $userid=="0"){
		return "You need to <a href=\"log_in.html\">log in</a> to add artists to your mailing list.";
		exit;
	}
	$item_filtered=$this->filter_item($field,$item);
	global $db;
	$return_text="";
	$sql="SELECT id from shopping_cart_mailinglist_data WHERE field_as_category_fieldname=\"$field\" AND field_as_category_value=\"$item\" AND user_id=$userid";
	$res=$db->query($sql);
	if ($db->num_rows($res)>0){
		$return_text="You are already subscribed to the mailing list for " . $item_filtered;
		$return_text .= $this->all_mailing_lists_signed_up_by_user_per_field($field,$userid);
	}
	
	if (!$return_text){
		$sql="INSERT INTO shopping_cart_mailinglist_data (field_as_category_fieldname,field_as_category_value,user_id,time_added) VALUES (";
		$sql .= "\"$field\",\"$item\",$userid,NOW())";
		$res=$db->query($sql);
		$return_text="<p>You are now signed up to the $field mailing list for the $field: $item_filtered.</p>";
		$return_text .= $this->all_mailing_lists_signed_up_by_user_per_field($field,$userid);
	}

	$return_text .= "<p><a href=\"Javascript:history.go(-1)\">&lt; Back</a></p>"; 
	return $return_text;
	
}

function all_mailing_lists_signed_up_by_user_per_field($field,$user_id){
	global $db;
	$return_list="<p style=\"font-weight:bold\">You are currently signed up to the following $field mailing lists: </p><table>";
	$sql="SELECT * from shopping_cart_mailinglist_data WHERE user_id=$user_id";
	$res=$db->query($sql);
	while ($h=$db->fetch_array($res)){
		$return_list .= "<tr><td>".$this->filter_item($field,$h['field_as_category_value']) . "</td><td><a href=\"site.php?action=cart_mailer_remove_from_list&cart_mailer_item=".$h['field_as_category_fieldname']."&cart_mailer_value=".$h['field_as_category_value']."\">Unsubscribe</a></td></tr>";
	}		
	$return_list .= "</table>";
	return $return_list;
}

function filter_item($field,$item){

	// is there a view filter registered on the products table?
	$filter_id = filter_registered_on_table("products","list_table"); 
	if ($filter_id){
		$filter_options=load_dbforms_filter($filter_id);
		if ($filter_options[$field]['select_value_list']){
			if (preg_match("/^SQL:/",$filter_options[$field]['select_value_list'])){
				$item=sql_value_from_id($filter_options[$field]['select_value_list'],$item);
			}
		}
	}
	return $item;
}

function remove_from_my_list($field,$item,$userid){
	
	global $db;
	$return_text="";
	$sql="SELECT id from shopping_cart_mailinglist_data WHERE field_as_category_fieldname=\"$field\" AND field_as_category_value=\"$item\" AND user_id=$userid";
	$res=$db->query($sql);
	if ($db->num_rows($res)==0){
		$return_text="You are already unsubscribed to the mailing list for " . $this->filter_item($field,$item);
	}
	
	if (!$return_text){
		$sql = "DELETE FROM shopping_cart_mailinglist_data WHERE field_as_category_fieldname=\"$field\" AND field_as_category_value=\"$item\" AND user_id=$userid";
		$res=$db->query($sql);
		$return_text = "You are no longer subscribed to the mailing list for " . $this->filter_item($field,$item);
	}

	$return_text .= $this->all_mailing_lists_signed_up_by_user_per_field($field,$userid);
	return $return_text;
}
	

}

?>
