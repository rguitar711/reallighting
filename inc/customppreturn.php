<?php
// This is an example of how you would go about setting up a custom payment 
// provider for the Ecommerce Plus template range. More information can be found
// at http://www.ecommercetemplates.com
// Here we have used the 2Checkout.com system as an example of how a common payment
// processor works. You can edit this file to match the details of your particular payment system

// Payment systems will normally pass back 3 different pieces of information. One will be the order
// id that we sent with the transaction. The second will be the authorization code and sometimes a
// variable will be passed back indicating the success of the transaction. You can use these to
// check that the order did indeed come from the payment system you are implementing
$theorderid=unstripslashes(trim(@$_POST['cart_order_id']));
$theauthcode=unstripslashes(trim(@$_POST['order_number']));
$thesuccess=unstripslashes(trim(@$_POST['credit_card_processed']));
if($theorderid != '' && $theauthcode != '' && $thesuccess=='Y'){
	// You should not normally need to change the code below
	$sSQL="UPDATE cart SET cartCompleted=1 WHERE cartOrderID='" . mysql_escape_string($theorderid) . "'";
	mysql_query($sSQL) or print(mysql_error());
	$sSQL="UPDATE orders SET ordStatus=3,ordAuthNumber='" . mysql_escape_string($theauthcode) . "' WHERE ordPayProvider=14 AND ordID=" . mysql_escape_string($theorderid);
	mysql_query($sSQL) or print(mysql_error());
	order_success($theorderid,$emailAddr,$sendEmail);
}else{
	// Make sure you leave this condition here. It calls a failure routine if no match is found for any payment system.
	order_failed();
}
?>