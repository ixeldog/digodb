<?php

	class score
	{
		public $scoreID = null;
		public $score = null;
		public $time = null;
		public $hole = null;
		
		function __construct($newScoreID, $newScore, $newTime, $newHole) {
			$this->scoreID = $newScoreID;
			$this->score = $newScore;
			$this->time = $newTime;
			$this->hole = $newHole;
		}
		
		public function __toString() {
			return "scoreID=" . $this->scoreID . " holeNum=" . $this->hole->holeNum .
				" score=" . $this->score . " par=" . $this->hole->par;
		}
	}
	
?>