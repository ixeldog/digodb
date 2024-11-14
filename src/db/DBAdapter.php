<?php

	class DBAdapter {
		
		private $dbConn = null;
		private $logger = null;
		
		function __construct($dbConn, $logger) {
			$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
			require_once($root . "db/DBConnection.php");
			require_once($root . "lib/KLogger.php");
			
			if ($dbConn != null) {
				$this->dbConn = $dbConn;
			} else {
				$this->dbConn = new DBConnection(new KLogger($root . "log/DBConnection.txt", KLogger::DEBUG));
			}
			
			if ($logger == null) {
				$this->logger = new KLogger($root . "log/DBAdapter.txt", KLogger::DEBUG);
			} else {
				$this->logger = $logger;
			}
		}
		
		public function getRow($result) {
			return $this->dbConn->getRow($result);
		}
		
		public function close() {
			$this->dbConn->close();
		}
		
		
		//creates html for options of select box of available courses
	   public function getCourses() {
			$query = "select * from course order by active_tf desc, course_name";
			return $this->dbConn->query($query);
	   }
	   
	   public function getTeeboxes() {
			$query = "select teebox_id, teebox_name from teebox order by teebox_name";
			return $this->dbConn->query($query);
	   }
	   
	   //gets all players from PLAYER table
	   public function getPlayers($selectedPlayers) {
	   		$this->logger->LogDebug("getPlayers: selectedPlayers=" . implode(' ', $selectedPlayers));
	   		$query = "select p.* from player p";
			if ($selectedPlayers != null) {
				$query .= " where " . self::getSqlORs($selectedPlayers, "p", "player_id");
			}
			$query .= " order by p.player_display_name";
			return $this->dbConn->query($query);
	   }
	   
	   //returns people who have friended current user
	   //$selectedFriends[playerID] == player_display_name
	   public function getPeopleWhoHaveFriendedCurrentUser($curUser) {
	   		$this->logger->LogDebug("getPeopleWhoHaveFriendedCurrentUser: curUser=" . $curUser);
			$query = "select * from player where player_id in (";
			$query .= "select player_id from player_to_friend_map where friend_id = " . $curUser;
			$query .= ") order by player_display_name";
			return $this->dbConn->query($query);
	   }
	   
	   //returns people who current user has friended
	   //$selectedFriends[playerID] == player_display_name
	   public function getPeopleCurrentUserHasFriended($curUser) {
	   		$this->logger->LogDebug("getPeopleCurrentUserHasFriended: curUser=" . $curUser);
			$query = "select * from player where player_id in (";
			$query .= "select friend_id from player_to_friend_map where player_id = " . $curUser;
			$query .= ") order by player_display_name";
			return $this->dbConn->query($query);
	   }
	   
	   //returns holes for selected course
	   public function getHolesForCourse($courseID) {
	   		$this->logger->LogDebug("getHolesForCourse: courseID=" . $courseID);
			$query = "select h.*, t.*, hd.description ";
			$query .= "from hole h, teebox t, hole_description hd ";
			$query .= "where h.course_id=" . $courseID . " and h.teebox = t.teebox_id ";
			$query .= "and h.description_id = hd.description_id order by h.hole_number, t.teebox_name";
			return $this->dbConn->query($query);
		}
		
		public function getHoleDescriptionsForCourse($courseID) {
			$this->logger->LogDebug("getHoleDescriptionsForCourse: courseID=" . $courseID);
			$query = "select h.*, t.teebox_name, hd.description from hole h, teebox t, hole_description hd where h.course_id=" . $courseID;
			$query .= " and h.teebox = t.teebox_id and h.description_id = hd.description_id order by h.hole_number, t.teebox_name";
			return $this->dbConn->query($query);
		}
		
		//get scores for selected players and selected course for selected date
		public function getScoresForPlayersAndCourseAndDate($playerIDs, $courseID, $selectedDate) {
			$this->logger->LogDebug("getScoresForPlayersAndCourseAndDate: courseID=" . 
				$courseID . " selectedDate=" . $selectedDate . 
				" playerIDs=" . implode(' ', $playerIDs));
			
			//$query = "select s.player_id, s.hole_id, h.hole_number, h.par, s.score, s.score_id ";
			$query = "select s.score_id, s.player_id, s.hole_id, s.time, s.score ";
			$query .= "from score s, hole h, player p where s.hole_id = h.hole_id and s.player_id = p.player_id and h.course_id = '" . $courseID . "' and ( ";
			$query .= self::getSqlORs($playerIDs, "s", "player_id") . ") ";
			$query .= "and to_days(s.time) = to_days(" . self::convertUserSelectedDateToDbFormat($selectedDate) . ") ";
			$query .= "order by s.time, p.player_display_name, s.hole_id";
			return $this->dbConn->query($query);
		}
		
		//if have a selected hole and just saved at least one player score to the DB then go ahead and
		//select next hole: just finished 16 blue tees then select 17 blue tees.
		public function getNextHoleForGivenHole($lastRecordedHole) {
			$this->logger->LogDebug("getNextHoleForGivenHole: lastRecordedHole=" .
				$lastRecordedHole);
			/*
			$query = "select *, (select teebox from hole where hole_id = " . $lastRecordedHole . ") as curteebox from hole ";
			$query .= "where course_id = (select course_id from hole where hole_id = " . $lastRecordedHole . ") and ";
			$query .= "hole_number = ((select hole_number from hole where hole_id = " . $lastRecordedHole . ") + 1) order by hole_id";
			*/
			$query = "select h.*, x.teebox as curteebox, x.pin_location as curpinlocation " . 
				"from hole h, (select * from hole where hole_id = ". $lastRecordedHole . ") as x " . 
				"where h.course_id = x.course_id and h.hole_number = (x.hole_number + 1) order by h.hole_id";
			return $this->dbConn->query($query);
		}
		
		public function getCourseFirstHoleForGivenHole($lastRecordedHole) {
			$this->logger->LogDebug("getCourseFirstHoleForGivenHole: lastRecordedHole=" .
				$lastRecordedHole);
			/*
			$query = "select *, (select teebox from hole where hole_id = " . $lastRecordedHole . ") as curteebox from hole ";
			$query .= "where course_id = (select course_id from hole where hole_id = " . $lastRecordedHole . ") and ";
			$query .= "hole_number = 1 order by hole_id";
			 * 
			 */
			$query = "select h.*, x.teebox as curteebox, x.pin_location as curpinlocation " . 
				"from hole h, (select * from hole where hole_id = ". $lastRecordedHole . ") as x " . 
				"where h.course_id = x.course_id and h.hole_number = 1 order by h.hole_id";
			return $this->dbConn->query($query);
		}
		
		public function checkLogin($username, $password) {
			$this->logger->LogDebug("checkLogin: user=" . $username);
			$query = "select * from player where player_login_name = '" . $username .
				"' and player_password = sha1('" . $password . "')";
			return $this->dbConn->query($query);
		}
		
		public function createUser($displayName, $username, $password) {
			$this->logger->LogDebug("createUser: displayName=" . $displayName .
				" username=" . $username);
			$query = "insert into player (player_id, player_display_name, player_login_name, player_password) ";
			$query .= "values (null, '" . $displayName . "', '" . $username . "', sha1('" . $password . "'))";
			return $this->dbConn->query($query);
		}
		
		public function addScore($playerID, $holeID, $date, $score) {
			$query = "insert into score values(NULL, " . $playerID . ", " . $holeID .
				", " . self::convertUserSelectedDateToDbFormat($date) . ", " .
				$score . ", NULL)";
			return $this->dbConn->query($query);
		}
		
		public function deleteScores($scoreIDs) {
			$query = "";
			if (is_array($scoreIDs)) {
				$this->logger->LogDebug("deleteScores: scoreIDs=" . implode(' ', $scoreIDs));
				$query = "delete from score s where " . self::getSqlORs($values, "s", "score_id");
			} else {
				$this->logger->LogDebug("deleteScores: scoreID=" . $scoreIDs);
				$query = "delete from score where score_id = " . $scoreIDs;
			}
			return $this->dbConn->query($query);
		}
		
		public function editScore($scoreID, $newValue) {
			$this->logger->LogDebug("editScore: ID=" . $scoreID . " newValue=" . $newValue);
			$query = "update score set score = " . $newValue . " where score_id = ". $scoreID;
			return $this->dbConn->query($query);
		}
		
		/*
		public function getPastScores($players, $course, $order) {
			$query = "select s.player_id, p.player_display_name, c.course_name, ";
			$query .= "c.course_id, h.par, s.score, date(s.time) as time ";
			$query .= "from score s, hole h, course c, player p ";
			$query .= "where s.hole_id = h.hole_id and h.course_id = c.course_id and s.player_id = p.player_id and (";
			$query .= self::getSqlORs($players, "s", "player_id") . ") ";
			
			//if have a course to filter search by
			if (isset($course) && strlen($course) > 0) {
				$query .= "and c.course_id = " . $course . " ";
			}
			
			//order results by date or by course
			if (isset($order) == false || $order == "ORDER_BY_DATE") {
				$query .= "order by time desc, c.course_name, p.player_display_name";
			} else if ($order == "ORDER_BY_COURSE") {
				$query .= "order by c.course_name, time desc,  p.player_display_name";
			}
			
			return $this->dbConn->query($query);
		} */
		
		public function getPastScores($players, $course, $teeboxes, $order) {
			$query = "select s.player_id, p.player_display_name, c.course_name, " .
				"c.course_id, sum(h.par) as par, sum(s.score) as score, " . 
				"count(s.score) as numHoles, date(s.time) as time ".
				"from score s, hole h, course c, player p " .
				"where s.hole_id = h.hole_id and h.course_id = c.course_id and " .
				"s.player_id = p.player_id and (" .
				self::getSqlORs($players, "s", "player_id") . ") ";
				
				//if have a course to filter search by
				if (isset($course) && strlen($course) > 0) {
					$query .= "and c.course_id = " . $course . " ";
				}
				
				if (isset($teeboxes) && is_array($teeboxes) && count($teeboxes) > 0) {
					$query .= " and (" . self::getSqlORs($teeboxes, "h", "teebox") . ") ";
				}
			
				$query .= "group by s.player_id, c.course_id, date(s.time) ";			

				//order results by date or by course
				if (isset($order) == false || $order == "ORDER_BY_DATE") {
					//$query .= "order by time desc, c.course_name, p.player_display_name";
					$query .= "order by time desc, c.course_name, p.player_id";
				} else if ($order == "ORDER_BY_COURSE") {
					//$query .= "order by c.course_name, time desc, p.player_display_name";
					$query .= "order by c.course_name, time desc, p.player_id";
				} else if ($order == "ORDER_BY_SCORE") {
					$query .= "order by c.course_name, (sum(s.score) - sum(h.par)), p.player_id";
				}
				
				return $this->dbConn->query($query);
		}
		
		public function updatePlayerDisplayName($playerID, $newName) {
			$query = "update player set player_display_name = '" . $newName;
			$query .= "' where player_id = " . $playerID;
			return $this->dbConn->query($query);
		}
		
		public function removeFriend($playerID, $friendIDs) {
			$query = "delete from p using player_to_friend_map as p where p.player_id = " . $playerID . " and (";
			$query .= self::getSqlORs($friendIDs, "p", "friend_id") . ")";
			return $this->dbConn->query($query);
		}
		
		//return user login name to user ID. return null if login name not found or
		//if current user is already friends with newFriend
		//TODO: this is kludgey: maybe refactor into Player.getNewFriend() where Player object
		//has list of friends already in it
		public function getNewFriendID($curUser, $newFriendLoginName) {
			$query = "select player_id from player where player_login_name = '" . $newFriendLoginName .
				"' and player_id not in (select friend_id from player_to_friend_map where " .
				"player_id = '" . $curUser . "')";
			return $this->dbConn->query($query);
		}
		
		public function addFriend($curUserID, $newFriendID) {
			$query = "insert into player_to_friend_map (player_id, friend_id) values(" .
				$curUserID . ", " . $newFriendID . ")";
			return $this->dbConn->query($query);
		}
		
		//have to use user name because of Reset Password
		public function changePassword($userName, $oldPassword, $newPassword) {
			$query = "select true from player where player_login_name = '" . $userName . "' and sha1('" .
				$oldPassword . "') = player_password";
			$result = $this->dbConn->query($query);
			//if old password is correct
			if ($this->dbConn->getRow($result)) {
				return $this->setPassword($userName, $newPassword);
			} else {
				return false;
			}
		}
		
		//have to use user name because of Reset Password
		public function setPassword($userName, $newPassword) {
			$query = "update player set player_password = sha1('" .
				$newPassword . "') where player_login_name = '" . $userName . "'";
			return $this->dbConn->query($query);
		}
		
		public function getPlayerStats($playerIDs, $courseID, $teeboxes, $sortBy) {
			$query = "select s.player_id, s.hole_id, count(*) as count, min(s.score) as min, " .
				"round(avg(s.score), 2) as avg, max(s.score) as max " .
				"from score s, hole h, player p " . 
				"where (" . self::getSqlORs($playerIDs, "s", "player_id") . 
				") and s.hole_id = h.hole_id and h.course_id = " . $courseID .
				" and s.player_id = p.player_id";
				
				if ($teeboxes != null && is_array($teeboxes) && count($teeboxes) > 0) {
					$query .= " and (" . self::getSqlORs($teeboxes, "h", "teebox") . ") ";
				}
				 
				$query .= " group by s.hole_id, s.player_id ";
				//"order by h.teebox, h.hole_number, h.pin_location, avg";
				if ($sortBy == "ORDER_BY_DIFF") {
					$query .= "order by h.teebox, h.par - round(avg(s.score), 2) desc, h.hole_number, h.pin_location";
				} else {
					$query .= "order by h.teebox, h.hole_number, h.pin_location, avg";
				}
				$query .= ", p.player_display_name";
			return $this->dbConn->query($query);
		}
		
		public function changeScoreTeebox($scoreID, $newTee) {
			$query = "update score set hole_id = (" . 
						"select h.hole_id from hole h, " .
							"(select * from hole where hole_id = " .
								"(select hole_id from score where score_id = " . $scoreID . ")" .
							") as x " .
						"where h.course_id = x.course_id and h.hole_number = x.hole_number and " .
						"(h.pin_location = x.pin_location or (h.pin_location is null and x.pin_location is null)) " .
						"and h.teebox = " . $newTee . ") " .
					"where score_id = " . $scoreID;
			return $this->dbConn->query($query);
		}
		
		public function changeScorePinLocation($scoreID, $newPinLocation) {
			$query = "update score set hole_id = (" . 
						"select h.hole_id from hole h, " .
							"(select * from hole where hole_id = " .
								"(select hole_id from score where score_id = " . $scoreID . ")" .
							") as x " .
						"where h.course_id = x.course_id and h.hole_number = x.hole_number and h.teebox = x.teebox " .
						"and h.pin_location = '" . $newPinLocation . "') " .
					"where score_id = " . $scoreID;
			return $this->dbConn->query($query);
		}
		
		//TODO: same as above, one is for UserSettings.php and one for ResetPassword.php
		//fetch user email address, security question and answer
		public function getSecuritySettings($userName) {
			$query = "select email, question, answer from player " .
				"where player_login_name = '" . $userName . "'";
			return $this->dbConn->query($query);
		}
		
		public function setSecuritySettings($userID, $email, $question, $answer) {
			$query = "update player set email = '" . $email . "', " .
				"question = '" . $question . "', answer = '" . $answer . "' " .
				"where player_id = " . $userID;
			return $this->dbConn->query($query);
		}
		
		
		//**************************************************************************
		//**************************************************************************
		
		//$values = ["one":34, "two":123, "three":45 ...]
		//$tableName = "s"
		//$columnName = "columnName"
		//returns "s.columnName = '34' OR s.columnName = '123' OR s.columnName='45'"
		private static function getSqlORs($values, $tableName, $columnName) {
			$sql = "";
			foreach($values as $a) {
				$sql .= $tableName . "." . $columnName . " = '" . $a . "' OR ";
			}
			$sql = substr($sql, 0, strlen($sql) - 4); //remove " OR "
			return $sql;
		}
		
		//takes in "2/1/2003" (Feb 1, 2003) and returns concat('2003-02-01 ', curtime())
	   public static function convertUserSelectedDateToDbFormat($userDate) {
			if ($userDate == null) {
				return null;
			}
			
			$userDateArray = preg_split("/[\/]/", $userDate);
			$month = $userDateArray[0];
			$day = $userDateArray[1];
			$year = $userDateArray[2];
			
			if ($month < 10) {
				$month = "0" . $month;
			}
			if ($day < 10) {
				$day = "0" . $day;
			}
			
			return "concat(\"" . $year . "-" . $month . "-" . $day . " \", curtime())";
	   }
	}
?>