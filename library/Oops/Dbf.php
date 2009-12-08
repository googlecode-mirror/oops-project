<?php

// @todo Optimize file reading
// @todo Implement ArrayAccess, Iterator, Countable interfaces


/**
 * 
 * @author Dmitry Ivanov
 * 
 * Class for reading DBF and attached FTP (memo) files
 * 
 * @property-read array $header
 * @property-read string $_memoFile
 * @property-read array $fields
 * @property-read int $numRecords
 * @property-read int $numFields
 */
class Oops_Dbf {
	const MODE_READONLY = 0;
	const MODE_WRITEONLY = 1;
	const MODE_READWRITE = 2;
	
	protected $_file;
	protected $_fp;
	
	protected $_header;
	protected $_fields;
	protected $_recordFormat;
	
	protected $_memoFile;
	protected $_memoData;
	protected $_memoHeader;

	/**
	 * Creates DBF handler
	 * 
	 * @param string $file DBF File name
	 * @param int $mode File open mode (only 0 - readonly now supported)
	 * @throws Exception
	 */
	public function __construct($file, $mode = self::MODE_READONLY) {
		if($mode !== self::MODE_READONLY) {
			throw new Exception("DBF mode $mode not impletented");
		}
		
		$this->_file = $file;
		$this->_initDbf();
		$this->_initMemoFile();
	}

	protected function _initMemoFile() {
		if(isset($this->_memoFile)) return;
		$file = realpath($this->_file);
		switch(true) {
			case file_exists($memoFile = substr($file, 0, -1) . "t"):
				break;
			case file_exists($memoFile = substr($file, 0, -1) . "T"):
				break;
			case file_exists($memoFile = substr($file, 0, -3) . "fpt"):
				break;
			case file_exists($memoFile = substr($file, 0, -3) . "FPT"):
				break;
			default:
				$memoFile = false;
		}
		$this->_memoFile = $memoFile;
	}

	public function __get($name) {
		switch($name) {
			case 'numRecords':
				return $this->countRecords();
			case 'numFields':
				return $this->countFields();
			case 'header':
				return $this->_header;
			case 'memoHeader':
				return $this->getMemoHeader();
			case 'memoFile':
				return $this->_memoFile;
			case 'fields':
				return $this->_fields;
			case 'memoData':
				return $this->_memoData;
			default:
				return null;
		}
	}

	public function countRecords() {
		return $this->_header['numRecords'];
	}

	protected function _initMemo() {
		if(isset($this->_memoData)) return;
		if($this->_memoFile === false) {
			$this->_memoData = false;
			$this->_memoHeader = false;
			return;
		}
		$f = fopen($this->_memoFile, 'rb');
		$this->_memoData = fread($f, filesize($this->_memoFile));
		$memo_header_format = 'Nnext/NSize of blocks';
		$this->_memoHeader = unpack($memo_header_format, substr($this->_memoData, 0, 8));
		if($this->_memoHeader["Size of blocks"] == 0) $this->_memoHeader["Size of blocks"] = 512;
	}

	/**
	 * @return int Number of fields in record
	 */
	public function countFields() {
		return count($this->_fields);
	}

	public function getMemoHeader() {
		$this->_initMemo();
		return $this->_memoHeader;
	}

	public function getRecordSize() {
		$size = 0;
		foreach($this->header as $field) {
			$size += $field['size'];
		}
		return $size;
	}

	/**
	 * Extracts record from DBF file
	 * @param int $num Record number (0 .. $numRecords-1)
	 * @return array Record assoc array
	 */
	public function getRecordWithNames($num) {
		if($num < 0 || $num >= $this->countRecords()) {
			throw new Exception("Invalid record range $num");
		}
		if(!isset($this->_data)) {
			fseek($this->_fp, $this->_header['headerSize'] + 1);
			$this->_data = fread($this->_fp, $this->_header['numRecords'] * $this->_header['recordSize'] - 1);
		}
		
		$offset = $num * $this->_header['recordSize'];
		$record = unpack("@$offset/" . $this->_getRecordFormat(), $this->_data);

		foreach($this->_fields as $i => $field) {
			$record[$field['name']] = trim($record[$field['name']]);
			if($field['type'] == 'M' || $field['type'] == 'G') {
				$record[$field['name']] = $this->_getMemo($record[$field['name']]);
			}
		}
		return $record;
	}

	protected function _getRecordFormat() {
		if(!isset($this->_recordFormat)) {
			$this->_recordFormat = '';
			foreach($this->_fields as $field) {
				$this->_recordFormat .= 'A' . $field['length'] . $field['name'] . '/';
			}
			$this->_recordFormat = substr($this->_recordFormat, 0, -1);
		}
		return $this->_recordFormat;
	}

	protected function _getMemo($block) {
		$this->_initMemo();
		if($this->_memoData === false) return null;
		$sizeOfBlock = $this->_memoHeader['Size of blocks'];
		if(!preg_match('/^\d+$/',$block)) {
			$b = unpack("S", $block);
			$block = $b[1];
		}
		$offset = ($block) * $sizeOfBlock;
		$block_format = 'N/N';
		$block_data = unpack("@$offset/$block_format", $this->_memoData);
		return substr($this->_memoData, $offset + 8, $block_data[1]);
	
	}

	protected function _getMemoEnd($string) {
		for($i = -1; $i <= strlen($string); $i++) {
			$temp_s = implode(" ", unpack('H*', substr($string, $i, 1)));
			if($temp_s == "1a") return substr($string, 0, $i);
		}
		return $string;
	}

	protected function _initDbf() {
		if(($this->_fp = fopen($this->_file, 'rb')) === false) {
			throw new Exception("File {$this->_file} cannot be opened");
		}
		if(($data = fread($this->_fp, 32)) === false) {
			throw new Exception("Could not read header from file {$this->_file}");
		}
		$header_format = 'H2id/' . 'CYear/' . 'CMonth/' . 'CDay/' . 'LnumRecords/' . 'SheaderSize/' . 'SrecordSize';
		$this->_header = unpack($header_format, $data);
		
		if(($data = fread($this->_fp, $this->_header['headerSize'] - 34)) === false) {
			throw new Exception("Could not read data from file {$this->_file}");
		}
		$record_format = 'A11name/' . 'Atype/' . 'x4/' . 'Clength/' . 'Cprecision';
		$x = 0;
		for($offset = 0; $offset < strlen($data); $offset += 32) {
			$x++;
			$field = unpack("@$offset/$record_format", $data);
			foreach($field as $key => $value) {
				$field[$key] = $this->_dropAfterNULL(trim($value));
			}
			if(strlen($field['name'])) {
				$fields[$x] = $field;
			}
		
		}
		$this->_fields = $fields;
	}

	protected function _dropAfterNULL($string) {
		for($i = -1; $i <= strlen($string); $i++) {
			$temp_s = implode(' ', unpack('C*', substr($string, $i, 1)));
			if($temp_s == 0) return substr($string, 0, $i);
		}
		return $string;
	}

}