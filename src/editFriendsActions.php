<?php

	session_start();
	
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	require_once $root . "utils/validator.php";
	require_once $root . 'utils/golfUtil.php';
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'db/DBConnection.php';
	require_once $root . 'db/DBAdapter.php';
	
	$log = new KLogger ( "log/editFriendsActions.txt" , KLogger::DEBUG );
	$dbConn = new DBConnection(new KLogger($root . "log/editFriendsActions.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new Klogger($root . "log/editFriendsActions.DBAdapter.txt", KLogger::DEBUG));
	$golfUtil = new golfUtil($dbAdapter, $log);
	
	$curUserID = $_SESSION["currentUserID"];
	$resultsOfActions = "";
	
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
	}
	
	if (strlen($resultsOfActions) > 0) {
		header( "Location: editFriends.php?message=" . $resultsOfActions);
	} else {
		header( "Location: editFriends.php");
	}
	
	$dbConn->close();
?>