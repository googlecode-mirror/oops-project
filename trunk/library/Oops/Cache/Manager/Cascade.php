<?php

/**
 * 
 * @author DI
 * 
 * Class for cache operations, incapsulating dependencies
 *
 */
class Oops_Cache_Manager_Cascade {
	
	/**
	 * 
	 * @var Oops_Config
	 */
	private $_config;
	
	/**
	 * 
	 * @var Oops_Cache_Driver_Abstract
	 */
	private $_driver;
	
	/**
	 * 
	 * @var Oops_Cache_Mapstore_Abstract
	 */
	private $_mapstore;
	
	/**
	 * @var bool Whenever collecting cache map is running or not 
	 */
	private $_isMapping = false;
	
	
	private $_missing = array();
	private $_touched = array();
	
	/**
	 * 
	 * @param Oops_Config $config
	 */
	public function __construct($config = null) {
		if(!$config instanceof Oops_Config) $config = new Oops_Cache_Defconf();
		$this->_config = $config;
		$this->_initDriver();
	}
	
	/**
	 * 
	 * @param string $key
	 * @return mixed|false Value or FALSE on failure
	 */
	public function get($key) {
		$value = $this->_driver->get($key);
		if($value !== false) { //value found in cache, we must just return it
			// but if some earlier requests keys missing, touch this one for stats
			if($this->_isMapping) $this->touch($key);
			// finally return the value
			return $this->_tmp[$key];
		}
		
		// remember key is missing and touch it for harvesting needs
		$this->_missing[$key] = $this->touch($key);
		// set isMapping flag as we started to map this key's dependencies
		$this->_isMapping = true;
	}

	public function set($key, $value, $ttl = null) {
		$this->_driver->set($key, $value, $ttl);
		
		// nothing to do if we're not mapping now, just return
		if(!$this->_isMapping) return;
		
		// find all keys touched after this one to store them
		// 1. if this key is the last touched, there's no map, so skip to cleanup
		if($key != end($this->_touched))  { 
		
		// 2. find this key position in touch history
		$keyPos = array_search($key, $this->_touched);
		
		// 2a. not found in touched but was missing? something's broken
		if($keyPos === false) throw new Oops_Exception("Impossible but missing cache key was not touched");
		
		// 3. array tail contains the source keys (real keys or tags), this key depends on
		$sources = array_slice($this->_touched, $keyPos + 1);
		
		// 4a. Init mapstore if not done yet
		if(!isset($this->_mapstore)) $this->_initMapstore();
		// Store the dependencies map
		$this->_mapstore->store($key, $sources);
		}
		
		// 5. Cleanup _missing and _touched 
		// 5a. it was set, it's not missing anymore
		unset($this->_missing[$key]);
		
		// 5b. full chain from the very first missing was finished, clear the
		// touched chain
		// @todo consider checking _missing array is empty
		if($key == $this->_touched[0]) {
			$this->_touched = array();
			$this->_missing = array();
			$this->_isMapping = false;
		}
		
	}
	
	public function drop($key) {
		$this->_driver->drop($key);
		
		// init mapstore
		if(!isset($this->_mapstore)) $this->_initMapstore();
		// drop key's map
		$this->_mapstore->drop($key);
		
		$targets = $this->_mapstore->find($key);
		foreach($targets as $targetKey) {
			// @todo make driver drop array of keys
			$this->_driver->drop($targetKey);
			// @todo make mapstore drop array of keys
			$this->_mapstore->drop($targetKey);
		}
	}
	
	public function touch($key) {
		return array_push($this->_touched, $key) - 1;
	}
	
	private function _initDriver() {
		$name = $this->_config->driver;
		$class = 'Oops_Cache_Driver_' . ucfirst($name);
		if(!class_exists($class)) $class = $name;
		if(!class_exists($class)) throw new Oops_Exception("Cache driver class '$class' (name '$name') not found");
		$this->_driver = new $class($this->_config->$name);
	}
	
	private function _initMapstore() {
		$name = $this->_config->mapstore;
		$class = 'Oops_Cache_Mapstore_' . ucfirst($name);
		if(!class_exists($class)) $class = $name;
		if(!class_exists($class)) throw new Oops_Exception("Cache mapstore class '$class' (name '$name') not found");
		$this->_mapstore = new $class($this->_config->$name);
	}
}