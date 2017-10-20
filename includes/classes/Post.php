<?php
class Post {
	private $user_obj;
	private $con;

	public function __construct($con, $user) {
		$this->con = $con;
		$this->user_obj = new User($con, $user);
	}

	public function submitPost($body, $user_to, $image_name) {
		$body = strip_tags($body);
		$body = mysqli_real_escape_string($this->con, $body);
		$check_empty = preg_replace('/\s+/', '', $body);

		if ($check_empty !== "") {
			// Youtube embed support
			$body_array = preg_split("/\s+/", $body);
			foreach($body_array as $key => $value) {
				if (strpos($value, "www.youtube.com/watch?v=") !== false){
					$link = preg_split("!&!", $value);
					$value = preg_replace("!watch\?v=!", "embed/" , $link[0]);
					$value = "<br><iframe width=\'420\' height=\'315\' src=\'" . $value . "\'></iframe><br>";
					$body_array[$key] = $value;
				} elseif (strpos($value, "youtu.be/") !== false) {
					$link = preg_split("!\.be/!", $value);
					$value = "<br><iframe width=\'420\' height=\'315\' src=\'https://www.youtube.com/embed/" . $link[1] . "\'></iframe><br>";
					$body_array[$key] = $value;
				}
			}
			$body = implode(" ", $body_array);
			//
			$date_added = date("Y-m-d H:i:s");
			$added_by = $this->user_obj->getUsername();
			if ($user_to == $added_by) {
				$user_to = "none";
			}
			$add_post_query = "INSERT INTO posts (body, added_by, user_to, date_added, user_closed, deleted, likes, image) ";
			$add_post_query .= "VALUES('{$body}', '{$added_by}', '{$user_to}', '{$date_added}', 'no', 'no', '0', '{$image_name}' ) ";
			$query = mysqli_query($this->con, $add_post_query);
			$returned_id = mysqli_insert_id($this->con);
			
			// Insert Notifaction
			if ($user_to !== "none") {
				$notificaton = new Notification($this->con, $added_by);
				$notification->insertNotification($returned_id, $user_to, "profile_post");
			}

			$num_posts = $this->user_obj->getNumPosts();
			$num_posts++;
			$update_query = mysqli_query($this->con, "UPDATE users SET num_posts = '{$num_posts}' WHERE user_name = '{$added_by}' ");
			// Trending words support
			$stop_words = "a about above across after again against all almost alone along already
					also although always among am an and another any anybody anyone anything anywhere are 
					area areas around as ask asked asking asks at away b back backed backing backs be became
					because become becomes been before began behind being beings best better between big 
					both but by c came can cannot case cases certain certainly clear clearly come could
					d did differ different differently do does done down down downed downing downs during
					e each early either end ended ending ends enough even evenly ever every everybody
					everyone everything everywhere f face faces fact facts far felt few find finds first
					for four from full fully further furthered furthering furthers g gave general generally
					get gets give given gives go going good goods got great greater greatest group grouped
					grouping groups h had has have having he her here herself high high high higher
					highest him himself his how however i im if important in interest interested interesting
					interests into is it its itself j just k keep keeps kind knew know known knows
					large largely last later latest least less let lets like likely long longer
					longest m made make making man many may me member members men might more most
					mostly mr mrs much must my myself n necessary need needed needing needs never
					new new newer newest next no nobody non noone not nothing now nowhere number
					numbers o of off often old older oldest on once one only open opened opening
					opens or order ordered ordering orders other others our out over p part parted
					parting parts per perhaps place places point pointed pointing points possible
					present presented presenting presents problem problems put puts q quite r
					rather really right right room rooms s said same saw say says second seconds
					see seem seemed seeming seems sees several shall she should show showed
					showing shows side sides since small smaller smallest so some somebody
					someone something somewhere state states still still such sure t take
					taken than that the their them then there therefore these they thing
					things think thinks this those though thought thoughts three through
					thus to today together too took toward turn turned turning turns two
					u under until up upon us use used uses v very w want wanted wanting
					wants was way ways we well wells went were what when where whether
					which while who whole whose why will with within without work
					worked working works would x y year years yet you young younger
					youngest your yours z lol haha omg hey ill iframe wonder else like 
					hate sleepy reason for some little yes bye choose am";
			$stop_words = preg_split("/[\s,]+/", $stop_words);
			$no_punct = preg_replace("/[^a-zA-Z 0-9]+/", "", $body);

			if (strpos($no_punct, "height") === false && strpos($no_punct, "width") === false && strpos($no_punct, "http") === false) {
				$no_punct = preg_split("/[\s,]+/", $no_punct);
				foreach($stop_words as $value) {
					foreach($no_punct as $key => $value2) {
						if(strtolower($value) == strtolower($value2)) {
							$no_punct[$key] = "";
						}
					}
				}
				foreach($no_punct as $value) {
					$this->calcTrend(ucfirst($value));
				}
			}
			//
		}
	}

	public function calcTrend($term) {
		if ($term != '') {
			$sql = "SELECT * FROM trends WHERE title = '{$term}' ";
			$query = mysqli_query($this->con, $sql);
			if(mysqli_num_rows($query) == 0) {
				$insert_sql = "INSERT INTO trends (title, hits) VALUES ('{$term}', '1') ";
				$insert_query = mysqli_query($this->con, $insert_sql);
			} else {
				$insert_sql = "UPDATE trends SET hits = hits+1 WHERE title = '{$term}' ";
				$insert_query = mysqli_query($this->con, $insert_sql);
			}
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
				$image_path	= $row['image'];
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
				if($image_path != "") {
					$image_div = "<div class='posted_image'>
							<img src='" . $image_path . "'>
							</div>";
				} else {
					$image_div = "";
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
							{$image_div}
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
				$image_path	= $row['image'];
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
					if($image_path != "") {
						$image_div = "<div class='posted_image'>
								<img src='" . $image_path . "'>
								</div>";
					} else {
						$image_div = "";
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
							{$image_div}
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

	public function getSinglePost($post_id) {
		$user_logged_in = $this->user_obj->getUsername();

		$opened_sql = "UPDATE notifications SET opend = 'yes' WHERE user_to = '{$user_logged_in}' AND link LIKE '%={$post_id}' ";
		$opend_query = mysqli_query($this->con, $opened_sql);

		$str = "";
		$data_query = mysqli_query($this->con, "SELECT * FROM posts WHERE deleted = 'no' AND id = '{$post_id}' ");

		if (mysqli_num_rows($data_query) > 0) {
			$row = mysqli_fetch_array($data_query);
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
				return;
			}
			//
			$user_logged_obj = new User($this->con, $user_logged_in);
			if($user_logged_obj->isFriend($added_by)) {
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
				} else {
					echo "<p>You cannot see this post</p>";
					return;
				}//end if you are friends
			} else {
				echo "<p>No Post found.</p>";
				return;
				
			}// end mysqli if
			echo $str;
		}

	}
?>
