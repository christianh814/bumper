<?php
$error_arry = array(); //an array of errors
$info_arry = array(); //an array of errors
//
if (isset($_POST['reset_email_button'])) {
	$email = $_POST['reset_email'];
	$sql = "SELECT email, user_name FROM users WHERE email = '{$email}' ";
	$email_query = mysqli_query($con, $sql);
	$row = mysqli_fetch_array($email_query);
	$user_obj = new User($con, $row['user_name']);
	if (mysqli_num_rows($email_query) == 0) {
		array_push($error_arry, "Email not registerd<br>");
	} else {
		$token_length = 50;
		$reset_token = bin2hex(openssl_random_pseudo_bytes($token_length));
		$reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset.php?email=" . $email . "&reset_token=" . $reset_token;
		$body = "<h1>Password Reset</h1>\n<p>Your password reset link:&nbsp;<a href='{$reset_link}'>RESET</a></p>";
		$from = "do-not-reply@orpheus.social";
		$from_fullname = "Donot Reply";
		$subject = "Password Reset";
		$to = $email;
		$to_fullname = $user_obj->getFirstAndLastName();
		//

		if ($user_obj->sendEmail($from, $from_fullname, $subject, $to, $to_fullname, $body)) {
			array_push($info_arry, "<h4>Reset Link was sent!</h4>");
			$set_token_sql = "UPDATE users SET reset_token = '{$reset_token}' WHERE email = '{$to}' ";
			$set_token_query = mysqli_query($con, $set_token_sql);
		} else {
			array_push($error_arry, "Error sending Email, try again<br>");
		}
		//
	}
}
//
if (isset($_POST['reset_password_button'])) {
	$email = mysqli_real_escape_string($con, $_POST['reset_this_email']);
	$pw1 = mysqli_real_escape_string($con, $_POST['reset_password_1']);
	$pw2 = mysqli_real_escape_string($con, $_POST['reset_password_2']);
	if ($pw1 != $pw2) {
		array_push($error_arry, "Passwords do not match!<br>");
	} else if (strlen($pw1) < 6) {
		array_push($error_arry, "Password must be at least 6 characters!<br>");
	} else {
		$password = password_hash($pw1, PASSWORD_BCRYPT, array('cost' => 12));
		$sql = "UPDATE users SET password = '{$password}', reset_token = '' ";
		$password_reset_query = mysqli_query($con, $sql);
		if ($password_reset_query) {
			$_SESSION['pw_reset_msg'] = "Password sucessfully changed";
			header("Location: register.php");
		} else {
			array_push($error_arry, "Error updating password, please try again!<br>");
		}
	}
}
//
?>
