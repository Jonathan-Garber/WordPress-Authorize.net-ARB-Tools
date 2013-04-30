<?php
/*
	Save Settings before calling class that does display so the new settings are caught when displaying
*/

if (isset($_POST['saveAPISettings'])){
	update_option('apiLogin', $_POST['apiLogin']);
	update_option('apiKey', $_POST['apiKey']);
	update_option('apiTestMode', $_POST['apiTestMode']);
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