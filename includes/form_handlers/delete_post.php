<?php
require_once("../../config/config.php");
//
if (isset($_POST['post_id'])) {
	$post_id = $_POST['post_id'];
	if (isset($_POST['result'])) {
		if($_POST['result'] == "true") {
			$myfile = fopen("/tmp/log.php.txt", "w") or die("Unable to open file!");
			$txt = "I AM DELETING NOW";
			fwrite($myfile, $txt);
			fclose($myfile);
			$query = mysqli_query($con, "UPDATE posts SET deleted = 'yes' WHERE id = '{$post_id}' ");
		}
	}
}

?>
