<?php require_once("includes/header.php");?>
		<!-- site Content -->
<?php
	if(isset($_POST['post'])) {
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
	<?php 
		// $post = new Post($con, $user_logged_in);
		// $post->loadPostsFriends();
	?>
	<div class="post_area">
	<img id="loading" src="assets/images/icons/loading.gif"></img>
	</div>
</div>
	<script>
		var user_logged_in = '<?php echo $user_logged_in ?>';
		$(document).ready(function() {
			$('#loading').show();
			$.ajax({
				url: "includes/handlers/ajax_load_posts.php",
				type: "POST",
				data: "page=1&user_logged_in=" + user_logged_in,
				cache: false,

				success: function(data) {
					$('#loading').hide();
					$('.post_area').html(data);
				}
			});
			$(window).scroll(function () {
				var height = $('.post_area').height();
				var scroll_top = $(this).scrollTop();
				var page = $('.post_area').find('.nextPage').val();
				var noMorePosts = $('.post_area').find('.noMorePosts').val();

				if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
					$('#loading').show();
					var ajaxReq = $.ajax({
						url: "includes/handlers/ajax_load_posts.php",
						type: "POST",
						data: "page=" + page + "&user_logged_in=" + user_logged_in,
						cache: false,

						success: function(response) {
							$('.post_area').find('.nextPage').remove();
							$('.post_area').find('.noMorePosts').remove();
							$('#loading').hide();
							$('.post_area').append(response);
						}
					});
					
				} //end if
				return false;
			});
		});
	</script>
		<!--./ site Content -->
</div> <!-- end div wrapper in header -->
<?php require_once("includes/footer.php");?>
