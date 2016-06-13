<?php
require 'config.php';
require 'functions.php';
header('Content-Type: text/plain');
if(isset($_GET['sensor']) && isset($_GET['value']) && isset($_GET['signature'])) {
	$link = connect_to_database($dbhost, $dbuser, $dbpass, $dbname);
	$challenge = get_last_id($link, $_GET['sensor']);
	$sensor = $_GET['sensor'];
	$value = $_GET['value'];
	
	$checksum = sha1($challenge . $value . $sensor . $sensorsecret);
	
	if( $checksum == $_GET['signature']) {
		write_measurement($link, $_GET['sensor'], $_GET['value']);
		echo "Measurement stored!";
	} else {
		echo "Wrong signature!";
	}
	
}
?>