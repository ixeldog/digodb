<?php
	session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<title>FAQ/Help</title>
</head>
<body>
	<div><?php include "menuItems.php"; ?></div>
	<div class="blockHeader">FAQ/Help</div>
	<ul style="display:inline-block; text-align:left;">
		<li>How do I record scores for other people?
			<ul>
				<li>To record scores for other people they must have an account, login, and add you as a friend in their <a href="editFriends.php?PHPSESSID=<?php echo session_id(); ?>">Friends</a> page.</li>
				<li>Then they will show up in the scorecard setup Friend(s) drop down box.</li>
			</ul>
		</li>
		<li>How do I record two different rounds in the same day?
			<ul>
				<li>Unless you're playing two different courses, you can't.</li>
				<li>To record two different rounds on the same course you would have to change the date:
					<ul>
						<li>You can set the date to any day you don't already have a round saved on, but I would recommend either the previous day or the next.</li>
					</ul>
				</li>
			</ul>
		</li>
	</ul>
	<div class="blockHeader">&nbsp;</div>
	<div class="pageFooter"><?php include "menuItems.php"; ?></div>
</body>
</html>