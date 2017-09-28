<html>
	<head>
		<title></title>
		<!-- Sytlesheets -->
		<link rel="stylesheet" type="text/css" href="assets/css/style.css"></link>
	</head>
	<body>
		<style type="text/css">
			* {
				font-size: 12px;
				font-family: Arial, Helvetica, Sans-serif;
				/* background-color: #fff; */
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
		?>
	</body>
	<script>
		function toggle() {
			var element  = document.getElementById("comment_section");
			if(element.style.display == "block")
				element.style.display = "none";
			else 
				element.style.display = "block";
		}
	</script>
	<?php
		if (isset($_GET['post_id'])) {
			$post_id = $_GET['post_id'];
		}
		$send_user_query = "SELECT added_by, user_to FROM posts WHERE id = '{$post_id}' ";
		$user_query = mysqli_query($con, $send_user_query);
		$row = mysqli_fetch_array($user_query);
		$posted_to = $row['added_by'];
		$user_to = $row['user_to'];
		//
		if (isset($_POST['postComment' . $post_id])) {
			$post_body = $_POST['post_body'];
			$post_body = mysqli_escape_string($con, $post_body);
			$date_time_now = date("Y-m-d H:i:s");
			$insert_post_query = "INSERT INTO comments(post_body, posted_by, posted_to, date_added, removed, post_id) ";
			$insert_post_query .= "VALUES('{$post_body}', '{$user_logged_in}', '{$posted_to}', '{$date_time_now}', 'no', '{$post_id}') ";
			$insert_post = mysqli_query($con, $insert_post_query);
			if ($posted_to !== $user_logged_in) {
				$notificaton = new Notification($con, $user_logged_in);
				$notificaton->insertNotification($post_id, $posted_to, "comment");
			}
			if ($user_to !== "none" && $user_to !== $user_logged_in) {
				$notificaton = new Notification($con, $user_logged_in);
				$notificaton->insertNotification($post_id, $user_to, "profile_comment");
			}

			$get_commenters = mysqli_query($con, "SELECT * FROM comments WHERE post_id = '{$post_id}' ");
			$notified_users = array();
			while ($row = mysqli_fetch_array($get_commenters)) {
				if(
				$row['posted_by'] !== $posted_to &&
				$row['posted_by'] !== $user_to && 
				$row['posted_by'] !== $user_logged_in &&
				!in_array($row['posted_by'], $notified_users )
				) {
					$notificaton = new Notification($con, $user_logged_in);
					$notificaton->insertNotification($post_id, $row['posted_by'], "comment_non_owner");
					array_push($notified_users, $row['posted_by']);
				}
			}
			echo "<p>Comment Posted!</p>";
		}
	?>
	<form action="comment_frame.php?post_id=<?php echo $post_id ?>" id="comment_form" name="postComment<?php echo $post_id ?>" method="POST">
		<textarea name="post_body"></textarea>
		<input type="submit" name="postComment<?php echo $post_id ?>" value="Post"></input>
	</form>
	<?php
		$get_comments_query = "SELECT * FROM comments WHERE post_id = '{$post_id}' ORDER BY id ASC ";
		$get_comments = mysqli_query($con, $get_comments_query);
		$count = mysqli_num_rows($get_comments);

		if ($count !== 0) {
			while ($comment = mysqli_fetch_array($get_comments)) {
				$comment_body = $comment['post_body'];
				$posted_by = $comment['posted_by'];
				$posted_to = $comment['posted_to'];
				$date_added = $comment['date_added'];
				$removed = $comment['removed'];
				$post_id = $comment['post_id'];
				//Timeframe
				$date_time_now = date("Y-m-d H:i:s");
				$start_date = new DateTime($date_added);
				$end_date = new DateTime($date_time_now);
				$interval = $start_date->diff($end_date);
				
				if ($interval->y >= 1) {
					if ($interval == 1) {
						$time_message = $interval->y . " year ago";
					} else {
						$time_message = $interval->y . " years ago";
					}
				} elseif ($interval->m >= 1) {
					if ($interval->d == 0) {
						$days = " ago";
					} elseif ($interval->d == 1){
						$days = $interval->d . " day ago";
					} else {
						$days = $interval->d . " days ago";
					}
					if ($interval->m == 1) {
							$time_message = $interval->m . " month " .  $days;
					} else {
							$time_message = $interval->m . " months " . $days;
					}
				} elseif ($interval->d >= 1) {
					if ($interval->d == 1){
						$time_message = "Yesterday";
					} else {
						$time_message = $interval->d . " days ago";
					}
				} elseif ($interval->h >= 1) {
					if ($interval->h == 1){
						$time_message = $interval->h . " hour ago";
					} else {
						$time_message = $interval->h . " hours ago";
					}
				} elseif ($interval->i >= 1) {
					if ($interval->i == 1){
						$time_message = $interval->i . " minute ago";
					} else {
						$time_message = $interval->i . " minutes ago";
					}
				} else {
					if ($interval->s < 30){
						$time_message = "Just now";
					} else {
						$time_message = $interval->s . " seconds ago";
					}
				}
				//end Timeframe
				$user_obj = new User($con, $posted_by);
				?>
				<!-- Breaking out of PHP...for now -->
				<div class="comment_section">
					<a href="profile.php?profile_username=<?php echo $posted_by?>" target="_parent">
						<img src="<?php echo $user_obj->getProfilePic(); ?>" title="<?php echo $posted_by?>" style="float:left;" height="30">
						</img>
					</a>
					<a href="profile.php?profile_username=<?php echo $posted_by?>" target="_parent">
						<b><?php echo $user_obj->getFirstAndLastName(); ?></b>
					</a>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $time_message . "<br>" . $comment_body?>
					<hr>
				</div>
				<!-- Back to PHP -->
				<?php

			}//end while
		} else {
			echo "<center><br><br>No Comments to Show!</center>";
		}
	?>
</html>
