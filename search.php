<?php require_once("includes/header.php");?>
		<!-- site Content -->
		<?php
			if (isset($_GET['q'])) {
				$query = $_GET['q'];
			} else {
				$query = "";
			}
			//
			if (isset($_GET['type'])) {
				$type = $_GET['type'];
			} else {
				$type = "name";
			}
		?>
		<div class="main_column column" id="main_column">
			<?php
				if ($query == "") {
					echo "You must enter something to search...";
				} else {
					if ($type == "user_name") {
					        $sql = "SELECT * FROM users WHERE user_name LIKE '{$query}%' AND user_closed = 'no' LIMIT 8 ";
					        $users_returned_query = mysqli_query($con, $sql);
					} else {
						// Split elements into an array using " " (i.e. space) as the "split on"
						$names = explode(" ", $query);
						if (count($names) == 3) {
						        // if they provided a space assume they are searching for fname and lname respectivley
						        $sql = "SELECT * FROM users WHERE (first_name LIKE '{$names[0]}%' AND last_name LIKE '{$names[2]}%')";
							$sql .= " AND user_closed = 'no' ";
						        $users_returned_query = mysqli_query($con, $sql);
						} else if (count($names) == 2) {
						        // if they provided one; search for both fname or lname
						        $sql = "SELECT * FROM users WHERE (first_name LIKE '{$names[0]}%' AND last_name LIKE '{$names[1]}%')";
							$sql .= " AND user_closed = 'no' ";
						        $users_returned_query = mysqli_query($con, $sql);
						} else {
						        $sql = "SELECT * FROM users WHERE (first_name LIKE '{$names[0]}%' OR last_name LIKE '{$names[0]}%')";
							$sql .= " AND user_closed = 'no' ";
						        $users_returned_query = mysqli_query($con, $sql);
						}
						if (mysqli_num_rows($users_returned_query) == 0) {
							echo "No users found with " . $type . " like: " . $query;
						} else {
							echo mysqli_num_rows($users_returned_query) . " results found: <br> <br>";
						}
						echo "<p id='grey'>Try searching for: </p>";
						echo "<a href='search.php?q=" . $query. "&type=name'>Names</a>, <a href='search.php?q=" . $query. "&type=user_name'>Usernames</a><br><br><hr id='search_hr'>";
						while ($row = mysqli_fetch_array($users_returned_query)) {
							$user_obj = new User($con, $user['user_name']);
							$button = "";
							$mutual_friends = "";
							//
							if ($user['user_name'] !== $row['user_name']) {
								//Gen button depending on friend status
								if ($user_obj->isFriend($row['user_name'])) {
									$button = "<input type='submit' name='" . $row['user_name'] . "' class='danger' value='Remove Friend'>";
								} else if ($user_obj->didRecvReq($row['user_name'])) {
									$button = "<input type='submit' name='" . $row['user_name'] . "' class='warning' value='Respond to Request'>";
								} else if ($user_obj->didSndReq($row['user_name'])) {
									$button = "<input type='submit' name='" . $row['user_name'] . "' class='default' value='Request Sent'>";
								} else {
									$button = "<input type='submit' name='" . $row['user_name'] . "' class='success' value='Add Friend'>";
								}
								$mutual_friends = $user_obj->getMutualFriends($row['user_name']) . " friends in common";
								//Button forms
								if (isset($_POST[$row['user_name']])) {
									if($user_obj->isFriend($row['user_name'])) {
										$user_obj->removeFriend($row['user_name']);
										header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
									} else if ($user_obj->didRecvReq($row['user_name'])) {
										header("Location: requests.php");
									} else if ($user_obj->didSndReq($row['user_name'])) {
										//
									} else {
										$user_obj->sendFriendReq($row['user_name']);
										header("Location: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
									}
								}
							}
							echo "<div class='search_result'>
								<div class='searchPageFriendButtons'>
									<form action='' method='POST'>
									" . $button . "
									<br>
									</form>
								</div>
								<div class='result_profile_pic'>
									<a href=profile.php?profile_username=" . $row['user_name'] . ">
										<img src=" . $row['profile_pic'].  " style='height: 100px;' alt='profile_pic'><img>
									</a>
								</div>
									<a href=profile.php?profile_username=" . $row['user_name'] . ">
									" . $row['first_name'] . " " . $row['last_name']. "
									<p id='grey'> " . $row['user_name']. "</p>
									</a>
									<br>
									" . $mutual_friends . "
									<br>
							</div>
							<hr id='search_hr'>";
						} //end while
					}

				}
			?>
		</div>
		<!--./ site Content -->
</div> <!-- end div wrapper in header -->
<?php require_once("includes/footer.php");?>
