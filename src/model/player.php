<?php

	class player {
		
		public $scores = null; //array of score objects
		public $numScoresPerRow = 6;
		
		public $numScores = null; //total number of player scores. number of holes played.
		public $totalScore = null; //total player score = hole1Score + hole2Score + ...
		public $totalPar = null; //total par for holes player has played
		public $playerID = null;
		public $playerName = "";
		
		private $log = null;
	
		function __construct($newPlayerID, $newPlayerName) {
			$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
			require_once $root . 'lib/KLogger.php';
			require_once $root . "model/score.php";
			$this->log = new KLogger ($root . "log/player.txt" , KLogger::DEBUG );
			$this->log->LogDebug("player(): created player: ID=" . $newPlayerID . " name=" . $newPlayerName);
			
			$this->scores = array();
			$this->numScores = 0;
			$this->totalScore = 0;
			$this->totalPar = 0;
			$this->playerID = $newPlayerID;
			$this->playerName = $newPlayerName;
	   }
	   /*
	   public function addScore($scoreID, $holeNum, $par, $score) {
	   		$this->log->LogDebug("Player.addScore(): " . $this->playerID . " - " . $this->playerName);
			$this->log->LogDebug("\tscoreID=" . $scoreID .
				" holeNum=" . $holeNum . " par=" . $par . " score=" . $score);
			
			$this->scores[] = new score($scoreID, $holeNum, $score, $par);
			$this->numScores++;
			$this->totalScore += $score;
			$this->totalPar += $par;
	   	} */
	   	
	   	public function addScore($scoreID, $score, $time, $hole) {
	   		$this->log->LogDebug("Player.addScore(): " . $this->playerID . " - " . $this->playerName);
			$this->log->LogDebug("\tscoreID=" . $scoreID . " score=" . $score .
				" time=" . $time . " hole=" . $hole);
			
			$this->scores[] = new score($scoreID, $score, $time, $hole);
			$this->numScores++;
			$this->totalScore += $score;
			$this->totalPar += $hole->par;
	   	}
	   
	   public function __toString() {
		return "name=" . $this->playerName . " id=" . $this->playerID .
			" totalScore=" . $this->totalScore . " numScores=" . $this->numScores;
	   }
	}
?>