<?php
if (isset($_POST['update_details'])) {
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];
	//
	$email_check = mysqli_query($con, "SELECT * FROM users WHERE email = '{$email}' ");
	$row = mysqli_fetch_array($email_check);
	$matched_user = $row['user_name'];
	if ($matched_user == "" || $matched_user == $user_logged_in) {
		$message = "Details Updated <br><br>";
		$sql = "UPDATE users SET first_name = '{$first_name}', last_name = '{$last_name}', email = '{$email}' WHERE user_name = '{$user_logged_in}' ";
		$query = mysqli_query($con, $sql);
	} else {
		$message = "Email is already in use<br><br>";
	}
} else {
	$message = "";
}
// ********************************************************************************************************************** //

if (isset($_POST['update_password'])) {
	$old_password = strip_tags($_POST['old_password']);
	$new_password_1 = strip_tags($_POST['new_password_1']);
	$new_password_2 = strip_tags($_POST['new_password_2']);

	$sql = "SELECT * FROM users WHERE user_name = '{$user_logged_in}' ";
	$password_query = mysqli_query($con, $sql);
	$row = mysqli_fetch_array($password_query);
	$db_password = $row['password'];
	//
	if ($new_password_1 == $new_password_2) {
		if (strlen($new_password_1) <= 4) {
			$password_message = "Password must be more than 4 characters!<br><br>";
		} else {
			$enc_pass = password_hash($new_password_1, PASSWORD_BCRYPT, array('cost' => 12));
			$sql = "UPDATE users SET password = '{$enc_pass}' WHERE user_name = '{$user_logged_in}' ";
			$password_update_query = mysqli_query($con, $sql);
			$password_message = "Password has been updated!<br><br>";
		}
	} else {
		$password_message = "Passwords do not match!<br><br>";
	}
} else {
		$password_message = "";
}

// ********************************************************************************************************************** //

if(isset($_POST['close_account'])) {
	header("Location: close_account.php");
}

?>
