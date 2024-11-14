<html>

<body>

<?php
	/*
	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . "db/DBConnection.php";
	$test = new DBConnection(null);
	$test->close(); */
	
	$curDate = getDate();
	echo $curDate["mon"] . "/" . $curDate["mday"] . "/" . $curDate["year"] . " "
		. $curDate["hours"] . ":" . $curDate["minutes"] . ":" . $curDate["seconds"];
?>

	<!--table border="1">
		<tr><th>hello</th><th>world</th></tr>
		<a href="http://www.yahoo.com"><tr><td>one</td><td><a href="http://www.google.com">two</a></td></tr></a>
	</table-->

</body>
</html>