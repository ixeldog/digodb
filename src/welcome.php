<?php
	session_start();
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	$sessionID = session_id();
	
	/*
	//set course to East Roswell
	if ($_SESSION['currentUserID'] == 100) {
		$_SESSION['selectedCourse'] = 0;
	} */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<!--script type="text/javascript" src="jslib/welcomeController.js"></script-->
	<title>Welcome</title>
</head>
<body>
	<div><?php include "menuItems.php"; ?></div>
	<div class="blockHeader">Welcome</div>
	<ul style="display:inline-block; text-align:left;">
		<li>You are logged in as Guest.</li>
		<li>You have two friends added in your Friends list - Friend1 and Friend2.</li>
		<li>Your course has been selected as East Roswell.</li>
		<li>As a guest you can create and edit scores for today, but not past scores.</li>
		<li>If you want to use the site, Logout and Create New User.</li>
	</ul>
	<div class="blockHeader">What to do?</div>
	<ul style="display:inline-block; text-align:left;">
		<li class="expandLink" id="pastScoresLink">View Past Scores
			<ul class="expandBody" id="pastScoresBody">
				<li><a href="findPastScores.php?searchOrderBy=ORDER_BY_SCORE&amp;PHPSESSID=<?php echo $sessionID; ?>">Order by Score</a> to see best to worst rounds.</li>
				<li><a href="findPastScores.php?PHPSESSID=<?php echo $sessionID; ?>">Order by Date</a> to see past rounds.</li>
				<li><a href="findPastScores.php?findScoresSelectPlayers%5B%5D=100&amp;findScoresSelectPlayers%5B%5D=101&amp;findScoresSelectPlayers%5B%5D=102&amp;PHPSESSID=<?php echo $sessionID; ?>">Select multiple players</a> to compare scores. Winners of rounds are colored in.</li>
				<li>Click the course name/date link to bring up the scorecard for the round.</li>
			</ul>
		</li>
		<li class="expandLink" id="viewStatsLink">View Statistics
			<ul class="expandBody" id="viewStatsBody">
				<li>Hole average, min and max score, and # of scores</li>
				<li><a href="userStats.php?userStatsSelectPlayer%5B%5D=100&amp;statsOrderBy=ORDER_BY_DIFF&amp;PHPSESSID=<?php echo $sessionID; ?>">Order by difficulty</a> to see easiest to hardest hole.</li>
				<li><a href="userStats.php?userStatsSelectPlayer%5B%5D=100&amp;PHPSESSID=<?php echo $sessionID; ?>">Order by Hole #</a></li>
				<li><a href="userStats.php?userStatsSelectPlayer%5B%5D=100&amp;userStatsSelectPlayer%5B%5D=101&amp;userStatsSelectPlayer%5B%5D=102&amp;PHPSESSID=<?php echo $sessionID; ?>">Select multiple players</a> to compare stats. If ordered by Hole #, player with best average is colored in.</li>
			</ul>
		</li>
		<li class="expandLink" id="newRoundLink">Start a new round
			<ul class="expandBody" id="newRoundBody">
				<li>Go to the <?php
					//TODO: copied from menuItems.php
					//if session date is not current date then set it to current date
					$curDate = getDate();
					$userDateArray = preg_split("/[\/]/", $_SESSION['selectedDate']);
					$month = $userDateArray[0];
					$day = $userDateArray[1];
					$year = $userDateArray[2];
					echo "<a href=\"setCoursePlayersDate.php?" . 
						"dateDay=" . $curDate["mday"] . "&amp;dateMonth=" . $curDate["mon"] .
						"&amp;dateYear=" . $curDate["year"] . "&amp;returnPage=index.php&amp;PHPSESSID=" . session_id() . "\">Scorecard</a>";
				?>, set a course, add some friends (or not) and record some scores.</li>
				<li>Clicking the Scorecard menu link resets the scorecard course, players and date.</li>
			</ul>
		</li>
	</ul>
	<div class="blockHeader">&nbsp;</div>
	<div><?php include "menuItems.php"; ?></div>
	<div><br /></div>
</body>
</html>