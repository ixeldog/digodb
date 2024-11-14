<?php

	class validator {
		public static function validateString($input) {
			$inputChars = str_split($input);
			foreach ($inputChars as $curChar) {
				//if (preg_match("/[A-Za-z0-9]/", $curChar) <= 0) {
				if (preg_match("/[A-Za-z0-9@\._\? ]/", $curChar) <= 0) {
					return false;
				}
			}
			return true;
		}
	}
	
?>