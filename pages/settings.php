<?php

if ( isset($_POST['saveAPISettings']) ) {
  update_option('apiLogin', $_POST['apiLogin']);
  update_option('apiKey', $_POST['apiKey']);
  update_option('apiEmail', $_POST['apiEmail']);
  update_option('apiTestMode', $_POST['apiTestMode']);
  update_option('apiHashEnable', $_POST['apiHashEnable']);
  update_option('apiHash', $_POST['apiHash']);
  update_option('vtUser', $_POST['vtUser']);
  update_option('cutOffDay', $_POST['cutOffDay']);
  update_option('startDay', $_POST['startDay']);
  $resp = 'Your settings have been updated.';
}

$settings = new billing();

?>

<style type="text/css">
  .wpat-settings-form form li {
    min-height: 27px;
  }
  .wpat-settings-form form label {
    float: left;
    width: 210px;
    line-height: 27px;
  }
  .wpat-settings-form form input[type="checkbox"] {
    margin-top: 5px;
  }
</style>

<div class="wrap">
  <div class="title">
    <div id="icon-options-general" class="icon32"></div>
    <h2>WordPress Authorize.net ARB Tools - Settings</h2>
  </div>

  <p>Version: <?php echo get_option('wpat_version'); ?></p>

  <?php if ( $resp ) : ?>
  <div class="updated">
    <p><strong><?php echo $resp ?></strong></p>
  </div>
  <?php endif; ?>

  <div class="wpat-settings-form">
<?php
  $apiHashEnable = $settings->apiHashEnable == 'on' ? 'checked="checked"' : '';
  $apiTestMode = $settings->apiTestMode == 'on' ? 'checked="checked"' : '';
?>

    <form method="POST">

      <h3>API Settings</h3>
      <ul>
        <li><label for="apiLogin">API Login ID: </label>
        <input id="apiLogin" maxlength="45" size="10" name="apiLogin" value="<?php echo $settings->apiLogin; ?>" /></li>    
        <li><label for="apiKey">API Transaction Key: </label>
        <input id="apiKey" maxlength="45" size="25" name="apiKey" value="<?php echo $settings->apiKey; ?>" /></li>
        <li><label for="apiHashEnable">Enable Return MD5 Hashing: </label>
        <input id="apiHashEnable" name="apiHashEnable" type="checkbox" <?php echo $apiHashEnable; ?> /></li>
        <li><label for="apiHash">API Hash Key: </label>
        <input id="apiHash" maxlength="45" size="25" name="apiHash" value="<?php echo $settings->hash; ?>" /></li>
        <li><label for="apiEmail">API Admin Alert Email Address(es): </label>
        <input id="apiEmail" maxlength="45" size="25" name="apiEmail" value="<?php echo $settings->apiEmail; ?>" />
        <small>For multiple addresses use a comma-delimited list.</small></li>
        <li><label for="apiTestMode">Enable API Test Mode: </label>
        <input id="apiTestMode" name="apiTestMode" type="checkbox" <?php echo $apiTestMode; ?> /></li>
        <li><label for="vtUser">Virtual Terminal Username: </label>
        <input id="vtUser" maxlength="45" size="10" name="vtUser" value="<?php echo $settings->vtUser; ?>" /></li>
      </ul>

      <h3>Billing Settings</h3>
      <ul>
        <li>
          <label for="cutOffDay">Billing Cut Off Day: </label>
          <select id="cutOffDay" name="cutOffDay">
            <?php for ( $i=1; $i<=31; $i++ ) : ?>
            <option value="<?php echo $i; ?>" <?php if ( $settings->cutOffDay == $i ) echo "selected='$settings->cutOffDay'"; ?>><?php echo $i; ?></option>
            <?php endfor; ?>

          </select>
          <small>Subscriptions created after cut-off are preauthorized and queued for the following period.</small>
        </li>
        <li>
          <label for="startDay">Billing Day: </label>
          <select id="startDay" name="startDay">
            <?php for ( $i=1; $i<=31; $i++ ) : ?>
            <option value="<?php echo $i; ?>" <?php if ( $settings->startDay == $i ) echo "selected='$settings->startDay'"; ?>><?php echo $i; ?></option>
            <?php endfor; ?>

          </select>
        </li>
      </ul>
      
      <input type="submit" name="saveAPISettings" value="Save Settings" class="button-primary">
    
    </form>

  </div><!-- /wpat-settings-form -->

</div><!-- /wrap -->
