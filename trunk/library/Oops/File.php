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
				return $this->_type;
			case 'size':
				return $this->_size;
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

	function __set($var, $value) {
		return;
	}

	function __toString() {
		return $this->_filename;
	}

	/**
	 *
	 * @param string $filename File name
	 * @param bool $autoCreate Try to create file if it does not exist
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
		$pathInfo = pathinfo($this->_filename);
		if(isset($pathInfo['dirname'])) $this->_dirname = $pathInfo['dirname'];
		if(isset($pathInfo['extension'])) $this->_basename = $pathInfo['basename'];
		if(isset($pathInfo['extension'])) $this->_extension = $pathInfo['extension'];
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
	public function copy($dest, $mode) {
		$destFile = new Oops_File($dest);
		$destFile->makeWriteable();
		if(copy($this->_filename, $dest)) return false;
		@chmod($dest, $mode);
		$destFile = null;
		$destFile = new Oops_File($dest);
		$destFile->makeWriteable();
	}

	/**
	 * Rename file
	 *
	 * @param string $dest
	 * @return boolean True if moved successfully
	 */
	public function rename($dest, $mode = 0666) {
		$destFile = new Oops_File($dest);
		// @todo Consider using exception here
		if(!$destFile->makeWriteable()) return false;
		
		if(rename($this->_filename, $destFile->filename)) {
			$this->_filename = $destFile->filename;
			@chmod($this->_filename, $mode);
			$this->_stat();
			return true;
		}
		return false;
	}

	/**
	 * 
	 * @return string File contents
	 */
	public function getContents() {
		if($this->_exists && $this->_isFile) return file_get_contents($this->_filename);
		return "";
	}

	/**
	 * Write content to the file
	 * @param $content
	 * @return unknown_type
	 */
	public function putContents($content) {
		if(!$this->makeWritable()) return false;
		if($this->isDirectory()) return false;
		return file_put_contents($this->_filename, $content);
	}

	/**
	 * Make a file (or directory) writable
	 * @return bool True on success
	 */
	public function makeWriteable() {
		//if($this->isWritable()) return true;
		if(!$this->_exists) {
			$dir = new Oops_File($this->_dirname);
			if(!$dir->exists) {
				require_once "Oops/File/Utils.php";
				try {
					Oops_File_Utils::autoCreateDir($dir->filename, 0777);
				} catch(Exception $e) {
					return false;
				}
				return true;
			} else {
				return $dir->makeWriteable();
			}
		} else {
			if(is_file($this->_filename))
				return @chmod($this->_filename, 0666);
			elseif(is_dir($this->_filename))
				return @chmod($this->_filename, 0777);
			else
				return false;
		}
	}
	
	public function remove() {
		throw new Exception("Oops_File::remove not implemented");
	}
}