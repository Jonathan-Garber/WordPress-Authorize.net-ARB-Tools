<?php
$transactions = get_posts(
  array(
    'author' => '-1',
    'posts_per_page'  => 50,
    'post_type'       => 'auth-transactions',
    'post_status'     => 'publish'
  )
);
?>

<div class="wrap wpat-wrap wpat-transactions">
  
  <div class="title">
    <h2>Transactions</h2>
  </div>

  <br/>

  <div class="aux-toolbar">
    <h5>Toggle Columns</h5>
    <div class="switches">
      <button id="column-<?php echo $i=0; ?>" class="active">Post ID</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Date/Time</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Transaction ID</button>
      <button id="column-<?php echo $i = $i+1; ?>">Invoice Number</button>
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
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Card Last Four</button>
      <button id="column-<?php echo $i = $i+1; ?>">Status</button>
      <button id="column-<?php echo $i = $i+1; ?>">Transaction Type</button>
      <button id="column-<?php echo $i = $i+1; ?>" class="active">Amount</button>
    </div>
  </div>

  <br/>

  <span class="loading-message">Loading...</span>

  <table class="wpat-transactions not-loaded data">
    <thead>
      <tr>
        <th>Post ID</th>
        <th>Date/Time</th>
        <th>Transaction ID</th>
        <th>Invoice Number</th>
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
        <th>Status</th>
        <th>Transaction Type</th>
        <th>Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $transactions as $t ) : ?>
      <tr id="transaction-<?php echo $t->ID; ?>">
        <td><?php echo $t->ID; ?></td>
        <td><?php echo get_post_time("F j, Y, g:i a",false,$t->ID); ?></td>
        <td><?php echo get_post_meta($t->ID,'transactionID',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'transactionInvoiceNumber',true); ?></td>
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
        <td><?php echo get_post_meta($t->ID,'status',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'transactionType',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'transactionAmount',true); ?></td>
      </tr>
      <?php endforeach; ?>

    </tbody>
  </table>

  <br/><br/>

</div>
