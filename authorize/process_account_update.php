<?php
/*
Update Process Template: Processes the submitted data for updating subscription
*/

if (isset($_POST['doCancel'])){
	$billing = new billingUpdate($_POST['subscriptionPostID']);
	$billing->cancelSubscription();	
}

if (isset($_POST['doUpdate'])){
	
	$billing = new billingUpdate($_POST['subscriptionPostID']);
	
	//Billing Information for subscription
	$billing->billingFirstName = $_POST['billingFirstName'];
	$billing->billingLastName = $_POST['billingLastName'];
	$billing->billingCompany = $_POST['billingCompany'];
	$billing->billingEmail = $_POST['billingEmail'];
	$billing->billingPhoneNumber = $_POST['billingPhoneNumber'];
	$billing->billingAddress = $_POST['billingAddress'];
	$billing->billingCity = $_POST['billingCity'];
	$billing->billingState = $_POST['billingState'];
	$billing->billingZip = $_POST['billingZip'];
	$billing->billingCountry = $_POST['billingCountry'];
		
	//CC info for subscription
	$billing->ccNumber = $_POST['ccNumber'];		
	$billing->ccMonth = $_POST['ccMonth'];
	$billing->ccYear = $_POST['ccYear'];
	$billing->ccCode = $_POST['ccCode'];
	$billing->lastFour = substr($billing->ccNumber, -4);	

	
	if ($_POST['updateShipping'] == 'Y'){
		//shipping info for subscription
		$billing->shippingFirstName = $_POST['shippingFirstName'];
		$billing->shippingLastName = $_POST['shippingLastName'];
		$billing->shippingCompany = $_POST['shippingCompany'];
		$billing->shippingAddress = $_POST['shippingAddress'];
		$billing->shippingCity = $_POST['shippingCity'];
		$billing->shippingState = $_POST['shippingState'];
		$billing->shippingZip = $_POST['shippingZip'];
		$billing->shippingCountry = $_POST['shippingCountry'];
	}
	
	$billing->processBillingUpdate();
}
?>
<?php wp_enqueue_script("jquery"); ?>
<?php get_header();	?>

<?php if ($billing->response){ ?>	
	<div class="Error">
		<?php echo $billing->response; ?>
	</div>
<?php } ?>


<?php
	//open connector to the billing class so it can grab some data for this user for use in the billing update form
	//REQUIRED for the class and functions. You must pass the ID of the post containing the subscription being updated.
	$subscriptionPostID = '2762';
	
	$subscription = new billingUpdate($subscriptionPostID);
	$subscriptionData = $subscription->subscriptionDataArray();	
	
	//we are borrowing the form helper functions from another class here. Using billing class we show the input elements for country, ccmonth and ccyear in the form below
	$billing = new billing();
	
?>
		<script>
jQuery(document).ready(function($) {
	
	$('#shippingInputs').hide();
      
	
    $('input[name=updateShipping]').click(function() {
    //alert('Using the same address');
    if ($("input[name=updateShipping]:checked").is(':checked')) {
		$('#shippingInputs').show('slow');
      }else{
		$('#shippingInputs').hide('slow');
	  };
    });
});
		</script>
		<form method="POST">
		<input type="hidden" name="subscriptionPostID" value="<?php echo $subscriptionPostID ?>">
		
		<h2>Update Billing Address</h2>
		First Name: <input type="text" id="billingFirstName" name="billingFirstName" value="<?php echo $subscriptionData[billingFirstName] ?>"><br/>
		Last Name: <input type="text" id="billingLastName" name="billingLastName" value="<?php echo $subscriptionData[billingLastName] ?>"><br/>
		Company: <input type="text" id="billingCompany" name="billingCompany" value="<?php echo $subscriptionData[billingCompany] ?>"><br/>
		Email: <input type="text" id="billingEmail" name="billingEmail" value="<?php echo $subscriptionData[billingEmail] ?>"><br/>		
		Phone Number: <input type="text" id="billingPhoneNumber" name="billingPhoneNumber" value="<?php echo $subscriptionData[billingPhoneNumber] ?>"><br/>
		Address: <input type="text" id="billingAddress" name="billingAddress" value="<?php echo $subscriptionData[billingAddress] ?>"><br/>
		City: <input type="text" id="billingCity" name="billingCity" value="<?php echo $subscriptionData[billingCity] ?>"><br/>
		State/Province: <input type="text" id="billingState" name="billingState" value="<?php echo $subscriptionData[billingState] ?>"><br/>
		Zip: <input type="text" id="billingZip" name="billingZip" value="<?php echo $subscriptionData[billingZip] ?>"><br/>
		Country: <?php $billing->countrySelect('billingCountry') ?>
		<br/><br/>
		<h2>Shipping Address</h2>
		<p>Update Shipping Address <input type="checkbox" id="updateShipping" name="updateShipping" value="Y" /></p><br/><br/>
<div id="shippingInputs">	
		First Name: <input type="text" id="shippingFirstName" name="shippingFirstName" value="<?php echo $subscriptionData[shippingFirstName] ?>"><br/>
		Last Name: <input type="text" id="shippingLastName" name="shippingLastName" value="<?php echo $subscriptionData[shippingLastName] ?>"><br/>
		Company: <input type="text" id="shippingCompany" name="shippingCompany" value="<?php echo $subscriptionData[shippingCompany] ?>"><br/>
		Address: <input type="text" id="shippingAddress" name="shippingAddress" value="<?php echo $subscriptionData[shippingAddress] ?>"><br/>
		City: <input type="text" id="shippingCity" name="shippingCity" value="<?php echo $subscriptionData[shippingCity] ?>"><br/>
		State/Province: <input type="text" id="shippingState" name="shippingState" value="<?php echo $subscriptionData[shippingState] ?>"><br/>
		Zip: <input type="text" id="shippingZip" name="shippingZip" value="<?php echo $subscriptionData[shippingZip] ?>"><br/>
		Country: <?php $billing->countrySelect('shippingCountry', $subscriptionData[shippingCountry]) ?><br/><br/>
</div>

<br/><br/>
		<h2>Credit Card Information</h2>
		<small>User the following numbers to test<br/><br/>
		42222222222222 = Error<br/>
		4007000000027 = Valid Card<br/>
		</small><br/><br/>
		Credit Card Number: <input type="text" name="ccNumber"><br/><br/>
		Expiration Date: <?php $billing->monthSelect('ccMonth') ?> - <?php $billing->yearSelect('ccYear') ?><br/><br/>
		Security Code<input type="text" name="ccCode"><br/><br/>
		<input type="submit" name="doUpdate" value="Update Billing Information">
		</form>
		<br/><br/>
		<form method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription?');">
		<input type="hidden" name="subscriptionPostID" value="<?php echo $subscriptionPostID ?>">
		<input type="submit" name="doCancel" value="Cancel Subscription">
		</form>
<?php get_footer(); ?>