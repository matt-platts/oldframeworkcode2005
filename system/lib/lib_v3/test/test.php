<?php

require_once("require.php");
print "okhere";
exit;

$order_total=396.46;
$x= floor($order_total/50)*50;
print $x;
exit;


// example array
$template_variables['cart_quantity']="5"; // get this value from function that queries existing values in current source code
$template_variables['current_user_name']="Matt Platts"; // as above

// actual template will come from a database or external file where it is easy to change/edit
$template='<div id="items_in_cart"><p>Hi {=variable:current_user_name} - you currently have {=variable:cart_quantity} item(s) in your shopping cart.</p></div>';

//parse the template using the function below
$parsed_template=replace_template_vars($template,$template_variables);
print $parsed_template;
exit;

// parse the above template, substituting correct variable names
// this example function expects two paramaters - $template_html is assumed to be the html for the template itself, $template_variables is the array above.
function replace_template_vars($template_html,$template_variables){
    	preg_match_all("/{=variable:(\w+)}/",$template_html,$matches,PREG_SET_ORDER);
        foreach ($matches as $each_match){
		if ($template_variables[$each_match[1]]){
			$template_html=str_replace($each_match[0],$template_variables[$each_match[1]],$template_html);
		}
        }
return $template_html;
}

$sql="SELECT payments.id AS payment_id, user.first_name as first_name, user.second_name as second_name,user.sage_customer_id as sage_customer_id,user.trade_customer AS trade_customer,payments.description as description,payment_amount,payments.payment_date AS payment_date FROM payments INNER JOIN user on payments.user = user.id WHERE description LIKE \"%Web Order - Immediate Payment via Credit Card%\" AND user.trade_customer=1";

$sql='SELECT orders.id AS order_number, orders.order_date AS order_date, user.first_name AS first_name, user.second_name AS second_name, user.trade_customer AS trade_customer, user.sage_account_name as sage_account_name, countries.Name as country, orders.vatable as vatable, orders.total_amount AS product_total, orders.shipping_total AS shipping, (SELECT amount AS VAT FROM order_total_extras WHERE module="vat_itemisation" AND order_total_extras.order_id=orders.id), (SELECT amount AS volume_discount FROM order_total_extras WHERE module="volume_discount" AND order_total_extras.order_id=orders.id), (SELECT amount AS vouchers FROM order_total_extras WHERE module="gift_vouchers_complex" AND order_total_extras.order_id=orders.id), orders.grand_total AS grand_total, IF(SUM((SELECT amount FROM order_total_extras WHERE module != "vat_itemisation" AND order_total_extras.order_id=orders.id)),SUM((SELECT amount FROM order_total_extras WHERE module != "vat_itemisation" AND order_total_extras.order_id=orders.id))+total_amount+shipping_total,total_amount+shipping_total) AS net_total FROM orders INNER JOIN user on orders.ordered_by=user.id INNER JOIN countries ON orders.order_country=countries.ID WHERE orders.complete=1 GROUP BY orders.id';

$tables=join(",",tables_in_sql_statement($sql));
print $tables;

function tables_in_sql_statement($sql){
        @list($part0,$orderby)=preg_split("/ ORDER BY /i",$sql);
        @list($part1,$having)=preg_split("/ HAVING /i",$part0);
        @list($part2,$where)=preg_split("/ WHERE /i",$part1);
        @list($fields,$from)=preg_split("/ FROM /i",$part2);
        $tables = preg_split("/ (INNER|OUTER|LEFT) JOIN /i",$from);
        $table_list=array();
        foreach ($tables as $table){
                @list($tablename,$rest) = preg_split("/ ON /i",$table);
                array_push($table_list,$tablename);
        }
        return $table_list;
}
exit;

class Customer {
 
	static public $instance_count = 0; //static data member
 
	public function __construct() {
		Customer::$instance_count++;
	}
 
	public function __destruct() {
		Customer::$instance_count--;
	}
 
	public function getFirstName() {
		//body of method
	}
 
	static public function getInstanceCount() {
		//body of method
	}
}
 
$c1 = new Customer();
$c2 = new Customer();
 
echo Customer::$instance_count;
exit;

$x="1";
$y=1;
if ($x==$y){ print "ok 1"; }
if ($x===$y){ print "ok 2"; }
exit;

$s="Buy {=lookup:products:title:product_id} by {=lookup:products:artist:product_id} FROM Gonzo";

$m=preg_match_all("/{=lookup:[\w+:_-]+}?/",$s,$matches);
var_dump($matches[0]);


?>
