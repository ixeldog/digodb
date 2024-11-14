<?php

	//redirect to login page if not logged in
	if (isset($_SESSION["currentUserID"]) == false) {
		header( "Location: login/logout.php");
		die();
	}
	
?>