<?php
	
	session_start();
	
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once($root . 'utils/validator.php');
	require_once($root . "lib/KLogger.php");
	require_once($root . 'db/DBConnection.php');
	require_once($root . 'db/DBAdapter.php');
	require_once($root . 'utils/golfUtil.php');
	
	$dbConn = new DBConnection(new KLogger($root . "log/LoginActions.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/LoginActions.DBAdapter.txt", KLogger::DEBUG));
	$golfUtil = new golfUtil($dbAdapter, new KLogger($root . "log/LoginActions.GolfUtil.txt", KLogger::DEBUG));
	
	//validate username and password
	$username = "";
	$userPassword = "";
	$invalidInput = false;
	$errorMessage = "";
	if (isset($_REQUEST["username"]) == false || isset($_REQUEST["userPassword"]) == false ||
		strlen($_REQUEST['username']) == 0 || strlen($_REQUEST['userPassword']) == 0) {
		$invalidInput = true;
		$errorMessage = "Emtpy value found. Username and password cannot be blank.";
	} else {
		$username = $_REQUEST["username"];
		$userPassword = $_REQUEST["userPassword"];
		
		//validate username
		if (validator::validateString($userPassword) == false) {
			$invalidInput = true;
			$errorMessage = "invalid character found in username. Only letters and numbers allowed";
		}
		
		//validate userPassword
		if (validator::validateString($userPassword) == false) {
			$invalidInput = true;
			$errorMessage = "invalid character found in password. Only letters and numbers allowed.";
		}
	}
	
	//if invalid input redirect to login page with error message
	if ($invalidInput) {
		header( "Location: login.php?errorMessage=" . $errorMessage);
		die();
	}
	
	//inputs are valid, but do they match any entry in DB? TODO: same as createUserActions.php
	$result = $dbAdapter->checkLogin($username, strtolower($userPassword));
	$userFound = false;
	if (($row = $dbAdapter->getRow($result)) != false) {
		foreach ($_SESSION as $a => $b) {
				unset($_SESSION[$a]);
		}
		$_SESSION['currentUserLoginName'] = $username;
		$_SESSION['currentUserID'] = $row['player_id'];
		$_SESSION['selectedPlayers'] = array($row['player_id']);
		
		//create map of selected player IDs to their display names - TODO: DUPLICATED FROM setCoursePlayersDate.php
		$selectedPlayers = $_SESSION['selectedPlayers'];
		$_SESSION['playerIdToNameMap'] = $golfUtil->getPlayerIDToNameMap($selectedPlayers);
		
		$curDate = getDate();
		$_SESSION['selectedDate'] = $curDate["mon"] . "/" . $curDate["mday"] . "/" . $curDate["year"];
		
		if ($_REQUEST['jsEnabled'] != null) {
			if ($_REQUEST['jsEnabled'] == 'true' || $_REQUEST['jsEnabled'] == 'TRUE') {
				$_SESSION['jsEnabled'] = true;
			} else {
				$_SESSION['jsEnabled'] = false;
			}
		}
		
		$userFound = true;
	}
	
	$dbAdapter->close();
	
	//user found - redirect to main page
	if ($userFound != false) {
		if ($_SESSION['currentUserID'] != 100) {
			header( "Location: ../index.php?PHPSESSID=" . session_id());
		} else {
			//header( "Location: ../welcome.php?PHPSESSID=" . session_id());
			header( "Location: ../setCoursePlayersDate.php?" . 
				"selectCourse=0&returnPage=welcome.php&" . 
				"PHPSESSID=" . session_id());
		}
		
		die();
	} else {
		header( "Location: login.php?errorMessage=Login%20Failed");
		die();
	} /**/

?>