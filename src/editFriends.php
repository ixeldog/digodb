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
	<title>Edit user friends</title>
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
	<script type="text/javascript" src="jslib/enableSubmitButton.js"></script>
	<script type="text/javascript" src="jslib/editFriendsController.js"></script>
</head>
<body>
	<?php
		$dbConn = new DBConnection(new KLogger($root . "log/EditFriends.DBConnection.txt", KLogger::DEBUG));
		$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/EditFriends.DBAdapter.txt", KLogger::DEBUG));
	
		$golfUtil = new golfUtil($dbAdapter, new KLogger($root . "log/EditFriends.golfUtil.txt", KLogger::DEBUG));
		$userDisplayName = $_SESSION['playerIdToNameMap'][$_SESSION['currentUserID']];
		
		if (isset($_REQUEST["message"])) {
			//echo $_REQUEST["message"] . "<br />\n";
			echo $_REQUEST["message"] . "\n";
		}
	?>
	
	<div><?php include "menuItems.php"; ?></div>

	<form action="editFriendsActions.php" method="post">
	<div>
		<input type="hidden" id="currentUserDisplayName" name="currentUserDisplayName" value="<?php echo $userDisplayName; ?>"/>
		<div class="blockHeader">Add Friend</div>
		<a href="whatIsFriend.php?PHPSESSID=<?php echo session_id(); ?>">What is a Friend?</a><br />
		(User login ID):&nbsp;<br />
		<input type="text" id="addFriendName" name="addFriendName" class="enableSubmitButton"/>
		<br />
		<div class="blockHeader">Remove Friend</div>
		<select id="removeFriends" name="removeFriends[]" multiple="multiple" class="enableSubmitButton">
			<?php
				echo $golfUtil->getPeopleCurrentUserHasFriended($_SESSION["currentUserID"], null);
			?>
		</select>
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