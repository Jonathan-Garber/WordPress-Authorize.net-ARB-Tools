<?php
if ( isset($_POST) ){
	$sbd = new sbd($_POST);
}else{
	echo 'nothing to see here - move along';
}
?>