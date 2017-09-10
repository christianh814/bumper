<?php
require_once("config/config.php");
//
if (isset($_SESSION['username'])) {
	$user_logged_in = $_SESSION['username'];
} else {
	header("Location: register.php");
}
?>
<html>
	<head>
	<title>Welcome to Bumper</title>
	</head>
<body>
