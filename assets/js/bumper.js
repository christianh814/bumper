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
