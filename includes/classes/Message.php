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
		$sql = "SELECT user_to, user_from FROM messages WHERE user_to = '{$user_logged_in}' "
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
}
?>
