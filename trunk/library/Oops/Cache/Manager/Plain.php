<?php

class Oops_Cache_Manager_Plain implements Oops_Cache_Manager_Interface {
	private $_missing = array();
	private $_nocache = array();

	public function __construct($config) {
		$this->_driver = Oops_Cache_Factory::getDriver($config);
	}

	public function get($key) {
		$value = $this->_driver->get($key);
		if($value === false) $this->_missing[$key] = true;
		return $value;
	}

	public function set($key, $value, $ttl = null) {
		if(isset($this->_nocache[$key])) {
			unset($this->_nocache[$key]);
			return false;
		}
		unset($this->_missing[$key]);
		return $this->_driver->set($key, $value, $ttl);
	}

	public function drop($key) {
		unset($this->_missing[$key]);
		return $this->_driver->drop($key);
	}

	public function touch($key) {
		return;
	}

	public function nocache() {
		foreach(array_keys($this->_missing) as $key)
			$this->_nocache[$key] = true;
		$this->_missing = array();
	}
}

