<?php
require_once("config/config.php");
require_once("includes/classes/User.php");
require_once("includes/sendgrid-php/sendgrid-php.php");
require_once("includes/form_handlers/reset_handler.php");
?>
<html>
	<head>
		<title>Welcome to Bumper</title>
		<link rel="stylesheet" type="text/css" href="assets/css/register_style.css"></link>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<script src="assets/js/register.js"></script>
	</head>
	<body>
		<div class="wrapper">
			<div class="login_box">
				<div class="login_header">
					<h1>Bumper</h1>
					Reset your Password below
				</div>

				<?php
					if(isset($_GET['reset_token']) && isset($_GET['email'])) {
						$reset_token = mysqli_real_escape_string($con, $_GET['reset_token']);
						$email_reset = mysqli_real_escape_string($con, $_GET['email']);

						$sql = "SELECT * FROM users WHERE email = '{$email_reset}' AND reset_token = '{$reset_token}' ";
						$reset_query = mysqli_query($con, $sql);

						if(mysqli_num_rows($reset_query) != 0) {
							?>
							<center><h4>Updating Password for <br><?php echo $email_reset ?></h4></center>
							<div id="fourth">
								<form action="reset.php" method="post">
									<input type="hidden" name="reset_this_email" value="<?php echo $email_reset; ?>">
									<br>
									<input type="password" name="reset_password_1" placeholder="Password" required>
									<br>
									<input type="password" name="reset_password_2" placeholder="Confirm Password" required>
									<br>
									<input type="submit" name="reset_password_button" value="Reset Password">
									<br>
									<a href="register.php" id="signin" class="signin">Have an account? Signin here!</a>
								</form>
							</div>
							<?php
						} else {
							echo "<center><h4>Invalid request</h4></center>";
						}
						//
					} else {
					?>
						<div id="third">
							<form action="reset.php" method="post">
								<input type="email" name="reset_email" placeholder="Email Address" required>
								<br>
								<input type="submit" name="reset_email_button" value="Reset Password">
								<br>
								<a href="register.php" id="signin" class="signin">Have an account?	Signin here!</a>
							</form>
						</div>
					<?php
					}
				?>
				<?php
					// Display info we pushed into an array
					if (!empty($error_arry)) {
						echo "<center>";
               					echo "<h4>The following errors were found:</h4>";
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
