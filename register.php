<?php
require_once("config/config.php");
require_once("includes/form_handlers/login_handler.php");
require_once("includes/form_handlers/register_handler.php");
?>
<html>
	<head>
		<title>Welcome to Bumper</title>
	</head>
	<body>
		<form action="register.php" method="post">
			<input type="email" name="log_email" placeholder="Email Address">
			<br>
			<input type="password" name="log_password" placeholder="Password">
			<br>
			<input type="submit" name="login_button" value="Login">
		</form>
		<form action="register.php" method="post">
			<input type="text" name="reg_fname" placeholder="First Name" required>
			<br>
			<input type="text" name="reg_lname" placeholder="Last Name" required>
			<br>
			<input type="email" name="reg_email" placeholder="Email" required>
			<br>
			<input type="email" name="reg_email2" placeholder="Confirm Email" required>
			<br>
			<input type="password" name="reg_password" placeholder="Password" required>
			<br>
			<input type="password" name="reg_password2" placeholder="Confirm Password" required>
			<br>
			<input type="submit" name="register_button" value="Register">
			<br>
		</form>
	</body>
</html>
