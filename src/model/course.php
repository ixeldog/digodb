<?php

	class course {
		public $courseID = null;
		//public $courseName = null;
		public $holeOrder = null; //holes in order of hole # and teebox
		public $holeObjects = null; // [ holeID:{holeObject}, ...]
		public $teeboxes = null; // [ teebox_id:"teebox_name", ...]
		public $pinLocations = null; // [ pinLocation:"pinLocation", ...]
		
		private $dbAdapter = null;
		private $log = null;
		
		function __construct($newDbAdapter, $courseID) {
			$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
			require_once($root . "model/hole.php");
			require_once($root . "lib/KLogger.php");
			$this->log = new KLogger($root . "log/course.txt", KLogger::DEBUG);
			
			$this->courseID = $courseID;
			$this->dbAdapter  = $newDbAdapter;
			
			$this->holeOrder = array(); 
			$this->holeObjects = array();
			$this->teeboxes = array();
			$this->pinLocations = array();
						
			//$result = $this->dbAdapter->getHoleObjectsForCourse($courseID);
			$result = $this->dbAdapter->getHolesForCourse($courseID);
			while ($row = $this->dbAdapter->getRow($result)) {
				$this->holeOrder[] = $row['hole_id'];
				$this->holeObjects[$row['hole_id']] = new hole($row['hole_id'],
					$courseID, $row['hole_number'], $row['teebox_name'],
					$row['pin_location'], $row['par']);
				if (isset($row['teebox_name']) && $row['teebox_name'] != "NONE") {
					$this->teeboxes[$row['teebox_id']] = $row['teebox_name'];
				}
				if (isset($row['pin_location'])) {
					$this->pinLocations[$row['pin_location']] = $row['pin_location'];
				}
			}
		}
		
		public function __toString() {
			return "courseID=" . $this->courseID . "\n" .
				"teeboxes: " . implode(",", $this->teeboxes) . "\n" .
				"pinLocations: " . implode(",", $this->pinLocations) . "\n" .
				implode(",", $this->holeOrder) . "\n" .
				implode("\n", $this->holeObjects);
		}
		
		public function toHTML() {
			$html = "courseID=" . $this->courseID . "<br />" . 
			"teeboxes: " . implode(",", $this->teeboxes) . "<br />" .
			"pinLocations: " . implode(",", $this->pinLocations) . "<br />" .
			$holeObjectsInOrder = "<br /><table border=\"1\">";
			for ($a = 0; isset($this->holeOrder[$a]); $a++) {
				$html .= $this->holeOrder[$a] . ",";
				$holeObjectsInOrder .= $this->holeObjects[$this->holeOrder[$a]]->toHTML();
			}
			return $html . $holeObjectsInOrder . "</table>";
		}
		
	}

?>