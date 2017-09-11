<?php
require_once("config/config.php");
require_once("includes/classes/User.php");
require_once("includes/classes/Post.php");
//
if (isset($_SESSION['username'])) {
	$user_logged_in = $_SESSION['username'];
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE user_name = '{$user_logged_in}' ");
	$user = mysqli_fetch_array($user_details_query);
} else {
	header("Location: register.php");
}
?>
<html>
	<head>
	<title>Welcome to Bumper</title>
	<!-- Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<script src="assets/js/bootstrap.js"></script>

	<!-- Sytlesheets -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"></link>
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.css"></link>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css"></link>

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"></link>
	<link rel="icon" href="favicon.ico" type="image/x-icon"></link>
	</head>
<body>
	<div class="top_bar">
		<div class="logo">
			<a href="index.php">Bumper</a>
		</div>
		<nav>
			<a href="<?php echo $user_logged_in ?>"><?php echo $user['first_name'] ?></a>
			<a href="index.php"><i class="fa fa-home fa-lg"></i></a>
			<a href="#"><i class="fa fa-envelope fa-lg"></i></a>
			<a href="#"><i class="fa fa-bell-o fa-lg"></i></a>
			<a href="#"><i class="fa fa-users fa-lg"></i></a>
			<a href="#"><i class="fa fa-cog fa-lg"></i></a>
			<a href="includes/handlers/logout.php"><i class="fa fa-sign-out fa-lg"></i></a>
		</nav>
	</div>
<div class="wrapper">
