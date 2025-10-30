<?php

class flight_logistics_stock_check extends shopping_cart {

function __construct(){
	global $db;
	$sql="SELECT * from flight_logistics_config WHERE active=1";
	$rv=$db->query($sql);
	$h=$db->fetch_array();
	//$this->sku_field=$h['sku_field_in_products_table'];
	$this->company_seq=$h['company_seq'];
}

function value($of){
	return $this->$of;
}

function get_stock_level_at_flight($stockno){

        $url="https://my.flightlg.com/cgi-bin/omnisapi.dll?OmnisClass=rtStockFind&OmnisLibrary=stock&OmnisServer=5912&ivCompanySeq=".$this->value("company_seq")."&ivStockCode=".$stockno;

        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $returned_data=curl_exec($ch);
        curl_close($ch);

        if (empty($returned_data)){
                $return="Unable to get stock level";
        } else {
                if (preg_match("/Status=\d+~\w+/",$returned_data)){
                        $stock_level=str_replace("Status=","",$returned_data);
                        $stock_level=preg_replace("/~\w+/","",$stock_level);
                        $return_value = $stock_level;
                } else {
                        $returned_data=str_replace("Status=","",$returned_data);
                        $return_value = $returned_data;
                }
        }
	return $return_value;
}

}

?>
