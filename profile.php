<?php
require_once("includes/header.php");
//
if (isset($_GET['profile_username'])) {
	$user_name = $_GET['profile_username'];
	$user_details = "SELECT * FROM users WHERE user_name = '{$user_name}' ";
	$user_details_query = mysqli_query($con, $user_details);
	$user_array = mysqli_fetch_array($user_details_query);
	$num_friends = (substr_count($user_array['friend_arrary'], ",")) - 1;
}
//
if (isset($_POST['remove_friend'])) {
	$user = new User($con, $user_logged_in);
	$user->removeFriend($user_name);
}
//
if (isset($_POST['add_friend'])) {
	$user = new User($con, $user_logged_in);
	$user->sendFriendReq($user_name);
}
//
if (isset($_POST['respond_request'])) {
	header("Location: requests.php");
}
//
?>
<style type="text/css">
	.wrapper {
		margin-left: 0px;
		padding-left: 0px;
	}
</style>
		<!-- site Content -->
<div class="profile_left">
	<img src='<?php echo $user_array['profile_pic'] ?>'></img>
	<div class="profile_info">
		<p><?php echo "Posts: " . $user_array['num_posts']?></p>
		<p><?php echo "Bumps: " . $user_array['num_likes']?></p>
		<p><?php echo "Friends: " . $num_friends ?></p>
	</div>
	<form action="profile.php?profile_username=<?php echo $user_name; ?>" method="POST">
		<?php
		$profile_user_obj = new User($con, $user_name);
		if ($profile_user_obj->isClosed()) {
			header("Location: user_closed.php");
		}
		$logged_in_user_obj = new User($con, $user_logged_in);

		if ($user_logged_in !== $user_name) {
			if($logged_in_user_obj->isFriend($user_name)) {
				echo "<input type='submit' name='remove_friend' class='danger' value='Remove Friend'></input><br>";
			} elseif ($logged_in_user_obj->didRecvReq($user_name)) {
				echo "<input type='submit' name='respond_request' class='warning' value='Respond to Request'></input><br>";
			} elseif ($logged_in_user_obj->didSndReq($user_name)) {
				echo "<input type='submit' name='' class='default' value='Request sent'></input><br>";
			} else {
				echo "<input type='submit' name='add_friend' class='success' value='Add Friend'></input><br>";
			}
		}
		?>
	</form>
		<input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something"></input>
</div>

<div class="main_column column">
	<?php echo $user_array['user_name']; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="myModalLabel">Post Something!</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
      	<p>This will appear on the user's profile page and newsfeed for the world to see</p>
      	<form class="profile_post" action="" method="POST">
      		<div class="form_group">
			<textarea class="form-control" name="post_body"></textarea>
			<input type="hidden" name="user_from" value="<?php echo $user_logged_in ?>"></input>
			<input type="hidden" name="user_to" value="<?php echo $user_name ?>"></input>
		</div>
	</form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
      </div>

    </div>
  </div>
</div>
		<!--./ site Content -->
</div> <!-- end div wrapper in header -->
<?php require_once("includes/footer.php");?>
