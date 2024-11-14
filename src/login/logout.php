<?php
	session_start();
	
	//delete any scores that tour user might have created
	if (isset($_SESSION['currentUserID']) &&
		$_SESSION['currentUserID'] == 100)
	{
		$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
		require_once($root . 'db/DBConnection.php');
		require_once($root . "lib/KLogger.php");
		$db = new DBConnection(new KLogger($root . "log/login.DBConnection.txt", KLogger::DEBUG));
		$db->query("delete from score where to_days(curdate()) = to_days(time) " . 
			"and (player_id = 100 or player_id = 101 or player_id = 102)");
	}
	
	// resets the session data for the rest of the runtime
	$_SESSION = array();
	// sends as Set-Cookie to invalidate the session cookie
	if (isset($_COOKIES[session_name()])) { 
	    $params = session_get_cookie_params();
	    setcookie(session_name(), '', 1, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
	}
	session_destroy();
	header( "Location: login.php");
?>