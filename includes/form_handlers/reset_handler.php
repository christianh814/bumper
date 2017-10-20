<?php
$fname = ""; //First Name
$lname = ""; //Last Name
$em = "";    //Email
$em2 = "";    //Email2
$pw = ""; //password
$pw2 = ""; //password2
$date = ""; //signup date
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
		} else {
			array_push($error_arry, "Error sending Email, try again<br>");
		}
		//
	}
}
//
//
if (isset($_POST['register_button'])) {
	$fname = strip_tags($_POST['reg_fname']);
	$fname = str_replace(' ', '', $fname);
	$fname = ucfirst(strtolower($fname));
	$_SESSION['reg_fname'] = $fname;
	//
	$lname = strip_tags($_POST['reg_lname']);
	$lname = str_replace(' ', '', $lname);
	$lname = ucfirst(strtolower($lname));
	$_SESSION['reg_lname'] = $lname;
	//
	$em = strip_tags($_POST['reg_email']);
	$em = str_replace(' ', '', $em);
	$_SESSION['reg_email'] = $em;
	//
	$em2 = strip_tags($_POST['reg_email2']);
	$em2 = str_replace(' ', '', $em2);
	$_SESSION['reg_email2'] = $em2;
	//
	$password = strip_tags($_POST['reg_password']);
	$password2 = strip_tags($_POST['reg_password2']);
	//
	$date = date("Y-m-d");
	// ** VAILIDATION ** //
	
	// Email
	if ($em == $em2){
		if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
			// check if email already exists
			$e_check = mysqli_query($con, "SELECT email FROM users WHERE email = '{$em}' ");
			$num_rows = mysqli_num_rows($e_check);
			if ($num_rows > 0) {
				array_push($error_arry, "Email already in use<br>");
			}
		} else {
			array_push($error_arry, "Please enter valid email<br>");
		}
	} else {
		array_push($error_arry, "Emails don't match<br>");
	}
	// User F/L-name
	if (strlen($fname) > 25 || strlen($fname) < 2) {
		array_push($error_arry, "Your First Name must be between 2 and 25 characters<br>");
	}
	if (strlen($lname) > 25 || strlen($lname) < 2) {
		array_push($error_arry, "Your Last Name must be between 2 and 25 characters<br>");
	}

	//Password
	if ($password != $password2) {
		array_push($error_arry, "Passwords must match<br>");
	} else {
		if (preg_match('[^A-Za-z0-9]', $password)) {
			array_push($error_arry, "Password can only be letters and numbers<br>");
		}
	}
	if (strlen($password) > 30 || strlen($password) < 8) {
		array_push($error_arry, "Your Password must be between 8 and 30 characters<br>");
	}
	//
	if (empty($error_arry)) {
		$password = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
		$username = strtolower($fname . "_" . $lname);
		$check_username_query = mysqli_query($con, "SELECT user_name FROM users WHERE user_name = '{$username}' ");
		$i = 0;
		while (mysqli_num_rows($check_username_query) != 0) {
			$i++;
			$username = $username . "_" . $i;
			$check_username_query = mysqli_query($con, "SELECT user_name FROM users WHERE user_name = '{$username}' ");
		}
		$profile_pic = "assets/images/profile_pics/defaults/head_deep_blue.png";
		$query = "INSERT INTO users (first_name, last_name, user_name, email, password, signup_date, profile_pic, num_posts, num_likes, user_closed, friend_arrary) ";
		$query .= "VALUES ('{$fname}', '{$lname}', '{$username}', '{$em}', '{$password}', '{$date}', '{$profile_pic}', '0', '0', 'no', ',') ";
		$send_query = mysqli_query($con, $query) or trigger_error("Query Failed! SQL: $query - Error: ".mysqli_error($con), E_USER_ERROR);
		if ($send_query) {
			array_push($info_arry, "<h4>You're all set to login!</h4>");
		}
	}
}
?>
