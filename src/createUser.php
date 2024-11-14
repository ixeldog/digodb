<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<title>Create user account</title>
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="jslib/enableSubmitButton.js"></script>
	<script type="text/javascript" src="jslib/createUserController.js"></script>
</head>

<body onload="document.getElementById('username').focus();">

	<div class="blockHeader">Create User</div>
	<?php
		if (isset($_REQUEST["errorMessage"])) {
			$errorMessage = $_REQUEST["errorMessage"];
			echo "<div style=\"color:#F76464\">Error:&nbsp;" . $errorMessage . "</div>\n";
		}
	?>
	<form action="createUserActions.php" method="post">
		<table>
			<tr><td>username:</td><td><input type="text" id="username" name="username" class="createUserField"/></td></tr>
			<tr><td>password:</td><td><input type="password" id="userPassword" name="userPassword" class="createUserField"/></td></tr>
			<tr><td>repeat password:</td><td><input type="password" id="userPasswordRepeat" name="userPasswordRepeat" class="createUserField"/></td></tr>
		</table>
		<div><input type="submit" id="createUserButton" value="Create new user" /></div>
	</form>
	<div class="blockHeader">&nbsp;</div>
	<div class="pageFooter"><a href="login/login.php">Back to login</a></div>

</body>
</html>