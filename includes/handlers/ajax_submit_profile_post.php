<?php
require_once("../../config/config.php");
require_once("../../includes/classes/User.php");
require_once("../../includes/classes/Post.php");
require_once("../../includes/classes/Notification.php");
//
if (isset($_POST['post_body'])) {
	$post = new Post($con, $_POST['user_from']);
	$post->submitPost($_POST['post_body'], $_POST['user_to']);
}
?>
