<?php
// This is an example of how you would go about setting up a custom payment 
// provider for the Ecommerce Plus template range. More information can be found
// at http://www.ecommercetemplates.com
// Here we have used the 2Checkout.com system as an example of how a common payment
// processor works. You can edit this file to match the details of your particular payment system
// Other useful parameters are countryCode, shipCountryCode, countryCurrency
// Firstly you will need to set the URL to pass payment variables below in the FORM action ?>
<form method="post" action="https://www.2checkout.com/cgi-bin/sbuyers/cartpurchase.2c">
<?php // A unique id is assigned to each order so that we can track the order. This is available as the orderid. Edit the name cart_order_id to that which is used by your payment system. ?>
	<input type="hidden" name="cart_order_id" value="<?php print $orderid?>" />
<?php // In the Ecommerce Templates admin section for the Custom Payment System, up to 2 pieces of data can be entered ?>
<?php // to configure a payment system. These are Data 1 and Data 2 and are available in the variables data1 and data2 ?>
	<input type="hidden" name="sid" value="<?php print $data1?>" />
<?php // Our example of 2Checkout.com does not require a return URL, but I´ve included one below as an example if needed ?>
	<input type="hidden" name="returnurl" value="<?php print $storeurl?>thanks.php" />
<?php // The variable ppmethod is available if needed to choose between authorize only and authorize capture payments. If this does not apply to your payment system just delete the line below ?>
	<input type="hidden" name="paymenttype" value="<?php if($ppmethod==1) print "1"; else print "0" ?>" />
<?php // The following should be quite self explanatory ?>
	<input type="hidden" name="total" value="<?php print $grandtotal?>" />
	<input type="hidden" name="card_holder_name" value="<?php print $ordName?>" />
	<input type="hidden" name="street_address" value="<?php print $ordAddress?>" />
	<input type="hidden" name="city" value="<?php print $ordCity?>" />
	<input type="hidden" name="state" value="<?php print $ordState?>" />
	<input type="hidden" name="zip" value="<?php print $ordZip?>" />
	<input type="hidden" name="country" value="<?php print $ordCountry?>" />
	<input type="hidden" name="email" value="<?php print $ordEmail?>" />
	<input type="hidden" name="phone" value="<?php print $ordPhone?>" />
<?php	if($ordShipName != '' || $ordShipAddress != ''){ ?>
	<input type="hidden" name="ship_name" value="<?php print $ordShipName?>" />
	<input type="hidden" name="ship_street_address" value="<?php print $ordShipAddress?>" />
	<input type="hidden" name="ship_city" value="<?php print $ordShipCity?>" />
	<input type="hidden" name="ship_state" value="<?php print $ordShipState?>" />
	<input type="hidden" name="ship_zip" value="<?php print $ordShipZip?>" />
	<input type="hidden" name="ship_country" value="<?php print $ordShipCountry?>" />
<?php	} ?>
<?php	// A variable "demomode" is made available to the admin section that signals the payment method is in demo mode
	if($demomode) print '<input type="hidden" name="demo" value="Y" />';
	// IMPORTANT NOTE ! You may notice there is not closing <FORM> tag. This is intentional. ?>