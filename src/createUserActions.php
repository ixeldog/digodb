<?php
	session_start();

	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'utils/validator.php';
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'db/DBConnection.php';
	require_once $root . "db/DBAdapter.php";
	
	$log = new KLogger ( "log/createUserActions.txt" , KLogger::DEBUG );
	$logPrefix = "createUserActions.php: ";
	
	$dbConn = new DBConnection(new KLogger($root . "log/CreateUserActions.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/CreateUserActions.DBAdapter.txt", KLogger::DEBUG));
	
	//validate username and password
	$username = "";
	$userPassword = "";
	$userPasswordRepeat = "";
	$invalidInput = false;
	$errorMessage = "";
	if (isset($_REQUEST["username"]) == false || isset($_REQUEST["userPassword"]) == false || isset($_REQUEST["userPasswordRepeat"]) == false) {
		$invalidInput = true;
		$errorMessage = "Empty value found. Username and password cannot be blank.";
	} else {
		$username = $_REQUEST["username"];
		$userPassword = strtolower($_REQUEST["userPassword"]); //passwords are case-insensitive
		$userPasswordRepeat = $_REQUEST["userPasswordRepeat"];
		
		//validate username
		if (validator::validateString($username) == false) {
			$invalidInput = true;
			$errorMessage = "invalid character found in username. Only letters and numbers allowed";
		}

		//validate userPassword
		if (validator::validateString($userPassword) == false) {
			$invalidInput = true;
			$errorMessage = "invalid character found in password. Only letters and numbers allowed.";
		}
		
		//validate userPasswordRepeat
		if (validator::validateString($userPasswordRepeat) == false) {
			$invalidInput = true;
			$errorMessage = "invalid character found in password. Only letters and numbers allowed.";
		}
		
		if ($invalidInput == false && $userPassword != $userPasswordRepeat) {
			$invalidInput = true;
			$errorMessage = "Passwords did not match. Please try again.";
		}
	}
	
	if ($invalidInput) {
		header( "Location: createUser.php?errorMessage=" . $errorMessage);
		die();
	}
	
	//if username is greater than display name max limit: username <= 30 chars but userDisplayName <= 10 chars
	//then limit display name to max length
	$userDisplayName = $username;
	if (strlen($userDisplayName) > 10) {
		$userDisplayName = substr($userDisplayName, 0, 10);
	}
	
	//create new user
	$result = $dbAdapter->createuser($userDisplayName, $username, $userPassword);
	if ($result == false) {
		$log->LogError("error creating user: displayName=" . $userDisplayName . " username=" . $username . " pass=" . $userPassword);
		header( "Location: createUser.php?errorMessage=Create%20Failed.%20Try%20different%20user%20name");
		die();
	}
	
	//TODO: same as loginActions.php?
	$result = $dbAdapter->checkLogin($username, $userPassword);
	if (($row = $dbAdapter->getRow($result)) != false) {
		foreach ($_SESSION as $a => $b) {
				unset($_SESSION[$a]);
		}
		$_SESSION["currentUserID"] = $row['player_id'];
		$_SESSION["currentUserLoginName"] = $row["player_login_name"];
		header( "Location: setCoursePlayersDate.php?returnPage=editFriends.php&PHPSESSID=" . session_id());
		die();
	} else {
		header( "Location: createUser.php?errorMessage=Create%20Failed.Try%20different%20user%20name");
		die();
	} /**/
	
	$dbAdapter->close();

?>