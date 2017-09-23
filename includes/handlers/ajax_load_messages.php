<?php
require_once("../../config/config.php");
require_once("../classes/User.php");
require_once("../classes/Message.php");

$limit = 7;

$msg = new Message($con, $_REQUEST['user_logged_in']);
echo $msg->getConvosDropdown($_REQUEST, $limit);
?>
