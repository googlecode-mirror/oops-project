<?php

/**
 * 
 * All-in-on cascade cache on local apc and local mongodb
 *
 */
class Oops_Cache_Box {

	/**
	 * @var bool Whenever collecting cache map is running or not
	 */
	private $_isMapping = false;


	private $_missing = array();
	private $_touched = array();
	
	/**
	 * 
	 * @var MongoCollection
	 */
	private $_collection;

	/**
	 * @var Oops_Cache_Box
	 */
	private static $_instance;
	
	/**
	 * 
	 * @return Oops_Cache_Box
	 */
	static public function getInstance() {
		if(!isset(self::$_instance)) self::$_instance = new self;
		return self::$_instance;
	} 
	
	/**
	 *
	 * @param string $key
	 * @return mixed|false Value or FALSE on failure
	 */
	public function get($key) {
		$value = apc_fetch($key);
		if($value !== false) { //value found in cache, we must just return it
			// but if some earlier requests keys missing, touch this one for stats
			if($this->_isMapping) $this->touch($key);
			// finally return the value
			return $value;
		}

		// remember key is missing and touch it for harvesting needs
		$this->_missing[$key] = $this->touch($key);
		// set isMapping flag as we started to map this key's dependencies
		$this->_isMapping = true;
		return false;
	}

	public function set($key, $value, $ttl = null) {

		// nothing to do if we're not mapping now, just return
		// find all keys touched after this one to store them
		// 1. if this key is the last touched, there's no map, so skip to cleanup
		if($this->_isMapping && $key != end($this->_touched))  {

			// 2. find this key position in touch history
			$keyPos = array_search($key, $this->_touched);

			// 2a. not found in touched but was missing? something's broken
			if($keyPos === false) throw new Oops_Exception("Impossible but missing cache key was not touched");

			// 3. array tail contains the source keys (real keys or tags), this key depends on
			$sources = array_slice($this->_touched, $keyPos + 1);

			// 4a. Init mapstore if not done yet
			if(!isset($this->_collection)) $this->_initMapstoreCollection();
			// Store the dependencies map
			$this->_collection->save(array('_id' => $key, 'source' => $sources));
		}
		
		// Store now
		apc_store($key, $value, $ttl);
		
		// 5. Cleanup _missing and _touched
		// 5a. it was set, it's not missing anymore
		unset($this->_missing[$key]);

		// 5b. full chain from the very first missing was finished, clear the
		// touched chain
		// @todo consider checking _missing array is empty
		if($this->_isMapping && $key == $this->_touched[0]) {
			$this->_touched = array();
			$this->_missing = array();
			$this->_isMapping = false;
		}

	}

	public function drop($key) {
		apc_delete($key);

		// init mapstore
		if(!isset($this->_collection)) $this->_initMapstoreCollection();
		// drop key's map
		$this->_collection->remove(array('_id' => $key));
		$cursor = $this->_collection->find(array('source' => $key), array('source' => false));
		$targets = array();
		foreach($cursor as $t) {
			$targets[] = (string) $t['_id'];
		}
		apc_delete($targets);
		$this->_collection->remove(array('_id' => array('$in' => $targets)));
	}

	public function touch($key) {
		return array_push($this->_touched, $key) - 1;
	}

	private function _initMapstoreCollection() {
		$cli = new MongoClient();
		$this->_collection = $cli->cascache->mapstore;
		$this->_collection->ensureIndex(array('source' => 1));
	}
	
	static public function g($k) {
		if(!isset(self::$_instance)) self::$_instance = new self;
		return self::$_instance->get($k);
	} 

	static public function s($k, $v, $t = null) {
		if(!isset(self::$_instance)) self::$_instance = new self;
		return self::$_instance->set($k, $v, $t);
	}

	static public function d($k) {
		if(!isset(self::$_instance)) self::$_instance = new self;
		return self::$_instance->drop($k);
	}
	
}
