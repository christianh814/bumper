<html>
	<head>
		<title></title>
		<!-- Sytlesheets -->
		<link rel="stylesheet" type="text/css" href="assets/css/style.css"></link>
	</head>
	<body>
	<style type="text/css">
		* {
			font-family: Arial, Helvetica, Sans-serif;
		}
		body {
			background-color: #fff;
		}
		form {
			position: absolute;
			top: 0;
		}
	</style>
	<?php
		require_once("config/config.php");
		require_once("includes/classes/User.php");
		require_once("includes/classes/Post.php");
		require_once("includes/classes/Notification.php");
		//
		if (isset($_SESSION['username'])) {
			$user_logged_in = $_SESSION['username'];
			$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE user_name = '{$user_logged_in}' ");
			$user = mysqli_fetch_array($user_details_query);
		} else {
			header("Location: register.php");
		}
		//
		if (isset($_GET['post_id'])) {
			$post_id = $_GET['post_id'];
		}
		//
		$get_likes_query = "SELECT likes, added_by FROM posts WHERE id = '{$post_id}' ";
		$get_likes = mysqli_query($con, $get_likes_query);
		$row = mysqli_fetch_array($get_likes);

		$total_likes = $row['likes'];
		$user_liked = $row['added_by'];

		$user_details = "SELECT * FROM users WHERE user_name = '{$user_liked}' ";
		$user_details_query = mysqli_query($con, $user_details);
		$row = mysqli_fetch_array($user_details_query);
		$total_user_likes = $row['num_likes'];

		// Like Button
		if (isset($_POST['like_button'])) {
			$total_likes++;
			$total_likes_send = "UPDATE posts SET likes = '{$total_likes}' WHERE id = '{$post_id}' ";
			$query = mysqli_query($con, $total_likes_send);
			//
			$total_user_likes++;
			$update_user_likes = "UPDATE users SET num_likes = '{$total_user_likes}' WHERE user_name = '{$user_liked}' ";
			$user_likes = mysqli_query($con, $update_user_likes);
			//
			$insert_user_query = "INSERT INTO likes (user_name, post_id) VALUES ('{$user_logged_in}', '{$post_id}') ";
			$insert_user = mysqli_query($con, $insert_user_query);
			// NOTIFY USER HERE//
			if ($user_liked !== $user_logged_in) {
				$notificaton = new Notification($con, $user_logged_in);
				$notificaton->insertNotification($post_id, $user_liked, "like");
			}
		}

		// Unlinke Button
		if (isset($_POST['unlike_button'])) {
			$total_likes--;
			$total_likes_send = "UPDATE posts SET likes = '{$total_likes}' WHERE id = '{$post_id}' ";
			$query = mysqli_query($con, $total_likes_send);
			//
			$total_user_likes--;
			$update_user_likes = "UPDATE users SET num_likes = '{$total_user_likes}' WHERE user_name = '{$user_liked}' ";
			$user_likes = mysqli_query($con, $update_user_likes);
			//
			$insert_user_query = "DELETE FROM likes WHERE user_name = '{$user_logged_in}' AND post_id = '{$post_id}' ";
			$insert_user = mysqli_query($con, $insert_user_query);
		}

		// Check for previous likes
		$check_likes_query = "SELECT * FROM likes WHERE user_name = '{$user_logged_in}' AND post_id = '{$post_id}'";
		$check_query = mysqli_query($con, $check_likes_query);
		$num_rows = mysqli_num_rows($check_query);
		if ($num_rows > 0) {
			echo "<form action='like.php?post_id={$post_id}' method='POST'>
				<input type='submit' class='comment_like' name='unlike_button' value='Unbump'></input>	
				<div class='like_value'>
					{$total_likes} Bumps
				</div>
				</form>
			";
		} else {
			echo "<form action='like.php?post_id={$post_id}' method='POST'>
				<input type='submit' class='comment_like' name='like_button' value='Bump'></input>	
				<div class='like_value'>
					{$total_likes} Bumps
				</div>
				</form>
			";
		}
	?>
	</body>
</html>
