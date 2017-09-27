<?php
require_once("config/config.php");
require_once("includes/classes/User.php");
require_once("includes/classes/Post.php");
require_once("includes/classes/Message.php");
//
if (isset($_SESSION['username'])) {
	$user_logged_in = $_SESSION['username'];
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE user_name = '{$user_logged_in}' ");
	$user = mysqli_fetch_array($user_details_query);
} else {
	header("Location: register.php");
}
?>
<html>
	<head>
	<title>Welcome to Bumper</title>
	<!-- Javascript -->
	<!-- <script src="assets/js/bootstrap.js"></script> -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
	<script src="assets/js/bumper.js"></script> 
	<script src="assets/js/bootbox.min.js"></script> 
	<script src="assets/js/jquery.Jcrop.js"></script> 
	<script src="assets/js/jcrop_bits.js"></script> 

	<!-- Sytlesheets -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"></link>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css"></link>
	<link rel="stylesheet" type="text/css" href="assets/css/jquery.Jcrop.css"></link>

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"></link>
	<link rel="icon" href="favicon.ico" type="image/x-icon"></link>
	</head>
<body>
	<div class="top_bar">
		<div class="logo">
			<a href="index.php">Bumper</a>
		</div>
		<nav>
			<a href="<?php echo "profile.php?profile_username=" .$user_logged_in ?>"><?php echo $user['first_name'] ?></a>
			<a href="index.php"><i class="fa fa-home fa-lg"></i></a>
			<a href="javascript:void(0);" onclick="getDropdownData('<?php echo $user_logged_in ?>', 'message')"><i class="fa fa-envelope fa-lg"></i></a>
			<a href="#"><i class="fa fa-bell-o fa-lg"></i></a>
			<a href="requests.php"><i class="fa fa-users fa-lg"></i></a>
			<a href="#"><i class="fa fa-cog fa-lg"></i></a>
			<a href="includes/handlers/logout.php"><i class="fa fa-sign-out fa-lg"></i></a>
		</nav>

		<div class="dropdown_data_window" style="height:0px; border:none;"></div>
		<input type="hidden" id="dropdown_data_type" value="">
	</div>
	    <script>
        $(function(){

            var user_logged_in = '<?php echo $user_logged_in; ?>';
            var dropdownInProgress = false;

            $(".dropdown_data_window").scroll(function() {
                var bottomElement = $(".dropdown_data_window a").last();
                var noMoreData = $('.dropdown_data_window').find('.noMoreDropdownData').val();

                if (isElementInView(bottomElement[0]) && noMoreData == 'false') {
                    loadPosts();
                }
            });

            function loadPosts() {
                if(dropdownInProgress) { //If it is already in the process of loading some posts, just return
                    return;
                }

                dropdownInProgress = true;

                var page = $('.dropdown_data_window').find('.nextPageDropdownData').val() || 1; 

                // var pageName = "ajax_load_messages.php"; 
                var pageName; //Holds name of page to send ajax request to
                var type = $('#dropdown_data_type').val();

		//console.log("type: " + type);

                if(type == 'notification')
                    pageName = "ajax_load_notifications.php";
                else if(type == 'message')
                    pageName = "ajax_load_messages.php";
		else
                    pageName = "ajax_load_messages.php";

                $.ajax({
                    url: "includes/handlers/" + pageName,
                    type: "POST",
                    data: "page=" + page + "&user_logged_in=" + user_logged_in,
                    cache: false,

                    success: function(response) {

                        $('.dropdown_data_window').find('.nextPageDropdownData').remove(); //Removes current .nextpage
                        $('.dropdown_data_window').find('.noMoreDropdownData').remove();

                        $('.dropdown_data_window').append(response);

                        dropdownInProgress = false;
                    }
                });
            }

            //Check if the element is in view
            function isElementInView (el) {
                var rect = el.getBoundingClientRect();

                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && //* or $(window).height()
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth) //* or $(window).width()
                );
            }
        });
    </script>
<div class="wrapper">
