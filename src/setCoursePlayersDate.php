<?php
	session_start();
	
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	require_once $root . 'lib/KLogger.php';
	require_once $root . "db/DBConnection.php";
	require_once $root . "db/DBAdapter.php";
	require_once $root . "utils/golfUtil.php";
	
	$log = new KLogger ( "log/setCoursePlayersDate.txt" , KLogger::DEBUG );
	$logPrefix = "setCoursePlayersDate: ";
	
	//connect to database
	$dbConn = new DBConnection(new KLogger($root . "log/SetCoursePlayersDate.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/SetCoursePlayersDate.DBAdapter.txt", KLogger::DEBUG));
	$golfUtil = new golfUtil($dbAdapter, new KLogger($root . "log/SetCoursePlayersDate.GolfUtil.txt", KLogger::DEBUG));
	
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
	
	$_SESSION['selectedCourse'] = $_REQUEST['selectCourse'];
	$_SESSION['selectedPlayers'] = $_REQUEST['selectPlayers'];
	//add ID of logged in user to selected players
	if (isset($_SESSION['selectedPlayers'])) {
		array_push($_SESSION['selectedPlayers'], $_SESSION['currentUserID']);
	} else {
		$_SESSION['selectedPlayers'] = array($_SESSION['currentUserID']);
	}
	
	$day = $_REQUEST['dateDay'];
	$month = $_REQUEST['dateMonth'];
	$year = $_REQUEST['dateYear'];
	//validate date
	//if (strtotime($month . "/" . $day . "/" . $year) != false) {
	if (checkdate($month, $day, $year)) {
		$_SESSION["selectedDate"] = $month . "/" . $day . "/" . $year;
	}
	
	//create map of selected player IDs to their display names
	$selectedPlayers = $_SESSION['selectedPlayers'];
	$_SESSION['playerIdToNameMap'] = $golfUtil->getPlayerIDToNameMap($selectedPlayers);
	
	$dbConn->close();
	
	$log->LogDebug("finished\n");
	header( "Location: " . $_REQUEST['returnPage'] . "?PHPSESSID=" . session_id());
?>