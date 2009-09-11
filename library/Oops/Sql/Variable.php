<?php

class Oops_Sql_Variable {
	private $varname;
	
	public function __construct($varname) {
		$this->varname = $varname;
	}
	
	public function __toString() {
		return '@`'.$this->varname.'`';
	}
}