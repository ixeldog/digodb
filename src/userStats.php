<?php
	session_start();
	
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	require_once $root . 'utils/golfUtil.php';
	require_once $root . "model/course.php";
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'db/DBConnection.php';
	require_once $root . 'db/DBAdapter.php';
	
	$dbConn = new DBConnection(new KLogger($root . "log/UserStats.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root. "log/UserStats.DBAdapter.txt", KLogger::DEBUG));
	$golfUtil = new golfUtil($dbAdapter, new KLogger($root . "log/UserStats.golfUtil.txt", KLogger::DEBUG));
	
	$log = new KLogger ($root . "log/userStats.txt" , KLogger::DEBUG );
	$golfUtil = new golfUtil($dbAdapter, $log);
	
	//have to determine selected players here to set style for players column
	//(whether to show it or not)
	$selectedPlayers = isset($_REQUEST['userStatsSelectPlayer']) ?
		$_REQUEST['userStatsSelectPlayer'] :
		$_SESSION['selectedPlayers'];
	$selectedPlayerNames = $golfUtil->getPlayerIDToNameMap($selectedPlayers);
	
	//have to get selected course here to determine style
	//for teebox and pin location column (display:none or not)
	$selectedCourse = null;
	if (isset($_REQUEST['userStatsSelectCourse'])) {
		$selectedCourse = $_REQUEST['userStatsSelectCourse'];
	} else if (isset($_SESSION['selectedCourse']) &&
		strlen($_SESSION['selectedCourse']) > 0 &&
		$_SESSION['selectedCourse'] != "NULL") {
		$selectedCourse = $_SESSION['selectedCourse'];
	}
	$course = null;
	$teeboxes = $pinLocations = false;
	if ($selectedCourse != null) {
		$course = new course($dbAdapter, $selectedCourse);
		$teeboxes = count($course->teeboxes) > 0;
		$pinLocations = count($course->pinLocations) > 0;
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<title>Player Statistics</title>
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<link rel="stylesheet" type="text/css" href="styles/scoreStyles.css" />
	<link rel="stylesheet" type="text/css" href="styles/statsStyles.css" />
	
	<style type="text/css">
		/* default (for small widths)*/
		.statsTable { border-collapse:collapse; }
		.teeboxes { <?php if (!$teeboxes) echo "display:none; visibility:hidden;"; ?> }
		.pinLocations { <?php if (!$pinLocations) echo "display:none; visibility:hidden;"; ?> }
		.players { <?php if (count($selectedPlayers) <= 1) echo "display:none; visibility:hidden;" ?> }
		/* hides last column if showing teeboxes, pin locations and players */
		.numScores {<?php
			if ($teeboxes && $pinLocations && count($selectedPlayers) > 1) {
				echo "display:none;";
			}
		?>}
		.hidden { visibility:hidden; } /* used to hide consecutive column values */
		/* for increasing screen sizes (asssuming media queries work) */
		@media only screen and (min-width:500px) {
			.statsTable { border-collapse:separate; }
			td { padding-right:10px; padding-left:10px; }
			.numScores { display:block; } /* shows last column if there is space */
		}
		@media only screen and (min-width:1000px) {
			td { padding-right:15px; padding-left:15px; }
		}
		@media only screen and (min-width:1300px) {
			td { padding-right:30px; padding-left:30px; }
		}
	</style>
	<!--link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css"-->
	<link rel='stylesheet' type="text/css" href="lib/DataTables-1.9.4/media/css/jquery.dataTables.css" />
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="jslib/enableSubmitButton.js"></script>
	<script type="text/javascript" charset="utf8" src="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="jslib/dataTables.numbersWithHTML.js"></script> 
	<script type="text/javascript" src="jslib/userStatsController.js"></script>
</head>

<body>

	<div><?php include "menuItems.php"; ?></div>
	<div class="blockHeader">Select Search Criteria</div>
	<form action="userStats.php" method="post">
	<div>
		<span style="font-weight:bold;">Player(s):</span>
		<select id="userStatsSelectPlayer" name="userStatsSelectPlayer[]" multiple="multiple"
			title="ctrl+click to select multiple" class="enableSubmitButton">
			<?php
				echo "<option value=\"" . $_SESSION['currentUserID'] . "\"";
				//if no users selected yet on this page or have selected users and current user is one
				if (isset($_REQUEST['userStatsSelectPlayer']) == false ||
					isset($selectedPlayerNames[$_SESSION['currentUserID']])) {
					echo " selected=\"selected\"";
				}
				echo ">" . $_SESSION['playerIdToNameMap'][$_SESSION['currentUserID']] . "</option>\n";
				echo $golfUtil->getPeopleWhoHaveFriendedCurrentUser($_SESSION["currentUserID"], $selectedPlayerNames);
			?>
		</select>
		<br />
		<span style="font-weight:bold;">Course:</span>
		<select id="userStatsSelectCourse" name="userStatsSelectCourse"
			class="enableSubmitButton">
			<?php
				//if have a course to filter search by then set it as selected
				if ($selectedCourse != null) {
					echo "<option value=\"\">&nbsp;&nbsp;&nbsp;</option>\n";
				}
				//if don't have a course to filter search by then auto-select "   " blank course
				else {
					echo "<option value=\"\" selected=\"selected\">&nbsp;&nbsp;&nbsp;</option>\n";
				}
				echo $golfUtil->getCoursesSelectBox($selectedCourse);
			?>
		</select>
		<br />
		
		<span style="font-weight:bold;">Teebox:</span>
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
		
		<span style="font-weight:bold;">Order by:</span>
		<select id="statsOrderBy" name="statsOrderBy" class="enableSubmitButton">
			<?php
				$submittedOrderBy = isset($_REQUEST['statsOrderBy']) ?
					$_REQUEST['statsOrderBy'] : null;
				$html = "<option value=\"ORDER_BY_HOLE\" ";
				if ($submittedOrderBy == null ||
					$submittedOrderBy == "ORDER_BY_HOLE") {
					$html .= "selected=\"selected\" ";
				}
				$html .= ">Hole #</option>\n";
				$html .= "<option value=\"ORDER_BY_DIFF\" ";
				if ($submittedOrderBy == "ORDER_BY_DIFF") {
					$html .= "selected=\"selected\" ";
				}
				$html .= ">Difficulty</option>\n";
				echo $html;
			?>
		</select>
		<br />
		<input id="savePlayersAndCourse" type="submit" value="Search for Stats" />
	</div>
	</form>
	<div class="blockHeader">Hole Stats</div>
	<div id="colorLegendAndTableWrapper" style="display:inline-block;">
	<table class="minimalTable" style="width:100%;">
		<tr>
			<td class="scoreMinToAvg" style="width:33%; text-align:left;">below</td>
			<td style="width:33%; text-align:center; font-weight:bold;">Par</td>
			<td class="scoreAvgToMax" style="width:33%; text-align:right;">above</td>
		</tr>
	</table>
	
	<?php
		$playerColors = array();
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
		if (count($selectedPlayers) > 1) {
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
	
	<table border="1" id="statsTable" class="statsTable">
		<?php 
			if ($selectedCourse != null) {
				echo "<thead><tr>";
					echo "<th class=\"holeNum\">#</th>";
					echo "<th class=\"teeboxes\">Tee</th>";
					echo "<th class=\"pinLocations\">Pin</th>";
					echo "<th>Par</th>";
					echo "<th class=\"players\">Player</th>";
					echo "<th class=\"avg\">Avg</th>";
					echo "<th class=\"min\">Min</th>";
					echo "<th class=\"max\">Max</th>";
					echo "<th class=\"numScores\"># scores</th>";
				echo "</tr></thead>\n";
				
				$numRow = 0;
				$curTeebox = "";
				$curPar = "";
				$curHoleNum = "";
				$curPinLocation = "";
				$curRowClass = "";
				$maxAvgUnderPar = .75;
				$maxAvgOverPar = 1;
				$averagesToPar = array();
				//used to determine whether to show 2nd, 3rd player avg (other than just the first)
				//Is set to null after first non-tieing avg found.
				$prevAvg = null;
				$result = $dbAdapter->getPlayerStats($selectedPlayers,
					$selectedCourse,
					$selectedTeeboxes,
					$submittedOrderBy);
				echo "<tbody>\n";
				while ($row = $dbAdapter->getRow($result)) {
					$holeID = $row['hole_id'];
					$hole = $course->holeObjects[$holeID];
					
					/*
					 * Is this a new hole#/teebox/pin location combination?
					 * Used to alternate table row background colors.
					 * Also used to only show first player color.
					 */
					$isNewHoleTeePin = false;
					if ($hole->holeNum != $curHoleNum ||
						($teeboxes && $hole->teebox != $curTeebox) ||
						($pinLocations && $hole->pinLocation != $curPinLocation))
					{
						$isNewHoleTeePin = true;
						$prevAvg = null;
					}
					
					/*
					if ($numRow % 2 == 0) {
						echo "<tr>";
					} else {
						echo "<tr class=\"scoreAlt\">";
					} */
					//done here, but also done in javascript controller
					if ($isNewHoleTeePin) {
						if ($curRowClass == "") {
							$curRowClass = "scoreAlt";
						} else {
							$curRowClass = "";
						}
					}
					echo "<tr class=\"" . $curRowClass . "\">";
					
					echo "<td class=\"holeNum\"><span class=\"hideConsecutive";
					if ($hole->holeNum != $curHoleNum) {
						$curHoleNum = $hole->holeNum;
					} else {
						echo " hidden";
					}
					echo "\">" . $hole->holeNum . "</span></td>";
					
					
					echo "<td class=\"teeboxes teebox" . strtoupper($hole->teebox);
					echo "\"><span class=\"hideConsecutive";
					if ($hole->teebox == $curTeebox) {
						echo " hidden";
					}
					$curTeebox = $hole->teebox;
					echo "\">" . $hole->teebox . "</span></td>";
					
					
					if ($hole->pinLocation == "" || $hole->pinLocation == null) {
						echo "<td class=\"pinLocations\" style=\"visibility:hidden;\"><span class=\"hideConsecutive";
					} else {
						echo "<td class=\"pinLocations\"><span class=\"hideConsecutive";	
					}
					if ($hole->pinLocation != $curPinLocation) {
						$curPinLocation = $hole->pinLocation;
					} else {
						echo " hidden";
					}
					echo "\">" . $hole->pinLocation . "</span></td>";
					
					echo "<td><span class=\"hideConsecutive";
					if ($hole->par != $curPar) {
						$curPar = $hole->par;
					} else {
						echo " hidden";
					}
					echo "\">" . $hole->par . "</span></td>";
					
					//set player background color if player has best avg or is tied
					if ($isNewHoleTeePin || $submittedOrderBy == "ORDER_BY_DIFF" ||
						($row['avg'] == $prevAvg && $prevAvg != null))
					{
						//if current user is one of the selected players
						/*
						if (array_search($_SESSION['currentUserID'], $selectedPlayers) !== false) {
							//current user is always first user (.player0)
							if ($row['player_id'] == $_SESSION['currentUserID']) {
								echo "<td class=\"players player0\">";
							} else if (array_search($row['player_id'], $selectedPlayers) == 0){
								echo "<td class=\"players player" . array_search($_SESSION['currentUserID'], $selectedPlayers) . "\">";
							} else {
								echo "<td class=\"players player" . array_search($row['player_id'], $selectedPlayers) . "\">";
							}
						} else {
							echo "<td class=\"players player" . array_search($row['player_id'], $selectedPlayers) . "\">";
						} */
						echo "<td class=\"players player" . $playerColors[$row['player_id']] . "\">";
						$prevAvg = $row['avg'];
					} else {
						//echo "<td class=\"players player5\">";
						echo "<td class=\"players\">";
						$prevAvg = null;
					}
					echo $selectedPlayerNames[$row['player_id']] . "</td>";
					
					//make score text color proportional to par
					$diff = $row['avg'] - $hole->par;
					$averagesToPar["atp" . $diff] = true; //keys must be string or int
					echo "<td class=\"avgToParIs" . str_replace(".", "", $diff) . "\">";
					echo "<span style=\"font-weight:bold;\">" . $row['avg'] . "</span></td>"; /**/
					
					if ($diff < 0) {
						$diff *= -1;
						if ($diff > $maxAvgUnderPar) {
							$maxAvgUnderPar = $diff;
						}
					} else if ($diff > 0) {
						if ($diff > $maxAvgOverPar) {
							$maxAvgOverPar = $diff;
						}
					}
					
					
					if ($row['min'] < $hole->par) {
						echo "<td><span class=\"scoreMinusParIs-1\">";
					} else if ($row['min'] >= ($hole->par + 1)) {
						echo "<td><span class=\"scoreMinusParIs1\">";
					} else {
						echo "<td><span>";
					}
					echo $row['min'] . "</span></td>";
					
					if ($row['max'] < $hole->par) {
						echo "<td><span class=\"scoreMinusParIs-1\">";
					} else if ($row['max'] >= ($hole->par + 2)) {
						echo "<td><span class=\"scoreMinusParIs2\">";
					} else if ($row['max'] >= ($hole->par + 1)) {
						echo "<td><span class=\"scoreMinusParIs1\">";
					} else {
						echo "<td><span>";
					}
					echo $row['max'] . "</span></td>";
					
					echo "<td class=\"numScores\"><span>" . $row['count'] . "</span></td>";
					
					echo "</tr>\n";
					$numRow++;
				}
				echo "</tbody>\n";
			} else {
				echo "<tr><th>no course selected yet</th></tr>\n";
			}
		?>
	
	</table>
	</div> <!-- end of <div id=colorLegendAndTableWrapper> -->
		
	<?php
		echo "<style type=\"text/css\">\n";
		$maxToReduceBy = 150;
		foreach ($averagesToPar as $a => $b) {
			//make score text color proportional to par
			$red = $green = $blue = 255;
			$curATP = (float) (str_replace("atp", "", $a));
			if ($curATP > 0) {
				$curATPRatio = $curATP / $maxAvgOverPar;
				$green = $blue = 255 - floor($maxToReduceBy * $curATPRatio);
			} else if ($curATP < 0) {
				$curATPRatio = ($curATP * -1) / $maxAvgUnderPar;
				$red = 255 - floor($maxToReduceBy * $curATPRatio);
				$green = 255 - (floor($maxToReduceBy * $curATPRatio * .25));
			}
			echo ".avgToParIs" . str_replace(".", "", $curATP) . "{background-color:rgb(".
				$red . "," . $green . "," . $blue . ");}\n";
		}
		echo "</style>\n";
	?>
	<div class="blockHeader">&nbsp;</div>
	<div class="pageFooter"><?php include "menuItems.php"; ?></div>

</body>
</html>