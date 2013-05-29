<?php
/*

	Plugin Name: Authorize.net Subscription Management	
	Description: Create & Manage Authorize.net Subscriptions & Payments	
	Version: 0.0.0

*/

/*
Requires
*/
require_once('authorize/authorizeClasses.php');
require_once('classes/billing.php');
require_once('classes/billing_update.php');
require_once('classes/sbd.php');

//add_action('admin_init', 'disableDashboard');
function disableDashboard() {
  if (!current_user_can('manage_options') && $_SERVER['DOING_AJAX'] != '/wp-admin/admin-ajax.php') {
  wp_redirect(home_url()); exit;
  }
}

/*
	Create required pages in backend
*/
register_activation_hook( __FILE__, 'registerPages' );

function registerPages(){

$thankYouPageID = get_option('thankYouPageID');
$silentReturnPostID = get_option('silentReturnPostID');

if ( empty($thankYouPageID) ){
	$thankyou = array(
	  'post_title'    => 'Thank You',
	  'comment_status' => 'closed',
	  'ping_status' => 'closed',
	  'post_status'   => 'publish',
	  'post_type' => 'page',
	  'post_author'   => 1,
	  'post_category' => array(0)
	);
}


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
}

// Insert the post into the database
$thankYouPageID = wp_insert_post( $thankyou );
$silentReturnPostID = wp_insert_post( $silentReturn );

update_option('thankYouPageID', $thankYouPageID);
update_option('silentReturnPostID', $silentReturnPostID);

}

/*
	Custom Template for displaying products/order form etc.
*/
add_filter('single_template', 'pageTemplates');

function pageTemplates($single) {
	global $wp_query, $post;
	$dir = ABSPATH . 'wp-content/plugins/WordPress-Authorize.net-ARB-Tools/authorize';
	
	$silentReturnPostID = get_option('silentReturnPostID');

	if ( is_single( $silentReturnPostID ) ) {
		return $dir . '/sbd.php';
	}	
}


/*
	Add Settings Menu
*/
function authorizeMenu() {
	add_submenu_page( 'options-general.php', 'Authorize.net Settings', 'Authorize.net Settings', 'manage_options', 'auth-settings', 'authSettings' );
}
add_action('admin_menu', 'authorizeMenu');

function authTrans(){
	include 'pages/transactions.php';
}

function authSubs(){
	include 'pages/subscriptions.php';
}

function authSettings(){
	include 'pages/settings.php';
}


/*
Metabox for services posts
*/

// Hook into WordPress
add_action( 'admin_init', 'addAuthMetaBox' );
add_action( 'save_post', 'saveAuthMetaBox' );

/**
 * Add meta box
 */
function addAuthMetaBox() {
	add_meta_box( 'auth-custom-metabox', __( 'Service Options &amp; Settings' ), 'authCustomMetaBox', 'auth-services', 'side', 'core' );
}

/**
 * Display the metabox
 */
function authCustomMetaBox() {
	global $post;
	$amount = get_post_meta( $post->ID, 'Amount', true );
	$description = get_post_meta( $post->ID, 'Description', true );
	$billingType = get_post_meta( $post->ID, 'BillingType', true );
	$billingUnitSelect = get_post_meta( $post->ID, 'BillingUnit', true );
	$days = get_post_meta( $post->ID, 'Days', true );
	$months = get_post_meta( $post->ID, 'Months', true );
	$occurrences = get_post_meta( $post->ID, 'Occurrences', true );
	$enableTrial = get_post_meta( $post->ID, 'EnableTrial', true );
	$trialOccurrences = get_post_meta( $post->ID, 'TrialOccurrences', true );
	$trialAmount = get_post_meta( $post->ID, 'TrialAmount', true );
	
?>
<script>
jQuery(document).ready(function($) {

//Hide by default

$('#recurringOptions').hide();
$('#recurringDays').hide();
$('#recurringMonths').hide();
$('#trialSettings').hide();


var billingType = $('#billingTypeSelect').val();
var billingUnit = $('#billingUnitSelect').val();
var trialSelect = $('#enableTrial').val();

doTypeSelect(billingType);
doUnitSelect(billingUnit);
doTrialSelect(trialSelect);

//catch on change events

	$('#billingTypeSelect').change(function(){
		var type = $(this).val();
		var unit = $('#billingUnitSelect').val();		
			doTypeSelect(type);
			doUnitSelect(unit);
	});

	$('#billingUnitSelect').change(function(){
		var type = $(this).val();
		var trial = $('#enableTrial').val();
			doUnitSelect(type);
			doTrialSelect(trial);
	});
	
	$('#enableTrial').change(function(){
		var type = $(this).val();		
		doTrialSelect(type);
	});

	
	function doTrialSelect(type){
			if (type == 'no'){
				$('#trialSettings').hide();
			}else{
				$('#trialSettings').show();
			}
	}
		

	function doUnitSelect(type){
			if (type == 'days'){
				$('#recurringMonths').hide();
				$('#recurringDays').show();			
			}else{
				$('#recurringMonths').show();
				$('#recurringDays').hide();
			}
	}


	function doTypeSelect(type){
			if (type == 'once'){
				$('#recurringOptions').hide();
				$('#recurringDays').hide();
				$('#recurringMonths').hide();
			}else{
				$('#recurringOptions').show();
				$('#recurringDays').hide();
				$('#recurringMonths').hide();			
			}
	}
	
});

</script>

		<input type="hidden" name="serivceForm" value="serviceForm">
		<p><label for="amount"><b>Amount:</b><br />
		$<input type="text" name="amount" value="<?php echo $amount; ?>"><br/>
		What will this service or product cost?
		</label></p>
		
		<p><label for="description"><b>Short Description:</b><br />
		<textarea name="description" cols="40" rows="3"><?php echo $description; ?></textarea><br/>
		This is the short description that appears in Authorize.net for this transaction.
		</label></p>		
	
		<p><label for="type"><b>Billing Type:</b><br />
		<select id="billingTypeSelect" name="type">
		<option <?php if ($billingType == 'once') echo 'selected="selected"'; ?> value="once">One Time Billing</option>
		<option <?php if ($billingType == 'recurring') echo 'selected="selected"'; ?> value="recurring">Recurring Billing</option>
		</select><br />
		Will this be a one time billing or recurring billing?
		</label></p>
	
<div id="recurringOptions">
		<p><label for="unit"><b>Billing Unit:</b><br />
		<select id="billingUnitSelect" name="unit">
		<option <?php if ($billingUnitSelect == 'days') echo 'selected="selected"'; ?> value="days">Days</option>
		<option <?php if ($billingUnitSelect == 'months') echo 'selected="selected"'; ?> value="months">Months</option>
		</select><br />
		The unit of time, in association with the Interval Length, between each billing occurrence.
		</label></p>
		
<div id="recurringDays">
			<p><label for="days"><b>Day Interval:</b><br />
			<input type="text" name="days" value="<?php echo $days ?>"><br />
			How many days will pass before billing occurs again. Any number between 7 and 365.
			</label></p>
</div>

<div id="recurringMonths">
			<p><label for="months"><b>Month Interval:</b><br />
			<input type="text" name="months" value="<?php echo $months ?>"><br />
			How many months will pass before billing occurs again. Any number between 1 and 12.
			</label></p>
</div>			
		
		<p><label for="occurrences"><b>Total Occurrences:</b><br />
		<input type="text" name="occurrences" value="<?php echo $occurrences ?>"><br />
		How many times will billing occur before the subscription/service expires?.<br/><small>Set this to 9999 to create a never ending subscription</small>
		</label></p>	
		
		<p><label for="trial"><b>Enable Trial Period:</b><br />
		<select id="enableTrial" name="trial">
		<option <?php if ($enableTrial == 'no') echo 'selected="selected"'; ?> value="no">No</option>
		<option <?php if ($enableTrial == 'yes') echo 'selected="selected"'; ?> value="yes">Yes</option>
		</select><br />
		If you enable Trial periods. You can setup special pricing or a free trial period based on total occurences.
		</label></p>
		
<div id="trialSettings">
			<p><label for="trialOccurrences"><b>Trial Occurrences:</b><br />
			<input type="text" name="trialOccurrences" value="<?php echo $trialOccurrences ?>"><br />
			How many times out of the Total Occurrences will the trial pricing take effect?
			</label></p>
			
			<p><label for="trialAmount"><b>Trial Amount:</b><br />
			<input type="text" name="trialAmount" value="<?php echo $trialAmount ?>"><br />
			How much will you charge per trial occurrence. A free trial can be setup by simply entering the amount as 0.00 dollars.
			</label></p>			
</div>

</div>
<?php
}

/**
 * Process the custom metabox fields
 */
function saveAuthMetaBox( $post_id ) {
	global $post;	

	if( $_POST['serivceForm'] ) {
		update_post_meta( $post->ID, 'Amount', $_POST['amount'] );
		update_post_meta( $post->ID, 'Description', $_POST['description'] );
		update_post_meta( $post->ID, 'BillingType', $_POST['type'] );
		update_post_meta( $post->ID, 'BillingUnit', $_POST['unit'] );
		update_post_meta( $post->ID, 'Days', $_POST['days'] );
		update_post_meta( $post->ID, 'Months', $_POST['months'] );
		update_post_meta( $post->ID, 'Occurrences', $_POST['occurrences'] );
		update_post_meta( $post->ID, 'EnableTrial', $_POST['trial'] );
		update_post_meta( $post->ID, 'TrialOccurrences', $_POST['trialOccurrences'] );
		update_post_meta( $post->ID, 'TrialAmount', $_POST['trialAmount'] );			
	}
}

//end metaboxes

/*
Modify custom post type for better fit to plugin
*/
add_filter( 'enter_title_here', 'authDefaultTitle' );

function authDefaultTitle( $title ){
     $screen = get_current_screen();
 
     if  ( 'auth-services' == $screen->post_type ) {
          $title = 'Enter Product or Service Name Here';
     } 
     return $title;
}


/*
Register post type for services
*/
add_action('init', 'authRegisterPosts');
add_action('delete_post', 'authRegisterPosts');

function authRegisterPosts(){

$testMode = get_option('apiTestMode');

if ($testMode == 'on'){
	$display = true;
}else{
	$display = false;
}

			register_post_type ( 'auth-processors',
				array( 
				'label' => 'Processors',
				'public' => $display,
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

/*
			register_post_type ( 'auth-services',
				array( 
				'label' => 'Products',
				'public' => true,
				'show_ui' => true,
				'menu_icon' => '',
				'show_in_admin' => true,
				'show_in_nav_menus' => false,
				'rewrite' => true,
				'supports' => array(
								'title',
								'editor',
								'custom-fields'
								)
				)
			);	
*/
			
			
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


?>