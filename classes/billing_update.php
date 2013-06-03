<?php
class billingUpdate{

	public function __construct($subscriptionPostID){
	
		//API Login
		$this->apiLogin = get_option('apiLogin');
		$this->apiKey = get_option('apiKey');
		$this->apiTestMode = get_option('apiTestMode');
		$this->apiEmail = get_option('apiEmail');
		
		$this->dateToday = date('Y-m-d');
	
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
		$this->cclastFour = get_post_meta($this->subscriptionPostID, 'ccLastFour', true);
		$this->ccMonth = get_post_meta($this->subscriptionPostID, 'ccMonth', true);
		$this->ccYear = get_post_meta($this->subscriptionPostID, 'ccYear', true);
		$this->subscriptionInterval = get_post_meta($this->subscriptionPostID, 'subscriptionInterval', true);
		$this->subscriptionUnit = get_post_meta($this->subscriptionPostID, 'subscriptionUnit', true);		
	}
	
	
	public function cancelSubscription($cancelledBy = ''){
	
		if ($cancelledBy){
		$this->cancelledBy = $cancelledBy;		
		}
		
		$this->refID = 'BUCANCEL-UID-'.$this->userID;
		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);
	    $xml->ARBCancelSubscriptionRequest(array(
			'refId' => $this->refID,
			'subscriptionId' => $this->subscriptionID,
		));
		
		if ($xml->isSuccessful()){
			update_post_meta($this->subscriptionPostID, 'subscriptionStatus', 'cancelled');
			update_post_meta($this->subscriptionPostID, 'subscriptionCancelledBy', $this->cancelledBy);
			$this->response = 'Subscription Cancelled.';
		}else{
			$this->response = (string) 'Subscription Cancel Error: '.$xml->messages->message->text;			
		}
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
		$this->updateARBNoCC();
	
		if ($this->arbUpdateStatus == 'Ok'){
			$this->updateSubscriptionMeta();
			$this->response = 'Your subscription information has been updated.';
		}	
	}
	
	public function updateARBNoCC(){
		$this->refID = 'BU-UID-'.$this->userID;
		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);
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
		
		if ($xml->isSuccessful()){
			$this->arbUpdateStatus = (string) $xml->messages->resultCode;			
		}else{
			$this->response = (string) 'ARB Update Error: '.$xml->messages->message->text;
			
		}
	}
	
	
	
	public function processBillingUpdate(){
	
		if ($this->subscriptionStatus == 'suspended') {
			/*
				silent post returned error for this subscription and its been suspended on authorize.net
				we need to update the payment information and authorize.net will try to charge again on the
				next buisness day
			*/
			$this->updateMethod = 'preauth';
			$this->updateARBBilling();
			
		}else if ($this->subscriptionStatus == 'activeSuspended') {
			/*
				silent post returned an error on ARB and authorize.net did NOT suspend the subscription. So we now need to take payment for the missed payment and update the users card info on ARB and HERE
			*/			
			$this->updateMethod = 'capture';
			$this->updateARBBilling();
			
		}else if ($this->subscriptionStatus == 'active'){
			/*
				there is no error at all and the incoming billing data is simply a billing update
				we need to run the preauth, void, and then update ARB and our info here.
			*/
			$this->updateMethod = 'preauth';
			$this->updateARBBilling();
		}
	}
	
	public function updateARBBilling(){
	
		if ($this->updateMethod == 'capture'){
			$this->updateBillingCapture();
			
			if ( !empty($this->transactionID) ){
					$this->updateARB();
				}
				
		}else{
			$this->updateBillingPreAuth();			
			if ( !empty($this->transactionID) ){
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
		update_post_meta($this->subscriptionPostID, 'ccLastFour', $this->cclastFour);
		update_post_meta($this->subscriptionPostID, 'ccMonth', $this->ccMonth);
		update_post_meta($this->subscriptionPostID, 'ccYear', $this->ccYear);
		
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
		$this->transactionInvoiceNumber = rand(1000000, 100000000).'-UID-'.$this->userID;
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

		if ($xml->isSuccessful()){
			$this->transactionID = (string) $xml->transactionResponse->transId;			
		}else{
			$this->response = (string) 'Payment Error: '.$xml->messages->message->text.' -- '.$xml->transactionResponse->errors->error->errorText;
			
		}
	}
	
	public function updateBillingCapture(){
		//This will attempt to charge the user during updating billing data and then update the arb data and subscription here with new info
		$this->transactionInvoiceNumber = rand(1000000, 100000000).'-UID-'.$this->userID;
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

		if ($xml->isSuccessful()){
			$this->transactionID = (string) $xml->transactionResponse->transId;			
		}else{
			$this->response = (string) 'Payment Error: '.$xml->messages->message->text.' -- '.$xml->transactionResponse->errors->error->errorText;
			
		}	
	}
	
	public function voidBillingPreAuth(){
		$this->refID = 'BUPV-PA-U-'.$this->userID;
		
		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);
		$xml->createTransactionRequest(array(
			'refId' => $this->refID,
			'transactionRequest' => array(
				'transactionType' => 'voidTransaction',
				'refTransId' => $this->transactionID,
			),
		));
		
		if ($xml->isSuccessful()){
			$this->voidCode = (string) $xml->transactionResponse->responseCode;
		}else{
			$this->response = (string) 'Void Error: '.$xml->messages->message->text;
			
		}
	}
	
}
?>