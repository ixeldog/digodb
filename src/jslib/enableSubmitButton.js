//pulse form submission buttons when values change
var buttonPulseID = null;
var buttonPulseDuration = 750;
var buttonPulseOpacity = .25;

function startButtonPulse(elementID) {
	//if button pulse loop already started
	if (buttonPulseID != null) {
		stopButtonPulse(buttonPulseID);
	}
	buttonPulseID = elementID;
	$("#" + elementID).fadeTo(buttonPulseDuration, buttonPulseOpacity, pulseButton);
}

function stopButtonPulse(elementID) {
	$("#" + buttonPulseID).stop(true);
	$("#" + buttonPulseID).css("opacity", 1);
	buttonPulseID = null;
}

function pulseButton() {
	var curOpacity = $("#" + buttonPulseID).css("opacity");
	var newOpacity = buttonPulseOpacity;
	if (curOpacity == buttonPulseOpacity) {
		newOpacity = 1;
	}
	$("#" + buttonPulseID).fadeTo(buttonPulseDuration, newOpacity, pulseButton);
}

function enableSubmitButton(buttonID) {
	//$("#" + buttonID).prop("disabled", false);
	$("#" + buttonID).show();
	$("#" + buttonID).addClass("enabledSubmitButton");
	$("#" + buttonID).click(function() {
		//$("#" + buttonID).removeClass("enabledSubmitButton");
		stopButtonPulse(buttonID);
	})
	if (buttonPulseID == null || buttonPulseID != buttonID) {
		startButtonPulse(buttonID);
	}
}

function disableSubmitButton(buttonID) {
	stopButtonPulse(buttonID);
	$("#" + buttonID).removeClass("enabledSubmitButton");
	//$("#" + buttonID).prop("disabled", true);
	$("#" + buttonID).hide();
	buttonPulseID = null;
}
