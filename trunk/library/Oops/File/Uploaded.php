<?php

require_once 'Oops/File.php';

/**
 * 
 * @author Dmitry Ivanov <rockmagic@yandex.ru>
 *
 * @property-read string $extension Uploaded file original extension
 * @property-read string $name	Uploaded file original name
 */
class Oops_File_Uploaded extends Oops_File {
	
	protected $_name;
	
	/**
	 * Requires uploaded file info as array with keys: 'tmp_name','name','type','error','size'
	 *  
	 * @param array $fileInfo
	 * @return unknown_type
	 */
	public function __construct($fileInfo) {
		parent::__construct($fileInfo['tmp_name']);
		$this->_size = $fileInfo['size'];
		$this->_type = $fileInfo['type'];
		$this->_name = $fileInfo['name'];
		$pathInfo = pathinfo($this->_name);
		$this->_extension = strtolower($pathInfo['extension']);
		$this->_basename = $pathInfo['basename'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Oops/Oops_File#copy($dest, $mode)
	 * 
	 * Prohibited
	 * 
	 */
	public function copy() {
		throw new Exception("Copying uploaded files is deprecated");
	}
	
	public function __get($name) {
		switch($name) {
			case 'name':
				return $this->_name;
			default:
				return parent::__get($name);
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Oops/Oops_File#rename($dest, $mode)
	 * 
	 * Moves uploaded file to a given destination
	 */
	public function rename($dest, $mode = 0666) {
		$destFile = new Oops_File($dest);
		// @todo Consider using exception here
		if(!$destFile->makeWriteable()) return false;
		
		if(move_uploaded_file($this->_filename, $destFile->filename)) {
			$this->_filename = $destFile->filename;
			chmod($this->_filename, $mode);
			$this->_stat();
			return true;
		}
		return false;
	}
}