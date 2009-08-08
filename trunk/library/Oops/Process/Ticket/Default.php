<?php

class Oops_Process_Ticket_Default implements Oops_Process_Ticket_Interface {
	private $_timestamp;
	private $_user;
	
	public function __construct() {
		$this->_timestamp = time();
		$this->_user = 'Bender';
	}
	
	public function getTimestamp() {
		return $this->_timestamp;
	}
	
	public function getUser() {
		return $this->_user;
	}
}