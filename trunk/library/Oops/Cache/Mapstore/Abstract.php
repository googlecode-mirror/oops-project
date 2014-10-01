<?php

abstract class Oops_Cache_Mapstore_Abstract {
	
	/**
	 * 
	 * @param string $target Target key, depending on sources
	 * @param array $sources Array of keys, target depends on
	 */
	abstract public function store($target, $sources);
	
	/**
	 * 
	 * @param string $source
	 * @return array Target keys depending on given one
	 */
	abstract public function find($source);
	
	/**
	 * 
	 * @param string $key
	 */
	abstract public function drop($target);

	/**
	 * 
	 * @param array $keys
	 */
	public function multiDrop($targets) {
		foreach($targets as $target) $this->drop($target);
	}
}