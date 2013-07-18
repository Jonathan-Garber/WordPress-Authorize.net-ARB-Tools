<?php
class billing {

	public function __construct($postID = ''){
	
		$this->postID = $postID;

		//dateToday never changes from todays actual date during the processes of this class.
		$this->dateToday = date('Y-m-d');
	
		//API Settings
		$this->apiLogin = get_option('apiLogin');
		$this->apiEmail = get_option('apiEmail');
		$this->apiKey = get_option('apiKey');
		$this->apiHashEnable = get_option('apiHashEnable');
		$this->hash = get_option('apiHash');
		$this->vtUser = get_option('vtUser');
		$this->apiTestMode = get_option('apiTestMode');
		$this->userID = get_current_user_id();
		
		$this->apiReporting = 'on'; //Harcoded for now. optional later in settings
		
		$this->cutOffDay = get_option('cutOffDay');
		$this->startDay = get_option('startDay');

		
		$this->userFirstName = get_userdata($this->userID)->user_firstname;
		$this->userLastName = get_userdata($this->userID)->user_lastname;
		$this->userEmail = get_userdata($this->userID)->user_email;
		$this->userCompany = get_user_meta($this->userID, 'userCompany', true);	
		$this->userPhoneNumber = get_user_meta($this->userID, 'userPhoneNumber', true);
		$this->userIPAddress = $_SERVER["REMOTE_ADDR"];
		
		//billing info from user
		$this->billingFirstName = get_user_meta($this->userID, 'billingFirstName', true);
		$this->billingLastName = get_user_meta($this->userID, 'billingLastName', true);
		$this->billingEmail = get_user_meta($this->userID, 'billingEmail', true);
		$this->billingCompany = get_user_meta($this->userID, 'billingCompany', true);
		$this->billingPhoneNumber = get_user_meta($this->userID, 'billingPhoneNumber', true);
		$this->billingAddress = get_user_meta($this->userID, 'billingAddress', true);
		$this->billingCity = get_user_meta($this->userID, 'billingCity', true);
		$this->billingState = get_user_meta($this->userID, 'billingState', true);
		$this->billingZip = get_user_meta($this->userID, 'billingZip', true);
		$this->billingCountry = get_user_meta($this->userID, 'billingCountry', true);
				
		//never store full CC number or CVV codes
		$this->billingCreditCardLastFour = get_user_meta($this->userID, 'billingCreditCardLastFour', true);
		$this->billingCreditCardMonth = get_user_meta($this->userID, 'billingCreditCardMonth', true);
		$this->billingCreditCardYear = get_user_meta($this->userID, 'billingCreditCardYear', true);
		
		//shipping info from user
		$this->shippingFirstName = get_user_meta($this->userID, 'shippingFirstName', true);
		$this->shippingLastName = get_user_meta($this->userID, 'shippingLastName', true);
		$this->shippingCompany = get_user_meta($this->userID, 'shippingCompany', true);
		$this->shippingAddress = get_user_meta($this->userID, 'shippingAddress', true);
		$this->shippingCity = get_user_meta($this->userID, 'shippingCity', true);
		$this->shippingState = get_user_meta($this->userID, 'shippingState', true);
		$this->shippingZip = get_user_meta($this->userID, 'shippingZip', true);
		$this->shippingCountry = get_user_meta($this->userID, 'shippingCountry', true);
		
		//new tracking with reference ID to ensure we can associate ALL steps of the order process to each other properly
		$this->uniqueID = uniqid('00'); //You have to set uniqueID in its own variable here
		$this->refID = $this->uniqueID;
		
	}
	
	/*
		This is the void function called during the ordering process
	*/
	public function processVoidTransaction(){
		//invoice number is unique and used to associate all portions of the transaction being run together.
		$this->invoiceNumber = $this->uniqueID.'-'.$this->userID;
		
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
			$this->transactionID = (string) $xml->transactionResponse->transId;
		}else{
			$message = (string) $xml->messages->message->text;			
			$this->errorMessage = 'Void Error: '.$message;
			$this->errorArray = array ('type' => 'Void Error', 'message' => $message);
		}
	}
	
	/*
		Runs after submitted order/payment data this is ONLY for processing initital payments on subscriptions that start at a later date. this only authorizes the amount. We capture after the subscription is successfully created.
	*/
	
	public function processInitialPayment(){
		//invoice number is unique and used to associate all portions of the transaction being run together.
		$this->invoiceNumber = $this->uniqueID.'-'.$this->userID;	
		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);
		$xml->createTransactionRequest(array(
			'refId' => $this->refID,
			'transactionRequest' => array(
				'transactionType' => 'authCaptureTransaction',
				'amount' => $this->productAmount,
				'payment' => array(
					'creditCard' => array(
						'cardNumber' => $this->ccNumber,
						'expirationDate' => $this->ccMonth.$this->ccYear,
						'cardCode' => $this->ccCode,
					),
				),
				'order' => array(
					'invoiceNumber' => $this->invoiceNumber,
					'description' => $this->productDescription,
				),
				'lineItems' => array(
					'lineItem' => array(
						0 => array(
							'itemId' => '1',
							'name' => $this->productName,
							'description' => $this->productDescription,
							'quantity' => '1',
							'unitPrice' => $this->productAmount
						)
					)
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
				'shipTo' => array(
					'firstName' => $this->shippingFirstName,
					'lastName' => $this->shippingLastName,
					'company' => $this->shippingCompany,
					'address' => $this->shippingAddress,
					'city' => $this->shippingCity,
					'state' => $this->shippingState,
					'zip' => $this->shippingZip,
					'country' => $this->shippingCountry,
				),
				'customerIP' => $this->userIPAddress,
			),
		));

		$this->responseCode = (string) $xml->transactionResponse->responseCode;
		
		if ($this->responseCode == '1'){
			$this->transactionID = (string) $xml->transactionResponse->transId;
		}else{
			$message = (string) $xml->messages->message->text.' -- '.$xml->transactionResponse->errors->error->errorText;			
			$this->errorMessage = 'Payment Error: '. $message;
			$this->errorArray = array('type' => 'Payment Error', 'message' => $message);
		}
	}
	
	/*
		Runs pre-auth and void on CC info for 0.01cent
	*/	
	public function processPreAuth(){
		//invoice number is unique and used to associate all portions of the transaction being run together.
		$this->invoiceNumber = $this->uniqueID.'-'.$this->userID;	
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
					'invoiceNumber' => $this->invoiceNumber,
					'description' => 'Authorize Only Transaction',
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
			$this->transactionID = (string) $xml->transactionResponse->transId;
		}else{			
			$message = (string) $xml->messages->message->text.' -- '.$xml->transactionResponse->errors->error->errorText;			
			$this->errorMessage = 'Payment Error: '. $message;
			$this->errorArray = array ('type' => 'Payment Error', 'message' => $message);
		}
	}


	public function calculateStartDate(){				
		$cutOffDate = strtotime( date('Y-m-'.$this->cutOffDay) );
		$today = strtotime( $this->dateToday );
		
		//if date of order is past the cutOffDate we perform different actions
		if ($today > $cutOffDate){
			$this->billInitialPayment = 'off';
		}else{
			$this->billInitialPayment = 'on';
		}	
			$this->startDate = strtotime(date('Y-m-'.$this->startDay, strtotime(date('Y-m-'.$this->startDay))) . '+1 month');
			$this->startDate = date('Y-m-d', $this->startDate);
	}	
	
	/*
		Runs after submitted data and pre-auths/voids the card and creates ARB when successful
	*/
	public function processARB(){
	
		/*
			we calculate the startDate and set the initial billing based on cutOff Date
		*/
		$this->calculateStartDate();
		
		if ($this->billInitialPayment == 'on'){
		
			/*
				We are taking the first payment for this subscription now and setting the start date to this same day one month from now to allow recurring billing to take over.
			*/
			$this->processInitialPayment();
			
			//Processing Initial Payment sets TransID upon success & the proper startDate into the variable
			if ($this->responseCode == '1'){
				
				//trans ID and new startDate are configured so we can continue with creating the subscription
				$this->createSubscription();

				//create subscription sets a subscription ID upon success				
				if ( strlen($this->subscriptionID) > 4 ){
					//we insert the initial subscription post and data here.
					$this->insertSubscription();
					
					//Since we billed them the initial payment already we are going to update their Subscription Meta to be ACTIVE & show payment number of 1 as well as the last date billing occurred and the next date billing is scheduled to occur on.
					
					update_post_meta($this->subscriptionPostID, 'subscriptionLastBillingDate', $this->dateToday);
					update_post_meta($this->subscriptionPostID, 'subscriptionNextBillingDate', $this->startDate);
					update_post_meta($this->subscriptionPostID, 'subscriptionPaymentNumber', '1');
					update_post_meta($this->subscriptionPostID, 'subscriptionStatus', 'active');
					
					//return variable so we know we can redirect
					$this->transactionSuccess = 'true';
					
				}
			}
			
		}else{
			$this->processPreAuth();
			
			//Preauth sets response code to 1 upon success
			if ($this->responseCode == '1'){
				$this->voidReason = 'Pre-Authorization Void';				
				$this->processVoidTransaction();
				
				//voiding sets the response code variable to 1 upon success if it fails response will not equal 1
				if ($this->responseCode == '1'){
					$this->createSubscription();						
					
					//create subscription sets a subscription ID upon success
					//We use string length to be double sure we have a ID number by making sure the variable is numeric and longer then 4 digits minimal. This ensures we do not have some resultCode or error code by mistake in the variable
						
					if ( strlen($this->subscriptionID) > 4 ){
						//insert subscription post
						$this->insertSubscription();
						$this->transactionSuccess = 'true';
					}
				}
			}
		}
	}
	
	public function insertSubscription(){
		//Notice the productDescription variable is used in this first step as the post Content the rest of the product information is added in the updateSubscriptionMeta function.
		
		$subscription = array(
		  'post_title'    => esc_attr($this->subscriptionID),
		  'post_content' => esc_attr($this->productDescription),
		  'comment_status' => 'closed',
		  'ping_status' => 'closed',
		  'post_status'   => 'publish',
		  'post_type' => 'auth-subscriptions',
		  'post_author'   => esc_attr($this->userID),
		  'post_category' => array(0)
		);
		
		$subscriptionPostID = wp_insert_post($subscription);
		
		//set the subscription post ID this instance is working with into a variable
		$this->subscriptionPostID = $subscriptionPostID;
		
		//update this new subscription post with any and all data we feel is needed
		$this->updateSubscriptionMeta();		
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
		
		//inserts payment data for subscription
		update_post_meta($this->subscriptionPostID, 'ccLastFour', $this->lastFour);
		update_post_meta($this->subscriptionPostID, 'ccMonth', $this->ccMonth);
		update_post_meta($this->subscriptionPostID, 'ccYear', $this->ccYear);
		
		//insert Subscription information		
		update_post_meta($this->subscriptionPostID, 'subscriptionID', $this->subscriptionID);
		update_post_meta($this->subscriptionPostID, 'subscriptionStartDate', $this->startDate);
		update_post_meta($this->subscriptionPostID, 'subscriptionReferenceID', $this->refID);
		update_post_meta($this->subscriptionPostID, 'subscriptionInvoiceNumber', $this->invoiceNumber);
		update_post_meta($this->subscriptionPostID, 'subscriptionName', $this->productName);
		update_post_meta($this->subscriptionPostID, 'subscriptionInterval', $this->productInterval);
		update_post_meta($this->subscriptionPostID, 'subscriptionUnit', $this->productUnit);
		update_post_meta($this->subscriptionPostID, 'subscriptionOccurrences', $this->productOccurrences);
		update_post_meta($this->subscriptionPostID, 'subscriptionTrialOccurrences', $this->productTrialOccurrences);
		update_post_meta($this->subscriptionPostID, 'subscriptionAmount', $this->productAmount);
		update_post_meta($this->subscriptionPostID, 'subscriptionTrialAmount', $this->productTrialAmount);
		
		//These variables are updated during Silent Post Captures
		update_post_meta($this->subscriptionPostID, 'subscriptionLastBillingDate', '0');
		update_post_meta($this->subscriptionPostID, 'subscriptionNextBillingDate', $this->startDate);
		update_post_meta($this->subscriptionPostID, 'subscriptionPaymentNumber', '0');
		update_post_meta($this->subscriptionPostID, 'subscriptionStatus', 'active');		
		
		//take additional form data and add it to subscription meta
		if ( is_array( $this->addonData ) ){			
			foreach ($this->addonData as $k => $v){
				update_post_meta($this->subscriptionPostID, $k, $v);
			}
			
		}
	}
	
	public function createSubscription(){
		//invoice number is unique and used to associate all portions of the transaction being run together.
		$this->invoiceNumber = $this->uniqueID.'-'.$this->userID;
		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);
		$xml->ARBCreateSubscriptionRequest(array(
			'refId' => $this->refID,
			'subscription' => array(
				'name' => $this->productName,
				'paymentSchedule' => array(
					'interval' => array(
						'length' => $this->productInterval,
						'unit' => $this->productUnit
					),
					'startDate' => $this->startDate,
					'totalOccurrences' => $this->productOccurrences,
					'trialOccurrences' => $this->productTrialOccurrences
				),
				'amount' => $this->productAmount,
				'trialAmount' => $this->productTrialAmount,
				'payment' => array(
					'creditCard' => array(
						'cardNumber' => $this->ccNumber,
						'expirationDate' => $this->ccYear.'-'.$this->ccMonth
					)
				),
				'order' => array(
					'invoiceNumber' => $this->invoiceNumber,
					'description' => $this->productDescription,
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
			)		
		));
		
		if ($xml->isSuccessful()){
			$this->subscriptionID = (string) $xml->subscriptionId;
		}else{
			$message = (string) $xml->messages->message->text;
			$this->errorMessage = 'Subscription Error: '.$message;
			$this->errorArray = array ('type' => 'Subscription Error', 'message' => $message);
			
			if ($this->billInitialPayment == 'on'){
				//We already billed for this subscription but it failed to create. We now need to void the billing charge.
				$this->voidReason = 'Voided due to subscription creation error';
				$this->processVoidTransaction();
			}			
		}
	}
	
	
	/*
		Create user account from incoming data if they are not logged in already
	*/
	
	public function createUser(){
		$newpass = wp_generate_password( 12, false );
		$userdata = array(
			'user_login' => esc_attr($this->userEmail),
			'first_name' => esc_attr($this->userFirstName),
			'last_name' => esc_attr($this->userLastName),
			'user_email' => esc_attr($this->userEmail),
			'user_pass' => esc_attr($newpass),
			'role' => 'subscriber'
		);
		$userID = wp_insert_user( $userdata );
		
		if (is_numeric($userID)){
			$this->userID = $userID;			
			//log in the successfully created user...
			$this->loginUser();	
			wp_new_user_notification($this->userID, $newpass);
			
			//set defaults and user data after successful payment/sub creations	
			$this->setUserData();
			
			return $userID;
			
		}else{
			$message = $userID->get_error_message();
			$this->errorMessage = 'Registration Error: '.$message;
			$this->errorArray = array ('type' => 'Registration Error', 'message' => $message);
		}
		
	}
	
	public function loginUser(){
		wp_set_current_user($this->userID, $this->userEmail);
        wp_set_auth_cookie($this->userID);
        do_action('wp_login', $this->userEmail);
	}
	
	public function setUserData(){
	
		//User Data
		update_user_meta($this->userID, 'userCompany', $this->userCompany);
		update_user_meta($this->userID, 'userPhoneNumber', $this->userPhoneNumber);
		update_user_meta($this->userID, 'userIPAddress', $this->userIPAddress);
	
		//inserts billing data to user meta
		update_user_meta($this->userID, 'billingFirstName', $this->billingFirstName);
		update_user_meta($this->userID, 'billingLastName', $this->billingLastName);
		update_user_meta($this->userID, 'billingEmail', $this->billingEmail);
		update_user_meta($this->userID, 'billingCompany', $this->billingCompany);
		update_user_meta($this->userID, 'billingPhoneNumber', $this->billingPhoneNumber);
		update_user_meta($this->userID, 'billingAddress', $this->billingAddress);
		update_user_meta($this->userID, 'billingCity', $this->billingCity);
		update_user_meta($this->userID, 'billingState', $this->billingState);
		update_user_meta($this->userID, 'billingZip', $this->billingZip);
		update_user_meta($this->userID, 'billingCountry', $this->billingCountry);
	
		//inserts shipping data to user meta
		update_user_meta($this->userID, 'shippingFirstName', $this->shippingFirstName);
		update_user_meta($this->userID, 'shippingLastName', $this->shippingLastName);
		update_user_meta($this->userID, 'shippingCompany', $this->shippingCompany);
		update_user_meta($this->userID, 'shippingAddress', $this->shippingAddress);
		update_user_meta($this->userID, 'shippingCity', $this->shippingCity);
		update_user_meta($this->userID, 'shippingState', $this->shippingState);
		update_user_meta($this->userID, 'shippingZip', $this->shippingZip);
		update_user_meta($this->userID, 'shippingCountry', $this->shippingCountry);
		
		//inserts cc data to user meta
		update_user_meta($this->userID, 'ccLastFour', $this->lastFour);
		update_user_meta($this->userID, 'ccMonth', $this->ccMonth);
		update_user_meta($this->userID, 'ccYear', $this->ccYear);
		
	}

	/*
		Returns an array containing the data needed to create a form for this service. including user data if available. Loop the array to create a new form on page.
	*/
	public function serviceArray(){
		$name = get_the_title($this->postID);
		$description = get_post_meta($this->postID, 'Description', true);
		$amount = get_post_meta( $this->postID, 'Amount', true );
		$billingType = get_post_meta( $this->postID, 'BillingType', true );
		$billingUnitSelect = get_post_meta( $this->postID, 'BillingUnit', true );
		$days = get_post_meta( $this->postID, 'Days', true );
		$months = get_post_meta( $this->postID, 'Months', true );
		$occurrences = get_post_meta( $this->postID, 'Occurrences', true );
		$enableTrial = get_post_meta( $this->postID, 'EnableTrial', true );
		$trialOccurrences = get_post_meta( $this->postID, 'TrialOccurrences', true );
		$trialAmount = get_post_meta( $this->postID, 'TrialAmount', true );
		
		//set us up the array
		
		$array = array();
		
		//product or service
		$array['name'] = $name;
		$array['description'] = $description;
		
		//recurring billing or not?
		
		if ($billingType == 'recurring'){
			$array['recurring'] = 'yes';
			
			//recurring month or days
			if ($billingUnitSelect == 'months'){
				$array['unit'] = 'months';
				$array['interval'] = $months;
					
					if ($occurrences < 1){
						$array['prettyUnit'] = 'Month';
					}else{
						$array['prettyUnit'] = 'Months';
					}				
				
			}else{
				$array['unit'] = 'days';
				$array['interval'] = $days;
				
					if ($occurrences < 1){
						$array['prettyUnit'] = 'Day';
					}else{
						$array['prettyUnit'] = 'Days';
					}
				
			}
			
			//recurring enable trial
			if ($enableTrial == 'yes'){
				$array['enableTrial'] = 'yes';
				$array['trialOccurrences'] = $trialOccurrences;
				$array['trialAmount'] = $trialAmount;
			}
			
			
			$array['occurrences'] = $occurrences;			
		}else{
			//is single charge
			$array['recurring'] = 'no';		
		}
		
		$array['amount'] = $amount;
		
		return $array;
	}
	
	public function userArray(){
		
		$array = array();
		
		$array['userID'] = $this->userID;
		$array['userFirstName'] = $this->userFirstName;
		$array['userLastName'] = $this->userLastName;
		$array['userCompany'] = $this->userCompany;
		$array['userEmail'] = $this->userEmail;
		$array['userPhoneNumber'] = $this->userPhoneNumber;
		$array['userIPAddress'] = $this->userIPAddress;
		
		return $array;
	}

	public function billingArray(){		
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
		
		return $array;
	}
	
	public function shippingArray(){		
		//set us up the array		
		$array = array();
		
		//shipping
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
	
	/*
		Returns an array of customer data from the subscription POST passed into it. Easy to use for displaying data on pages.
	*/
	
	public function subscriptionCustomerData(){
	
		$array = array();
		
		//inserts billing data for subscription
		$array[billingFirstName] = get_post_meta($this->postID, 'billingFirstName', true);		
		$array[billingLastName] = get_post_meta($this->postID, 'billingLastName', true);
		$array[billingEmail] = get_post_meta($this->postID, 'billingEmail', true);
		$array[billingCompany] = get_post_meta($this->postID, 'billingCompany', true);
		$array[billingPhoneNumber] = get_post_meta($this->postID, 'billingPhoneNumber', true);
		$array[billingAddress] = get_post_meta($this->postID, 'billingAddress', true);
		$array[billingCity] = get_post_meta($this->postID, 'billingCity', true);
		$array[billingState] = get_post_meta($this->postID, 'billingState', true);
		$array[billingZip] = get_post_meta($this->postID, 'billingZip', true);
		$array[billingCountry] = get_post_meta($this->postID, 'billingCountry', true);
	
		//inserts shipping data for subscription
		$array[shippingFirstName] = get_post_meta($this->postID, 'shippingFirstName', true);
		$array[shippingLastName] = get_post_meta($this->postID, 'shippingLastName', true);
		$array[shippingCompany] = get_post_meta($this->postID, 'shippingCompany', true);
		$array[shippingAddress] = get_post_meta($this->postID, 'shippingAddress', true);
		$array[shippingCity] = get_post_meta($this->postID, 'shippingCity', true);
		$array[shippingState] = get_post_meta($this->postID, 'shippingState', true);
		$array[shippingZip] = get_post_meta($this->postID, 'shippingZip', true);
		$array[shippingCountry] = get_post_meta($this->postID, 'shippingCountry', true);
		
		//inserts payment data for subscription
		$array[ccLastFour] = get_post_meta($this->postID, 'ccLastFour', true);
		$array[ccMonth] = get_post_meta($this->postID, 'ccMonth', true);
		$array[ccYear] = get_post_meta($this->postID, 'ccYear', true);
		
		//insert Subscription information		
		$array[subscriptionStartDate] = get_post_meta($this->postID, 'subscriptionStartDate', true);
		$array[subscriptionReferenceID] = get_post_meta($this->postID, 'subscriptionReferenceID', true);
		$array[subscriptionInvoiceNumber] = get_post_meta($this->postID, 'subscriptionInvoiceNumber', true);
		$array[subscriptionName] = get_post_meta($this->postID, 'subscriptionName', true);
		$array[subscriptionInterval] = get_post_meta($this->postID, 'subscriptionInterval', true);
		$array[subscriptionUnit] = get_post_meta($this->postID, 'subscriptionUnit', true);
		$array[subscriptionOccurrences] = get_post_meta($this->postID, 'subscriptionOccurrences', true);
		$array[subscriptionTrialOccurrences] = get_post_meta($this->postID, 'subscriptionTrialOccurrences', true);
		$array[subscriptionAmount] = get_post_meta($this->postID, 'subscriptionAmount', true);
		$array[subscriptionTrialAmount] = get_post_meta($this->postID, 'subscriptionTrialAmount', true);
		
		//These variables are updated during Silent Post Captures
		$array[subscriptionLastBillingDate] = get_post_meta($this->postID, 'subscriptionLastBillingDate', true);
		$array[subscriptionNextBillingDate] = get_post_meta($this->postID, 'subscriptionNextBillingDate', true);
		$array[subscriptionPaymentNumber] = get_post_meta($this->postID, 'subscriptionPaymentNumber', true);
		$array[subscriptionStatus] = get_post_meta($this->postID, 'subscriptionStatus', true);		
		return $array;
	}
	
	public function transactionCustomerData(){
	
		$array = array();
		
		//inserts billing data for subscription
		$array[billingFirstName] = get_post_meta($this->postID, 'billingFirstName', true);		
		$array[billingLastName] = get_post_meta($this->postID, 'billingLastName', true);
		$array[billingEmail] = get_post_meta($this->postID, 'billingEmail', true);
		$array[billingCompany] = get_post_meta($this->postID, 'billingCompany', true);
		$array[billingPhoneNumber] = get_post_meta($this->postID, 'billingPhoneNumber', true);
		$array[billingAddress] = get_post_meta($this->postID, 'billingAddress', true);
		$array[billingCity] = get_post_meta($this->postID, 'billingCity', true);
		$array[billingState] = get_post_meta($this->postID, 'billingState', true);
		$array[billingZip] = get_post_meta($this->postID, 'billingZip', true);
		$array[billingCountry] = get_post_meta($this->postID, 'billingCountry', true);
	
		//inserts shipping data for subscription
		$array[shippingFirstName] = get_post_meta($this->postID, 'shippingFirstName', true);
		$array[shippingLastName] = get_post_meta($this->postID, 'shippingLastName', true);
		$array[shippingCompany] = get_post_meta($this->postID, 'shippingCompany', true);
		$array[shippingAddress] = get_post_meta($this->postID, 'shippingAddress', true);
		$array[shippingCity] = get_post_meta($this->postID, 'shippingCity', true);
		$array[shippingState] = get_post_meta($this->postID, 'shippingState', true);
		$array[shippingZip] = get_post_meta($this->postID, 'shippingZip', true);
		$array[shippingCountry] = get_post_meta($this->postID, 'shippingCountry', true);
		
		//inserts payment data for subscription
		$array[ccLastFour] = get_post_meta($this->postID, 'ccLastFour', true);		
		
		//These variables are updated during Silent Post Captures
		$array[transactionAmount] = get_post_meta($this->postID, 'transactionAmount', true);
		$array[transactionType] = get_post_meta($this->postID, 'transactionType', true);
		$array[transactionDate] = get_post_meta($this->postID, 'transactionDate', true);
		$array[transactionID] = get_post_meta($this->postID, 'transactionID', true);
		$array[transactionInvoiceNumber] = get_post_meta($this->postID, 'transactionInvoiceNumber', true);
		$array[status] = get_post_meta($this->postID, 'status', true);
		
		return $array;	
	
	}
	
}
?>