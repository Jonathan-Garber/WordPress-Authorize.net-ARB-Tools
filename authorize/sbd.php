<?php
$requestType = $_SERVER['REQUEST_METHOD'];
	if ( $requestType == "POST" ){
		$sbd = new sbd($_POST);
	}else{
		echo 'Nothing to see here... Move along';
	}
?>