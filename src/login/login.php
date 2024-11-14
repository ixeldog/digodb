<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="../styles/styles.css" />
	<title>Atlanta Disc Golf Scorecard &amp; Database</title>
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="../jslib/enableSubmitButton.js"></script>
	<script type="text/javascript" src="../jslib/loginController.js"></script>
	<script type="text/javascript">
		<!--
			function init() {
				document.getElementById("username").focus();
				document.getElementById("jsEnabled").value = "true";
			}
		// -->
	</script>
	
</head>
<body onload="init();">

	<div class="blockHeader">Atlanta Disc Golf Scorecard &amp; Database</div>
	<?php
		if (isset($_REQUEST["errorMessage"])) {
			$errorMessage = $_REQUEST["errorMessage"];
			echo "<div style=\"color:#F76464;\">Error:&nbsp;" . $errorMessage . "</div>\n";
		}
	?>
	<form id="userLogin" action="loginActions.php" method="post">
		<table>
			<tr>
				<td><label for="username">username:</label></td>
				<td><input type="text" id="username" name="username" class="loginField"/></td>
			</tr>
			<tr>
				<td><label for="userPassword">password:</label></td>
				<td><input type="password" id="userPassword" name="userPassword" class="loginField"/></td>
			</tr>
			<tr><td colspan="2"><input type="submit" id="loginButton" value="Login" /></td></tr>
		</table>
		<div><input type="hidden" id="jsEnabled" name="jsEnabled" value="false" /></div>
	</form>
	<div id="tourLink" style="padding-top:4px; padding-bottom:4px;">
		<a style="display:block;" href="loginActions.php?username=TestUser&amp;userPassword=password">Tour the site - Login as Guest</a>
	</div>
	
	<div class="blockHeader">&nbsp;</div>
	<div class="pageFooter">
		<a class="menuItem" href="../about.html">FAQ/What is this website?</a>
		<a class="menuItem" href="../createUser.php">Create New User</a>
		<a class="menuItem" href="../resetPassword.php">Forgot Password</a>
	</div>

</body>
</html>