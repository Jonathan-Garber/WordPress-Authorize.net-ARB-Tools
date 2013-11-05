<?php
$args = array(
  'posts_per_page'  => 50,
  'post_type'       => 'auth-subscriptions',
  'post_status'     => 'publish'
);
$subscriptions = get_posts($args);
?>

<div class="wrap wpat-wrap wpat-subscriptions">
  
  <div class="title">
    <h2>Subscriptions</h2>
  </div>

  <br/>

  <div class="aux-toolbar">
    <h5>Toggle Columns</h5>
    <div class="switches">
      <button id="column-<?php echo $i = 0; ?>" class="active">Account Login</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Post ID</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Date/Time</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Billing First Name</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Billing Last Name</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Billing Email</button>
      <button id="column-<?php echo $i = $i+1; ?>">Billing Company</button>
      <button id="column-<?php echo $i = $i+1; ?>">Billing Phone Number</button>
      <button id="column-<?php echo $i = $i+1; ?>">Billing Address</button>
      <button id="column-<?php echo $i = $i+1; ?>">Billing City</button>
      <button id="column-<?php echo $i = $i+1; ?>">Billing State</button>
      <button id="column-<?php echo $i = $i+1; ?>">Billing Zip</button>
      <button id="column-<?php echo $i = $i+1; ?>">Billing Country</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping First Name</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping Last Name</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping Company</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping Address</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping City</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping State</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping Zip</button>
      <button id="column-<?php echo $i = $i+1; ?>">Shipping Country</button>
      <button id="column-<?php echo $i = $i+1; ?>">Card Last Four</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Subscription ID</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Start Date</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Reference ID</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Invoice Number</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Subscription Name</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Interval</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Unit</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Occurrences</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Trial Occurrences</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Amount</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Trial Amount</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Last Billing Date</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Next Billing Date</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Payment Number</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Subscription Status</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Canceled By</button>
      <button id="column-<?php echo $i = $i+1; ?>">Subscription Canceled Date</button>
    </div>
  </div>

  <br/>

  <span class="loading-message">Loading...</span>

  <table class="wpat-subscriptions not-loaded data">
    <thead>
      <tr>
        <th>Post ID</th>
        <th>Account Login</th>
        <th>Date/Time</th>
        <th>Billing First Name</th>
        <th>Billing Last Name</th>
        <th>Billing Email</th>
        <th>Billing Company</th>
        <th>Billing Phone Number</th>
        <th>Billing Address</th>
        <th>Billing City</th>
        <th>Billing State</th>
        <th>Billing Zip</th>
        <th>Billing Country</th>
        <th>Shipping First Name</th>
        <th>Shipping Last Name</th>
        <th>Shipping Company</th>
        <th>Shipping Address</th>
        <th>Shipping City</th>
        <th>Shipping State</th>
        <th>Shipping Zip</th>
        <th>Shipping Country</th>
        <th>Card Last Four</th>
        <th>Subscription ID</th>
        <th>Subscription Start Date</th>
        <th>Subscription Reference ID</th>
        <th>Subscription Invoice Number</th>
        <th>Subscription Name</th>
        <th>Subscription Interval</th>
        <th>Subscription Unit</th>
        <th>Subscription Occurrences</th>
        <th>Subscription Trial Occurrences</th>
        <th>Subscription Amount</th>
        <th>Subscription Trial Amount</th>
        <th>Subscription Last Billing Date</th>
        <th>Subscription Next Billing Date</th>
        <th>Subscription Payment Number</th>
        <th>Subscription Status</th>
        <th>Cancelled By</th>
        <th>Cancelled Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $subscriptions as $t ) : $userData = get_userdata($t->post_author); ?>
      <tr id="transaction-<?php echo $t->ID; ?>">
        <td><?php echo $t->ID; ?></td>
        <td><a href="<?php bloginfo('wpurl') ?>/wp-admin/user-edit.php?user_id=<?php echo $userData->ID; ?>"><?php echo $userData->user_login; ?></a></td>
        <td><?php echo get_post_time("F j, Y, g:i a",false,$t->ID); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingFirstName',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingLastName',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingEmail',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingCompany',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingPhoneNumber',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingAddress',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingCity',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingState',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingZip',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingCountry',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingFirstName',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingLastName',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingCompany',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingAddress',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingCity',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingState',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingZip',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'shippingCountry',true); ?></td>
        <td><?php echo str_replace('XXXX', '', get_post_meta($t->ID,'ccLastFour',true)); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionID',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionStartDate',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionReferenceID',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionInvoiceNumber',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionName',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionInterval',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionUnit',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionOccurrences',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionTrialOccurrences',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionAmount',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionTrialAmount',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionLastBillingDate',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionNextBillingDate',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionPaymentNumber',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionStatus',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionCanceledBy',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'subscriptionCanceledDate',true); ?></td>
      </tr>
      <?php endforeach; ?>

    </tbody>
  </table>

  <br/><br/>

</div>
