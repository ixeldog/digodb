$(document).ready(function() {

	$(".expandBody").hide();
	//$(".expandBody").css("overflow", "hidden");
	//$(".expandBody").height(0);
	$(".expandLink").each(function() {
		var curText = $(this).html();
		$(this).html("<a href=\"#\">" + curText + "</a>");
	});
	
	$("#pastScoresLink").click(function() {
		$("#pastScoresBody").show(700);
		//$("#pastScoresBody").height("auto");
	});
	$("#viewStatsLink").click(function() {
		$("#viewStatsBody").show(700);
		//$("#viewStatsBody").height("auto");
	});
	$("#newRoundLink").click(function() {
		$("#newRoundBody").show(700);
		//$("#newRoundBody").height("auto");
	});
});
