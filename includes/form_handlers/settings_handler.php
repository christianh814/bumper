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
?>
