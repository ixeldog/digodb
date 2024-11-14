<html>

<body>

<?php

	$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
	require_once $root . 'lib/KLogger.php';
	require_once $root . 'utils/golfUtil.php';
	require_once $root . 'db/DBConnection.php';
	require_once $root . 'db/DBAdapter.php';
	require_once $root . 'model/course.php';
	
	$dbConn = new DBConnection(new KLogger($root . "log/golfUtilTest.DBConnection.txt", KLogger::DEBUG));
	$dbAdapter = new DBAdapter($dbConn, new KLogger($root . "log/golfUtilTest.DBAdapter.txt", KLogger::DEBUG));
	$logger = new KLogger($root . "log/golfUtilTest.txt", KLogger::DEBUG);
	$golfUtil = new golfUtil($dbAdapter, $logger);
	
	$courseID = 0;
	if (isset($_REQUEST['courseID'])) {
		$courseID = $_REQUEST['courseID'];
	}
	
	echo "<div>getCoursesSelectBox:<br />\n";
	echo "<select>" . $golfUtil->getCoursesSelectBox(null) . "</select></div><br />\n\n";
	
	echo "<div>getPlayersSelectBox:<br />\n<select>";
	echo $golfUtil->getPlayersSelectBox(null) . "</select></div><br />\n\n";
	
	echo "<div>getPeopleWhoHaveFriendedCurrentUser:<br />\n<select>";
	echo $golfUtil->getPeopleWhoHaveFriendedCurrentUser(6, null) . "</select></div><br />\n\n";
	
	echo "<div>getPeopleCurrentUserHasFriended:<br />\n<select>";
	echo $golfUtil->getPeopleCurrentUserHasFriended(6, null) . "</select></div><br />\n\n";
	
	echo "<div>getHolesForCourse:<br />\n<select>";
	echo $golfUtil->getHolesForCourse(new course($dbAdapter, $courseID), null) . "</select></div><br />\n\n";
	
	echo "<div>getHoleDescriptionForCourse:<br />\n<select>";
	echo $golfUtil->getHoleDescriptionsForCourse($courseID, null) . "</select></div><br />\n\n";
	
	echo "<div>getFormattedScoreStyle: score=1, par=3<br />\n";
	echo "<span style=\"" . golfUtil::getFormattedScoreStyle(1, 3) . "\">eagle</span></div>\n\n";
	
	echo "<div>getFormattedScoreStyle: score=2, par=3<br />\n";
	echo "<span style=\"" . golfUtil::getFormattedScoreStyle(2, 3) . "\">birdie</span></div>\n\n";
	
	echo "<div>getFormattedScoreStyle: score=3, par=3<br />\n";
	echo "<span style=\"" . golfUtil::getFormattedScoreStyle(3, 3) . "\">par</span></div>\n\n";
	
	echo "<div>getFormattedScoreStyle: score=4, par=3<br />\n";
	echo "<span style=\"" . golfUtil::getFormattedScoreStyle(4, 3) . "\">bogey</span></div>\n\n";
	
	echo "<div>getFormattedScoreStyle: score=5, par=3<br />\n";
	echo "<span style=\"" . golfUtil::getFormattedScoreStyle(5, 3) . "\">double bogey</span></div><br />\n\n";
	
	echo "<div>getDateDaySelect:<br />\n";
	echo golfUtil::getDateDaySelect(null) . "<br />\n";
	
	echo "<div>getDateDaySelect: selectedDay == 15<br />\n";
	echo golfUtil::getDateDaySelect(15) . "<br /><br />\n";
	
	echo "<div>getDateMonthSelect:<br />\n";
	echo golfUtil::getDateMonthSelect(null) . "<br />\n";
	
	echo "<div>getDateMonthSelect: selectedMonth == 4<br />\n";
	echo golfUtil::getDateMonthSelect(4) . "<br /><br />\n";
	
	echo "<div>getDateYearSelect:<br />\n";
	echo golfUtil::getDateYearSelect(null) . "<br />\n";
	
	echo "<div>getDateYearSelect: selectedYear == 2005<br />\n";
	echo golfUtil::getDateYearSelect(2005) . "<br /><br />\n";
	
	/*
	//moved to $dbAdapter
	echo "<div>convertUserSelectedDateToDbFormat:<br />5/6/2005 -&gt;&nbsp;";
	echo golfUtil::convertUserSelectedDateToDbFormat("5/6/2005") . "<br /><br />\n";
	 */
	
	$dbAdapter->close();
?>

</body>
</html>