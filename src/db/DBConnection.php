<?php
	class DBConnection {
	
		private $conn = null;
		private $logger = null;
	
		function __construct($logger) {
			if ($logger == null) {
				$root = realpath($_SERVER["DOCUMENT_ROOT"]) . "/";
				require_once $root . 'lib/KLogger.php';
				$logger = new KLogger($root . "log/DBConnection.txt", KLogger::DEBUG);
			} else {
				$this->logger = $logger;
			}
			
			$user="discgolfdb";
			$password="Godles0!";
			$database="dgolfdb1";
			
			$this->conn = mysql_connect("localhost",$user,$password);
			if (@mysql_select_db($database) == false) {
				$logger->LogError("cannot connect to DB");
				die("Unable to select database");
			} else {
				$this->logger->LogDebug("successfully connected to DB");
			}
		}
		
		public function query($query) {
			if ($this->conn != null) {
				$this->logger->LogDebug("DBConnection.query: query=" . $query);
				return mysql_query($query, $this->conn);
			}
		}
		
		public function getRow($result) {
			return mysql_fetch_assoc($result);
		}
		
		public function close() {
			mysql_close($this->conn);
		}
		
	}
?>