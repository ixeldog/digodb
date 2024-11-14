<?php

	class hole
	{	
		public $holeID = null;
		public $courseID = null;
		public $holeNum = null;
		public $teebox = null; //display name: "black" or "red"
		public $pinLocation = null;
		public $par = null;
		
		function __construct($newHoleID, $newCourseID, $newHoleNum, $newTeebox,
								$newPinLocation, $newPar) {
			$this->holeID = $newHoleID;
			$this->courseID = $newCourseID;
			$this->holeNum = $newHoleNum;
			$this->teebox = $newTeebox;
			$this->pinLocation = $newPinLocation;
			$this->par = $newPar;
		}
		
		public function __toString() {
			return "holeID=". $this->holeID . " courseID=" . $this->courseID .
				" holeNum=" . $this->holeNum . " teebox=" . $this->teebox .
				" pinLocation=" . $this->pinLocation . " par=" . $this->par;
		}
		
		public function toHTML() {
			return "<tr><td>holeID=". $this->holeID . "</td><td>courseID=" . $this->courseID .
				"</td><td>holeNum=" . $this->holeNum . "</td><td>teebox=" . $this->teebox .
				"</td><td>pinLocation=" . $this->pinLocation . "</td><td>par=" . $this->par . "</td></tr>";
		}
	}
	
?>