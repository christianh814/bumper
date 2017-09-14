<?php require_once("includes/header.php");?>
<!-- CONTENT -->
<div class="main_column column" id="main_column">
	<h4>Friend Requests</h4>
	<?php
		$query = mysqli_query($con, "SELECT * FROM friend_req WHERE user_to = '{$user_logged_in}' ");
		if (mysqli_num_rows($query) == 0) {
			echo "There are no pending requests";
		} else {
			while ($row = mysqli_fetch_array($query)) {
				$user_from = $row['user_from'];
				$user_from_obj = new User($con, $user_from);
				//
				echo $user_from_obj->getFirstAndLastName() . " " . "sent you a friend request!";
			
				$user_friend_array = $user_from_obj->getFriendArray();

				if (isset($_POST['accept_request' . $user_from])) {
					$afq1 = "UPDATE users SET friend_arrary = CONCAT(friend_arrary, '{$user_from},') WHERE user_name = '{$user_logged_in}' ";
					$add_friend_query1 = mysqli_query($con, $afq1);
					echo mysqli_error($con);
					//
					$afq2 = "UPDATE users SET friend_arrary = CONCAT(friend_arrary, '{$user_logged_in},') WHERE user_name = '{$user_from}' ";
					$add_friend_query2 = mysqli_query($con, $afq2);
					echo mysqli_error($con);

					$del_query = mysqli_query($con, "DELETE FROM friend_req WHERE user_to = '{$user_logged_in}' AND user_from = '{$user_from}' ");
					echo "You are now friends!";
					header("Location: requests.php");
					echo mysqli_error($con);
				}

				if (isset($_POST['ignore_request' . $user_from])) {
					$del_query = mysqli_query($con, "DELETE FROM friend_req WHERE user_to = '{$user_logged_in}' AND user_from = '{$user_from}' ");
					echo "Request ignored!";
					header("Location: requests.php");
				}
				?>
				<form action="requests.php" method="POST">
					<input type="submit" name="accept_request<?php echo $user_from ?>" id="accept_button" value="Accept"></input>
					<input type="submit" name="ignore_request<?php echo $user_from ?>" id="ignore_button" value="Ignore"></input>
				</form>
				<?php
				
			}
		}
	?>
</div>
<!-- ./CONTENT -->
</div> <!-- end div wrapper in header -->
<?php require_once("includes/footer.php");?>
