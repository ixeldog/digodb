<?php
	session_start();
	
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'login/AuthHeader.php';
	require_once $root . "lib/KLogger.php";
	require_once $root . "db/DBConnection.php";
	require_once $root . "db/DBAdapter.php";
	require_once $root . 'utils/golfUtil.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<title>Edit user settings</title>
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="jslib/enableSubmitButton.js"></script>
	<script type="text/javascript" src="jslib/userSettingsController.js"></script>
</head>
<body>
	<?php
		$dbConn = new DBConnection(new KLogger($root . "log/UserSettings.DBConnection.txt", KLogger::DEBUG));
		$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/UserSettings.DBAdapter.txt", KLogger::DEBUG));
	
		$golfUtil = new golfUtil($dbAdapter, new KLogger($root . "log/UserSettings.golfUtil.txt", KLogger::DEBUG));
		$userDisplayName = $_SESSION['playerIdToNameMap'][$_SESSION['currentUserID']];
		
		if (isset($_REQUEST["message"])) {
			//echo $_REQUEST["message"] . "<br />\n";
			echo $_REQUEST["message"] . "\n";
		}
	?>
	
	<div><?php include "menuItems.php"; ?></div>
	
	<div class="blockHeader">User Settings</div>
	<form action="userSettingsActions.php" method="post">
	<div>
	
		<label for="userDisplayName">Scorecard display name:<br />
			<span id="maxCharWarning">(max 10 characters)</span><br />
			<input type="text" id="userDisplayName" name="userDisplayName"
				class="enableSubmitButton" value="<?php echo $userDisplayName; ?>"/>
		</label>
		<input type="hidden" id="currentUserDisplayName" name="currentUserDisplayName" value="<?php echo $userDisplayName; ?>"/>
		<br />
		
		<div class="blockHeader">Change Password</div>
		<label for="oldPassword">Old password:&nbsp;<br />
			<input type="password" id="oldPassword" name="oldPassword" class="enableSubmitButton"/>
		</label>
		<br />
		<label for="changePassword1">New password:&nbsp;<br />
			<input type="password" id="changePassword1" name="changePassword1" class="enableSubmitButton"/>
		</label>
		<br />
		<label for="changePassword2">repeat password:&nbsp;<br />
			<input type="password" id="changePassword2" name="changePassword2" class="enableSubmitButton"/>
		</label>
		<br />
		
		<div class="blockHeader">Reset Password Settings</div>
		<?php
			$curUserID = $_SESSION['currentUserID'];
			if ($result = $dbAdapter->getSecuritySettings($_SESSION['currentUserLoginName'])) {
				$row = $dbAdapter->getRow($result);
				$email = $row['email'];
				$question = $row['question'];
				$answer = $row['answer'];
				echo "<input type=\"hidden\" name=\"curEmail\" value=\"" . $email . "\" />\n";
				echo "<input type=\"hidden\" name=\"curQuestion\" value=\"" . $question . "\" />\n";
				echo "<input type=\"hidden\" name=\"curAnswer\" value=\"" . $answer . "\" />\n";
			}
		?>
		<label for="emailAddress">Email address:&nbsp;<br />
			<input type="text" id="emailAddress" name="emailAddress"
				class="enableSubmitButton" value="<?php echo $email; ?>"/>
		</label>
		<br />
		<label for="securityQuestion">Question:&nbsp;<br />
			<input type="text" id="securityQuestion" name="securityQuestion"
				class="enableSubmitButton" value="<?php echo $question; ?>"/>
		</label>
		<br />
		<label for="securityAnswer">Answer:&nbsp;<br />
			<input type="text" id="securityAnswer" name="securityAnswer"
				class="enableSubmitButton" value="<?php echo $answer; ?>"/>
		</label>
		<br />
		
		<?php
			$html = "<input type=\"submit\" id=\"saveChangesButton\" ";
			if ($_SESSION['currentUserID'] == 100) {
				$html .= "disabled=\"disabled\" value=\"This is a tour. No Changes.\" ";
			} else {
				$html .= "value=\"Submit Changes\"";
			}
			$html .= "/>\n";
			echo $html;
		?>
	</div>
	</form>
	<div class="blockHeader">&nbsp;</div>
	
	<div class="pageFooter"><?php include "menuItems.php"; ?></div>
	
	<?php
		$dbConn->close();
	?>
		
</body>
</html>