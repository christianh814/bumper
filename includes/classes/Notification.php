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
				$message = $user_logged_in_name . " bumped your post";
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

	public function getNotifications($data, $limit) {
		$page = $data['page'];
		$user_logged_in = $this->user_obj->getUsername();
		$return_string = "";
	
		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}
	
		$set_viewed_query = mysqli_query($this->con, "UPDATE notifications SET viewed = 'yes' WHERE user_to = '{$user_logged_in}' ");
	
		$sql = "SELECT * FROM notifications WHERE user_to = '{$user_logged_in}' ORDER BY id DESC";
		$query = mysqli_query($this->con, $sql);

		if (mysqli_num_rows($query) == 0) {
			echo "You have no notifications";
			return;
		}
	
		$num_iterations = 0; //Num of msg checked
		$count = 1; // Number of msg posted
	
		while ($row = mysqli_fetch_array($query)) {
			//
			if ($num_iterations++ < $start) {
				continue;
			}
			if ($count > $limit) {
				break;
			} else {
				$count++;
			}
			//
			$user_from = $row['user_from'];
			$fromsql = "SELECT * FROM users WHERE user_name = '{$user_from}' ";
			$user_data_query = mysqli_query($this->con, $fromsql);
			$user_data = mysqli_fetch_array($user_data_query);
			//
			//Timeframe
			$date_time_now = date("Y-m-d H:i:s");
			$start_date = new DateTime($row['datetime']);
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

			// END - Timeframe
			//
			$opend = $row['opend'];
			$style = ($row['opend'] == 'no') ? "background-color: #ddedff;" : "";
			//
	
			$return_string .= "<a href='{$row['link']}'>";
			$return_string .= "<div class='resultDisplay resultDisplayNotification' style='{$style}'>";
			$return_string .= "<div class='notificationsProfilePic'>";
			$return_string .= "<img src='{$user_data['profile_pic']}'>";
			$return_string .= "</div>";
			$return_string .= "<p class='timestamp_smaller' id='grey'>{$time_message}</p> {$row['message']}";
			$return_string .= "</div>";
			$return_string .= "</a>";
		}
		if ($count > $limit) {
			$return_string .= "<input type='hidden' class='nextPageDropDownData' value='" . ($page + 1). "'>";
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='false'>";
		} else {
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>No more notifications</p>";
		}
		return $return_string;
	}
}
?>
