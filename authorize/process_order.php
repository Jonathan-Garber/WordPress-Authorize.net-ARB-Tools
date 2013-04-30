<?php
/*
Order Process Template: Processes the submitted order data.
*/

if (isset($_POST['doSignUp'])){
	$billing = new billing();
	
	//Shipping Information
	$billing->shippingFirstName = $_POST['shippingFirstName'];
	$billing->shippingLastName = $_POST['shippingLastName'];
	$billing->shippingEmail = $_POST['shippingEmail'];
	$billing->shippingPhoneNumber = $_POST['shippingPhoneNumber'];
	$billing->shippingAddress = $_POST['shippingAddress'];
	$billing->shippingCity = $_POST['shippingCity'];
	$billing->shippingState = $_POST['shippingState'];
	$billing->shippingZip = $_POST['shippingZip'];
	$billing->shippingCountry = $_POST['shippingCountry'];

	//Billing Information
	$billing->billingFirstName = $_POST['billingFirstName'];
	$billing->billingLastName = $_POST['billingLastName'];
	$billing->billingEmail = $_POST['billingEmail'];
	$billing->billingPhoneNumber = $_POST['billingPhoneNumber'];
	$billing->billingAddress = $_POST['billingAddress'];
	$billing->billingCity = $_POST['billingCity'];
	$billing->billingState = $_POST['billingState'];
	$billing->billingZip = $_POST['billingZip'];
	$billing->billingCountry = $_POST['billingCountry'];
		
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
		
		//is there a trial period
			if ($billing->productTrial == 'yes'){
				$billing->productTrialOccurrences = $_POST['productTrialOccurrences'];
				$billing->productTrialAmount = $_POST['productTrialAmount'];				
			}
		
	}else{
		$billing->transactionType = 'single';
	}
	
	//user is not logged in if ID is 0
	if ($billing->userID == 0){
		$createUser = $billing->createUser();
		
		if ($createUser == 'registered'){
			//the user was successfully registered continue with transaction
			if ($billing->transactionType == 'single'){
				$billing->processPayment();
			}else{
				$billing->processARB();
			}				
		}else{
			//is an error during creation so we set the output to an error variable to be used on screen
			$error = $createUser;
		}
			
	}else{
	//user is already logged into an account. So we go ahead and process the order under this account.
		if ($billing->transactionType == 'single'){
			$billing->processPayment();
		}else{
			$billing->processARB();
		}
	}
}
?>
<?php get_header();	?>

<?php if ($error){ ?>	
	<div class="Error">
		<?php echo $error; ?>
	</div>	
<?php }else{ ?>
We are processing your order. Please hold.
<?php } ?>
<?php get_footer(); ?>

