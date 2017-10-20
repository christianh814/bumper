<?php
class User {
	private $user;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE user_name  = '{$user}' ");
		$this->user = mysqli_fetch_array($user_details_query);
	}

	public function getUsername() {
		return $this->user['user_name'];
	}

	public function sendEmail($from, $from_fullname, $subject, $to, $to_fullname, $body) {
		$from = new SendGrid\Email($from_fullname, $from);    
		$subject = $subject;
		$to = new SendGrid\Email($to_fullname, $to);    
		$content = new SendGrid\Content("text/plain", $body);    
		
		$mail = new SendGrid\Mail($from, $subject, $to, $content);    
		
		$apiKey = getenv('SENDGRID_API_KEY');    
		$sg = new \SendGrid($apiKey);    
		
		$response = $sg->client->mail()->send()->post($mail);    
		if ($response->statusCode() == 202) {    
			return true;
		} else {    
			return false; 
		} 
	}

	public function getNumPosts() {
		$username = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT num_posts FROM users WHERE user_name = '{$username}'");
		$row = mysqli_fetch_array($query);
		return $row['num_posts'];
	}

	public function getNumFriendReq() {
		$username = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT * FROM friend_req WHERE user_to = '{$username}'");
		return mysqli_num_rows($query);
	}

	public function getFirstAndLastName() {
		$username = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT first_name, last_name FROM users WHERE user_name = '{$username}' ");
		$row = mysqli_fetch_array($query);
		return $row['first_name'] . " " . $row['last_name'];
	}

	public function isClosed() {
		$username = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT user_closed FROM users WHERE user_name = '{$username}'");
		$row = mysqli_fetch_array($query);
		if ($row['user_closed'] == "yes") {
			return true;
		} else {
			return false;
		}
	}

	public function isFriend($username_to_check) {
		$username_comma = "," . $username_to_check . ",";
		if (strstr($this->user['friend_arrary'], $username_comma) || $username_to_check == $this->user['user_name']) {
			return true;
		} else {
			return false;
		}
	}

	public function getProfilePic() {
		$username = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT profile_pic, last_name FROM users WHERE user_name = '{$username}' ");
		$row = mysqli_fetch_array($query);
		return $row['profile_pic'];
	}

	public function getFriendArray() {
		$username = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT friend_arrary FROM users WHERE user_name = '{$username}' ");
		$row = mysqli_fetch_array($query);
		return $row['friend_arrary'];
	}

	public function didRecvReq($user_from) {
		$user_to = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT * FROM friend_req WHERE user_to = '{$user_to}' AND user_from = '{$user_from}' ");
		if (mysqli_num_rows($query) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function didSndReq($user_to) {
		$user_from = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT * FROM friend_req WHERE user_to = '{$user_to}' AND user_from = '{$user_from}' ");
		if (mysqli_num_rows($query) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	public function removeFriend($user_to_remove) {
		$logged_in_user = $this->user['user_name'];
		$query = mysqli_query($this->con, "SELECT friend_arrary FROM users WHERE user_name = '{$user_to_remove}' ");
		$row = mysqli_fetch_array($query);
		$friend_array_username = $row['friend_arrary'];
		//
		$new_friend_array = str_replace($user_to_remove . ",", "", $this->user['friend_arrary']);
		$remove_friend = mysqli_query($this->con, "UPDATE users SET friend_arrary = '{$new_friend_array}' WHERE user_name = '{$logged_in_user}' ");

		//
		$new_friend_array = str_replace($this->user['user_name'] . ",", "", $friend_array_username);
		$remove_friend = mysqli_query($this->con, "UPDATE users SET friend_arrary = '{$new_friend_array}' WHERE user_name = '{$user_to_remove}' ");
	}

	public function sendFriendReq($user_to) {
		$user_from = $this->user['user_name'];
		$query = mysqli_query($this->con, "INSERT INTO friend_req (user_to, user_from) VALUES ('{$user_to}', '{$user_from}') ");
	}

	public function getMutualFriends($user_to_check) {
		$mutual_friends = 0;
		$user_array = $this->user['friend_arrary'];
		$user_array_explode = explode(",", $user_array);
		//
		$query = mysqli_query($this->con, "SELECT friend_arrary FROM users WHERE user_name = '{$user_to_check}' ");
		$row = mysqli_fetch_array($query);
		$user_to_check_array = $row['friend_arrary'];
		$user_to_check_array_explode = explode(",", $user_to_check_array);
		//
		foreach($user_array_explode as $i) {
			foreach ($user_to_check_array_explode as $j) {
				if ($i == $j && $i !== "") {
					$mutual_friends++;
				}
			}
		}
		return $mutual_friends;
	}

}
?>
