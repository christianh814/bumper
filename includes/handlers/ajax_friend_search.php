<?php
require_once("../../config/config.php");
require_once("../classes/User.php");
//require_once("../classes/Post.php");
$query = $_POST['query'];
$user_logged_in = $_POST['user_logged_in'];

$names = explode(" ", $query);

if (strpos($query, "_") !== false) {
	$sql = "SELECT * FROM users WHERE user_name LIKE '{$query}%' AND user_closed = 'no' LIMIT 8 ";
	$users_returnd = mysqli_query($con, $sql);
} else if (count($names) == 2) {
	$sql = "SELECT * FROM users WHERE (first_name LIKE '%{$names[0]}%' AND last_name LIKE '%{$names[1]}%') AND user_closed = 'no' LIMIT 8 ";
	$users_returnd = mysqli_query($con, $sql);
} else {
	$sql = "SELECT * FROM users WHERE (first_name LIKE '%{$names[0]}%' OR last_name LIKE '%{$names[0]}%') AND user_closed = 'no' LIMIT 8 ";
	$users_returnd = mysqli_query($con, $sql);
}
//
if ($query !== "") {
	while ($row = mysqli_fetch_array($users_returnd)) {
		$user = new User($con, $user_logged_in);
		
		if ($row['user_name'] !== $user_logged_in) {
			$mutual_friends = $user->getMutualFriends($row['user_name']) . " friends in common";
		} else {
			$mutual_friends = "";
		}

		if ($user->isFriend($row['user_name'])) {
			echo "<div class='resultDisplay'>";

			echo "<a href='messages.php?u={$row['user_name']}' style='color:#000;'>";

			echo "<div class='liveSearchProfilePic'>";
			echo "<img src='{$row['profile_pic']}'></img>";
			echo "</div>";

			echo "<div class='liveSearchText'>" . $row['first_name'] . " " . $row['last_name'];
			echo "<p style='margin:0;'>{$row['user_name']}</p>";
			echo "<p id='grey'>{$mutual_friends}</p>";
			echo "</div>";

			echo "</a>";

			echo "</div>";
		}
	}
}
?>
