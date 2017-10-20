<?php require_once("includes/header.php");?>
		<!-- site Content -->
<?php
	if(isset($_POST['post'])) {
		$upload_ok = 1;
		$image_name = $_FILES['file_to_upload']['name'];
		$error_msg = "";
		if ($image_name != "") {
			$target_dir = "assets/images/posts/";
			$image_name = $target_dir . uniqid() . basename($image_name);;
			$image_file_type = pathinfo($image_name, PATHINFO_EXTENSION);

			if ($_FILES['file_to_upload']['size'] > 10000000) {
				$error_msg = "Sorry, file is too large";
				$upload_ok = 0;
			}
			if(
				strtolower($image_file_type) != "jpeg" &&
				strtolower($image_file_type) != "jpg" &&
				strtolower($image_file_type) != "png" &&
				strtolower($image_file_type) != "gif" &&
				strtolower($image_file_type) != "svg" &&
				strtolower($image_file_type) != "bmp"
			) {
				$error_msg = "Sorry, that file is not allowed";
				$upload_ok = 0;
			}

			//
			if ($upload_ok) {
				if(move_uploaded_file($_FILES['file_to_upload']['tmp_name'], $image_name)) {
					//image uploaded okay
				} else {
					//image NOT uploaded
					$upload_ok = 0;
				}
			}
		}
		
		if ($upload_ok) {
			$post = new Post($con, $user_logged_in);
			$post->submitPost($_POST['post_text'], 'none', $image_name);
		} else {
			echo "<div style='text-align:center' class='alert alert-danger'>{$error_msg}</div>";
		}
		//
	}
?>
<div class="user_details column">
	<a href="profile.php?profile_username=<?php echo $user_logged_in ?>"><img src="<?php echo $user['profile_pic']; ?>"></img></a>
	<div class="user_details_left_right">
	<a href="profile.php?profile_username=<?php echo $user_logged_in ?>"><?php echo $user['first_name'] . " " . $user['last_name']; ?></a><br>
		<?php echo "Posts: " . $user['num_posts']?><br>
		<?php echo "Bumps: " . $user['num_likes']?>
	</div>
</div>
<div class="main_column column">
	<form class="post_form" action="index.php" method="post" enctype="multipart/form-data">
		<input type="file" name="file_to_upload" id="file_to_upload"></input>
		<textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
		<input type="submit" name="post" id="post_button" value="Post"></input>
		<hr>
	</form>
	<?php 
		// $post = new Post($con, $user_logged_in);
		// $post->loadPostsFriends();
	?>
	<div class="post_area"> </div>
	<img id="loading" src="assets/images/icons/loading.gif"></img>
</div>
<div class="user_details column">
	<u><h4>Popular</h4></u>
	<div class="trends">
	<?php
		$sql = "SELECT * FROM trends ORDER BY hits DESC LIMIT 9";
		$query = mysqli_query($con, $sql);
		//
		foreach($query as $row) {
			$word = $row['title'];
			$word_dot = strlen($word) >= 14 ? "..." : "";
			$trimmed_word = str_split($word, 14);
			$trimmed_word = $trimmed_word[0];
			//
			echo "<div style='padding:1px;'>" . $trimmed_word . $word_dot . "<br></div>";
		}
	?>
	</div>
</div>
	<script>
		// Below controls what gets seen during scrolling
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
