<?php
/*
Order Process Template: Processes the submitted order data.
*/

if (isset($_POST['doSignUp'])){
	$billing = new billing();
	
	//userData
	$billing->userCompany = $_POST['userCompany'];
	$billing->userPhoneNumber = $_POST['userPhoneNumber'];
	
	//Shipping Information
	$billing->shippingFirstName = $_POST['shippingFirstName'];
	$billing->shippingLastName = $_POST['shippingLastName'];
	$billing->shippingEmail = $_POST['shippingEmail'];
	$billing->shippingCompany = $_POST['shippingCompany'];
	$billing->shippingPhoneNumber = $_POST['shippingPhoneNumber'];
	$billing->shippingAddress = $_POST['shippingAddress'];
	$billing->shippingCity = $_POST['shippingCity'];
	$billing->shippingState = $_POST['shippingState'];
	$billing->shippingZip = $_POST['shippingZip'];
	$billing->shippingCountry = $_POST['shippingCountry'];

	//Are we using the same info for billing as we did for shipping?
	if ($_POST['sameAddress'] == 'Y'){
		//User wants to use the shipping info for billing info.
		$billing->billingFirstName = $_POST['shippingFirstName'];
		$billing->billingLastName = $_POST['shippingLastName'];
		$billing->billingCompany = $_POST['shippingCompany'];
		$billing->billingEmail = $_POST['shippingEmail'];
		$billing->billingPhoneNumber = $_POST['shippingPhoneNumber'];
		$billing->billingAddress = $_POST['shippingAddress'];
		$billing->billingCity = $_POST['shippingCity'];
		$billing->billingState = $_POST['shippingState'];
		$billing->billingZip = $_POST['shippingZip'];
		$billing->billingCountry = $_POST['shippingCountry'];
	}else{
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
	}
	
	if ($billing->userID > 0){
	//user is logged into the new account or was already logged into current account.	
		if ($billing->transactionType == 'single'){
			$processPayment = $billing->processPayment();			
		}else{
			$processARB = $billing->processARB();			
		}
	}
}
?>
<?php get_header();	?>

<?php if ($billing->errorMessage){ ?>	
	<div class="Error">
		<?php echo $billing->errorMessage; ?>
		<input type="button" value="Try Again" onclick="window.history.go(-1)">
	</div>
<?php }else{ ?>
We are processing your order. Please hold.
<?php } ?>
<?php get_footer(); ?>

