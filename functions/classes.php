<?php

class billing {

	public function __construct($postID = ''){
		$this->postID = $postID;
		$this->startDate = date('Y-m-d');
	
		//API Settings
		$this->apiLogin = get_option('apiLogin');
		$this->apiKey = get_option('apiKey');
		$this->apiTestMode = get_option('apiTestMode');
		
		//User Data
		$this->userID = get_current_user_id();
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
		$this->shippingEmail = get_user_meta($this->userID, 'shippingEmail', true);
		$this->shippingCompany = get_user_meta($this->userID, 'shippingCompany', true);
		$this->shippingPhoneNumber = get_user_meta($this->userID, 'shippingPhoneNumber', true);
		$this->shippingAddress = get_user_meta($this->userID, 'shippingAddress', true);
		$this->shippingCity = get_user_meta($this->userID, 'shippingCity', true);
		$this->shippingState = get_user_meta($this->userID, 'shippingState', true);
		$this->shippingZip = get_user_meta($this->userID, 'shippingZip', true);
		$this->shippingCountry = get_user_meta($this->userID, 'shippingCountry', true);
	}
	
	/*
		Runs after submitted data and does a single charge/payment. this is NOT for ARB creation.
	*/
	
	
	public function processPayment(){
	
		$preauth = $this->processPreAuth();
		print_r($preauth);
	
	}
	/*
		Runs pre-auth and void on CC info for 0.01cent
	*/	
	public function processPreAuth(){	
		$this->invoiceNumber = 'PA-'.rand(1000000, 100000000).'-UID-'.$this->userID;
		$this->refID = 'PA-UID-'.$this->userID;
			
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

		if ($xml->isSuccessful()){
			$this->transID = $xml->transactionResponse->transId;
		}else{
			$this->errorMessage = 'Payment Error: '.$xml->messages->message->text.' -- '.$xml->transactionResponse->errors->error->errorText;
		}
	}
	
	public function voidTransaction(){
		$this->refID = 'VOID-UID-'.$this->userID;
		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);
		$xml->createTransactionRequest(array(
			'refId' => $this->refID,
			'transactionRequest' => array(
				'transactionType' => 'voidTransaction',
				'refTransId' => $this->transID,
			),
		));
		
		if ($xml->isSuccessful()){
			$this->voided = $xml->transactionResponse->responseCode;
		}else{
			$this->errorMessage = 'Void Error: '.$xml->transactionResponse->errors->error->errorText;
		}
		
	}
	
	/*
		Runs after submitted data and pre-auths/voids the card and creates ARB when successful
	*/
	public function processARB(){			
			$this->processPreAuth();
			
			//Preauth sets TransID upon success
			if ($this->transID != ''){
				$this->voidTransaction();
				
				//voiding sets the voided variable to 1 upon success
				if ($this->voided == '1'){
					$this->createSubscription();
					
					//create subscription sets a subscription ID upon success
					if ($this->subscriptionID){
						//send em to the thank you page
						wp_redirect('/thank-you');
					}
				}
			}
	}
	
	public function createSubscription(){
		$this->refID = 'SUB-UID-'.$this->userID;
		$this->invoiceNumber = 'SUB-'.rand(1000000, 100000000).'-UID-'.$this->userID;
		
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
			$this->subscriptionID = $xml->messages->resultCode;
		}else{
			$this->errorMessage = 'Subscription Error: '.$xml->messages->message->text;
		}		
		
	}
	
	
	/*
		Create user account from incoming data if they are not logged in already
	*/
	
	public function createUser(){
		$newpass = wp_generate_password( 12, false );
		$userdata = array(
			'user_login' => esc_attr($this->billingEmail),
			'first_name' => esc_attr($this->billingFirstName),
			'last_name' => esc_attr($this->billingLastName),
			'user_email' => esc_attr($this->billingEmail),
			'user_pass' => esc_attr($newpass),
			'role' => 'subscriber'
		);
		$userID = wp_insert_user( $userdata );
		
		if (is_numeric($userID)){
			$this->userID = $userID;
			$this->setUserData();
			//log in the successfully created user...
			$this->loginUser();			
		}else{
			$this->errorMessage = $userID->get_error_message();
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
		update_user_meta($this->userID, 'shippingEmail', $this->shippingEmail);
		update_user_meta($this->userID, 'shippingPhoneNumber', $this->shippingPhoneNumber);
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
		Settings form is drawn using this function
	*/
	public function settingsForm(){
		
		if ($this->apiTestMode == 'on'){
			$apiTestMode = 'checked="checked"';
		}
		
		printf('
			<form method="POST">
			API Login ID: <input type="text" name="apiLogin" value="%s"><br/>
			API Transaction Key: <input type="text" name="apiKey" value="%s"><br/>
			API Test Mode: <input %s type="checkbox" name="apiTestMode"<br/><br/>
			<input type="submit" name="saveAPISettings" value="Save Settings">
		', $this->apiLogin, $this->apiKey, $apiTestMode);
	
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

	public function shippingArray(){		
		//set us up the array		
		$array = array();
		
		//shipping		
		$array['shippingFirstName'] = $this->shippingFirstName;
		$array['shippingLastName'] = $this->shippingLastName;
		$array['shippingCompany'] = $this->shippingCompany;
		$array['shippingEmail'] = $this->shippingEmail;
		$array['shippingPhoneNumber'] = $this->shippingPhoneNumber;
		$array['shippingAddress'] = $this->shippingAddress;
		$array['shippingCity'] = $this->shippingCity;
		$array['shippingState'] = $this->shippingState;
		$array['shippingZip'] = $this->shippingZip;
		$array['shippingCountry'] = $this->shippingCountry;
		
		return $array;
	}			
	
	public function monthSelect($fieldName){
		echo '<select id="'.$fieldName.'" name="'.$fieldName.'">';
		  for ($i = 1; $i <= 12; $i++) {			
			$month = date("F", mktime(0, 0, 0, $i, 1));			
			printf('<option value="%s">%s - %s</option>', $i, $i ,$month);
		  }
		echo '</select>';
	}
	
	public function yearSelect($fieldName){
		echo '<select id="'.$fieldName.'" name="'.$fieldName.'">';
		$year = date("Y"); for ($i = 0; $i <= 12; $i++) {echo "<option>$year</option>"; $year++;}
		echo '</select>';
	}
	
	public function countrySelect($fieldName, $selected = ''){
	$countries = array(
		  "GB" => "United Kingdom",
		  "US" => "United States",
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
?>