<?php
	session_start();
	
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	require_once $root . 'lib/KLogger.php';
	require_once $root . "db/DBConnection.php";
	require_once $root . "db/DBAdapter.php";
	require_once $root . 'utils/golfUtil.php';
	require_once $root . "model/player.php";
	
	$log = new KLogger($root . "log/findPastScores.txt" , KLogger::DEBUG );
	$logPrefix = "findPastScores: ";
	$dbConn = new DBConnection(new KLogger($root . "log/FindPastScores.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/FindPastScores.DBAdapter.txt", KLogger::DEBUG));
	$golfUtil = new golfUtil($dbAdapter, new KLogger($root . "log/FindPastScores.golfUtil.txt", KLogger::DEBUG));
	
	foreach ($_REQUEST as $a => $b) {
		if (is_array($b)) {
			$log->LogDebug($logPrefix . "\$_REQUEST[" . $a . "] = " . implode(",", $b));
		} else {
			$log->LogDebug($logPrefix . "\$_REQUEST[" . $a . "] = " . $b);
		}
	}
	foreach ($_SESSION as $a => $b) {
		if (is_array($b)) {				
			$log->LogDebug($logPrefix . "\$_SESSION[" . $a . "] = " . implode(",", $b));
		} else {
			$log->LogDebug($logPrefix . "\$_SESSION[" . $a . "] = " . $b);
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<link rel="stylesheet" type="text/css" href="styles/scoreStyles.css" />
	<link rel="stylesheet" type="text/css" href="styles/statsStyles.css" />
	
	<!-- should course and date be on two lines or one? -->
	<style type="text/css">
		.courseNameInLink { display:inline; }
		@media only screen and (min-width:500px) {
			.courseNameInLink { display:none; }
		}
	</style>
	
	<title>Find past scores</title>
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="jslib/enableSubmitButton.js"></script>
	<script type="text/javascript" src="jslib/findPastScoresController.js"></script>
</head>

<body>

	<div><?php include "menuItems.php"; ?></div>
	<div class="blockHeader">Select Search Criteria</div>
	<!--select players-->
	<form action="findPastScores.php" method="post">
	<div>
		<label for="findScoresSelectPlayers" style="font-weight:bold;">Player(s):</label>
		<select id="findScoresSelectPlayers" name="findScoresSelectPlayers[]" multiple="multiple"
			title="ctrl+click to select multiple" class="enableSubmitButton">
			<?php
				$selectedPlayers = isset($_REQUEST['findScoresSelectPlayers']) ?
					$_REQUEST['findScoresSelectPlayers'] :
					$_SESSION['selectedPlayers'];
				$selectedPlayerNames = $golfUtil->getPlayerIDToNameMap($selectedPlayers);
				echo "<option value=\"" . $_SESSION['currentUserID'] . "\"";
				//if no users selected yet on this page or have selected users and current user is one
				if (isset($_REQUEST['findScoresSelectPlayers']) == false ||
					isset($selectedPlayerNames[$_SESSION['currentUserID']])) {
					echo " selected=\"selected\"";
				}
				echo ">" . $_SESSION['playerIdToNameMap'][$_SESSION['currentUserID']] . "</option>\n";
				echo $golfUtil->getPeopleWhoHaveFriendedCurrentUser($_SESSION["currentUserID"], $selectedPlayerNames);
			?>
		</select>
		<br />
		<label for="searchByCourse" style="font-weight:bold;">Course:</label>
		<select id="searchByCourse" name="searchByCourse" class="enableSubmitButton">
			<?php
				$selectedCourse = null;
				if (isset($_REQUEST['searchByCourse']) && strlen($_REQUEST['searchByCourse']) > 0) {
					$selectedCourse = $_REQUEST['searchByCourse'];
					$selectedCourse = ($selectedCourse == "ALL_COURSES") ? "" : $selectedCourse;
				} else if (isset($_SESSION['selectedCourse']) &&
					strlen($_SESSION['selectedCourse']) > 0 &&
					$_SESSION['selectedCourse'] != "NULL") {
					$selectedCourse = $_SESSION['selectedCourse'];
				}
				//if have a course to filter search by
				if ($selectedCourse != null) {
					echo "<option value=\"ALL_COURSES\">All Courses</option>\n";	
				}
				//if don't have a course to filter search by
				else {
					echo "<option value=\"ALL_COURSES\" selected=\"selected\">All Courses</option>\n";
				}
				echo $golfUtil->getCoursesSelectBox($selectedCourse);
			?>
		</select>
		<br />
		
		<label for="searchByTeebox" style="font-weight:bold;">Teebox:</label>
		<select id="searchByTeebox" name="searchByTeebox[]" class="enableSubmitButton"
			title="ctrl+click to select multiple" multiple="multiple">
			<?php
				$selectedTeeboxes = array();
				//if first page load || All was selected last screen
				//and All was the only thing selected last and now it isn't
				if (isset($_REQUEST['searchByTeebox']) == false ||
					(in_array("ALL", $_REQUEST['searchByTeebox']) &&
					isset($_REQUEST['wasAllSelectedLast']) == false))
				{
					$selectedTeeboxes[] = "ALL";
					echo "<option value=\"ALL\" selected=\"selected\">All</option>\n";
				} else {
					echo "<option value=\"ALL\">All</option>\n";
				}
				
				if (isset($_REQUEST['searchByTeebox']) &&
					in_array("99", $_REQUEST['searchByTeebox']))
				{
					$selectedTeeboxes[] = 99;
					echo "<option value=\"99\" selected=\"selected\">None</option>\n";
				} else {
					echo "<option value=\"99\">None</option>\n";
				}
				
				$result = $dbAdapter->getTeeboxes();
				while ($row = $dbAdapter->getRow($result)) {
					//$teeboxName = str_replace("NONE", "None", $row['teebox_name']);
					$teeboxName = $row['teebox_name'];
					if ($teeboxName != "NONE") {
						if (isset($_REQUEST['searchByTeebox']) &&
							in_array($row['teebox_id'], $_REQUEST['searchByTeebox']))
						{
							$selectedTeeboxes[] = $row['teebox_id'];
							echo "<option value=\"" . $row['teebox_id'] . "\" selected=\"selected\">" .
								$teeboxName . "</option>\n";
						} else {
							echo "<option value=\"" . $row['teebox_id'] . "\">" .
								$teeboxName . "</option>\n";
						}
					}
					
				}
			?>
		</select>
		<?php
			if (in_array("ALL", $selectedTeeboxes)) {
				if (count($selectedTeeboxes) == 1) {
					echo "<input type=\"hidden\" name=\"wasAllSelectedLast\" value=\"true\" />\n";					
				}
				$selectedTeeboxes = array();
			}
		?>
		<br />
		
		<label for="searchOrderBy" style="font-weight:bold;">Order by:</label>
		<select id="searchOrderBy" name="searchOrderBy" class="enableSubmitButton">
			<?php
				$submittedOrderBy = isset($_REQUEST['searchOrderBy']) ?
					$_REQUEST['searchOrderBy'] : null;
				$html = "<option value=\"ORDER_BY_DATE\"";
				if ($submittedOrderBy == "ORDER_BY_DATE") {
					$html .= " selected=\"selected\"";
				}
				$html .= ">Date</option>";
				$html .= "<option value=\"ORDER_BY_SCORE\"";
				if ($submittedOrderBy == "ORDER_BY_SCORE") {
					$html .= " selected=\"selected\"";
				}
				$html .= ">Score</option>";
				$html .= "<option value=\"ORDER_BY_COURSE\"";
				if ($submittedOrderBy == "ORDER_BY_COURSE") {
					$html .= " selected=\"selected\""; 
				}
				$html .= ">Course</option>";
				echo $html;
			?>
		</select>
		<br />
		
		<input id="submitSearchOptions" type="submit" value="Search for Scores" />
	</div>
	</form>

	<div class="blockHeader">Past Scores</div>
	<div id="colorLegendAndTableWrapper" style="display:inline-block;">
	<table class="minimalTable" style="width:100%;">
		<tr>
			<td class="scoreMinToAvg" style="width:33%; height:100%; text-align:center;">below</td>
			<td style="width:33%; text-align:center; height:100%; font-weight:bold; background-color:#FAFAFA;">Course Avg</td>
			<td class="scoreAvgToMax" style="width:33%; height:100%; text-align:center;">above</td>
		</tr>
	</table>
	
	<?php
		$playerColors = array();
		if (count($selectedPlayers) > 1) {
			$numPlayers = 0;
			foreach ($selectedPlayers as $a => $b) {
				if ($b == $_SESSION['currentUserID']) {
					$playerColors[$b] = 0;
				} else if (isset($playerColors[$_SESSION['currentUserID']]) == false) {
					$playerColors[$b] = $numPlayers + 1;
				} else {
					$playerColors[$b] = $numPlayers;
				}
				$numPlayers++;
			}
			
			echo "<div style=\"width:100%;\">\n";
			if (isset($playerColors[$_SESSION['currentUserID']])) {
				echo "<div class=\"player" . $playerColors[$_SESSION['currentUserID']] . "\" style=\"width:32%; display:inline-block;\">" .
					$selectedPlayerNames[$_SESSION['currentUserID']] . "</div>\n"; 
			}
			foreach ($playerColors as $a => $b) {
				//echo $a . " - " . $b;
				if ($a != $_SESSION['currentUserID']) {
					echo "<div class=\"player" . $b . "\" style=\"width:32%; display:inline-block;\">" .
						$selectedPlayerNames[$a] . "</div>\n";
				}
			}
			echo "</div>\n";
		}
		
	?>
	
	
	<table border="1">
		<thead>
			<tr><th>Course</th><th>Player(s)</th><th>Total (Par) - #</th></tr>
		</thead>
	
		<?php
			$result = $dbAdapter->getPastScores(
				$selectedPlayers,
				$selectedCourse,
				$selectedTeeboxes,
				(isset($_REQUEST['searchOrderBy']) ? $_REQUEST['searchOrderBy'] : null)
			);
			
			$prevDate = null;
			$prevCourse = null;
			$prevCourseID = null;
			$curDate = null;
			$curCourseName = null;
			$curCourseID = null;
			$entryLink = null;
			//$playerNames = array(); // { "57":"Player1", "59":"player 2", "56", "Bob"}
			//$playerTotals = array(); // [ 57, 59, 56 ]
			
			//{ "playerID":[nameHTML, scoreMinusPar, scoreHTML], "42":["<div>...", 3, "<div>..."]}
			$players = array();
			//$playerColors = array();
			$numScores = null;
			$numPlayers = 0;
			
			//used to set colors for scores
			$scores = array(); //$scores[course_name] = [62:true, 64:true, ...]
			$minScores = array(); //$minScores[course_name] == minScore
			$maxScores = array(); //$maxScores[course_name] == maxScore
			
			//used to find total average for course for all shown players 
			$courseTotals = array(); //$courseTotals[course_name] == courseTotal
			$courseNumScores = array(); //$courseNumScores[course_name] == #scores
			
			while ($row = $dbAdapter->getRow($result)) {
				$curCourseID = $row['course_id'];
				$curCourseName = $row['course_name'];
				$curDate = $row['time'];
				
				//if course or date has changed (or if first course/date)
				if ($curCourseName != $prevCourse || $curDate != $prevDate) {
					//if not the first course/date then print previous
					if ($entryLink != null) {
						//echo getTableRow($entryLink, $prevCourse, $prevDate, $playerNames, $playerTotals, $numScores);
						echo getTableRow($entryLink, $prevCourse, $prevDate, $players, $numScores, $playerColors, $prevCourseID);
					}
					
					$dateArray = preg_split("/[-]/", $curDate);
					$entryLink = "setCoursePlayersDate.php?PHPSESSID=" . session_id() . "&amp;returnPage=index.php";
					$entryLink .= "&amp;selectCourse=" . $row['course_id'];
					$entryLink .= "&amp;dateYear=" . $dateArray[0] . "&amp;dateMonth=" . $dateArray[1] . "&amp;dateDay=" . $dateArray[2];
					
					//$playerNames = array();
					//$playerTotals = array();
					$players = array();
					$numScores++;
				}
				$prevCourseID = $curCourseID;
				$prevCourse = $curCourseName;
				$prevDate = $curDate;
				
				//don't need to set logged in user - they will always be added
				if ($row['player_id'] != $_SESSION['currentUserID']) {
					$entryLink .= "&amp;selectPlayers%5B%5D=" . $row['player_id'];
				}
				
				//set values used to color scores
				$scoreMinusPar = $row['score'] - $row['par'];
				if (isset($scores[$curCourseID]) == false) {
					$scores[$curCourseID] = array();
				}
				if (isset($courseTotals[$curCourseID]) == false) {
					$courseTotals[$curCourseID] = 0;
				}
				$courseTotals[$curCourseID] += $scoreMinusPar;
				if (isset($courseNumScores[$curCourseID]) == false) {
					$courseNumScores[$curCourseID] = 0;
				}
				$courseNumScores[$curCourseID] += 1;
				
				//find min and max scores for course
				$scores[$curCourseID][$scoreMinusPar] = true;
				if (isset($minScores[$curCourseID]) == false ||
					$minScores[$curCourseID] > ($scoreMinusPar)) {
					$minScores[$curCourseID] = $scoreMinusPar;
				}
				if (isset($maxScores[$curCourseID]) == false ||
					$maxScores[$curCourseID] < ($scoreMinusPar)) {
					$maxScores[$curCourseID] = ($scoreMinusPar);						
				}
				
				//$playerName = "<div>" . $row['player_display_name'] . "</div>";
				$playerName = $row['player_display_name'];
				$playerTotal = "<div class=\"_" . $curCourseID . "_" . ($scoreMinusPar);
				
				$playerTotal .= "\">&nbsp;" . $row['score'] . " (<span style=\"font-weight:bold;\">";
				$tempTotal = ($scoreMinusPar);
				if ($tempTotal > 0) $tempTotal = "+" . $tempTotal;
				else if ($tempTotal == 0) $tempTotal = "E";
				$playerTotal .= $tempTotal . "</span>) - " . $row['numHoles'] . "</div>";
				$players[$row['player_id']] = array("name"=>$playerName,
					"scoreMinusPar"=>$scoreMinusPar,
					"total"=>$playerTotal);
				/*
				if (isset($playerColors[$row['player_id']]) == false) {
					$playerColors[$row['player_id']] = $numPlayers;
					$numPlayers++;
				} /**/
			}
			//need to display last table row created (if there is one)
			if ($entryLink != null) {
				//echo getTableRow($entryLink, $curCourse, $curDate, $playerNames, $playerTotals, $numScores);
				echo getTableRow($entryLink, $curCourseName, $curDate, $players, $numScores, $playerColors, $curCourseID);
			}

			$log->LogDebug($logPrefix . "finished\n");
			$dbAdapter->close();
			
			//function getTableRow($entryLink, $course, $date, $playerNames, $playerTotals, $numScores) {
			function getTableRow($entryLink, $course, $date, $players, $numScores, $playerColors, $curCourseID) {
				$curClass = "";
				if ($numScores % 2 == 0) {
					$curClass = "scoreAlt";
				}
				$tableRow = "<tr class=\"" . $curClass . "\">";
				$tableRow .= "<td style=\"text-align:center;\">";
				//$tableRow .= "<a href=\"" . $entryLink . "\"><div class=\"courseNameInLink\">" . $course . "&nbsp;</div>" . $date . "</a></td>";
				$tableRow .= "<a href=\"" . $entryLink . "\">" . $course . "&nbsp;<br class=\"courseNameInLink\" />" . $date . "</a></td>";
				//$tableRow .= "<td>" . $playerNames . "</td><td>" . $playerTotals . "</td></tr>\n";
				
				$playerNames = "";
				$playerTotals = "";
				$lowScore = null;
				$lastScoreMinusPar = null;
				$lastPlayerColColor = null;
				$thereBeTies = false;
				$totalPlayersForRow = count($players);
				
				//TODO: same code as at top of page
				$selectedPlayers = isset($_REQUEST['findScoresSelectPlayers']) ?
					$_REQUEST['findScoresSelectPlayers'] :
					$_SESSION['selectedPlayers'];
				$totalPlayersSelected = count($selectedPlayers);
				
				//removes an entry from $players upon every iteration
				$numPlayers = 0;
				for (; count($players) > 0; $numPlayers++) {
					$minKey = null;
					foreach ($players as $a => $b) {
						if ($minKey == null || $b["scoreMinusPar"] < $players[$minKey]["scoreMinusPar"]) {
							$minKey = $a;
						}
					}
					
					if (($totalPlayersForRow > 1 && ($numPlayers == 0 || $players[$minKey]["scoreMinusPar"] == $lowScore)) ||
						($totalPlayersSelected > 1 && isset($_REQUEST['searchOrderBy']) && $_REQUEST['searchOrderBy'] == "ORDER_BY_SCORE"))
					{
						if ($lowScore !== null && $players[$minKey]["scoreMinusPar"] == $lowScore) {
							//echo $players[$minKey]["scoreMinusPar"] . " - " . $lowScore . "<br />";
							$thereBeTies = true;
						}
						$playerNames .= "<div class=\"player" . $playerColors[$minKey] .
							"\">" . $players[$minKey]["name"] . "</div>\n";
						$lowScore = $players[$minKey]["scoreMinusPar"];
						$lastPlayerColColor = "player" . $playerColors[$minKey];
					} else if ($totalPlayersForRow > 1) {
						//$playerNames .= "<div class=\"player5\">" . $players[$minKey]["name"] . "</div>\n";
						$playerNames .= "<div>" . $players[$minKey]["name"] . "</div>\n";
					} else {
						$playerNames .= "<div>" . $players[$minKey]["name"] . "</div>\n";
					}
					$playerTotals .= $players[$minKey]["total"];
					$lastScoreMinusPar = $players[$minKey]["scoreMinusPar"];
					unset($players[$minKey]);
				}
				//if only one player then color entire score table cell (specifically for iphone portrait display)
				//echo $entryLink . " - " . $numPlayers . " - " . $lastPlayerColColor . " - " . $thereBeTies . "<br />";
				if ($numPlayers == 1 && $lastPlayerColColor != null) {
					$tableRow .= "<td class=\"" . $lastPlayerColColor . "\">" . $playerNames . "</td>" . 
						"<td class=\"_" . $curCourseID . "_" . $lastScoreMinusPar . "\">" . $playerTotals . "</td></tr>\n";
				} else if ($numPlayers > 1 && $thereBeTies == false) {
					$tableRow .= "<td class=\"" . $lastPlayerColColor . "\">" . $playerNames . "</td><td>" . $playerTotals . "</td></tr>\n";
				} else if ($numPlayers == 1) {
					$tableRow .= "<td>" . $playerNames . "</td><td class=\"_" . $curCourseID . "_" . $lastScoreMinusPar . "\">" . $playerTotals . "</td></tr>\n";
				} else {
					$tableRow .= "<td>" . $playerNames . "</td><td>" . $playerTotals . "</td></tr>\n";					
				}
				
				//$tableRow .= "<tr class=\"" . $curClass . "\"><td colspan=\"3\" style=\"text-align:right;\"><a href=\"" . $entryLink . "\">" . $prevDate . "</a></td></tr>\n";
				return $tableRow;
			}
		?>
	
	</table>
	</div> <!-- end of <div id=colorLegendAndTableWrapper> -->
	
	<?php
		echo "<style type=\"text/css\">\n";
		foreach ($scores as $a => $b) {
			if (isset($minScores[$a]) && isset($maxScores[$a]) &&
				$minScores[$a] != $maxScores[$a])
			{
				$maxToReduceBy = 150;
				$average = $courseTotals[$a] / $courseNumScores[$a];
				foreach ($b as $c => $d) {
					$red = $green = $blue = 255;
					$diff = $average - $c;
					if ($diff < 0) {
						$diff = (-1 * $diff) / ($maxScores[$a] - $average);
						$green = $blue = 255 - floor($maxToReduceBy * $diff);
					} else if ($diff > 0) {
						$diff = $diff / ($average - $minScores[$a]);
						$red = 255 - floor($maxToReduceBy * $diff);
						$green = 255 - (floor($maxToReduceBy * $diff * .25));
					}
					echo "._" . $a . "_" . $c . "{background-color:rgb(" .
						$red . "," . $green . "," . $blue . ");}\n";
				}
			}
		}
		echo "</style>\n";
	?>
	
	<div class="blockHeader">&nbsp;</div>
	<div class="pageFooter"><?php include "menuItems.php"; ?></div>
</body>
</html>