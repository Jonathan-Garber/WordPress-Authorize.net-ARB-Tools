<?php

class billingUpdate{



	public function __construct($subscriptionPostID){

	

		//API Login

		$this->apiLogin = get_option('apiLogin');

		$this->apiKey = get_option('apiKey');

		$this->apiTestMode = get_option('apiTestMode');

		$this->apiEmail = get_option('apiEmail');

		
		$this->dateToday = date('Y-m-d', current_time('timestamp') );

	

		//sets the user ID of the logged in user

		$this->userID = get_current_user_id();

	

		//get the current information for this subscription		

		$this->subscriptionPostID = $subscriptionPostID;		



		/*

			current shipping data from the subscription

		*/

		$this->shippingFirstName = get_post_meta($this->subscriptionPostID, 'shippingFirstName', true);

		$this->shippingLastName = get_post_meta($this->subscriptionPostID, 'shippingLastName', true);

		$this->shippingCompany = get_post_meta($this->subscriptionPostID, 'shippingCompany', true);

		$this->shippingAddress = get_post_meta($this->subscriptionPostID, 'shippingAddress', true);

		$this->shippingCity = get_post_meta($this->subscriptionPostID, 'shippingCity', true);

		$this->shippingState = get_post_meta($this->subscriptionPostID, 'shippingState', true);

		$this->shippingZip = get_post_meta($this->subscriptionPostID, 'shippingZip', true);

		$this->shippingCountry = get_post_meta($this->subscriptionPostID, 'shippingCountry', true);	

		

		

		/*

			current billing data from the subscription

		*/		

		$this->billingFirstName = get_post_meta($this->subscriptionPostID, 'billingFirstName', true);

		$this->billingLastName = get_post_meta($this->subscriptionPostID, 'billingLastName', true);

		$this->billingEmail = get_post_meta($this->subscriptionPostID, 'billingEmail', true);

		$this->billingCompany = get_post_meta($this->subscriptionPostID, 'billingCompany', true);

		$this->billingPhoneNumber = get_post_meta($this->subscriptionPostID, 'billingPhoneNumber', true);

		$this->billingAddress = get_post_meta($this->subscriptionPostID, 'billingAddress', true);

		$this->billingCity = get_post_meta($this->subscriptionPostID, 'billingCity', true);

		$this->billingState = get_post_meta($this->subscriptionPostID, 'billingState', true);

		$this->billingZip = get_post_meta($this->subscriptionPostID, 'billingZip', true);

		$this->billingCountry = get_post_meta($this->subscriptionPostID, 'billingCountry', true);

		

		/*

			current data as stored in the subscription

		*/

		$this->subscriptionID = get_post_meta($this->subscriptionPostID, 'subscriptionID', true);

		$this->subscriptionName = get_post_meta($this->subscriptionPostID, 'subscriptionName', true);		

		$this->subscriptionAmount = get_post_meta($this->subscriptionPostID, 'subscriptionAmount', true);		

		$this->subscriptionLastBillingDate = get_post_meta($this->subscriptionPostID, 'subscriptionLastBillingDate', true);

		$this->subscriptionNextBillingDate = get_post_meta($this->subscriptionPostID, 'subscriptionNextBillingDate', true);

		$this->subscriptionPaymentNumber = get_post_meta($this->subscriptionPostID, 'subscriptionPaymentNumber', true);

		$this->subscriptionStatus = get_post_meta($this->subscriptionPostID, 'subscriptionStatus', true);

		$this->ccLastFour = get_post_meta($this->subscriptionPostID, 'ccLastFour', true);

		$this->ccMonth = get_post_meta($this->subscriptionPostID, 'ccMonth', true);

		$this->ccYear = get_post_meta($this->subscriptionPostID, 'ccYear', true);

		$this->subscriptionInterval = get_post_meta($this->subscriptionPostID, 'subscriptionInterval', true);

		$this->subscriptionUnit = get_post_meta($this->subscriptionPostID, 'subscriptionUnit', true);		

		$this->transactionInvoiceNumber = get_post_meta($this->subscriptionPostID, 'subscriptionInvoiceNumber', true);

	}

	

	

	public function cancelSubscription($canceledBy = ''){

	

		if ($canceledBy){
			$this->canceledBy = $canceledBy;
		}

		

		$this->refID = 'BUCANCEL-UID-'.$this->userID;

		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);

	    $xml->ARBCancelSubscriptionRequest(array(

			'refId' => $this->refID,

			'subscriptionId' => $this->subscriptionID,

		));

		

		if ($xml->isSuccessful()){
			update_post_meta($this->subscriptionPostID, 'subscriptionStatus', 'canceled');
			update_post_meta($this->subscriptionPostID, 'subscriptionCanceledDate', $this->dateToday);
			update_post_meta($this->subscriptionPostID, 'subscriptionCanceledBy', $this->canceledBy);
			
			$this->authResponse = 'canceled';
			$this->response = 'Subscription Canceled.';

		}else{
			$this->response = (string) 'Subscription Cancel Error: '.$xml->messages->message->text;
		}

	}

	

	public function billingArray(){

		//set us up the array		

		$array = array();

		

		//billing		

		$array['billingFirstName'] = $this->billingFirstName;

		$array['billingLastName'] = $this->billingLastName;

		$array['billingEmail'] = $this->billingEmail;

		$array['billingPhoneNumber'] = $this->billingPhoneNumber;

		$array['billingCompany'] = $this->billingCompany;				

		$array['billingAddress'] = $this->billingAddress;

		$array['billingCity'] = $this->billingCity;

		$array['billingState'] = $this->billingState;

		$array['billingZip'] = $this->billingZip;

		$array['billingCountry'] = $this->billingCountry;

		$array['subscriptionID'] = $this->subscriptionID;

		return $array;	

	}

	

	public function shippingArray(){

		//set us up the array		

		$array = array();

		

		$array['shippingFirstName'] = $this->shippingFirstName;

		$array['shippingLastName'] = $this->shippingLastName;

		$array['shippingCompany'] = $this->shippingCompany;				

		$array['shippingAddress'] = $this->shippingAddress;

		$array['shippingCity'] = $this->shippingCity;

		$array['shippingState'] = $this->shippingState;

		$array['shippingZip'] = $this->shippingZip;

		$array['shippingCountry'] = $this->shippingCountry;	

		

		return $array;

	

	}

	

	public function subscriptionDataArray(){	

		//set us up the array		

		$array = array();

		

		//shipping		

		$array['billingFirstName'] = $this->billingFirstName;

		$array['billingLastName'] = $this->billingLastName;

		$array['billingEmail'] = $this->billingEmail;

		$array['billingPhoneNumber'] = $this->billingPhoneNumber;

		$array['billingCompany'] = $this->billingCompany;				

		$array['billingAddress'] = $this->billingAddress;

		$array['billingCity'] = $this->billingCity;

		$array['billingState'] = $this->billingState;

		$array['billingZip'] = $this->billingZip;

		$array['billingCountry'] = $this->billingCountry;



		$array['shippingFirstName'] = $this->shippingFirstName;

		$array['shippingLastName'] = $this->shippingLastName;

		$array['shippingCompany'] = $this->shippingCompany;				

		$array['shippingAddress'] = $this->shippingAddress;

		$array['shippingCity'] = $this->shippingCity;

		$array['shippingState'] = $this->shippingState;

		$array['shippingZip'] = $this->shippingZip;

		$array['shippingCountry'] = $this->shippingCountry;



		$array['subscriptionID'] = $this->subscriptionID;

		

		return $array;

	

	}



	

	public function processBillingUpdateNoCC(){
		error_log("Updating subscription address only", 0);
		$arbStatus = wpat_getARBSubscriptionStatus($this->subscriptionID);
		if ($this->subscriptionStatus == 'suspended') {
				error_log("Updating cancelled - subscription suspended and requires full billing update", 0);
				$this->response = 'We are sorry but your subscription is currently "Suspended". You must update payment information in order to make any changes to this subscription.';
		}else if ($this->subscriptionStatus == 'active') {
			if ($arbStatus ==  'active' || $arbStatus == 'suspended'){
				$this->updateARBNoCC();
			}else{
				error_log("Updating Cancelled Internal Error", 0);
				$this->response = 'There is an internal error with this subscription. Making any changes to this subscription is currently unavailable. An Administrator has been notified of this error.';
				wp_mail($this->apiEmail, 'Internal Billing Update Error', 'Internal Update Error for this subscription ID'. $this->subscriptionID);
			}
		}
		
		if ($this->arbUpdateStatus == 'Ok'){

			$this->updateSubscriptionMeta();

			$this->response = 'Your subscription information has been updated.';

		}

	}

	

	public function updateARBNoCC($userID = false){

		$this->refID = 'BU-UID-'.$this->userID;

		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);

		

		if ($userID){

			$this->userID = $userID;

				$xml->ARBUpdateSubscriptionRequest(array(

					'refId' => $this->refID,

					'subscriptionId' => $this->subscriptionID,

					'subscription' => array(

						'customer' => array(

							'id' => $this->userID,

						)

					)

				));

				

		}else{

			$xml->ARBUpdateSubscriptionRequest(array(

				'refId' => $this->refID,

				'subscriptionId' => $this->subscriptionID,

				'subscription' => array(

					'customer' => array(

						'id' => $this->userID,

						'email' => $this->billingEmail,

						'phoneNumber' => $this->billingPhoneNumber,

					),

					'billTo' => array(

						'firstName' => $this->billingFirstName,

						'lastName' => $this->billingLastName,

						'company' => $this->billingCompany,

						'address' => $this->billingAddress,

						'city' => $this->billingCity,

						'state' => $this->billingState,

						'zip' => $this->billingZip,

						'country' => $this->billingCountry,

					),

					'shipTo' => array(

						'firstName' => $this->shippingFirstName,

						'lastName' => $this->shippingLastName,

						'company' => $this->shippingCompany,

						'address' => $this->shippingAddress,

						'city' => $this->shippingCity,

						'state' => $this->shippingState,

						'zip' => $this->shippingZip,

						'country' => $this->shippingCountry,

					)				

				),

			));	

		}		

		if ($xml->isSuccessful()){
			error_log("Updating Subscription Address Successful", 0);
			$this->arbUpdateStatus = (string) $xml->messages->resultCode;			

		}else{
			error_log("Updating Subscription Address Failed", 0);
			$this->response = (string) 'ARB Update Error: '.$xml->messages->message->text;			

		}

	}

	public function processBillingUpdate(){
		error_log("Updating Subscription Full Billing", 0);
		//get status of this subscription right away from ARB
		$arbStatus = wpat_getARBSubscriptionStatus($this->subscriptionID);
		
		/*
			if we marked it suspended that means we failed to take payment on last run
			now we check to see what auth has it marked to determine what steps to take next
		*/
		
		if ($this->subscriptionStatus == 'suspended') {
			error_log("Subscription Suspended on SB", 0);
			if ($arbStatus ==  'suspended'){
				/*
					Auth has suspended this ARB on their end just like we did on ours due to failed payment
					This means when we pre-auth and update our ends billing data AUTH will try to capture payment again next day
					We will pre-auth and update users CC and billing data only
				*/
				error_log("Subscription Suspended on AUTH", 0);
				error_log("Pre-Auth & Update Billing Only - SB set to Active", 0);
				error_log("AUTH should re-capture missing payment next day", 0);
				$this->updateMethod = 'preauth';
				//Do Update
				$this->updateARBBilling();
			}
			if ($arbStatus ==  'active'){
				/*
					Auth has this ARB on their end marked as active even though the last payment attempt failed. This means the card on file
					was being used previously without issues and suddenly failed. Auth will NOT attempt to take payment for this missed payment again
					We must update the CC and billing data and capture the missed payment
				*/
				error_log("Subscription Active on AUTH", 0);
				error_log("AUTH will not recapure this payment", 0);
				error_log("Capture missed payment & Update Billing - SB set to Active", 0);
				$this->updateMethod = 'capture';
				//Do Update
				$this->updateARBBilling();
			}
		}else if ($this->subscriptionStatus == 'active') {
			error_log("Subscription Active on SB", 0);
			/*
				We marked the subscription active on our end meaning we have accepted payment on its last run. We now check the status on Auths end to see what actions we need to perform.
			*/
			if ($arbStatus ==  'active'){
				/*
					Auth has this ARB on their end marked as active and we have it marked active on our end. this means the user is simply updating their billing data so we will only need to pre-auth their data then update it. 
				*/
				error_log("Subscription Active on AUTH", 0);
				error_log("Pre-Auth only - this customer is just updating their cc info", 0);				
				$this->updateMethod = 'preauth';
				//Do Update
				$this->updateARBBilling();
			}
			if ($arbStatus ==  'suspended'){
				error_log("Subscription Suspended on AUTH - Internal Error - requires investigation", 0);
				/*
					Auth has this ARB on their end marked as suspended and we have it marked active on our end. This means somewhere between our last successful billing of their card that Auth marked them suspended on their end. This should NEVER happen but if it does ever occur we need to have this checked out by an admin.
				*/
				$this->response = 'There is an internal error with this subscription. Making any changes to this subscription is currently unavailable. An Administrator has been notified of this error.';
				wp_mail($this->apiEmail, 'Internal Billing Update Error', 'Internal Update Error for this subscription ID'. $this->subscriptionID);
			}		
		}	
	}

	

	public function updateARBBilling(){

		if ($this->updateMethod == 'capture'){
			$this->updateBillingCapture();



			if ($this->responseCode == '1'){
				$this->updateARB();
			}

				

		}else{
			$this->updateBillingPreAuth();			

		if ($this->responseCode == '1'){

				$this->voidBillingPreAuth();			

				if ($this->voidCode == '1'){

					$this->updateARB();

				}

			}

		}

		

		if ($this->arbUpdateStatus == 'Ok'){

			$this->updateSubscriptionMeta();

			$this->response = 'Your billing information has been updated.';

		}

	}	

	

	public function updateSubscriptionMeta(){

		

		//inserts billing data for subscription

		update_post_meta($this->subscriptionPostID, 'billingFirstName', $this->billingFirstName);

		update_post_meta($this->subscriptionPostID, 'billingLastName', $this->billingLastName);

		update_post_meta($this->subscriptionPostID, 'billingEmail', $this->billingEmail);

		update_post_meta($this->subscriptionPostID, 'billingCompany', $this->billingCompany);

		update_post_meta($this->subscriptionPostID, 'billingPhoneNumber', $this->billingPhoneNumber);

		update_post_meta($this->subscriptionPostID, 'billingAddress', $this->billingAddress);

		update_post_meta($this->subscriptionPostID, 'billingCity', $this->billingCity);

		update_post_meta($this->subscriptionPostID, 'billingState', $this->billingState);

		update_post_meta($this->subscriptionPostID, 'billingZip', $this->billingZip);

		update_post_meta($this->subscriptionPostID, 'billingCountry', $this->billingCountry);

	

		//inserts shipping data for subscription

		update_post_meta($this->subscriptionPostID, 'shippingFirstName', $this->shippingFirstName);

		update_post_meta($this->subscriptionPostID, 'shippingLastName', $this->shippingLastName);

		update_post_meta($this->subscriptionPostID, 'shippingCompany', $this->shippingCompany);

		update_post_meta($this->subscriptionPostID, 'shippingAddress', $this->shippingAddress);

		update_post_meta($this->subscriptionPostID, 'shippingCity', $this->shippingCity);

		update_post_meta($this->subscriptionPostID, 'shippingState', $this->shippingState);

		update_post_meta($this->subscriptionPostID, 'shippingZip', $this->shippingZip);

		update_post_meta($this->subscriptionPostID, 'shippingCountry', $this->shippingCountry);

		

		update_post_meta($this->subscriptionPostID, 'subscriptionStatus', 'active');

		update_post_meta($this->subscriptionPostID, 'ccLastFour', $this->ccLastFour);

		update_post_meta($this->subscriptionPostID, 'ccMonth', $this->ccMonth);

		update_post_meta($this->subscriptionPostID, 'ccYear', $this->ccYear);

		//if the update method was CAPTURE then we captured a missed ARB and need to update the lastbillingdate to reflect that
		if ($this->updateMethod == 'capture'){
			update_post_meta($this->subscriptionPostID, 'subscriptionLastBillingDate', date('Y-m-01') );
		}

		//take additional form data and add it to subscription meta

		if ( is_array( $this->addonData ) ){

			foreach ($this->addonData as $k => $v){

				update_post_meta($this->subscriptionPostID, $k, $v);

			}			

		}	

		

	}

	

	public function updateARB(){

		$this->refID = 'BU-UID-'.$this->userID;

		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);

		$xml->ARBUpdateSubscriptionRequest(array(

			'refId' => $this->refID,

			'subscriptionId' => $this->subscriptionID,

			'subscription' => array(

				'payment' => array(

					'creditCard' => array(

						'cardNumber' => $this->ccNumber,

						'expirationDate' => $this->ccMonth.$this->ccYear

					),

				),

				'customer' => array(

					'id' => $this->userID,

					'email' => $this->billingEmail,

					'phoneNumber' => $this->billingPhoneNumber,

				),

				'billTo' => array(

					'firstName' => $this->billingFirstName,

					'lastName' => $this->billingLastName,

					'company' => $this->billingCompany,

					'address' => $this->billingAddress,

					'city' => $this->billingCity,

					'state' => $this->billingState,

					'zip' => $this->billingZip,

					'country' => $this->billingCountry,

				),

				'shipTo' => array(

					'firstName' => $this->shippingFirstName,

					'lastName' => $this->shippingLastName,

					'company' => $this->shippingCompany,

					'address' => $this->shippingAddress,

					'city' => $this->shippingCity,

					'state' => $this->shippingState,

					'zip' => $this->shippingZip,

					'country' => $this->shippingCountry,

				)				

			),

		));

		

		if ($xml->isSuccessful()){

			$this->arbUpdateStatus = (string) $xml->messages->resultCode;			

		}else{

			$this->response = (string) 'ARB Update Error: '.$xml->messages->message->text;

			

		}

	}

	

	public function updateBillingPreAuth(){
		error_log("Pre-Auth Only", 0);

		$this->refID = 'BPA-UID-'.$this->userID;

		$this->description = 'Pre-Auth for Billing Update';

			

		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);

		$xml->createTransactionRequest(array(

			'refId' => $this->refID,

			'transactionRequest' => array(

				'transactionType' => 'authOnlyTransaction',

				'amount' => '0.01',

				'payment' => array(

					'creditCard' => array(

						'cardNumber' => $this->ccNumber,

						'expirationDate' => $this->ccMonth.$this->ccYear,

						'cardCode' => $this->ccCode,

					),

				),

				'order' => array(

					'invoiceNumber' => $this->transactionInvoiceNumber,

					'description' => $this->description,

				),

				'customer' => array(

				   'id' => $this->userID,

				   'email' => $this->billingEmail,

				),

				'billTo' => array(

					'firstName' => $this->billingFirstName,

					'lastName' => $this->billingLastName,

					'company' => $this->billingCompany,

					'address' => $this->billingAddress,

					'city' => $this->billingCity,

					'state' => $this->billingState,

					'zip' => $this->billingZip,

					'country' => $this->billingCountry,

					'phoneNumber' => $this->billingPhoneNumber,

				),

				'customerIP' => $this->userIPAddress,

			),

		));



		$this->responseCode = (string) $xml->transactionResponse->responseCode;

		

		if ($this->responseCode == '1'){
			error_log("Pre-Auth Only Successful", 0);
			$this->transactionID = (string) $xml->transactionResponse->transId;			

		}else{
			error_log("Pre-Auth Only Failed", 0);
			$this->response = (string) 'Payment Error: '.$xml->messages->message->text.' -- '.$xml->transactionResponse->errors->error->errorText;

			

		}

	}

	

	public function updateBillingCapture(){
		error_log("Capture Missed Payment", 0);

		//This will attempt to charge the user during updating billing data and then update the arb data and subscription here with new info

		$this->refID = 'BUP-RECAP-UID-'.$this->userID;

		$this->description = 'Recapturing Missed ARB Payment';

			

		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);

		$xml->createTransactionRequest(array(

			'refId' => $this->refID,

			'transactionRequest' => array(

				'transactionType' => 'authCaptureTransaction',

				'amount' => $this->subscriptionAmount,

				'payment' => array(

					'creditCard' => array(

						'cardNumber' => $this->ccNumber,

						'expirationDate' => $this->ccMonth.$this->ccYear,

						'cardCode' => $this->ccCode,

					),

				),

				'order' => array(

					'invoiceNumber' => $this->transactionInvoiceNumber,

					'description' => $this->description,

				),

				'customer' => array(

				   'id' => $this->userID,

				   'email' => $this->billingEmail,

				),

				'billTo' => array(

					'firstName' => $this->billingFirstName,

					'lastName' => $this->billingLastName,

					'company' => $this->billingCompany,

					'address' => $this->billingAddress,

					'city' => $this->billingCity,

					'state' => $this->billingState,

					'zip' => $this->billingZip,

					'country' => $this->billingCountry,

					'phoneNumber' => $this->billingPhoneNumber,

				),

				'customerIP' => $this->userIPAddress,

			),

		));



		$this->responseCode = (string) $xml->transactionResponse->responseCode;

		

		if ($this->responseCode == '1'){
			error_log("Payment Recaptured Successfully", 0);
			$this->transactionID = (string) $xml->transactionResponse->transId;			

		}else{
			error_log("Payment Recapture Failed", 0);
			$this->response = (string) 'Payment Error: '.$xml->messages->message->text.' -- '.$xml->transactionResponse->errors->error->errorText;	

		}

	}

	

	public function voidBillingPreAuth(){
		error_log("Void Pre-Auth", 0);
		$this->refID = 'BUPV-PA-U-'.$this->userID;

		

		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);

		$xml->createTransactionRequest(array(

			'refId' => $this->refID,

			'transactionRequest' => array(

				'transactionType' => 'voidTransaction',

				'refTransId' => $this->transactionID,

			),

		));

		

		$this->responseCode = (string) $xml->transactionResponse->responseCode;

		

		if ($this->responseCode == '1'){
			error_log("Void Pre-Auth Successful", 0);
			$this->voidCode = (string) $xml->transactionResponse->responseCode;

		}else{
			error_log("Void Pre-Auth Failed", 0);
			$this->response = (string) 'Void Error: '.$xml->messages->message->text;

			

		}

	}

	

	public function monthSelect($fieldName, $array = ''){

	

		if ($array === true){

		

			$array = array();

			  for ($i = 1; $i <= 12; $i++) {

				$month = date("F", mktime(0, 0, 0, $i, 1));

				$array[$i] = $month;

			  }			

			return $array;

			

		}else{

		

			echo '<select id="'.$fieldName.'" name="'.$fieldName.'">';

			  for ($i = 1; $i <= 12; $i++) {			

				$month = date("F", mktime(0, 0, 0, $i, 1));			

				printf('<option value="%s">%s - %s</option>', $i, $i ,$month);

			  }

			echo '</select>';

			

		}

		

	}

	

	public function yearSelect($fieldName, $array = ''){

	

		if ($array === true){

			$array = array();

			

			$year = date("Y"); 

			for ($i = 0; $i <= 12; $i++) {

				$array[$year] = $year;

				$year++;

			}

			return $array;

			

		}else{	

			echo '<select id="'.$fieldName.'" name="'.$fieldName.'">';

			$year = date("Y"); for ($i = 0; $i <= 12; $i++) {echo "<option>$year</option>"; $year++;}

			echo '</select>';

		}

		

	}

	

	public function countrySelect($fieldName, $array = '', $selected = ''){

	$countries = array(

		  "US" => "United States",

		  "GB" => "United Kingdom",		  

		  "AF" => "Afghanistan",

		  "AL" => "Albania",

		  "DZ" => "Algeria",

		  "AS" => "American Samoa",

		  "AD" => "Andorra",

		  "AO" => "Angola",

		  "AI" => "Anguilla",

		  "AQ" => "Antarctica",

		  "AG" => "Antigua And Barbuda",

		  "AR" => "Argentina",

		  "AM" => "Armenia",

		  "AW" => "Aruba",

		  "AU" => "Australia",

		  "AT" => "Austria",

		  "AZ" => "Azerbaijan",

		  "BS" => "Bahamas",

		  "BH" => "Bahrain",

		  "BD" => "Bangladesh",

		  "BB" => "Barbados",

		  "BY" => "Belarus",

		  "BE" => "Belgium",

		  "BZ" => "Belize",

		  "BJ" => "Benin",

		  "BM" => "Bermuda",

		  "BT" => "Bhutan",

		  "BO" => "Bolivia",

		  "BA" => "Bosnia And Herzegowina",

		  "BW" => "Botswana",

		  "BV" => "Bouvet Island",

		  "BR" => "Brazil",

		  "IO" => "British Indian Ocean Territory",

		  "BN" => "Brunei Darussalam",

		  "BG" => "Bulgaria",

		  "BF" => "Burkina Faso",

		  "BI" => "Burundi",

		  "KH" => "Cambodia",

		  "CM" => "Cameroon",

		  "CA" => "Canada",

		  "CV" => "Cape Verde",

		  "KY" => "Cayman Islands",

		  "CF" => "Central African Republic",

		  "TD" => "Chad",

		  "CL" => "Chile",

		  "CN" => "China",

		  "CX" => "Christmas Island",

		  "CC" => "Cocos (Keeling) Islands",

		  "CO" => "Colombia",

		  "KM" => "Comoros",

		  "CG" => "Congo",

		  "CD" => "Congo, The Democratic Republic Of The",

		  "CK" => "Cook Islands",

		  "CR" => "Costa Rica",

		  "CI" => "Cote D'Ivoire",

		  "HR" => "Croatia (Local Name: Hrvatska)",

		  "CU" => "Cuba",

		  "CY" => "Cyprus",

		  "CZ" => "Czech Republic",

		  "DK" => "Denmark",

		  "DJ" => "Djibouti",

		  "DM" => "Dominica",

		  "DO" => "Dominican Republic",

		  "TP" => "East Timor",

		  "EC" => "Ecuador",

		  "EG" => "Egypt",

		  "SV" => "El Salvador",

		  "GQ" => "Equatorial Guinea",

		  "ER" => "Eritrea",

		  "EE" => "Estonia",

		  "ET" => "Ethiopia",

		  "FK" => "Falkland Islands (Malvinas)",

		  "FO" => "Faroe Islands",

		  "FJ" => "Fiji",

		  "FI" => "Finland",

		  "FR" => "France",

		  "FX" => "France, Metropolitan",

		  "GF" => "French Guiana",

		  "PF" => "French Polynesia",

		  "TF" => "French Southern Territories",

		  "GA" => "Gabon",

		  "GM" => "Gambia",

		  "GE" => "Georgia",

		  "DE" => "Germany",

		  "GH" => "Ghana",

		  "GI" => "Gibraltar",

		  "GR" => "Greece",

		  "GL" => "Greenland",

		  "GD" => "Grenada",

		  "GP" => "Guadeloupe",

		  "GU" => "Guam",

		  "GT" => "Guatemala",

		  "GN" => "Guinea",

		  "GW" => "Guinea-Bissau",

		  "GY" => "Guyana",

		  "HT" => "Haiti",

		  "HM" => "Heard And Mc Donald Islands",

		  "VA" => "Holy See (Vatican City State)",

		  "HN" => "Honduras",

		  "HK" => "Hong Kong",

		  "HU" => "Hungary",

		  "IS" => "Iceland",

		  "IN" => "India",

		  "ID" => "Indonesia",

		  "IR" => "Iran (Islamic Republic Of)",

		  "IQ" => "Iraq",

		  "IE" => "Ireland",

		  "IL" => "Israel",

		  "IT" => "Italy",

		  "JM" => "Jamaica",

		  "JP" => "Japan",

		  "JO" => "Jordan",

		  "KZ" => "Kazakhstan",

		  "KE" => "Kenya",

		  "KI" => "Kiribati",

		  "KP" => "Korea, Democratic People's Republic Of",

		  "KR" => "Korea, Republic Of",

		  "KW" => "Kuwait",

		  "KG" => "Kyrgyzstan",

		  "LA" => "Lao People's Democratic Republic",

		  "LV" => "Latvia",

		  "LB" => "Lebanon",

		  "LS" => "Lesotho",

		  "LR" => "Liberia",

		  "LY" => "Libyan Arab Jamahiriya",

		  "LI" => "Liechtenstein",

		  "LT" => "Lithuania",

		  "LU" => "Luxembourg",

		  "MO" => "Macau",

		  "MK" => "Macedonia, Former Yugoslav Republic Of",

		  "MG" => "Madagascar",

		  "MW" => "Malawi",

		  "MY" => "Malaysia",

		  "MV" => "Maldives",

		  "ML" => "Mali",

		  "MT" => "Malta",

		  "MH" => "Marshall Islands",

		  "MQ" => "Martinique",

		  "MR" => "Mauritania",

		  "MU" => "Mauritius",

		  "YT" => "Mayotte",

		  "MX" => "Mexico",

		  "FM" => "Micronesia, Federated States Of",

		  "MD" => "Moldova, Republic Of",

		  "MC" => "Monaco",

		  "MN" => "Mongolia",

		  "MS" => "Montserrat",

		  "MA" => "Morocco",

		  "MZ" => "Mozambique",

		  "MM" => "Myanmar",

		  "NA" => "Namibia",

		  "NR" => "Nauru",

		  "NP" => "Nepal",

		  "NL" => "Netherlands",

		  "AN" => "Netherlands Antilles",

		  "NC" => "New Caledonia",

		  "NZ" => "New Zealand",

		  "NI" => "Nicaragua",

		  "NE" => "Niger",

		  "NG" => "Nigeria",

		  "NU" => "Niue",

		  "NF" => "Norfolk Island",

		  "MP" => "Northern Mariana Islands",

		  "NO" => "Norway",

		  "OM" => "Oman",

		  "PK" => "Pakistan",

		  "PW" => "Palau",

		  "PA" => "Panama",

		  "PG" => "Papua New Guinea",

		  "PY" => "Paraguay",

		  "PE" => "Peru",

		  "PH" => "Philippines",

		  "PN" => "Pitcairn",

		  "PL" => "Poland",

		  "PT" => "Portugal",

		  "PR" => "Puerto Rico",

		  "QA" => "Qatar",

		  "RE" => "Reunion",

		  "RO" => "Romania",

		  "RU" => "Russian Federation",

		  "RW" => "Rwanda",

		  "KN" => "Saint Kitts And Nevis",

		  "LC" => "Saint Lucia",

		  "VC" => "Saint Vincent And The Grenadines",

		  "WS" => "Samoa",

		  "SM" => "San Marino",

		  "ST" => "Sao Tome And Principe",

		  "SA" => "Saudi Arabia",

		  "SN" => "Senegal",

		  "SC" => "Seychelles",

		  "SL" => "Sierra Leone",

		  "SG" => "Singapore",

		  "SK" => "Slovakia (Slovak Republic)",

		  "SI" => "Slovenia",

		  "SB" => "Solomon Islands",

		  "SO" => "Somalia",

		  "ZA" => "South Africa",

		  "GS" => "South Georgia, South Sandwich Islands",

		  "ES" => "Spain",

		  "LK" => "Sri Lanka",

		  "SH" => "St. Helena",

		  "PM" => "St. Pierre And Miquelon",

		  "SD" => "Sudan",

		  "SR" => "Suriname",

		  "SJ" => "Svalbard And Jan Mayen Islands",

		  "SZ" => "Swaziland",

		  "SE" => "Sweden",

		  "CH" => "Switzerland",

		  "SY" => "Syrian Arab Republic",

		  "TW" => "Taiwan",

		  "TJ" => "Tajikistan",

		  "TZ" => "Tanzania, United Republic Of",

		  "TH" => "Thailand",

		  "TG" => "Togo",

		  "TK" => "Tokelau",

		  "TO" => "Tonga",

		  "TT" => "Trinidad And Tobago",

		  "TN" => "Tunisia",

		  "TR" => "Turkey",

		  "TM" => "Turkmenistan",

		  "TC" => "Turks And Caicos Islands",

		  "TV" => "Tuvalu",

		  "UG" => "Uganda",

		  "UA" => "Ukraine",

		  "AE" => "United Arab Emirates",

		  "UM" => "United States Minor Outlying Islands",

		  "UY" => "Uruguay",

		  "UZ" => "Uzbekistan",

		  "VU" => "Vanuatu",

		  "VE" => "Venezuela",

		  "VN" => "Viet Nam",

		  "VG" => "Virgin Islands (British)",

		  "VI" => "Virgin Islands (U.S.)",

		  "WF" => "Wallis And Futuna Islands",

		  "EH" => "Western Sahara",

		  "YE" => "Yemen",

		  "YU" => "Yugoslavia",

		  "ZM" => "Zambia",

		  "ZW" => "Zimbabwe"

		);

		

		if ($array === true){

			return $countries;		

		}else{		

			echo '<select id="'.$fieldName.'" name="'.$fieldName.'">';

			foreach ($countries as $value => $option){

				if ($selected == $value){

					echo '<option selected="selected" value="'.$value.'">'.$option.'</option>';

				}

				echo '<option value="'.$value.'">'.$option.'</option>';

				

			}

			echo '</select>';

		}

	}	

}

?>
