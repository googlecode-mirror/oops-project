<?php

class Oops_Config_Writer_Ini {
	
	protected $_keyDelimiter = '.';
	
	protected $_filename;
	
	public function write($filename = null, Oops_Config $config = null) {
		if(!is_null($filename)) $this->setFilename($filename);
		if(!is_null($config)) $this->setConfig($config);
		
		if(is_null($this->_config) || !($this->_config instanceof Oops_Config)) {
			throw new Exception("Invalid config object");
		}

		$iniString = '';
		$sectionsPart  = '';
		foreach($this->_config as $key => $value) {
			if($value instanceof Oops_Config && count($value)) {
				$sectionsPart  .= "[$key]\n";
				$sectionsPart .= $this->_config2string($value);
			} else {
				if($value === true) $value = 'On';
				elseif($value === false) $value = 'Off';
				elseif(preg_match('/[^a-zA-Z0-9 ]/', $value)) $value = "\"$value\"";
				$iniString .= "$key = $value\n";
			}
		}
		
		$iniString .= "\n" . $sectionsPart;
		
		return (bool) file_put_contents($this->_filename, $iniString);
	}
	
	public function setKeyDelimiter($keyDelimiter) {
		$this->_keyDelimiter = $keyDelimiter;
	}
	
	public function setConfig(Oops_Config $config) {
		$this->_config = $config; 
	}
	
	public function setFilename($filename) {
		$this->_filename = $filename;
	}
	
	protected function _config2string($config, $prefix = '') {
		$string = '';
		foreach($config as $key => $value) {
			if($value instanceof Oops_Config && count($value)) {
				$string .= $this->_config2string($value, $prefix . $key . $this->_keyDelimiter) . "\n";
			} else {
				if($value === true) $value = 'On';
				elseif($value === false) $value = 'Off';
				elseif(preg_match('/[^a-zA-Z0-9 ]/', $value)) $value = "\"$value\"";
				$string .= $prefix . $key . " = $value\n";
			}			
		}
		return $string;
	}
}