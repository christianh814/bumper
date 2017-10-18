<?php require_once("includes/header.php");?>
<?php require_once("includes/form_handlers/settings_handler.php");?>
		<!-- site Content -->
		<div class="main_column column">
			<h4>Account Settings</h4>
			<?php
				echo "<img src='" . $user['profile_pic'] . "' id='small_profile_pics'>";
			?>
			<br>
			<a href="upload.php">Upload new profile picture</a><br><br><br>
			Modify the values and click 'Update Details'
			<?php
				$user_data_query = mysqli_query($con, "SELECT first_name, last_name, email FROM users WHERE user_name = '{$user_logged_in}' ");
				$row = mysqli_fetch_array($user_data_query);
				$first_name = $row['first_name'];
				$last_name = $row['last_name'];
				$email = $row['email'];
			?>
			<form action="settings.php" method="POST">
				First Name: <input type="text" name="first_name" value="<?php echo $first_name; ?>"><br>
				Last Name: <input type="text" name="last_name" value="<?php echo $last_name; ?>"><br>
				E-Email: <input type="text" name="email" value="<?php echo $email; ?>"><br>
				<input type="submit" name="update_details" id="save_details" value="Update Details"><br>
				<?php echo $message; ?>
			</form>
			<h4>Change Password</h4>
			<form action="settings.php" method="POST">
				Old Password: <input type="password" name="old_password" value="<?php echo $user['password']; ?>"><br>
				New Password: <input type="password" name="new_password_1" value=""><br>
				New Password Again: <input type="password" name="new_password_2" value=""><br>
				<input type="submit" name="update_password" id="save_details" value="Update Password"><br>
			</form>

			<h4>Close Account</h4>
			<form action="settings.php">
				<input type="submit" name="close_account" id="close_account" value="Close Account">
			</form>
		</div>
		<!--./ site Content -->
</div> <!-- end div wrapper in header -->
<?php require_once("includes/footer.php");?>
