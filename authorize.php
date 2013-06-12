<?php
/*
Plugin Name: WordPress Authorize.net ARB Tools
Author: TechStudio
Version: 1.0.1
*/

// Version
$wpat_version = '1.0.1';
update_option('wpat_version',$wpat_version);

require_once('authorize/authorizeClasses.php');
require_once('classes/billing.php');
require_once('classes/billing_update.php');
require_once('classes/sbd.php');

// WP_Cron
function doCancelSuspended(){
  $subscriptionsArray = array(
    'post_type' => 'auth-subscriptions',
    'posts_per_page' => -1,
    'meta_query' => array(
      array(
        'key' => 'subscriptionStatus',
        'value' => 'suspended',
        'compare' => 'LIKE'
      )   
    )
  );
  $posts = get_posts($subscriptionsArray);
  foreach ($posts as $p){
    $subscriptionPostID = $p->ID;
    $subscriptionStatus = get_post_meta($subscriptionPostID, 'subscriptionStatus', true);
    $subscriptionLastBillingDate = get_post_meta($subscriptionPostID, 'subscriptionLastBillingDate', true);
    $timeStamp = strtotime($subscriptionLastBillingDate);
    if ($timeStamp <= strtotime('-7 days')){
       $billing = new billingUpdate($subscriptionPostID);
       $billing->cancelSubscription('System Cancelled');
       echo $billing->response;
    }   
  }
}
add_action( 'cancelSuspended', 'doCancelSuspended' );
if ( ! wp_next_scheduled( 'cancelSuspended' ) ) {
  wp_schedule_event( time(), 'daily', 'cancelSuspended' );
}

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
function wpat_settings(){
  include 'pages/settings.php';
}
function wpat_transactions(){
  include 'pages/transactions.php';
}

// Menus
function wpat_menu() {
  add_menu_page('WordPress Authorize.net ARB Tools - Settings', 'ARB', 'manage_options', 'wpat', 'wpat_settings');
  add_submenu_page('wpat', 'WordPress Authorize.net ARB Tools - Transactions', 'Transactions', 'manage_options', 'wpat-transactions', 'wpat_transactions');
}
add_action('admin_menu', 'wpat_menu');




/*
* Future stufff
function authSubs(){
	include 'pages/subscriptions.php';
}
*/

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

?>
