<?php
/*
	Template Name: Thank You
*/



if (!isset($_GET['subPostID'])){
wp_redirect('/');
}

//capture the post id of the newly created subscription
$subscriptionPostID = $_GET['subPostID'];

//open billing class and use its function to return an array of all the data related to this subscription
$billing = new billing($subscriptionPostID);
$subscriptionData = $billing->subscriptionCustomerData();

?>
<?php get_header(); ?>
<pre>
<?php print_r($subscriptionData); ?>
</pre>