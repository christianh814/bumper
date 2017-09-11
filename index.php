<?php require_once("includes/header.php");?>
		<!-- site Content -->
<?php
	if(isset($_POST['post_button'])) {
		$post = new Post($con, $user_logged_in);
		$post->submitPost($_POST['post_text'], 'none');
	}
?>
<div class="user_details column">
	<a href="profile.php?profile_username=<?php echo $user_logged_in ?>"><img src="<?php echo $user['profile_pic']; ?>"></img></a>
	<div class="user_details_left_right">
	<a href="profile.php?profile_username=<?php echo $user_logged_in ?>"><?php echo $user['first_name'] . " " . $user['last_name']; ?></a><br>
		<?php echo "Posts: " . $user['num_posts']?><br>
		<?php echo "Likes: " . $user['num_likes']?>
	</div>
</div>
<div class="main_column column">
	<form class="post_form" action="index.php" method="post">
		<textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
		<input type="submit" name="post" id="post_button" value="Post"></input>
		<hr>
	</form>
</div>
		<!--./ site Content -->
</div> <!-- end div wrapper in header -->
<?php require_once("includes/footer.php");?>
