<?php
class Message {
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($con, $user);
	}
	//

	public function getMostRecentUser() {
		$user_logged_in = $this->user_obj->getUsername();
		$sql  = "SELECT user_to, user_from FROM messages WHERE user_to = '{$user_logged_in}' ";
		$sql .= "OR user_from = '{$user_logged_in}' ORDER BY id DESC LIMIT 1 ";
		$query = mysqli_query($this->con, $sql);
		if(mysqli_num_rows($query) == 0) {
			return false;
		}
		$row = mysqli_fetch_array($query);
		$user_to = $row['user_to'];
		$user_from = $row['user_from'];

		if ($user_to !== $user_logged_in) {
			return $user_to;
		} else {
			return $user_from;
		}
	}

	public function sendMessage($user_to, $body, $date) {
		if (!$body !== "") {
			$user_logged_in = $this->user_obj->getUsername();
			$sql = "INSERT INTO messages (user_to, user_from, body, date, opend, viewed, deleted) ";
			$sql .= "VALUES ('{$user_to}', '{$user_logged_in}', '{$body}', '{$date}', 'no', 'no', 'no') ";
			$query = mysqli_query($this->con, $sql) or trigger_error("Query Failed! SQL: $sql - Error: ".mysqli_error($this->con), E_USER_ERROR);
		}
	}

	public function getMessages($other_user) {
		$user_logged_in = $this->user_obj->getUsername();
		$data = "";

		$sql = "UPDATE messages SET opend = 'yes' WHERE user_to = '{$user_logged_in}' AND user_from = '{$other_user}' ";
		$query = mysqli_query($this->con, $sql);

		$get_msg_sql = "SELECT * FROM messages WHERE (user_to = '{$user_logged_in}' AND user_from = '{$other_user}') ";
		$get_msg_sql .= "OR (user_from = '{$user_logged_in}' AND user_to = '{$other_user}') ";
		$get_messages_query = mysqli_query($this->con, $get_msg_sql);

		while ($row = mysqli_fetch_array($get_messages_query)) {
			$user_to = $row['user_to'];
			$user_from = $row['user_from'];
			$body = $row['body'];
			$div_top = ($user_to == $user_logged_in) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
			$data = $data . $div_top . $body . "</div><br><br>";
		}
		return $data;
	}

	public function getLatestMsg($user_logged_in, $user2) {
		$details_array = array();

		$sql = "SELECT body, user_to, date FROM messages WHERE (user_to = '{$user_logged_in}' AND user_from = '{$user2}') ";
		$sql .= "OR (user_to = '{$user2}' AND user_from = '{$user_logged_in}') ";
		$sql .= "ORDER BY id DESC LIMIT 1";
		$query = mysqli_query($this->con, $sql);
		$row = mysqli_fetch_array($query);
		$sent_by = ($row['user_to'] == $user_logged_in) ? "<i>They Said:</i> " : "<i>You said:</i> ";
		//Timeframe
		$date_time_now = date("Y-m-d H:i:s");
		$start_date = new DateTime($row['date']);
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
		} //end Timeframe
		array_push($details_array, $sent_by);
		array_push($details_array, $row['body']);
		array_push($details_array, $time_message);
		return $details_array;
	}

	public function getConvos() {
		$user_logged_in = $this->user_obj->getUsername();
		$return_string = "";
		$convos = array();

		$sql = "SELECT user_to, user_from FROM messages WHERE user_to = '{$user_logged_in}' OR user_from = '{$user_logged_in}' ORDER BY id DESC";
		$query = mysqli_query($this->con, $sql);

		while ($row = mysqli_fetch_array($query)) {
			$user_to_push = ($row['user_to'] !== $user_logged_in) ? $row['user_to'] : $row['user_from'];
			if (!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push);
			}
		}

		foreach ($convos as $username) {
			$user_found_obj = new User($this->con, $username);
			$latest_msg_details = $this->getLatestMsg($user_logged_in, $username);

			$dots = (strlen($latest_msg_details[1]) >= 12) ? "..." : "" ;
			$split = str_split($latest_msg_details[1], 12);
			$split = $split[0] . $dots;

			$return_string .= "<a href='messages.php?u={$username}'>";
			$return_string .= "<div class='user_found_messages'>";
			$return_string .= "<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius:5px; margin-right:5px;'>" . "</img>"; 
			$return_string .= $user_found_obj->getFirstAndLastName() . "<span class='timestamp_smaller' id='grey'>" . $latest_msg_details[2];
			$return_string .= "</span>";
			$return_string .= "<p id='grey' style='margin:0;'>" . $latest_msg_details[0] . $split . "</p></div></a>";
		}
		return $return_string;
	}

	public function getConvosDropdown($data, $limit) {
		$page = $data['page'];
		$user_logged_in = $this->user_obj->getUsername();
		$return_string = "";
		$convos = array();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$set_viewed_query = mysqli_query($this->con, "UPDATE messages SET viewed = 'yes' WHERE user_to = '{$user_logged_in}' ");

		$sql = "SELECT user_to, user_from FROM messages WHERE user_to = '{$user_logged_in}' OR user_from = '{$user_logged_in}' ORDER BY id DESC";
		$query = mysqli_query($this->con, $sql);

		while ($row = mysqli_fetch_array($query)) {
			$user_to_push = ($row['user_to'] !== $user_logged_in) ? $row['user_to'] : $row['user_from'];
			if (!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push);
			}
		}

		$num_iterations = 0; //Num of msg checked
		$count = 1; // Number of msg posted

		foreach ($convos as $username) {
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
			$unread_q = "SELECT opend FROM messages WHERE user_to = '{$user_logged_in}' AND user_from = '{$username}' ORDER BY id DESC";
			$is_uread_query = mysqli_query($this->con, $unread_q);
			$row = mysqli_fetch_array($is_uread_query);
			$style = ($row['opend'] == 'no') ? "background-color: #ddedff;" : "";
			//
			$user_found_obj = new User($this->con, $username);
			$latest_msg_details = $this->getLatestMsg($user_logged_in, $username);

			$dots = (strlen($latest_msg_details[1]) >= 12) ? "..." : "" ;
			$split = str_split($latest_msg_details[1], 12);
			$split = $split[0] . $dots;

			$return_string .= "<a href='messages.php?u={$username}'>";
			$return_string .= "<div class='user_found_messages' style='" . $style . "'>";
			$return_string .= "<img src='" . $user_found_obj->getProfilePic() . "' style='border-radius:5px; margin-right:5px;'>" . "</img>"; 
			$return_string .= $user_found_obj->getFirstAndLastName() . "<span class='timestamp_smaller' id='grey'>" . $latest_msg_details[2];
			$return_string .= "</span>";
			$return_string .= "<p id='grey' style='margin:0;'>" . $latest_msg_details[0] . $split . "</p></div></a>";
		}
		if ($count > $limit) {
			$return_string .= "<input type='hidden' class='nextPageDropDownData' value='" . ($page + 1). "'>";
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='false'>";
		} else {
			$return_string .= "<input type='hidden' class='noMoreDropdownData' value='true'><p style='text-align: center;'>No more messages</p>";
		}
		return $return_string;
	}
}
?>
