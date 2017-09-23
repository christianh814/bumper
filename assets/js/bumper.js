$(document).ready(function() {
	//profile post
	$('#submit_profile_post').click(function () {
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_submit_profile_post.php",
			data: $('form.profile_post').serialize(),
			success: function(msg) {
				$("#post_form").modal('hide');
				location.reload();

			},
			error: function() {
				alert('Failure');
			}
		});
	});
});

function getUsers(value, user) {
	$.post("includes/handlers/ajax_friend_search.php", {query:value, user_logged_in:user}, function(data) {
		$(".results").html(data);
	});
}

function getDropdownData(user, type) {
	if($(".dropdown_data_window").css("height") == "0px") {
		var pageName;
		if(type == 'notifcation') {
		} else if (type == 'message') {
			pageName = "ajax_load_messages.php";
			$("span").remove("#unread_message");
		}

		var ajaxreq = $.ajax({
			url: "includes/handlers/" + pageName,
			type: "POST", 
			data: "page=1&user_logged_in=" + user,
			cache: false,

			succes: function(response) {
				$(".dropdown_data_window").html(response);
				$(".dropdown_data_window").css({"padding" : "0px", "height" : "280px"});
				$(".dropdown_data_type").val(type);
			}
		});
	} else {
		$(".dropdown_data_window").html("");
		$(".dropdown_data_window").css({"padding" : "0px", "height" : "0px"});
	}
}
