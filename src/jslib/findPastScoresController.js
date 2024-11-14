
$(document).ready(function() {
	$(".enableSubmitButton").change(function(event) { enableSubmitButton("submitSearchOptions"); });
	//$("#submitSearchOptions").prop("disabled", true);
	disableSubmitButton("submitSearchOptions");
	
});
