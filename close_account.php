<?php
require_once("includes/header.php");
//
if (isset($_POST['cancel'])) {
	header("Location: settings.php");
}
if (isset($_POST['close_account'])) {
	$sql = "UPDATE users SET user_closed = 'yes' WHERE user_name = '{$user_logged_in}' ";
	$close_query = mysqli_query($con, $sql);
	session_destroy();
	header("Location: register.php");
}
?>
		<!-- site Content -->
		<div class="main_column column">
			<h4>Close Account</h4>
			Are you sure you want to close your account?<br><br>
			Closing your account will hide your profile and all your activity from other users.<br><br>
			You can re-open your account by simply logging in.<br><br>
			<form action="close_account.php" method="POST">
				<input class="deep_blue settings_submit" type="submit" name="close_account" id="close_account" value="Yes! Close it!"></input>
				<input class="danger settings_submit" type="submit" name="cancel" id="update_details" value="No way!"></input>
			</form>
		</div>
		<!--./ site Content -->
</div> <!-- end div wrapper in header -->
<?php require_once("includes/footer.php");?>
