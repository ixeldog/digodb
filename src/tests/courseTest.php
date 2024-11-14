<html>
<body>
	
	<?php
	
		$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
		require_once($root . "model/course.php");
		require_once($root . "db/DBConnection.php");
		require_once($root . "db/DBAdapter.php");
		require_once($root . "lib/KLogger.php");
		
		$dbConn = new DBConnection(new KLogger($root . "log/courseTest.DBConnection.txt", KLogger::DEBUG));
		$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/courseTest.DBAdapter.txt", KLogger::DEBUG));
		
		$courseID = 1;
		if (isset($_REQUEST['courseID'])) {
			$courseID = $_REQUEST['courseID'];
		}
		
		$course = new course($dbAdapter, $courseID);
		echo $course->toHTML();
	?>
	
</body>
</html>