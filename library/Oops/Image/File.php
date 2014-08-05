<?php

/**
 * 
 * @author Dmitry Ivanov
 * 
 * @property-read int $width
 * @property-read int $height
 * @property-read int $orient
 */
class Oops_Image_File extends Oops_File {
	
	protected $_width;
	protected $_height;
	protected $_orient;
	
	/**
	 * Used for preserving file objects with active destructor (f.e. Oops_File_Temporary)
	 * @var Oops_File
	 */
	protected $_fileObject;
	
	public function __construct($filename, $autoCreate = false) {
		if($filename instanceof Oops_File) {
			$this->_fileObject = $filename;
			$filename = $filename->filename;
		}
		parent::__construct($filename, $autoCreate);
	}
	
	public function _stat() {
		parent::_stat();

		if(($info = getimagesize($this->_filename)) === false) {
			throw new Oops_Image_Exception("Invalid image file {$this->_filename}");
		}
		$this->_width = $info[0];
		$this->_height = $info[1];
		
		$this->_orient = 1;
		if(function_exists('exif_read_data')) {
			$exifData = @exif_read_data($this->_filename);
			if(sizeof($exifData) && isset($exifData['Orientation'])) $this->_orient = $exifData['Orientation'];
		}
	}
	
	public function __get($key) {
		switch($key) {
			case 'width':
				return $this->_width;
			case 'height':
				return $this->_height;
			default:
				return parent::__get($key);
		}
	} 
}