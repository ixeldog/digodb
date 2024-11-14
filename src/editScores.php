<?php
	session_start();

	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php' ;
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'db/DBConnection.php';
	require_once $root . 'db/DBAdapter.php';
	
	$log = new KLogger ( "log/editScores.txt" , KLogger::DEBUG );
	$dbConn = new DBConnection(new KLogger($root . "log/EditScores.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/EditScores.DBAdapter.txt", KLogger::DEBUG));
	
	//enables debugging output
	$debug = true;
	if ($debug) {
		foreach ($_REQUEST as $a => $b) {
			$log->LogDebug("\$_REQUEST[" . $a . "] = " . $b);
		}
		foreach ($_SESSION as $a => $b) {
			$log->LogDebug("\$_SESSION[" . $a . "] = " . $b);
		}
	}
	
	//course selected by user = COURSE.ID
	$selectedCourse = "NULL";
	//hole selected by user = HOLE.ID
	$selectedHole = "NULL";
	//array of players selected = PLAYER.ID
	$selectedPlayers = array();

	if (isset($_SESSION['selectedCourse']))
		$selectedCourse = $_SESSION['selectedCourse'];
		
	if (isset($_SESSION['selectedPlayers']))
		$selectedPlayers = $_SESSION['selectedPlayers'];
	
	if (isset($_REQUEST['selectHole'])) {
		$selectedHole = $_REQUEST['selectHole'];
	}
	
	//do not allow edit past scores if tour user
	$allowEdit = false;
	$curDate = getDate();
	$userDateArray = preg_split("/[\/]/", $_SESSION['selectedDate']);
	$month = $userDateArray[0];
	$day = $userDateArray[1];
	$year = $userDateArray[2];
	if ($_SESSION['currentUserID'] != 100 ||
		($curDate["mon"] == $month &&
		$curDate["mday"] == $day &&
		$curDate["year"] == $year))
	{
		$allowEdit = true;
	}
	
	//if there are any scores to edit then save new score to DB
	if ($allowEdit && $selectedCourse != null && $selectedCourse != "NULL") {
		foreach ($selectedPlayers as $a) {
			if (isset($_REQUEST["scoreEditSelectHole" . $a]) && $_REQUEST["scoreEditSelectHole" . $a] != "NULL") {
				$newValue = $_REQUEST["scoreEditSelectScore" . $a];
				
				//score edit is either a delete or a change score, teebox, or pin location
				$result = false;
				if ($newValue == "delete") {
					$result = $dbAdapter->deleteScores($_REQUEST["scoreEditSelectHole" . $a]);
				} else if (strpos($newValue, "_tee_") !== false) {
					$tee = str_replace("_tee_", "", $newValue);
					$result = $dbAdapter->changeScoreTeebox($_REQUEST["scoreEditSelectHole" . $a], $tee);
				} else if (strpos($newValue, "_pin_") !== false) {
					$pin = str_replace("_pin_", "", $newValue);
					$result = $dbAdapter->changeScorePinLocation($_REQUEST["scoreEditSelectHole" . $a], $pin);
				} else {
					$result = $dbAdapter->editScore($_REQUEST["scoreEditSelectHole" . $a], $newValue);
				}
				if ($result == false)
					$log->LogDebug("ERROR updating score id " . $_REQUEST["scoreEditSelectHole" . $a] . " newScore = " . $newValue);
			}
		}
	}
	
	$dbAdapter->close();
	$log->LogDebug("\n");
	header( "Location: index.php?PHPSESSID=" . session_id());

?>
