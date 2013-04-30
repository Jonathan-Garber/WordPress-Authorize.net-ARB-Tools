		<?php
		//open connector to billing class with post ID of the service/product we need
		$billing = new billing($post->ID);
		$serviceData = $billing->serviceArray();
		$shippingData = $billing->shippingArray();
		$billingData = $billing->billingArray();
		?>
		<h2>Sign up now</h2>
		<?php echo $serviceData['name']; ?>
		<br/><br/>
		<form method="POST">
		<h2>Shipping Address</h2>
		First Name: <input type="text" name="shippingFirstName" value="<?php echo $shippingData[shippingFirstName] ?>"><br/>
		Last Name: <input type="text" name="shippingLastName" value="<?php echo $shippingData[shippingLastName] ?>"><br/>
		Email: <input type="text" name="shippingEmail" value="<?php echo $shippingData[shippingEmail] ?>"><br/>
		Phone Number: <input type="text" name="shippingPhoneNumber" value="<?php echo $shippingData[shippingPhoneNumber] ?>"><br/>
		Address: <input type="text" name="shippingAddress" value="<?php echo $shippingData[shippingAddress] ?>"><br/>
		City: <input type="text" name="shippingCity" value="<?php echo $shippingData[shippingCity] ?>"><br/>
		State/Province: <input type="text" name="shippingState" value="<?php echo $shippingData[shippingState] ?>"><br/>
		Zip: <input type="text" name="shippingZip" value="<?php echo $shippingData[shippingZip] ?>"><br/>
		Country: <input type="text" name="shippingCountry" value="<?php echo $shippingData[shippingCountry] ?>"><br/><br/>

		<h2>Billing Address</h2>
		First Name: <input type="text" name="billingFirstName" value="<?php echo $billingData[billingFirstName] ?>"><br/>
		Last Name: <input type="text" name="billingLastName" value="<?php echo $billingData[billingLastName] ?>"><br/>
		Email: <input type="text" name="billingEmail" value="<?php echo $billingData[billingEmail] ?>"><br/>
		Phone Number: <input type="text" name="billingPhoneNumber" value="<?php echo $billingData[billingPhoneNumber] ?>"><br/>
		Address: <input type="text" name="billingAddress" value="<?php echo $billingData[billingAddress] ?>"><br/>
		City: <input type="text" name="billingCity" value="<?php echo $billingData[billingCity] ?>"><br/>
		State/Province: <input type="text" name="billingState" value="<?php echo $billingData[billingState] ?>"><br/>
		Zip: <input type="text" name="billingZip" value="<?php echo $billingData[billingZip] ?>"><br/>
		Country: <input type="text" name="billingCountry" value="<?php echo $billingData[billingCountry] ?>"><br/><br/>

		<h2>Credit Card Information</h2>
		Credit Card Number: <input type="text" name="ccNumber"><br/><br/>

		Expiration Date: <?php $billing->monthSelect('ccMonth') ?> - <?php $billing->yearSelect('ccYear') ?><br/><br/>

		Security Code<input type="text" name="ccCode"><br/><br/>

		<input type="submit" name="doSignUp" value="Sign up" disabled>
		</form>	