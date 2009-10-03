<?php

require_once 'Oops/File.php';

class Oops_File_Temporary extends Oops_File {
	protected $_isTemp = true;
	
	/**
	 * Creates new temporary file
	 * 
	 */
	public function __construct() {
		$this->_filename = tempnam("/tmp", "oft");
		$this->_stat();
		$this->_exists = true;
	}
	
	public function __destruct() {
		if($this->_isTemp && file_exists($this->_filename)) unlink($this->_filename);
	}
	
	public function rename($dest, $mode = 0666) {
		$this->_isTemp = false;
		return parent::rename($dest, $mode);
	}
	
}