<?php
require 'config.php';
require 'functions.php';
header('Content-Type: text/plain');
if(isset($_GET['sensor'])) {
	$link = connect_to_database($dbhost, $dbuser, $dbpass, $dbname);
	echo get_last_id($link, $_GET['sensor']);
}
?>