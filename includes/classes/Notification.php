<?php
class Notification {
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($con, $user);
	}
	//

	public function getUnreadNumber() {
		$user_logged_in = $this->user_obj->getUsername();
		$sql = "SELECT * FROM notifications WHERE viewed = 'no' AND user_to = '{$user_logged_in}' ";
		$query = mysqli_query($this->con, $sql);
		return mysqli_num_rows($query);
	}

	public function insertNotification($post_id, $user_to, $type) {
		$user_logged_in = $this->user_obj->getUsername();
		$user_logged_in_name = $this->user_obj->getFirstAndLastName();

		$date_time = date("Y-m-d H:i:s");

		switch ($type) {
			case 'comment':
				$message = $user_logged_in_name . " commented on your post";
			break;

			case 'like':
				$message = $user_logged_in_name . " liked your post";
			break;

			case 'profile_post':
				$message = $user_logged_in_name . " posted on your profile";
			break;

			case 'comment_non_owner':
				$message = $user_logged_in_name . " commented on a post you commented on";
			break;

			case 'profile_comment':
				$message = $user_logged_in_name . " commented on your profile post";
			break;

		}

		$link = "post.php?id=" . $post_id;

		$sql = "INSERT INTO notifications (user_to, user_from, message, link, datetime, opend, viewed) ";
		$sql .= "VALUES ('{$user_to}', '{$user_logged_in}', '{$message}', '{$link}', '{$date_time}', 'no', 'no') ";
		$insert_query = mysqli_query($this->con, $sql);
	}
}
?>
