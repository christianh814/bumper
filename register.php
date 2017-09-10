<?php
require_once("config/config.php");
require_once("includes/form_handlers/login_handler.php");
require_once("includes/form_handlers/register_handler.php");
?>
<html>
	<head>
		<title>Welcome to Bumper</title>
		<link rel="stylesheet" type="text/css" href="assets/css/register_style.css"></link>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="assets/js/register.js"></script>
	</head>
	<body>
	<?php
		if (isset($_POST['register_button'])) {
			echo '
				<script>
					$(document).ready(function() {
						$("#first").hide();
						$("#second").show();
					});
				</script>
			';
		}
	?>
		<div class="wrapper">
			<div class="login_box">
				<div class="login_header">
					<h1>Bumper</h1>
					Login or Signup below
				</div>
				<div id="first">
					<form action="register.php" method="post">
						<input type="email" name="log_email" placeholder="Email Address">
						<br>
						<input type="password" name="log_password" placeholder="Password">
						<br>
						<input type="submit" name="login_button" value="Login">
						<br>
						<a href="#" id="signup" class="signup">Need an account?	Register here!</a>
					</form>
				</div>
				<div id="second">
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
						<a href="#" id="signin" class="signin">Have an account?	Signin here!</a>
					</form>
				</div>
				<?php
					// Display info we pushed into an array
					if (!empty($error_arry)) {
						echo "<center>";
               					echo "<h4>You have the following errors</h4>";
                				echo "<ul>";
                				foreach($error_arry as $key => $value) {
                        				echo "<li>{$value}</li>";
                				}
                				echo "</ul>";
						echo "</center>";
					} elseif (!empty($info_arry)) {
						echo "<center>";
                				foreach($info_arry as $key => $value) {
                        				echo "{$value}";
                				}
						echo "</center>";
					} elseif (!empty($login_error_arry)) {
						echo "<center>";
                				foreach($login_error_arry as $key => $value) {
                        				echo "{$value}";
                				}
						echo "</center>";
					}
				?>
			<div>
		<div>
	</body>
</html>
