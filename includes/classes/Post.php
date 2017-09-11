<?php
class Post {
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($con, $user);
	}

	public function submitPost($body, $user_to) {
		$body = strip_tags($body);
		$body = mysqli_real_escape_string($this->con, $body);
		$check_empty = preg_replace('/\s+/', '', $body);

		if ($check_empty !== "") {
			$date_added = date("Y-m-d H:i:s");
			$added_by = $this->user_obj->getUsername();
			if ($user_to == $added_by) {
				$user_to = "none";
			}
			$add_post_query = "INSERT INTO posts (body, added_by, user_to, date_added, user_closed, deleted, likes) ";
			$add_post_query .= "VALUES('{$body}', '{$added_by}', '{$user_to}', '{$date_added}', 'no', 'no', '0' ) ";
			$query = mysqli_query($this->con, $add_post_query);
			$returned_id = mysqli_insert_id($this->con);
			
			// Insert Notifaction

			$num_posts = $this->user_obj->getNumPosts();
			$num_posts++;
			$update_query = mysqli_query($this->con, "UPDATE users SET num_posts = '{$num_posts}' WHERE user_name = '{$added_by}' ");
		}
	}
}
?>
