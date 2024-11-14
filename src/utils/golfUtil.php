<?php

	class golfUtil {
		
		private $dbAdapter = null;
		private $logger = null;
	
		function __construct($db, $logger) {
			$this->dbAdapter = $db;
			$this->logger = $logger; 
	   	}
	   
	   
	   //creates html for options of select box of available courses
	   public function getCoursesSelectBox($selectedCourse) {
			$result = $this->dbAdapter->getCourses();
			$html = "";

			for ($a = 0; $row = $this->dbAdapter->getRow($result); $a++) {
				if ($selectedCourse == $row['course_id'])
					$html .= "<option id=\"selectCourse" . $a . "\" value=\"" . $row['course_id'] . "\" selected=\"selected\" >" . $row['course_name'] . "</option>\n";
				else
					$html .= "<option id=\"selectCourse" . $a . "\" value=\"" . $row['course_id'] . "\" >" . $row['course_name'] . "</option>\n";
			} /**/
			return $html;
	   }
	   
	   public function getTeeboxOptions($selectedTeebox) {
			$html = "";
			if ($selectedTeebox == NULL || $selectedTeebox == 99) {
				$html .= "<option value=\"99\" selected=\"selected\"></option>";
			} else {
				$html .= "<option value=\"99\"></option>";
			}
			
			$result = $this->dbAdapter->getTeeboxes();
			//iterate through teeboxes
			while ($row = $this->dbAdapter->getRow($result)) {
				//we already added teebox 99 (which means there's only one teebox for a hole)
				if ($row['teebox_id'] != '99') {
					if ($row['teebox_id'] == $selectedTeebox) {
						$html .= "<option value=\"" . $row['teebox_id'] . "\" selected=\"selected\">" . $row['teebox_name'] . "</option>";
					} else {
						$html .= "<option value=\"" . $row['teebox_id'] . "\">" . $row['teebox_name'] . "</option>";
					}
				}
			}
			return $html;
	   }
	   
	   //returns options for select box of available players
	   //$selectedPlayers[playerID] == player_display_name
	   public function getPlayersSelectBox($selectedPlayers) {
			$result = $this->dbAdapter->getPlayers($selectedPlayers);
			$html = "";

			while ($row = $this->dbAdapter->getRow($result)) {
				$curID = $row['player_id'];
				if (isset($selectedPlayers[$curID])) {
					$html .= "<option id=\"selectPlayer" . $curID . "\" value=\"" . $curID . "\" selected=\"selected\" >" . $row['player_display_name'] . "</option>\n";
				} else {
					$html .= "<option id=\"selectPlayer" . $curID . "\" value=\"" . $curID . "\" >" . $row['player_display_name'] . "</option>\n";
				}
			} /**/
			return $html;
	   }
	   
	   //returns options for select box of people who have friended current user
	   //$selectedFriends[playerID] == player_display_name || $selectedFriends == playerID
	   //public function getFriendsSelectBox($curUser, $selectedFriends) {
	   public function getPeopleWhoHaveFriendedCurrentUser($curUser, $selectedFriends) {
			$result = $this->dbAdapter->getPeopleWhoHaveFriendedCurrentUser($curUser);
			$html = "";
			
			while ($row = $this->dbAdapter->getRow($result)) {
				$curID = $row['player_id'];
				//TODO: this if statement is kludgey: userStats passes in sequential array
				if (isset($selectedFriends[$curID]))
				{
					$html .= "<option id=\"selectPlayer" . $curID . "\" value=\"" . $curID . "\" selected=\"selected\" >" . $row['player_display_name'] . "</option>\n";
				} else {
					$html .= "<option id=\"selectPlayer" . $curID . "\" value=\"" . $curID . "\" >" . $row['player_display_name'] . "</option>\n";
				}
			} /**/
			return $html;
	   }
	   
	   //returns options for select box of people who current user has friended
	   //$selectedFriends[playerID] == player_display_name
	   public function getPeopleCurrentUserHasFriended($curUser, $selectedFriends) {
			$result = $this->dbAdapter->getPeopleCurrentUserHasFriended($curUser);
			$html = "";
			
			while ($row = $this->dbAdapter->getRow($result)) {
				$curID = $row['player_id'];
				if (isset($selectedFriends[$curID])) {
					$html .= "<option id=\"selectPlayer" . $curID . "\" value=\"" . $curID . "\" selected=\"selected\" >" . $row['player_display_name'] . "</option>\n";
				} else {
					$html .= "<option id=\"selectPlayer" . $curID . "\" value=\"" . $curID . "\" >" . $row['player_display_name'] . "</option>\n";
				}
			} /**/
			return $html;
	   }
	   
	   public function getHolesForCourse($course, $holeToAutoSelect) {
	   		$teeboxes = count($course->teeboxes) > 0 ? true : false;
			$pinLocations = count($course->pinLocations) > 0 ? true : false;
			$html = "";
			
			for ($a = 0; isset($course->holeOrder[$a]); $a++) {
				$hole = $course->holeObjects[$course->holeOrder[$a]];
				/*
				$displayName = $hole->holeNum;
				
				if ($teeboxes) {
					$displayName .= "&nbsp;-&nbsp;";
					if ($hole->teebox != null && $hole->teebox != "NONE") {
						$displayName .= $hole->teebox;
					} else {
						$displayName .= "&nbsp;";
					}
				}
				
				if ($pinLocations) {
					$displayName .= "&nbsp;-&nbsp;";
					if ($hole->pinLocation != null) {
						$displayName .= $hole->pinLocation;
					}
				} */
				$displayName = $this->getHoleText($hole, $teeboxes, $pinLocations);
				
				$html .= "<option value=\"" . $hole->holeID . "\" ";
				if ($holeToAutoSelect == $hole->holeID) {
					$html .= "selected=\"selected\"";
				}
				$html .= ">" . $displayName . "</option>\n";
			}
			return $html;
	   }
	   
	   public function getHolesForCourseHeader($course) {
			$teeboxes = count($course->teeboxes) > 0 ? true : false;
			$pinLocations = count($course->pinLocations) > 0 ? true : false;
			$html = "<option value=\"NULL\">#";
			if ($teeboxes) {
				$html .= " - Tee";
			}
			if ($pinLocations) {
				$html .= " - pin";
			}
			$html .= "</option>\n";
			return $html;
	   }
	   
	   public function getHoleDescriptionsForCourse($courseID, $holeToAutoSelect) {
			$result = $this->dbAdapter->getHoleDescriptionsForCourse($courseID);
			$html = "";
			
			$prevDescription = ""; //if prev hole has same description don't need to add it again
			$prevHole = ""; //if prev hole has same number don't need to display it again
			while ($row = $this->dbAdapter->getRow($result)) {
				//$displayName = "1 (3)" = hole number 1 which is a par 3
				$displayName = $row['hole_number'];
				$displayName .= " (" . $row['par'] . ") ";
				
				//add hole description if there is one
				if (isset($row['description'])) {
					if ($row['description'] != "NONE")
						$displayName .= " - " . $row['description'];
				} /**/
				
				if ($prevDescription != $displayName) {
					$prevDescription = $displayName;
					if ($prevHole == $row['hole_number']) {
						if ($prevHole > 9)
							$displayName = "&nbsp;&nbsp;&nbsp;&nbsp;" .  substr($displayName, 3);
						else
							$displayName = "&nbsp;&nbsp;&nbsp;" . substr($displayName, 2);
					}
					$prevHole = $row['hole_number'];
					if ($holeToAutoSelect == $row['hole_id'])
					{
						$html .= "<option value=\"" . $row['hole_id'] . "\" selected=\"selected\" >" . $displayName . "</option>\n";
					}
					else
						$html .= "<option value=\"" . $row['hole_id'] . "\" >" . $displayName . "</option>\n";
				}
			}
			return $html;
		}

		function getPlayerIDToNameMap($selectedPlayers) {
			$playerIdToNameMap = null;
			$result = $this->dbAdapter->getPlayers($selectedPlayers);
			while ($row = $this->dbAdapter->getRow($result)) {
				$playerIdToNameMap[$row['player_id']] = $row['player_display_name'];
			}
			return $playerIdToNameMap;
		}
		
		public function getPlayerScorecard($player) {
			$html =  "<table border=\"1\" class=\"minimalTable\">\n<tr>\n";
			for ($a = 0; $a < $player->numScores; $a++) {
				//if first score of a row, last score of a row, prevHoleNum+1 != curHoleNum, or curHoleNum+1 != nextHoleNum, or last score
		    	//then we need to display the hole number. otherwise leave it blank (&nbsp;&nbsp;&nbsp;)
				$scores = $player->scores;
				$holeNumToDisplay = self::getHoleNumToDisplay($player, $a);
	    			
	    		//display 6 scores per row - add line break/new <tr>
	    		if ($a > 0 && $a % $player->numScoresPerRow == 0) {
	    			$html .= "</tr>\n<tr>";
	    		}
	    			
	    		//make scorecard checkerboard
	    		$scoreClass = "score";
	    		$rowNum = floor($a / $player->numScoresPerRow);
	    		if (($rowNum % 2 == 0 && $a % 2 == 0) ||
	    			($rowNum % 2 == 1  && $a % 2 == 1))
	    		{
	    			$scoreClass = "scoreAlt";
	    		}
	    			
	    		$html .= "<td class=\"" . $scoreClass . "\">" . $holeNumToDisplay;
	    		$html .= "<span class=\"scoreMinusParIs" . ($scores[$a]->score - $scores[$a]->hole->par);
	    		$html .= "\">" . $scores[$a]->score . "</span></td>\n";
			}
			return $html . "</tr>\n</table>\n";
		}

		//only need to show hole number if it's the first or last hole in a row or
	   //lastHoleNum+1 != curHoleNum or curHoleNum != nextHoleNum-1 or it's the last score
	   private function getHoleNumToDisplay($player, $scoreNum) {
			$scores = $player->scores;
			$holeNum = $scores[$scoreNum]->hole->holeNum;
			
			//logMsg = "prevHoleNum= curHoleNum= nextHoleNum="
			$logMsg = "\tgetHoleNumToDisplay: prevHoleNum=";
			if (isset($scores[$scoreNum-1])) {
				$logMsg .= $scores[$scoreNum-1]->hole->holeNum;
			} else {
				$logMsg .= "NULL";
			}
			$logMsg .= " curHoleNum=" . $holeNum . " nextHoleNum=";
			if (isset($scores[$scoreNum+1])) {
				$logMsg .= $scores[$scoreNum+1]->hole->holeNum;				
			} else {
				$logMsg .= "NULL";
			}
			$this->logger->LogDebug($logMsg);
			
			if ($scoreNum % $player->numScoresPerRow == 0) {
				$this->logger->LogDebug("\t\tfirst score in a row");
				return $scores[$scoreNum]->hole->holeNum . '-';
			} else if ($scoreNum % $player->numScoresPerRow == ($player->numScoresPerRow - 1)) {
				$this->logger->LogDebug("\t\tlast score in a row");
				return $scores[$scoreNum]->hole->holeNum . '-';
			} else if (isset($scores[$scoreNum - 1]) &&
				$scores[$scoreNum-1]->hole->holeNum != $holeNum - 1)
			{
				$this->logger->LogDebug("\t\tprevHoleNum != curHoleNum + 1");
				return $scores[$scoreNum]->hole->holeNum . '-';
			} else if (isset($scores[$scoreNum+1]) &&
				$scores[$scoreNum+1]->hole->holeNum != $holeNum + 1)
			{
				$this->logger->LogDebug("\t\tcurHoleNum != nextHoleNum + 1");
				return $scores[$scoreNum]->hole->holeNum . '-';
			} else if (isset($scores[$scoreNum+1]) == false) {
				$this->logger->LogDebug("\t\tlast score");
				return $scores[$scoreNum]->hole->holeNum . '-'; 
			}
			return "&nbsp;&nbsp;&nbsp;"; //not going to show hole number, just spaces instead
		}

		public function getScoresEditHTML($course, $player) {
			$teeboxes = count($course->teeboxes) > 0 ? true : false;
			$pinLocations = count($course->pinLocations) > 0 ? true : false;
			$html =  "<select id=\"scoreEditSelectHole" . $player->playerID .
				"\" name=\"scoreEditSelectHole" . $player->playerID . "\"" .
				" class=\"scoreEditSelectHole\" >\n";
			
			//iterate through scores, fetch each one's editHTML and append it
			foreach ($player->scores as $a) {
				$html .= "<option class=\"scoreMinusParIs" . ($a->score - $a->hole->par) .
				"\" value=\"" . $a->scoreID . "\">";
				$html .= $this->getHoleText($a->hole, $teeboxes, $pinLocations);
				$html .= " - " . $a->score . "</option>\n";
			}
				
			$html .= "<option value=\"NULL\" selected=\"selected\">#";
			if ($teeboxes) {
				$html .= "-tee";
			}
			if ($pinLocations) {
				$html .= "-pin";
			}
			$html .= "-score</option>\n" . "</select>\n";
			return $html;
		}
		
		//returns: "hole# - teebox - pinLocation" "4 - Blue - Long"
		//or if, for instance, $pinLocations==false: "4 - Blue"
		public function getHoleText($hole, $teeboxes, $pinLocations) {
			$html = $hole->holeNum;
			if ($teeboxes) {
				$html .= " - ";
				if (isset($hole->teebox) && $hole->teebox != "NONE") {
					$html .= $hole->teebox;
				} else {
					$html .= "&nbsp;";
				}
			}
			if ($pinLocations) {
				$html .= " - ";
				if (isset($hole->pinLocation)) {
					$html .= $hole->pinLocation;
				} else {
					$html .= "&nbsp;";
				}
			}
			return $html;
		}
	   
	   
	   //************************************************************************
	   //************************************************************************
	   
	   //TODO: not used anymore - delete?
	   //determine score color and text-decoration: eagle and better and double bogey and worse are underlined
	   public static function getFormattedScoreStyle($score, $par) {
			if ($par == null)
				return "";
	   
			$scoreColor = "#000000";
			if ($score > $par)
				$scoreColor = "#FF0000";
			else if ($score < $par)
				$scoreColor = "#0000FF";
			$textDecoration = "none";
			if (($score - $par) > 1 || ($score - $par) < -1)
				$textDecoration = "underline";
				
			//create one score: player scored a 3 on hole 16: $playerScores .= "16|3 ";
			//return "<span style=\"color:" . $scoreColor .";text-decoration:" . $textDecoration . ";\" >" . $score . "</span>";
			return "color:" . $scoreColor .";text-decoration:" . $textDecoration . ";";
	   }
	   
	   public static function getDateDaySelect($dayToSelect) {
			$html = "<select id=\"dateDay\" name=\"dateDay\" class=\"enableSubmitButton\">\n";
			for ($a = 1; $a < 32; $a++) {
				if ($a == $dayToSelect)
					$html .= "<option selected=\"selected\" value=\"" . $a . "\">" . $a . "</option>\n";
				else
					$html .= "<option value=\"" . $a . "\">" . $a . "</option>\n";
			}
			$html .= "</select>\n";
			return $html;
	   }
	   
	   public static function getDateMonthSelect($monthToSelect) {
			$html = "<select id=\"dateMonth\" name=\"dateMonth\" class=\"enableSubmitButton\">\n";
			$months = array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
			foreach ($months as $a => $b) {
				if (($a + 1) == $monthToSelect) {
					$html .= "<option selected=\"selected\" value=\"" . ($a + 1) . "\" >" . $b . "</option>\n";
				} else {
					$html .= "<option value=\"" . ($a + 1) . "\" >" . $b . "</option>\n";
				}
			}
			$html .= "</select>\n";
			return $html;
	   }
	   
	   public static function getDateYearSelect($yearToSelect) {
			$html = "<select id=\"dateYear\" name=\"dateYear\" class=\"enableSubmitButton\">\n";
			$curDate = getDate();
			$curYear = $curDate["year"];
			for ($a = $curYear; $a > ($curYear - 10); $a--) {
				if ($a == $yearToSelect)
					$html .= "<option selected=\"selected\" value=\"" . $a . "\">" . $a . "</option>\n";
				else
					$html .= "<option value=\"" . $a . "\">" . $a . "</option>\n";
			}
			$html .= "</select>\n";
			return $html;
	   }
	}

?>