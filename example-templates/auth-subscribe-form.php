<?php
/*
Template Name: Subscribe Form
*/


if (isset($_POST['doSignUp'])){
	$billing = new billing();
	
	//userData
	$billing->userCompany = $_POST['userCompany'];
	$billing->userPhoneNumber = $_POST['userPhoneNumber'];
	
	//Billing Information is different from shipping information..
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
	
	
	//do we have addon data?
	$billing->addonData = $_POST['addonData'];

	//Are we using the same info for billing as we did for shipping?
	if ($_POST['sameAddress'] == 'Y'){
		//User wants to use the billing info for shipping info.
		$billing->shippingFirstName = $_POST['billingFirstName'];
		$billing->shippingLastName = $_POST['billingLastName'];
		$billing->shippingCompany = $_POST['billingCompany'];
		$billing->shippingAddress = $_POST['billingAddress'];
		$billing->shippingCity = $_POST['billingCity'];
		$billing->shippingState = $_POST['billingState'];
		$billing->shippingZip = $_POST['billingZip'];
		$billing->shippingCountry = $_POST['billingCountry'];
	}else{
		//Billing Information is different from shipping information..
		$billing->shippingFirstName = $_POST['shippingFirstName'];
		$billing->shippingLastName = $_POST['shippingLastName'];
		$billing->shippingCompany = $_POST['shippingCompany'];
		$billing->shippingAddress = $_POST['shippingAddress'];
		$billing->shippingCity = $_POST['shippingCity'];
		$billing->shippingState = $_POST['shippingState'];
		$billing->shippingZip = $_POST['shippingZip'];
		$billing->shippingCountry = $_POST['shippingCountry'];
	}
		
	//CC info		
	$billing->ccNumber = $_POST['ccNumber'];		
	$billing->ccMonth = $_POST['ccMonth'];
	$billing->ccYear = $_POST['ccYear'];
	$billing->ccCode = $_POST['ccCode'];
	$billing->lastFour = substr($billing->ccNumber, -4);
	
	//product or service information		
	$billing->productName = $_POST['productName'];
	$billing->productDescription = $_POST['productDescription'];
	$billing->productAmount = $_POST['productAmount'];
	$billing->productOccurrences = $_POST['productOccurrences'];		
	
	//is this a subscription or single payment?
	if ($billing->productOccurrences > 1){
		$billing->transactionType = 'arb';
		$billing->productUnit = $_POST['productUnit'];
		$billing->productInterval = $_POST['productInterval'];
		$billing->productTrial = $_POST['productTrial'];
		
		/*
		is there a trial period
		if not the gateway still requires u to either pass proper data for those fields or ommit those fields. Easiest solution is to simply pass the proper data since its all right here already in code.
		*/
			if ($billing->productTrial == 'yes'){
				$billing->productTrialOccurrences = $_POST['productTrialOccurrences'];
				$billing->productTrialAmount = $_POST['productTrialAmount'];				
			}else{
				$billing->productTrialOccurrences = '0';
				$billing->productTrialAmount = '0.00';
			}
		
	}else{
		$billing->transactionType = 'single';
	}
	
	//user is not logged in if ID is 0
	if ($billing->userID == 0){
		$createUser = $billing->createUser();
		
		//if the ID is now higher then 0 then the user is logged in
		if ($createUser != 0){
			if ($billing->transactionType == 'single'){
				$processPayment = $billing->processPayment();			
			}else{
				$processARB = $billing->processARB();
			}
		}
	}else{
		//user is logged into the new account or was already logged into current account.	
		if ($billing->transactionType == 'single'){
			$processPayment = $billing->processPayment();			
		}else{
			$processARB = $billing->processARB();
		}
	}
}

?>
<?php wp_enqueue_script("jquery"); ?>
<?php get_header(); ?>

<?php if ($billing->errorMessage){ ?>	
	<div class="Error">
		<?php echo $billing->errorMessage; ?>
	</div>
<?php }?>
<?php
		$billing = new billing();
		$userData = $billing->userArray();
		$billingData = $billing->billingArray();
?>
		<script>
jQuery(document).ready(function($) {
	
    if ($("input[name=sameAddress]:checked").is(':checked')) {
		$('#shippingInputs').hide();
      }	
	
	$("#userFirstName").on('keyup focusout', function(){
		$("#billingFirstName").val(this.value);
	});
	$("#userLastName").on('keyup focusout', function(){
		$("#billingLastName").val(this.value);
	});	
	$("#userCompany").on('keyup focusout', function(){
		$("#billingCompany").val(this.value);
	});	
	$("#userEmail").on('keyup focusout', function(){
		$("#billingEmail").val(this.value);
	});	
	$("#userPhoneNumber").on('keyup focusout', function(){
		$("#billingPhoneNumber").val(this.value);
	});		
	
    $('input[name=sameAddress]').click(function() {
    //alert('Using the same address');
    if ($("input[name=sameAddress]:checked").is(':checked')) {
		$('#shippingInputs').hide('slow');
      }else{
		$('#shippingInputs').show('slow');
	  };
    });
});
		</script>
		
		<h2>Sign up now</h2>
		<br/>
		<form method="POST">
		<input type="hidden" name="userID" value="<?php echo $billing->userID ?>">
		<input type="hidden" name="productName" value="Starbox Subscription">
		<input type="hidden" name="productDescription" value="Starbox monthly order">
		<input type="hidden" name="productAmount" value="15.00">
		<input type="hidden" name="productUnit" value="months">
		<input type="hidden" name="productInterval" value="1">
		<input type="hidden" name="productOccurrences" value="50">
		<!--
		Used for enabling free or lower cost trial periods
		<input type="hidden" name="productTrial" value="no">
		<input type="hidden" name="productTrialOccurrences" value="0">
		<input type="hidden" name="productTrialAmount" value="0.00">
		-->
		
		<h2>Test Additional Data</h2>
		Birthday: <input type="text" name="addonData[birthday]" value=""><br/>
		Referral: <input type="text" name="addonData[referrals]" value=""><br/><br/>
		
		<h2>Account Information</h2>
		<p>Please enter your account information we will use this information to create your account during the order process.<br/><br/><small>IP Address recorded for security purposes: <?php echo $userData[userIPAddress] ?></small></p><br/>
		First Name: <input type="text" id="userFirstName" name="userFirstName" value="<?php echo $userData[userFirstName] ?>"><br/>
		Last Name: <input type="text" id="userLastName" name="userLastName" value="<?php echo $userData[userLastName] ?>"><br/>
		Company Name: <input type="text" id="userCompany" name="userCompany" value="<?php echo $userData[userCompany] ?>"><br/>
		Email: <input type="text" id="userEmail" name="userEmail" value="<?php echo $userData[userEmail] ?>"><br/>
		Phone Number: <input type="text" id="userPhoneNumber" name="userPhoneNumber" value="<?php echo $userData[userPhoneNumber] ?>"><br/><br/>
		
		<h2>Billing Address</h2>
		First Name: <input type="text" id="billingFirstName" name="billingFirstName" value="<?php echo $billingData[billingFirstName] ?>"><br/>
		Last Name: <input type="text" id="billingLastName" name="billingLastName" value="<?php echo $billingData[billingLastName] ?>"><br/>
		Company: <input type="text" id="billingCompany" name="billingCompany" value="<?php echo $billingData[billingCompany] ?>"><br/>			
		Email: <input type="text" id="billingEmail" name="billingEmail" value="<?php echo $billingData[billingEmail] ?>"><br/>
		Phone Number: <input type="text" id="billingPhoneNumber" name="billingPhoneNumber" value="<?php echo $billingData[billingPhoneNumber] ?>"><br/>
		Address: <input type="text" id="billingAddress" name="billingAddress" value="<?php echo $billingData[billingAddress] ?>"><br/>
		City: <input type="text" id="billingCity" name="billingCity" value="<?php echo $billingData[billingCity] ?>"><br/>
		State/Province: <input type="text" id="billingState" name="billingState" value="<?php echo $billingData[billingState] ?>"><br/>
		Zip: <input type="text" id="billingZip" name="billingZip" value="<?php echo $billingData[billingZip] ?>"><br/>
		Country: <?php $billing->countrySelect('billingCountry') ?>
		<br/><br/>
		<h2>Shipping Address</h2>
		<p>Same as Billing Address <input type="checkbox" id="sameAddress" name="sameAddress" value="Y" checked="checked" /></p><br/><br/>
<div id="shippingInputs">	
		First Name: <input type="text" id="shippingFirstName" name="shippingFirstName" value="<?php echo $shippingData[shippingFirstName] ?>"><br/>
		Last Name: <input type="text" id="shippingLastName" name="shippingLastName" value="<?php echo $shippingData[shippingLastName] ?>"><br/>
		Company: <input type="text" id="shippingCompany" name="shippingCompany" value="<?php echo $shippingData[shippingCompany] ?>"><br/>
		Address: <input type="text" id="shippingAddress" name="shippingAddress" value="<?php echo $shippingData[shippingAddress] ?>"><br/>
		City: <input type="text" id="shippingCity" name="shippingCity" value="<?php echo $shippingData[shippingCity] ?>"><br/>
		State/Province: <input type="text" id="shippingState" name="shippingState" value="<?php echo $shippingData[shippingState] ?>"><br/>
		Zip: <input type="text" id="shippingZip" name="shippingZip" value="<?php echo $shippingData[shippingZip] ?>"><br/>
		Country: <?php $billing->countrySelect('shippingCountry', $shippingData[shippingCountry]) ?><br/><br/>
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
		<input type="submit" name="doSignUp" value="Place Order">
		</form>
<?php get_footer(); ?>

