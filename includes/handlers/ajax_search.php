<?php
require_once("../../config/config.php");
require_once("../classes/User.php");
require_once("../classes/Post.php");

$query = $_POST['query'];
$user_logged_in = $_POST['user_logged_in'];

// Split elements into an array using " " (i.e. space) as the "split on"
$names = explode(" ", $query);

//Check for underscore. Assume user is searching for usernames
if (strpos($query, '_') !== false) {
	$sql = "SELECT * FROM users WHERE user_name LIKE '{$query}%' AND user_closed = 'no' LIMIT 8 ";
	$users_returned_query = mysqli_query($con, $sql);
} else if (count($names) == 2) {
	// if they provided a space assume they are searching for fname and lname respectivley
	$sql = "SELECT * FROM users WHERE (first_name LIKE '{$names[0]}%' AND last_name LIKE '{$names[1]}%') AND user_closed = 'no' LIMIT 8 ";
	$users_returned_query = mysqli_query($con, $sql);
} else {
	// if they provided one; search for both fname or lname
	$sql = "SELECT * FROM users WHERE (first_name LIKE '{$names[0]}%' OR last_name LIKE '{$names[0]}%') AND user_closed = 'no' LIMIT 8 ";
	$users_returned_query = mysqli_query($con, $sql);
	echo mysqli_error($con);
}

if ($query != "") {
	while ($row = mysqli_fetch_array($users_returned_query)) {
		$user = new User($con, $user_logged_in);

		if ($row['user_name'] != $user_logged_in ) {
			$mutual_friends = $user->getMutualFriends($row['user_name']) . " friends in common";
		} else {
			$mutual_friends = "";
		}

		echo "<div class='resultDisplay'>
			<a href='profile.php?profile_username=" . $row['user_name'] . "' style='color: #1485bd'>
				<div class='liveSearchProfilePic'>
					<img src='" . $row['profile_pic'] . "'></img>
				</div>

				<div class='liveSearchText'>
					" . $row['first_name'] . " " . $row['last_name'] . "
					<p>" . $row['user_name'] . " </p>
					<p id='grey'>" . $mutual_friends . " </p>
				</div>
			</a>
			</div>";
	}
}
?>
