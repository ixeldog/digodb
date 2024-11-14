<?php
	$scorecardLink = "";
	//if session date is not current date then set it to current date
	$curDate = getDate();
	//$userDateArray = preg_split("/[\/]/", $_SESSION['selectedDate']);
	$userDateArray = isset($_SESSION['selectedDate']) ?
		preg_split("/[\/]/", $_SESSION['selectedDate']) : array(null, null, null);
	$month = $userDateArray[0];
	$day = $userDateArray[1];
	$year = $userDateArray[2];
	if ($curDate["mon"] != $month ||
		$curDate["mday"] != $day ||
		$curDate["year"] != $year)
	{
		$scorecardLink = "<a class=\"menuItem\" href=\"setCoursePlayersDate.php?" . 
		"dateDay=" . $curDate["mday"] . "&amp;dateMonth=" . $curDate["mon"] .
		"&amp;dateYear=" . $curDate["year"] . "&amp;returnPage=index.php&amp;PHPSESSID=" . session_id() . "\">Scorecard</a>\n";
	} else {
		$scorecardLink = "<a class=\"menuItem\" href=\"index.php?PHPSESSID=" . session_id() . "\">Scorecard</a>\n";
	}
	
	//if tour user show welcome instead of FAQ and show it first in links
	if ($_SESSION['currentUserID'] == 100) {
		echo "<a class=\"menuItem\" href=\"welcome.php?PHPSESSID=" . session_id() . "\">Welcome</a>\n";
		echo $scorecardLink;
		echo "<a class=\"menuItem\" href=\"findPastScores.php?PHPSESSID=" . session_id() . "\">Past Scores</a>\n";
		echo "<a class=\"menuItem\" href=\"userStats.php?PHPSESSID=" . session_id() . "\">Statistics</a>\n";
		echo "<a class=\"menuItem\" href=\"editFriends.php?PHPSESSID=" . session_id() . "\">Friends</a>\n";
		echo "<a class=\"menuItem\" href=\"userSettings.php?PHPSESSID=" . session_id() . "\">Settings</a>\n";
		echo "<a class=\"menuItem\" href=\"contact.php?PHPSESSID=" . session_id() . "\">Contact</a>\n";
		echo "<a class=\"menuItem\" href=\"login/logout.php\">Logout</a>\n";
	} else {
		echo $scorecardLink;
		echo "<a class=\"menuItem\" href=\"findPastScores.php?PHPSESSID=" . session_id() . "\">Past Scores</a>\n";
		echo "<a class=\"menuItem\" href=\"userStats.php?PHPSESSID=" . session_id() . "\">Statistics</a>\n";
		echo "<a class=\"menuItem\" href=\"editFriends.php?PHPSESSID=" . session_id() . "\">Friends</a>\n";
		echo "<a class=\"menuItem\" href=\"userSettings.php?PHPSESSID=" . session_id() . "\">Settings</a>\n";
		echo "<a class=\"menuItem\" href=\"FAQ.php?PHPSESSID=" . session_id() . "\">FAQ/Help</a>\n";
		echo "<a class=\"menuItem\" href=\"contact.php?PHPSESSID=" . session_id() . "\">Contact</a>\n";
		echo "<a class=\"menuItem\" href=\"login/logout.php\">Logout</a>\n";
	}
?>