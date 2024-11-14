
$(document).ready(function() {
	$("#userDisplayName").keyup(function(e) {
		var curValue = $("#userDisplayName").val();
		if (curValue.length > 10) {
			$("#maxCharWarning").css("color", "#FF0000");
			$("#userDisplayName").css("color", "#FF0000");
		} else {
			$("#maxCharWarning").css("color", "#000000");
			$("#userDisplayName").css("color", "#000000");
		}
	});

	var userDisplayName = $("#currentUserDisplayName").val();
	if (userDisplayName != "Guest") {
		$(".enableSubmitButton").change(function(event) { enableSubmitButton("saveChangesButton"); });
		$(".enableSubmitButton").keyup(function(event) {
			if ($(event.target).val().length > 0) {
				enableSubmitButton("saveChangesButton");
			}
		});
	}
	//$("#saveChangesButton").prop("disabled", true);
	disableSubmitButton("saveChangesButton");
});
