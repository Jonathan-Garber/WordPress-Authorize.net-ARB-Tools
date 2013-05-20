<?php
/*
	Save Settings before calling class that does display so the new settings are caught when displaying
*/

if (isset($_POST['saveAPISettings'])){
	update_option('apiLogin', $_POST['apiLogin']);
	update_option('apiKey', $_POST['apiKey']);
	update_option('apiEmail', $_POST['apiEmail']);
	update_option('apiTestMode', $_POST['apiTestMode']);
	update_option('apiHash', $_POST['apiHash']);
	update_option('vtUser', $_POST['vtUser']);
	update_option('cutOffDay', $_POST['cutOffDay']);
	update_option('startDay', $_POST['startDay']);
	update_option('thankYouPageID', $_POST['thankYouPageID']);
	$resp = 'All settings have been updated';
}

/*
Get current saved settings for API/Gateway
*/
$settings = new billing();		
?>
<h2>Settings</h2>
<?php $settings->settingsForm(); ?>
<p><strong>
<?php if ($resp) echo $resp ?>
</strong></p>