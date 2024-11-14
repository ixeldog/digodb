<?php
	session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1" />
	<link rel="stylesheet" type="text/css" href="styles/styles.css" />
	<title>What is a Friend?</title>
</head>
<body>
	<a href="editFriends.php?PHPSESSID=<?php echo session_id(); ?>">Back to Edit Friends</a>
	<div class="blockHeader">What is a Friend?</div>
	<ul style="display:inline-block; text-align:left;">
		<li>Someone you've given permission to record your scores for you.</li>
		<li>To record scores for someone else, they must have an account and add you as their friend.</li>
		<li>A friend can view your past scores and stats.</li>
		<li>To add a friend enter their user ID: the name they use to login.</li>
	</ul>
	<div class="blockHeader">&nbsp;</div>
	<a href="editFriends.php?PHPSESSID=<?php echo session_id(); ?>">Back to Edit Friends</a>
	<br /><br />
</body>
</html>