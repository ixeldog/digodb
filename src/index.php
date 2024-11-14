<?php
	session_start();
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	
	require_once $root . 'db/DBConnection.php';
	require_once $root . 'db/DBAdapter.php';
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'utils/golfUtil.php';
	require_once $root . 'model/scorecard.php';
	require_once $root . 'model/course.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	
	<!--link rel="stylesheet" type="text/css" href="lib/jquery-ui-1.10.2.custom/css/redmond/jquery-ui-1.10.2.custom.css" /-->
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<link rel="stylesheet" type="text/css" href="styles/scoreStyles.css" />
	
	<title>Scorecard</title>
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="jslib/enableSubmitButton.js"></script>
	<script type="text/javascript" src="jslib/indexController.js"></script>
</head>
<body>
	<!--div style="position:absolute; border:2px solid black; height:480px; width:320px;"></div-->
	<?php
		$log = new KLogger ($root . "log/index.txt" , KLogger::DEBUG );
		$logPrefix = "index.php: ";
		$dbConn = new DBConnection(new KLogger($root . "log/Index.DBConnection.txt", KLogger::DEBUG));
		$dbAdapter = new DBAdapter($dbConn, new KLogger($root. "log/Index.DBAdapter.txt", KLogger::DEBUG));
		$golfUtil = new golfUtil($dbAdapter, new KLogger($root . "log/Index.golfUtil.txt", KLogger::DEBUG));
		
		//course selected by user at previous screen = COURSE.ID
		$selectedCourse = null;
		//array of players selected on previous screen = PLAYER.ID
		$newSelectedPlayers = array();
		//type of form submitted/user action
		$submittedFormType = null;
		//array of selected player IDs
		$selectedPlayers = null;
		//map player IDs -> player names: $selectedPlayer[3] = "Cornelius", PID==3,name=="Cornelius"
		$playerIdToNameMap = null;
		//user selected date to save scores to
		$selectedDate = null;
		
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
	
		//if i have a course and at least one player than create hole score entry: hole drop down, player title, player score drop down
		$selectedCourse = "NULL";
		if (isset($_SESSION['selectedCourse']))
			$selectedCourse = $_SESSION['selectedCourse'];
		
		//user selected date - if user has selected a date to record scores for then set it here
		if (isset($_SESSION['selectedDate'])) {
			$selectedDate = $_SESSION['selectedDate'];
		}
		
		$selectedPlayers = $_SESSION['selectedPlayers'];
		$playerIdToNameMap = $_SESSION['playerIdToNameMap'];
		
		//===================================================================================================================
		//===================================================================================================================
		?>
		
		<?php
		//HOLE SELECT AND PLAYER SCORE ENTRY
		//if have course and players then display hole select and player score select
		if ($selectedCourse != "NULL" && $selectedPlayers != -1) {
			
			$course = new course($dbAdapter, $selectedCourse);
			//$scoreCard = new scorecard($dbAdapter, $selectedCourse, $selectedPlayers, $selectedDate);
			$scoreCard = new scorecard($dbAdapter, $course, $selectedPlayers, $selectedDate);
			$log->LogDebug($logPrefix . "scorecard: " . $scoreCard);
			
			echo "<div class=\"myBlock\" id=\"holeSelectScoreInputRefreshForm\">\n";
			echo "<form action=\"saveNewScores.php\" method=\"post\">\n";
			echo "<div>\n";
			
			//create hole selection drop down list
			echo "<label for=\"selectHole\" style=\"font-weight:bold;\">Hole:&nbsp;</label>\n";
			echo "<select id=\"selectHole\" name=\"selectHole\" >\n";
			//echo "<option value=\"NULL\"># - tee - pin</option>\n";
			echo $golfUtil->getHolesForCourseHeader($course);
			//echo $golfUtil->getHolesForCourse($selectedCourse, $scoreCard->nextSelectedHole);
			echo $golfUtil->getHolesForCourse($course, $scoreCard->nextSelectedHole);
			echo "</select>\n<br />\n";
			
			//create player score entry drop down lists: 1, 2, 3, 4, 5, ...
			$scoreOptions = "";
			for ($c = 1; $c < 10; $c++) {
				//if current score is par for next hole or
				//there is no par for next hole and current score is 3 (most probable par for next hole)
				if ($c == $scoreCard->nextSelectedHolePar ||
				($scoreCard->nextSelectedHolePar == null && $c == 3)) {
					$scoreOptions .= "<option class=\"\" value=\"" . $c . "\" selected=\"selected\" >" . $c . "</option>\n";
				} else {
					$scoreOptions .= "<option class=\"\" value=\"" . $c . "\">" . $c . "</option>\n";
				}
			}
			
			echo "<div class=\"hideUntilHoleChosen\">\n";
			echo "<div class=\"blockHeader\" id=\"enterScoresHeader\">Enter Scores</div>\n";
			
			
			echo "<table class=\"minimalTable\"><tr><td><table class=\"minimalTable\">";
			for ($a = 0; isset($scoreCard->playersInOrder[$a]); $a++) {
				$curPlayer = $scoreCard->players[$scoreCard->playersInOrder[$a]];
				//echo $curPlayer->playerName . ":&nbsp;";
				echo "<tr><td class=\"noPad\"><label for=\"playerScore$curPlayer->playerID\">$curPlayer->playerName:&nbsp;</label></td>";
				echo "<td class=\"noPad\">";
				echo "<select id=\"playerScore$curPlayer->playerID\" name=\"playerScore$curPlayer->playerID\" class=\"enterScoreSelect\">\n";
				echo "<option value=\"NULL\">&nbsp;&nbsp;&nbsp;</option>\n";
				echo $scoreOptions;
				echo "</select>\n</td>\n</tr>\n";
			}
			echo "</table></td>";
			echo "<td><input type=\"submit\" value=\"Save\" id=\"saveScoreButton\"/></td>\n";
			echo "</tr></table>\n"; /**/
			echo "</div>"; //end of hideUntilHoleChosen
			
			/*
			for ($a = 0; isset($scoreCard->playersInOrder[$a]); $a++) {
				$curClass = "";
				if (($a % 2) == 1) {
					$curClass = " class=\"scoreAlt\"";
				}
				echo "<span style=\"white-space:nowrap;\"" . $curClass . ">\n";
				$curPlayer = $scoreCard->players[$scoreCard->playersInOrder[$a]];
				echo "<span>" . $curPlayer->playerName . ":&nbsp;</span>\n";
				echo "<select id=\"playerScore" . $curPlayer->playerID . "\" name=\"playerScore" . $curPlayer->playerID . "\" class=\"enterScoreSelect\">\n";
				echo "<option value=\"NULL\">&nbsp;&nbsp;&nbsp;</option>\n";
				echo $scoreOptions;
				echo "</select>\n</span>\n";
			} /**/
			
			echo "</div></form>"; //end of hole selection and score input form
			
			//refresh button
			echo "<form action=\"index.php\" method=\"post\" class=\"hideUntilHoleChosen\">";
			echo "<div><input type=\"submit\" value=\"Refresh scores\" /></div></form>\n";
			echo "</div>\n"; //end id="holeSelectScoreInputRefreshForm"
			
			
			//***************** TOTAL SCORES BLOCK *******************************
			
			//if no nextSelectedHole then we shouldn't have any scores to show
			if ($scoreCard->nextSelectedHole != null) {
				echo "<div class=\"myBlock\" id=\"totalScores\" >\n";
				echo "<div class=\"blockHeader\" id=\"totalScoresHeader\">Total Scores</div>\n";
				echo "<table class=\"minimalTable\" style=\"padding:0px;\">";
				//print player summary lines - # of holes played, total score, ...
				for ($a = 0; isset($scoreCard->playersInOrder[$a]); $a++) {
					$curPlayer = $scoreCard->players[$scoreCard->playersInOrder[$a]];
					$playerScoreToPar = $curPlayer->totalScore - $curPlayer->totalPar;
					if ($playerScoreToPar > 0)
						$playerScoreToPar = "+" . $playerScoreToPar;
					else if ($playerScoreToPar == 0)
						$playerScoreToPar = "E";
	
					
					echo "<tr><td class=\"noPad\">" . $curPlayer->playerName . "&nbsp;-&nbsp;</td>";
					echo "<td class=\"noPad\">" . $curPlayer->totalScore . "</td>";
					echo "<td class=\"noPad\" style=\"text-align:left; font-weight:bold;\">&nbsp;(" . $playerScoreToPar . ")</td>";
					//echo "<td style=\"padding:0px;\"><span style=\"font-weight:bold;\">holes: </span>";
					echo "<td class=\"noPad\">&nbsp;-&nbsp;" . $curPlayer->numScores . "</td>";
					//echo "<td style=\"padding:0px;\"><span style=\"font-weight:bold;\">total: </span></td>";
					
					echo "</tr>"; /**/
					
					/*
					echo $curPlayer->playerName . " - <span style=\"font-weight:bold;\">holes: </span>" . $curPlayer->numScores;
					//echo " - <span style=\"font-weight:bold;\">total: </span>" . $curPlayer->totalScore . " (" . $playerScoreToPar . ")<br />\n";
					echo " - " . $curPlayer->totalScore . " (" . $playerScoreToPar . ")<br />\n";
					/**/
				}
				echo "</table>";
				echo "</div>\n";
			
				//***************** EDIT SCORES BLOCK *******************************
				echo "<div class=\"myBlock\" id=\"editScoresBlock\" >\n";
				echo "<div class=\"blockHeader\" id=\"editScoresHeader\">Edit Scores</div>\n";
				
				echo "<form action=\"editScores.php\" id=\"editScoresForm\" method=\"post\">\n";
				echo "<div>\n";
				for ($a = 0; isset($scoreCard->playersInOrder[$a]); $a++) {
					echo "<div class=\"editScoreBlock\" >\n";
					//"player name:" and select box for holes they have played today
					//echo $selectedPlayers[$a] . ":&nbsp;&nbsp;" . $playerScoreEditHTML[$a];
					$curPlayer = $scoreCard->players[$scoreCard->playersInOrder[$a]];
					//echo $curPlayer->playerName . ":&nbsp;&nbsp;" . $golfUtil->getScoresEditHTML($curPlayer);
					echo "<div style=\"font-weight:bold;\">" . $curPlayer->playerName . "</div>\n";
					echo $golfUtil->getScoresEditHTML($course, $curPlayer);
					
					/*
					//select box for new score or "delete" if they want to just remove score
					echo "<select id=\"scoreEditSelectScore" . $curPlayer->playerID . "\" name=\"scoreEditSelectScore". $curPlayer->playerID . "\" >\n";
					echo "<option value=\"delete\" >delete</option>\n";
					for ($c = 1; $c < 10; $c++) {
						echo "<option value=\"" . $c . "\" >" . $c . "</option>\n";
					}
					echo "</select><br />\n"; 
					 */
					 
					 
					//select box for new score or "delete" if they want to just remove score
					echo "<select id=\"scoreEditSelectScore" . $curPlayer->playerID . "\" name=\"scoreEditSelectScore". $curPlayer->playerID . "\" >\n";
					echo "<option value=\"delete\" >delete</option>\n";
					for ($c = 1; $c < 10; $c++) {
						echo "<option value=\"" . $c . "\" >" . $c . "</option>\n";
					}
					if (count($course->teeboxes) > 0) {
						echo "<optgroup label=\"Tee\">\n";
						foreach ($course->teeboxes as $x => $y) {
							echo "<option value=\"_tee_" . $x . "\">" . $y . "</option>\n";
						}
						echo "</optgroup>\n";
					}
					if (count($course->pinLocations) > 0) {
						echo "<optgroup label=\"Pin\">\n";
						foreach ($course->pinLocations as $b) {
							echo "<option value=\"_pin_" . $b . "\">" . $b . "</option>\n";
						}
						echo "</optgroup>\n";
					}
					echo "</select><br />\n";
					 
					if ($curPlayer->numScores > 0) {
						//echo $curPlayer->getScoresHTML();
						//echo "<div style=\"margin:auto; text-align:center;\">" . $golfUtil->getPlayerScorecard($curPlayer) . "</div>";
						echo $golfUtil->getPlayerScorecard($curPlayer);
					}
					echo "</div>\n";
				}
				echo "</div>\n";
				
				echo "<div>";
				echo "<input id=\"editScoresButton\" type=\"submit\" value=\"Edit score(s)\" />\n";
				echo "</div>\n";
				
				//TODO: only print submit button and form in general if there are scores to edit. if not don't display
				
				//echo "<b>======= Setup ========</b>\n";
				echo "</form>\n";
				echo "</div>\n"; //end id="editScoresForm"
			}
		} //END IF have course and players then display hole select, player score select and Scorecard
	
	?>

	<!-- select course, friends, and date -->
	<div class="myBlock" id="setCourseFriendsDateForm">
	<div class="blockHeader" id="scorecardSetupHeader">Scorecard Setup</div>
	<form action="setCoursePlayersDate.php" method="post">
	<div>
		<input type="hidden" id="returnPage" name="returnPage" value="index.php" />
		<span style="font-weight:bold;">Course:</span>
		<select id="selectCourse" name="selectCourse" class="enableSubmitButton">
			<option id="selectCourseNULL" value="NULL">&nbsp;&nbsp;&nbsp;</option>
			<?php echo $golfUtil->getCoursesSelectBox($selectedCourse); ?>
		</select><br class="showAfterSelectCourse" />
		<span class="showAfterSelectCourse">
			<!--add player-->
			<span style="font-weight:bold;">Friend(s):</span>
			<select id="selectPlayers" name="selectPlayers[]" multiple="multiple"
				title="ctrl+click to select multiple"
				class="enableSubmitButton showAfterSelectCourse">
				<?php
					echo $golfUtil->getPeopleWhoHaveFriendedCurrentUser($_SESSION["currentUserID"], $playerIdToNameMap);
				?>
			</select>
			<br />
			
			<!--select date-->
			<!--span style="font-weight:bold;">Date:&nbsp;<input type="date" data-role="datebox" data-options='{"mode":"calbox"}'/></span-->
			<!--select date-->
			<span style="font-weight:bold;">Date:</span>
			<?php
				$day = null;
				$month = null;
				$year = null;
				
				//if user hasn't selected a date yet (this should not be possible) set to current date
				if ($selectedDate == null) {
					$log->LogWarn($logPrefix + "selectedDate was null. How the hell did that happen?\n");
					$curDate = getDate();
					$day = $curDate["mday"];
					$month = $curDate["mon"];
					$year = $curDate["year"];
				} else {
					//split date upon '/' character
					$selectedDateArray = preg_split("/[\/]/", $selectedDate);
					$day = $selectedDateArray[1];
					$month = $selectedDateArray[0];
					$year = $selectedDateArray[2];
				}
				echo golfUtil::getDateDaySelect($day);
				echo golfUtil::getDateMonthSelect($month);
				echo golfUtil::getDateYearSelect($year);
			?>
			<br />
		</span>
			
		<input type="submit" id="scorecardSetupSubmit" value="Set Course, Friend(s), &amp; Date"/>
	</div>
	</form>
	<div class="blockHeader" id="pageFooterHeader">&nbsp;</div>
	</div>
	
	<!-- menu items -->
	<div class="pageFooter"><?php include "menuItems.php"; ?></div>
	
	<?php
		//close connection to DB
		$dbConn->close();
		$log->LogDebug($logPrefix . "finished\n");
		//$logger->closeLogger();
	?>
	<div id="debugOutput" style="clear:both;"></div>
	
</body>

</html>
