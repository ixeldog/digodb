<?php

	class scorecard {
		
		// [ 3:{Player}, 8:{Player}, 14:{Player}]
		public $players = null;
		// ['8, '3', '14']
		public $playersInOrder = null;
		//ID of last recorded hole
		public $lastRecordedHole = null;
		//ID of next hole to select given lastRecordedHole
		public $nextSelectedHole = null;
		//par for next hole to select given lastRecordedHole - used to auto select par for player score input
		public $nextSelectedHolePar = null;
		
		private $log = null;
		private $dbAdapter = null;
	
		function __construct($dbAdapter, $course, $playerIDs, $selectedDate) {
			
			$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
			require_once $root . 'model/player.php';
			require_once $root . 'model/hole.php';
			require_once $root . 'lib/KLogger.php';
			
			$this->dbAdapter = $dbAdapter;
			$this->players = array();
			$this->playersInOrder = array();
			
			$this->log = new KLogger ($root . "log/scorecard.txt" , KLogger::DEBUG );
			
			//determine order for player scores - alphabetical right now
			$result = $this->dbAdapter->getPlayers($playerIDs);
			for ($a = 0; $row = $this->dbAdapter->getRow($result); $a++) {
				$curPlayerID = $row['player_id'];
				//$this->playersInOrder[$a] = $curPlayerID;
				//currently user should always be listed first
				if ($curPlayerID == $_SESSION['currentUserID']) {
					$this->playersInOrder[0] = $curPlayerID;
				} else if (isset($this->playersInOrder[0]) == false) {
					$this->playersInOrder[$a+1] = $curPlayerID;
				} else {
					$this->playersInOrder[$a] = $curPlayerID;
				}
				$this->players[$curPlayerID] = new player($curPlayerID, $row['player_display_name']);
				$this->log->LogDebug("scorecard(): added player: ID=" . $curPlayerID . " name=" . $this->players[$curPlayerID]->playerName);
			}
			
			//get scores for selected players and selected course for selected date
			$result = $this->dbAdapter->getScoresForPlayersAndCourseAndDate($playerIDs, $course->courseID, $selectedDate);
			//iterate through scores and store in player objects
			for ($a = 0; $row = $this->dbAdapter->getRow($result); $a++) {		
				$curPlayerID = $row['player_id'];
				$this->players[$curPlayerID]->addScore($row['score_id'], $row['score'],
					$row['time'], $course->holeObjects[$row['hole_id']]);
				$this->lastRecordedHole = $row['hole_id']; //if don't have a selectedHole then we will auto select last recorded hole + 1
			}
			
			//if have a selected hole and just saved at least one player score to the DB then go ahead and
			//select next hole: just finished 16 blue tees then select 17 blue tees.
			//$holeToAutoSelect = $selectedHole;
			if ($this->lastRecordedHole != null) {
				//look for next hole with same teebox
				$this->nextSelectedHole = null;
				$this->nextSelectedHolePar = null;
							
				$result = $this->dbAdapter->getNextHoleForGivenHole($this->lastRecordedHole);
				$this->getNextSelectedHole($result);
				
				//if nextSelectedHole still null it means we just played last hole on course and need to set next hole to hole number 1
				if ($this->nextSelectedHole == null) {
					$result = $this->dbAdapter->getCourseFirstHoleForGivenHole($this->lastRecordedHole);
					$this->getNextSelectedHole($result);
				} /**/
			} //end of find next hole number
	    } //end of contructor
	    
	    public function __toString() {
	    	$return = "";
	    		
	    	$return = "players:[";
	    	foreach ($this->players as $a => $b) {
	    		$return .= "(" . $a . ":" . $b . ")";
	    	}
	    	$return .= "]";
	    		
	    	$return .= " players in order:[";
	    	foreach ($this->playersInOrder as $a => $b) {
	    		$return .= "(" . $a . "=" . $b . ")";
	    	}
	    	$return .= "]";
	    		
	    	$return .= " lastRecordedHole=" . $this->lastRecordedHole;
	    	return $return;
	    }
		
		//TODO: pass in $this->nextSelectedHole and $this->nextSelectedHolePar by reference?
		private function getNextSelectedHole($result) {
			$matchFound = false;
			while (($row = $this->dbAdapter->getRow($result)) && !$matchFound) {
				//if we don't find a hole with same teebox we'll just use first entry (if there is one)
				if ($this->nextSelectedHole == null) {
					$this->nextSelectedHole = $row['hole_id'];
					$this->nextSelectedHolePar = $row['par'];
				}
				if ($row['teebox'] == $row['curteebox'] &&
					$row['pin_location'] == $row['curpinlocation'])
				{
					$matchFound = true;
					$this->nextSelectedHole = $row['hole_id'];
					$this->nextSelectedHolePar = $row['par'];
				} else if ($row['teebox'] == $row['curteebox']) {
					$this->nextSelectedHole = $row['hole_id'];
					$this->nextSelectedHolePar = $row['par'];
				}
			}
		}
		
	} //end of scorecard class
?>