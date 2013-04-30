<?php wp_enqueue_script("jquery"); ?>
<?php get_header();	?>
		<?php
		//open connector to billing class it will grab the users data if it exists.
		$billing = new billing(1351);
		$userData = $billing->userArray();
		$shippingData = $billing->shippingArray();
		//removed the call for billing data because the form always has empty billing data. Billing data is empty so the user can easily use different billing info for different orders. Each successfull order gets the proper billing data attached as META during the order process
		?>
		<script>
jQuery(document).ready(function($) {
	
    if ($("input[name=sameAddress]:checked").is(':checked')) {
		$('#billingInputs').hide();
      }	
	
	$("#userFirstName").on('keyup focusout', function(){
		$("#shippingFirstName").val(this.value);
	});
	$("#userLastName").on('keyup focusout', function(){
		$("#shippingLastName").val(this.value);
	});	
	$("#userCompany").on('keyup focusout', function(){
		$("#shippingCompany").val(this.value);
	});	
	$("#userEmail").on('keyup focusout', function(){
		$("#shippingEmail").val(this.value);
	});	
	$("#userPhoneNumber").on('keyup focusout', function(){
		$("#shippingPhoneNumber").val(this.value);
	});		
	
    $('input[name=sameAddress]').click(function() {
    //alert('Using the same address');
    if ($("input[name=sameAddress]:checked").is(':checked')) {
		$('#billingInputs').hide('slow');
      }else{
		$('#billingInputs').show('slow');
	  };
    });
});
		</script>
		
		<h2>Sign up now</h2>
		<br/>
		<form method="POST" action="/processing-order">
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
		
		<h2>Account Information</h2>
		<p>Please enter your account information we will use this information to create your account during the order process.<br/><br/><small>IP Address recorded for security purposes: <?php echo $userData[userIPAddress] ?></small></p>
		First Name: <input type="text" id="userFirstName" name="userFirstName" value="<?php echo $userData[userFirstName] ?>"><br/>
		Last Name: <input type="text" id="userLastName" name="userLastName" value="<?php echo $userData[userLastName] ?>"><br/>
		Company Name: <input type="text" id="userCompany" name="userCompany" value="<?php echo $userData[userCompany] ?>"><br/>
		Email: <input type="text" id="userEmail" name="userEmail" value="<?php echo $userData[userEmail] ?>"><br/>
		Phone Number: <input type="text" id="userPhoneNumber" name="userPhoneNumber" value="<?php echo $userData[userPhoneNumber] ?>"><br/><br/>
		
		<h2>Shipping Address</h2>
		First Name: <input type="text" id="shippingFirstName" name="shippingFirstName" value="<?php echo $shippingData[shippingFirstName] ?>"><br/>
		Last Name: <input type="text" id="shippingLastName" name="shippingLastName" value="<?php echo $shippingData[shippingLastName] ?>"><br/>
		Company: <input type="text" id="shippingCompany" name="shippingCompany" value="<?php echo $shippingData[shippingCompany] ?>"><br/>		
		Email: <input type="text" id="shippingEmail" name="shippingEmail" value="<?php echo $shippingData[shippingEmail] ?>"><br/>
		Phone Number: <input type="text" id="shippingPhoneNumber" name="shippingPhoneNumber" value="<?php echo $shippingData[shippingPhoneNumber] ?>"><br/>
		Address: <input type="text" id="shippingAddress" name="shippingAddress" value="<?php echo $shippingData[shippingAddress] ?>"><br/>
		City: <input type="text" id="shippingCity" name="shippingCity" value="<?php echo $shippingData[shippingCity] ?>"><br/>
		State/Province: <input type="text" id="shippingState" name="shippingState" value="<?php echo $shippingData[shippingState] ?>"><br/>
		Zip: <input type="text" id="shippingZip" name="shippingZip" value="<?php echo $shippingData[shippingZip] ?>"><br/>
		Country: <?php $billing->countrySelect('shippingCountry', $shippingData[shippingCountry]) ?><br/><br/>
		
		<h2>Billing Address</h2>
		<p>Same as Shipping <input type="checkbox" id="sameAddress" name="sameAddress" value="Y" checked="checked" /></p>
<div id="billingInputs">
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
		
<?php
$serviceArray = $billing->serviceArray();
echo '<pre>';
print_r($serviceArray);
echo '</pre>';
echo '<br/><br/>';

