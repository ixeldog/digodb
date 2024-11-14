
$(document).ready(function() {
	//$("#createUserButton").prop("disabled", true);
	disableSubmitButton("createUserButton");
	$(".createUserField").keyup(function(e) {
		var username = $("#username").val();
		var pass1 = $("#userPassword").val();
		var pass2 = $("#userPasswordRepeat").val();
		if (username != null && username.length > 0 &&
			pass1 != null && pass1.length > 0 &&
			pass2 != null && pass2.length > 0)
		{
			//$("#createUserButton").prop("disabled", false);
			//$("#createUserButton").addClass("enabledSubmitButton");
			enableSubmitButton("createUserButton");
		} else {
			//$("#createUserButton").prop("disabled", true);
			//$("#createUserButton").removeClass("enabledSubmitButton");
			disableSubmitButton("createUserButton");
		}
	});
});
