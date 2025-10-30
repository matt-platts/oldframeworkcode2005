<?php

function sagepay_manual_pay_authorisation($order_id){

        $continue_manual=$_GET['continue_manual_pay_authorisation'];
        print "<p class=\"admin_header\">Pay Authorisation</p>";
        global $db;
        $sql="SELECT ordered_by,grand_total FROM orders where id = $order_id";
        print $sql;
        $rv=$db->query($sql);
        $h=$db->fetch_array($rv);
        $grand_total=$h['grand_total'];
        $user_id=$h['ordered_by'];
        print "<p>About to authorise order #$order_id for &pound;$grand_total</p>";
        if (!$continue_manual){
                print "<p><a href=\"mui-administrator.php?action=sagepay_manual_pay_authorisation&order_id=$order_id&continue_manual_pay_authorisation=1\">Continue</a></p>";
        } else {
                print "Posting the following details: <br />";
                $sql="SELECT * from sagepay_responses WHERE order_id = $order_id AND status like \"%AUTHORISED%\"";
                $rv=$db->query($sql);
                $row=$db->fetch_array($rv);

                //$row['VendorTxCode']="535e376081d7c";
                //$row['SecurityKey']="IUIIE5RQSI";
                //$row['VPSTxId']="{EDEB8934-2D90-20A8-01B3-5A16371C3047}";
                //VPSProtocol=2.23&TxType=AUTHORISE&Vendor=gonzomultimedia&VendorTxCode=53d77bda8df3d&Amount=999.99&Currency=GBP&Description=Preorder authorise for order no 202&RelatedVPSTxId={EDEB8934-2D90-20A8-01B3-5A16371C3047}&RelatedSecurityKey=IUIIE5RQSI&RelatedVendorTxCode=535e376081d7c

                $details['relatedVPSTxId']=$row['VPSTxId'];
                $details['relatedSecurityKey']=$row['SecurityKey'];
                $details['relatedVendorTxCode']=$row['VendorTxCode'];
                global $libpath;
                require_once("$libpath/classes/shopping_cart.php");
                $mycart=new shopping_cart();
                require_once("$libpath/classes/sagepay_direct.php");
                $attempt_auth=new sagepay_direct();
                $details['order_type']="authorise_preorder";
                $details['amount']=$grand_total;
                $details['user_id']=$user_id;
                $details['order_number']=$order_id;
                foreach ($details as $detailname=>$detail){
                        print $detailname . " - " . $detail  . "<br>";
                }
                $payment_result=$attempt_auth->authorise_preorder($details);
                print "<p><b>Got return value from sagepay auth process of " . $payment_result['value'] . ": </b></p>";
                print "<p>Status: " . $payment_result['status'] . "<br />Details: " . $payment_result['statusdetail'] . "</p>";
                if ($payment_result['value']==0){
                        print "<p style=\"color:red\">We have not been able to take monies for this transaction.</p>";
                } else if ($payment_result['value']==1){
                        print "<p style=\"color:orange\">This order needs to be posted to flight logistics separately.</p>";
                }
                print "<p>";
                var_dump($payment_result);
                print "</p>";
        }

}

?>
