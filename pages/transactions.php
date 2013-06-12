<?php
$transactions = get_posts(
  array(
    'posts_per_page'  => -1,
    'post_type'       => 'auth-transactions',
    'post_status'     => 'publish'
  )
);
?>

<style type="text/css">
  .wpat-transactions {
  }
  .wpat-transactions .filters h4 {
    padding: 0;
    margin: 0;
  }
  .wpat-transactions tr.hover {
    background-color: white;
  }
  .wpat-transactions tr.expanded {
    display: none;
  }
  .wpat-transactions span.expand {
    margin-left: 2px;
  }
  .wpat-transactions span.expand, .wpat-transactions span.expand-all {
    font-weight: bold;
    padding: 1px 3px;
  }
  .wpat-transactions span.expand:hover, .wpat-transactions span.expand-all:hover {
    cursor: pointer;
    background-color: red;
    color: white;
  }
</style>

<div class="wrap wpat-transactions">
  
  <div class="title">
    <h2>Transactions</h2>
  </div>

  <br/><br/>

  <table class="widefat">
    <thead>
      <tr>
        <th><span class="expand-all">+</span></th>
        <th>Date</th>
        <th>Post ID</th>
        <th>Transaction ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Card</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th><span class="expand-all">+</span></th>
        <th>Date</th>
        <th>Post ID</th>
        <th>Transaction ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Email</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Card</th>
      </tr>
    </tfoot>
    <tbody>
      <?php foreach ( $transactions as $t ) : ?>
      <tr id="t-<?php echo $t->ID; ?>" class="primary">
        <td><span class="expand">+</span></td>
        <td><?php echo get_post_meta($t->ID,'transactionDate',true); ?></td>
        <td><?php echo $t->ID; ?></td>
        <td><?php echo get_post_meta($t->ID,'transactionID',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingFirstName',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingLastName',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'billingEmail',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'transactionType',true); ?></td>
        <td><?php echo get_post_meta($t->ID,'transactionAmount',true); ?></td>
        <td><?php echo str_replace('XXXX', '*', get_post_meta($t->ID,'ccLastFour',true)); ?></td>
      </tr>
      <tr id="te-<?php echo $t->ID; ?>" class="expanded">
        <td class="expanded" colspan="10">
          <p>
            <strong>Billing</strong><br>
            Phone Number: <?php echo get_post_meta($t->ID,'billingPhoneNumber',true); ?><br>
            Company: <?php echo get_post_meta($t->ID,'billingCompany',true); ?><br>
            Address: <?php echo get_post_meta($t->ID,'billingAddress',true); ?><br>
            City: <?php echo get_post_meta($t->ID,'billingCity',true); ?><br>
            State: <?php echo get_post_meta($t->ID,'billingState',true); ?><br>
            Zip: <?php echo get_post_meta($t->ID,'billingZip',true); ?><br>
            Country: <?php echo get_post_meta($t->ID,'billingCountry',true); ?><br>
            <strong>Shipping</strong><br>
            First Name: <?php echo get_post_meta($t->ID,'shippingFirstName',true); ?><br>
            Last Name: <?php echo get_post_meta($t->ID,'shippingLastName',true); ?><br>
            Company: <?php echo get_post_meta($t->ID,'shippingCompany',true); ?><br>
            Address: <?php echo get_post_meta($t->ID,'shippingAddress',true); ?><br>
            City: <?php echo get_post_meta($t->ID,'shippingCity',true); ?><br>
            State: <?php echo get_post_meta($t->ID,'shippingState',true); ?><br>
            Zip: <?php echo get_post_meta($t->ID,'shippingZip',true); ?><br>
            Country: <?php echo get_post_meta($t->ID,'shippingCountry',true); ?><br>
            <strong>Transaction</strong><br>
            Invoice #: <?php echo get_post_meta($t->ID,'transactionInvoiceNumber',true); ?><br>
            Status: <?php echo get_post_meta($t->ID,'status',true); ?>
          </p>
        </td>
      </tr>
      <?php endforeach; ?>

    </tbody>
  </table>

  <br/><br/>

</div>

<script type="text/javascript">

  var wpat_transactions = {
    el: {
      hovers: jQuery('.wpat-transactions table tbody tr.primary'),
      expand: jQuery('.wpat-transactions span.expand'),
      expand_all: jQuery('.wpat-transactions span.expand-all')
    },
    init_expansion: function() {
      this.el.expand.on('click', function() {
        wpat_transactions.el.expand_all.unbind('click').removeClass('expand-all');
        var t = jQuery(this).parents('tr').attr('id'),
            te = t.replace('t-','te-');
        if ( jQuery('#'+te).hasClass('expanded') == true ) {
          jQuery(this).html('-');
          jQuery('#'+te).removeClass('expanded');
          return false;
        }
        jQuery(this).html('+');
        jQuery('#'+te).addClass('expanded');
        return false;
      });
      this.el.expand_all.on('click', function() {
        wpat_transactions.el.expand.click();
      });
    }
  };

  jQuery(document).ready(function($) {
    wpat_transactions.init_expansion();
  });

</script>
