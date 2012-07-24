<?php

class Oops_Sql_Value_String {
	private $_s;

	public function __construct($s) {
		$this->_s = (string) $s;
	}

	public function __toString() {
		return $this->_s;
	}
}