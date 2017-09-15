<?php
require_once("../../config/config.php");
require_once("../classes/User.php");
require_once("../classes/Post.php");

$limit = 10;

$posts = new Post($con, $_REQUEST['user_logged_in']);
$posts->loadPostsProfile($_REQUEST, $limit);
?>
