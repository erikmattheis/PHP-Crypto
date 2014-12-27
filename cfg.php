<?php
$db_database = '[your MySQL database]';
$db_host ='localhost';
$db_user = '[your user]';
$db_pass = '[your pass]';

$link = mysqli_connect($db_host, $db_user, $db_pass, $db_database);

if(mysqli_connect_errno()) {
	echo "DB Connection Failed<br>";
	exit();
}

date_default_timezone_set('America/New_York');

$website = 'bter';
$bot_version = 0.5;

?>