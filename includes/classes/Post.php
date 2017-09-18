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

	public function loadPostsProfile($data, $limit) {
		$page = $data['page'];
		$profile_user = $data['profile_username'];
		$user_logged_in = $this->user_obj->getUsername();
		if ($page == 1 ) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}
		$str = "";
		$select_post_qry = "SELECT * FROM posts WHERE deleted = 'no' ";
		$select_post_qry .= "AND ((added_by = '$profile_user' AND user_to = 'none') OR user_to = '{$profile_user}') ";
		$select_post_qry .= "ORDER BY id DESC";
		$data_query = mysqli_query($this->con, $select_post_qry);

		if (mysqli_num_rows($data_query) > 0) {
			$num_itterations = 0;
			$count = 1;
			while ($row = mysqli_fetch_array($data_query)) {
				$id 		= $row['id'];
				$body 		= $row['body'];
				$added_by 	= $row['added_by'];
				$user_to	= $row['user_to'];
				$date_added	= $row['date_added'];
				$user_closed	= $row['user_closed'];
				$deleted	= $row['deleted'];
				$likes		= $row['likes'];
				//
				//
				if  ($num_itterations++ < $start) {
					continue;
				}
				if ($count > $limit) {
					break;
				} else {
					$count++;
				}
				//
				if ($user_logged_in == $added_by) {
					$delete_button = "<input type='button' class='close' color='red' data-toggle='modal' data-target='#delete_post' value='X'></input>";
				} else {
					$delete_button = "";
				}
				//
				
				$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE user_name = '{$added_by}' " );
				$user_row = mysqli_fetch_array($user_details_query);
				$first_name = $user_row['first_name'];
				$last_name = $user_row['last_name'];
				$profile_pic = $user_row['profile_pic'];
	
				//HTML Block
				?>
				<script>
					function toggle<?php echo $id ?>(event) {
						var target = $(event.target);
						if (!target.is('a')) {
							var element  = document.getElementById("toggleComment<?php echo $id ?>");
							if(element.style.display == "block")
								element.style.display = "none";
							else 
								element.style.display = "block";
							}
						}
				</script>


				<?php
				$comment_check_query = "SELECT * FROM comments WHERE post_id = '{$id}' ";
				$comment_check = mysqli_query($this->con, $comment_check_query);
				$comment_check_num = mysqli_num_rows($comment_check);
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
				//
				$str .= "<div class='status_post' onClick='javascript:toggle{$id}(event)'>
						<div class='post_profile_pic'>
							<img src='{$profile_pic}' width='50'></img>
						</div>
						<div class='posted_by' style='color:#acacac;'>
							<a href='profile.php?profile_username={$added_by}'>
								{$first_name}&nbsp;{$last_name}
							</a> 
							&nbsp;&nbsp;&nbsp;&nbsp;{$time_message}
							{$delete_button}
						</div>
						<div id='post_body'>{$body}<br>
						</div>
						<br>
						<br>
						<br>
						<div class='newsfeedPostOptions'>
							Comments({$comment_check_num})&nbsp;&nbsp;&nbsp;
							<iframe src='like.php?post_id={$id}' scrolling='no'></iframe>
						</div>
					</div>
					<div class='post_comment' id='toggleComment{$id}' style='display:none;'>
						<iframe src='comment_frame.php?post_id={$id}' id='comment_iframe' frameborder='0'></iframe>
					</div>
					<hr>
				
				" ;
	
				?>

				<div class="modal fade" id="delete_post" tabindex="-1" role="dialog" aria-labelledby="delete_postModalLabel" aria-hidden="true">
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="delete_postModalLabel">Delete Post?</h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        Are you sure you want to delete this post?
				      </div>
				      <div class="modal-footer">
				       	<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<a href="includes/form_handlers/delete_post.php?post_id=<?php echo $id ?>">
						<button type="button" class="btn btn-primary" >Delete</button>
					</a>
				      </div>
				    </div>
				  </div>
				</div>

				<script>
					/*
					$(document).ready(function() {
						$("#post<?php echo $id?>").on("click", function() {
							console.log("hello");
							bootbox.confirm("Are you sure you want to delete this post?", function(result) {
								$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id ?>", {result:result});
								if(result)
									location.reload();
							});
						});
					});
					*/
				</script>
				<?php
			} //end while

			if ($count > $limit) {
				$str .= "<input type='hidden' class='nextPage' value='" . ($page +1) . "'>";
				$str .= "<input type='hidden' class='noMorePosts' value='false'>";
			} else {
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'>No More Posts</p>";
			}
		} // end mysqli if
		echo $str;
	}

	public function loadPostsFriends($data, $limit) {
		$page = $data['page'];
		$user_logged_in = $this->user_obj->getUsername();
		if ($page == 1 ) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}
		$str = "";
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted = 'no' ORDER BY id DESC");

		if (mysqli_num_rows($data_query) > 0) {
			$num_itterations = 0;
			$count = 1;
			while ($row = mysqli_fetch_array($data_query)) {
				$id 		= $row['id'];
				$body 		= $row['body'];
				$added_by 	= $row['added_by'];
				$user_to	= $row['user_to'];
				$date_added	= $row['date_added'];
				$user_closed	= $row['user_closed'];
				$deleted	= $row['deleted'];
				$likes		= $row['likes'];
				//
				if ($user_to == "none") {
					$user_to = "";
				} else {
					$user_to_obj = new User($this->con, $user_to);
					$user_to_name = $user_to_obj->getFirstAndLastName();
					$user_to = "to <a href='profile.php?profile_username={$row['user_to']}'>$user_to_name</a>";
				}
				//
				$added_by_obj = new User($this->con, $added_by);
				if($added_by_obj->isClosed()) {
					continue;
				}
				//
				$user_logged_obj = new User($this->con, $user_logged_in);
				if($user_logged_obj->isFriend($added_by)) {
					//
					if  ($num_itterations++ < $start) {
						continue;
					}
					if ($count > $limit) {
						break;
					} else {
						$count++;
					}
					//
					if ($user_logged_in == $added_by) {
						$delete_button = "<input type='button' class='close' color='red' data-toggle='modal' data-target='#delete_post' value='X'></input>";
					} else {
						$delete_button = "";
					}
					//
					
					$user_details_query = mysqli_query($this->con, "SELECT first_name, last_name, profile_pic FROM users WHERE user_name = '{$added_by}' " );
					$user_row = mysqli_fetch_array($user_details_query);
					$first_name = $user_row['first_name'];
					$last_name = $user_row['last_name'];
					$profile_pic = $user_row['profile_pic'];
	
					//HTML Block
					?>
					<script>
						function toggle<?php echo $id ?>(event) {
							var target = $(event.target);
							if (!target.is('a')) {
								var element  = document.getElementById("toggleComment<?php echo $id ?>");
								if(element.style.display == "block")
									element.style.display = "none";
								else 
									element.style.display = "block";
								}
							}
					</script>


					<?php
					$comment_check_query = "SELECT * FROM comments WHERE post_id = '{$id}' ";
					$comment_check = mysqli_query($this->con, $comment_check_query);
					$comment_check_num = mysqli_num_rows($comment_check);
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
					//
					$str .= "<div class='status_post' onClick='javascript:toggle{$id}(event)'>
							<div class='post_profile_pic'>
								<img src='{$profile_pic}' width='50'></img>
							</div>
							<div class='posted_by' style='color:#acacac;'>
								<a href='profile.php?profile_username={$added_by}'>
									{$first_name}&nbsp;{$last_name}
								</a> 
								{$user_to}&nbsp;&nbsp;&nbsp;&nbsp;{$time_message}
								{$delete_button}
							</div>
							<div id='post_body'>{$body}<br>
							</div>
							<br>
							<br>
							<br>
							<div class='newsfeedPostOptions'>
								Comments({$comment_check_num})&nbsp;&nbsp;&nbsp;
								<iframe src='like.php?post_id={$id}' scrolling='no'></iframe>
							</div>
						</div>
						<div class='post_comment' id='toggleComment{$id}' style='display:none;'>
							<iframe src='comment_frame.php?post_id={$id}' id='comment_iframe' frameborder='0'></iframe>
						</div>
						<hr>
					
					" ;
				} //end if you are friends
	
				?>

					<div class="modal fade" id="delete_post" tabindex="-1" role="dialog" aria-labelledby="delete_postModalLabel" aria-hidden="true">
					  <div class="modal-dialog" role="document">
					    <div class="modal-content">
					      <div class="modal-header">
					        <h5 class="modal-title" id="delete_postModalLabel">Delete Post?</h5>
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					          <span aria-hidden="true">&times;</span>
					        </button>
					      </div>
					      <div class="modal-body">
					        Are you sure you want to delete this post?
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
						<a href="includes/form_handlers/delete_post.php?post_id=<?php echo $id ?>">
							<button type="button" class="btn btn-primary" >Delete</button>
						</a>
					      </div>
					    </div>
					  </div>
					</div>
					<script>
						/*
						$(document).ready(function() {
							$("#post<?php echo $id?>").on("click", function() {
								console.log("hello");
								bootbox.confirm("Are you sure you want to delete this post?", function(result) {
									$.post("includes/form_handlers/delete_post.php?post_id=<?php echo $id ?>", {result:result});
									if(result)
										location.reload();
								});
							});
						});
						*/
					</script>
				<?php
			} //end while

			if ($count > $limit) {
				$str .= "<input type='hidden' class='nextPage' value='" . ($page +1) . "'>";
				$str .= "<input type='hidden' class='noMorePosts' value='false'>";
			} else {
				$str .= "<input type='hidden' class='noMorePosts' value='true'><p style='text-align:center;'>No More Posts</p>";
			}
		} // end mysqli if
		echo $str;
	}

}
?>
