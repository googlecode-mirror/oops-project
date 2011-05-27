<?php

/**
 * 
 * Object for trusted SQL expression for selectors and Oops_Sql_Common methods
 * @author DI
 *
 */
class Oops_Sql_Expression {
	private $_exp;
	
	public function __construct($exp) {
		$this->_exp = (string) $exp;
	}
	
	public function __toString() {
		return $this->_exp;
	}
	
}