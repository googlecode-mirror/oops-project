<?php

class Oops_Cache_Box {
	private $_missing = array();
	private $_touched = array();
	private $_tmp = array();
	private $_map;

	public function get($key) {
		if(isset($this->_tmp[$key])) {
			// if some earlier requests keys missing, touch this one for stats
			if(count($this->_missing)) $this->touch($key);
			return $this->_tmp[$key];
		}
		
		// remember key is missing and touch it for harvesting needs
		$this->_missing[$key] = $this->touch($key);
	}

	public function set($key, $value) {
		$this->_tmp[$key] = $value;
		
		// find all keys touched after this one to store them
		/*
		 * $keyPos = array_search($key, $this->_touched); if($keyPos === false)
		 * return; // it was not missing
		 */
		
		/*
		 *  вариант с _missing удобнее т.к. можно не делать touch пока нет missing
		 *   и точно знать что ключ не был найден до того как вызван set
		 *   но пока под вопросом
		 */  
		if(!isset($this->_missing[$key])) return; // it was not missing, nothing
		                                          // to do
		                                          
		// find key position in touch history
		$keyPos = array_search($key, $this->_touched);
		
		// not found in touched but was missing? something's broken
		if($keyPos === false) throw new Oops_Exception("Impossible but missing cache key was not touched");
		
		// it's the last touched element, nothing to do
		if($keyPos == count($this->_touched) - 1) return;
		
		// Here are elements (cache keys or tags), this key depends on
		$dependsOn = array_slice($this->_touched, $keyPos + 1);
		
		// Store the dependencies map
		$this->_map[$key] = $dependsOn;
		
		// Cleanup arrays
		// it was set, it's not missing anymore
		unset($this->_missing[$key]);
		
		// full chain from the very first missing was finished, clear the
		// touched chain
		if($keyPos === 0) {
			$this->_touched = array();
		}
	}

	public function touch($key) {
		return array_push($this->_touched, $key) - 1;
	}

	public function drop($key) {
		unset($this->_tmp[$key]);
		
		if(isset($this->_map[$key])) unset($this->_map[$key]);
		
		foreach($this->_map as $k => $v) {
			if(in_array($key, $v)) {
				unset($this->_tmp[$k]);
				
				unset($this->_map[$k]);
			}
		}
	}
}