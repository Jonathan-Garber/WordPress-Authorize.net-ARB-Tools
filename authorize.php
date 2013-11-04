<?php
/*
Plugin Name: WordPress Authorize.net ARB Tools
Author: TechStudio
Author URI: http://techstudio.co
Version: 1.0.9
Description: WordPress Authorize.net ARB Tools is a WordPress plugin designed to allow developers to build subscription based billing management sites using WordPress, a custom theme and this plugin.
*/

$plugins_url = plugins_url(false,__FILE__);

// Version
$wpat_version = '1.0.9';
$wpat_previous_version = get_option('wpat_version');
if ( $wpat_version != $wpat_previous_version ) {
  // Update routines go here
}
update_option('wpat_version',$wpat_version);

// Includes
include('authorize/authorizeClasses.php');
include('classes/billing.php');
include('classes/billing_update.php');
include('classes/sbd.php');
include('functions/functions.php');

// Styles
function wpat_styles() {
  if ( strpos($_GET['page'],'wpat') !== false ) {
    wp_enqueue_style('wpat-global-css', "/wp-content/plugins/WordPress-Authorize.net-ARB-Tools/assets/css/wpat.global.css", null, $wpat_version, 'screen');
    wp_enqueue_style('wpat-data-tables-css', "/wp-content/plugins/WordPress-Authorize.net-ARB-Tools/assets/css/jquery.dataTables.css", null, $wpat_version, 'screen');
    //wp_enqueue_style('wpat-data-tables-themeroller-css', "/wp-content/plugins/WordPress-Authorize.net-ARB-Tools/assets/css/jquery.dataTables_themeroller.css", null, $wpat_version, 'screen');
  }
}
add_action('admin_init','wpat_styles');

// Scripts
function wpat_scripts() {
  if ( strpos($_GET['page'],'wpat') !== false ) {
    wp_enqueue_script('wpat-jquery-data-tables-js', "/wp-content/plugins/WordPress-Authorize.net-ARB-Tools/assets/js/jquery.dataTables.min.js", 'jquery', $wpat_version, true);
    wp_enqueue_script('wpat-global-js', "/wp-content/plugins/WordPress-Authorize.net-ARB-Tools/assets/js/jquery.wpat.global.min.js", 'jquery', $wpat_version, true);
  }
}
add_action('admin_init','wpat_scripts');

// Register required pages
function registerPages() {
  $silentReturnPostID = get_option('silentReturnPostID');
  if ( empty($silentReturnPostID) ){
  	$silentReturn = array(
  	  'post_title'    => 'Silent Return',
  	  'comment_status' => 'closed',
  	  'ping_status' => 'closed',
  	  'post_status'   => 'publish',
  	  'post_type' => 'auth-processors',
  	  'post_author'   => 1,
  	  'post_category' => array(0)
  	);
    $silentReturnPostID = wp_insert_post( $silentReturn );
    update_option('silentReturnPostID', $silentReturnPostID);
  }
}
register_activation_hook(__FILE__, 'registerPages');

// Custom Template for displaying products/order form etc.
function pageTemplates($single) {
	global $wp_query, $post;
	$dir = ABSPATH . 'wp-content/plugins/WordPress-Authorize.net-ARB-Tools/authorize';
	
	$silentReturnPostID = get_option('silentReturnPostID');

	if ( is_single( $silentReturnPostID ) ) {
		return $dir . '/sbd.php';
	}	
}
add_filter('single_template', 'pageTemplates');

// Pages
function wpat_settings() {
  include 'pages/settings.php';
}
function wpat_transactions() {
  include 'pages/transactions.php';
}
function wpat_subscriptions() {
  include 'pages/subscriptions.php';
}

// Menus
function wpat_menu() {
  add_menu_page('WordPress Authorize.net ARB Tools - Settings', 'ARB', 'manage_options', 'wpat', 'wpat_settings');
  add_submenu_page('wpat', 'WordPress Authorize.net ARB Tools - Transactions', 'Transactions', 'manage_options', 'wpat-transactions', 'wpat_transactions');
  add_submenu_page('wpat', 'WordPress Authorize.net ARB Tools - Subscriptions', 'Subscriptions', 'manage_options', 'wpat-subscriptions', 'wpat_subscriptions');
}
add_action('admin_menu', 'wpat_menu');

// Register custom post types
function authRegisterPosts(){
  
  $display = get_option('apiTestMode') == 'on' ? true : false;

	register_post_type ( 'auth-processors',
		array( 
		'label' => 'Processors',
		'public' => TRUE,
		'show_ui' => $display,
		'menu_icon' => '',
		'show_in_admin' => $display,
		'show_in_nav_menus' => false,
		'rewrite' => true,
		'supports' => array(
						'title',
						'editor',
						'custom-fields'
						)
		)
	);
			
	register_post_type ( 'auth-transactions',
		array( 
		'label' => 'Transactions',
		'public' => $display,
		'show_ui' => $display,
		'menu_icon' => '',
		'show_in_admin' => $display,
		'show_in_nav_menus' => false,
		'rewrite' => true,
		'supports' => array(
						'title',
						'editor',
						'custom-fields',
						'author'
						)
		)
	);

	register_post_type ( 'auth-subscriptions',
		array( 
		'label' => 'Subscriptions',
		'public' => $display,
		'show_ui' => $display,
		'menu_icon' => '',
		'show_in_admin' => $display,
		'show_in_nav_menus' => false,
		'rewrite' => true,
		'supports' => array(
						'title',
						'editor',
						'custom-fields',
						'author'
						)
		)
	);			
			
	flush_rewrite_rules(false);
}
add_action('init', 'authRegisterPosts');
add_action('delete_post', 'authRegisterPosts');

// Set up a WP_Cron that cancels aged subscriptions
function doCancelSuspended(){
  $subscriptionsArray = array(
    'post_type' => 'auth-subscriptions',
    'posts_per_page' => -1,
    'meta_query' => array(
      array(
        'key' => 'subscriptionStatus',
        'value' => 'suspended',
        'compare' => '=='
      )   
    )
  );
  $posts = get_posts($subscriptionsArray);
  $successCount = 0;
  $failedCount = 0;
	foreach ($posts as $p){		
		$subscriptionPostID = $p->ID;
		$subscriptionID = get_post_meta($subscriptionPostID, 'subscriptionID', true);
		//$arbStatus = wpat_getARBSubscriptionStatus($subscriptionID);
		//removed if ARB Status statement that was from line 181 to 203
		
			$firstName = get_post_meta($subscriptionPostID, 'billingFirstName', true);
			$lastName = get_post_meta($subscriptionPostID, 'billingLastName', true);
			$email = get_post_meta($subscriptionPostID, 'billingEmail', true);
			
			$subscriptionStatus = get_post_meta($subscriptionPostID, 'subscriptionStatus', true);
			$subscriptionLastBillingDate = get_post_meta($subscriptionPostID, 'subscriptionLastBillingDate', true);	
			$timeStamp = strtotime($subscriptionLastBillingDate);

			if ($timeStamp <= strtotime('-7 days')){
				$subscriptionList .= "Subscription ID: ".$subscriptionID."\n";
				$subscriptionList .= "Name: ".$firstName." ".$lastName."\n";
				$subscriptionList .= "Email: ".$email."\n";
				
				$billing = new billingUpdate($subscriptionPostID);
				$billing->cancelSubscription('System Canceled');
				if ($billing->authResponse == 'canceled'){
					$successCount++;
					$subscriptionList .= "Response: Canceled\n\n";
				}else{
					$failedCount++;
					$subscriptionList .= "Response: FAILED TO CANCEL\n\n";
				}
			}
	}	
	$apiEmail = get_option('apiEmail');
	$subject = "Daily Subscription Cancelation Report ".date('m-d-Y H:m:s A');
	$message = "The following subscriptions have been automatically canceled due to their suspension status. Any subscription marked suspended for more then 7 days from the last billing date are automatically canceled.\n\nCanceled Successfully: ".$successCount."\n\nFailed to Cancel: ".$failedCount."\n\n".$subscriptionList;
	wp_mail($apiEmail,$subject,$message);
}

add_action( 'cancelSuspended', 'doCancelSuspended' );
if ( ! wp_next_scheduled( 'cancelSuspended' ) ) {
  wp_schedule_event( time(), 'daily', 'cancelSuspended' );
}

?>
