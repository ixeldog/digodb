<?php

	session_start();
	
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	require_once $root . "utils/validator.php";
	require_once $root . 'utils/golfUtil.php';
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'db/DBConnection.php';
	require_once $root . 'db/DBAdapter.php';
	
	$log = new KLogger ( "log/userSettingsActions.txt" , KLogger::DEBUG );
	$dbConn = new DBConnection(new KLogger($root . "log/UserSettingsActions.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new Klogger($root . "log/UserSettingsActions.DBAdapter.txt", KLogger::DEBUG));
	$golfUtil = new golfUtil($dbAdapter, $log);
	
	$curUserID = $_SESSION["currentUserID"];
	$resultsOfActions = "";
	
	//update user display name if set
	if (isset($_REQUEST["userDisplayName"]) && $_REQUEST["userDisplayName"] != $_REQUEST["currentUserDisplayName"]) {
		if (validator::validateString($_REQUEST["userDisplayName"]) == false) {
			$errorMessage = "Invalid character found in new display name. Only letters and numbers are allowed";
			header( "Location: userSettings.php?message=" . $errorMessage);
			die();
		}
		
		if ($dbAdapter->updatePlayerDisplayName($curUserID, $_REQUEST["userDisplayName"])) {
			$resultsOfActions .= "Updated display name<br />";
			$_SESSION['playerIdToNameMap'] = $golfUtil->getPlayerIDToNameMap($_SESSION['selectedPlayers']);
		} else {
			$resultsOfActions .= "Error changing display name<br />";
		}
	}
	/*
	//remove friend
	if (isset($_REQUEST["removeFriends"])) {
		if ($dbAdapter->removeFriend($curUserID, $_REQUEST['removeFriends'])) {
			$resultsOfActions .= "Removed friend(s)<br />";
		} else {
			$resultsOfActions .= "Error removing friend(s)<br />";
		}
	}
	
	//add friend
	if (isset($_REQUEST["addFriendName"]) && strlen($_REQUEST["addFriendName"]) > 0) {
		$newFriend = $_REQUEST["addFriendName"];
		
		//convert new friend name to player_ID only if not already friends
		$newFriendID = null;
		$result = $dbAdapter->getNewFriendID($curUserID, $newFriend);
		if ($result != false) {
			$row = $dbAdapter->getRow($result);
			if ($row != null) {
				$newFriendID = $row["player_id"];
			}
		}
		
		//if new friend name doesn't equal name of current user
		if ($newFriendID != null && $curUserID != $newFriendID) {
			if ($dbAdapter->addFriend($curUserID, $newFriendID)) {
				$resultsOfActions .= "Friend(s) added<br />";
			} else {
				$resultsOfActions .= "Error adding friend<br />";
			}
		}
	} */
	
	//set new password
	$oldPassword = $_REQUEST["oldPassword"];
	$password1 = $_REQUEST["changePassword1"];
	$password2 = $_REQUEST["changePassword2"];
	if (strlen($oldPassword) > 0 && strlen($password1) > 0 && strlen($password2) > 0) {
		if ($password1 != $password2) {
			$resultsOfActions .= "New passwords do not match<br />";
		} else if (validator::validateString($password1) == false ||
			validator::validateString($password2) == false)
		{
			$resultsOfActions .= "New passwords not valid<br />";
		} else if ($dbAdapter->changePassword($_SESSION['currentUserLoginName'], $oldPassword, $password1) == false) {
			$resultsOfActions .= "Error changing password<br />"; 
		} else {
			$resultsOfActions .= "Password changed<br />";
		}
	}
	
	//set email address, security question and answer
	$email = $_REQUEST['emailAddress'];
	$question = $_REQUEST['securityQuestion'];
	$answer = $_REQUEST['securityAnswer'];
	//if have values for email, question and answer 
	if (strlen($email) > 0 && strlen($question) > 0 && strlen($answer) > 0) {
		//if one of the values has an invalid character
		if (validator::validateString($email) == false)  {
			$resultsOfActions .= "Invalid character found in email. Only characters and numbers allowed<br />";
		} else if (validator::validateString($question) == false) {
			//$resultsOfActions .= "Invalid character found in question. Only characters and numbers allowed<br />\n";
			$resultsOfActions .= "'" . $question . "'";
		} else if (validator::validateString($answer) == false) {
			$resultsOfActions .= "Invalid character found in answer. Only characters and numbers allowed<br />";
		//all values are set and are valid
		} else if ($dbAdapter->setSecuritySettings($_SESSION['currentUserID'], $email, $question, $answer) == false) {
			$resultsOfActions .= "Error saving security settings<br />";
		} else if (($email != $_REQUEST['curEmail']) ||
			($question != $_REQUEST['curQuestion']) ||
			($answer != $_REQUEST['curAnswer'])) {
			$resultsOfActions .= "Saved security settings<br />";
		}
	//if at least one field is emtpy
	} else if (strlen($email) > 0 || strlen($question) > 0 || strlen($answer) > 0) {
		if (strlen($email) == 0) {
			$resultsOfActions .= "No email address given<br />";
		}
		if (strlen($question) == 0) {
			$resultsOfActions .= "No security question given<br />";
		}
		if (strlen($answer) == 0) {
			$resultsOfActions .= "No security answer given<br />";
		}
	}
	
	if (strlen($resultsOfActions) > 0) {
		header( "Location: userSettings.php?message=" . $resultsOfActions);
	} else {
		header( "Location: userSettings.php");
	}
	
	$dbConn->close();
?>