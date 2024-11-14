var debug = false;
var sectionHeaders = new Object();

$(document).ready(function() {
	logMessage("page init starting");
	
	//setup date picker
	if (true) {
		
	} else if (true) {
		$("#datepicker").datepicker();
	} else {
		$('#datepicker').mobiscroll().date({
	        theme: 'ios',
	        display: 'inline',
	        mode: 'scroller',
	        dateOrder: 'mmD ddyy'
	    });
	}
	
	//if no course selected yet then hide friend and date select
	if ($("#selectCourse option:selected").val() == "NULL") {
		$(".showAfterSelectCourse").css("display", "none");
		$("#dateDay").css("display", "none");
		$("#dateMonth").css("display", "none");
		$("#dateYear").css("display", "none");
		$("#selectCourse").change(function(event) {
			$("#selectCourse").unbind("change");
			$(".showAfterSelectCourse").show(750);
			$("#dateDay").show(300);
			$("#dateMonth").show(300);
			$("#dateYear").show(300);
		});
	}
	
	/*
	//turn "select Friend(s)" into jquery multi select
	$("#selectPlayers").multiselect({
    selectedText: "# of # selected",
		minWidth:"auto",
		position: {
			my: 'center top',
			at: 'center bottom'
		}
	}); /**/
	
	//if no hole selected yet then hide enter scores, total scores and edit scores
	if ($("#selectHole option:selected").val() == "NULL") {
		//$("#saveScoreButton").prop("disabled", true);
		disableSubmitButton("saveScoreButton");
		$(".hideUntilHoleChosen").css("display", "none");
		$("#selectHole").change(function(event) {
			$("#selectHole").unbind("change");
			$(".hideUntilHoleChosen").show(500);
			enableSubmitButton("saveScoreButton");
		});
		$("#selectHole").focus();
	}
	
	//bold save scores button when one changes (it is enabled by default)
	$(".enterScoreSelect").change(function(event) { enableSubmitButton("saveScoreButton"); });
	
	//disable edit score button until value change
	$(".scoreEditSelectHole").change(function(event) { enableSubmitButton("editScoresButton"); });
	//$("#editScoresButton").prop("disabled", true);
	disableSubmitButton("editScoresButton");
	
	//disable "selectCourseFriendsDate" button until value change
	$(".enableSubmitButton").change(function(event) { enableSubmitButton("scorecardSetupSubmit"); });
	//$("#scorecardSetupSubmit").prop("disabled", true);
	disableSubmitButton("scorecardSetupSubmit");
	
	//save section headers text - section header text
	//can then be used for error console for AJAX calls
	var sectionHeadersArr = $(".blockHeader");
	for (var a = 0; a < sectionHeadersArr.length; a++) {
		var curID = sectionHeadersArr[a].id
		sectionHeaders[curID] = sectionHeadersArr[a].innerHTML;
		//logMessage(sectionHeaders[curID]);
	}
});

function logMessage(message) {
	if (!debug) return;
    var curText = message + "<br />" + $("#debugOutput").html();
    if (curText.length > 5000) {
        curText = curText.substring(0, 5000);
    }
    $("#debugOutput").html(curText);
}
