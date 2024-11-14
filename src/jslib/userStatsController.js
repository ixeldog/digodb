
//if user sorts dataTable then remove player column colors
var sortCount = 0;

$(document).ready(function() {
	$(".enableSubmitButton").change(function(event) { enableSubmitButton("savePlayersAndCourse"); });
	//$("#savePlayersAndCourse").prop("disabled", true);
	disableSubmitButton("savePlayersAndCourse");
	
	var rowCount = $('#statsTable tr').length;
	if (rowCount <= 1) {
		return;
	}
	
	/*
	$("#statsTable").dataTable({
		"bPaginate":false,
		"bFilter":false,
		"bInfo":false,
		"aaSorting":[],
		"aoColumnDefs": [
			{"bSortable":false, "aTargets":[3,4]},
			{"sType":"html", "aTargets": [1,2]},
			{"sType":"num-html", "aTargets":[0,3,5,6,7,8]},
			{"aDataSort":[1,0,2], "aTargets":[0,1]},
			{"aDataSort":[1,5], "aTargets":[5]}
	    ],
		"fnDrawCallback":function(settings) {
			//alternate row background color
				
			var prevHole = "";
			var prevTeebox = "";
			var prevPinLocation = "";
			var curRowClass = "scoreAlt";
			$("#statsTable tr").each (function(event, data) {
				if ($(this).children("td").length > 0) {
					var holeNum = $(this).children(".holeNum").children(".hideConsecutive").first().html();
					var teebox = $(this).children(".teeboxes").children(".hideConsecutive").first().html();
					var pinLocation = $(this).children(".pinLocations").children(".hideConsecutive").first().html();
					if (holeNum != prevHole || prevTeebox != teebox || prevPinLocation != pinLocation) {
						if (curRowClass == "") {
							curRowClass = "scoreAlt";
						} else {
							curRowClass = "";
						}
					}
					prevHole = holeNum;
					prevTeebox = teebox;
					prevPinLocation = pinLocation;
					$(this).removeClass();
					$(this).addClass(curRowClass);
				}
				
			});
			
			//hide consecutive column values
			var seen = {};
			$("#statsTable td").each(function() {
				if ($(this).children(".hideConsecutive").length > 0) {
					var index = $(this).index();
					var txt = $(this).children(".hideConsecutive")[0].innerHTML;	
					if (seen[index] == txt) {
						$(this).children(".hideConsecutive").addClass("hidden");
					} else {
						seen[index] = txt;
						$(this).children(".hideConsecutive").removeClass("hidden");
					}
				}
			});
			
			//remove player column coloring - sort breaks it
			if (sortCount > 0) {
				for (var a = 0; a < 10; a++) {
					$(".player" + a).removeClass("player" + a);					
				}
			}
			sortCount++;
		}
	}); */
});

