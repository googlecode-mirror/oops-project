<?php

require_once 'Oops/File.php';

class Oops_File_Temporary extends Oops_File {
	protected $_isTemp = true;

	/**
	 * Creates new temporary file
	 * 
	 * @param string $dir Temporary file location, defaults to /tmp
	 */
	public function __construct($dir = "/tmp") {
		$this->_filename = tempnam($dir, "oft");
		$this->_stat();
		$this->_exists = true;
	}

	public function __destruct() {
		if($this->_isTemp && file_exists($this->_filename)) {
			if(is_file($this->_filename))
				unlink($this->_filename);
			elseif(is_dir($this->_filename)) {
				$files = glob($this->_filename . DIRECTORY_SEPARATOR . "/*.*");
				foreach($files as $file)
					unlink($file);
				rmdir($this->_filename);
			}
		}
	}
	
	public function __set($var, $value) {
		switch($var) {
			case 'filename':
				rename($this->_filename, $value);
				$this->_filename = $value;
				break;
			default:
				parent::__set($var, $value);
		}
	}

	public function rename($dest, $mode = 0666) {
		$this->_isTemp = false;
		return parent::rename($dest, $mode);
	}

	public function toDir() {
		if(is_dir($this->_filename)) return;
		unlink($this->_filename);
		mkdir($this->_filename, 0777);
	}
}