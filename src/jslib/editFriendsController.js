
$(document).ready(function() {
	var userDisplayName = $("#currentUserDisplayName").val();
	if (userDisplayName != "Guest") {
		$(".enableSubmitButton").change(function(event) { enableSubmitButton("saveChangesButton"); });
		$(".enableSubmitButton").keyup(function(event) {
			if ($(event.target).val().length > 0) {
				enableSubmitButton("saveChangesButton");
			}
		});
	}
	disableSubmitButton("saveChangesButton");
});
