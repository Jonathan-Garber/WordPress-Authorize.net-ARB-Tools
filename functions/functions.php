<?php
function wpat_encrypt_user_id($userId){
return $userId + 56849;
}

function wpat_decrypt_user_id($userId){
return $userId - 56849;
}

function wpat_get_referrals($userId) {
	$refs = get_user_meta($userId, 'sb_referral', false);
  	return $refs;
}


function wpat_getARBSubscriptionStatus($subscription = ''){
	$apiLogin = get_option('apiLogin');
	$apiKey = get_option('apiKey');
	$apiTestMode = get_option('apiTestMode');	
	
	if (!empty($subscription)){
		if (is_array($subscription)){
			$return = array();
			foreach ($subscription as $a){
				$xml = new AuthnetXML($apiLogin, $apiKey, $apiTestMode);
				$xml->ARBGetSubscriptionStatusRequest(array(
					'refId' => $a,
					'subscriptionId' => $a
				));
				if ($xml->isSuccessful()){
					$return[$a] = (string) $xml->status;
				}else{
					$return[$a] = 'request-failed';
				}
			}
		}else{
			$xml = new AuthnetXML($apiLogin, $apiKey, $apiTestMode);
			$xml->ARBGetSubscriptionStatusRequest(array(
				'refId' => $subscription,
				'subscriptionId' => $subscription
			));
			if ($xml->isSuccessful()){
				$return = (string) $xml->status;
			}else{
				$return =  'request-failed';
			}
		}		
	}else{	
		$args = array(
				'post_type' => 'auth-subscriptions',
				'posts_per_page' => -1
				);
		$posts = get_posts($args);
		$return = array();
		foreach ($posts as $p){
			$xml = new AuthnetXML($apiLogin, $apiKey, $apiTestMode);
			$subID = get_post_meta($p->ID, 'subscriptionID', true);
			$xml->ARBGetSubscriptionStatusRequest(array(
				'refId' => $p->ID,
				'subscriptionId' => $subID
			));
			if ($xml->isSuccessful()){
				$return[$subID] = (string) $xml->status;
			}else{
				$return[$subID] = 'request-failed';
			}
		}
	}
	
	return $return;
	
}




// Subscriptions loader for jQuery data tables
function wpat_jqdt_sub_total() {
  if ( $_GET['wpat_jqdt_sub_total'] ) {
    header('Content-type: application/json');
    $count_posts = wp_count_posts('auth-subscriptions');
    $published_posts = $count_posts->publish;
    echo json_encode($published_posts);
    die;
  }
}
add_action('admin_init','wpat_jqdt_sub_total');

function wpat_jqdt_sub_loader() {
  $offset = $_GET['wpat_jqdt_sub_offset'];
  if ( $offset ) {
    header('Content-type: application/json');
    $args = array(
      'posts_per_page'  => 50,
      'post_type'       => 'auth-subscriptions',
      'post_status'     => 'publish',
      'offset' => $offset
    );
    $subs = get_posts($args);
    $subscriptions = array();
    
    foreach ( $subs as $i => $t ) {
      $subscriptions[$i]['0'] = $t->ID;
      $subscriptions[$i]['1'] = get_post_time("F j, Y, g:i a",false,$t->ID);
      $subscriptions[$i]['2'] = get_post_meta($t->ID,'billingFirstName',true);
      $subscriptions[$i]['3'] = get_post_meta($t->ID,'billingLastName',true);
      $subscriptions[$i]['4'] = get_post_meta($t->ID,'billingEmail',true);
      $subscriptions[$i]['5'] = get_post_meta($t->ID,'billingCompany',true);
      $subscriptions[$i]['6'] = get_post_meta($t->ID,'billingPhoneNumber',true);
      $subscriptions[$i]['7'] = get_post_meta($t->ID,'billingAddress',true);
      $subscriptions[$i]['8'] = get_post_meta($t->ID,'billingCity',true);
      $subscriptions[$i]['9'] = get_post_meta($t->ID,'billingState',true);
      $subscriptions[$i]['10'] = get_post_meta($t->ID,'billingZip',true);
      $subscriptions[$i]['11'] = get_post_meta($t->ID,'billingCountry',true);
      $subscriptions[$i]['12'] = get_post_meta($t->ID,'shippingFirstName',true);
      $subscriptions[$i]['13'] = get_post_meta($t->ID,'shippingLastName',true);
      $subscriptions[$i]['14'] = get_post_meta($t->ID,'shippingCompany',true);
      $subscriptions[$i]['15'] = get_post_meta($t->ID,'shippingAddress',true);
      $subscriptions[$i]['16'] = get_post_meta($t->ID,'shippingCity',true);
      $subscriptions[$i]['17'] = get_post_meta($t->ID,'shippingState',true);
      $subscriptions[$i]['18'] = get_post_meta($t->ID,'shippingZip',true);
      $subscriptions[$i]['19'] = get_post_meta($t->ID,'shippingCountry',true);
      $subscriptions[$i]['20'] = str_replace('XXXX', '', get_post_meta($t->ID,'ccLastFour',true));
      $subscriptions[$i]['21'] = get_post_meta($t->ID,'subscriptionID',true);
      $subscriptions[$i]['22'] = get_post_meta($t->ID,'subscriptionStartDate',true);
      $subscriptions[$i]['23'] = get_post_meta($t->ID,'subscriptionReferenceID',true);
      $subscriptions[$i]['24'] = get_post_meta($t->ID,'subscriptionInvoiceNumber',true);
      $subscriptions[$i]['25'] = get_post_meta($t->ID,'subscriptionName',true);
      $subscriptions[$i]['26'] = get_post_meta($t->ID,'subscriptionInterval',true);
      $subscriptions[$i]['27'] = get_post_meta($t->ID,'subscriptionUnit',true);
      $subscriptions[$i]['28'] = get_post_meta($t->ID,'subscriptionOccurrences',true);
      $subscriptions[$i]['29'] = get_post_meta($t->ID,'subscriptionTrialOccurrences',true);
      $subscriptions[$i]['30'] = get_post_meta($t->ID,'subscriptionAmount',true);
      $subscriptions[$i]['31'] = get_post_meta($t->ID,'subscriptionTrialAmount',true);
      $subscriptions[$i]['32'] = get_post_meta($t->ID,'subscriptionLastBillingDate',true);
      $subscriptions[$i]['33'] = get_post_meta($t->ID,'subscriptionNextBillingDate',true);
      $subscriptions[$i]['34'] = get_post_meta($t->ID,'subscriptionPaymentNumber',true);
      $subscriptions[$i]['35'] = get_post_meta($t->ID,'subscriptionStatus',true);
      $subscriptions[$i]['36'] = get_post_meta($t->ID,'subscriptionCanceledBy',true);
      $subscriptions[$i]['37'] = get_post_meta($t->ID,'subscriptionCanceledDate',true);
    }

    echo json_encode($subscriptions);

    die;
  }
}
add_action('admin_init','wpat_jqdt_sub_loader');







// Transactions loader for jQuery data tables
function wpat_jqdt_trans_total() {
  if ( $_GET['wpat_jqdt_trans_total'] ) {
    header('Content-type: application/json');
    $count_posts = wp_count_posts('auth-transactions');
    $published_posts = $count_posts->publish;
    echo json_encode($published_posts);
    die;
  }
}
add_action('admin_init','wpat_jqdt_trans_total');

function wpat_jqdt_trans_loader() {
  $offset = $_GET['wpat_jqdt_trans_offset'];
  if ( $offset ) {
    header('Content-type: application/json');
    $args = array(
      'posts_per_page'  => 50,
      'post_type'       => 'auth-transactions',
      'post_status'     => 'publish',
      'offset' => $offset
    );
    $trans = get_posts($args);
    $transactions = array();
    
    foreach ( $trans as $i => $t ) {
      $transactions[$i][0] = $t->ID;
      $transactions[$i][1] = get_post_time("F j, Y, g:i a",false,$t->ID);
      $transactions[$i][2] = get_post_meta($t->ID,'transactionID',true);
      $transactions[$i][3] = get_post_meta($t->ID,'transactionInvoiceNumber',true);
      $transactions[$i][4] = get_post_meta($t->ID,'billingFirstName',true);
      $transactions[$i][5] = get_post_meta($t->ID,'billingLastName',true);
      $transactions[$i][6] = get_post_meta($t->ID,'billingEmail',true);
      $transactions[$i][7] = get_post_meta($t->ID,'billingCompany',true);
      $transactions[$i][8] = get_post_meta($t->ID,'billingPhoneNumber',true);
      $transactions[$i][9] = get_post_meta($t->ID,'billingAddress',true);
      $transactions[$i][10] = get_post_meta($t->ID,'billingCity',true);
      $transactions[$i][11] = get_post_meta($t->ID,'billingState',true);
      $transactions[$i][12] = get_post_meta($t->ID,'billingZip',true);
      $transactions[$i][13] = get_post_meta($t->ID,'billingCountry',true);
      $transactions[$i][14] = get_post_meta($t->ID,'shippingFirstName',true);
      $transactions[$i][15] = get_post_meta($t->ID,'shippingLastName',true);
      $transactions[$i][16] = get_post_meta($t->ID,'shippingCompany',true);
      $transactions[$i][17] = get_post_meta($t->ID,'shippingAddress',true);
      $transactions[$i][18] = get_post_meta($t->ID,'shippingCity',true);
      $transactions[$i][19] = get_post_meta($t->ID,'shippingState',true);
      $transactions[$i][20] = get_post_meta($t->ID,'shippingZip',true);
      $transactions[$i][21] = get_post_meta($t->ID,'shippingCountry',true);
      $transactions[$i][22] = str_replace('XXXX', '', get_post_meta($t->ID,'ccLastFour',true));
      $transactions[$i][23] = get_post_meta($t->ID,'status',true);
      $transactions[$i][24] = get_post_meta($t->ID,'transactionType',true);
      $transactions[$i][25] = get_post_meta($t->ID,'transactionAmount',true);
    }

    echo json_encode($transactions);

    die;
  }
}
add_action('admin_init','wpat_jqdt_trans_loader');

?>
