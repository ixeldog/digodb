
$(document).ready(function() {
	disableSubmitButton("loginButton");
	$(".loginField").keyup(function() {
		var username = $("#username").val();
		var password = $("#userPassword").val();
		if (username != null && username.length > 0 &&
			password != null && password.length > 0)
		{
			enableSubmitButton("loginButton")
		} else {
			disableSubmitButton("loginButton");
		}
	});
	
	//convert "Take a tour" link to a button if javascript enabled
	var curLinkText = $("#tourLink").children().first().html();
	$("#tourLink").html("<input type=\"button\" class=\"enabledSubmitButton\"" +
		" value=\"" + curLinkText + "\" />");
	$("#tourLink").click(function(e) {
		window.location = "loginActions.php?username=TestUser&userPassword=password";
	});
})
