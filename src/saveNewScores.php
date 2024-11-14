<?php
	session_start();

	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php' ;
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'db/DBConnection.php';
	require_once $root . 'db/DBAdapter.php';
	
	$log = new KLogger ( "log/SaveNewScores.txt" , KLogger::DEBUG );
	$dbConn = new DBConnection(new KLogger($root . "log/SaveNewScores.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/SaveNewScores.DBConnection.txt", KLogger::DEBUG));
	
	//enables debugging output
	$debug = true;
	if ($debug) {
		foreach ($_REQUEST as $a => $b) {
			$log->LogDebug("\$_REQUEST[" . $a . "] = " . $b . "\n");
		}
		foreach ($_SESSION as $a => $b) {
			$log->LogDebug("\$_SESSION[" . $a . "] = " . $b . "\n");
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
	
	//do not allow save new scores on past date if tour user
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
	
	if ($allowEdit && $selectedHole != null && $selectedHole != "NULL") {
		foreach ($selectedPlayers as $a) {
			if (isset($_REQUEST["playerScore" . $a]) && $_REQUEST["playerScore" . $a] != "NULL") {
				$result = $dbAdapter->addScore($a, $selectedHole, $_SESSION['selectedDate'], $_REQUEST['playerScore' . $a]);
				if ($result == false) {
					$log->LogDebug("ERROR saving player " . $a . " score " . $b . "\n");
				}
			}
		}
	}
	
	$dbAdapter->close();
	$log->LogDebug("\n\n");
	header( "Location: index.php?PHPSESSID=" . session_id());
?>
