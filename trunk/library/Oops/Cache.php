<?php

class Oops_Cache {
	/**
	 *
	 * @var Oops_Cache_Manager_Interface
	 */
	private static $_instance;
	
	/**
	 *
	 * @return Oops_Cache_Manager_Interface
	 */
	static public function getInstance() {
		if(!isset(self::$_instance)) {
			$cfg = new Oops_Cache_Defconf();
			$cfg->mergeConfig(Oops_Server::getConfig()->cache);
	
				
			self::$_instance = $cfg->manager == 'cascade' ? new Oops_Cache_Manager_Cascade($cfg) : new Oops_Cache_Manager_Plain($cfg);
		}
		return self::$_instance;
	}
	
	/**
	 * 
	 * Get stored value from cache 
	 * 
	 * @param string $key
	 * @return mixed Cached value or FALSE on failure
	 */
	static public function get($key) {
		return self::getInstance()->get($key);
	}
	
	/**
	 * 
	 * @param string $key Cache key
	 * @param mixed $value Value to store
	 * @param int $ttl TTL
	 * @return bool
	 */
	static public function set($key, $value, $ttl = null) {
		return self::getInstance()->set($key, $value, $ttl);
	}

	/**
	 * 
	 * @param string $key
	 */
	static public function drop($key) {
		return self::getInstance()->drop($key);
	}
	
	/**
	 * 
	 * @param string $key
	 */
	static public function touch($key) {
		return self::getInstance()->touch($key);
	}
}