<?php
class sbd {

	public function __construct($array){
	
		//MD5 Required Data
		$this->hashKey = get_option('apiHash');
		$this->apiLogin = get_option('apiLogin');
		$this->apiTestMode = get_option('apiTestMode');
		$this->apiEmail = get_option('apiEmail');
		$this->vtUser = get_option('vtUser');
		$this->dateToday = date('Y-m-d');
	
	
		if ($this->apiTestMode == 'on'){
			$body = print_r( $array, true );
			wp_mail($this->apiEmail, 'HIT: '.$array['x_type'], $body);
		}
	
	
		//POST Data
		$this->x_response_code = $array['x_response_code'];
		$this->x_response_subcode = $array['x_response_subcode'];
		$this->x_response_reason_code = $array['x_response_reason_code'];
		$this->x_response_reason_text = $array['x_response_reason_text'];
		$this->x_auth_code = $array['x_auth_code'];
		$this->x_avs_code = $array['x_avs_code'];
		$this->x_trans_id = $array['x_trans_id'];
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
				BREAK;
				
				case 'void':
					$this->status = 'Void Error: '.$this->x_response_reason_text;
					
				BREAK;
				
				case 'auth_only':
				if ($this->x_amount <= 0.01){
					$this->status = 'Pre Authorization Error: '.$this->x_response_reason_text;
				}else{
					$this->status = 'Authorization Only Error: '.$this->x_response_reason_text;
				}
				BREAK;
				
				case 'auth_capture':
					$this->status = 'Payment Error: '.$this->x_response_reason_text;
					//if we have a subID then this error is from an ARB transaction
					if ( !empty($this->x_subscription_id) ){
						$this->subscriptionStatus = 'error';			
					}
				BREAK;
			}
		}else{
		//not an error
			switch ($this->x_type){
				case 'credit':
					$this->status = 'Refund';
				BREAK;
				
				case 'void':
					$this->status = 'Void';
					
				BREAK;
				
				case 'auth_only':
				if ($this->x_amount <= 0.01){
					$this->status = 'Card Pre Authorization';
				}else{
					$this->status = 'Card Authorization Only';
				}
				BREAK;
				
				case 'auth_capture':
					$this->status = 'Paid';
					//if we have a subID then this error is from an ARB transaction
					if ( !empty($this->x_subscription_id) ){
						$this->subscriptionStatus = 'paid';			
					}
				BREAK;
			}		
		
		}
		
		if ($this->x_trans_id == 0 || $this->x_trans_id == ''){
			$this->x_trans_id = 'Error-'.rand(10000, 100000);
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
		
		$this->NextBillingDate = strtotime(date('Y-m-d', strtotime($subscriptionLastBillingDate)) . $add);
	
		update_post_meta($this->subscriptionPostID, 'subscriptionLastBillingDate', $this->dateToday);
		update_post_meta($this->subscriptionPostID, 'subscriptionNextBillingDate', $this->NextBillingDate);
		update_post_meta($this->subscriptionPostID, 'subscriptionPaymentNumber', $this->x_subscription_paynum);
		update_post_meta($this->subscriptionPostID, 'subscriptionStatus', $this->subscriptionStatus);
	}

}
?>