<?php

/**
 * Class for handling local files
 *
 * @author Dmitry Ivanov rockmagic@yandex.ru
 * @license GNUv3
 * 
 * @property string $filename File name
 * @property bool $exists True if file exists
 * @property string $type Mime type
 * @property int $size File size
 * @property string $dirname Path to file
 * @property string $basename File base name
 * @property string $extension File extension
 * 
 */
class Oops_File {
	protected $_filename;
	protected $_exists;
	protected $_type;
	protected $_size;
	protected $_dirname;
	protected $_basename;
	protected $_extension;

	function __get($var) {
		switch($var) {
			case 'filename':
				return $this->_filename;
			case 'exists':
				return $this->_exists;
			case 'type':
				return $this->type();
			case 'size':
				return $this->size();
			case 'dirname':
				return $this->_dirname;
			case 'basename':
				return $this->_basename;
			case 'extension':
				return $this->_extension;
			case 'isFile':
				return is_file($this->_filename);
		}
		return null;
	}

	function __set($var) {
		return;
	}

	function __toString() {
		return $this->_filename;
	}

	/**
	 * 
	 * @param string $filename File name
	 * @param bool $autoCreate Try to create file if it doens not exist
	 */
	public function __construct($filename, $autoCreate = false) {
		$this->_filename = $filename;
		if(file_exists($filename)) {
			$this->_exists = true;
		} elseif($autoCreate) {
			// @todo Try to create the file
		

		} else {
			$this->_exists = false;
		}
		
		$this->_stat();
	}

	protected function _stat() {
		if(!$this->_exists) return;
		
		$pathInfo = pathinfo($this->filename);
		$this->_dirname = $pathInfo['dirname'];
		$this->_basename = $pathInfo['basename'];
		$this->_extension = $pathInfo['extension'];
		if(is_file($this->_filename)) $this->_size = filesize($this->_filename);
	}

	public function exists() {
		return $this->_exists;
	}

	public function isReadable() {
		return is_readable($this->_filename);
	}

	/**
	 * Check whenever file (or dir) is writeable
	 * @return unknown_type
	 */
	public function isWritable() {
		if($this->_exists)
			return is_writeable($this->_filename);
		else {
			$dir = new Oops_File($this->_dirname);
			return $dir->isWritable();
		}
	}

	/**
	 * Copy file
	 * @param $dest string Destination file name
	 * @return Oops_File
	 */
	public function copy($dest) {
		if(copy($this->_filename, $dest)) return new Oops_File($dest);
		// @todo Check for errors
	}

	/**
	 * Rename file
	 * 
	 * @param string $dest
	 * @return boolean True if moved successfully
	 */
	public function rename($dest) {
		$destFile = new Oops_File($dest);
		$destFile->makeWriteable();
		
		if(rename($this->_filename, $destFile->filename)) {
			$this->_filename = $destFile->filename;
			$this->_stat();
			return true;
		}
		return false;
	}

	function getContents() {
		if($this->_exists) return file_get_contents($this->_filename);
	}

	function putContents($content) {
		if(!$this->makeWritable) return false;
		return file_put_contents($this->_filename, $content);
	}

	function makeWriteable() {
		if($this->isWritable()) return true;
		if(!$this->exists) {
			$dir = new Oops_File($this->_dirname);
			if(!$dir->exists) {
				require_once "Oops/File/Utils.php";
				try {
					Oops_File_Utils::autoCreateDir($dir->filename);
				} catch(Exception $e) {
					return false;
				}
			}
		}
	}
}