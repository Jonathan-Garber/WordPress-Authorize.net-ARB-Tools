<?php
if (isset($_POST['cancelSubscription'])){
	$subID = $_POST['subID'];
	$reason = $_POST['cancelReason'];

	$billing = new billing();
	$billing->adminCancelSubscription();
	
	
}

$subsArray = array(
'post_type' => 'auth-subscriptions'
);

$subscriptions = get_posts($subsArray);

foreach ($subscriptions as $s){
$postID = $s->ID;
$customerID = $s->post_author;
$subscriptionID = $s->post_title;

$billing = new billing($postID, $customerID);

$customerData = $billing->subscriptionCustomerData();
//billing data
$billingFirstName = $customerData['billingFirstName'];
$billingLastName = $customerData['billingLastName'];
$billingEmail = $customerData['billingEmail'];
$billingCompany = $customerData['billingCompany'];
$billingPhoneNumber = $customerData['billingPhoneNumber'];
$billingAddress = $customerData['billingAddress'];
$billingCity = $customerData['billingCity'];
$billingState = $customerData['billingState'];
$billingZip = $customerData['billingZip'];
$billingCountry = $customerData['billingCountry'];

$billingCreditCardLastFour = $customerData['billingCreditCardLastFour'];
$billingCreditCardMonth = $customerData['billingCreditCardMonth'];
$billingCreditCardYear = $customerData['billingCreditCardYear'];

//shipping data
$shippingFirstName = $customerData['shippingFirstName'];
$shippingLastName = $customerData['shippingLastName'];
$shippingEmail = $customerData['shippingEmail'];
$shippingCompany = $customerData['shippingCompany'];
$shippingPhoneNumber = $customerData['shippingPhoneNumber'];
$shippingAddress = $customerData['shippingAddress'];
$shippingCity = $customerData['shippingCity'];
$shippingState = $customerData['shippingState'];
$shippingZip = $customerData['shippingZip'];
$shippingCountry = $customerData['shippingCountry'];

?>
<h2>Subscription ID:<?php echo $subscriptionID ?></h2>

<h2>Billing Information</h2>
First Name: <?php echo $billingFirstName; ?><br/>
Last Name: <?php echo $billingLastName; ?><br/>
Email: <?php echo $billingEmail; ?><br/>
Company: <?php echo $billingCompany; ?><br/>
Phone Number: <?php echo $billingPhoneNumber; ?><br/>
Address: <?php echo $billingAddress; ?><br/>
City: <?php echo $billingCity; ?><br/>
State: <?php echo $billingState; ?><br/>
Zip: <?php echo $billingZip; ?><br/>
Country: <?php echo $billingCountry; ?><br/><br/>
Some form here for editing billing and updating shit etc..
and a cancel button



<h2>Shipping Information</h2>
First Name: <?php echo $shippingFirstName; ?><br/>
Last Name: <?php echo $shippingLastName; ?><br/>
Email: <?php echo $shippingEmail; ?><br/>
Company: <?php echo $shippingCompany; ?><br/>
Phone Number: <?php echo $shippingPhoneNumber; ?><br/>
Address: <?php echo $shippingAddress; ?><br/>
City: <?php echo $shippingCity; ?><br/>
State: <?php echo $shippingState; ?><br/>
Zip: <?php echo $shippingZip; ?><br/>
Country: <?php echo $shippingCountry; ?><br/><br/>
Some form here for editing shipping and updating shit etc..
<?php } ?>
<br/><br/>
<h2>Administration Tools</h2>
Cancel A Subscription<br/>
<form method="POST">
Subscription ID: <input type="text" name="subID"><br/>
Reason for Canceling: <input type="text" name="cancelReason"><br/>
<input type="submit" name="cancelSubscription" value="Cancel">
<?php 
echo '<pre>';
echo print_r($subscriptions);
echo '</pre>';
?>