<?php

class Oops_Sql_Exception extends Exception {
	public $query;

	public function __construct($message, $code, $query = null) {
		parent::__construct($message, $code);
		$this->query = $query;
	}

}