<?php
if (isset($_POST['x_response_code'])){
	$sbd = new sbd($_POST);
}else{
wp_redirect('/');
}
?>