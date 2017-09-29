<?php
require_once("../../config/config.php");
require_once("../classes/User.php");
require_once("../classes/Notification.php");

$limit = 7;

$not = new Notification($con, $_REQUEST['user_logged_in']);
echo $not->getNotifications($_REQUEST, $limit);
?>
