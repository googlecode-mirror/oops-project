<?php

class Oops_Sql_Variable {
	private $varname;
	
	public function __construct($varname) {
		$this->varname = $varname;
	}
	
	public function __toString() {
		return '@`'.$this->varname.'`';
	}
	
	public function getValue() {
		require_once 'Oops/Sql.php';
		$r = Oops_Sql::Query("SELECT $this");
		list($value) = mysql_fetch_row($r);
		return $value;
	}
}