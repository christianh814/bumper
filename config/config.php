<?php
ob_start();
session_start();
$timezone = date_default_timezone_set('America/Los_Angeles');
$con = mysqli_connect(getenv("MYSQL_SERVICE_HOST"), getenv("MYSQL_USERNAME"), getenv("MYSQL_PASSWORD"), getenv("MYSQL_DATABASE"));
if (mysqli_connect_errno()) {
	echo "Failed to connect: " . mysqli_connect_errno();
}
?>
