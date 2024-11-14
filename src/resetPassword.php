<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<title>Reset Password</title>
</head>
<body onload="document.getElementById('userName').focus();">
	
	<div><a href="login/login.php">Back to Login</a></div>

	<div class="blockHeader">Reset Password</div>
	
	<form method="post" action="resetPassword.php">
		<div>
			<label>Enter username:&nbsp;<br />
				<input type="text" id="userName" name="userName" value="<?php
						if (isset($_REQUEST['userName'])) {
							echo $_REQUEST['userName'];
						}
					?>"/>
			</label>
			<br />
			<input type="submit" value="Get Question" />
		</div>
	
	<?php
		if (isset($_REQUEST['userName'])) {
			$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
			require_once $root . "db/DBConnection.php";
			require_once $root . "db/DBAdapter.php";
			require_once $root . "lib/KLogger.php";
			
			$dbConn = new DBConnection(new KLogger($root . "log/ResetPassword.DBConnection.txt", KLogger::DEBUG));
			$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/ResetPassword.DBAdapter.txt", KLogger::DEBUG));
		
			if ($results = $dbAdapter->getSecuritySettings($_REQUEST['userName'])) {
				$row = $dbAdapter->getRow($results);
				$email = $row['email'];
				$question = $row['question'];
				$answer = $row['answer'];

				//if any field is blank, cannot automatically reset password
				if (strlen($email) == 0 || strlen($question) == 0 || strlen($answer) == 0) {
					echo "<h3>Security Settings not set.<br />Cannot reset password automatically.<br />" .
						"Email admin@digodb.com</h3>\n";
				//if we have an answer to try and match
				} else if (isset($_REQUEST['answer'])) {
					if ($answer == $_REQUEST['answer']) {
						$newPassword = rand(0, 9) . rand(0, 9) . rand(0, 9);
						if (mail($email, "Password Reset", "Your password is now " . $newPassword, "From: admin@digodb.com\r\n") == false) {
							echo "<h3>An error occurred sending email. Password was not reset.<br />\n" .
								"Please contact admin@digodb.com to reset password.<br />\n";
						} else if ($dbAdapter->setPassword($_REQUEST['userName'], $newPassword) == false) {
							echo "<h3>Error resetting password. Contact admin@digodb.com</h3>\n";
						} else {
							echo "<h3>Password Reset. Email sent. Check your inbox.</h3>\n";
						}
					} else {
						echo "<h3>Answer did not match. Password not reset</h3>\n";
					}
				//else display email, question and answer submission form
				} else {
					echo "<div>Email:&nbsp;" . $email . "</div>\n";
					echo "<div>Question:&nbsp;" . $question . "</div>\n";
					echo "<label>Answer:&nbsp;<input type=\"text\" name=\"answer\" /></label><br />\n";
					echo "<input type=\"submit\" value=\"Reset Password\" /><br />\n";
				}
			} else {
				echo "<h3>Error fetching security settings.</h3>";
			}
			$dbAdapter->close();
		}
	?>
	</form>
	
	<div class="blockHeader">&nbsp;</div>
	<div style="padding-bottom:10px;"><a href="login/login.php">Back to Login</a></div>

</body>
</html>