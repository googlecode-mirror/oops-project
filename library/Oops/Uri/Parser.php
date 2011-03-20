<?php

/**
 *
 * @property-read string $path
 * @property-read string $expectedPath
 * @property-read string $action
 * @property-read string $extension
 * @property-read array $parts
 *
 */
class Oops_Uri_Parser {
	protected $_path;
	protected $_expectedPath;
	protected $_action;
	protected $_extension;
	protected $_parts;
	

	/**
	 * Parses URI into parts, action and extension, checks spelling
	 *
	 */
	public function __construct($path, $defaultAction = 'index', $defaultExtension = 'php', $validExtensions = null) {
		$this->_path = $path;
		$parts = explode("/", $path);
		$coolparts = array();
		
		//Let's remove any empty parts. path//to/something/ should be turned into path/to/something
		for($i = 0, $cnt = count($parts); $i < $cnt; $i++) {
			if(strlen($parts[$i])) $coolparts[] = strtolower($parts[$i]);
		}
		if(($cnt = count($coolparts)) != 0) {
			$last = $coolparts[$cnt - 1];
			if(($dotpos = strrpos($last, '.')) !== FALSE) {
				$ext = substr($last, $dotpos + 1);
				if(!is_array($validExtensions) || in_array($ext, $validExtensions)) {
					$this->_action = substr($last, 0, $dotpos);
					$this->_extension = $ext;
					array_pop($coolparts);
				}
			}
		}
		
		if(!isset($this->_action)) {
			//action should be index, content-type - php
			$this->_action = $defaultAction;
			$this->_extension = $defaultExtension;
		}
		
		//Let's compile the one-and-only expected request_uri for this kind of request
		$this->_expectedPath = sizeof($coolparts) ? '/' . join('/', $coolparts) . '/' : '/';
		if($this->_action != $defaultAction || $this->_extension != $defaultExtension) $this->_expectedPath .= "{$this->_action}.{$this->_extension}";
		$this->_parts = $coolparts;
	}
	
	public function __get($name) {
		switch($name) {
			case 'path':
				return $this->_path;
			case 'expectedPath':
				return $this->_expectedPath;
			case 'action':
				return $this->_action;
			case 'extension':
				return $this->_extension;
			case 'parts':
				return $this->_parts;
		}
	}
}