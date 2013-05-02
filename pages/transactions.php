<?php
$transArray = array(
'post_type' => 'auth-transactions'
);

$transactions = get_posts($transArray);
?>
<h2>Transactions</h2>
<table class="wp-list-table widefat fixed posts">
	<thead>
		<tr>
			<th>Transaction Type</th>
			<th>Transaction Response</th>
			<th>Transaction ID</th>
			<th>Customer Name</th>
			<th>Amount</th>
			<th>Options</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th>Transaction Type</th>
			<th>Transaction Response</th>
			<th>Transaction ID</th>
			<th>Customer Name</th>
			<th>Amount</th>
			<th>Options</th>
		</tr>
	</tfoot>
	<tbody>	
<?php
foreach ($transactions as $t){

$postID = $t->ID;
$customerID = $t->post_author;
$transID = $t->post_title;

$billing = new billing($postID);
$customerData = $billing->transactionCustomerData();

//billing data
$billingFirstName = $customerData['billingFirstName'];
$billingLastName = $customerData['billingLastName'];
$billingEmail = $customerData['billingEmail'];
$billingCompany = $customerData['billingCompany'];
$billingPhoneNumber = $customerData['billingPhoneNumber'];
$billingAddress = $customerData['billingAddress'];
$billingCity = $customerData['billingCity'];
$billingState = $customerData['billingState'];
$billingZip = $customerData['billingZip'];
$billingCountry = $customerData['billingCountry'];

$billingCreditCardLastFour = $customerData['billingCreditCardLastFour'];


//shipping data
$shippingFirstName = $customerData['shippingFirstName'];
$shippingLastName = $customerData['shippingLastName'];
$shippingCompany = $customerData['shippingCompany'];
$shippingAddress = $customerData['shippingAddress'];
$shippingCity = $customerData['shippingCity'];
$shippingState = $customerData['shippingState'];
$shippingZip = $customerData['shippingZip'];
$shippingCountry = $customerData['shippingCountry'];


$transactionAmount = $customerData['transactionAmount'];
$transactionType = $customerData['transactionType'];
$transactionDate = $customerData['transactionDate'];
$transactionID = $customerData['transactionID'];
$transactionInvoiceNumber = $customerData['transactionInvoiceNumber'];
$status = $customerData['status'];


?>

			<tr>
				<td><?php echo $transactionType ?></td>
				<td><?php echo $status ?></td>				
				<td><?php echo $transactionID; ?></td>
				<td><?php echo $billingFirstName.' '.$billingLastName; ?></td>
				<td><?php echo $transactionAmount; ?></td>
				<td>Form - buttons</td>
			</tr>	
<?php
}
?>
	</tbody>
</table>