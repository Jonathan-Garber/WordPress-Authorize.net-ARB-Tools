<?php
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

?>
