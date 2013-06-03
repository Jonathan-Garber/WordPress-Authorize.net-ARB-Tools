<?php
class sbd {

	public function __construct($array){
	
		//MD5 Required Data
		$this->hashKey = get_option('apiHash');
		$this->apiLogin = get_option('apiLogin');
		$this->apiKey = get_option('apiKey');
		$this->apiTestMode = get_option('apiTestMode');
		$this->apiEmail = get_option('apiEmail');
		$this->vtUser = get_option('vtUser');
		$this->dateToday = date('Y-m-d');
		
		//Hardcoded for now
		$this->emailSignature = 'The '.get_bloginfo('name').' Team';
			
		//POST Data
		$this->x_response_code = $array['x_response_code'];
		$this->x_response_reason_code = $array['x_response_reason_code'];
		$this->x_response_reason_text = $array['x_response_reason_text'];
		$this->x_avs_code = $array['x_avs_code'];
		$this->x_auth_code = $array['x_auth_code'];		
		$this->x_trans_id = $array['x_trans_id'];
		$this->x_card_type = $array['x_card_type'];
		$this->x_invoice_num = $array['x_invoice_num'];
		$this->x_description = $array['x_description'];
		$this->x_amount = $array['x_amount'];
		$this->x_method = $array['x_method'];
		$this->x_type = $array['x_type'];
		$this->x_cust_id = $array['x_cust_id'];
		$this->x_account_number = $array['x_account_number'];
		$this->x_first_name = $array['x_first_name'];
		$this->x_last_name = $array['x_last_name'];
		$this->x_company = $array['x_company'];
		$this->x_address = $array['x_address'];
		$this->x_city = $array['x_city'];
		$this->x_state = $array['x_state'];
		$this->x_zip = $array['x_zip'];
		$this->x_country = $array['x_country'];
		$this->x_phone = $array['x_phone'];
		$this->x_fax = $array['x_fax'];
		$this->x_email = $array['x_email'];
		$this->x_ship_to_first_name = $array['x_ship_to_first_name'];
		$this->x_ship_to_last_name = $array['x_ship_to_last_name'];
		$this->x_ship_to_company = $array['x_ship_to_company'];
		$this->x_ship_to_address = $array['x_ship_to_address'];
		$this->x_ship_to_city = $array['x_ship_to_city'];
		$this->x_ship_to_state = $array['x_ship_to_state'];
		$this->x_ship_to_zip = $array['x_ship_to_zip'];
		$this->x_ship_to_country = $array['x_ship_to_country'];
		$this->x_tax = $array['x_tax'];
		$this->x_duty = $array['x_duty'];
		$this->x_freight = $array['x_freight'];
		$this->x_tax_exempt = $array['x_tax_exempt'];
		$this->x_po_num = $array['x_po_num'];
		$this->x_MD5_Hash = $array['x_MD5_Hash'];
		$this->x_cavv_response = $array['x_cavv_response'];
		$this->x_test_request = $array['x_test_request'];
		$this->x_subscription_id = $array['x_subscription_id'];
		$this->x_subscription_paynum = $array['x_subscription_paynum'];
		
		//ARB MD5
		$this->arbMD5 = strtoupper( md5( $this->hashKey . $this->x_trans_id . $this->x_amount ) );

		//AIM FROM API MD5
		$this->aimMD5 = strtoupper( md5( $this->hashKey . $this->apiLogin . $this->x_trans_id . $this->x_amount ) );	
		
		//VT MD5
		$this->vtMD5 = strtoupper( md5( $this->hashKey . $this->vtUser . $this->x_trans_id . $this->x_amount ) );
		
		if ( $this->arbMD5 == $this->x_MD5_Hash || $this->aimMD5 == $this->x_MD5_Hash || $this->vtMD5 == $this->x_MD5_Hash ){		
			$this->insertTransaction();			
		}		
	}
	
	
	public function insertTransaction(){
		//error transaction
		if ($this->x_response_code == 2 || $this->x_response_code == 3  || $this->x_response_code == 4){		
			switch ($this->x_type){
				case 'credit':
					$this->status = 'Refund Error: '.$this->x_response_reason_text;
					$this->sendEmail = 'creditError';
				BREAK;
				
				case 'void':
					$this->status = 'Void Error: '.$this->x_response_reason_text;
					$this->sendEmail = 'voidError';
					
				BREAK;
				
				case 'auth_only':
					$this->status = 'Pre Authorization Error: '.$this->x_response_reason_text;
					$this->sendEmail = 'authOnlyError';
				BREAK;
				
				case 'auth_capture':
					$this->status = 'Payment Error: '.$this->x_response_reason_text;
					$this->sendEmail = 'authCaptureError';
					if ( !empty($this->x_subscription_id) ){
					/*
						We have a subscription payment ERROR.
						Check status of subscription at ARB
						if status = suspended then this was the first payment ever for this cc info
						we need to mark our records as suspended
						
						if status = active then this was a failed payment on cc info that previously worked. We need to update our status to activeSuspended
						
						on both cases we will alert the customer to the issue. request they update billing data. if no update occurs within set amount of days in settings we then auto-cancel their subscription.
						
					*/
						$arbStatus = $this->getARBSubscriptionStatus();
							if ($arbStatus == 'suspended'){
								$this->subscriptionStatus = 'suspended';
							}
							
							if($arbStatus == 'active'){
								$this->subscriptionStatus = 'activeSuspended';
							}
							
							$this->sendEmail = 'arbCaptureError';
					}
				BREAK;
			}		
		}else{
		//not an error
			switch ($this->x_type){
				case 'credit':
					$this->status = 'Refund';
					$this->sendEmail = 'credit';
				BREAK;
				
				case 'void':
					$this->status = 'Void';
					$this->sendEmail = 'void';
					
				BREAK;
				
				case 'auth_only':
					$this->status = 'Card Pre Authorization';
					$this->sendEmail = 'authOnly';
				BREAK;
				
				case 'auth_capture':
					$this->status = 'paid';
					$this->sendEmail = 'authCapture';
					//if we have a subID then this error is from an ARB transaction
					if ( !empty($this->x_subscription_id) ){
						$this->subscriptionStatus = 'active';
						$this->sendEmail = 'arbAuthCapture';
					}
				BREAK;
			}
		
		}
		
		if ($this->x_trans_id == 0 || $this->x_trans_id == ''){
			$this->x_trans_id = 'Error-'.$this->x_invoice_num;
		}
		
				
		if ( $this->x_cust_id == '' || $this->x_cust_id == 0){
			$this->x_cust_id = '1';
		}
		
		$transactionPost = array(
			'post_type' => 'auth-transactions',
			 'post_title' => esc_attr($this->x_trans_id),
			 'post_content' => esc_attr($this->x_description),
			 'post_status' => 'publish',
			 'post_author' => esc_attr($this->x_cust_id),
			 'post_category' => array(0)
		  );
			
			$this->transactionPostID = wp_insert_post( $transactionPost );			
			$this->updateTransactionPostMeta();
			
			//sends email alerts out
			$this->sendEmails();
	}
	
	
	public function sendEmails(){
		/*
			Debug function so I get an alert when SBD is hit...
		*/
		if ($this->apiTestMode == 'on'){
			$subject = 'Debug Email';
			$adminBody = "
			Full Name: [-fullname-]\n
			First Name: [-billingfirstname-]\n
			Last Name: [-billinglastname-]\n
			Email: [-billingemail-]\n
			Phone: [-billingphonenumber-]\n
			Address: [-billingaddress-]\n
			City: [-billingcity-]\n
			State: [-billingstate-]\n
			Zip: [-billingzip-]\n
			Country: [-billingcountry-]\n
			Card Type: [-billingcardtype-]\n
			Card Number: [-billingcardnumber-]\n
			Description: [-productdescription-]\n
			Amount: [-productamount-]\n
			Error Message/Status: [-errormessage-]\n
			Email Signature: [-emailsignature-]\n
			Subscription ID: [-subscriptionid-]\n
			Payment Number: [-paymentnumber-]\n
			";
			$adminBody = $this->processEmailBody($body);
			
			wp_mail($this->apiEmail, $this->status, $adminBody);
		}
		
		//error emails			
		if ($this->sendEmail == 'authOnlyError'){			
			$subject = get_bloginfo('name').' Card Authorization Error';
			$adminBody = 
			"There was a authorization error with the following information\n\n
			".$this->status."\n\n
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Amount: ".$this->x_amount."\n
			";			
		}
		
		if ($this->sendEmail == 'authCaptureError'){	
			$subject = get_bloginfo('name').' Payment Failed';		
			$adminBody = 
			"There was a payment error with the following information\n\n
			".$this->status."\n\n
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Product: ".$this->x_description."\n
			Amount: ".$this->x_amount."\n
			";		
		}
		if ($this->sendEmail == 'arbCaptureError'){
			$subject = get_bloginfo('name').' Subscription Payment Failed';
			$adminBody = 
			"There was a payment error for the following subscription\n\n
			".$this->status."\n\n
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Subscription ID: ".$this->x_subscription_id."\n
			Payment Number: ".$this->x_subscription_paynum."\n
			Product: ".$this->x_description."\n
			Amount: ".$this->x_amount."\n
			";
				
			$body = "
			Hello [-fullname-],\n\n
			We attempted to process payment number [-paymentnumber-] in the amount of [-productamount-] for the following subscription.\n\n
			Subscription Id: [-subscriptionid-]\n
			Product or Service: [-productdescription-]\n\n
			Amount: [-productamount-]\n\n
			We tried to process this payment with the card you have on file.\n
			Card Type: [-billingcardtype-]\n
			Card Number: [-billingcardnumber-]\n\n
			There was an error returned with the following message.\n
			[-errormessage-]\n\n
			Subscriptions that fail to process a payment require a billing information update. You can do this via your account management tools on site. You must update your billing information with a valid credit card before 7 days from the date of the suspension or the subscription will be cancelled automatically.
			";
			$subject = apply_filters('arbCaptureErrorSubject',$subject);
			$body = apply_filters('arbCaptureErrorBody',$body);			
			$body = $this->processEmailBody($body);
		}
		
		
		//success emails
		if ($this->sendEmail == 'void'){
			$subject = get_bloginfo('name').' Void Approved';
			$adminBody = 
			"There was a void issued for the following\n\n
			
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Description: ".$this->x_description."\n
			Amount: ".$this->x_amount."\n
			";		
		}		
		
		if ($this->sendEmail == 'credit'){	
			$subject = get_bloginfo('name').' Refund Approved';
			$adminBody = 
			"There was a refund issued for the following\n\n
			
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Description: ".$this->x_description."\n
			Amount: ".$this->x_amount."\n
			";
			
			$body = "
			Hello [-fullname-],\n\n
			We have processed a refund for [-productdescription-] for the following amount [-productamount-] to the card we have on file.\n\n
			Card Type: [-billingcardtype-]\n
			Card Number: [-billingcardnumber-]
			";
			
			$subject = apply_filters('creditSuccessSubject',$subject);
			$body = apply_filters('creditSuccessBody',$body);
			$body = $this->processEmailBody($body);
		}
		

		
		if ($this->sendEmail == 'authOnly'){		
			$subject = get_bloginfo('name').' Card Pre Authorization Approved';	
			$adminBody = 
			"There was a pre authorization approved for the following\n\n
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Amount: ".$this->x_amount."\n
			";			
		}
		
		if ($this->sendEmail == 'authCapture'){
			$subject = get_bloginfo('name').' Payment Approved';
			$adminBody = 
			"There was a payment approved for the following\n\n
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Product: ".$this->x_description."\n
			Amount: ".$this->x_amount."\n
			";
			
			$body = "
			Hello [-fullname-],\n\n
			We have processed a payment for [-productdescription-] in the following amount [-productamount-] to your credit card.\n\n
			Card Type: [-billingcardtype-]\n
			Card Number: [-billingcardnumber-]\n\n
			Your payment was successful & we wanted to thank you for your business.\n
			If you have any questions or concerns please feel free to reply to this e-mail or check our website for other options.\n\n
			[-emailsignature-]
			";
			
			$subject = apply_filters('authCaptureSuccessSubject',$subject);
			$body = apply_filters('authCaptureSuccessBody',$body);
			$body = $this->processEmailBody($body);			
			
		}
		
		if ($this->sendEmail == 'arbAuthCapture'){			
			$subject = get_bloginfo('name').' Subscription Payment Approved';
			$adminBody = 
			"There was a payment approved for the following subscription\n\n
			Customer Name: ".$this->x_first_name." ".$this->x_last_name."\n
			Customer Email: ".$this->x_email."\n
			Customer Phone: ".$this->x_phone."\n
			Card Number: ".$this->x_account_number."\n
			Subscription ID: ".$this->x_subscription_id."\n
			Payment Number: ".$this->x_subscription_paynum."\n
			Product: ".$this->x_description."\n
			Amount: ".$this->x_amount."\n
			";
			
			$body = "
			Hello [-fullname-],\n\n
			We have processed payment number [-paymentnumber-] in the amount of [-productamount-] for the following subscription.\n\n
			Subscription Id: [-subscriptionid-]\n
			Product or Service: [-productdescription-]\n\n
			Amount: [-productamount-]\n\n
			We tried to process this payment with the card you have on file.\n
			Card Type: [-billingcardtype-]\n
			Card Number: [-billingcardnumber-]\n\n
			";
			
			$subject = apply_filters('arbAuthCaptureSuccessSubject',$subject);
			$body = apply_filters('arbAuthCaptureSuccessBody',$body);
			$body = $this->processEmailBody($body);				
			
		}
		
		
		//We check for subject variable then check if there is a admin or customer email or both etc. 
		$headers = apply_filters('wpAuthEmailHeader',false) ? apply_filters('wpAuthEmailHeader',false) : null;
		
		if ($subject && $adminBody){
			wp_mail($this->apiEmail, $subject, $adminBody, $headers);
		}
		
		if ($subject && $body){
			wp_mail($this->x_email, $subject, $body, $headers);
		}		
		
	}
	
	
	public function processEmailBody($body){
		$tagsArray = array(
					'[-fullname-]' => $this->x_first_name.' '.$this->x_last_name,
					'[-billingfirstname-]' => $this->x_first_name,
					'[-billinglastname-]' => $this->x_last_name,
					'[-billingemail-]' => $this->x_email,
					'[-billingphonenumber-]' => $this->x_phone,
					'[-billingaddress-]' => $this->x_address,
					'[-billingcity-]' => $this->x_city,
					'[-billingstate-]' => $this->x_state,
					'[-billingzip-]' => $this->x_zip,
					'[-billingcountry-]' => $this->x_country,
					'[-billingcardtype-]' => $this->x_card_type,
					'[-billingcardnumber-]' => $this->x_account_number,
					'[-productdescription-]' => $this->x_description,
					'[-productamount-]' => $this->x_amount,
					'[-errormessage-]' => $this->status,
					'[-emailsignature-]' => $this->emailSignature,
					'[-subscriptionid-]' => $this->x_subscription_id,
					'[-paymentnumber-]' => $this->x_subscription_paynum
					);
		
		$body = str_replace(array_keys($tagsArray), $tagsArray, $body);
		return $body;
	}
	
	public function updateTransactionPostMeta(){
	
		//inserts billing data for this transaction
		update_post_meta($this->transactionPostID, 'billingFirstName', $this->x_first_name);
		update_post_meta($this->transactionPostID, 'billingLastName', $this->x_last_name);
		update_post_meta($this->transactionPostID, 'billingEmail', $this->x_email);
		update_post_meta($this->transactionPostID, 'billingCompany', $this->x_company);
		update_post_meta($this->transactionPostID, 'billingPhoneNumber', $this->x_phone);
		update_post_meta($this->transactionPostID, 'billingAddress', $this->x_address);
		update_post_meta($this->transactionPostID, 'billingCity', $this->x_city);
		update_post_meta($this->transactionPostID, 'billingState', $this->x_state);
		update_post_meta($this->transactionPostID, 'billingZip', $this->x_zip);
		update_post_meta($this->transactionPostID, 'billingCountry', $this->x_country);
	
		//inserts shipping data for this transaction
		update_post_meta($this->transactionPostID, 'shippingFirstName', $this->x_ship_to_first_name);
		update_post_meta($this->transactionPostID, 'shippingLastName', $this->x_ship_to_last_name);
		update_post_meta($this->transactionPostID, 'shippingCompany', $this->x_ship_to_company);
		update_post_meta($this->transactionPostID, 'shippingAddress', $this->x_ship_to_address);
		update_post_meta($this->transactionPostID, 'shippingCity', $this->x_ship_to_city);
		update_post_meta($this->transactionPostID, 'shippingState', $this->x_ship_to_state);
		update_post_meta($this->transactionPostID, 'shippingZip', $this->x_ship_to_zip);
		update_post_meta($this->transactionPostID, 'shippingCountry', $this->x_ship_to_country);
		
		//inserts payment data for this transaction
		update_post_meta($this->transactionPostID, 'ccLastFour', $this->x_account_number);

		//inserts transaction data
		update_post_meta($this->transactionPostID, 'transactionDate', $this->dateToday);
		update_post_meta($this->transactionPostID, 'transactionID', $this->x_trans_id);
		update_post_meta($this->transactionPostID, 'transactionInvoiceNumber', $this->x_invoice_num);
		update_post_meta($this->transactionPostID, 'status', $this->status);
		update_post_meta($this->transactionPostID, 'transactionType', $this->x_type);
		update_post_meta($this->transactionPostID, 'transactionAmount', $this->x_amount);
		
		//if this is a subscription transaction we run update subscription
		if ( !empty($this->x_subscription_id) ){
			$this->updateSubscription();
		}
	}
	
	public function updateSubscription(){
		//gotta find this subscription that matches the incoming ID
		$subscriptionsArray = array(
					'post_type' => 'auth-subscriptions',
					'meta_query' => array(
										array(
											'key' => 'subscriptionID',
											'value' => $this->x_subscription_id,
										)
									),
					);
						
		$subscription = get_posts($subscriptionsArray);
		$this->subscriptionPostID = $subscription[0]->ID;
		
		//update subscription post meta to match new data
		$this->updateSubscriptionMeta();
	}
	
	public function updateSubscriptionMeta(){
	
		//figure next billing date
		$subscriptionLastBillingDate = get_post_meta($this->subscriptionPostID, 'subscriptionLastBillingDate', true);
		$subscriptionInterval = get_post_meta($this->subscriptionPostID, 'subscriptionInterval', true);
		$subscriptionUnit = get_post_meta($this->subscriptionPostID, 'subscriptionUnit', true);
		$add = '+'.$subscriptionInterval.' '.$subscriptionUnit;		
		
		$nextBillingDate = strtotime(date('Y-m-d', strtotime($subscriptionLastBillingDate)) . $add);
		$this->NextBillingDate = date('Y-m-d', $nextBillingDate);
	
		update_post_meta($this->subscriptionPostID, 'subscriptionLastBillingDate', $this->dateToday);
		update_post_meta($this->subscriptionPostID, 'subscriptionNextBillingDate', $this->NextBillingDate);
		update_post_meta($this->subscriptionPostID, 'subscriptionPaymentNumber', $this->x_subscription_paynum);
		update_post_meta($this->subscriptionPostID, 'subscriptionStatus', $this->subscriptionStatus);
	}
	
	public function getARBSubscriptionStatus(){
		$this->refID = 'ARB-STATUS-UID-'.$this->x_cust_id;
		$xml = new AuthnetXML($this->apiLogin, $this->apiKey, $this->apiTestMode);
		$xml->ARBGetSubscriptionStatusRequest(array(
			'refId' => $this->refID,
			'subscriptionId' => $this->x_subscription_id
		));	
		
		if ($xml->isSuccessful()){
			$return = (string) $xml->status;
		}else{
			$return = 'request-failed';
		}
		return $return;
	}

}
?>