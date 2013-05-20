<?php
/*
Template Name: Account Management
*/

/*
	If logged in else redirect to login
	if admin and has ghostID load ghosts subscriptions
	if admin and no ghostID load the admins subscriptions -probably never used-
	if not admin and logged in log current users id and show their subs

	this way the system locks down to only admins being able to ghost a user
*/

if ( is_user_logged_in() ) {
	if ( current_user_can( 'manage_options' ) ) {
		 if (isset($_GET['ghostID'])){
			$userID = $_GET['ghostID'];
		}else{
			$userID = get_current_user_id();			
		}
	}else{
		/*
			This stops a user that is not admin from even trying to open a ghostID.
			it redirects any user with a ghostID who is not admin to the homepage.
		*/
		 if (isset($_GET['ghostID'])){
			wp_redirect('/');
		}else{
			$userID = get_current_user_id();
		}
	}
}else{
wp_redirect('/wp-login.php');
}

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
	
	if ($_POST['updateCC'] == 'Y'){
		$billing->processBillingUpdate();
	}else{
		$billing->processBillingUpdateNoCC();
	}
}
?>
<?php wp_enqueue_script("jquery"); ?>
<?php get_header(); ?>
<script>
jQuery(document).ready(function($) {
	
	$('#shippingInputs').hide();
    $('#ccInputs').hide();
	
    $('input[name=updateShipping]').click(function() {
    //alert('Using the same address');
    if ($("input[name=updateShipping]:checked").is(':checked')) {
		$('#shippingInputs').show('slow');
      }else{
		$('#shippingInputs').hide('slow');
	  };
    });
	
    $('input[name=updateCC]').click(function() {
    if ($("input[name=updateCC]:checked").is(':checked')) {
		$('#ccInputs').show('slow');
      }else{
		$('#ccInputs').hide('slow');
	  };
    });
	
});
</script>
<style>
.table {
width: 100%;
}

.table th {
padding: 8px;
}

.table td {
padding: 8px;
}
</style>
<h2>Your Subscriptions</h2><br/>
<?php 
$args = array(
		'post_type' => 'auth-subscriptions',
		'author' => $userID,
		);
$sub_array = get_posts($args);
?>
<table class="table">
	<thead>
		<tr>
			<th>Subscription ID</th>
			<th>Subscription Name</th>
			<th>Card Number</th>
			<th>Card Expiration</th>
			<th>Cost</th>
			<th>Status</th>
			<th>Options</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th>Subscription ID</th>
			<th>Subscription Name</th>
			<th>Card Number</th>
			<th>Card Expiration</th>
			<th>Cost</th>
			<th>Status</th>
			<th>Options</th>
		</tr>
	</tfoot>
<?php foreach ($sub_array as $sub) { 
		$subscriptionPostID = $sub->ID;
		$subscriptionID = get_post_meta($subscriptionPostID, 'subscriptionID', true);
		$subscriptionName = get_post_meta($subscriptionPostID, 'subscriptionName', true);
		$subscriptionAmount = get_post_meta($subscriptionPostID, 'subscriptionAmount', true);
		$ccLastFour = get_post_meta($subscriptionPostID, 'ccLastFour', true);
		$ccMonth = get_post_meta($subscriptionPostID, 'ccMonth', true);
		$ccYear = get_post_meta($subscriptionPostID, 'ccYear', true);
		$subscriptionStatus = get_post_meta($subscriptionPostID, 'subscriptionStatus', true);
		
		if ($subscriptionStatus == 'cancelled'){
			$subscriptionCancelledBy = get_post_meta($subscriptionPostID, 'subscriptionCancelledBy', true);
			$status = 'Cancelled by '.$subscriptionCancelledBy;
		}else{
			$status = $subscriptionStatus;
		}		
		
		
		
?>
<tr>
<td><?php echo $subscriptionID; ?></td>
<td><?php echo $subscriptionName; ?></td>
<td><?php echo $ccLastFour; ?></td>
<td><?php echo $ccMonth.'-'.$ccYear; ?></td>
<td><?php echo $subscriptionAmount; ?></td>
<td><?php echo $status; ?></td>
<td>
<?php if ($status == 'active') { ?>
<form method="POST"><input type="hidden" name="subscriptionPostID" value="<?php echo $subscriptionPostID ?>"><input type="submit" name="doUpdateForm" value="Update Subscription"></form>
<?php }else{ ?>
No options available.
<?php } ?>
</td>
</tr>
<?php } ?>
</table>
<br/><br/>
<?php if ($billing->response){ ?>	
	<div class="Error">
		<?php echo $billing->response; ?>
	</div>
<?php } ?>
<?php

//Shows the update form for whatever subscription was clicked
if (isset($_POST['doUpdateForm'])){
	
	//open connector to the billing class so it can grab some data for this user for use in the billing update form
	//REQUIRED for the class and functions. You must pass the ID of the post containing the subscription being updated.
	$subscriptionPostID = $_POST['subscriptionPostID'];
	$subscription = new billingUpdate($subscriptionPostID);
	$subscriptionData = $subscription->subscriptionDataArray();
	
	//we are borrowing the form helper functions from another class here. Using billing class we show the input elements for country, ccmonth and ccyear in the form below
	$billing = new billing();
?>
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
<p>Update Credit Card <input type="checkbox" id="updateCC" name="updateCC" value="Y" /></p><br/><br/>
<div id="ccInputs">
		<small>User the following numbers to test<br/><br/>
		42222222222222 = Error<br/>
		4007000000027 = Valid Card<br/>
		</small><br/><br/>
		Credit Card Number: <input type="text" name="ccNumber"><br/><br/>
		Expiration Date: <?php $billing->monthSelect('ccMonth') ?> - <?php $billing->yearSelect('ccYear') ?><br/><br/>
		Security Code<input type="text" name="ccCode"><br/><br/>
</div>
		<input type="submit" name="doUpdate" value="Update Subscription">
		</form>
		<br/><br/>
		<form method="POST" onsubmit="return confirm('Are you sure you want to cancel your subscription?');">
		<input type="hidden" name="subscriptionPostID" value="<?php echo $subscriptionPostID ?>">
		<input type="submit" name="doCancel" value="Cancel Subscription">
		</form>
<?php
}
?>
<?php get_footer(); ?>