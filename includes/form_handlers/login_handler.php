<?php
$login_error_arry = array();
if (isset($_POST['login_button'])) {
	$email = filter_var($_POST['log_email'], FILTER_SANITIZE_EMAIL);
	$_SESSION['log_email'] = $email;
	$password = filter_var($_POST['log_password']);
	$query = "SELECT * FROM users WHERE email = '{$email}' ";
	$select_user = mysqli_query($con, $query);
	if (!$select_user) {
		die("ERROR: " . mysqli_error($con));
	}
	while ($user = mysqli_fetch_array($select_user)) {
		$user_email = $user['email'];
		$user_enc_pass = $user['password'];
		$user_name = $user['user_name'];
		if (password_verify($password, $user_enc_pass)) {
			$user_closed_query = mysqli_query($con, "SELECT user_closed FROM users WHERE email = '{$user_email}' AND user_closed = 'yes' ");
			if (mysqli_num_rows($user_closed_query) == 1) {
				$reopen_account = mysqli_query($con, "UPDATE users SET user_closed = 'no' WHERE email = '{$user_email}' ");
			}
			$_SESSION['username'] = $user_name;
			header("Location: index.php");
		} else {
			array_push($login_error_arry, "<h4>Incorrect Email or Password</h4>");
		}
	}
}
?>
